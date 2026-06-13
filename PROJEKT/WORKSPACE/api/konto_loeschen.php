<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /konto_loeschen.php  (JWT-geschützt)
 *
 * Löscht das Konto des angemeldeten Benutzers endgültig:
 *  - verlangt das aktuelle Passwort als Bestätigung (sonst 401),
 *  - DELETE auf users genügt — Spielstand, Einwilligungen und 2FA-Daten
 *    werden über die Fremdschlüssel (ON DELETE CASCADE) bzw. mit der
 *    Zeile selbst entfernt (DSGVO: vollständige Löschung).
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
$abfrage = $pdo->prepare('SELECT id, passwort_hash FROM users WHERE id = :id');
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}
if (!password_verify($passwort, (string) $benutzer['passwort_hash'])) {
    antwort_fehler(401, 'passwort_falsch', 'Das Passwort ist falsch.');
}

// --- 3. Endgültig löschen (CASCADE räumt abhängige Daten mit ab) -----------------------
$loeschen = $pdo->prepare('DELETE FROM users WHERE id = :id');
$loeschen->execute(['id' => $userId]);

antwort_ok(['geloescht' => true]);
