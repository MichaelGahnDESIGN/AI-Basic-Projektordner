-- ===========================================================================
-- Sperrbegriffe (SMU) — global gesperrte Wörter
-- ===========================================================================
-- Gilt für ALLE Shape-Miner-Spiele und den künftigen Messenger:
--   - Benutzernamen, die einen Sperrbegriff enthalten, werden abgelehnt.
--   - In Nachrichten (Messenger) werden Sperrbegriffe durch ***** ersetzt.
-- Begriffe werden klein gespeichert; geprüft wird case-insensitiv als Teilwort.
-- Pflege erfolgt im Editor (über die Admin-API).

CREATE TABLE IF NOT EXISTS sperrbegriffe (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    begriff     VARCHAR(100) NOT NULL,
    erstellt_am DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_begriff (begriff)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
