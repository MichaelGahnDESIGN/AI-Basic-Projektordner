<?php

declare(strict_types=1);

/**
 * Aufgabe: globale Sperrbegriffe laden und anwenden.
 *
 *  - sperrbegriffe_alle(): liefert alle Begriffe (klein), einmal pro Request.
 *  - sperrbegriff_verletzt(): findet den ersten in einem Text enthaltenen
 *    Sperrbegriff (case-insensitiv, Teilwort) — für die Namensprüfung.
 *  - sperrbegriffe_zensieren(): ersetzt jeden Sperrbegriff durch Sternchen
 *    gleicher Länge — für den künftigen Messenger.
 *
 * Die Begriffe liegen global in der SMU-DB (Tabelle `sperrbegriffe`) und
 * werden im Editor gepflegt.
 */

/**
 * Liefert alle Sperrbegriffe in Kleinschreibung (einmal pro Request gecacht).
 *
 * @return list<string>
 */
function sperrbegriffe_alle(PDO $pdo): array
{
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }
    try {
        $cache = array_map(
            static fn ($b): string => (string) $b,
            $pdo->query('SELECT begriff FROM sperrbegriffe')->fetchAll(PDO::FETCH_COLUMN)
        );
    } catch (Throwable) {
        // Tabelle fehlt o. Ä.: keine Sperrung, App bleibt nutzbar.
        $cache = [];
    }
    return $cache;
}

/**
 * Liefert den ersten im [$text] enthaltenen Sperrbegriff oder null.
 * Vergleich case-insensitiv als Teilzeichenkette.
 */
function sperrbegriff_verletzt(PDO $pdo, string $text): ?string
{
    $klein = mb_strtolower($text, 'UTF-8');
    foreach (sperrbegriffe_alle($pdo) as $begriff) {
        if ($begriff !== '' && mb_strpos($klein, $begriff, 0, 'UTF-8') !== false) {
            return $begriff;
        }
    }
    return null;
}

/**
 * Ersetzt jeden Sperrbegriff im [$text] durch Sternchen gleicher Länge.
 * Für Chat-/Messenger-Nachrichten. Reihenfolge: längste Begriffe zuerst,
 * damit Teiltreffer nicht kürzere Begriffe „überschreiben".
 */
function sperrbegriffe_zensieren(PDO $pdo, string $text): string
{
    $begriffe = sperrbegriffe_alle($pdo);
    if ($begriffe === []) {
        return $text;
    }
    usort($begriffe, static fn ($a, $b): int => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

    foreach ($begriffe as $begriff) {
        if ($begriff === '') {
            continue;
        }
        $muster = '/' . preg_quote($begriff, '/') . '/iu';
        $text = (string) preg_replace_callback(
            $muster,
            static fn (array $t): string => str_repeat('*', mb_strlen($t[0], 'UTF-8')),
            $text
        );
    }
    return $text;
}

/**
 * Normalisiert einen neuen Sperrbegriff (getrimmt, klein) oder null bei ungültig.
 */
function sperrbegriff_normalisieren(mixed $wert): ?string
{
    if (!is_string($wert)) {
        return null;
    }
    $wert = mb_strtolower(trim($wert), 'UTF-8');
    $laenge = mb_strlen($wert, 'UTF-8');
    return ($laenge >= 1 && $laenge <= 100) ? $wert : null;
}
