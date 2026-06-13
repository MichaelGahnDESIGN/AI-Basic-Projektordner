-- ===========================================================================
-- TOTP-Recovery-Codes (SMU)
-- ===========================================================================
-- Einmal-Codes als Ersatz für den Authenticator (Verlust des Geräts).
-- Gespeichert wird NUR der SHA-256-Hash; Klartext wird dem Nutzer einmalig
-- bei der 2FA-Einrichtung angezeigt. Jeder Code ist genau einmal nutzbar.

CREATE TABLE IF NOT EXISTS totp_recovery_codes (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    code_hash   CHAR(64)        NOT NULL,
    benutzt_am  DATETIME        NULL,
    erstellt_am DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_code (code_hash),
    INDEX idx_user (user_id),
    CONSTRAINT fk_recovery_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
