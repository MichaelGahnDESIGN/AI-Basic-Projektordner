<?php

declare(strict_types=1);

/**
 * Aufgabe: Nutzerprofil aus SMU-DB laden und PII entschlüsseln.
 *
 * Nutzt crypto.php aus dem API-Ordner (gleiche Schlüssel, gleiche Logik).
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../../api/crypto.php';

/**
 * Lädt das vollständige Profil eines Nutzers (mit entschlüsselter PII).
 *
 * @return array{id:int, benutzername:string, vorname:string, nachname:string,
 *               email:string, sprache:string, rolle:string, erstellt_am:string,
 *               totp_aktiviert:bool}|null
 */
function nutzer_profil_laden(int $userId): ?array
{
    $stmt = smu_db()->prepare(
        'SELECT id, email_enc, vorname_enc, nachname_enc, benutzername,
                sprache, rolle, aktiv, erstellt_am, totp_aktiviert
         FROM users WHERE id = :id'
    );
    $stmt->execute([':id' => $userId]);
    $zeile = $stmt->fetch();

    if ($zeile === false || (int) $zeile['aktiv'] !== 1) {
        return null;
    }

    return [
        'id'             => (int) $zeile['id'],
        'benutzername'   => (string) $zeile['benutzername'],
        'vorname'        => smu_entschluesseln((string) $zeile['vorname_enc'], 'vorname_enc'),
        'nachname'       => smu_entschluesseln((string) $zeile['nachname_enc'], 'nachname_enc'),
        'email'          => smu_entschluesseln((string) $zeile['email_enc'], 'email_enc'),
        'sprache'        => (string) $zeile['sprache'],
        'rolle'          => (string) $zeile['rolle'],
        'erstellt_am'    => (string) $zeile['erstellt_am'],
        'totp_aktiviert' => (bool) $zeile['totp_aktiviert'],
    ];
}

/**
 * Entschlüsselt einen verschlüsselten DB-Wert.
 * Wrapper um krypto_entschluesseln() aus der API.
 */
function smu_entschluesseln(string $chiffretext, string $kontext): string
{
    try {
        return krypto_entschluesseln($chiffretext, $kontext);
    } catch (Throwable) {
        return '';
    }
}

/**
 * Verschlüsselt einen Klartextwert für die DB.
 * Wrapper um krypto_verschluesseln() aus der API.
 */
function smu_verschluesseln(string $klartext, string $kontext): string
{
    return krypto_verschluesseln($klartext, $kontext);
}

/**
 * Berechnet den E-Mail-Blind-Index für Datenbanksuchen.
 */
function smu_blind_index(string $email): string
{
    return krypto_email_blind_index(mb_strtolower(trim($email)));
}
