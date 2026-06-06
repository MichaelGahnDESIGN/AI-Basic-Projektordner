# Projektvorlage Für Claude Und Codex

Diese Vorlage ist die Ausgangssituation für neue Projekte mit Claude Code,
Claude Cowork und ChatGPT Codex.

## Root-Prinzip

Im Root liegen bewusst nur diese sichtbaren Dateien:

- `index.md`: menschlicher Startpunkt.
- `claude.md`: Startpunkt für Claude Code und Claude Cowork.
- `AGENTS.md`: Startpunkt für ChatGPT Codex.

Hidden Tool-Ordner wie `.codex/`, `.claude/`, `.agents/` und `.git/` dürfen im
Root bleiben, weil sie von den jeweiligen Werkzeugen erwartet werden.

## Ordnerstruktur

- `VORLAGE/`: Regeln, Agenten, Skills, Tooling-Dokumentation und historische
  Vorlagenentscheidungen.
- `PROJEKT/WORKSPACE/`: konkreter Projektcode, Assets und technische Dateien.
- `DOKUMENTATION/`: Vorlagen- und Entwicklungsdokumentation.
- `BACKUPS/`: lokale Sicherungen und Wiederherstellungsgrundlagen.
- `DEMOS/OPENROUTER/`: separater OpenRouter-Demo- und Testbereich.

## Neues Projekt Starten

1. Diesen Ordner kopieren und passend benennen.
2. `AGENTS.md` oder `claude.md` vom verwendeten Tool lesen lassen.
3. Projektkontext in
   `VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md` ausfüllen.
4. Freigaben und Grenzen in
   `VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md` dokumentieren.
5. Projektcode ausschließlich in `PROJEKT/WORKSPACE/` anlegen.
6. Wenn ein echtes Produkt entsteht, zusätzlich
   `PROJEKT/WORKSPACE/DOKUMENTATION/` pflegen.
7. Entscheidungen, Risiken und Versionen in `DOKUMENTATION/` aktualisieren.

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
