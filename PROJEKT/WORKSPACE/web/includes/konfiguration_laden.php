<?php

declare(strict_types=1);

/**
 * Aufgabe: SMU-Konfiguration + API-Pfad-Konstante bereitstellen.
 *
 * SMU_API_PFAD wird einmal ermittelt und als Konstante gesetzt.
 * Beide Umgebungen funktionieren ohne Code-Änderung:
 *   - Server: api/ liegt im Webroot-Unterordner neben web-Dateien
 *   - Lokal:  api/ liegt als Geschwisterordner neben web/
 *
 * Config-Suchreihenfolge:
 *   1. Pfad aus Umgebungsvariable SMU_CONFIG_PFAD  (empfohlen: Server)
 *   2. SMU_API_PFAD/config.php                    (lokale Entwicklung)
 */

// --- Härtung: keine internen Details an den Browser -----------------------
// Die Web-UI hat (anders als die API) keinen bootstrap.php; deshalb hier
// zentral: Fehler nur protokollieren, nie ausgeben, und unbehandelte
// Exceptions zu einer generischen Fehlerseite machen (kein Stacktrace-Leak).
if (!defined('SMU_WEB_BOOTSTRAP')) {
    define('SMU_WEB_BOOTSTRAP', true);

    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);

    // Sicherheits-Header (nur wenn noch nichts gesendet wurde).
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // CSP: nur eigene Quellen; QR-Code wird lokal (data:) erzeugt.
        header("Content-Security-Policy: default-src 'self'; "
            . "img-src 'self' data:; style-src 'self' 'unsafe-inline'; "
            . "script-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
        // HSTS nur über HTTPS senden.
        if (($_SERVER['HTTPS'] ?? '') === 'on'
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    set_exception_handler(static function (Throwable $e): void {
        error_log('SMU-Web: unbehandelte Exception: ' . $e->getMessage()
            . ' @ ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!doctype html><meta charset="utf-8"><title>Fehler</title>'
            . '<body style="background:#05060E;color:#E6F0FF;font-family:system-ui;'
            . 'display:flex;min-height:100vh;align-items:center;justify-content:center;text-align:center">'
            . '<div><h1 style="color:#66E0FF">Ein Fehler ist aufgetreten</h1>'
            . '<p>Bitte versuche es später erneut.</p>'
            . '<p><a href="/anmelden" style="color:#66E0FF">Zur Anmeldung</a></p></div>';
    });
}

if (!defined('SMU_API_PFAD')) {
    // Webroot/api/ (Server) hat Vorrang; fällt zurück auf WORKSPACE/api/ (lokal)
    $smuApiPfad = is_file(dirname(__DIR__) . '/api/crypto.php')
        ? dirname(__DIR__) . '/api'
        : dirname(__DIR__, 2) . '/api';
    define('SMU_API_PFAD', $smuApiPfad);
    unset($smuApiPfad);
}

function smu_konfiguration(): array
{
    static $geladen = null;

    if (is_array($geladen)) {
        return $geladen;
    }

    $pfade = [];

    $env = getenv('SMU_CONFIG_PFAD') ?: ($_SERVER['SMU_CONFIG_PFAD'] ?? '');
    if ($env !== '') {
        $pfade[] = (string) $env;
    }
    $pfade[] = SMU_API_PFAD . '/config.php';

    foreach ($pfade as $pfad) {
        if (is_readable($pfad)) {
            $inhalt = require $pfad;
            if (is_array($inhalt)) {
                $geladen = $inhalt;
                return $geladen;
            }
        }
    }

    throw new RuntimeException('SMU config.php nicht gefunden. SMU_CONFIG_PFAD prüfen.');
}
