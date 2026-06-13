<?php

declare(strict_types=1);

/**
 * Aufgabe: SMU-Konfiguration + API-Pfad-Konstante bereitstellen.
 *
 * SMU_API_PFAD wird einmal ermittelt und als Konstante gesetzt.
 * Beide Umgebungen funktionieren ohne Code-Änderung:
 *   - Server: api/ liegt im Webroot-Unterordner neben web-Dateien
 *   - Lokal:  api/ liegt als Geschwisterordner neben web/
 *
 * Config-Suchreihenfolge:
 *   1. Pfad aus Umgebungsvariable SMU_CONFIG_PFAD  (empfohlen: Server)
 *   2. SMU_API_PFAD/config.php                    (lokale Entwicklung)
 */

if (!defined('SMU_API_PFAD')) {
    // Webroot/api/ (Server) hat Vorrang; fällt zurück auf WORKSPACE/api/ (lokal)
    $smuApiPfad = is_file(dirname(__DIR__) . '/api/crypto.php')
        ? dirname(__DIR__) . '/api'
        : dirname(__DIR__, 2) . '/api';
    define('SMU_API_PFAD', $smuApiPfad);
    unset($smuApiPfad);
}

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
    $pfade[] = SMU_API_PFAD . '/config.php';

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
