<?php

declare(strict_types=1);

/**
 * Aufgabe: Eine öffentliche Rechtstext-Seite rendern (AGB/Datenschutz/Impressum).
 *
 * Der Inhalt kommt zentral aus dem Editor (siehe rechtstexte.php). Die einzelnen
 * Routen (agb.php, datenschutz.php, impressum.php) sind nur dünne Aufrufer.
 */

require_once __DIR__ . '/konfiguration_laden.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sitzung.php';
require_once __DIR__ . '/rechtstexte.php';
require_once __DIR__ . '/layout_kopf.php';
require_once __DIR__ . '/layout_fuss.php';

/**
 * Gibt eine vollständige Rechtstext-Seite aus.
 *
 * @param string $typ           rechtstexte-Typ (agb|datenschutz|impressum|jugendschutz|credits)
 * @param string $fallbackTitel Titel, falls der Editor (noch) keinen liefert
 */
function smu_rechtstext_seite(string $typ, string $fallbackTitel): void
{
    smu_sitzung_starten();

    $sprache = 'de'; // SMU-Web ist deutschsprachig; ?lang=en optional erweiterbar
    $text = smu_rechtstext($typ, $sprache);

    $titel  = $text['titel'] ?? $fallbackTitel;
    $inhalt = $text['inhalt'] ?? '';

    layout_kopf($titel, false);
    ?>
    <div class="auth-karte" style="max-width: 760px; text-align: left;">
        <div class="auth-logo" style="margin-bottom: 8px;">
            <strong>Shape Miner</strong>
            <span><?= htmlspecialchars($titel, ENT_QUOTES) ?></span>
        </div>

        <?php if ($inhalt === ''): ?>
        <div class="hinweis hinweis-warnung">
            Dieser Text ist derzeit nicht verfügbar. Bitte später erneut versuchen.
        </div>
        <?php else: ?>
        <div class="rechtstext"><?= smu_rechtstext_html($inhalt) ?></div>
        <?php endif; ?>

        <div class="auth-fuss" style="margin-top: 24px;">
            <a href="/agb">AGB</a> · <a href="/datenschutz">Datenschutz</a> ·
            <a href="/impressum">Impressum</a><br>
            <a href="/anmelden">Zur Anmeldung</a>
        </div>
    </div>
    <?php
    layout_fuss();
}
