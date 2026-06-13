<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /register.php
 *
 * Legt ein neues Benutzerkonto an:
 *  - prüft alle Eingaben (422 mit feldbezogenen Hinweisen),
 *  - speichert E-Mail, Vorname und Nachname verschlüsselt (AES-256-GCM),
 *  - speichert die E-Mail zusätzlich als Blind-Index für die Login-Suche,
 *  - hasht das Passwort mit Argon2id,
 *  - protokolliert AGB- und Datenschutz-Einwilligung mit Version + Zeitstempel,
 *  - liefert direkt ein JWT (Benutzer ist nach der Registrierung eingeloggt).
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/validierung.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/sperrbegriffe.php';

eingabe_methode_erzwingen('POST');
$eingabe = eingabe_json();

// --- 1. Eingaben prüfen ------------------------------------------------------
$probleme = [];

$vorname = validierung_text($eingabe['vorname'] ?? null, 1, 100);
if ($vorname === null) {
    $probleme['vorname'] = 'Pflichtfeld, 1 bis 100 Zeichen.';
}

$nachname = validierung_text($eingabe['nachname'] ?? null, 1, 100);
if ($nachname === null) {
    $probleme['nachname'] = 'Pflichtfeld, 1 bis 100 Zeichen.';
}

$email = validierung_email($eingabe['email'] ?? null);
if ($email === null) {
    $probleme['email'] = 'Gültige E-Mail-Adresse erforderlich (max. 254 Zeichen).';
}

$sprache = validierung_sprache($eingabe['sprache'] ?? null);
if ($sprache === null) {
    $probleme['sprache'] = 'Erlaubt sind nur "de" oder "en".';
}

$benutzername = validierung_benutzername($eingabe['benutzername'] ?? null);
if ($benutzername === null) {
    $probleme['benutzername'] = '3 bis 30 Zeichen: Buchstaben, Ziffern, Unterstrich.';
}

$passwort = validierung_passwort($eingabe['passwort'] ?? null);
if ($passwort === null) {
    $probleme['passwort'] = 'Mindestens 8, höchstens 200 Zeichen.';
}

$agbVersion = validierung_version($eingabe['agb_version'] ?? null);
if ($agbVersion === null) {
    $probleme['agb_version'] = 'Zustimmung zu den AGB (mit Versionsangabe) erforderlich.';
}

$datenschutzVersion = validierung_version($eingabe['datenschutz_version'] ?? null);
if ($datenschutzVersion === null) {
    $probleme['datenschutz_version'] = 'Zustimmung zur Datenschutzerklärung (mit Versionsangabe) erforderlich.';
}

if ($probleme !== []) {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', $probleme);
}

// --- 2. Argon2id muss auf dieser PHP-Installation verfügbar sein --------------
if (!defined('PASSWORD_ARGON2ID')) {
    throw new RuntimeException('PASSWORD_ARGON2ID wird von dieser PHP-Installation nicht unterstützt.');
}

// --- 3. Eindeutigkeit vorab prüfen (für klare Fehlermeldungen) ----------------
$pdo = db_verbindung();
$blindIndex = krypto_email_blind_index($email);

$pruefung = $pdo->prepare(
    'SELECT email_blind_index, benutzername
     FROM users
     WHERE email_blind_index = :blind_index OR benutzername = :benutzername'
);
$pruefung->execute(['blind_index' => $blindIndex, 'benutzername' => $benutzername]);

foreach ($pruefung->fetchAll() as $vorhanden) {
    if ($vorhanden['email_blind_index'] === $blindIndex) {
        antwort_fehler(409, 'email_vergeben', 'Für diese E-Mail-Adresse existiert bereits ein Konto.');
    }
    if (strcasecmp((string) $vorhanden['benutzername'], $benutzername) === 0) {
        antwort_fehler(409, 'benutzername_vergeben', 'Dieser Benutzername ist bereits vergeben.');
    }
}

// --- 3b. Benutzername gegen globale Sperrbegriffe prüfen ----------------------
if (sperrbegriff_verletzt($pdo, $benutzername) !== null) {
    antwort_fehler(422, 'name_nicht_erlaubt', 'Eingaben sind unvollständig oder ungültig.', [
        'benutzername' => 'Dieser Name enthält einen nicht erlaubten Begriff.',
    ]);
}

// --- 4. Konto und Einwilligungen in einer Transaktion anlegen -----------------
$passwortHash = password_hash($passwort, PASSWORD_ARGON2ID, konfiguration()['argon2_optionen'] ?? []);

$ip = eingabe_client_ip();
$ipHash = $ip === null ? null : krypto_ip_hash($ip);

$pdo->beginTransaction();

try {
    $einfuegen = $pdo->prepare(
        'INSERT INTO users
            (email_blind_index, email_enc, vorname_enc, nachname_enc,
             benutzername, sprache, passwort_hash, rolle)
         VALUES
            (:blind_index, :email_enc, :vorname_enc, :nachname_enc,
             :benutzername, :sprache, :passwort_hash, :rolle)'
    );
    $einfuegen->execute([
        'blind_index'   => $blindIndex,
        'email_enc'     => krypto_verschluesseln($email, KRYPTO_KONTEXT_EMAIL),
        'vorname_enc'   => krypto_verschluesseln($vorname, KRYPTO_KONTEXT_VORNAME),
        'nachname_enc'  => krypto_verschluesseln($nachname, KRYPTO_KONTEXT_NACHNAME),
        'benutzername'  => $benutzername,
        'sprache'       => $sprache,
        'passwort_hash' => $passwortHash,
        'rolle'         => 'spieler',
    ]);

    $userId = (int) $pdo->lastInsertId();

    $einwilligung = $pdo->prepare(
        'INSERT INTO einwilligungen (user_id, typ, version, ip_hash)
         VALUES (:user_id, :typ, :version, :ip_hash)'
    );
    $einwilligung->execute([
        'user_id' => $userId,
        'typ'     => 'agb',
        'version' => $agbVersion,
        'ip_hash' => $ipHash,
    ]);
    $einwilligung->execute([
        'user_id' => $userId,
        'typ'     => 'datenschutz',
        'version' => $datenschutzVersion,
        'ip_hash' => $ipHash,
    ]);

    $pdo->commit();
} catch (PDOException $fehler) {
    $pdo->rollBack();

    // Wettlauf mit parallelem Insert: Die Unique-Constraints der DB
    // haben das letzte Wort (SQLSTATE 23000 = Integritätsverletzung).
    if ((string) $fehler->getCode() === '23000') {
        antwort_fehler(409, 'bereits_vergeben', 'E-Mail-Adresse oder Benutzername ist bereits vergeben.');
    }

    throw $fehler;
}

// --- 5. Direkt einloggen: JWT ausstellen ---------------------------------------
antwort_ok([
    'token'                  => jwt_erstellen($userId, 'spieler'),
    'token_gueltig_sekunden' => (int) konfiguration()['jwt']['gueltigkeit_sekunden'],
    'benutzer' => [
        'id'           => $userId,
        'benutzername' => $benutzername,
        'sprache'      => $sprache,
        'rolle'        => 'spieler',
    ],
], 201);
