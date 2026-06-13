<?php

declare(strict_types=1);

/**
 * Aufgabe: Registrierungsseite — neues Shape-Miner-Konto anlegen.
 * Identische Felder wie die Flutter-App (Kompatibilität mit API-Schema).
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/nutzer.php';
require_once SMU_API_PFAD . '/validierung.php';
require_once __DIR__ . '/includes/layout_kopf.php';
require_once __DIR__ . '/includes/layout_fuss.php';

smu_sitzung_starten();

if (smu_sitzung_nutzer() !== null) {
    header('Location: /konto');
    exit;
}

$feldFehler = [];
$eingaben   = ['vorname' => '', 'nachname' => '', 'email' => '',
               'benutzername' => '', 'sprache' => 'de'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $vorname      = trim((string) ($_POST['vorname'] ?? ''));
    $nachname     = trim((string) ($_POST['nachname'] ?? ''));
    $email        = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
    $benutzername = trim((string) ($_POST['benutzername'] ?? ''));
    $passwort     = (string) ($_POST['passwort'] ?? '');
    $sprache      = (string) ($_POST['sprache'] ?? 'de');
    $agb          = isset($_POST['agb']);
    $datenschutz  = isset($_POST['datenschutz']);

    $eingaben = compact('vorname', 'nachname', 'email', 'benutzername', 'sprache');

    // Validierung — dieselben Regeln wie die API (validierung.php),
    // u. a. Ablehnung von Steuerzeichen in Namen (Konsistenz Web ↔ API).
    if (validierung_text($vorname, 1, 100) === null) {
        $feldFehler['vorname'] = '1–100 Zeichen, keine Steuerzeichen.';
    }
    if (validierung_text($nachname, 1, 100) === null) {
        $feldFehler['nachname'] = '1–100 Zeichen, keine Steuerzeichen.';
    }
    if (validierung_email($email) === null) {
        $feldFehler['email'] = 'Keine gültige E-Mail-Adresse.';
    }
    if (validierung_benutzername($benutzername) === null) {
        $feldFehler['benutzername'] = '3–30 Zeichen, nur A–Z, 0–9 und _.';
    }
    if (validierung_passwort($passwort) === null) {
        $feldFehler['passwort'] = 'Mindestens 8 Zeichen (höchstens 200).';
    }
    if (!$agb) {
        $feldFehler['agb'] = 'Bitte AGB bestätigen.';
    }
    if (!$datenschutz) {
        $feldFehler['datenschutz'] = 'Bitte Datenschutz bestätigen.';
    }
    if (!in_array($sprache, ['de', 'en'], true)) {
        $sprache = 'de';
    }

    if (empty($feldFehler)) {
        // Eindeutigkeit prüfen
        $blindIdx = smu_blind_index($email);
        $check = smu_db()->prepare(
            'SELECT email_blind_index, benutzername FROM users
             WHERE email_blind_index = :bi OR benutzername = :bn'
        );
        $check->execute([':bi' => $blindIdx, ':bn' => $benutzername]);
        $duplikat = $check->fetch();

        if ($duplikat !== false) {
            if ($duplikat['email_blind_index'] === $blindIdx) {
                $feldFehler['email'] = 'Diese E-Mail ist bereits registriert.';
            }
            if ($duplikat['benutzername'] === $benutzername) {
                $feldFehler['benutzername'] = 'Benutzername bereits vergeben.';
            }
        }
    }

    if (empty($feldFehler)) {
        $cfg    = smu_konfiguration();
        $argon  = $cfg['argon2_optionen'] ?? [];
        $hash   = password_hash($passwort, PASSWORD_ARGON2ID, [
            'memory_cost' => (int) ($argon['memory_cost'] ?? 65536),
            'time_cost'   => (int) ($argon['time_cost'] ?? 4),
            'threads'     => (int) ($argon['threads'] ?? 1),
        ]);
        $ip     = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ipHash = $ip !== '' ? hash_hmac('sha256', $ip, konfiguration_schluessel_web('ip_hash')) : null;

        $db = smu_db();
        $db->beginTransaction();

        try {
            $db->prepare(
                'INSERT INTO users
                 (email_blind_index, email_enc, vorname_enc, nachname_enc,
                  benutzername, sprache, passwort_hash, rolle)
                 VALUES (:bi, :em, :vn, :nn, :bn, :sp, :ph, :ro)'
            )->execute([
                ':bi' => $blindIdx,
                ':em' => smu_verschluesseln($email, 'email_enc'),
                ':vn' => smu_verschluesseln($vorname, 'vorname_enc'),
                ':nn' => smu_verschluesseln($nachname, 'nachname_enc'),
                ':bn' => $benutzername,
                ':sp' => $sprache,
                ':ph' => $hash,
                ':ro' => 'spieler',
            ]);

            $userId = (int) $db->lastInsertId();

            $einw = $db->prepare(
                'INSERT INTO einwilligungen (user_id, typ, version, ip_hash)
                 VALUES (:uid, :typ, :ver, :ip)'
            );
            foreach (['agb', 'datenschutz'] as $typ) {
                $einw->execute([':uid' => $userId, ':typ' => $typ, ':ver' => '1.0', ':ip' => $ipHash]);
            }

            $db->commit();

            smu_sitzung_anmelden($userId);
            smu_hinweis_setzen('erfolg', 'Willkommen bei Shape Miner!');
            header('Location: /konto');
            exit;
        } catch (Throwable $e) {
            $db->rollBack();
            $feldFehler['allgemein'] = 'Registrierung fehlgeschlagen. Bitte erneut versuchen.';
        }
    }
}

/** Hilfsfunction: Schlüssel aus Config für IP-Hash. */
function konfiguration_schluessel_web(string $name): string
{
    $roh = base64_decode((string) (smu_konfiguration()['schluessel'][$name] ?? ''), true);
    if ($roh === false || strlen($roh) !== 32) {
        throw new RuntimeException('Schlüssel "' . $name . '" fehlt oder ungültig.');
    }
    return $roh;
}

