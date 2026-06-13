-- ===========================================================================
-- Rate-Limiting / Brute-Force-Schutz (SMU)
-- ===========================================================================
-- Zählt Fehlversuche pro Schlüssel (gehashte IP bzw. Konto) in einem Zeitfenster
-- und sperrt nach Überschreiten temporär. Schützt Login (Passwort + TOTP) und
-- Registrierung vor Online-Brute-Force und E-Mail-Enumeration.

CREATE TABLE IF NOT EXISTS rate_limit (
    schluessel    VARCHAR(190) NOT NULL,
    versuche      INT UNSIGNED NOT NULL DEFAULT 0,
    fenster_start DATETIME     NOT NULL,
    gesperrt_bis  DATETIME     NULL,
    PRIMARY KEY (schluessel),
    INDEX idx_gesperrt_bis (gesperrt_bis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
