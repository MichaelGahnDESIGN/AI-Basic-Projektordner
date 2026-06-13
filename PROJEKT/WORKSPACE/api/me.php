<?php

declare(strict_types=1);

/**
 * Endpunkt: GET /me.php
 *
 * Liefert das Profil des angemeldeten Benutzers:
 *  - verlangt ein gültiges JWT (Header "Authorization: Bearer <token>"),
 *  - entschlüsselt die personenbezogenen Felder erst zur Auslieferung,
 *  - liefert zusätzlich alle protokollierten Einwilligungen, damit der
 *    Client bei neuen AGB-/Datenschutz-Versionen erneut fragen kann.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/auth.php';

eingabe_methode_erzwingen('GET');

$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);

// --- 1. Konto laden -------------------------------------------------------------
$pdo = db_verbindung();
$abfrage = $pdo->prepare(
    'SELECT id, email_enc, vorname_enc, nachname_enc, benutzername,
            sprache, rolle, aktiv, erstellt_am
     FROM users
     WHERE id = :id'
);
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    // Konto wurde zwischenzeitlich gelöscht — das Token ist damit wertlos.
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}

if ((int) $benutzer['aktiv'] !== 1) {
    antwort_fehler(403, 'konto_gesperrt', 'Dieses Konto ist deaktiviert.');
}

// --- 2. Einwilligungen laden ------------------------------------------------------
$einwilligungen = $pdo->prepare(
    'SELECT typ, version, zeitstempel
     FROM einwilligungen
     WHERE user_id = :user_id
     ORDER BY zeitstempel ASC, id ASC'
);
$einwilligungen->execute(['user_id' => $userId]);

// --- 3. Profil entschlüsselt ausliefern ---------------------------------------------
antwort_ok([
    'benutzer' => [
        'id'           => (int) $benutzer['id'],
        'email'        => krypto_entschluesseln((string) $benutzer['email_enc'], KRYPTO_KONTEXT_EMAIL),
        'vorname'      => krypto_entschluesseln((string) $benutzer['vorname_enc'], KRYPTO_KONTEXT_VORNAME),
        'nachname'     => krypto_entschluesseln((string) $benutzer['nachname_enc'], KRYPTO_KONTEXT_NACHNAME),
        'benutzername' => $benutzer['benutzername'],
        'sprache'      => $benutzer['sprache'],
        'rolle'        => $benutzer['rolle'],
        'erstellt_am'  => $benutzer['erstellt_am'],
    ],
    'einwilligungen' => $einwilligungen->fetchAll(),
]);
