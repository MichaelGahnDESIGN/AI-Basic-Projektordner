<?php

declare(strict_types=1);

/**
 * Aufgabe: Account dauerhaft löschen (Passwort + Bestätigungstext erforderlich).
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
$fehler = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $passwort     = (string) ($_POST['passwort'] ?? '');
    $bestaetigung = trim((string) ($_POST['bestaetigung'] ?? ''));

    $stmt = smu_db()->prepare('SELECT passwort_hash FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $zeile = $stmt->fetch();

    if ($zeile === false) { header('Location: /abmelden'); exit; }

    if (!password_verify($passwort, (string) $zeile['passwort_hash'])) {
        $fehler = 'Passwort ist falsch.';
    } elseif (strtolower($bestaetigung) !== 'account löschen') {
        $fehler = 'Bitte tippe genau „Account löschen" ein.';
    } else {
        // Account + alle Daten löschen (CASCADE via FK: einwilligungen, refresh_tokens)
        smu_db()->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $userId]);
        smu_sitzung_abmelden();

        smu_hinweis_setzen('erfolg', 'Dein Account wurde gelöscht. Auf Wiedersehen!');
        header('Location: /anmelden');
        exit;
    }
}

layout_kopf('Account löschen', true, 'loeschen');
?>

<div class="seitenkopf">
    <h1>Account löschen</h1>
    <p>Diese Aktion ist <strong>unwiderruflich</strong> — alle Daten werden dauerhaft entfernt.</p>
</div>

<?php if ($fehler !== ''): ?>
<div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
<?php endif; ?>

<div class="karte">
    <h2>Konto dauerhaft löschen</h2>
    <p style="color: var(--fehler); margin-bottom: 20px;">
        Alle Spielstände, Einwilligungen und Kontodaten werden gelöscht.
    </p>
    <form method="POST" class="formular">
        <?= csrf_feld() ?>

        <div class="formular-gruppe">
            <label for="passwort">Passwort bestätigen</label>
            <input type="password" id="passwort" name="passwort"
                   autocomplete="current-password" required>
        </div>

        <div class="formular-gruppe">
            <label for="bestaetigung">Tippe „Account löschen" zur Bestätigung</label>
            <input type="text" id="bestaetigung" name="bestaetigung"
                   placeholder="Account löschen" required>
        </div>

        <button type="submit" class="btn btn-gefahr">Account unwiderruflich löschen</button>
    </form>
</div>

<?php layout_fuss(); ?>
