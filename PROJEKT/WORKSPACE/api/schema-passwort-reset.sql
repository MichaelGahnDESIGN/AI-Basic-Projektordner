-- ===========================================================================
-- Passwort-Reset-Tokens (SMU)
-- ===========================================================================
-- Speichert NUR den SHA-256-Hash des Tokens (Klartext-Token geht per E-Mail
-- an den Nutzer). So sind Tokens bei DB-Kompromittierung nicht nutzbar.
-- Tokens sind einmalig (benutzt_am) und kurzlebig (ablauf).

CREATE TABLE IF NOT EXISTS passwort_reset (
    token_hash  CHAR(64)        NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    ablauf      DATETIME        NOT NULL,
    benutzt_am  DATETIME     NULL,
    erstellt_am DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (token_hash),
    INDEX idx_user (user_id),
    INDEX idx_ablauf (ablauf),
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