layout_kopf('Registrieren', false);
?>

<div class="auth-karte" style="max-width: 480px;">
    <div class="auth-logo">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="1.5" width="45" height="45" rx="10" stroke="#66E0FF" stroke-width="1.5" fill="none" opacity="0.35"/>
            <polygon points="24,7 41,24 24,41 7,24" stroke="#66E0FF" stroke-width="1.5" fill="none"/>
            <polygon points="24,14 34,24 24,34 14,24" stroke="#66E0FF" stroke-width="1" fill="rgba(102,224,255,0.08)"/>
        </svg>
        <strong>Shape Miner</strong>
        <span>Konto erstellen</span>
    </div>

    <?php if (!empty($feldFehler['allgemein'])): ?>
    <div class="hinweis hinweis-fehler"><?= htmlspecialchars($feldFehler['allgemein'], ENT_QUOTES) ?></div>
    <?php endif; ?>

    <form method="POST" class="formular">
        <?= csrf_feld() ?>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="formular-gruppe">
                <label for="vorname">Vorname</label>
                <input type="text" id="vorname" name="vorname"
                       value="<?= htmlspecialchars($eingaben['vorname'], ENT_QUOTES) ?>" required>
                <?php if (!empty($feldFehler['vorname'])): ?>
                <span class="feld-fehler"><?= htmlspecialchars($feldFehler['vorname'], ENT_QUOTES) ?></span>
                <?php endif; ?>
            </div>
            <div class="formular-gruppe">
                <label for="nachname">Nachname</label>
                <input type="text" id="nachname" name="nachname"
                       value="<?= htmlspecialchars($eingaben['nachname'], ENT_QUOTES) ?>" required>
                <?php if (!empty($feldFehler['nachname'])): ?>
                <span class="feld-fehler"><?= htmlspecialchars($feldFehler['nachname'], ENT_QUOTES) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="formular-gruppe">
            <label for="email">E-Mail</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($eingaben['email'], ENT_QUOTES) ?>"
                   autocomplete="email" required>
            <?php if (!empty($feldFehler['email'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['email'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="benutzername">Benutzername</label>
            <input type="text" id="benutzername" name="benutzername"
                   value="<?= htmlspecialchars($eingaben['benutzername'], ENT_QUOTES) ?>"
                   autocomplete="username" pattern="[A-Za-z0-9_]{3,30}" required>
            <?php if (!empty($feldFehler['benutzername'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['benutzername'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="passwort">Passwort</label>
            <input type="password" id="passwort" name="passwort"
                   autocomplete="new-password" minlength="8" required>
            <?php if (!empty($feldFehler['passwort'])): ?>
            <span class="feld-fehler"><?= htmlspecialchars($feldFehler['passwort'], ENT_QUOTES) ?></span>
            <?php endif; ?>
        </div>

        <div class="formular-gruppe">
            <label for="sprache">Sprache</label>
            <select id="sprache" name="sprache">
                <option value="de" <?= $eingaben['sprache'] === 'de' ? 'selected' : '' ?>>Deutsch</option>
                <option value="en" <?= $eingaben['sprache'] === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </div>

        <div style="display:flex; flex-direction:column; gap:10px; font-size:13px; color:var(--text-60);">
            <label style="display:flex; align-items:center; gap:10px; text-transform:none; font-weight:400; letter-spacing:0;">
                <input type="checkbox" name="agb" <?= !empty($_POST['agb']) ? 'checked' : '' ?>>
                Ich akzeptiere die <a href="/agb" target="_blank">AGB</a>
                <?php if (!empty($feldFehler['agb'])): ?>
                <span class="feld-fehler"><?= htmlspecialchars($feldFehler['agb'], ENT_QUOTES) ?></span>
                <?php endif; ?>
            </label>
            <label style="display:flex; align-items:center; gap:10px; text-transform:none; font-weight:400; letter-spacing:0;">
                <input type="checkbox" name="datenschutz" <?= !empty($_POST['datenschutz']) ? 'checked' : '' ?>>
                Ich akzeptiere die <a href="/datenschutz" target="_blank">Datenschutzerklärung</a>
                <?php if (!empty($feldFehler['datenschutz'])): ?>
                <span class="feld-fehler"><?= htmlspecialchars($feldFehler['datenschutz'], ENT_QUOTES) ?></span>
                <?php endif; ?>
            </label>
        </div>

        <button type="submit" class="btn btn-primaer">Konto erstellen</button>
    </form>

    <div class="auth-fuss">
        Bereits registriert? <a href="/anmelden">Anmelden</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/layout_fuss.php';
layout_fuss();
