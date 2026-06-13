# Projektvorlage Für Claude Und Codex

Diese Vorlage ist die Ausgangssituation für neue Projekte mit Claude Code,
Claude Cowork und ChatGPT Codex.

## Root-Prinzip

Im Root liegen bewusst nur diese sichtbaren Dateien:

- `README.md`: öffentliche GitHub-Erklärung für Menschen.
- `LICENSE`: Lizenz der Vorlage.
- `CHANGELOG.md`: öffentlicher Änderungsverlauf.
- `VERSION`: aktuelle Vorlagenversion.
- `SECURITY.md`: Sicherheitsrichtlinie.
- `index.md`: menschlicher Startpunkt.
- `CLAUDE.md`: automatisch erkannter Einstieg für Claude Code.
- `claude.md`: menschenfreundliche Claude-Erklärung.
- `AGENTS.md`: Startpunkt für ChatGPT Codex.

Hidden Tool-Ordner wie `.codex/`, `.claude/`, `.agents/` und `.git/` dürfen im
Root bleiben, weil sie von den jeweiligen Werkzeugen erwartet werden.

## Ordnerstruktur

- `VORLAGE/`: Regeln, Agenten, Skills, Tooling-Dokumentation und historische
  Vorlagenlogik.
- `PROJEKT/WORKSPACE/`: konkreter Projektcode, Assets und technische Dateien.
- `DOKUMENTATION/`: Vorlagen- und Entwicklungsdokumentation.
- `BACKUPS/`: lokale Sicherungen und Wiederherstellungsgrundlagen.
- `DEMOS/OPENROUTER/`: separater OpenRouter-Demo- und Testbereich.

## Neues Projekt Starten

1. Diesen Ordner kopieren und passend benennen.
2. `DOKUMENTATION/Informationen/Start_und_Orientierung.md` lesen.
3. `AGENTS.md`, `CLAUDE.md` oder `claude.md` vom verwendeten Tool lesen lassen.
4. Projektkontext in
   `VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md` ausfüllen.
5. Freigaben und Grenzen in
   `VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md` dokumentieren.
6. Projektcode ausschließlich in `PROJEKT/WORKSPACE/` anlegen.
7. Wenn ein echtes Produkt entsteht, zusätzlich
   `PROJEKT/WORKSPACE/DOKUMENTATION/` pflegen.
8. Entscheidungen, Risiken und Versionen in `DOKUMENTATION/` aktualisieren.

## Beste Übersicht

Die ausführliche, nutzerfreundliche Gesamtübersicht liegt hier:

`DOKUMENTATION/Informationen/Start_und_Orientierung.md`

## Dokumentation Aktualisieren

Bei Strukturänderungen oder neuen Dokumentationsinhalten:

```bash
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
```

## Sicherheit

Keine Secrets, Tokens, Passwörter, Zahlungsdaten, personenbezogenen Daten,
Sessiondaten oder Kundendaten in Code, Dokumentation, Logs, Prompts oder Git
ablegen.

## Git

Die Vorlage ist als Root-Repository versioniert. Vor Commits prüfen:

```bash
git status --short --branch
```
