# Start und Orientierung

Diese Datei ist die zentrale Leseseite für Menschen und KI-Agenten. Sie erklärt
kurz, wofür diese Vorlage gedacht ist, wo welche Arbeit hingehört und wie ein
neues Projekt sauber gestartet wird.

## Kurzfassung

Diese Vorlage ist ein aufgeräumter Startpunkt für neue Projekte mit Claude
Code, Claude Cowork und ChatGPT Codex.

Die wichtigste Idee:

- Im Root liegen nur die Start-, GitHub-, Sicherheits- und Tool-Dateien.
- Regeln, Agenten und Skills liegen in `VORLAGE/`.
- Konkreter Projektcode liegt in `PROJEKT/WORKSPACE/`.
- Dokumentation liegt in `DOKUMENTATION/`.
- Demos und Tests liegen getrennt in `DEMOS/`.
- Backups bleiben lokal in `BACKUPS/`.

## Startdateien im Root

| Datei | Für wen | Zweck |
| --- | --- | --- |
| `README.md` | GitHub und Menschen | Öffentliche Erklärung der Vorlage. |
| `LICENSE` | Menschen und Plattformen | Lizenz der Vorlage. |
| `CHANGELOG.md` | Menschen und Releases | Öffentlicher Änderungsverlauf. |
| `VERSION` | Menschen und Automationen | Aktuelle Vorlagenversion. |
| `SECURITY.md` | Menschen, Teams und Plattformen | Sicherheitsrichtlinie und Meldeweg. |
| `index.md` | Menschen | Schneller Überblick über die Vorlage. |
| `CLAUDE.md` | Claude Code | Automatisch erkannter Claude-Code-Einstieg mit Import von `AGENTS.md`. |
| `claude.md` | Menschen, Claude Code und Claude Cowork | Menschenfreundliche Claude-Erklärung. |
| `AGENTS.md` | ChatGPT Codex | Automatisch gelesene Codex-Projektanweisung. |

Im Root sollen keine weiteren sichtbaren Dateien entstehen, außer es gibt einen
bewussten und dokumentierten Grund.

## Ordnerkarte

| Ordner | Inhalt | Wichtigste Regel |
| --- | --- | --- |
| `VORLAGE/` | Regeln, Agenten, Skills und Tooling | Hier liegt die Logik der Vorlage, nicht der Projektcode. |
| `PROJEKT/WORKSPACE/` | Code, Apps, Websites, Plugins, Assets | Hier entsteht das konkrete neue Projekt. |
| `DOKUMENTATION/` | Entscheidungen, Risiken, Setup, Versionen, Rechtliches | Jede relevante Änderung nachvollziehbar dokumentieren. |
| `BACKUPS/` | Lokale Sicherungen | Wird nicht versioniert, außer die README. |
| `DEMOS/OPENROUTER/` | Separater API-Testbereich | Bleibt getrennt vom Workspace. |
| `.agents/skills/` | Codex-sichtbare Repo-Skills | Kleine, wiederverwendbare Arbeitsabläufe. |
| `.claude/` | Claude-Adapter | Nur Tool-Hilfe, keine widersprüchlichen Regeln. |
| `.codex/` | Codex-Konfiguration | Nur Codex-Einstellungen, keine Projektdaten. |

## Neues Projekt in 7 Schritten

1. Vorlage kopieren und den Ordner passend benennen.
2. `index.md` lesen, dann je nach Tool `CLAUDE.md`, `claude.md` oder `AGENTS.md`.
3. `VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md` ausfüllen.
4. `VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md` ausfüllen.
5. Projektcode nur in `PROJEKT/WORKSPACE/` anlegen.
6. Bei einem echten Produkt `PROJEKT/WORKSPACE/DOKUMENTATION/` anlegen.
7. Entscheidungen, Risiken und Versionen in `DOKUMENTATION/` pflegen.

## Welches Tool liest was?

### Claude Code

Claude Code startet automatisch über `CLAUDE.md`.

`CLAUDE.md` importiert `AGENTS.md`, damit Claude und Codex dieselbe gemeinsame
Agentenlogik nutzen. Die Datei `claude.md` bleibt als menschenfreundliche
Erklärung erhalten.

Danach sind besonders wichtig:

- `VORLAGE/REGELN/GRUNDREGELN.md`
- `VORLAGE/AI/PROJEKTREGELN/PROJEKTREGELN.md`
- `VORLAGE/AI/AGENTEN/AGENTS.md`
- `DOKUMENTATION/SKILL.md`

### Claude Cowork

