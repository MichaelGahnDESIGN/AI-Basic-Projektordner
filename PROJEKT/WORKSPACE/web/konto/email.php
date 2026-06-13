<?php

declare(strict_types=1);

/**
 * Aufgabe: E-Mail-Adresse ändern (Passwort-Bestätigung + Blind-Index-Update).
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

    $neueEmail = mb_strtolower(trim((string) ($_POST['email_neu'] ?? '')));
    $passwort  = (string) ($_POST['passwort'] ?? '');

    // Passwort prüfen
    $stmt = smu_db()->prepare('SELECT passwort_hash FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $zeile = $stmt->fetch();

    if ($zeile === false) { header('Location: /abmelden'); exit; }

    if (!password_verify($passwort, (string) $zeile['passwort_hash'])) {
        $feldFehler['passwort'] = 'Passwort ist falsch.';
    }
    if (!filter_var($neueEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($neueEmail) > 254) {
        $feldFehler['email_neu'] = 'Keine gültige E-Mail-Adresse.';
    }

    if (empty($feldFehler)) {
        $neuerBlindIdx = smu_blind_index($neueEmail);

        // Prüfen ob neue E-Mail bereits vergeben
        $check = smu_db()->prepare('SELECT id FROM users WHERE email_blind_index = :bi AND id != :id');
        $check->execute([':bi' => $neuerBlindIdx, ':id' => $userId]);
        if ($check->fetch() !== false) {
            $feldFehler['email_neu'] = 'Diese E-Mail-Adresse ist bereits vergeben.';
        }
    }

    if (empty($feldFehler)) {
        smu_db()->prepare(
            'UPDATE users SET email_enc = :enc, email_blind_index = :bi, aktualisiert_am = NOW()
             WHERE id = :id'
        )->execute([
            ':enc' => smu_verschluesseln($neueEmail, 'email_enc'),
            ':bi'  => $neuerBlindIdx,
            ':id'  => $userId,
        ]);

        smu_hinweis_setzen('erfolg', 'E-Mail-Adresse erfolgreich geändert.');
        header('Location: /konto/email');
        exit;
    }
}

layout_kopf('E-Mail ändern', true, 'email');
?>

<div class="seitenkopf">
    <h1>E-Mail ändern</h1>
    <p>Aktuelle Adresse: <strong><?= htmlspecialchars($profil['email'], ENT_QUOTES) ?></strong></p>
</div>

<div class="karte">
    <form method="POST" class="formular">
        <?= csrf_feld() ?>

        <div class="formular-gruppe">
            <label for="email_neu">Neue E-Mail-Adresse</label>
            <input type="email" id="email_neu" name="email_neu"
                   autocomplete="email" required>
            <?php if (!empty($feldFehler['email_neu'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['email_neu'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="passwort">Passwort bestätigen</label>
            <input type="password" id="passwort" name="passwort"
                   autocomplete="current-password" required>
            <?php if (!empty($feldFehler['passwort'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['passwort'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primaer">E-Mail ändern</button>
    </form>
</div>

<?php layout_fuss(); ?>
