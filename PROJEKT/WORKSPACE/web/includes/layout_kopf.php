<?php

declare(strict_types=1);

/**
 * Aufgabe: HTML-Kopf + optionale Seitenleiste für die SMU-Web-UI.
 *
 * @param string $titel       Seitentitel (ohne Suffix)
 * @param bool   $mitSidebar  Ob die Accountnavigation gezeigt wird
 * @param string $aktivNav    Schlüssel des aktiven Navpunkts
 */
function layout_kopf(string $titel, bool $mitSidebar = true, string $aktivNav = ''): void
{
    $hinweis = smu_hinweis_holen();
    $nutzer  = $mitSidebar ? smu_sitzung_nutzer() : null;
    $benutzername = htmlspecialchars((string) ($nutzer['benutzername'] ?? ''), ENT_QUOTES);
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titel, ENT_QUOTES) ?> · Shape Miner Account</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="<?= $mitSidebar ? 'mit-sidebar' : 'ohne-sidebar' ?>">

<?php if ($mitSidebar): ?>
<nav class="sidebar">
    <div class="marke">
        <svg class="marke-logo" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1" y="1" width="34" height="34" rx="8" stroke="#66E0FF" stroke-width="1.5" fill="none" opacity="0.4"/>
            <polygon points="18,6 30,18 18,30 6,18" stroke="#66E0FF" stroke-width="1.5" fill="none"/>
            <polygon points="18,11 25,18 18,25 11,18" stroke="#66E0FF" stroke-width="1" fill="rgba(102,224,255,0.08)"/>
        </svg>
        <div class="marke-text">
            <strong>Shape Miner</strong>
            <span>Account</span>
        </div>
    </div>

    <ul class="nav-liste">
        <?php
        $navPunkte = [
            'konto'      => ['/konto',           'Übersicht'],
            'profil'     => ['/konto/profil',     'Profil'],
            'email'      => ['/konto/email',      'E-Mail'],
            'passwort'   => ['/konto/passwort',   'Passwort'],
            'sicherheit' => ['/konto/sicherheit', '2-Faktor-Auth'],
            'loeschen'   => ['/konto/loeschen',   'Account löschen'],
        ];
        foreach ($navPunkte as $key => [$href, $label]):
            $aktiv = $aktivNav === $key ? ' aktiv' : '';
        ?>
        <li>
            <a href="<?= $href ?>" class="nav-link<?= $aktiv ?>">
                <?= htmlspecialchars($label, ENT_QUOTES) ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <div class="sidebar-fuss">
        <span class="konto-name"><?= $benutzername ?></span>
        <a href="/abmelden" class="abmelden-link">Abmelden</a>
    </div>
</nav>
<?php endif; ?>

<main class="inhalt">

<?php if ($hinweis !== null): ?>
<div class="hinweis hinweis-<?= htmlspecialchars($hinweis['typ'], ENT_QUOTES) ?>">
    <?= htmlspecialchars($hinweis['text'], ENT_QUOTES) ?>
</div>
<?php endif; ?>

<?php
}
