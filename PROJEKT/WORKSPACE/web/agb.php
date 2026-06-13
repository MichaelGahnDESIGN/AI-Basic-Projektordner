<?php

declare(strict_types=1);

/**
 * Aufgabe: Allgemeine Geschäftsbedingungen (öffentlich, ohne Login).
 *
 * HINWEIS: Strukturierter ENTWURF. Verbindliche Rechtstexte muss der Betreiber
 * juristisch prüfen/ergänzen, bevor sie produktiv gelten.
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';
require_once __DIR__ . '/includes/layout_kopf.php';
require_once __DIR__ . '/includes/layout_fuss.php';

smu_sitzung_starten();
layout_kopf('AGB', false);
?>
<div class="auth-karte" style="max-width: 720px; text-align: left;">
    <div class="auth-logo" style="margin-bottom: 8px;">
        <strong>Allgemeine Geschäftsbedingungen</strong>
        <span>Shape Miner Account</span>
    </div>

    <div class="hinweis hinweis-warnung" style="margin-bottom: 20px;">
        Entwurf — rechtlich noch zu prüfen und zu vervollständigen.
    </div>

    <div class="rechtstext">
        <h2>1. Geltungsbereich</h2>
        <p>Diese Bedingungen gelten für die Nutzung des Shape-Miner-Kontos und der
        damit verbundenen Spiele der Shape-Miner-Reihe.</p>

        <h2>2. Konto</h2>
        <p>Ein Konto berechtigt zur Nutzung aller angebundenen Shape-Miner-Spiele
        (Single-Sign-On). Die Zugangsdaten sind vertraulich zu behandeln.</p>

        <h2>3. Pflichten der Nutzer</h2>
        <ul>
            <li>Wahrheitsgemäße Angaben bei der Registrierung</li>
            <li>Keine missbräuchliche Nutzung, kein automatisiertes Ausspähen</li>
            <li>Schutz der eigenen Zugangsdaten</li>
        </ul>

        <h2>4. Verfügbarkeit</h2>
        <p>Der Dienst wird mit angemessener Sorgfalt betrieben; ein Anspruch auf
        ununterbrochene Verfügbarkeit besteht nicht.</p>

        <h2>5. Kündigung</h2>
        <p>Das Konto kann jederzeit über „Account löschen" beendet werden.</p>

        <h2>6. Kontakt</h2>
        <p><a href="mailto:support@shapeminer.com">support@shapeminer.com</a></p>
    </div>

    <div class="auth-fuss" style="margin-top: 24px;">
        <a href="/registrieren">Zurück zur Registrierung</a>
    </div>
</div>
<?php layout_fuss(); ?>
