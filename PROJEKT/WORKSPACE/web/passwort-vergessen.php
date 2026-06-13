<?php

declare(strict_types=1);

/**
 * Aufgabe: Passwort-vergessen — Reset-Link per E-Mail anfordern.
 *
 * Datenschutz: Die Antwort ist IMMER generisch ("falls die E-Mail existiert …"),
 * damit registrierte Adressen nicht enumeriert werden können.
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/nutzer.php';
require_once SMU_API_PFAD . '/rate_limit.php';
require_once SMU_API_PFAD . '/mail.php';

smu_sitzung_starten();

if (smu_sitzung_nutzer() !== null) { header('Location: /konto'); exit; }

$gesendet = false;
$fehler   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();
    $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));

    // Rate-Limit pro IP (verhindert Massenversand / Enumeration über Timing).
    $pdo = smu_db();
    $rateKey = rate_limit_schluessel('pw-reset', (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    if (rate_limit_gesperrt($pdo, $rateKey) > 0) {
        $fehler = 'Zu viele Anfragen. Bitte später erneut versuchen.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fehler = 'Bitte eine gültige E-Mail-Adresse eingeben.';
    } else {
        rate_limit_fehlschlag($pdo, $rateKey, 5, 900, 900);

        // Konto suchen (Blind-Index). Bei Treffer: Token erzeugen + mailen.
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email_blind_index = :bi AND aktiv = 1');
        $stmt->execute([':bi' => smu_blind_index($email)]);
        $zeile = $stmt->fetch();

        if ($zeile !== false) {
            $token = bin2hex(random_bytes(32));
            $pdo->prepare(
                'INSERT INTO passwort_reset (token_hash, user_id, ablauf)
                 VALUES (:th, :uid, (NOW() + INTERVAL 1 HOUR))'
            )->execute([
                ':th'  => hash('sha256', $token),
                ':uid' => (int) $zeile['id'],
            ]);

            $link = 'https://login.shapeminer.com/passwort-neu?token=' . $token;
            smu_mail_senden(
                $email,
                'Shape Miner — Passwort zurücksetzen',
                "Hallo,\n\n"
                . "du hast angefordert, dein Shape-Miner-Passwort zurückzusetzen.\n"
                . "Öffne dazu innerhalb der nächsten Stunde diesen Link:\n\n"
                . $link . "\n\n"
                . "Wenn du das nicht warst, ignoriere diese E-Mail einfach — dein "
                . "Passwort bleibt dann unverändert.\n\n"
                . "Dein Shape-Miner-Team"
            );
        }

        // IMMER generische Erfolgsmeldung (keine Enumeration).
        $gesendet = true;
    }
}

require_once __DIR__ . '/includes/layout_kopf.php';
layout_kopf('Passwort vergessen', false);
?>
<div class="auth-karte">
    <div class="auth-logo">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1.5" y="1.5" width="45" height="45" rx="10" stroke="#66E0FF" stroke-width="1.5" fill="none" opacity="0.35"/>
            <polygon points="24,7 41,24 24,41 7,24" stroke="#66E0FF" stroke-width="1.5" fill="none"/>
        </svg>
        <strong>Shape Miner</strong>
        <span>Passwort zurücksetzen</span>
    </div>

    <?php if ($gesendet): ?>
    <div class="hinweis hinweis-erfolg">
        Falls ein Konto mit dieser E-Mail existiert, haben wir dir einen
        Link zum Zurücksetzen geschickt. Prüfe dein Postfach (auch den Spam-Ordner).
    </div>
    <div class="auth-fuss"><a href="/anmelden">Zurück zur Anmeldung</a></div>
    <?php else: ?>
        <?php if ($fehler !== ''): ?>
        <div class="hinweis hinweis-fehler"><?= htmlspecialchars($fehler, ENT_QUOTES) ?></div>
        <?php endif; ?>
        <p style="color: var(--text-60); font-size: 14px; margin-bottom: 18px;">
            Gib deine E-Mail-Adresse ein. Wir senden dir einen Link zum Zurücksetzen.
        </p>
        <form method="POST" class="formular">
            <?= csrf_feld() ?>
            <div class="formular-gruppe">
                <label for="email">E-Mail</label>
                <input type="email" id="email" name="email" autocomplete="email" required>
            </div>
            <button type="submit" class="btn btn-primaer">Link anfordern</button>
        </form>
        <div class="auth-fuss"><a href="/anmelden">Zurück zur Anmeldung</a></div>
    <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/includes/layout_fuss.php';
layout_fuss();
