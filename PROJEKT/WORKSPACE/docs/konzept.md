# Konzept

## Ziel

Claude-Codex-MCP ist ein lokales Kommunikationssystem für mehrere KI-Agenten.
Es soll die bewährte Idee einer gemeinsamen `AI_COMMS.md` aus TINTLING in ein
wiederverwendbares Werkzeug überführen.

Phase 1 konzentriert sich auf:

- lokale Dateien statt Cloud
- menschenlesbares Markdown
- maschinenlesbaren JSON-State
- Aufgaben-Queue mit Statuswechseln
- Chat, Blocker, Entscheidungen und Übergaben
- Safety-Check vor Schreibvorgängen

## Speicher

Der Server nutzt zwei Dateien:

- `agent_comms.md`: lesbares Protokoll für Menschen und Agenten.
- `agent_comms.state.json`: strukturierter Zustand für Tools.

Beide Dateien werden aus demselben State geschrieben. Dadurch bleibt Markdown
lesbar, während Tools zuverlässig mit strukturierten Daten arbeiten.

## Architektur

- `src/domain/`: reine Status- und Datenlogik.
- `src/storage/`: Laden und Speichern von JSON und Markdown.
- `src/markdown/`: Rendern des lesbaren Protokolls.
- `src/safety/`: Prüfung riskanter Inhalte.
- `src/tools/`: MCP-Tool-Handler.
- `src/mcp/`: lokaler JSON-RPC-Server über stdio.

## Grenzen von Phase 1

- Kein Publishing.
- Kein NPM-Release.
- Keine Cloud.
- Kein SQLite-Backend.
- Keine automatische Skill-Generierung.
- Keine echte DLP- oder Rechtsprüfung.

Diese Punkte sind bewusst für spätere Phasen offen.
