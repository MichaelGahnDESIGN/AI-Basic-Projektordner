<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: Zugriffsschutz für Endpunkte, die ein gültiges
 * JWT verlangen (Header "Authorization: Bearer <token>").
 */

require_once __DIR__ . '/antwort.php';
require_once __DIR__ . '/jwt.php';

/**
 * Erzwingt ein gültiges Bearer-Token und liefert dessen Nutzdaten.
 * Bei fehlendem oder ungültigem Token endet die Anfrage mit 401.
 *
 * @return array<string, mixed> JWT-Nutzdaten (u. a. 'sub' = User-ID)
 */
function auth_erzwingen(): array
{
    $kopf = auth_authorization_header();

    if (preg_match('/^Bearer\s+(\S+)$/i', $kopf, $treffer) !== 1) {
        antwort_fehler(401, 'nicht_angemeldet', 'Es wird ein Bearer-Token im Authorization-Header erwartet.');
    }

    $nutzdaten = jwt_pruefen($treffer[1]);
    if ($nutzdaten === null) {
        antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
    }

    return $nutzdaten;
}

/**
 * Liest den Authorization-Header robust über die je nach Server-Setup
 * unterschiedlichen Quellen (FastCGI, Apache, Rewrite).
 */
function auth_authorization_header(): string
{
    $kopf = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($kopf === '' && function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $name => $wert) {
            if (strcasecmp((string) $name, 'Authorization') === 0) {
                $kopf = (string) $wert;
                break;
            }
        }
    }

    return is_string($kopf) ? $kopf : '';
}
