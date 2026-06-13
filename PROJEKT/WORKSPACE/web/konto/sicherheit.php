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

        if (!password_verify($passwort, (string) $z['passwort_hash'])) {
            $fehler = 'Passwort falsch.';
        } else {
            smu_db()->prepare(
                'UPDATE users SET totp_secret_enc = NULL, totp_aktiviert = 0, aktualisiert_am = NOW()
                 WHERE id = :id'
            )->execute([':id' => $userId]);

            smu_hinweis_setzen('erfolg', '2-Faktor-Authentifizierung deaktiviert.');
            header('Location: /konto/sicherheit');
            exit;
        }

    } elseif ($aktion === 'aktivieren') {
        $code        = trim((string) ($_POST['totp_code'] ?? ''));
        $secretRoh   = (string) ($_POST['secret_b32'] ?? '');

        if (!totp_code_pruefen($secretRoh, $code)) {
            $fehler = 'Code ungültig oder abgelaufen. Bitte erneut versuchen.';
        } else {
            smu_db()->prepare(
                'UPDATE users SET totp_secret_enc = :enc, totp_aktiviert = 1, aktualisiert_am = NOW()
                 WHERE id = :id'
            )->execute([
                ':enc' => krypto_verschluesseln($secretRoh, 'totp_secret_enc'),
                ':id'  => $userId,
            ]);

            smu_hinweis_setzen('erfolg', '2-Faktor-Authentifizierung erfolgreich aktiviert!');
            header('Location: /konto/sicherheit');
            exit;
        }
        // Bei Fehler: secret aus POST wiederverwenden um QR-Code erneut anzuzeigen
        $secret = $secretRoh;
    }
}

// Für Aktivierungs-Formular: Secret generieren (aus POST oder frisch)
if (!$ist2faAktiv && $secret === '') {
    $secret = totp_secret_erzeugen();
}

if (!$ist2faAktiv) {
    $benutzername = urlencode($profil['benutzername']);
    $qrUri = 'otpauth://totp/Shape%20Miner%3A' . $benutzername
        . '?secret=' . urlencode($secret)
        . '&issuer=Shape%20Miner&algorithm=SHA1&digits=6&period=30';
}

layout_kopf('2-Faktor-Auth', true, 'sicherheit');
?>

<div class="seitenkopf">
    <h1>2-Faktor-Authentifizierung</h1>
    <p>Schütze dein Konto mit einem Einmal-Code aus einer Authenticator-App.</p>
</div>

<?php if ($fehler !== ''): ?>
<div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
<?php endif; ?>

<?php if ($ist2faAktiv): ?>
<div class="karte">
    <h2>Status: Aktiv</h2>
    <p style="color: var(--erfolg); margin-bottom: 20px;">
        Dein Konto ist durch 2FA geschützt.
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
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode($qrUri) ?>"
                 alt="QR-Code für Authenticator-App" width="160" height="160">
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
        <input type="hidden" name="secret_b32" value="<?= htmlspecialchars($secret, ENT_QUOTES) ?>">
        <div class="formular-gruppe">
            <label for="totp_code">6-stelliger Code aus der App</label>
            <input type="number" id="totp_code" name="totp_code"
                   placeholder="000000" maxlength="6" autocomplete="one-time-code" required>
        </div>
        <button type="submit" class="btn btn-primaer">2FA aktivieren</button>
    </form>
</div>
<?php endif; ?>

<?php layout_fuss(); ?>
