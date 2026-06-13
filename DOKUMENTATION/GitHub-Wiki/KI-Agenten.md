# KI-Agenten

Die Vorlage ist für mehrere KI-Werkzeuge vorbereitet.

## Claude Code

Claude Code erkennt `CLAUDE.md` automatisch.

`CLAUDE.md` importiert `AGENTS.md` und verweist zusätzlich auf `claude.md`.
Dadurch bleibt die gemeinsame Agentenlogik gebündelt, während Claude trotzdem
einen offiziellen Einstieg findet.

## Claude Cowork

Claude Cowork kann `claude.md` als menschenfreundlichen Einstieg nutzen.

Wichtig bei gemeinsamer Arbeit:

- vor Änderungen `git status --short --branch` prüfen
- keine fremden Änderungen überschreiben
- Entscheidungen dokumentieren
- Sicherheits- und Datenschutzregeln beachten

## ChatGPT Codex

Codex nutzt `AGENTS.md` als Projektanweisung.

Zusätzlich sind Repo-Skills unter `.agents/skills/` vorhanden.

## Gemeinsame Arbeitsregel

Alle Agenten sollen:

- erst lesen, dann ändern
- Projektcode nur in `PROJEKT/WORKSPACE/` anlegen
- wichtige Entscheidungen dokumentieren
- keine Secrets ausgeben oder speichern
- nach Änderungen passende Prüfungen ausführen
