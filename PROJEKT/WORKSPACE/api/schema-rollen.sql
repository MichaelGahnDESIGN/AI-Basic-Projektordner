-- ===========================================================================
-- Rollen-Erweiterung (SMU): Moderator-Rolle ergänzen
-- ===========================================================================
-- Bisher kannte users.rolle nur 'spieler' und 'admin'. Für die zentrale
-- Nutzerverwaltung im Editor wird eine dritte Stufe gebraucht:
--
--   spieler   → normaler Account, KEIN Editor-Zugang
--   moderator → darf sich in den Editor einloggen (nur Sprach-/Rechtstexte),
--               darf aber KEINE Berechtigungen anderer ändern
--   admin     → voller Editor-Zugriff, darf Rollen vergeben
--
-- Einmalig auf der SMU-Datenbank (d04757cf) via phpMyAdmin ausführen.
-- Bestehende Werte ('spieler'/'admin') bleiben unverändert erhalten.

ALTER TABLE users
    MODIFY rolle ENUM('spieler','moderator','admin')
    NOT NULL DEFAULT 'spieler';
