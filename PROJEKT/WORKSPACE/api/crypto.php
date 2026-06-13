<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: Schutz personenbezogener Daten.
 *
 *  - Verschlüsselung at rest mit AES-256-GCM (authentifiziert, openssl).
 *  - Deterministischer E-Mail-Blind-Index mit HMAC-SHA256 für die
 *    Login-Suche — die Klartext-E-Mail erreicht die Datenbank nie.
 *  - IP-Hash (HMAC-SHA256) für datensparsame Einwilligungs-Nachweise.
 *
 * openssl statt sodium, weil sodium AES-256-GCM nur mit passender
 * CPU-Unterstützung anbietet; openssl ist auf All-Inkl immer verfügbar.
 *
 * Speicherformat: "v1:" + Base64( Nonce 12 Byte | Tag 16 Byte | Geheimtext )
 * Das Versionspräfix erlaubt späteren Schlüssel- oder Verfahrenswechsel.
 */

require_once __DIR__ . '/konfiguration.php';

// Der Kontext bindet jeden Geheimtext als GCM-Zusatzdaten (AAD) an seine
// Spalte, damit verschlüsselte Werte nicht zwischen Feldern oder Tabellen
// vertauscht werden können, ohne dass die Entschlüsselung fehlschlägt.
const KRYPTO_KONTEXT_EMAIL    = 'users.email_enc';
const KRYPTO_KONTEXT_VORNAME  = 'users.vorname_enc';
const KRYPTO_KONTEXT_NACHNAME = 'users.nachname_enc';

/**
 * Verschlüsselt einen Klartext mit AES-256-GCM und frischer Zufalls-Nonce.
 *
 * @param string $klartext zu schützender Wert (UTF-8)
 * @param string $kontext  Spalten-Kontext, z. B. KRYPTO_KONTEXT_EMAIL
 * @throws RuntimeException wenn die Verschlüsselung fehlschlägt
 */
function krypto_verschluesseln(string $klartext, string $kontext = ''): string
{
    $nonce = random_bytes(12);
    $tag = '';

    $geheimtext = openssl_encrypt(
        $klartext,
        'aes-256-gcm',
        konfiguration_schluessel('verschluesselung'),
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
        $kontext,
        16
    );

    if ($geheimtext === false) {
        throw new RuntimeException('Verschlüsselung fehlgeschlagen.');
    }

    return 'v1:' . base64_encode($nonce . $tag . $geheimtext);
}

/**
 * Entschlüsselt einen mit krypto_verschluesseln() erzeugten Wert.
 * Der Authentifizierungs-Tag erkennt jede Manipulation am Geheimtext.
 *
 * @throws RuntimeException bei unbekanntem Format oder ungültigem Tag
 */
function krypto_entschluesseln(string $wert, string $kontext = ''): string
{
    if (!str_starts_with($wert, 'v1:')) {
        throw new RuntimeException('Unbekanntes Geheimtext-Format.');
    }

    $roh = base64_decode(substr($wert, 3), true);
    if ($roh === false || strlen($roh) < 28) { // mindestens Nonce (12) + Tag (16)
        throw new RuntimeException('Geheimtext beschädigt.');
    }

    $nonce      = substr($roh, 0, 12);
    $tag        = substr($roh, 12, 16);
    $geheimtext = substr($roh, 28);

    $klartext = openssl_decrypt(
        $geheimtext,
        'aes-256-gcm',
        konfiguration_schluessel('verschluesselung'),
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
        $kontext
    );

    if ($klartext === false) {
        throw new RuntimeException('Entschlüsselung fehlgeschlagen (Tag ungültig).');
    }

    return $klartext;
}

/**
 * Normalisiert eine E-Mail-Adresse für Blind-Index und Vergleich
 * (Kleinschreibung, ohne führende/abschließende Leerzeichen).
 */
function krypto_email_normalisieren(string $email): string
{
    return mb_strtolower(trim($email), 'UTF-8');
}

/**
 * Berechnet den durchsuchbaren Blind-Index der E-Mail:
 * deterministischer HMAC-SHA256 mit Server-Schlüssel, 64 Hex-Zeichen.
 * Gleiches Verfahren bei Registrierung und Login ergibt denselben Wert.
 */
function krypto_email_blind_index(string $email): string
{
    return hash_hmac(
        'sha256',
        krypto_email_normalisieren($email),
        konfiguration_schluessel('blind_index')
    );
}

/**
 * Hasht eine IP-Adresse mit HMAC-SHA256 (Datensparsamkeit: für den
 * Einwilligungs-Nachweis genügt der Hash, die IP selbst wird nie gespeichert).
 */
function krypto_ip_hash(string $ip): string
{
    return hash_hmac('sha256', $ip, konfiguration_schluessel('ip_hash'));
}
