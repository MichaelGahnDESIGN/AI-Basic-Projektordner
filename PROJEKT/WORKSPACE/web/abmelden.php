<?php

declare(strict_types=1);

/**
 * Aufgabe: Nutzer abmelden und zur Anmeldeseite weiterleiten.
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';

smu_sitzung_starten();
smu_sitzung_abmelden();
smu_hinweis_setzen('erfolg', 'Erfolgreich abgemeldet.');

header('Location: /anmelden');
exit;
