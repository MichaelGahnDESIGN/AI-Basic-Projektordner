<?php

declare(strict_types=1);

/**
 * Aufgabe: 2-Faktor-Auth aktivieren (QR-Code anzeigen) oder deaktivieren.
 *
 * Flow Aktivierung:
 *   1. GET  → neues TOTP-Secret generieren, QR-Code anzeigen
 *   2. POST (aktivieren) → Code prüfen → totp_aktiviert = 1
 *
 * Flow Deaktivierung:
 *   POST (deaktivieren) → Passwort bestätigen → totp_aktiviert = 0, secret löschen
 */

require_once __DIR__ . '/../includes/konfiguration_laden.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sitzung.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/nutzer.php';
require_once __DIR__ . '/../includes/layout_kopf.php';
require_once __DIR__ . '/../includes/layout_fuss.php';
require_once SMU_API_PFAD . '/totp.php';
require_once SMU_API_PFAD . '/crypto.php';
require_once SMU_API_PFAD . '/recovery.php';

smu_sitzung_starten();
smu_einloggen_erforderlich();

$userId = (int) smu_sitzung_nutzer()['id'];
$profil = nutzer_profil_laden($userId);

if ($profil === null) { header('Location: /abmelden'); exit; }

$fehler = '';
$qrUri  = '';
$secret = '';

