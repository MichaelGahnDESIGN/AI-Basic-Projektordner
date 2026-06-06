# Codex Start

Diese Datei ist der Root-Einstieg für ChatGPT Codex.

Codex liest `AGENTS.md` automatisch als Projektanweisung. Diese Datei bleibt
deshalb bewusst der zentrale Codex-Einstieg im Root.

## Lies Zuerst

1. `index.md`
2. `VORLAGE/REGELN/GRUNDREGELN.md`
3. `VORLAGE/AI/PROJEKTREGELN/PROJEKTREGELN.md`
4. `VORLAGE/AI/AGENTEN/AGENTS.md`
5. `VORLAGE/AI/SKILLS/SKILL.md`
6. `DOKUMENTATION/SKILL.md`

## Codex-Arbeitsorte

- `PROJEKT/WORKSPACE/`: konkreter Projektinhalt.
- `PROJEKT/WORKSPACE/DOKUMENTATION/`: Dokumentation des konkreten Produkts.
- `DOKUMENTATION/`: Vorlagen- und Entwicklungsdokumentation.
- `VORLAGE/`: Regeln, Agenten, Skills und Tooling.
- `DEMOS/OPENROUTER/`: separater Demo- und Testbereich.

## Repo-Skills

Codex-sichtbare Skills liegen unter `.agents/skills/`.

Wichtig:

- Nutze `rg` für Suche.
- Verwende `git mv` für Strukturänderungen.
- Erzeuge keine zusätzlichen sichtbaren Root-Dateien.
- Prüfe nach OpenRouter-Änderungen:

```bash
npm --prefix DEMOS/OPENROUTER run check
```

## Sicherheit

Keine Secrets, Tokens, Zahlungsdaten, personenbezogenen Daten oder
Sessiondaten ausgeben, speichern oder versionieren.
