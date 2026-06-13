# MCP-Tools

Alle Tools geben JSON als Textinhalt zurück. Schreibende Tools führen vorher
einen Safety-Check aus.

## read_context

Liest Projektkontext, aktive Aufgaben, offene Blocker, letzte Logs,
Entscheidungen und Übergaben.

Eingabe: `{}`.

## list_tasks

Gibt die aktive Aufgaben-Queue zurück.

Eingabe: `{}`.

## create_task

Erstellt eine Aufgabe.

Pflichtfelder:

- `sender`
- `recipient`
- `type`
- `priority`
- `context`
- `task`
- `acceptanceCriteria`
- `safetyNote`

## claim_task

Setzt eine Aufgabe auf `IN_PROGRESS`.

Pflichtfelder:

- `taskId`
- `agent`

## complete_task

Verschiebt eine Aufgabe nach `DONE`.

Pflichtfelder:

- `taskId`
- `resultNote`
- `files`
- `tests`

Optionale Felder:

- `commit`
- `pullRequest`

## append_chat

Fügt eine Chat- oder Hinweisnachricht hinzu.

Pflichtfelder:

- `sender`
- `kind`
- `message`

## add_blocker

Dokumentiert eine Blockade oder Rückfrage.

Pflichtfelder:

- `reporter`
- `message`

Optional:

- `relatedTaskId`

## resolve_blocker

Markiert einen Blocker als gelöst.

Pflichtfelder:

- `blockerId`
- `resolvedBy`
- `resolutionNote`

## add_decision

Dokumentiert eine dauerhafte Entscheidung.

Pflichtfelder:

- `author`
- `title`
- `rationale`

Optional:

- `relatedTaskId`

## write_handoff

Schreibt eine Übergabe.

Pflichtfelder:

- `from`
- `to`
- `summary`
- `nextSteps`

Optional:

- `relatedTaskIds`

## validate_safety

Prüft Text auf riskante Muster.

Pflichtfeld:

- `content`

## reset_round

Setzt Queue, DONE, Chat und Übergaben zurück und schreibt eine History.
Offene Blocker bleiben erhalten.

Pflichtfelder:

- `actor`
- `summary`
