<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /zwei_faktor_aktivieren.php  (JWT-geschützt)
 *
 * Schließt das 2FA-Setup ab: Der Benutzer beweist mit einem gültigen
 * TOTP-Code, dass seine Authenticator-App das Secret korrekt übernommen
 * hat — erst dann wird totp_aktiviert auf 1 gesetzt.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/recovery.php';

eingabe_methode_erzwingen('POST');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);
$eingabe = eingabe_json();

// --- 1. Eingabe prüfen -------------------------------------------------------------
$codeRoh = $eingabe['code'] ?? null;
$code = is_string($codeRoh) ? trim($codeRoh) : '';
if ($code === '') {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', [
        'code' => 'Der 6-stellige Code aus der Authenticator-App ist erforderlich.',
    ]);
}

// --- 2. Konto und gespeichertes Secret laden -------------------------------------------
$pdo = db_verbindung();
$abfrage = $pdo->prepare('SELECT id, aktiv, totp_secret_enc, totp_aktiviert FROM users WHERE id = :id');
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}
if ((int) $benutzer['aktiv'] !== 1) {
    antwort_fehler(403, 'konto_gesperrt', 'Dieses Konto ist deaktiviert.');
}
if ($benutzer['totp_secret_enc'] === null || $benutzer['totp_secret_enc'] === '') {
    antwort_fehler(409, 'zwei_faktor_nicht_gestartet', 'Es wurde noch kein 2FA-Setup begonnen.');
}

// --- 3. Code gegen das entschlüsselte Secret prüfen -------------------------------------
$secretBase32 = krypto_entschluesseln((string) $benutzer['totp_secret_enc'], 'users.totp_secret_enc');

if (!totp_code_pruefen($secretBase32, $code)) {
    antwort_fehler(401, 'code_falsch', 'Der Code ist ungültig oder abgelaufen.');
}

// --- 4. 2FA endgültig aktivieren ----------------------------------------------------------
$aktualisieren = $pdo->prepare('UPDATE users SET totp_aktiviert = 1 WHERE id = :id');
$aktualisieren->execute(['id' => $userId]);

// --- 5. Recovery-Codes erzeugen (einmalig im Response, danach nur als Hash) -------------
$recoveryCodes = recovery_codes_erzeugen($pdo, $userId);

antwort_ok([
    'aktiviert'       => true,
    'recovery_codes'  => $recoveryCodes,
]);
