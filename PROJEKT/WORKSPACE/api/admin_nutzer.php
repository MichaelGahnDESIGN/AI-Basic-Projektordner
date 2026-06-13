<?php

declare(strict_types=1);

/**
 * Endpunkt: POST /admin_nutzer.php — zentrale Nutzerverwaltung (Server-zu-Server)
 *
 * Wird ausschließlich vom Shape-Miner-Editor (anderer All-Inkl-Account)
 * aufgerufen, nicht vom Browser. Authentifizierung über ein gemeinsames
 * Geheimnis (konfiguration()['admin_api']['geheimnis']), das im Editor und in
 * der SMU-Konfiguration identisch hinterlegt ist. Der Editor schützt den
 * Zugang zusätzlich über seine eigene Rolle (nur Admin/Moderator).
 *
 * Aktionen (Body-Feld "aktion"):
 *   liste         → alle Konten (E-Mail entschlüsselt, für Support)
 *   reset_link    → Passwort-Reset-Token erzeugen (+ optional per Mail senden)
 *   sperren       → Konto deaktivieren (aktiv = 0)
 *   freigeben     → Konto aktivieren  (aktiv = 1)
 *   aktualisieren → Benutzername/Sprache/Rolle ändern
 *   loeschen      → Konto endgültig löschen (kaskadiert)
 *
 * Rollenänderungen verlangen zusätzlich akteur_rolle = 'admin' (Moderatoren
 * dürfen keine Berechtigungen vergeben). Der letzte aktive Admin kann weder
 * gesperrt, gelöscht noch herabgestuft werden (kein Aussperren).
 *
 * Antworten sind generische JSON-Objekte (siehe antwort.php). Personenbezogene
 * Daten verlassen den Server nur über die TLS-Verbindung zum Editor.
 */

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/eingabe.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';
require_once __DIR__ . '/mail.php';

eingabe_methode_erzwingen('POST');
$eingabe = eingabe_json();

// --- 1. Gemeinsames Geheimnis prüfen (zeitkonstant) ----------------------------
$cfg     = konfiguration()['admin_api'] ?? null;
$secret  = is_array($cfg) ? (string) ($cfg['geheimnis'] ?? '') : '';
$gesendet = (string) ($eingabe['geheimnis'] ?? '');

if ($secret === '' || strlen($secret) < 16 || !hash_equals($secret, $gesendet)) {
    antwort_fehler(401, 'nicht_berechtigt', 'Zugriff verweigert.');
}

$aktion      = (string) ($eingabe['aktion'] ?? '');
$akteurRolle = (string) ($eingabe['akteur_rolle'] ?? '');   // Editor-Rolle: admin|moderator
$akteurId    = (int) ($eingabe['akteur_id'] ?? 0);          // SMU-User-ID des Editors (0 = Break-Glass)
$pdo         = db_verbindung();

/**
 * Lädt ein Konto oder beendet die Anfrage mit 404.
 *
 * @return array<string, mixed>
 */
function nutzer_holen(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT id, benutzername, email_enc, sprache, rolle, aktiv, erstellt_am
         FROM users WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $zeile = $stmt->fetch();
    if ($zeile === false) {
        antwort_fehler(404, 'nicht_gefunden', 'Konto nicht gefunden.');
    }
    return $zeile;
}

/** Zählt die aktiven Admin-Konten (Aussperr-Schutz). */
function aktive_admins(PDO $pdo): int
{
    return (int) $pdo->query(
        "SELECT COUNT(*) FROM users WHERE rolle = 'admin' AND aktiv = 1"
    )->fetchColumn();
}

/** Entschlüsselt eine E-Mail defensiv; bei Fehler '—'. */
function nutzer_email(string $enc): string
{
    try {
        return krypto_entschluesseln($enc, KRYPTO_KONTEXT_EMAIL);
    } catch (Throwable) {
        return '—';
    }
}

