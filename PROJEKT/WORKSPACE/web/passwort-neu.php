<?php

declare(strict_types=1);

/**
 * Aufgabe: Neues Passwort über einen gültigen Reset-Token setzen.
 *
 * Token kommt aus der E-Mail (Query ?token=…). Gespeichert ist nur sein
 * SHA-256-Hash. Token ist einmalig (benutzt_am) und kurzlebig (ablauf).
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';
require_once __DIR__ . '/includes/csrf.php';

smu_sitzung_starten();

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$fehler = '';
$feldFehler = '';

/** Lädt einen gültigen (nicht abgelaufenen, unbenutzten) Reset-Datensatz. */
function reset_datensatz_laden(string $token): array|false
{
    if (!preg_match('/^[0-9a-f]{64}$/', $token)) {
        return false;
    }
    $stmt = smu_db()->prepare(
        'SELECT token_hash, user_id FROM passwort_reset
         WHERE token_hash = :th AND benutzt_am IS NULL AND ablauf > NOW()'
    );
    $stmt->execute([':th' => hash('sha256', $token)]);
    return $stmt->fetch();
}

$datensatz = reset_datensatz_laden($token);

if ($datensatz === false) {
    $fehler = 'Dieser Link ist ungültig oder abgelaufen. Bitte fordere einen neuen an.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();
    $neu  = (string) ($_POST['passwort'] ?? '');
    $neu2 = (string) ($_POST['passwort2'] ?? '');

    if (mb_strlen($neu) < 8 || mb_strlen($neu) > 200) {
        $feldFehler = 'Mindestens 8, höchstens 200 Zeichen.';
    } elseif ($neu !== $neu2) {
        $feldFehler = 'Die Passwörter stimmen nicht überein.';
    } else {
        $cfg   = smu_konfiguration();
        $argon = $cfg['argon2_optionen'] ?? [];
        $hash  = password_hash($neu, PASSWORD_ARGON2ID, [
            'memory_cost' => (int) ($argon['memory_cost'] ?? 65536),
            'time_cost'   => (int) ($argon['time_cost'] ?? 4),
            'threads'     => (int) ($argon['threads'] ?? 1),
        ]);

        $db = smu_db();
        $db->beginTransaction();
        try {
            $db->prepare('UPDATE users SET passwort_hash = :h, aktualisiert_am = NOW() WHERE id = :id')
               ->execute([':h' => $hash, ':id' => (int) $datensatz['user_id']]);
            // Diesen Token als benutzt markieren UND alle anderen offenen Tokens
            // des Nutzers entwerten.
            $db->prepare('DELETE FROM passwort_reset WHERE user_id = :id')
               ->execute([':id' => (int) $datensatz['user_id']]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            $fehler = 'Zurücksetzen fehlgeschlagen. Bitte erneut versuchen.';
        }

        if ($fehler === '') {
            smu_hinweis_setzen('erfolg', 'Passwort geändert. Bitte melde dich neu an.');
            header('Location: /anmelden');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/layout_kopf.php';
layout_kopf('Neues Passwort', false);
?>
<div class="auth-karte">
    <div class="auth-logo">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="1.5" width="45" height="45" rx="10" stroke="#66E0FF" stroke-width="1.5" fill="none" opacity="0.35"/>
            <polygon points="24,7 41,24 24,41 7,24" stroke="#66E0FF" stroke-width="1.5" fill="none"/>
        </svg>
        <strong>Shape Miner</strong>
        <span>Neues Passwort</span>
    </div>

    <?php if ($fehler !== ''): ?>
    <div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
    <div class="auth-fuss"><a href="/passwort-vergessen">Neuen Link anfordern</a></div>
    <?php else: ?>
    <form method="POST" class="formular">
        <?= csrf_feld() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
        <div class="formular-gruppe">
            <label for="passwort">Neues Passwort</label>
            <input type="password" id="passwort" name="passwort"
                   autocomplete="new-password" minlength="8" required>
        </div>
        <div class="formular-gruppe">
            <label for="passwort2">Passwort wiederholen</label>
            <input type="password" id="passwort2" name="passwort2"
                   autocomplete="new-password" minlength="8" required>
            <?php if ($feldFehler !== ''): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler, ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primaer">Passwort speichern</button>
    </form>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/includes/layout_fuss.php';
layout_fuss();
