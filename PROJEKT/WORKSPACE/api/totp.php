<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: TOTP-Einmalcodes nach RFC 6238 (auf Basis von
 * HOTP, RFC 4226) — ohne externe Pakete.
 *
 *  - HMAC-SHA1, 30-Sekunden-Zeitschritt, 6 Stellen (Standard aller
 *    gängigen Authenticator-Apps).
 *  - Prüf-Fenster ±1 Zeitschritt gleicht kleine Uhrabweichungen zwischen
 *    Server und Smartphone aus.
 *  - Base32 (RFC 4648) ist selbst implementiert, weil Authenticator-Apps
 *    das Secret in dieser Kodierung erwarten.
 */

/** Zeitschrittlänge in Sekunden (RFC-6238-Standard). */
const TOTP_ZEITSCHRITT_SEKUNDEN = 30;

/** Anzahl der Code-Stellen. */
const TOTP_STELLEN = 6;

/** Toleranz in Zeitschritten vor/zurück beim Prüfen. */
const TOTP_FENSTER = 1;

/** Base32-Alphabet nach RFC 4648. */
const TOTP_BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

/**
 * Erzeugt ein frisches TOTP-Geheimnis: 20 Zufallsbytes (160 Bit, die
 * native Blockgröße von HMAC-SHA1) als Base32-String (32 Zeichen).
 */
function totp_secret_erzeugen(): string
{
    return totp_base32_kodieren(random_bytes(20));
}

/**
 * Prüft einen vom Benutzer eingegebenen Code gegen das Secret.
 * Akzeptiert den aktuellen Zeitschritt sowie ±TOTP_FENSTER Nachbarn.
 * Vergleich mit hash_equals() und ohne frühen Abbruch (konstante Zeit).
 */
function totp_code_pruefen(string $secretBase32, string $code): bool
{
    $code = trim($code);
    if (preg_match('/^[0-9]{' . TOTP_STELLEN . '}$/', $code) !== 1) {
        return false;
    }

    $secret = totp_base32_dekodieren($secretBase32);
    if ($secret === null || $secret === '') {
        return false;
    }

    $zeitschritt = intdiv(time(), TOTP_ZEITSCHRITT_SEKUNDEN);
    $gueltig = false;

    for ($versatz = -TOTP_FENSTER; $versatz <= TOTP_FENSTER; $versatz++) {
        if (hash_equals(totp_code_berechnen($secret, $zeitschritt + $versatz), $code)) {
            $gueltig = true;
        }
    }

    return $gueltig;
}

/**
 * Baut die otpauth-URI für Authenticator-Apps (QR-Code-Inhalt),
 * z. B. otpauth://totp/Issuer:konto@example.de?secret=...&issuer=...
 */
function totp_otpauth_uri(string $secretBase32, string $kontoLabel, string $issuer): string
{
    return 'otpauth://totp/'
        . rawurlencode($issuer) . ':' . rawurlencode($kontoLabel)
        . '?secret=' . rawurlencode($secretBase32)
        . '&issuer=' . rawurlencode($issuer)
        . '&algorithm=SHA1'
        . '&digits=' . TOTP_STELLEN
        . '&period=' . TOTP_ZEITSCHRITT_SEKUNDEN;
}

/**
 * Berechnet den HOTP-Code (RFC 4226) für einen Zählerwert:
 * HMAC-SHA1 über den 64-Bit-Big-Endian-Zähler, dynamische Trunkierung,
 * dann auf TOTP_STELLEN Dezimalstellen gekürzt (führende Nullen bleiben).
 */
function totp_code_berechnen(string $secretRoh, int $zaehler): string
{
    $hmac = hash_hmac('sha1', pack('J', $zaehler), $secretRoh, true);

    $offset = ord($hmac[19]) & 0x0F;
    $wert = ((ord($hmac[$offset]) & 0x7F) << 24)
        | ((ord($hmac[$offset + 1]) & 0xFF) << 16)
        | ((ord($hmac[$offset + 2]) & 0xFF) << 8)
        | (ord($hmac[$offset + 3]) & 0xFF);

    $code = $wert % (10 ** TOTP_STELLEN);

    return str_pad((string) $code, TOTP_STELLEN, '0', STR_PAD_LEFT);
}

/**
 * Kodiert Rohbytes als Base32 (RFC 4648, ohne Padding-Zeichen).
 */
function totp_base32_kodieren(string $bytes): string
{
    $bits = '';
    foreach (str_split($bytes) as $byte) {
        $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
    }

    $ausgabe = '';
    foreach (str_split($bits, 5) as $gruppe) {
        $ausgabe .= TOTP_BASE32_ALPHABET[(int) bindec(str_pad($gruppe, 5, '0'))];
    }

    return $ausgabe;
}

/**
 * Dekodiert einen Base32-String (RFC 4648) zu Rohbytes.
 * Liefert null bei ungültigen Zeichen; Padding "=" wird toleriert.
 */
function totp_base32_dekodieren(string $base32): ?string
{
    $base32 = strtoupper(rtrim(trim($base32), '='));
    if ($base32 === '' || preg_match('/^[A-Z2-7]+$/', $base32) !== 1) {
        return null;
    }

    $bits = '';
    foreach (str_split($base32) as $zeichen) {
        $bits .= str_pad(decbin(strpos(TOTP_BASE32_ALPHABET, $zeichen)), 5, '0', STR_PAD_LEFT);
    }

    $bytes = '';
    foreach (str_split($bits, 8) as $gruppe) {
        if (strlen($gruppe) === 8) { // Restbits am Ende sind Füllung, kein Byte
            $bytes .= chr((int) bindec($gruppe));
        }
    }

    return $bytes;
}
