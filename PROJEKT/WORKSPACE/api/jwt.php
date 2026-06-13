<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: minimale, eigene JWT-Implementierung (nur HS256).
 *
 * Bewusst ohne Fremdpakete. Sicherheitsentscheidungen:
 *  - Es wird ausschließlich HS256 akzeptiert (kein "alg: none",
 *    keine Algorithmus-Verwechslungsangriffe).
 *  - Signaturvergleich mit hash_equals() (konstante Zeit).
 *  - Ablauf (exp) und Aussteller (iss) werden immer geprüft.
 */

require_once __DIR__ . '/konfiguration.php';

/**
 * Kodiert Binärdaten als Base64-URL (RFC 7515, ohne Padding).
 */
function jwt_base64url_kodieren(string $daten): string
{
    return rtrim(strtr(base64_encode($daten), '+/', '-_'), '=');
}

/**
 * Dekodiert Base64-URL; liefert false bei ungültiger Eingabe.
 */
function jwt_base64url_dekodieren(string $daten): string|false
{
    $rest = strlen($daten) % 4;
    if ($rest > 0) {
        $daten .= str_repeat('=', 4 - $rest);
    }
    return base64_decode(strtr($daten, '-_', '+/'), true);
}

/**
 * Liefert das binäre HS256-Geheimnis aus der Konfiguration.
 *
 * @throws RuntimeException wenn das Geheimnis fehlt oder zu kurz ist
 */
function jwt_geheimnis(): string
{
    $geheimnis = base64_decode((string) konfiguration()['jwt']['geheimnis'], true);

    if ($geheimnis === false || strlen($geheimnis) < 32) {
        throw new RuntimeException('JWT-Geheimnis fehlt oder ist zu kurz (mind. 32 Byte, Base64).');
    }

    return $geheimnis;
}

/**
 * Erstellt ein signiertes JWT für einen Benutzer.
 *
 * Nutzdaten: iss (Aussteller), sub (User-ID als String), rolle,
 *            iat (ausgestellt) und exp (Ablauf).
 */
function jwt_erstellen(int $userId, string $rolle): string
{
    $jwtKonfiguration = konfiguration()['jwt'];
    $jetzt = time();

    $kopf = ['alg' => 'HS256', 'typ' => 'JWT'];
    $nutzdaten = [
        'iss'   => (string) $jwtKonfiguration['aussteller'],
        'sub'   => (string) $userId,
        'rolle' => $rolle,
        'iat'   => $jetzt,
        'exp'   => $jetzt + (int) $jwtKonfiguration['gueltigkeit_sekunden'],
    ];

    $kopfTeil  = jwt_base64url_kodieren((string) json_encode($kopf, JSON_UNESCAPED_UNICODE));
    $datenTeil = jwt_base64url_kodieren((string) json_encode($nutzdaten, JSON_UNESCAPED_UNICODE));
    $signatur  = hash_hmac('sha256', $kopfTeil . '.' . $datenTeil, jwt_geheimnis(), true);

    return $kopfTeil . '.' . $datenTeil . '.' . jwt_base64url_kodieren($signatur);
}

/**
 * Prüft ein JWT vollständig: Format, Algorithmus, Signatur, Ablauf, Aussteller.
 *
 * @return array<string, mixed>|null Nutzdaten bei Erfolg, sonst null
 */
function jwt_pruefen(string $token): ?array
{
    $teile = explode('.', $token);
    if (count($teile) !== 3) {
        return null;
    }
    [$kopfTeil, $datenTeil, $signaturTeil] = $teile;

    $kopfRoh  = jwt_base64url_dekodieren($kopfTeil);
    $datenRoh = jwt_base64url_dekodieren($datenTeil);
    $signatur = jwt_base64url_dekodieren($signaturTeil);
    if ($kopfRoh === false || $datenRoh === false || $signatur === false) {
        return null;
    }

    $kopf      = json_decode($kopfRoh, true);
    $nutzdaten = json_decode($datenRoh, true);
    if (!is_array($kopf) || !is_array($nutzdaten)) {
        return null;
    }

    // Nur exakt HS256/JWT zulassen — alles andere wird abgelehnt.
    if (($kopf['alg'] ?? '') !== 'HS256' || ($kopf['typ'] ?? '') !== 'JWT') {
        return null;
    }

    // Signatur in konstanter Zeit vergleichen.
    $erwartet = hash_hmac('sha256', $kopfTeil . '.' . $datenTeil, jwt_geheimnis(), true);
    if (!hash_equals($erwartet, $signatur)) {
        return null;
    }

    // Ablauf prüfen (exp ist Pflicht).
    $jetzt = time();
    if (!isset($nutzdaten['exp']) || !is_int($nutzdaten['exp']) || $nutzdaten['exp'] < $jetzt) {
        return null;
    }

    // Token aus der Zukunft ablehnen (kleine Toleranz für Uhrenabweichung).
    if (isset($nutzdaten['iat']) && is_int($nutzdaten['iat']) && $nutzdaten['iat'] > $jetzt + 60) {
        return null;
    }

    // Aussteller muss zu dieser API gehören.
    if (($nutzdaten['iss'] ?? '') !== (string) konfiguration()['jwt']['aussteller']) {
        return null;
    }

    return $nutzdaten;
}
