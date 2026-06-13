<?php

declare(strict_types=1);

/**
 * Aufgabe: minimaler, authentifizierter SMTP-Versand (ohne Fremdpakete).
 *
 * Nutzt eine direkte SSL-Verbindung (Port 465) + AUTH LOGIN. Zugangsdaten
 * kommen aus konfiguration()['smtp']. Versand ist best-effort: Fehler werden
 * protokolliert und als false zurückgegeben, nie als Ausnahme nach außen.
 */

require_once __DIR__ . '/konfiguration.php';

/**
 * Sendet eine E-Mail (Plaintext). Gibt true bei erfolgreicher Übergabe zurück.
 */
function smu_mail_senden(string $anEmail, string $betreff, string $textKoerper): bool
{
    $cfg = konfiguration()['smtp'] ?? null;
    if (!is_array($cfg)) {
        error_log('SMU-Mail: keine SMTP-Konfiguration vorhanden.');
        return false;
    }

    $host     = (string) ($cfg['host'] ?? '');
    $port     = (int) ($cfg['port'] ?? 465);
    $benutzer = (string) ($cfg['benutzer'] ?? '');
    $passwort = (string) ($cfg['passwort'] ?? '');
    $absender = (string) ($cfg['absender'] ?? $benutzer);
    $name     = (string) ($cfg['absender_name'] ?? 'Shape Miner');

    if ($host === '' || $benutzer === '' || $passwort === '') {
        error_log('SMU-Mail: SMTP-Konfiguration unvollständig.');
        return false;
    }

    $kontext = stream_context_create([
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $verbindung = @stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $fehlerNr,
        $fehlerText,
        15,
        STREAM_CLIENT_CONNECT,
        $kontext
    );

    if ($verbindung === false) {
        error_log('SMU-Mail: Verbindung fehlgeschlagen: ' . $fehlerText);
        return false;
    }
    stream_set_timeout($verbindung, 15);

    // Hilfsfunktionen für den SMTP-Dialog (erwarteter Antwortcode-Präfix).
    $lesen = static function () use ($verbindung): string {
        $antwort = '';
        while (($zeile = fgets($verbindung, 515)) !== false) {
            $antwort .= $zeile;
            // Mehrzeilige Antworten: "250-..." gefolgt von "250 ..."
            if (isset($zeile[3]) && $zeile[3] === ' ') {
                break;
            }
        }
        return $antwort;
    };
    $senden = static function (string $befehl) use ($verbindung): void {
        fwrite($verbindung, $befehl . "\r\n");
    };
    $erwarte = static function (string $antwort, string $code): bool {
        return str_starts_with(ltrim($antwort), $code);
    };

    $erfolg = false;
    try {
        if (!$erwarte($lesen(), '220')) { throw new RuntimeException('kein 220'); }

        $senden('EHLO ' . ($host));
        if (!$erwarte($lesen(), '250')) { throw new RuntimeException('EHLO abgelehnt'); }

        $senden('AUTH LOGIN');
        if (!$erwarte($lesen(), '334')) { throw new RuntimeException('AUTH LOGIN abgelehnt'); }
        $senden(base64_encode($benutzer));
        if (!$erwarte($lesen(), '334')) { throw new RuntimeException('Benutzer abgelehnt'); }
        $senden(base64_encode($passwort));
        if (!$erwarte($lesen(), '235')) { throw new RuntimeException('Authentifizierung fehlgeschlagen'); }

        $senden('MAIL FROM:<' . $absender . '>');
        if (!$erwarte($lesen(), '250')) { throw new RuntimeException('MAIL FROM abgelehnt'); }
        $senden('RCPT TO:<' . $anEmail . '>');
        if (!$erwarte($lesen(), '250')) { throw new RuntimeException('RCPT TO abgelehnt'); }

        $senden('DATA');
        if (!$erwarte($lesen(), '354')) { throw new RuntimeException('DATA abgelehnt'); }

        // Header + Body. Punkt-Zeilen werden gepunktet (Transparenz nach RFC 5321).
        $betreffKod = '=?UTF-8?B?' . base64_encode($betreff) . '?=';
        $absenderKopf = '=?UTF-8?B?' . base64_encode($name) . '?= <' . $absender . '>';
        $nachricht =
            'From: ' . $absenderKopf . "\r\n" .
            'To: <' . $anEmail . ">\r\n" .
            'Subject: ' . $betreffKod . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-Type: text/plain; charset=UTF-8' . "\r\n" .
            'Content-Transfer-Encoding: base64' . "\r\n" .
            "\r\n" .
            chunk_split(base64_encode($textKoerper));

        // "." am Zeilenanfang verdoppeln (dot-stuffing).
        $nachricht = preg_replace('/^\./m', '..', $nachricht);
        $senden($nachricht);
        $senden('.');
        if (!$erwarte($lesen(), '250')) { throw new RuntimeException('Nachricht abgelehnt'); }

        $senden('QUIT');
        $erfolg = true;
    } catch (Throwable $e) {
        error_log('SMU-Mail: SMTP-Fehler: ' . $e->getMessage());
        $erfolg = false;
    } finally {
        fclose($verbindung);
    }

    return $erfolg;
}
