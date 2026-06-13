<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /passwort_aendern.php  (JWT-geschützt)
 *
 * Ändert das Passwort des angemeldeten Benutzers:
 *  - verlangt das aktuelle Passwort als Bestätigung (sonst 401),
 *  - prüft das neue Passwort (mind. 8 Zeichen, sonst 422),
 *  - speichert ausschließlich den Argon2id-Hash — nie ein Klartext-Passwort.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/validierung.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

eingabe_methode_erzwingen('POST');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);
$eingabe = eingabe_json();

// --- 1. Eingaben prüfen ------------------------------------------------------------
$altesPasswortRoh = $eingabe['altes_passwort'] ?? null;
$altesPasswort = is_string($altesPasswortRoh) ? $altesPasswortRoh : '';
if ($altesPasswort === '') {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', [
        'altes_passwort' => 'Das aktuelle Passwort ist erforderlich.',
    ]);
}

$neuesPasswort = validierung_passwort($eingabe['neues_passwort'] ?? null);
if ($neuesPasswort === null) {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', [
        'neues_passwort' => 'Mindestens 8, höchstens 200 Zeichen.',
    ]);
}

// --- 2. Konto laden und altes Passwort bestätigen --------------------------------------
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
if (!password_verify($altesPasswort, (string) $benutzer['passwort_hash'])) {
    antwort_fehler(401, 'passwort_falsch', 'Das Passwort ist falsch.');
}

// --- 3. Neuen Argon2id-Hash speichern ---------------------------------------------------
$aktualisieren = $pdo->prepare('UPDATE users SET passwort_hash = :hash WHERE id = :id');
$aktualisieren->execute([
    'hash' => password_hash($neuesPasswort, PASSWORD_ARGON2ID, konfiguration()['argon2_optionen'] ?? []),
    'id'   => $userId,
]);

antwort_ok(['geaendert' => true]);
