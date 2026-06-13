# DEPLOY — Shape Miner User (SMU)

Runbook für den Release-Workflow (`/dev`). Server: All-Inkl (Account `w021b1e1`).

## Voraussetzungen
- Zugangsdaten im gitignorierten `SMU_SMDZ4-u-B-3-Rwo-r-t.md` (FTP, MySQL, SSH).
- SSH via `ssh w021b1e1@w021b1e1.kasserver.com` nutzbar.
- DB-Schema bereits eingespielt (einmalig, phpMyAdmin `w021b1e1.kasserver.com/mysqladmin`).
- `config.php` liegt auf dem Server unter `/www/htdocs/w021b1e1/smu-konfiguration/config.php` (nie im Repo).

## Preflight

Kein Flutter-Build (reine PHP-App). Nur Syntax-Check:
```
find PROJEKT/WORKSPACE/api PROJEKT/WORKSPACE/web -name "*.php" | head -5
```
(keine automatisierten PHP-Tests — manueller Smoke-Test nach Deploy)

## Build

Kein Build-Schritt — PHP wird direkt deployed.

## Tests

Keine automatisierten Tests. Nach Deploy manuell prüfen (siehe Verify).

## Deploy

**Erstmalig / DB-Schema:**
1. `PROJEKT/WORKSPACE/api/schema.sql` + `schema-2fa.sql` in DB `d04757cf` einspielen (phpMyAdmin).
2. `config.php` (außerhalb Repo) via SSH hochladen:
   ```
   scp /lokaler/pfad/config.php w021b1e1@w021b1e1.kasserver.com:/www/htdocs/w021b1e1/smu-konfiguration/
   ```

**API deployen (`api/`):**
```
lftp ftp.kasserver.com -u w021b1e1,PASSWORT -e "
  mirror -R PROJEKT/WORKSPACE/api/ /www/htdocs/w021b1e1/smu-api/;
  chmod -R 755 /www/htdocs/w021b1e1/smu-api/;
  bye"
```

**Web-UI deployen (`web/`):**
```
lftp ftp.kasserver.com -u w021b1e1,PASSWORT -e "
  mirror -R PROJEKT/WORKSPACE/web/ /www/htdocs/w021b1e1/;
  chmod -R 755 /www/htdocs/w021b1e1/;
  bye"
```

**Nicht deployen:** `migration/migrieren.php` (gitignoriert, nur lokaler Trockenrun).

## Verify

Nach dem Deploy Smoke-Tests:
- `curl https://login.shapeminer.com/` → HTTP 302 (→ /anmelden)
- `curl https://login.shapeminer.com/api/` → HTTP 403 (directory blocked)
- `https://login.shapeminer.com/anmelden` im Browser → Login-Formular lädt
- POST auf `https://login.shapeminer.com/api/login.php` mit gültigen Credentials → `{"ok":true}` + JWT mit `"iss":"shape-miner"`
- JWT aus Login in `https://deluxe.shapeminer.com/api/spielstand.php` → HTTP 200

## Migration (einmalig)

Nur wenn Nutzer aus der DELUXE-DB übernommen werden sollen:
1. `migration/migrieren.php` ausfüllen (beide DB-Passwörter eintragen).
2. Trockenrun: `php migration/migrieren.php --dry-run`
3. Ausführen: `php migration/migrieren.php`
4. Zeilenzahl DELUXE-users == SMU-users prüfen.
5. **Skript sofort löschen** — enthält DB-Zugangsdaten.

## WICHTIG
- FTP lädt Dateien als `700` hoch → nach JEDEM Mirror `chmod -R 755`.
- Secrets nie committen (`SMU_SMDZ4-*`, `**/config.php`, `**/migration/migrieren.php`).
- `migration/migrieren.php` ist gitignoriert und darf NIE auf den Server deployed werden.
