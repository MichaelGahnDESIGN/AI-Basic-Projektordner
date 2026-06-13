# Schnellstart

## Projekt Aus Der Vorlage Starten

1. Repository herunterladen, klonen oder als Vorlage verwenden.
2. Ordner für das neue Projekt kopieren.
3. Ordner passend zum Projekt benennen.
4. `README.md` und `index.md` lesen.
5. Für Claude Code `CLAUDE.md` verwenden.
6. Für ChatGPT Codex `AGENTS.md` verwenden.
7. Projektkontext in `VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md` ausfüllen.
8. Freigaben und Grenzen in
   `VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md` dokumentieren.
9. Code nur in `PROJEKT/WORKSPACE/` anlegen.

## Erster Prompt Für Claude Code

```text
Lies CLAUDE.md, claude.md und index.md.

Richte diese Vorlage als neue Projektbasis ein.

Projektziel:
<kurze Beschreibung>

Bitte prüfe die Struktur, fülle den Projektkontext soweit möglich vor,
dokumentiere offene Fragen und lege noch keinen Produktcode an, bevor der
Arbeitskontext klar ist.
```

## Erster Prompt Für ChatGPT Codex

```text
Lies AGENTS.md, index.md und
DOKUMENTATION/Informationen/Start_und_Orientierung.md.

Nutze diese Vorlage als Startpunkt für ein neues Projekt.

Projektziel:
<kurze Beschreibung>

Bitte analysiere die Struktur, erstelle einen kurzen Plan und beginne erst
danach mit Dateien in PROJEKT/WORKSPACE/.
```

## Nach Dem Start Prüfen

```bash
git status --short --branch
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
npm --prefix DEMOS/OPENROUTER run check
```
