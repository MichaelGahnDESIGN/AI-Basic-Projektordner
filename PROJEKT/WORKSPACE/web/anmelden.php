<?php

declare(strict_types=1);

/**
 * Aufgabe: Login-Seite der SMU-Web-Accountverwaltung.
 *
 * Flow:
 *   1. Bereits eingeloggt → /konto
 *   2. POST: Email + Passwort prüfen, ggf. TOTP verlangen
 *   3. Erfolg → /konto
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/nutzer.php';
require_once __DIR__ . '/../../api/totp.php';

smu_sitzung_starten();

if (smu_sitzung_nutzer() !== null) {
    header('Location: /konto');
    exit;
}

$fehler      = '';
$totpErforderlich = false;
$eingabeEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $email    = trim((string) ($_POST['email'] ?? ''));
    $passwort = (string) ($_POST['passwort'] ?? '');
    $totpCode = trim((string) ($_POST['totp_code'] ?? ''));
    $eingabeEmail = $email;

    // Blind-Index für E-Mail-Suche
    $blindIdx = smu_blind_index($email);

    $stmt = smu_db()->prepare(
        'SELECT id, passwort_hash, aktiv, totp_secret_enc, totp_aktiviert
         FROM users WHERE email_blind_index = :bi'
    );
    $stmt->execute([':bi' => $blindIdx]);
    $zeile = $stmt->fetch();

    // Timing-konstant: immer einen Hash-Vergleich durchführen
    $dummyHash = '$argon2id$v=19$m=65536,t=4,p=1$dummy$dummy';
    $hashZuPruefen = ($zeile !== false) ? (string) $zeile['passwort_hash'] : $dummyHash;
    $passwortKorrekt = password_verify($passwort, $hashZuPruefen);

    if ($zeile === false || !$passwortKorrekt) {
        $fehler = 'E-Mail oder Passwort ungültig.';
    } elseif ((int) $zeile['aktiv'] !== 1) {
        $fehler = 'Dieses Konto ist deaktiviert.';
    } elseif ((int) $zeile['totp_aktiviert'] === 1) {
        // 2FA aktiv — Code prüfen
        if ($totpCode === '') {
            $totpErforderlich = true;
            $fehler = '';
        } else {
            $secret = smu_entschluesseln((string) $zeile['totp_secret_enc'], 'totp_secret_enc');
            if (!totp_code_pruefen($secret, $totpCode)) {
                $totpErforderlich = true;
                $fehler = 'Ungültiger 2-Faktor-Code.';
            } else {
                smu_sitzung_anmelden((int) $zeile['id']);
                header('Location: /konto');
                exit;
            }
        }
    } else {
        smu_sitzung_anmelden((int) $zeile['id']);
        header('Location: /konto');
        exit;
    }
}

require_once __DIR__ . '/includes/layout_kopf.php';
layout_kopf('Anmelden', false);
?>

<div class="auth-karte">
    <div class="auth-logo">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="1.5" width="45" height="45" rx="10" stroke="#66E0FF" stroke-width="1.5" fill="none" opacity="0.35"/>
            <polygon points="24,7 41,24 24,41 7,24" stroke="#66E0FF" stroke-width="1.5" fill="none"/>
            <polygon points="24,14 34,24 24,34 14,24" stroke="#66E0FF" stroke-width="1" fill="rgba(102,224,255,0.08)"/>
        </svg>
        <strong>Shape Miner</strong>
        <span>Account-Anmeldung</span>
    </div>

    <?php if ($fehler !== ''): ?>
    <div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form method="POST" class="formular">
        <?= csrf_feld() ?>

        <?php if (!$totpErforderlich): ?>
        <div class="formular-gruppe">
            <label for="email">E-Mail</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($eingabeEmail, ENT_QUOTES) ?>"
                   autocomplete="email" required autofocus>
        </div>
        <div class="formular-gruppe">
            <label for="passwort">Passwort</label>
            <input type="password" id="passwort" name="passwort"
                   autocomplete="current-password" required>
        </div>
        <?php else: ?>
        <input type="hidden" name="email" value="<?= htmlspecialchars($eingabeEmail, ENT_QUOTES) ?>">
        <input type="hidden" name="passwort" value="">
        <div class="formular-gruppe">
            <label for="totp_code">2-Faktor-Code</label>
            <input type="number" id="totp_code" name="totp_code"
                   placeholder="6-stelliger Code" maxlength="6"
                   autocomplete="one-time-code" autofocus>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primaer">
            <?= $totpErforderlich ? 'Code bestätigen' : 'Anmelden' ?>
        </button>
    </form>

    <div class="auth-fuss">
        Noch kein Konto? <a href="/registrieren">Registrieren</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/layout_fuss.php';
layout_fuss();
