# Claude Start

Diese Datei ist die menschenfreundliche Claude-Erklärung.

Claude Code erkennt automatisch die Root-Datei `CLAUDE.md`. Diese Datei
importiert `AGENTS.md` und verweist auf diesen Einstieg, damit Claude und Codex
mit möglichst wenig doppelter Logik arbeiten.

## Lies Zuerst

1. `CLAUDE.md`
2. `index.md`
3. `DOKUMENTATION/Informationen/Start_und_Orientierung.md`
4. `VORLAGE/REGELN/GRUNDREGELN.md`
5. `VORLAGE/AI/PROJEKTREGELN/PROJEKTREGELN.md`
6. `VORLAGE/AI/AGENTEN/AGENTS.md`
7. `VORLAGE/AI/SKILLS/SKILL.md`
8. `DOKUMENTATION/SKILL.md`

## Arbeitsorte

- `PROJEKT/WORKSPACE/`: konkreter Projektinhalt.
- `PROJEKT/WORKSPACE/DOKUMENTATION/`: Dokumentation des konkreten Produkts.
- `DOKUMENTATION/`: Vorlagen- und Entwicklungsdokumentation.
- `VORLAGE/`: Regeln, Agenten, Skills und Tooling.
- `DEMOS/OPENROUTER/`: separater Demo- und Testbereich.

## Claude-Hinweise

- Nutze `.claude/` nur als lokalen Tool-Adapter.
- Erzeuge keine neuen Root-Dateien, außer der Mensch fordert es ausdrücklich.
- Für GitHub und Tool-Kompatibilität bleiben `README.md`, `LICENSE`,
  `CHANGELOG.md`, `VERSION`, `SECURITY.md`, `CLAUDE.md`, `claude.md`,
  `AGENTS.md` und `index.md` im Root erlaubt.
- Dokumentiere wichtige Entscheidungen auf Deutsch in `DOKUMENTATION/`.
- Überschreibe keine fremden Änderungen.
- Gib keine Secrets, Tokens oder personenbezogenen Daten aus.

## Standardauftrag Bei Neuer Projektkopie

Richte die Projektkopie anhand dieser Vorlage ein, fülle den Arbeitskontext in
`VORLAGE/AI/PROJEKTREGELN/` aus und lege Projektcode nur in
`PROJEKT/WORKSPACE/` an.
