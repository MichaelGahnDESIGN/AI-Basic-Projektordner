<?php

declare(strict_types=1);

/**
 * Aufgabe: Konto-Übersicht — zeigt Profildaten auf einen Blick.
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

$sitzungNutzer = smu_sitzung_nutzer();
$profil = nutzer_profil_laden((int) $sitzungNutzer['id']);

if ($profil === null) {
    smu_sitzung_abmelden();
    header('Location: /anmelden');
    exit;
}

layout_kopf('Mein Konto', true, 'konto');
?>

<div class="seitenkopf">
    <h1>Mein Konto</h1>
    <p>Willkommen zurück, <?= htmlspecialchars($profil['vorname'], ENT_QUOTES) ?>.</p>
</div>

<div class="karte">
    <h2>Profil</h2>
    <div class="profil-zeile">
        <span class="profil-label">Name</span>
        <span class="profil-wert">
            <?= htmlspecialchars($profil['vorname'] . ' ' . $profil['nachname'], ENT_QUOTES) ?>
        </span>
    </div>
    <div class="profil-zeile">
        <span class="profil-label">Benutzername</span>
        <span class="profil-wert"><?= htmlspecialchars($profil['benutzername'], ENT_QUOTES) ?></span>
    </div>
    <div class="profil-zeile">
        <span class="profil-label">E-Mail</span>
        <span class="profil-wert"><?= htmlspecialchars($profil['email'], ENT_QUOTES) ?></span>
    </div>
    <div class="profil-zeile">
        <span class="profil-label">Sprache</span>
        <span class="profil-wert"><?= $profil['sprache'] === 'de' ? 'Deutsch' : 'English' ?></span>
    </div>
    <div class="profil-zeile">
        <span class="profil-label">Konto erstellt</span>
        <span class="profil-wert"><?= htmlspecialchars($profil['erstellt_am'], ENT_QUOTES) ?></span>
    </div>
    <div class="profil-zeile">
        <span class="profil-label">2-Faktor-Auth</span>
        <span class="profil-wert" style="color: <?= $profil['totp_aktiviert'] ? 'var(--erfolg)' : 'var(--text-45)' ?>">
            <?= $profil['totp_aktiviert'] ? 'Aktiv' : 'Inaktiv' ?>
        </span>
    </div>
</div>

<div class="karte">
    <h2>Meine Spiele</h2>
    <div class="profil-zeile">
        <span class="profil-label">Shape Miner Deluxe</span>
        <span class="profil-wert">
            <a href="https://deluxe.shapeminer.com">Spielen →</a>
        </span>
    </div>
</div>

<?php layout_fuss(); ?>
