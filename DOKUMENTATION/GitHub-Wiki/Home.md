# Übersicht: AI Basic Projektordner

Willkommen im Wiki zur Vorlage **AI Basic Projektordner**.

Diese Vorlage ist ein sauberer Startpunkt für neue KI-gestützte Projekte. Sie
hilft Menschen, Claude Code, Claude Cowork, ChatGPT Codex und ähnliche
KI-Agenten von Anfang an mit derselben Struktur, denselben Regeln und derselben
Dokumentationslogik arbeiten zu lassen.

Die Vorlage ist bewusst **keine fertige App** und kein Framework-Boilerplate.
Sie ist eine neutrale Projektbasis für Ordnung, Orientierung, Sicherheit,
Dokumentation und nachvollziehbare Zusammenarbeit, bevor der eigentliche
Projektcode entsteht.

## Für Wen Ist Diese Vorlage Gedacht?

- Menschen, die regelmäßig mit KI-Agenten an Projekten arbeiten.
- Selbstständige, kleine Teams und Agenturen.
- Projekte, bei denen saubere Dokumentation wichtig ist.
- Anwender, die eine kopierbare Startstruktur für neue Projekte brauchen.
- Teams, die Claude, Codex und andere Tools nicht jedes Mal neu einrichten
  möchten.

## Was Die Vorlage Löst

Viele KI-Projekte starten schnell, werden aber nach kurzer Zeit unübersichtlich:
Regeln liegen verstreut, Dokumentation fehlt, Entscheidungen sind nicht
nachvollziehbar und Agenten wissen nicht zuverlässig, wo sie beginnen sollen.

Diese Vorlage trennt deshalb klar:

- **öffentliche Einstiegsdateien** im Root
- **KI-Regeln und Agentenlogik** in eigenen Ordnern
- **konkreten Projektcode** in `PROJEKT/WORKSPACE/`
- **Dokumentation, Entscheidungen und Risiken** in `DOKUMENTATION/`
- **Demos und optionale Beispiele** in `DEMOS/`
- **lokale Sicherungen** in `BACKUPS/`

## Empfohlener Einstieg

1. Repository herunterladen, klonen oder als Vorlage verwenden.
2. `README.md` lesen.
3. `index.md` als kompakten Projekteinstieg öffnen.
4. Je nach Werkzeug `CLAUDE.md`, `claude.md` oder `AGENTS.md` nutzen.
5. Projektziel, Grenzen, Risiken und offene Fragen dokumentieren.
6. Erst danach Code, App-Dateien oder Website-Dateien in `PROJEKT/WORKSPACE/`
   anlegen.

## Schnellnavigation

- [Schnellstart](Schnellstart)
- [Ordnerstruktur](Ordnerstruktur)
- [KI-Agenten](KI-Agenten)
- [Prompt-Cheatsheet](Prompt-Cheatsheet)
- [Dokumentation pflegen](Dokumentation-pflegen)
- [Backups](Backups)
- [Versionierung und Releases](Versionierung-und-Releases)
- [Sicherheit](Sicherheit)
- [OpenRouter-Demo](OpenRouter-Demo)
- [FAQ](FAQ)

## Die Wichtigsten Dateien

| Datei | Zweck |
| --- | --- |
| `README.md` | Öffentliche Erklärung für Menschen auf GitHub. |
| `index.md` | Kurze Start- und Orientierungsseite im Projekt. |
| `CLAUDE.md` | Einstieg für Claude Code. |
| `claude.md` | Menschlich lesbare Claude-Hinweise. |
| `AGENTS.md` | Einstieg für ChatGPT Codex und kompatible Agenten. |
| `SECURITY.md` | Sicherheits- und Meldehinweise. |
| `CHANGELOG.md` | Nachvollziehbare Versionshistorie. |

## Grundprinzipien

- Root ruhig halten.
- Projektcode nur in `PROJEKT/WORKSPACE/` entwickeln.
- Dokumentation direkt mitführen.
- Entscheidungen und Risiken verständlich notieren.
- Keine Secrets, Tokens oder personenbezogenen Daten veröffentlichen.
- Demos getrennt halten.
- Änderungen über Git nachvollziehbar machen.

## Nächster Sinnvoller Schritt

Wenn du die Vorlage für ein neues Projekt nutzt, beginne mit dem
[Schnellstart](Schnellstart) und kopiere anschließend einen passenden Prompt
aus dem [Prompt-Cheatsheet](Prompt-Cheatsheet).
