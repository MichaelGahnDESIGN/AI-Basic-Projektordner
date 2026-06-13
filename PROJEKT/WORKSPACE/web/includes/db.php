<?php

declare(strict_types=1);

/**
 * Aufgabe: PDO-Verbindung zur SMU-Datenbank (Singleton pro Request).
 */

require_once __DIR__ . '/konfiguration_laden.php';

function smu_db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = smu_konfiguration()['db'];
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['name']);

    $pdo = new PDO($dsn, $cfg['benutzer'], $cfg['passwort'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}
