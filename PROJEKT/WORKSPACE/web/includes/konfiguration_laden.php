<?php

declare(strict_types=1);

/**
 * Aufgabe: SMU-Konfiguration laden (gleiche Suchreihenfolge wie API).
 *
 * Suchreihenfolge:
 *   1. Pfad aus Umgebungsvariable SMU_CONFIG_PFAD
 *   2. ../../api/config.php  (relativ zum web-Ordner, für lokale Entwicklung)
 */

function smu_konfiguration(): array
{
    static $geladen = null;

    if (is_array($geladen)) {
        return $geladen;
    }

    $pfade = [];

    $env = getenv('SMU_CONFIG_PFAD') ?: ($_SERVER['SMU_CONFIG_PFAD'] ?? '');
    if ($env !== '') {
        $pfade[] = (string) $env;
    }
    $pfade[] = dirname(__DIR__, 2) . '/api/config.php';

    foreach ($pfade as $pfad) {
        if (is_readable($pfad)) {
            $inhalt = require $pfad;
            if (is_array($inhalt)) {
                $geladen = $inhalt;
                return $geladen;
            }
        }
    }

    throw new RuntimeException('SMU config.php nicht gefunden. SMU_CONFIG_PFAD prüfen.');
}
