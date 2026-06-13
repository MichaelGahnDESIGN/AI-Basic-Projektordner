<?php

declare(strict_types=1);

/**
 * Endpunkt: GET /zwei_faktor_status.php  (JWT-geschützt)
 *
 * Liefert, ob die Zwei-Faktor-Authentifizierung (TOTP) für das Konto
 * des angemeldeten Benutzers aktiviert ist. Das Secret selbst verlässt
 * den Server dabei nie.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

eingabe_methode_erzwingen('GET');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);

// --- 1. Status laden ---------------------------------------------------------------
$pdo = db_verbindung();
$abfrage = $pdo->prepare('SELECT totp_aktiviert FROM users WHERE id = :id');
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}

antwort_ok(['aktiviert' => (int) $benutzer['totp_aktiviert'] === 1]);
