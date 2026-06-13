<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /zwei_faktor_start.php  (JWT-geschützt)
 *
 * Beginnt das 2FA-Setup (TOTP) für den angemeldeten Benutzer:
 *  - erzeugt ein frisches Secret (20 Zufallsbytes, Base32),
 *  - speichert es verschlüsselt (AES-256-GCM, Kontext 'users.totp_secret_enc'),
 *  - lässt totp_aktiviert auf 0 — aktiv wird 2FA erst, wenn der Benutzer
 *    über zwei_faktor_aktivieren.php einen gültigen Code bestätigt,
 *  - liefert Secret und otpauth-URI (QR-Code-Inhalt) für die Authenticator-App.
 *
 * Ist 2FA bereits aktiviert, wird das Setup abgelehnt (409) — sonst könnte
 * ein gestohlenes Token den aktiven zweiten Faktor unbemerkt ersetzen.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/auth.php';

eingabe_methode_erzwingen('POST');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);

// --- 1. Konto laden ----------------------------------------------------------------
$pdo = db_verbindung();
$abfrage = $pdo->prepare('SELECT id, email_enc, aktiv, totp_aktiviert FROM users WHERE id = :id');
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}
if ((int) $benutzer['aktiv'] !== 1) {
    antwort_fehler(403, 'konto_gesperrt', 'Dieses Konto ist deaktiviert.');
}
if ((int) $benutzer['totp_aktiviert'] === 1) {
    antwort_fehler(409, 'zwei_faktor_bereits_aktiv', 'Die Zwei-Faktor-Authentifizierung ist bereits aktiviert.');
}

// --- 2. Frisches Secret erzeugen und verschlüsselt speichern --------------------------
$secretBase32 = totp_secret_erzeugen();

$aktualisieren = $pdo->prepare(
    'UPDATE users SET totp_secret_enc = :secret_enc, totp_aktiviert = 0 WHERE id = :id'
);
$aktualisieren->execute([
    'secret_enc' => krypto_verschluesseln($secretBase32, 'users.totp_secret_enc'),
    'id'         => $userId,
]);

// --- 3. Secret und otpauth-URI für die Authenticator-App liefern ----------------------
$email = krypto_entschluesseln((string) $benutzer['email_enc'], KRYPTO_KONTEXT_EMAIL);

antwort_ok([
    'secret_base32' => $secretBase32,
    'otpauth_uri'   => totp_otpauth_uri($secretBase32, $email, 'Shape Miner Deluxe'),
]);
