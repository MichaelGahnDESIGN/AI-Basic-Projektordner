<?php

declare(strict_types=1);

/**
 * Aufgabe: Rechtstexte aus dem zentralen Editor (DELUXE) laden.
 *
 * Single Source of Truth ist die rechtstexte-Tabelle im Shape-Miner-Editor.
 * SMU liest sie über den öffentlichen Endpunkt deluxe.shapeminer.com/api/rechtstexte.php
 * und cached die Antwort kurz (Datei-Cache), damit nicht jeder Seitenaufruf
 * einen HTTP-Request auslöst. Bearbeitet man die Texte im Editor + veröffentlicht,
 * erscheinen sie nach Cache-Ablauf automatisch auch hier.
 */

const SMU_RECHTSTEXTE_URL       = 'https://deluxe.shapeminer.com/api/rechtstexte.php';
const SMU_RECHTSTEXTE_CACHE_TTL = 600; // Sekunden (10 min)

/**
 * Liefert alle Rechtstexte als Liste (aus Cache oder frisch geladen).
 *
 * @return list<array{typ:string,sprache:string,titel:string,inhalt:string,version:string}>
 */
function smu_rechtstexte_alle(): array
{
    static $geladen = null;
    if (is_array($geladen)) {
        return $geladen;
    }

    $cacheDatei = sys_get_temp_dir() . '/smu_rechtstexte_cache.json';
    $frisch = null;

    // 1. Gültigen Cache verwenden, wenn vorhanden und nicht abgelaufen.
    if (is_readable($cacheDatei)
        && (time() - (int) @filemtime($cacheDatei)) < SMU_RECHTSTEXTE_CACHE_TTL) {
        $frisch = json_decode((string) @file_get_contents($cacheDatei), true);
    }

    // 2. Sonst frisch laden.
    if (!is_array($frisch)) {
        $roh = @file_get_contents(SMU_RECHTSTEXTE_URL, false, stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
            'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
        ]));
        $json = is_string($roh) ? json_decode($roh, true) : null;

        if (is_array($json) && ($json['ok'] ?? false) && isset($json['daten']['rechtstexte'])) {
            $frisch = $json['daten']['rechtstexte'];
            @file_put_contents($cacheDatei, json_encode($frisch));
        } elseif (is_readable($cacheDatei)) {
            // Netz-/Serverfehler: notfalls veralteten Cache nehmen (stale-if-error).
            $frisch = json_decode((string) @file_get_contents($cacheDatei), true);
        }
    }

    $geladen = is_array($frisch) ? $frisch : [];
    return $geladen;
}

/**
 * Liefert einen einzelnen Rechtstext (typ + sprache) oder null.
 *
 * @return array{titel:string,inhalt:string,version:string}|null
 */
function smu_rechtstext(string $typ, string $sprache = 'de'): ?array
{
    foreach (smu_rechtstexte_alle() as $t) {
        if (($t['typ'] ?? '') === $typ && ($t['sprache'] ?? '') === $sprache) {
            return [
                'titel'   => (string) ($t['titel'] ?? ''),
                'inhalt'  => (string) ($t['inhalt'] ?? ''),
                'version' => (string) ($t['version'] ?? ''),
            ];
        }
    }
    return null;
}

/**
 * Rendert einen Rechtstext-Inhalt sicher als HTML (Absätze + Zeilenumbrüche).
 */
function smu_rechtstext_html(string $inhalt): string
{
    $sicher = htmlspecialchars($inhalt, ENT_QUOTES, 'UTF-8');
    // Doppelte Umbrüche → Absätze, einfache → <br>.
    $absaetze = preg_split('/\n\s*\n/', trim($sicher)) ?: [];
    $html = '';
    foreach ($absaetze as $absatz) {
        $html .= '<p>' . nl2br(trim($absatz)) . '</p>';
    }
    return $html;
}
