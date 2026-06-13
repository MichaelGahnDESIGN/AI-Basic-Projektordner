-- ============================================================================
-- Shape Miner Deluxe — Schema-Erweiterung: Zwei-Faktor-Authentifizierung (TOTP)
-- Ziel: MariaDB 10.6 (All-Inkl), Zeichensatz durchgehend utf8mb4
-- ============================================================================
-- Hinweise:
--  - Wird zusätzlich zu schema.sql eingespielt (z. B. über phpMyAdmin);
--    die Tabelle users muss bereits existieren.
--  - totp_secret_enc enthält das TOTP-Geheimnis NIEMALS im Klartext, sondern
--    ausschließlich verschlüsselt (AES-256-GCM, Speicherformat "v1:<Base64>",
--    Kontext 'users.totp_secret_enc'). NULL = kein 2FA-Setup begonnen.
--  - totp_aktiviert: 0 = 2FA aus (oder Setup noch nicht bestätigt), 1 = aktiv.
--    Erst nach erfolgreich geprüftem Code wird auf 1 gesetzt.
--  - Beim Löschen eines Kontos verschwinden beide Spaltenwerte mit der Zeile —
--    es ist keine zusätzliche Aufräumlogik nötig.
--
-- Idempotenz: Dieses Skript ist NICHT von sich aus mehrfach ausführbar —
-- MariaDB 10.6 unterstützt bei ALTER TABLE ... ADD COLUMN die Klausel
-- IF NOT EXISTS, ältere MySQL-Versionen jedoch nicht. Vor dem Einspielen
-- prüfen, ob die Spalten bereits existieren (phpMyAdmin: Struktur der
-- Tabelle users). Alternativ die untenstehende Variante mit IF NOT EXISTS
-- verwenden, die auf MariaDB sicher mehrfach läuft:
--
--   ALTER TABLE users
--       ADD COLUMN IF NOT EXISTS totp_secret_enc VARCHAR(1024) NULL,
--       ADD COLUMN IF NOT EXISTS totp_aktiviert  TINYINT(1) NOT NULL DEFAULT 0;
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE users
    ADD COLUMN totp_secret_enc VARCHAR(1024) NULL,
    ADD COLUMN totp_aktiviert  TINYINT(1) NOT NULL DEFAULT 0;
