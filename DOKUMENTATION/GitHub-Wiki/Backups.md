# Backups

Backups bleiben lokal und werden nicht automatisch versioniert.

## Backup-Regeln

Nicht sichern:

- echte `.env`-Dateien
- API-Schlüssel
- Tokens
- Passwörter
- `node_modules/`
- Logs
- Build-Ausgaben
- temporäre Dateien

## Empfohlene Benennung

```text
YYYY-MM-DD_HH-mm-ss_vVERSION
```

## Dokumentation

Jedes relevante Backup sollte dokumentiert werden in:

```text
DOKUMENTATION/Projektbetrieb/Backups.md
```

Festhalten:

- Datum
- Backup-Name
- gesicherter Stand
- Anlass
- Wiederherstellungsweg
