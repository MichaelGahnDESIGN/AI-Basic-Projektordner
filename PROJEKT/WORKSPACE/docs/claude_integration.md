# Claude-Integration

## Ziel

Claude kann das MCP als gemeinsamen lokalen Kommunikationskanal mit Codex
verwenden. Die Datei `agent_comms.md` bleibt weiterhin lesbar, aber Aufgaben und
Statuswechsel laufen über strukturierte Tools.

## Startbefehl

```bash
cd PROJEKT/WORKSPACE
npm start
```

Mit projektspezifischem Speicher:

```bash
AGENT_COMMS_DIR=/pfad/zum/projekt npm start
```

## Empfohlener Ablauf

1. `read_context` lesen.
2. Mit `list_tasks` offene Aufgaben prüfen.
3. Passende Aufgabe mit `claim_task` übernehmen.
4. Bei Rückfragen `add_blocker` nutzen.
5. Nach Abschluss `complete_task` mit Ergebnis, Dateien und Tests ausführen.
6. Bei dauerhaften Entscheidungen `add_decision` nutzen.
7. Am Ende bei Bedarf `write_handoff` an Codex oder das Team schreiben.

## Sicherheitsverhalten

Claude soll sensible Inhalte nicht in Chat, Aufgaben, Blocker oder Logs
eintragen. Besonders zu vermeiden sind Passwörter, Tokens, `.env`-Daten,
private Schlüssel, Datenbank-Zugänge und personenbezogene Detaildaten.