Claude Cowork nutzt ebenfalls `claude.md`, soll aber besonders auf parallele
Änderungen achten.

Wichtig:

- Vor Änderungen `git status --short --branch` prüfen.
- Keine fremden Änderungen überschreiben.
- Entscheidungen und Risiken dokumentieren.

### ChatGPT Codex

Codex liest `AGENTS.md` automatisch.

Zusätzlich relevant:

- `.codex/config.toml`
- `.agents/skills/projektstart/SKILL.md`
- `.agents/skills/dokumentation/SKILL.md`

## Entscheidungsbaum Für Neue Dateien

| Frage | Zielordner |
| --- | --- |
| Ist es konkreter Projektcode oder ein Asset? | `PROJEKT/WORKSPACE/` |
| Ist es eine Regel, Rolle oder ein Skill? | `VORLAGE/` |
| Ist es eine Entscheidung, ein Risiko oder Setup-Hinweis? | `DOKUMENTATION/` |
| Ist es eine lokale Sicherung? | `BACKUPS/` |
| Ist es ein API-Test oder Demo-Beispiel? | `DEMOS/` |
| Ist es eine Tool-Konfiguration für Codex oder Claude? | `.codex/`, `.claude/` oder `.agents/` |

Wenn die Antwort unklar ist, zuerst dokumentieren, warum die Datei gebraucht
wird. Danach den passendsten bestehenden Ordner nutzen.

## Dokumentationspflicht

Dokumentiert werden müssen:

- wichtige Entscheidungen
- offene Punkte
- Risiken
- Setup- und Deployment-Schritte
- Versionsstände
- rechtlich oder sicherheitsrelevante Änderungen
- Kosten, Lizenzen und Drittanbieter-Abhängigkeiten

Wichtige Dateien:

- `DOKUMENTATION/Informationen/Entscheidungen.md`
- `DOKUMENTATION/Informationen/Risiken.md`
- `DOKUMENTATION/Informationen/Kosten_und_Lizenzen.md`
- `DOKUMENTATION/Projektbetrieb/Setup.md`
- `DOKUMENTATION/Projektbetrieb/Deployment.md`
- `DOKUMENTATION/Projektbetrieb/Versionen.md`
- `DOKUMENTATION/ToDo/Offene_Punkte.md`

Nach Strukturänderungen:

```bash
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
```

## Sicherheitsregeln

Nie in Code, Dokumentation, Logs, Prompts oder Git ablegen:

- API-Schlüssel
- Tokens
- Passwörter
- Sessiondaten
- Zahlungsdaten
- personenbezogene Daten
- Kundendaten
- private Vertrags- oder Rechnungsdaten

Bei Datenschutz, Authentifizierung, Zahlung, Uploads, Logs oder Admin-Funktionen
immer bewusst prüfen:

- Welche Daten werden wirklich gebraucht?
- Wo werden sie gespeichert?
- Wer hat Zugriff?
- Werden Secrets sicher getrennt?
- Gibt es eine dokumentierte Rückfallstrategie?

## Prüfkommandos

Allgemeiner Status:

```bash
git status --short --branch
```

Dokumentationsdaten aktualisieren:

```bash
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
```

OpenRouter-Demo prüfen:

```bash
npm --prefix DEMOS/OPENROUTER run check
```

Root sauber prüfen:

```bash
find . -maxdepth 1 -type f \
  ! -name 'README.md' \
  ! -name 'LICENSE' \
  ! -name 'CHANGELOG.md' \
  ! -name 'VERSION' \
  ! -name 'SECURITY.md' \
  ! -name 'index.md' \
  ! -name 'CLAUDE.md' \
  ! -name 'claude.md' \
  ! -name 'AGENTS.md' \
  ! -name '.*' \
  -print
```

Wenn dieser letzte Befehl etwas ausgibt, liegt wahrscheinlich eine sichtbare
Datei falsch im Root.

## Gute Arbeitsweise für KI-Agenten

1. Erst Root-Startdatei lesen.
2. Danach passende Regel- und Skill-Dateien lesen.
3. Vor Änderungen `git status --short --branch` prüfen.
4. Dateien in den passenden Ordner legen.
5. Keine Secrets ausgeben.
6. Relevante Entscheidungen dokumentieren.
7. Passende Prüfkommandos ausführen.
8. Ergebnis kurz und ehrlich zusammenfassen.

## Merksatz

Root bleibt ruhig. `VORLAGE/` erklärt. `PROJEKT/WORKSPACE/` baut.
`DOKUMENTATION/` erinnert. `DEMOS/` testet. `BACKUPS/` sichert.
