# Claude-Codex-MCP

Dieses Projekt ist Phase 1 eines lokalen MCP-Systems für die Zusammenarbeit von
KI-Agenten wie Codex und Claude. Es verwaltet Aufgaben, Chat-Nachrichten,
Übergaben, Blocker, Entscheidungen und eine kleine History in gut lesbaren
Dateien.

Der Arbeitstitel ist noch vorläufig. Das System bleibt lokal-first: keine
Cloud-Pflicht, keine externen Dienste und keine Speicherung sensibler Daten.

## Start

Voraussetzung ist Node.js mit TypeScript-Type-Stripping, empfohlen ab Node
`22.6.0`.

```bash
npm --prefix PROJEKT/WORKSPACE run check
```

Server lokal starten:

```bash
cd PROJEKT/WORKSPACE
npm start
```

Optional kann der Speicherort gesetzt werden:

```bash
AGENT_COMMS_DIR=/pfad/zum/projekt npm start
```

Dann nutzt der Server dort `agent_comms.md` und
`agent_comms.state.json`.

## MCP-Tools

Phase 1 registriert diese Tools:

- `read_context`
- `list_tasks`
- `create_task`
- `claim_task`
- `complete_task`
- `append_chat`
- `add_blocker`
- `resolve_blocker`
- `add_decision`
- `write_handoff`
- `validate_safety`
- `reset_round`

Details stehen in [docs/mcp_tools.md](docs/mcp_tools.md).

## Sicherheitsgrundsatz

Alle Schreib-Tools prüfen Eingaben mit `validate_safety`, bevor sie in Dateien
geschrieben werden. Offensichtliche Secrets, `.env`-Hinweise, private Schlüssel,
Passwort-/Token-Zuweisungen, Datenbank-URLs, SSH-Material und Dump-Hinweise
werden blockiert.

Die Prüfung ist bewusst konservativ, ersetzt aber keine vollständige
Datenschutz- oder Sicherheitsprüfung. Menschen und Agenten sollen weiterhin
keine sensiblen Inhalte in Aufgaben, Chat, Logs oder Dokumentation schreiben.

## Struktur

```text
src/
├── domain/      # Statuswechsel, Tasks, Chat, Blocker, Entscheidungen
├── markdown/    # menschenlesbare Markdown-Ausgabe
├── mcp/         # kleiner JSON-RPC/MCP-Server über stdio
├── safety/      # lokale Sicherheitsprüfung
├── scripts/     # Smoke-Test
├── storage/     # dateibasierter Speicher
└── tools/       # MCP-Tool-Handler und Eingabeprüfung
```

## Tests

```bash
cd PROJEKT/WORKSPACE
npm test
npm run smoke
```

`npm run check` führt beides zusammen aus.

## Integration

- Codex: [docs/codex_integration.md](docs/codex_integration.md)
- Claude: [docs/claude_integration.md](docs/claude_integration.md)
- Konzept: [docs/konzept.md](docs/konzept.md)
- Sicherheit: [docs/sicherheitsregeln.md](docs/sicherheitsregeln.md)
