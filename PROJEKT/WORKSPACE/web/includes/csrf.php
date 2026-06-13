<?php

declare(strict_types=1);

/**
 * Aufgabe: CSRF-Schutz für alle Formulare der SMU-Web-UI.
 */

/** Gibt das CSRF-Token der aktuellen Session zurück (erzeugt es bei Bedarf). */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

/** Gibt ein verstecktes HTML-Input-Feld mit dem CSRF-Token aus. */
function csrf_feld(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/** Prüft das gesendete CSRF-Token — bricht mit 403 ab falls ungültig. */
function csrf_pruefen(): void
{
    $gesendet = $_POST['csrf_token'] ?? '';
    if (!is_string($gesendet) || !hash_equals(csrf_token(), $gesendet)) {
        http_response_code(403);
        exit('Ungültige Anfrage (CSRF).');
    }
}
