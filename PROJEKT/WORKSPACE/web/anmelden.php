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
require_once SMU_API_PFAD . '/totp.php';
require_once SMU_API_PFAD . '/rate_limit.php';
require_once SMU_API_PFAD . '/recovery.php';

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

    // Brute-Force-Schutz: pro IP zählen, nach Grenze temporär sperren.
    $ratePdo = smu_db();
    $rateKey = rate_limit_schluessel('web-login', (string) ($_SERVER['REMOTE_ADDR'] ?? ''));

    // Offenen 2FA-Schritt aus der Session laden (max. 5 Minuten gültig).
    $pending = $_SESSION['pending_2fa'] ?? null;
    if (is_array($pending) && (time() - (int) ($pending['zeit'] ?? 0)) > 300) {
        $pending = null;
        unset($_SESSION['pending_2fa']);
    }

    if (rate_limit_gesperrt($ratePdo, $rateKey) > 0) {
        $wartenSek = rate_limit_gesperrt($ratePdo, $rateKey);
        $fehler = 'Zu viele Anmeldeversuche. Bitte in etwa '
            . (int) ceil($wartenSek / 60) . ' Minuten erneut versuchen.';
        $totpErforderlich = $pending !== null;

    } elseif ($pending !== null) {
        // --- Zweiter Schritt: TOTP- bzw. Recovery-Code prüfen --------------------
        $userId = (int) $pending['user_id'];
        $stmt = smu_db()->prepare(
            'SELECT id, aktiv, totp_secret_enc, totp_aktiviert FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $userId]);
        $zeile = $stmt->fetch();

        if ($zeile === false || (int) $zeile['aktiv'] !== 1 || (int) $zeile['totp_aktiviert'] !== 1) {
            unset($_SESSION['pending_2fa']);
            $fehler = 'Anmeldung abgelaufen. Bitte erneut anmelden.';
        } elseif ($totpCode === '') {
            $totpErforderlich = true;
        } else {
            $secret = smu_entschluesseln((string) $zeile['totp_secret_enc'], 'users.totp_secret_enc');
            $totpOk = totp_code_pruefen($secret, $totpCode);
            $recoveryOk = !$totpOk && recovery_code_einloesen($ratePdo, $userId, $totpCode);

            if (!$totpOk && !$recoveryOk) {
                rate_limit_fehlschlag($ratePdo, $rateKey);
                $totpErforderlich = true;
                $fehler = 'Ungültiger 2-Faktor- oder Recovery-Code.';
            } else {
                rate_limit_erfolg($ratePdo, $rateKey);
                unset($_SESSION['pending_2fa']);
                smu_sitzung_anmelden($userId);
                if ($recoveryOk) {
                    smu_hinweis_setzen('warnung',
                        'Du hast dich mit einem Recovery-Code angemeldet. Verbleibend: '
                        . recovery_codes_verbleibend($ratePdo, $userId) . '.');
                }
                header('Location: /konto');
                exit;
            }
        }

    } else {
        // --- Erster Schritt: E-Mail + Passwort -----------------------------------
        $stmt = smu_db()->prepare(
            'SELECT id, passwort_hash, aktiv, totp_aktiviert
             FROM users WHERE email_blind_index = :bi'
        );
        $stmt->execute([':bi' => smu_blind_index($email)]);
        $zeile = $stmt->fetch();

        // Timing-konstant: immer einen ECHTEN Argon2id-Vergleich (gültiger Dummy).
        $dummyHash = '$argon2id$v=19$m=65536,t=4,p=1$MDEyMzQ1Njc4OWFiY2RlZg$MDEyMzQ1Njc4OWFiY2RlZjAxMjM0NTY3ODlhYmNkZWY';
        $hashZuPruefen = ($zeile !== false) ? (string) $zeile['passwort_hash'] : $dummyHash;

        if ($zeile === false || !password_verify($passwort, $hashZuPruefen)) {
            rate_limit_fehlschlag($ratePdo, $rateKey);
            $fehler = 'E-Mail oder Passwort ungültig.';
        } elseif ((int) $zeile['aktiv'] !== 1) {
            $fehler = 'Dieses Konto ist deaktiviert.';
        } elseif ((int) $zeile['totp_aktiviert'] === 1) {
            // Passwort ok → zweiten Faktor verlangen (Passwort NICHT erneut nötig).
            $_SESSION['pending_2fa'] = ['user_id' => (int) $zeile['id'], 'zeit' => time()];
            $totpErforderlich = true;
        } else {
            rate_limit_erfolg($ratePdo, $rateKey);
            smu_sitzung_anmelden((int) $zeile['id']);
            header('Location: /konto');
            exit;
        }
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
        <div class="formular-gruppe">
            <label for="totp_code">2-Faktor-Code</label>
            <input type="text" id="totp_code" name="totp_code"
                   placeholder="6-stelliger Code oder Recovery-Code" inputmode="text"
                   autocomplete="one-time-code" autofocus required>
            <span class="feld-hinweis" style="font-size:12px;color:var(--text-45);">
                Authenticator-App verloren? Gib einen deiner Recovery-Codes ein.
            </span>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primaer">
            <?= $totpErforderlich ? 'Code bestätigen' : 'Anmelden' ?>
        </button>
    </form>

    <div class="auth-fuss">
        Noch kein Konto? <a href="/registrieren">Registrieren</a>
        <br><a href="/passwort-vergessen">Passwort vergessen?</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/layout_fuss.php';
layout_fuss();