// Aktuellen 2FA-Status aus DB laden
$row2fa = smu_db()->prepare('SELECT totp_secret_enc, totp_aktiviert FROM users WHERE id = :id');
$row2fa->execute([':id' => $userId]);
$status2fa = $row2fa->fetch();
$ist2faAktiv = (bool) ($status2fa['totp_aktiviert'] ?? false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $aktion = (string) ($_POST['aktion'] ?? '');

    if ($aktion === 'deaktivieren') {
        $passwort = (string) ($_POST['passwort'] ?? '');
        $stmt = smu_db()->prepare('SELECT passwort_hash FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        $z = $stmt->fetch();

        if ($z === false) { header('Location: /abmelden'); exit; }

        if (!password_verify($passwort, (string) $z['passwort_hash'])) {
            $fehler = 'Passwort falsch.';
        } else {
            smu_db()->prepare(
                'UPDATE users SET totp_secret_enc = NULL, totp_aktiviert = 0, aktualisiert_am = NOW()
                 WHERE id = :id'
            )->execute([':id' => $userId]);
            unset($_SESSION['totp_setup_secret']);

            smu_hinweis_setzen('erfolg', '2-Faktor-Authentifizierung deaktiviert.');
            header('Location: /konto/sicherheit');
            exit;
        }

    } elseif ($aktion === 'aktivieren') {
        $code = trim((string) ($_POST['totp_code'] ?? ''));
        // Secret kommt SERVERSEITIG aus der Session, nie aus dem Client-POST.
        $secretRoh = (string) ($_SESSION['totp_setup_secret'] ?? '');

        if ($secretRoh === '') {
            $fehler = 'Einrichtung abgelaufen. Bitte Seite neu laden.';
        } elseif (!totp_code_pruefen($secretRoh, $code)) {
            $fehler = 'Code ungültig oder abgelaufen. Bitte erneut versuchen.';
        } else {
            smu_db()->prepare(
                'UPDATE users SET totp_secret_enc = :enc, totp_aktiviert = 1, aktualisiert_am = NOW()
                 WHERE id = :id'
            )->execute([
                ':enc' => krypto_verschluesseln($secretRoh, 'users.totp_secret_enc'),
                ':id'  => $userId,
            ]);
            unset($_SESSION['totp_setup_secret']);

            // Recovery-Codes erzeugen und EINMALIG zur Anzeige in die Session legen.
            $_SESSION['recovery_codes_neu'] = recovery_codes_erzeugen(smu_db(), $userId);

            smu_hinweis_setzen('erfolg', '2-Faktor-Authentifizierung erfolgreich aktiviert!');
            header('Location: /konto/sicherheit');
            exit;
        }
        // Bei Fehler: vorhandenes Session-Secret weiterverwenden (QR erneut).
        $secret = $secretRoh;
    }
}

// Für Aktivierungs-Formular: Secret serverseitig in der Session halten.
if (!$ist2faAktiv) {
    if ($secret === '') {
        $secret = (string) ($_SESSION['totp_setup_secret'] ?? '');
    }
    if ($secret === '') {
        $secret = totp_secret_erzeugen();
    }
    $_SESSION['totp_setup_secret'] = $secret;
}

if (!$ist2faAktiv) {
    $benutzername = urlencode($profil['benutzername']);
    $qrUri = 'otpauth://totp/Shape%20Miner%3A' . $benutzername
        . '?secret=' . urlencode($secret)
        . '&issuer=Shape%20Miner&algorithm=SHA1&digits=6&period=30';
}

// Frisch erzeugte Recovery-Codes (nur EINMAL anzeigen, dann aus Session löschen).
$recoveryCodesNeu = $_SESSION['recovery_codes_neu'] ?? [];
unset($_SESSION['recovery_codes_neu']);
$recoveryVerbleibend = $ist2faAktiv ? recovery_codes_verbleibend(smu_db(), $userId) : 0;

layout_kopf('2-Faktor-Auth', true, 'sicherheit');
?>

<div class="seitenkopf">
    <h1>2-Faktor-Authentifizierung</h1>
    <p>Schütze dein Konto mit einem Einmal-Code aus einer Authenticator-App.</p>
</div>

<?php if ($fehler !== ''): ?>
<div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
<?php endif; ?>

<?php if (!empty($recoveryCodesNeu)): ?>
<div class="karte" style="border-color: rgba(102,224,255,0.4); margin-bottom: 20px;">
    <h2>Deine Recovery-Codes</h2>
    <p style="color: var(--warnung); margin-bottom: 16px;">
        Bewahre diese Codes sicher auf. Jeder Code funktioniert <strong>einmal</strong>
        und ersetzt deine Authenticator-App, falls du sie verlierst.
        Sie werden <strong>nur jetzt</strong> angezeigt.
    </p>
    <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap: 8px; font-size: 15px;">
        <?php foreach ($recoveryCodesNeu as $rc): ?>
        <code style="letter-spacing:0.1em; color:var(--akzent); background:rgba(102,224,255,0.06);
                     padding:8px 10px; border-radius:6px; text-align:center;">
            <?= htmlspecialchars($rc, ENT_QUOTES) ?>
        </code>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($ist2faAktiv): ?>
<div class="karte">
    <h2>Status: Aktiv</h2>
    <p style="color: var(--erfolg); margin-bottom: 8px;">
        Dein Konto ist durch 2FA geschützt.
    </p>
    <p style="color: var(--text-60); font-size: 13px; margin-bottom: 20px;">
        Verbleibende Recovery-Codes: <strong><?= (int) $recoveryVerbleibend ?></strong>
    </p>
    <form method="POST" class="formular">
        <?= csrf_feld() ?>
        <input type="hidden" name="aktion" value="deaktivieren">
        <div class="formular-gruppe">
            <label for="passwort">Passwort bestätigen</label>
            <input type="password" id="passwort" name="passwort"
                   autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-gefahr">2FA deaktivieren</button>
    </form>
</div>

<?php else: ?>
<div class="karte">
    <h2>2FA einrichten</h2>
    <ol style="color: var(--text-60); margin: 0 0 20px 16px; line-height: 2;">
        <li>Öffne eine Authenticator-App (z. B. Google Authenticator, Authy)</li>
        <li>Scanne den QR-Code oder trage den Schlüssel manuell ein</li>
        <li>Bestätige mit dem angezeigten 6-stelligen Code</li>
    </ol>

    <div style="display:flex; gap: 24px; align-items: flex-start; margin-bottom: 20px;">
        <div class="qr-bereich">
            <!-- QR wird LOKAL im Browser erzeugt (Secret geht nicht an Dritte). -->
            <div id="totp-qr" data-otpauth="<?= htmlspecialchars($qrUri, ENT_QUOTES) ?>"
                 style="width:176px;height:176px;background:#fff;border-radius:8px;padding:8px"></div>
        </div>
        <div>
            <p style="font-size: 12px; color: var(--text-45); margin-bottom: 8px;">
                MANUELLER SCHLÜSSEL
            </p>
            <code style="font-size: 13px; letter-spacing: 0.1em; color: var(--akzent); word-break: break-all;">
                <?= htmlspecialchars($secret, ENT_QUOTES) ?>
            </code>
        </div>
    </div>

    <form method="POST" class="formular">
        <?= csrf_feld() ?>
        <input type="hidden" name="aktion" value="aktivieren">
        <div class="formular-gruppe">
            <label for="totp_code">6-stelliger Code aus der App</label>
            <input type="number" id="totp_code" name="totp_code"
                   placeholder="000000" maxlength="6" autocomplete="one-time-code" required>
        </div>
        <button type="submit" class="btn btn-primaer">2FA aktivieren</button>
    </form>
</div>
<script src="/assets/qrcode.min.js"></script>
<script src="/assets/totp-qr.js"></script>
<?php endif; ?>

<?php layout_fuss(); ?>
