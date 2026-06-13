<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /profil_aktualisieren.php  (JWT-geschützt)
 *
 * Aktualisiert Profilfelder des angemeldeten Benutzers:
 *  - alle Felder optional: vorname, nachname, benutzername,
 *  - Vor- und Nachname werden verschlüsselt gespeichert (AES-256-GCM),
 *  - der Benutzername muss eindeutig bleiben (sonst 409).
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/validierung.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/sperrbegriffe.php';

eingabe_methode_erzwingen('POST');
$nutzdaten = auth_erzwingen();
$userId = (int) ($nutzdaten['sub'] ?? 0);
$eingabe = eingabe_json();

// --- 1. Übergebene Felder prüfen (nur vorhandene werden geändert) ---------------
$probleme = [];
$spalten  = []; // Spaltenname => neuer (ggf. verschlüsselter) Wert
$benutzername = null;

if (array_key_exists('vorname', $eingabe)) {
    $vorname = validierung_text($eingabe['vorname'], 1, 100);
    if ($vorname === null) {
        $probleme['vorname'] = '1 bis 100 Zeichen.';
    } else {
        $spalten['vorname_enc'] = krypto_verschluesseln($vorname, KRYPTO_KONTEXT_VORNAME);
    }
}

if (array_key_exists('nachname', $eingabe)) {
    $nachname = validierung_text($eingabe['nachname'], 1, 100);
    if ($nachname === null) {
        $probleme['nachname'] = '1 bis 100 Zeichen.';
    } else {
        $spalten['nachname_enc'] = krypto_verschluesseln($nachname, KRYPTO_KONTEXT_NACHNAME);
    }
}

if (array_key_exists('benutzername', $eingabe)) {
    $benutzername = validierung_benutzername($eingabe['benutzername']);
    if ($benutzername === null) {
        $probleme['benutzername'] = '3 bis 30 Zeichen: Buchstaben, Ziffern, Unterstrich.';
    } else {
        $spalten['benutzername'] = $benutzername;
    }
}

if ($probleme !== []) {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben sind unvollständig oder ungültig.', $probleme);
}
if ($spalten === []) {
    antwort_fehler(422, 'validierung_fehlgeschlagen', 'Es wurde kein Feld zum Aktualisieren übergeben.');
}

// --- 2. Konto laden ---------------------------------------------------------------
$pdo = db_verbindung();
$abfrage = $pdo->prepare('SELECT id, benutzername, sprache, rolle, aktiv FROM users WHERE id = :id');
$abfrage->execute(['id' => $userId]);
$benutzer = $abfrage->fetch();

if ($benutzer === false) {
    antwort_fehler(401, 'token_ungueltig', 'Das Token ist ungültig oder abgelaufen.');
}
if ((int) $benutzer['aktiv'] !== 1) {
    antwort_fehler(403, 'konto_gesperrt', 'Dieses Konto ist deaktiviert.');
}

// --- 3. Eindeutigkeit + Sperrbegriffe des neuen Benutzernamens prüfen ----------------
if ($benutzername !== null) {
    if (sperrbegriff_verletzt($pdo, $benutzername) !== null) {
        antwort_fehler(422, 'name_nicht_erlaubt', 'Eingaben sind unvollständig oder ungültig.', [
            'benutzername' => 'Dieser Name enthält einen nicht erlaubten Begriff.',
        ]);
    }
    $pruefung = $pdo->prepare('SELECT id FROM users WHERE benutzername = :benutzername AND id <> :id');
    $pruefung->execute(['benutzername' => $benutzername, 'id' => $userId]);
    if ($pruefung->fetch() !== false) {
        antwort_fehler(409, 'benutzername_vergeben', 'Dieser Benutzername ist bereits vergeben.');
    }
}

// --- 4. Aktualisieren (Spaltenliste stammt ausschließlich aus dem Code oben) ---------
$zuweisungen = [];
foreach (array_keys($spalten) as $spalte) {
    $zuweisungen[] = $spalte . ' = :' . $spalte;
}

try {
    $aktualisieren = $pdo->prepare(
        'UPDATE users SET ' . implode(', ', $zuweisungen) . ' WHERE id = :id'
    );
    $aktualisieren->execute($spalten + ['id' => $userId]);
} catch (PDOException $fehler) {
    // Wettlauf mit parallelem Update: Unique-Constraint hat das letzte Wort.
    if ((string) $fehler->getCode() === '23000') {
        antwort_fehler(409, 'benutzername_vergeben', 'Dieser Benutzername ist bereits vergeben.');
    }
    throw $fehler;
}

// --- 5. Aktualisiertes Profil (öffentliche Felder) zurückgeben -----------------------
antwort_ok([
    'benutzer' => [
        'id'           => $userId,
        'benutzername' => $benutzername ?? $benutzer['benutzername'],
        'sprache'      => $benutzer['sprache'],
        'rolle'        => $benutzer['rolle'],
    ],
]);