// --- 2. Aktionen ----------------------------------------------------------------
switch ($aktion) {

    // ---- Liste aller Konten ----------------------------------------------------
    case 'liste':
        $suche = trim((string) ($eingabe['suche'] ?? ''));

        if ($suche !== '' && filter_var($suche, FILTER_VALIDATE_EMAIL)) {
            // Exakte E-Mail → über den Blind-Index suchen (kein Klartext in DB).
            $stmt = $pdo->prepare(
                'SELECT id, benutzername, email_enc, sprache, rolle, aktiv, erstellt_am
                 FROM users WHERE email_blind_index = :bi'
            );
            $stmt->execute([':bi' => krypto_email_blind_index($suche)]);
        } elseif ($suche !== '') {
            // Sonst nach Spielername suchen (E-Mail ist verschlüsselt, nicht durchsuchbar).
            $stmt = $pdo->prepare(
                'SELECT id, benutzername, email_enc, sprache, rolle, aktiv, erstellt_am
                 FROM users WHERE benutzername LIKE :q ORDER BY erstellt_am DESC LIMIT 500'
            );
            $stmt->execute([':q' => '%' . $suche . '%']);
        } else {
            $stmt = $pdo->query(
                'SELECT id, benutzername, email_enc, sprache, rolle, aktiv, erstellt_am
                 FROM users ORDER BY erstellt_am DESC LIMIT 500'
            );
        }

        $konten = [];
        foreach ($stmt->fetchAll() as $z) {
            $konten[] = [
                'id'          => (int) $z['id'],
                'benutzername' => (string) $z['benutzername'],
                'email'       => nutzer_email((string) $z['email_enc']),
                'sprache'     => (string) $z['sprache'],
                'rolle'       => (string) $z['rolle'],
                'aktiv'       => (int) $z['aktiv'],
                'erstellt_am' => (string) $z['erstellt_am'],
            ];
        }
        antwort_ok(['konten' => $konten, 'anzahl' => count($konten)]);
        // (antwort_ok beendet die Verarbeitung)

    // ---- Passwort-Reset-Link erzeugen -----------------------------------------
    case 'reset_link':
        $ziel   = nutzer_holen($pdo, (int) ($eingabe['user_id'] ?? 0));
        $senden = (bool) ($eingabe['senden'] ?? false);

        // Frisches Token; nur der SHA-256-Hash wird gespeichert.
        $token = bin2hex(random_bytes(32));
        $pdo->prepare(
            'INSERT INTO passwort_reset (token_hash, user_id, ablauf)
             VALUES (:th, :uid, (NOW() + INTERVAL 1 HOUR))'
        )->execute([
            ':th'  => hash('sha256', $token),
            ':uid' => (int) $ziel['id'],
        ]);

        $link = 'https://login.shapeminer.com/passwort-neu?token=' . $token;
        $gesendet = false;

        if ($senden) {
            $email = nutzer_email((string) $ziel['email_enc']);
            if ($email !== '—' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $gesendet = smu_mail_senden(
                    $email,
                    'Shape Miner — Passwort zurücksetzen',
                    "Hallo,\n\n"
                    . "über die Shape-Miner-Verwaltung wurde ein Link zum Zurücksetzen "
                    . "deines Passworts erstellt.\n"
                    . "Öffne dazu innerhalb der nächsten Stunde diesen Link:\n\n"
                    . $link . "\n\n"
                    . "Wenn du das nicht erwartet hast, ignoriere diese E-Mail einfach — "
                    . "dein Passwort bleibt dann unverändert.\n\n"
                    . "Dein Shape-Miner-Team"
                );
            }
        }

        antwort_ok(['link' => $link, 'gesendet' => $gesendet]);

    // ---- Sperren / Freigeben ---------------------------------------------------
    case 'sperren':
    case 'freigeben':
        $ziel  = nutzer_holen($pdo, (int) ($eingabe['user_id'] ?? 0));
        $aktiv = $aktion === 'freigeben' ? 1 : 0;

        if ($aktiv === 0) {
            if ($akteurId > 0 && $akteurId === (int) $ziel['id']) {
                antwort_fehler(409, 'selbst', 'Das eigene Konto kann nicht gesperrt werden.');
            }
            if ((string) $ziel['rolle'] === 'admin' && (int) $ziel['aktiv'] === 1
                && aktive_admins($pdo) <= 1) {
                antwort_fehler(409, 'letzter_admin', 'Der letzte aktive Admin kann nicht gesperrt werden.');
            }
        }

        $pdo->prepare('UPDATE users SET aktiv = :a WHERE id = :id')
            ->execute([':a' => $aktiv, ':id' => (int) $ziel['id']]);
        antwort_ok(['id' => (int) $ziel['id'], 'aktiv' => $aktiv]);

    // ---- Konto bearbeiten ------------------------------------------------------
    case 'aktualisieren':
        $ziel         = nutzer_holen($pdo, (int) ($eingabe['user_id'] ?? 0));
        $benutzername = trim((string) ($eingabe['benutzername'] ?? ''));
        $sprache      = (string) ($eingabe['sprache'] ?? $ziel['sprache']);
        $neueRolle    = (string) ($eingabe['rolle'] ?? $ziel['rolle']);

        $fehler = [];
        if (preg_match('/^[\p{L}\p{N}_.\- ]{3,50}$/u', $benutzername) !== 1) {
            $fehler['benutzername'] = 'Benutzername: 3–50 Zeichen.';
        }
        if (!in_array($sprache, ['de', 'en'], true)) {
            $sprache = (string) $ziel['sprache'];
        }
        if (!in_array($neueRolle, ['spieler', 'moderator', 'admin'], true)) {
            $neueRolle = (string) $ziel['rolle'];
        }

        // Rollenänderung nur durch Admins (Moderatoren dürfen das nicht).
        $rolleAendern = $neueRolle !== (string) $ziel['rolle'];
        if ($rolleAendern && $akteurRolle !== 'admin') {
            antwort_fehler(403, 'rolle_verweigert', 'Nur Admins dürfen Rollen ändern.');
        }

        // Aussperr-Schutz: letzten aktiven Admin nicht herabstufen.
        if ($rolleAendern && (string) $ziel['rolle'] === 'admin' && $neueRolle !== 'admin'
            && (int) $ziel['aktiv'] === 1 && aktive_admins($pdo) <= 1) {
            $fehler['rolle'] = 'Der letzte aktive Admin kann nicht herabgestuft werden.';
        }

        if ($fehler !== []) {
            antwort_fehler(422, 'validierung_fehlgeschlagen', 'Eingaben prüfen.', $fehler);
        }

        try {
            $pdo->prepare(
                'UPDATE users SET benutzername = :bn, sprache = :sp, rolle = :ro WHERE id = :id'
            )->execute([
                ':bn' => $benutzername,
                ':sp' => $sprache,
                ':ro' => $neueRolle,
                ':id' => (int) $ziel['id'],
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                antwort_fehler(409, 'name_vergeben', 'Dieser Benutzername ist bereits vergeben.');
            }
            throw $e;
        }
        antwort_ok(['id' => (int) $ziel['id']]);

    // ---- Konto löschen ---------------------------------------------------------
    case 'loeschen':
        $ziel = nutzer_holen($pdo, (int) ($eingabe['user_id'] ?? 0));

        if ($akteurId > 0 && $akteurId === (int) $ziel['id']) {
            antwort_fehler(409, 'selbst', 'Das eigene Konto kann nicht gelöscht werden.');
        }
        if ((string) $ziel['rolle'] === 'admin' && (int) $ziel['aktiv'] === 1
            && aktive_admins($pdo) <= 1) {
            antwort_fehler(409, 'letzter_admin', 'Der letzte aktive Admin kann nicht gelöscht werden.');
        }

        // Kaskadiert über FOREIGN KEY ON DELETE CASCADE (Einwilligungen, Tokens …).
        $pdo->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => (int) $ziel['id']]);
        antwort_ok(['id' => (int) $ziel['id'], 'geloescht' => true]);

    default:
        antwort_fehler(400, 'unbekannte_aktion', 'Unbekannte Aktion.');
}
