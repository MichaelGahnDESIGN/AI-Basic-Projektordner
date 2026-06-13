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

## 2026-06-07 - Claude-Code-Kompatibilität und Sicherheitsrichtlinie ergänzt

- Datum: `2026-06-07`
- Entscheidung: Die Vorlage erhält zusätzlich `CLAUDE.md` als automatisch
  erkennbaren Claude-Code-Einstieg und `SECURITY.md` als öffentliche
  Sicherheitsrichtlinie.
- Begründung: Claude Code arbeitet zuverlässig mit `CLAUDE.md`. Durch den
  Import von `AGENTS.md` bleibt die gemeinsame Agentenlogik dennoch gebündelt.
  `SECURITY.md` macht den Sicherheitsfokus für GitHub-Nutzer sofort sichtbar.
- Betroffene Bereiche: Root-Dateien, README, GitHub-Workflow,
  Dokumentationsgenerator, Start- und Orientierungsdokumentation.
- Folgen oder Trade-offs: Der Root enthält zwei zusätzliche sichtbare Dateien,
  die bewusst erlaubt sind. Die Vorlage wird dadurch besser verständlich und
  kompatibler für Anwender.

## 2026-06-07 - GitHub Wiki veröffentlicht

- Datum: `2026-06-07`
- Entscheidung: Das GitHub Wiki wurde veröffentlicht und zusätzlich unter
  `DOKUMENTATION/GitHub-Wiki/` als versionierte Quelle gespiegelt.
- Begründung: Anwender sollen die Vorlage direkt im GitHub Wiki lesen können,
  während die Wiki-Inhalte im Hauptrepository nachvollziehbar pflegbar bleiben.
- Betroffene Bereiche: README, Dokumentation, GitHub Wiki,
  `DOKUMENTATION/GitHub-Wiki/`.
- Folgen oder Trade-offs: Das Wiki ist öffentlich nutzbar. Änderungen an
  Wiki-Inhalten sollten künftig im Repo und im Wiki synchron gehalten werden.

## 2026-06-07 - Phase 1 MCP lokal und dependency-frei umgesetzt

- Datum: `2026-06-07`
- Entscheidung: Der konkrete Workspace erhält einen lokalen TypeScript/Node.js
  MCP-Server unter `PROJEKT/WORKSPACE/`, der ohne externe Laufzeitdienste und
  ohne installierte NPM-Abhängigkeiten startet.
- Begründung: Die Phase-1-Anforderung ist lokal-first. Auf dem lokalen Volume
  war eine Paketinstallation unzuverlässig; deshalb nutzt die erste Version den
  Node-Test-Runner und einen kleinen JSON-RPC/MCP-Server über stdio.
- Betroffene Bereiche: `PROJEKT/WORKSPACE/src/`,
  `PROJEKT/WORKSPACE/tests/`, `PROJEKT/WORKSPACE/docs/`.
- Folgen oder Trade-offs: Der Server bleibt sehr schlank und lokal
  nachvollziehbar. Später kann das offizielle MCP-SDK ergänzt werden, wenn ein
  stabiler Paketinstallationsprozess und ein Lockfile vorliegen.
