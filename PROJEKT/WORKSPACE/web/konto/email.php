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
require_once SMU_API_PFAD . '/mail.php';
require_once __DIR__ . '/../includes/layout_kopf.php';
require_once __DIR__ . '/../includes/layout_fuss.php';

smu_sitzung_starten();
smu_einloggen_erforderlich();

$userId = (int) smu_sitzung_nutzer()['id'];
$profil = nutzer_profil_laden($userId);

if ($profil === null) { header('Location: /abmelden'); exit; }

$feldFehler = [];
$bestaetigungGesendet = false;

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
        // Double-Opt-In: Änderung NICHT sofort übernehmen, sondern Token an die
        // neue Adresse senden. Erst nach Bestätigung wird sie wirksam.
        $token = bin2hex(random_bytes(32));
        $db = smu_db();
        // Alte offene Änderungswünsche dieses Nutzers verwerfen.
        $db->prepare('DELETE FROM email_aenderung WHERE user_id = :id')->execute([':id' => $userId]);
        $db->prepare(
            'INSERT INTO email_aenderung (token_hash, user_id, neue_email_enc, neue_email_bidx, ablauf)
             VALUES (:th, :uid, :enc, :bidx, (NOW() + INTERVAL 1 HOUR))'
        )->execute([
            ':th'   => hash('sha256', $token),
            ':uid'  => $userId,
            ':enc'  => smu_verschluesseln($neueEmail, 'email_enc'),
            ':bidx' => $neuerBlindIdx,
        ]);

        // Bestätigungslink an die NEUE Adresse.
        smu_mail_senden(
            $neueEmail,
            'Shape Miner — E-Mail-Adresse bestätigen',
            "Hallo,\n\nbitte bestätige innerhalb der nächsten Stunde, dass diese "
            . "Adresse künftig für dein Shape-Miner-Konto verwendet werden soll:\n\n"
            . 'https://login.shapeminer.com/email-bestaetigen?token=' . $token . "\n\n"
            . "Wenn du das nicht warst, ignoriere diese E-Mail.\n\nDein Shape-Miner-Team"
        );
        // Sicherheits-Benachrichtigung an die ALTE Adresse.
        smu_mail_senden(
            $profil['email'],
            'Shape Miner — Änderung deiner E-Mail-Adresse angefordert',
            "Hallo,\n\nfür dein Konto wurde eine Änderung der E-Mail-Adresse "
            . "angefordert. Sie wird erst nach Bestätigung über die neue Adresse "
            . "wirksam.\n\nWenn du das nicht warst, ändere bitte umgehend dein "
            . "Passwort.\n\nDein Shape-Miner-Team"
        );

        $bestaetigungGesendet = true;
    }
}

layout_kopf('E-Mail ändern', true, 'email');
?>

<div class="seitenkopf">
    <h1>E-Mail ändern</h1>
    <p>Aktuelle Adresse: <strong><?= htmlspecialchars($profil['email'], ENT_QUOTES) ?></strong></p>
</div>

<?php if ($bestaetigungGesendet): ?>
<div class="hinweis hinweis-erfolg">
    Wir haben einen Bestätigungslink an die neue Adresse gesendet. Die Änderung
    wird erst wirksam, nachdem du den Link dort bestätigt hast (gültig 1 Stunde).
</div>
<?php endif; ?>

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
