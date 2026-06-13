<?php

declare(strict_types=1);

/**
 * Aufgabe: TOTP-Recovery-Codes erzeugen, verteilen und einlösen.
 *
 * Codes sind hochentropisch (40 Bit) und werden nur als SHA-256-Hash
 * gespeichert. Jeder Code ist genau einmal nutzbar.
 */

const RECOVERY_CODE_ANZAHL = 10;

/**
 * Erzeugt frische Recovery-Codes für einen Nutzer, ersetzt vorhandene.
 *
 * @return list<string> Klartext-Codes (NUR jetzt anzeigen, danach unwiederbringlich)
 */
function recovery_codes_erzeugen(PDO $pdo, int $userId): array
{
    $pdo->prepare('DELETE FROM totp_recovery_codes WHERE user_id = :id')->execute([':id' => $userId]);

    $codes = [];
    $insert = $pdo->prepare(
        'INSERT INTO totp_recovery_codes (user_id, code_hash) VALUES (:uid, :h)'
    );
    foreach (range(1, RECOVERY_CODE_ANZAHL) as $_) {
        // 5 Byte → 8 Base32-Zeichen, gruppiert als XXXX-XXXX (gut abtippbar).
        $roh = strtoupper(substr(base32_kodieren(random_bytes(5)), 0, 8));
        $anzeige = substr($roh, 0, 4) . '-' . substr($roh, 4, 4);
        $codes[] = $anzeige;
        $insert->execute([':uid' => $userId, ':h' => hash('sha256', recovery_normalisieren($anzeige))]);
    }
    return $codes;
}

/**
 * Normalisiert eine Nutzereingabe (Bindestriche/Leerzeichen weg, Großschreibung).
 */
function recovery_normalisieren(string $code): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
}

/**
 * Prüft einen eingegebenen Recovery-Code und verbraucht ihn bei Erfolg.
 */
function recovery_code_einloesen(PDO $pdo, int $userId, string $eingabe): bool
{
    $norm = recovery_normalisieren($eingabe);
    if (strlen($norm) !== 8) {
        return false;
    }
    $hash = hash('sha256', $norm);

    $stmt = $pdo->prepare(
        'SELECT id FROM totp_recovery_codes
         WHERE user_id = :uid AND code_hash = :h AND benutzt_am IS NULL'
    );
    $stmt->execute([':uid' => $userId, ':h' => $hash]);
    $zeile = $stmt->fetch();
    if ($zeile === false) {
        return false;
    }

    $pdo->prepare('UPDATE totp_recovery_codes SET benutzt_am = NOW() WHERE id = :id')
        ->execute([':id' => (int) $zeile['id']]);
    return true;
}

/**
 * Anzahl noch ungenutzter Recovery-Codes.
 */
function recovery_codes_verbleibend(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM totp_recovery_codes WHERE user_id = :uid AND benutzt_am IS NULL'
    );
    $stmt->execute([':uid' => $userId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Base32-Kodierung (RFC 4648, ohne Padding) — für gut lesbare Codes.
 */
function base32_kodieren(string $daten): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($daten) as $zeichen) {
        $bits .= str_pad(decbin(ord($zeichen)), 8, '0', STR_PAD_LEFT);
    }
    $ausgabe = '';
    foreach (str_split($bits, 5) as $block) {
        $ausgabe .= $alphabet[bindec(str_pad($block, 5, '0', STR_PAD_RIGHT))];
    }
    return $ausgabe;
}
