<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /login.php
 *
 * Meldet einen Benutzer an:
 *  - findet das Konto über den E-Mail-Blind-Index (kein Klartext in der DB),
 *  - prüft das Passwort gegen den Argon2id-Hash,
 *  - antwortet bei unbekannter E-Mail und falschem Passwort bewusst mit
 *    derselben Meldung (kein Ausspähen registrierter Konten),
 *  - verlangt bei aktivierter Zwei-Faktor-Authentifizierung zusätzlich
 *    einen gültigen TOTP-Code (Body-Feld "totp_code") — geprüft erst
 *    NACH dem Passwort, damit der 2FA-Status nichts über fremde Konten
 *    verrät,
 *  - liefert ein JWT für nachfolgende Anfragen.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/validierung.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/jwt.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/recovery.php';

/**
 * Gültig aufgebauter Argon2id-Dummy-Hash: Bei unbekannter E-Mail wird dagegen
 * verifiziert, damit die Antwortzeit der eines existierenden Kontos gleicht.
 * Das erschwert Timing-Angriffe zum Ausspähen registrierter E-Mail-Adressen.
 */
const LOGIN_TIMING_DUMMY_HASH =
    '$argon2id$v=19$m=65536,t=4,p=1$MDEyMzQ1Njc4OWFiY2RlZg$MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY';

eingabe_methode_erzwingen('POST');
$eingabe = eingabe_json();

// --- 1. Eingaben prüfen --------------------------------------------------------
$email = validierung_email($eingabe['email'] ?? null);

$passwortRoh = $eingabe['passwort'] ?? null;
$passwort = is_string($passwortRoh) ? $passwortRoh : '';

if ($email === null || $passwort === '') {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'E-Mail-Adresse und Passwort sind erforderlich.');
}

// --- 2. Konto über den Blind-Index suchen ---------------------------------------
$pdo = db_verbindung();

// Brute-Force-Schutz: pro IP zählen, nach Grenze temporär sperren.
$rateKey = rate_limit_schluessel('api-login', (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
$wartenSek = rate_limit_gesperrt($pdo, $rateKey);
if ($wartenSek > 0) {
    header('Retry-After: ' . $wartenSek);
    antwort_fehler(429, 'zu_viele_versuche',
        'Zu viele Anmeldeversuche. Bitte in etwa ' . (int) ceil($wartenSek / 60) . ' Minuten erneut versuchen.');
}

$abfrage = $pdo->prepare(
    'SELECT id, benutzername, sprache, rolle, aktiv, passwort_hash,
            totp_secret_enc, totp_aktiviert
     FROM users
     WHERE email_blind_index = :blind_index'
);
$abfrage->execute(['blind_index' => krypto_email_blind_index($email)]);
$benutzer = $abfrage->fetch();

// --- 3. Passwort prüfen (mit Timing-Ausgleich) ----------------------------------
if ($benutzer === false) {
    password_verify($passwort, LOGIN_TIMING_DUMMY_HASH);
    rate_limit_fehlschlag($pdo, $rateKey);
    antwort_fehler(401, 'anmeldung_fehlgeschlagen', 'E-Mail-Adresse oder Passwort ist falsch.');
}

if (!password_verify($passwort, (string) $benutzer['passwort_hash'])) {
    rate_limit_fehlschlag($pdo, $rateKey);
    antwort_fehler(401, 'anmeldung_fehlgeschlagen', 'E-Mail-Adresse oder Passwort ist falsch.');
}

// Erst NACH erfolgreicher Passwortprüfung melden, dass das Konto gesperrt ist —
// so verrät der Sperrstatus nichts über fremde Konten.
if ((int) $benutzer['aktiv'] !== 1) {
    antwort_fehler(403, 'konto_gesperrt', 'Dieses Konto ist deaktiviert.');
}

// --- 4. Zweiter Faktor (TOTP), falls für das Konto aktiviert ----------------------
// Erst NACH der Passwortprüfung, damit Unbefugte ohne gültiges Passwort
// nicht erfahren, ob ein Konto 2FA nutzt.
if ((int) $benutzer['totp_aktiviert'] === 1) {
    $totpCodeRoh = $eingabe['totp_code'] ?? null;
    $totpCode = is_string($totpCodeRoh) ? trim($totpCodeRoh) : '';

    if ($totpCode === '') {
        // Signal an den Client: Passwort war korrekt, jetzt den Code nachreichen.
        antwort_fehler(401, '2fa_erforderlich', 'Für dieses Konto ist ein Zwei-Faktor-Code erforderlich.');
    }

    $secretBase32 = krypto_entschluesseln((string) $benutzer['totp_secret_enc'], 'users.totp_secret_enc');

    // Gültig ist der TOTP-Code ODER ein ungenutzter Recovery-Code.
    $totpOk = totp_code_pruefen($secretBase32, $totpCode);
    if (!$totpOk && !recovery_code_einloesen($pdo, (int) $benutzer['id'], $totpCode)) {
        rate_limit_fehlschlag($pdo, $rateKey);
        antwort_fehler(401, '2fa_falsch', 'Der Zwei-Faktor- oder Recovery-Code ist ungültig oder abgelaufen.');
    }
}

// Erfolgreiche Anmeldung → Fehlversuchszähler zurücksetzen.
rate_limit_erfolg($pdo, $rateKey);

// --- 5. Hash bei geänderten Argon2id-Parametern transparent erneuern -------------
$argonOptionen = konfiguration()['argon2_optionen'] ?? [];
if (password_needs_rehash((string) $benutzer['passwort_hash'], PASSWORD_ARGON2ID, $argonOptionen)) {
    $erneuern = $pdo->prepare('UPDATE users SET passwort_hash = :hash WHERE id = :id');
    $erneuern->execute([
        'hash' => password_hash($passwort, PASSWORD_ARGON2ID, $argonOptionen),
        'id'   => (int) $benutzer['id'],
    ]);
}

// --- 6. JWT ausstellen ------------------------------------------------------------
antwort_ok([
    'token'                  => jwt_erstellen((int) $benutzer['id'], (string) $benutzer['rolle']),
    'token_gueltig_sekunden' => (int) konfiguration()['jwt']['gueltigkeit_sekunden'],
    'benutzer' => [
        'id'           => (int) $benutzer['id'],
        'benutzername' => $benutzer['benutzername'],
        'sprache'      => $benutzer['sprache'],
        'rolle'        => $benutzer['rolle'],
    ],
]);
