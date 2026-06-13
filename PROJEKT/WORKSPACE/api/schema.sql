-- ============================================================================
-- Shape Miner Deluxe — Datenbankschema
-- Ziel: MariaDB 10.6 (All-Inkl), Zeichensatz durchgehend utf8mb4
-- ============================================================================
-- Hinweise:
--  - Die Datenbank selbst wird bei All-Inkl über das KAS-Panel angelegt.
--    Dieses Skript erzeugt nur die Tabellen (z. B. über phpMyAdmin einspielen).
--  - Personenbezogene Daten (E-Mail, Vorname, Nachname) liegen ausschließlich
--    verschlüsselt vor (AES-256-GCM, Speicherformat "v1:<Base64>").
--  - Die Login-Suche läuft über den deterministischen E-Mail-Blind-Index
--    (HMAC-SHA256 mit Server-Schlüssel, 64 Hex-Zeichen) — die Klartext-E-Mail
--    berührt die Datenbank nie.
--  - Zeitstempel nutzen die Server-Zeitzone (All-Inkl: Europe/Berlin).
-- ============================================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
-- Tabelle: users — Benutzerkonten
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    -- Durchsuchbarer, eindeutiger Ersatz für die E-Mail
    -- (HMAC-SHA256 der normalisierten E-Mail, hex-kodiert)
    email_blind_index CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,

    -- Personenbezogene Felder, verschlüsselt at rest (AES-256-GCM, Base64)
    email_enc         VARCHAR(1024) NOT NULL,
    vorname_enc       VARCHAR(1024) NOT NULL,
    nachname_enc      VARCHAR(1024) NOT NULL,

    -- Öffentlicher Spielername (bewusst unverschlüsselt, frei wählbar)
    benutzername      VARCHAR(50)  NOT NULL,

    sprache           ENUM('de','en') NOT NULL DEFAULT 'de',

    -- Argon2id-Hash aus password_hash() — niemals ein Klartext-Passwort
    passwort_hash     VARCHAR(255) NOT NULL,

    rolle             ENUM('spieler','admin') NOT NULL DEFAULT 'spieler',

    -- 0 = Konto deaktiviert/gesperrt, 1 = aktiv
    aktiv             TINYINT(1) NOT NULL DEFAULT 1,

    erstellt_am       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aktualisiert_am   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                          ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_users_email_blind_index (email_blind_index),
    UNIQUE KEY uniq_users_benutzername (benutzername)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: einwilligungen — AGB- und Datenschutz-Zustimmungen
-- ----------------------------------------------------------------------------
-- Jede Zustimmung wird mit Dokumentversion und Zeitstempel nachweisbar
-- protokolliert. Die IP-Adresse wird aus Datensparsamkeit nur als
-- HMAC-SHA256-Hash gespeichert (optional, NULL erlaubt).
CREATE TABLE IF NOT EXISTS einwilligungen (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    typ         ENUM('agb','datenschutz') NOT NULL,
    version     VARCHAR(32) NOT NULL,
    zeitstempel DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_hash     CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,

    PRIMARY KEY (id),
    KEY idx_einwilligungen_user_typ (user_id, typ),
    CONSTRAINT fk_einwilligungen_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: refresh_tokens — vorbereitete Ausbaustufe (optional)
-- ----------------------------------------------------------------------------
-- Die API stellt aktuell nur kurzlebige JWTs aus. Diese Tabelle ist für eine
-- spätere Refresh-Token-Rotation vorbereitet. Es wird nie das Token selbst
-- gespeichert, sondern ausschließlich sein SHA-256-Hash.
CREATE TABLE IF NOT EXISTS refresh_tokens (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       BIGINT UNSIGNED NOT NULL,
    token_hash    CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    gueltig_bis   DATETIME NOT NULL,
    erstellt_am   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    widerrufen_am DATETIME NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_refresh_tokens_token_hash (token_hash),
    KEY idx_refresh_tokens_user (user_id),
    CONSTRAINT fk_refresh_tokens_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
