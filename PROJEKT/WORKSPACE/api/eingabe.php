<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: HTTP-Anfrage einlesen und Grundregeln durchsetzen
 * (erlaubte Methode, JSON-Körper mit Größenlimit, geprüfte Client-IP).
 */

require_once __DIR__ . '/antwort.php';

/** Obergrenze für Anfragekörper; alle Endpunkte kommen mit weit weniger aus. */
const EINGABE_MAX_BYTES = 65536;

/**
 * Erzwingt die HTTP-Methode des Endpunkts; sonst 405 mit Allow-Header.
 */
function eingabe_methode_erzwingen(string $methode): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $methode) {
        header('Allow: ' . $methode . ', OPTIONS');
        antwort_fehler(405, 'methode_nicht_erlaubt', 'Dieser Endpunkt erlaubt nur ' . $methode . '.');
    }
}

/**
 * Liest den JSON-Körper der Anfrage und liefert ihn als Array.
 * Bei fehlendem, zu großem oder ungültigem JSON endet die Anfrage mit 400/413.
 *
 * @return array<string, mixed>
 */
function eingabe_json(): array
{
    $roh = file_get_contents('php://input');

    if (!is_string($roh) || $roh === '') {
        antwort_fehler(400, 'ungueltige_anfrage', 'Es wird ein JSON-Körper erwartet.');
    }
    if (strlen($roh) > EINGABE_MAX_BYTES) {
        antwort_fehler(413, 'anfrage_zu_gross', 'Der Anfragekörper ist zu groß.');
    }

    $daten = json_decode($roh, true);
    if (!is_array($daten)) {
        antwort_fehler(400, 'ungueltiges_json', 'Der Anfragekörper ist kein gültiges JSON-Objekt.');
    }

    return $daten;
}

/**
 * Liefert die geprüfte Client-IP oder null (z. B. außerhalb eines Webrequests).
 * Es wird bewusst nur REMOTE_ADDR genutzt — Header wie X-Forwarded-For
 * sind durch Clients fälschbar.
 */
function eingabe_client_ip(): ?string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return null;
    }

    return $ip;
}
