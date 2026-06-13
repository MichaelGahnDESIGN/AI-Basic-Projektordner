# Codex-Integration

## Ziel

Codex soll das MCP als lokalen Aufgaben- und Übergabekanal nutzen können.
Dadurch muss nicht mehr eine einzelne Markdown-Datei manuell bearbeitet werden,
obwohl `agent_comms.md` weiterhin lesbar bleibt.

## Startbefehl

Im Codex-MCP-Setup kann der Server lokal gestartet werden mit:

```bash
cd PROJEKT/WORKSPACE
npm start
```

Für ein konkretes Projekt empfiehlt sich:

```bash
AGENT_COMMS_DIR=/pfad/zum/projekt npm start
```

## Empfohlene Nutzung

- Vor Arbeit: `read_context`
- Neue Aufgabe an Claude: `create_task`
- Eigene Übernahme: `claim_task`
- Abschluss: `complete_task`
- Rückfragen: `add_blocker`
- Übergabe: `write_handoff`

## Sicherheit

Codex soll niemals echte Secrets, `.env`-Inhalte, private Logauszüge oder
personenbezogene Detaildaten in Tool-Eingaben schreiben. Wenn solche Daten für
die Arbeit relevant wirken, soll Codex einen Blocker mit neutraler Beschreibung
anlegen.
