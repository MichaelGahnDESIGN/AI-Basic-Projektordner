<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: PDO-Verbindung zur MariaDB aufbauen.
 *
 *  - utf8mb4 direkt im DSN (korrekte Umlaute und volle Unicode-Unterstützung)
 *  - Exceptions statt stiller Fehler
 *  - echte Prepared Statements (keine Emulation) gegen SQL-Injection
 */

require_once __DIR__ . '/konfiguration.php';

/**
 * Liefert die einmalig pro Request aufgebaute PDO-Verbindung.
 *
 * @throws RuntimeException wenn die Verbindung fehlschlägt
 *         (Verbindungsdetails landen nur im Server-Log, nie beim Client)
 */
function db_verbindung(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = konfiguration()['db'];

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        (string) $db['host'],
        (string) $db['name']
    );
    if (isset($db['port'])) {
        $dsn .= ';port=' . (int) $db['port'];
    }

    try {
        $pdo = new PDO($dsn, (string) $db['benutzer'], (string) $db['passwort'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $fehler) {
        // Originalmeldung (kann Host/Benutzer enthalten) nur ins Log.
        error_log('[shape-miner-api] DB-Verbindung fehlgeschlagen: ' . $fehler->getMessage());
        throw new RuntimeException('Datenbankverbindung fehlgeschlagen.');
    }

    return $pdo;
}
