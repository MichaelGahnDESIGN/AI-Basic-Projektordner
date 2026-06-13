<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: gemeinsamer Einstieg aller Endpunkte.
 *
 *  - PHP-Fehler und Exceptions werden zu sauberen JSON-Antworten ohne
 *    interne Details; Originalfehler landen ausschließlich im Server-Log.
 *  - Setzt JSON- und Sicherheits-Header.
 *  - Behandelt CORS inklusive OPTIONS-Preflight für den Flutter-Web-Client.
 */

require_once __DIR__ . '/konfiguration.php';
require_once __DIR__ . '/antwort.php';

// Fehlerdetails gehören ins Server-Log, niemals in die HTTP-Antwort.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// PHP-Warnungen/-Notices als Exceptions behandeln, damit nichts unbemerkt bleibt.
set_error_handler(
    function (int $stufe, string $nachricht, string $datei = '', int $zeile = 0): bool {
        throw new ErrorException($nachricht, 0, $stufe, $datei, $zeile);
    }
);

// Letzte Auffanginstanz: generische 500er-Antwort, Details nur ins Log.
set_exception_handler(
    function (Throwable $fehler): void {
        error_log(sprintf(
            '[shape-miner-api] %s: %s in %s:%d',
            get_class($fehler),
            $fehler->getMessage(),
            $fehler->getFile(),
            $fehler->getLine()
        ));
        antwort_fehler(500, 'interner_fehler', 'Interner Serverfehler.');
    }
);

/**
 * Setzt Antwort-Header (JSON, Sicherheit, CORS) und beantwortet Preflights.
 */
function bootstrap_initialisieren(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');

    // CORS: Nur in der Konfiguration freigegebene Origins erhalten Zugriff.
    // Mobile Apps senden keinen Origin-Header und brauchen kein CORS.
    $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
    $erlaubt = konfiguration()['cors_erlaubte_origins'];

    if ($origin !== '' && (in_array($origin, $erlaubt, true) || in_array('*', $erlaubt, true))) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 86400');
    }

    // Preflight-Anfragen sind hier vollständig beantwortet.
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

bootstrap_initialisieren();
