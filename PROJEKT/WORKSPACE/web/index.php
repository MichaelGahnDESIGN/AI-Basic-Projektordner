<?php

declare(strict_types=1);

/**
 * Aufgabe: Einstiegspunkt — leitet je nach Login-Status weiter.
 */

require_once __DIR__ . '/includes/konfiguration_laden.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/sitzung.php';

smu_sitzung_starten();

if (smu_sitzung_nutzer() !== null) {
    header('Location: /konto');
} else {
    header('Location: /anmelden');
}
exit;
