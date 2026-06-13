<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /email_aendern.php  (JWT-geschützt)
 *
 * Ändert die E-Mail-Adresse des angemeldeten Benutzers:
 *  - verlangt das aktuelle Passwort als Bestätigung (sonst 401),
 *  - prüft die neue Adresse (422) und ihre Eindeutigkeit über den
 *    Blind-Index (409),
 *  - speichert die Adresse neu verschlüsselt samt neuem Blind-Index —
 *    die Klartext-E-Mail erreicht die Datenbank nie.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/validierung.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/auth.php';

eingabe_methode_erzwingen('POST');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);
$eingabe = eingabe_json();

// --- 1. Eingaben prüfen ------------------------------------------------------------
$neueEmail = validierung_email($eingabe['neue_email'] ?? null);
if ($neueEmail === null) {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', [
        'neue_email' => 'Gültige E-Mail-Adresse erforderlich (max. 254 Zeichen).',
    ]);
}

$passwortRoh = $eingabe['passwort'] ?? null;
$passwort = is_string($passwortRoh) ? $passwortRoh : '';
if ($passwort === '') {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', [
        'passwort' => 'Das aktuelle Passwort ist erforderlich.',
    ]);
}

// --- 2. Konto laden und Passwort bestätigen ------------------------------------------
$pdo = db_verbindung();
$abfrage = $pdo->prepare('SELECT id, aktiv, passwort_hash FROM users WHERE id = :id');
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}
if ((int) $benutzer['aktiv'] !== 1) {
    antwort_fehler(403, 'konto_gesperrt', 'Dieses Konto ist deaktiviert.');
}
if (!password_verify($passwort, (string) $benutzer['passwort_hash'])) {
    antwort_fehler(401, 'passwort_falsch', 'Das Passwort ist falsch.');
}

// --- 3. Kollision über den Blind-Index prüfen ----------------------------------------
$blindIndex = krypto_email_blind_index($neueEmail);

$pruefung = $pdo->prepare('SELECT id FROM users WHERE email_blind_index = :blind_index AND id <> :id');
$pruefung->execute(['blind_index' => $blindIndex, 'id' => $userId]);
if ($pruefung->fetch() !== false) {
    antwort_fehler(409, 'email_vergeben', 'Für diese E-Mail-Adresse existiert bereits ein Konto.');
}

// --- 4. Neu verschlüsseln und speichern -----------------------------------------------
try {
    $aktualisieren = $pdo->prepare(
        'UPDATE users
         SET email_enc = :email_enc, email_blind_index = :blind_index
         WHERE id = :id'
    );
    $aktualisieren->execute([
        'email_enc'   => krypto_verschluesseln($neueEmail, KRYPTO_KONTEXT_EMAIL),
        'blind_index' => $blindIndex,
        'id'          => $userId,
    ]);
} catch (PDOException $fehler) {
    // Wettlauf mit paralleler Registrierung: Unique-Constraint hat das letzte Wort.
    if ((string) $fehler->getCode() === '23000') {
        antwort_fehler(409, 'email_vergeben', 'Für diese E-Mail-Adresse existiert bereits ein Konto.');
    }
    throw $fehler;
}

antwort_ok(['email' => $neueEmail]);
