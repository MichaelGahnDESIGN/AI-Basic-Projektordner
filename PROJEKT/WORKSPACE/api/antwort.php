<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: einheitliche JSON-Antworten für alle Endpunkte.
 *
 * Erfolgsformat:  { "ok": true,  "daten": { ... } }
 * Fehlerformat:   { "ok": false, "fehler": { "code", "nachricht"[, "details"] } }
 *
 * Fehlerantworten enthalten grundsätzlich keine internen Details
 * (kein Stacktrace, kein SQL, keine Pfade) — Details landen nur im Server-Log.
 */

/**
 * Sendet eine Erfolgsantwort und beendet die Verarbeitung.
 *
 * @param array<string, mixed> $daten Nutzdaten für den Client
 * @param int                  $status HTTP-Statuscode (Standard 200)
 */
function antwort_ok(array $daten, int $status = 200): never
{
    antwort_senden($status, ['ok' => true, 'daten' => $daten]);
}

/**
 * Sendet eine Fehlerantwort und beendet die Verarbeitung.
 *
 * @param int                        $status    HTTP-Statuscode
 * @param string                     $code      maschinenlesbarer Fehlercode
 * @param string                     $nachricht menschenlesbare Meldung (de)
 * @param array<string, string>|null $details   feldbezogene Hinweise für Formulare
 */
function antwort_fehler(int $status, string $code, string $nachricht, ?array $details = null): never
{
    $fehler = ['code' => $code, 'nachricht' => $nachricht];
    if ($details !== null && $details !== []) {
        $fehler['details'] = $details;
    }
    antwort_senden($status, ['ok' => false, 'fehler' => $fehler]);
}

/**
 * Serialisiert und sendet die Antwort als UTF-8-JSON (Umlaute bleiben lesbar).
 *
 * @param array<string, mixed> $inhalt
 */
function antwort_senden(int $status, array $inhalt): never
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);
    }
    echo json_encode($inhalt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
