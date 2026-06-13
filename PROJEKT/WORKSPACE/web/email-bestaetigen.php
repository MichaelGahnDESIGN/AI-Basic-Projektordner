<?php

declare(strict_types=1);

/**
 * Aufgabe: Eine angeforderte E-Mail-Änderung über den Token aus der
 * Bestätigungs-Mail wirksam machen (Double-Opt-In, Abschluss).
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';

smu_sitzung_starten();

$token  = (string) ($_GET['token'] ?? '');
$fehler = '';
$erfolg = false;

if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
    $fehler = 'Ungültiger oder abgelaufener Bestätigungslink.';
} else {
    $db = smu_db();
    $stmt = $db->prepare(
        'SELECT user_id, neue_email_enc, neue_email_bidx FROM email_aenderung
         WHERE token_hash = :th AND ablauf > NOW()'
    );
    $stmt->execute([':th' => hash('sha256', $token)]);
    $ds = $stmt->fetch();

    if ($ds === false) {
        $fehler = 'Dieser Bestätigungslink ist ungültig oder abgelaufen.';
    } else {
        // Erneut prüfen, ob die Adresse zwischenzeitlich anderweitig vergeben wurde.
        $check = $db->prepare('SELECT id FROM users WHERE email_blind_index = :bi AND id != :id');
        $check->execute([':bi' => $ds['neue_email_bidx'], ':id' => (int) $ds['user_id']]);

        if ($check->fetch() !== false) {
            $fehler = 'Diese E-Mail-Adresse ist inzwischen vergeben. Bitte fordere die Änderung erneut an.';
            $db->prepare('DELETE FROM email_aenderung WHERE user_id = :id')->execute([':id' => (int) $ds['user_id']]);
        } else {
            $db->beginTransaction();
            try {
                $db->prepare(
                    'UPDATE users SET email_enc = :enc, email_blind_index = :bi, aktualisiert_am = NOW()
                     WHERE id = :id'
                )->execute([
                    ':enc' => (string) $ds['neue_email_enc'],
                    ':bi'  => (string) $ds['neue_email_bidx'],
                    ':id'  => (int) $ds['user_id'],
                ]);
                $db->prepare('DELETE FROM email_aenderung WHERE user_id = :id')->execute([':id' => (int) $ds['user_id']]);
                $db->commit();
                $erfolg = true;
            } catch (Throwable $e) {
                $db->rollBack();
                $fehler = 'Bestätigung fehlgeschlagen. Bitte erneut versuchen.';
            }
        }
    }
}

require_once __DIR__ . '/includes/layout_kopf.php';
layout_kopf('E-Mail bestätigen', false);
?>
<div class="auth-karte">
    <div class="auth-logo">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="1.5" width="45" height="45" rx="10" stroke="#66E0FF" stroke-width="1.5" fill="none" opacity="0.35"/>
            <polygon points="24,7 41,24 24,41 7,24" stroke="#66E0FF" stroke-width="1.5" fill="none"/>
        </svg>
        <strong>Shape Miner</strong>
        <span>E-Mail bestätigen</span>
    </div>

    <?php if ($erfolg): ?>
    <div class="hinweis hinweis-erfolg">
        Deine neue E-Mail-Adresse ist jetzt aktiv. Bitte melde dich künftig damit an.
    </div>
    <div class="auth-fuss"><a href="/anmelden">Zur Anmeldung</a></div>
    <?php else: ?>
    <div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
    <div class="auth-fuss"><a href="/konto/email">Erneut anfordern</a> · <a href="/anmelden">Anmeldung</a></div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/includes/layout_fuss.php';
layout_fuss();
