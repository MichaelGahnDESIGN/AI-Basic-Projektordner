# Entscheidungen

Hier werden wichtige fachliche, technische, organisatorische oder gestalterische Entscheidungen dokumentiert.

Jeder Eintrag soll mindestens enthalten:

- Datum
- Entscheidung
- Begründung
- betroffene Bereiche
- mögliche Folgen oder Trade-offs

## 2026-06-07 - OpenRouter bleibt separat

- Datum: `2026-06-07`
- Entscheidung: `DEMOS/OPENROUTER/` bleibt als separater Demo- und Testbereich im
  Projektroot.
- Begründung: Die OpenRouter-Demo ist kein eigentliches Projektartefakt im
  Sinne von `PROJEKT/WORKSPACE/`, sondern ein isolierter API-Testbereich.
- Betroffene Bereiche: `DEMOS/OPENROUTER/`, `PROJEKT/WORKSPACE/`,
  `VORLAGE/AI/PROJEKTREGELN/PROJEKTREGELN.md`, Projektdokumentation.
- Folgen oder Trade-offs: Die Trennung erleichtert Orientierung und verhindert
  Vermischung mit späteren Projektumsetzungen. Dafür muss in der Dokumentation
  klar sichtbar bleiben, warum `DEMOS/OPENROUTER/` außerhalb von `PROJEKT/WORKSPACE/` liegt.

## 2026-06-07 - Git am Projektroot initialisiert

- Datum: `2026-06-07`
- Entscheidung: Das Projektroot wird lokal mit Git versioniert.
- Begründung: Die Projektregeln verlangen nachvollziehbare Versionierung. Vorher
  existierte Git nur innerhalb von `DOKUMENTATION/`.
- Betroffene Bereiche: Projektroot, `.gitignore`, Dokumentationsabläufe.
- Folgen oder Trade-offs: Änderungen können künftig am Root nachvollzogen
  werden.

## 2026-06-07 - Verschachteltes Doku-Git bereinigt

- Datum: `2026-06-07`
- Entscheidung: Die innere Git-Historie von `DOKUMENTATION/` wurde aus der
  aktiven Vorlage entfernt.
- Begründung: Die Vorlage soll als ein sauberer Git-Root funktionieren und beim
  Kopieren neuer Projekte keine Embedded-Repository-Warnung erzeugen.
- Betroffene Bereiche: `DOKUMENTATION/`, `BACKUPS/`, Root-Git.
- Folgen oder Trade-offs: Die frühere isolierte Doku-Historie wird nicht mehr
  separat weitergeführt. Die relevante aktuelle Historie liegt im
  Root-Git-Repository.

## 2026-06-07 - Tool-Adapter für Agenten ergänzt

- Datum: `2026-06-07`
- Entscheidung: Die Vorlage erhält eigene Adapter für Claude Code, Claude
  Cowork und ChatGPT Codex.
- Begründung: Unterschiedliche KI-Werkzeuge sollen dieselbe Projektstruktur als
  Startpunkt nutzen können, ohne eigene widersprüchliche Regeln zu erzeugen.
- Betroffene Bereiche: `.claude/`, `.codex/`, `.agents/skills/`,
  `VORLAGE/AI/TOOLING/`, Root-Dateien.
- Folgen oder Trade-offs: Die Root-Dateien bleiben kurz, während
  tool-spezifische Details geordnet in eigenen Adapterordnern liegen.

## 2026-06-07 - Zentrale Orientierungsdatei ergänzt

- Datum: `2026-06-07`
- Entscheidung: Die Vorlage erhält mit
  `DOKUMENTATION/Informationen/Start_und_Orientierung.md` eine zentrale
  Übersichtsdatei für Menschen und KI-Agenten.
- Begründung: Neue Projektkopien sollen schneller verständlich sein, ohne den
  Root mit zusätzlichen Dateien zu belasten.
- Betroffene Bereiche: `index.md`, `AGENTS.md`, `claude.md`,
  `DOKUMENTATION/Informationen/`, Dokumentationsgenerator.
- Folgen oder Trade-offs: Die Orientierung ist besser gebündelt. Die Datei muss
  bei strukturellen Änderungen mit gepflegt werden.

## 2026-06-07 - Finale Vorlagenbereinigung

- Datum: `2026-06-07`
- Entscheidung: Lokale Laufzeit- und Sicherungsartefakte wurden aus der
  Vorlage entfernt. Dazu gehören `DEMOS/OPENROUTER/node_modules/`,
  `DEMOS/OPENROUTER/.env`, das temporäre Doku-Git-Backup unter `BACKUPS/` und
  eine veraltete Implementierungs-Historie.
- Begründung: Neue Projekte sollen keine lokalen Abhängigkeiten, Secrets,
  temporären Backups oder überholten Strukturhinweise mitkopieren.
- Betroffene Bereiche: `DEMOS/OPENROUTER/`, `BACKUPS/`,
  `VORLAGE/`, `DOKUMENTATION/`.
- Folgen oder Trade-offs: Die Vorlage ist schlanker und sicherer. Abhängigkeiten
  werden bei Bedarf frisch installiert, und echte Backups bleiben lokale,
  bewusst dokumentierte Arbeitsartefakte.

## 2026-06-07 - Öffentliche GitHub-Vorlage mit MIT-Lizenz

- Datum: `2026-06-07`
- Entscheidung: Die Vorlage wird als öffentlich nutzbare GitHub-Vorlage mit
  `README.md`, `LICENSE`, `CHANGELOG.md`, `VERSION` und `.github/` vorbereitet.
- Begründung: Menschen sollen die Ordnerstruktur direkt herunterladen,
  verstehen und als Startpunkt für neue Projekte einsetzen können.
- Betroffene Bereiche: Root-Dateien, `.github/`, `DOKUMENTATION/`,
  `VORLAGE/AI/SKILLS/`, Dokumentationsgenerator.
- Folgen oder Trade-offs: Der Root enthält nun bewusst einige öffentliche
  GitHub-Dateien. Die allgemeine Aufräumregel bleibt erhalten: Weitere
  Root-Dateien brauchen einen dokumentierten Grund.
