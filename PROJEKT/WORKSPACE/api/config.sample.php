<?php

declare(strict_types=1);

/**
 * Beispiel-Konfiguration für die Shape-Miner-User-API (SMU).
 *
 * WICHTIG — Umgang mit dieser Datei:
 *  - Diese Vorlage enthält NUR Platzhalter und funktioniert so absichtlich nicht.
 *  - Kopiere sie zu `config.php` und trage die echten Werte ein.
 *  - `config.php` ist gitignoriert und darf NIEMALS ins Repository gelangen.
 *  - Auf dem Server gehört `config.php` AUSSERHALB des Webroots:
 *      /www/htdocs/w021b1e1/smu-konfiguration/config.php
 *    Der Pfad wird per Umgebungsvariable `SMU_CONFIG_PFAD` gesetzt (siehe .htaccess).
 *
 * Schlüssel erzeugen (je 32 Byte Zufall, Base64-kodiert):
 *    php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
 * Den Befehl viermal ausführen — jeder Schlüssel braucht einen EIGENEN Wert.
 *
 * MIGRATION: Die Crypto-Schlüssel müssen identisch zu Shape-Miner-Deluxe sein,
 * damit bestehende Nutzerdaten (AES-GCM + Blind-Index) ohne Re-Verschlüsselung
 * übernommen werden können. jwt.geheimnis ist ein NEUER, gemeinsamer Wert.
 */

return [

    // --- Datenbank (MariaDB, Account w021b1e1) --------------------------------
    'db' => [
        'host'     => 'localhost',
        'name'     => 'd04757cf',
        'benutzer' => 'd04757cf',
        'passwort' => 'PLATZHALTER_DB_PASSWORT',
    ],

    // --- Kryptografie-Schlüssel (jeweils: base64_encode(random_bytes(32))) ---
    'schluessel' => [
        // AES-256-GCM-Schlüssel für E-Mail, Vorname, Nachname (at rest)
        // MIGRATION: gleicher Wert wie Shape-Miner-Deluxe verwenden
        'verschluesselung' => 'PLATZHALTER_BASE64_32_BYTE',
        // HMAC-SHA256 für durchsuchbaren E-Mail-Blind-Index
        // MIGRATION: gleicher Wert wie Shape-Miner-Deluxe verwenden
        'blind_index'      => 'PLATZHALTER_BASE64_32_BYTE',
        // HMAC-SHA256 für IP-Hash bei Einwilligungen
        'ip_hash'          => 'PLATZHALTER_BASE64_32_BYTE',
    ],

    // --- JWT (HS256, shared mit allen Shape-Miner-Spielen) -------------------
    'jwt' => [
        // Neues Geheimnis generieren und in allen Shape-Miner-Spiel-Configs eintragen
        'geheimnis'            => 'PLATZHALTER_BASE64_32_BYTE',
        'aussteller'           => 'shape-miner',
        'gueltigkeit_sekunden' => 3600,
    ],

    // --- CORS: alle Shape-Miner-Clients --------------------------------------
    'cors_erlaubte_origins' => [
        'https://deluxe.shapeminer.com',
        'https://login.shapeminer.com',
        // 'http://localhost:8080', // lokale Flutter-Web-Entwicklung
    ],

    // --- Argon2id-Parameter --------------------------------------------------
    'argon2_optionen' => [
        'memory_cost' => 65536,
        'time_cost'   => 4,
        'threads'     => 1,
    ],

    // --- SMTP (Passwort-Reset, Benachrichtigungen) ---------------------------
    // Authentifizierter Versand über SSL (Port 465). Postfach z. B. bei All-Inkl.
    'smtp' => [
        'host'          => 'PLATZHALTER_SMTP_HOST',     // z. B. wXXXXXXX.kasserver.com
        'port'          => 465,
        'benutzer'      => 'PLATZHALTER_SMTP_BENUTZER',  // z. B. support@deine-domain.de
        'passwort'      => 'PLATZHALTER_SMTP_PASSWORT',
        'absender'      => 'PLATZHALTER_ABSENDER_EMAIL',
        'absender_name' => 'Shape Miner',
    ],
];
