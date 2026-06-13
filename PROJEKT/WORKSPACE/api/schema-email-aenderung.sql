-- ===========================================================================
-- E-Mail-Änderung mit Double-Opt-In (SMU)
-- ===========================================================================
-- Eine E-Mail-Änderung wird erst wirksam, nachdem der Nutzer einen Link an der
-- NEUEN Adresse bestätigt hat. Die neue Adresse liegt bis dahin verschlüsselt
-- (gleiche AES-Logik wie users.email_enc) als ausstehende Änderung vor.

CREATE TABLE IF NOT EXISTS email_aenderung (
    token_hash       CHAR(64)        NOT NULL,
    user_id          BIGINT UNSIGNED NOT NULL,
    neue_email_enc   VARCHAR(1024)   NOT NULL,
    neue_email_bidx  CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    ablauf           DATETIME        NOT NULL,
    erstellt_am      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (token_hash),
    INDEX idx_user (user_id),
    INDEX idx_ablauf (ablauf),
    CONSTRAINT fk_emailaenderung_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
