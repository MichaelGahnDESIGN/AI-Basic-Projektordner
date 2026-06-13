# Setup

Hier werden lokale Voraussetzungen und Einrichtungsschritte dokumentiert.

Typische Inhalte:

- benötigte Software
- Versionen
- Installationsschritte
- Umgebungsvariablen
- Startbefehle
- bekannte Stolperstellen

## Lokales Projektroot

Das Projektroot ist lokal mit Git initialisiert.

Wichtige Schutzregeln:

- `.env`-Dateien werden nicht versioniert.
- `.DS_Store`, Logs, Build-Ausgaben und `node_modules/` werden ignoriert.
- Öffentliche GitHub-Dateien im Root sind erlaubt:
  `README.md`, `LICENSE`, `CHANGELOG.md`, `VERSION` und `SECURITY.md`.
- Tool-Kompatibilitätsdateien im Root sind erlaubt:
  `AGENTS.md`, `CLAUDE.md`, `claude.md` und `index.md`.
- Lokale Backups unter `BACKUPS/` werden nicht versioniert, außer der
  README.
- Ein GitHub-Remote ist noch nicht dokumentiert.

## Separater OpenRouter-Bereich

`DEMOS/OPENROUTER/` ist ein eigener Demo-Ordner und bleibt außerhalb von
`PROJEKT/WORKSPACE/`.

Einrichtung:

```bash
cd DEMOS/OPENROUTER
npm install
cp .env.example .env
```

Danach den lokalen API-Schlüssel in `DEMOS/OPENROUTER/.env` eintragen.

Prüfung ohne API-Aufruf:

```bash
npm run check
```
