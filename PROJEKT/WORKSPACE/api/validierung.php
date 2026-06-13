<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: Eingaben prüfen und normalisieren.
 *
 * Jede Funktion liefert den bereinigten Wert oder null bei ungültiger
 * Eingabe — die Endpunkte entscheiden, wie sie darauf antworten.
 */

/**
 * Allgemeiner Text (z. B. Vorname, Nachname): gültiges UTF-8, getrimmt,
 * keine Steuerzeichen, Länge in Zeichen zwischen $min und $max.
 * Umlaute und internationale Schriftzeichen sind ausdrücklich erlaubt.
 */
function validierung_text(mixed $wert, int $min, int $max): ?string
{
    if (!is_string($wert) || !mb_check_encoding($wert, 'UTF-8')) {
        return null;
    }

    $wert = trim($wert);
    $laenge = mb_strlen($wert, 'UTF-8');
    if ($laenge < $min || $laenge > $max) {
        return null;
    }

    // Steuerzeichen (inkl. NUL und DEL) ablehnen.
    if (preg_match('/[\x00-\x1F\x7F]/u', $wert) !== 0) {
        return null;
    }

    return $wert;
}

/**
 * E-Mail-Adresse: getrimmt, maximal 254 Zeichen, RFC-Grundprüfung.
 */
function validierung_email(mixed $wert): ?string
{
    if (!is_string($wert)) {
        return null;
    }

    $wert = trim($wert);
    if ($wert === '' || strlen($wert) > 254) {
        return null;
    }

    if (filter_var($wert, FILTER_VALIDATE_EMAIL) === false) {
        return null;
    }

    return $wert;
}

/**
 * Benutzername: 3 bis 30 Zeichen, nur Buchstaben (A–Z), Ziffern, Unterstrich.
 * Bewusst eng gefasst, damit Spielernamen überall problemlos anzeigbar sind.
 */
function validierung_benutzername(mixed $wert): ?string
{
    if (!is_string($wert)) {
        return null;
    }

    $wert = trim($wert);
    return preg_match('/^[A-Za-z0-9_]{3,30}$/', $wert) === 1 ? $wert : null;
}

/**
 * Passwort: 8 bis 200 Zeichen, gültiges UTF-8.
 * Wird bewusst NICHT getrimmt oder verändert — es zählt exakt die Eingabe.
 */
function validierung_passwort(mixed $wert): ?string
{
    if (!is_string($wert) || !mb_check_encoding($wert, 'UTF-8')) {
        return null;
    }

    $laenge = mb_strlen($wert, 'UTF-8');
    return ($laenge >= 8 && $laenge <= 200) ? $wert : null;
}

/**
 * Sprache: ausschließlich 'de' oder 'en' (entspricht dem ENUM in der DB).
 */
function validierung_sprache(mixed $wert): ?string
{
    return in_array($wert, ['de', 'en'], true) ? $wert : null;
}

/**
 * Dokumentversion für Einwilligungen (AGB/Datenschutz),
 * z. B. "1.0" oder "2026-06-01". Maximal 32 Zeichen.
 */
function validierung_version(mixed $wert): ?string
{
    if (!is_string($wert)) {
        return null;
    }

    $wert = trim($wert);
    return preg_match('/^[0-9A-Za-z._-]{1,32}$/', $wert) === 1 ? $wert : null;
}
