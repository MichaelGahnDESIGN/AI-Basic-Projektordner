<?php

declare(strict_types=1);

/**
 * Aufgabe: sichere PHP-Sitzungen für die SMU-Web-Accountverwaltung.
 *
 *  - Cookie httponly, SameSite=Lax, bei HTTPS secure.
 *  - Session-ID nach Login erneuert (gegen Session-Fixation).
 *  - Inaktivitäts-Timeout nach 120 Minuten.
 */

require_once __DIR__ . '/db.php';

const SMU_SITZUNG_TIMEOUT = 7200; // 120 Minuten

function smu_sitzung_starten(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('smu_konto');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => smu_ist_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $zuletzt = $_SESSION['letzte_aktivitaet'] ?? null;
    if (is_int($zuletzt) && (time() - $zuletzt) > SMU_SITZUNG_TIMEOUT) {
        smu_sitzung_abmelden();
        smu_hinweis_setzen('fehler', 'Sitzung abgelaufen — bitte erneut anmelden.');
    }

    $_SESSION['letzte_aktivitaet'] = time();
}

function smu_ist_https(): bool
{
    $https = $_SERVER['HTTPS'] ?? '';
    return (is_string($https) && $https !== '' && $https !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/** Anmelden: neue Session-ID + user_id merken. */
function smu_sitzung_anmelden(int $userId): void
{
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
    $_SESSION['user_id']           = $userId;
    $_SESSION['letzte_aktivitaet'] = time();
}

/** Abmelden: Session komplett leeren + neue leere ID (Hinweise überleben). */
function smu_sitzung_abmelden(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        $_SESSION = [];
    }
}

/**
 * Liefert den eingeloggten Nutzer (raw DB-Zeile ohne PII-Entschlüsselung)
 * oder null. Ergebnis wird pro Request gecacht.
 *
 * @return array{id:int, benutzername:string, sprache:string, rolle:string, aktiv:int}|null
 */
function smu_sitzung_nutzer(): ?array
{
    static $geprueft = false;
    static $nutzer   = null;

    if ($geprueft) {
        return $nutzer;
    }
    $geprueft = true;

    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId) || $userId <= 0) {
        return null;
    }

    $stmt = smu_db()->prepare(
        'SELECT id, benutzername, sprache, rolle, aktiv
         FROM users WHERE id = :id'
    );
    $stmt->execute([':id' => $userId]);
    $zeile = $stmt->fetch();

    if ($zeile === false || (int) $zeile['aktiv'] !== 1) {
        smu_sitzung_abmelden();
        return null;
    }

    $nutzer = [
        'id'           => (int) $zeile['id'],
        'benutzername' => (string) $zeile['benutzername'],
        'sprache'      => (string) $zeile['sprache'],
        'rolle'        => (string) $zeile['rolle'],
        'aktiv'        => (int) $zeile['aktiv'],
    ];
    return $nutzer;
}

/** Eingeloggt-Prüfung — leitet auf /anmelden weiter falls nicht. */
function smu_einloggen_erforderlich(): void
{
    if (smu_sitzung_nutzer() === null) {
        header('Location: /anmelden');
        exit;
    }
}

/** Setzt einen einmaligen Hinweis für die nächste Seite. */
function smu_hinweis_setzen(string $typ, string $text): void
{
    $_SESSION['hinweis'] = ['typ' => $typ, 'text' => $text];
}

/**
 * Holt und löscht den einmaligen Hinweis.
 *
 * @return array{typ:string, text:string}|null
 */
function smu_hinweis_holen(): ?array
{
    $h = $_SESSION['hinweis'] ?? null;
    unset($_SESSION['hinweis']);
    return is_array($h) ? $h : null;
}
