<?php

declare(strict_types=1);

/**
 * Aufgabe: Passwort ändern (altes Passwort bestätigen + neues setzen).
 */

require_once __DIR__ . '/../includes/konfiguration_laden.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/sitzung.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/nutzer.php';
require_once __DIR__ . '/../includes/layout_kopf.php';
require_once __DIR__ . '/../includes/layout_fuss.php';

smu_sitzung_starten();
smu_einloggen_erforderlich();

$userId = (int) smu_sitzung_nutzer()['id'];
$feldFehler = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $altPw  = (string) ($_POST['passwort_alt'] ?? '');
    $neuPw  = (string) ($_POST['passwort_neu'] ?? '');
    $neuPw2 = (string) ($_POST['passwort_neu2'] ?? '');

    $stmt = smu_db()->prepare('SELECT passwort_hash FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $zeile = $stmt->fetch();

    if (!password_verify($altPw, (string) $zeile['passwort_hash'])) {
        $feldFehler['passwort_alt'] = 'Aktuelles Passwort ist falsch.';
    }
    if (mb_strlen($neuPw) < 8) {
        $feldFehler['passwort_neu'] = 'Mindestens 8 Zeichen.';
    }
    if ($neuPw !== $neuPw2) {
        $feldFehler['passwort_neu2'] = 'Passwörter stimmen nicht überein.';
    }

    if (empty($feldFehler)) {
        $argon = smu_konfiguration()['argon2_optionen'] ?? [];
        $hash  = password_hash($neuPw, PASSWORD_ARGON2ID, [
            'memory_cost' => (int) ($argon['memory_cost'] ?? 65536),
            'time_cost'   => (int) ($argon['time_cost'] ?? 4),
            'threads'     => (int) ($argon['threads'] ?? 1),
        ]);

        smu_db()->prepare(
            'UPDATE users SET passwort_hash = :h, aktualisiert_am = NOW() WHERE id = :id'
        )->execute([':h' => $hash, ':id' => $userId]);

        smu_hinweis_setzen('erfolg', 'Passwort erfolgreich geändert.');
        header('Location: /konto/passwort');
        exit;
    }
}

layout_kopf('Passwort ändern', true, 'passwort');
?>

<div class="seitenkopf">
    <h1>Passwort ändern</h1>
    <p>Bitte bestätige zuerst dein aktuelles Passwort.</p>
</div>

<div class="karte">
    <form method="POST" class="formular">
        <?= csrf_feld() ?>

        <div class="formular-gruppe">
            <label for="passwort_alt">Aktuelles Passwort</label>
            <input type="password" id="passwort_alt" name="passwort_alt"
                   autocomplete="current-password" required>
            <?php if (!empty($feldFehler['passwort_alt'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['passwort_alt'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="passwort_neu">Neues Passwort</label>
            <input type="password" id="passwort_neu" name="passwort_neu"
                   autocomplete="new-password" required minlength="8">
            <?php if (!empty($feldFehler['passwort_neu'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['passwort_neu'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="passwort_neu2">Neues Passwort bestätigen</label>
            <input type="password" id="passwort_neu2" name="passwort_neu2"
                   autocomplete="new-password" required>
            <?php if (!empty($feldFehler['passwort_neu2'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['passwort_neu2'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primaer">Passwort ändern</button>
    </form>
</div>

<?php layout_fuss(); ?>
