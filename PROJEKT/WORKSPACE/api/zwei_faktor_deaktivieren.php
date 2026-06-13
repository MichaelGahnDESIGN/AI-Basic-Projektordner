<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /zwei_faktor_deaktivieren.php  (JWT-geschützt)
 *
 * Schaltet die Zwei-Faktor-Authentifizierung ab:
 *  - verlangt das aktuelle Passwort als Bestätigung (sonst 401) — ein
 *    gestohlenes Token allein genügt also nicht,
 *  - löscht das gespeicherte Secret und setzt totp_aktiviert auf 0.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

eingabe_methode_erzwingen('POST');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);
$eingabe = eingabe_json();

// --- 1. Eingabe prüfen -------------------------------------------------------------
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

// --- 3. Secret löschen und 2FA abschalten ---------------------------------------------
$aktualisieren = $pdo->prepare(
    'UPDATE users SET totp_secret_enc = NULL, totp_aktiviert = 0 WHERE id = :id'
);
$aktualisieren->execute(['id' => $userId]);

antwort_ok(['aktiviert' => false]);
