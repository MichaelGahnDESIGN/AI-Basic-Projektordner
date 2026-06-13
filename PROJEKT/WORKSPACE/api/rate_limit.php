<?php

declare(strict_types=1);

/**
 * Aufgabe: einfacher, robuster Brute-Force-Schutz (Rate-Limiting).
 *
 * Zählt Fehlversuche pro Schlüssel in einem gleitenden Zeitfenster und sperrt
 * nach Überschreiten temporär. Schlüssel werden gehasht gespeichert (keine
 * Klartext-IPs). Nutzbar aus API und Web (beide übergeben ihre PDO-Verbindung).
 *
 * Tabelle: rate_limit (siehe schema-ratelimit.sql).
 */

require_once __DIR__ . '/konfiguration.php';

/** Default-Grenzwerte (können pro Aufruf überschrieben werden). */
const RATE_LIMIT_MAX_VERSUCHE  = 8;     // Fehlversuche bis zur Sperre
const RATE_LIMIT_FENSTER_SEK   = 900;   // Zeitfenster (15 min)
const RATE_LIMIT_SPERRE_SEK    = 900;   // Sperrdauer (15 min)

/**
 * Bildet einen stabilen, gehashten Schlüssel (z. B. "login:ip" + Roh-IP).
 * Roh-Werte werden per HMAC (ip_hash-Schlüssel) verschleiert.
 */
function rate_limit_schluessel(string $bereich, string $rohwert): string
{
    $key = hash_hmac('sha256', $bereich . '|' . $rohwert, konfiguration_schluessel('ip_hash'));
    return substr($bereich . ':' . $key, 0, 190);
}

/**
 * Prüft, ob der Schlüssel aktuell gesperrt ist.
 *
 * @return int Verbleibende Sperrsekunden (0 = nicht gesperrt)
 */
function rate_limit_gesperrt(PDO $pdo, string $schluessel): int
{
    $stmt = $pdo->prepare(
        'SELECT gesperrt_bis FROM rate_limit
         WHERE schluessel = :s AND gesperrt_bis IS NOT NULL AND gesperrt_bis > NOW()'
    );
    $stmt->execute([':s' => $schluessel]);
    $bis = $stmt->fetchColumn();

    if ($bis === false) {
        return 0;
    }
    return max(0, strtotime((string) $bis) - time());
}

/**
 * Verbucht einen Fehlversuch und sperrt bei Überschreiten der Grenze.
 */
function rate_limit_fehlschlag(
    PDO $pdo,
    string $schluessel,
    int $maxVersuche = RATE_LIMIT_MAX_VERSUCHE,
    int $fensterSek = RATE_LIMIT_FENSTER_SEK,
    int $sperreSek = RATE_LIMIT_SPERRE_SEK
): void {
    // Atomar: neuen Datensatz anlegen ODER Zähler erhöhen. Fensterreset, wenn
    // das alte Fenster abgelaufen ist; Sperre setzen, sobald die Grenze fällt.
    $stmt = $pdo->prepare(
        'INSERT INTO rate_limit (schluessel, versuche, fenster_start)
         VALUES (:s, 1, NOW())
         ON DUPLICATE KEY UPDATE
            versuche      = IF(fenster_start < (NOW() - INTERVAL :fenster SECOND), 1, versuche + 1),
            fenster_start = IF(fenster_start < (NOW() - INTERVAL :fenster2 SECOND), NOW(), fenster_start),
            gesperrt_bis  = IF(
                IF(fenster_start < (NOW() - INTERVAL :fenster3 SECOND), 1, versuche + 1) >= :max,
                (NOW() + INTERVAL :sperre SECOND),
                gesperrt_bis
            )'
    );
    $stmt->execute([
        ':s' => $schluessel,
        ':fenster' => $fensterSek,
        ':fenster2' => $fensterSek,
        ':fenster3' => $fensterSek,
        ':max' => $maxVersuche,
        ':sperre' => $sperreSek,
    ]);
}

/**
 * Setzt den Zähler nach erfolgreichem Login zurück (Eintrag löschen).
 */
function rate_limit_erfolg(PDO $pdo, string $schluessel): void
{
    $pdo->prepare('DELETE FROM rate_limit WHERE schluessel = :s')->execute([':s' => $schluessel]);
}

/**
 * Räumt abgelaufene Einträge gelegentlich auf (best effort, kein harter Lauf).
 */
function rate_limit_aufraeumen(PDO $pdo): void
{
    try {
        $pdo->query('DELETE FROM rate_limit
            WHERE (gesperrt_bis IS NULL OR gesperrt_bis < NOW())
              AND fenster_start < (NOW() - INTERVAL 1 DAY)');
    } catch (Throwable) {
        // Aufräumen ist unkritisch — Fehler ignorieren.
    }
}
