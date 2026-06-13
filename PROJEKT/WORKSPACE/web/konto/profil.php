<?php

declare(strict_types=1);

/**
 * Aufgabe: Vorname, Nachname, Sprache ändern.
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
$profil = nutzer_profil_laden($userId);

if ($profil === null) { header('Location: /abmelden'); exit; }

$feldFehler = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $vorname = trim((string) ($_POST['vorname'] ?? ''));
    $nachname = trim((string) ($_POST['nachname'] ?? ''));
    $sprache  = (string) ($_POST['sprache'] ?? 'de');

    if (mb_strlen($vorname) < 1 || mb_strlen($vorname) > 100) {
        $feldFehler['vorname'] = 'Vorname muss 1–100 Zeichen lang sein.';
    }
    if (mb_strlen($nachname) < 1 || mb_strlen($nachname) > 100) {
        $feldFehler['nachname'] = 'Nachname muss 1–100 Zeichen lang sein.';
    }
    if (!in_array($sprache, ['de', 'en'], true)) {
        $sprache = 'de';
    }

    if (empty($feldFehler)) {
        smu_db()->prepare(
            'UPDATE users SET vorname_enc = :v, nachname_enc = :n, sprache = :s, aktualisiert_am = NOW()
             WHERE id = :id'
        )->execute([
            ':v'  => smu_verschluesseln($vorname, 'vorname_enc'),
            ':n'  => smu_verschluesseln($nachname, 'nachname_enc'),
            ':s'  => $sprache,
            ':id' => $userId,
        ]);

        smu_hinweis_setzen('erfolg', 'Profil gespeichert.');
        header('Location: /konto/profil');
        exit;
    }

    $profil['vorname']  = $vorname;
    $profil['nachname'] = $nachname;
    $profil['sprache']  = $sprache;
}

layout_kopf('Profil bearbeiten', true, 'profil');
?>

<div class="seitenkopf">
    <h1>Profil bearbeiten</h1>
    <p>Name und Sprache deines Kontos anpassen.</p>
</div>

<div class="karte">
    <form method="POST" class="formular">
        <?= csrf_feld() ?>

        <div class="formular-gruppe">
            <label for="vorname">Vorname</label>
            <input type="text" id="vorname" name="vorname"
                   value="<?= htmlspecialchars($profil['vorname'], ENT_QUOTES) ?>" required>
            <?php if (!empty($feldFehler['vorname'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['vorname'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="nachname">Nachname</label>
            <input type="text" id="nachname" name="nachname"
                   value="<?= htmlspecialchars($profil['nachname'], ENT_QUOTES) ?>" required>
            <?php if (!empty($feldFehler['nachname'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['nachname'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="sprache">Sprache</label>
            <select id="sprache" name="sprache">
                <option value="de" <?= $profil['sprache'] === 'de' ? 'selected' : '' ?>>Deutsch</option>
                <option value="en" <?= $profil['sprache'] === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primaer">Speichern</button>
    </form>
</div>

<?php layout_fuss(); ?>
