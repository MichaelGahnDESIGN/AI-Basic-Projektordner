<?php

declare(strict_types=1);

/**
 * Aufgabe dieser Datei: Konfiguration finden, laden und prüfen.
 *
 * Die echte `config.php` enthält Geheimnisse und liegt NIEMALS im Repository.
 * Suchreihenfolge:
 *   1. Pfad aus der Umgebungsvariablen `SMU_CONFIG_PFAD` (empfohlen:
 *      Datei außerhalb des Webroots, z. B. /www/htdocs/w021b1e1/smu-konfiguration/).
 *   2. `../config.php` — eine Ebene über dem api-Ordner. Ist der api-Ordner
 *      selbst der Webroot (z. B. Subdomain api.example.de), liegt dieser
 *      Pfad bereits außerhalb des Webroots.
 *   3. `./config.php` — nur für lokale Entwicklung (gitignoriert).
 */

/**
 * Lädt die Konfiguration genau einmal pro Request und liefert sie als Array.
 *
 * @return array<string, mixed>
 * @throws RuntimeException wenn keine vollständige Konfiguration gefunden wird
 */
function konfiguration(): array
{
    static $geladen = null;

    if (is_array($geladen)) {
        return $geladen;
    }

    foreach (konfiguration_kandidaten() as $pfad) {
        if ($pfad === '' || !is_readable($pfad)) {
            continue;
        }
        $inhalt = require $pfad;
        if (is_array($inhalt)) {
            konfiguration_pruefen($inhalt);
            $geladen = $inhalt;
            return $geladen;
        }
    }

    throw new RuntimeException(
        'Keine config.php gefunden. Vorlage: config.sample.php; Pfad per SMU_CONFIG_PFAD setzen.'
    );
}

/**
 * Liefert die möglichen Speicherorte der Konfigurationsdatei in Prüfreihenfolge.
 *
 * @return list<string>
 */
function konfiguration_kandidaten(): array
{
    $kandidaten = [];

    // 1. Umgebungsvariable (funktioniert auch via "SetEnv" in der .htaccess)
    $umgebungspfad = getenv('SMU_CONFIG_PFAD');
    if (!is_string($umgebungspfad) || $umgebungspfad === '') {
        $serverWert = $_SERVER['SMU_CONFIG_PFAD'] ?? '';
        $umgebungspfad = is_string($serverWert) ? $serverWert : '';
    }
    if ($umgebungspfad !== '') {
        $kandidaten[] = $umgebungspfad;
    }

    // 2. Eine Ebene über dem api-Ordner (außerhalb des Webroots, wenn
    //    der api-Ordner der Webroot ist)
    $kandidaten[] = dirname(__DIR__) . '/config.php';

    // 3. Im api-Ordner selbst — nur lokale Entwicklung, gitignoriert
    $kandidaten[] = __DIR__ . '/config.php';

    return $kandidaten;
}

/**
 * Prüft, ob alle Pflichteinträge vorhanden und keine Platzhalter mehr sind.
 * In Fehlermeldungen werden bewusst nur Schlüsselnamen genannt, niemals Werte.
 *
 * @param array<string, mixed> $inhalt
 */
function konfiguration_pruefen(array $inhalt): void
{
    $pflicht = [
        ['db', 'host'],
        ['db', 'name'],
        ['db', 'benutzer'],
        ['db', 'passwort'],
        ['schluessel', 'verschluesselung'],
        ['schluessel', 'blind_index'],
        ['schluessel', 'ip_hash'],
        ['jwt', 'geheimnis'],
        ['jwt', 'aussteller'],
        ['jwt', 'gueltigkeit_sekunden'],
        ['cors_erlaubte_origins'],
    ];

    foreach ($pflicht as $pfad) {
        $wert = $inhalt;
        foreach ($pfad as $schluessel) {
            if (!is_array($wert) || !array_key_exists($schluessel, $wert)) {
                throw new RuntimeException(
                    'Konfiguration unvollständig: "' . implode('.', $pfad) . '" fehlt.'
                );
            }
            $wert = $wert[$schluessel];
        }
        if ($wert === '' || (is_string($wert) && str_starts_with($wert, 'PLATZHALTER'))) {
            throw new RuntimeException(
                'Konfiguration unvollständig: "' . implode('.', $pfad) . '" ist noch ein Platzhalter.'
            );
        }
    }

    if (!is_array($inhalt['cors_erlaubte_origins'])) {
        throw new RuntimeException('Konfiguration: "cors_erlaubte_origins" muss ein Array sein.');
    }
}

/**
 * Liefert einen kryptografischen Schlüssel (32 Byte, binär) aus der Konfiguration.
 *
 * @param string $name 'verschluesselung' | 'blind_index' | 'ip_hash'
 * @throws RuntimeException wenn der Schlüssel fehlt oder das falsche Format hat
 */
function konfiguration_schluessel(string $name): string
{
    $eintraege = konfiguration()['schluessel'];
    $roh = base64_decode((string) ($eintraege[$name] ?? ''), true);

    if ($roh === false || strlen($roh) !== 32) {
        throw new RuntimeException(
            'Schlüssel "' . $name . '" fehlt oder ist kein Base64-Wert mit 32 Byte.'
        );
    }

    return $roh;
}
