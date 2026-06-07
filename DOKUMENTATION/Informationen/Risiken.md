# Risiken

Hier werden bekannte Risiken und Unsicherheiten dokumentiert.

Pro Eintrag festhalten:

- Risiko
- Eintrittswahrscheinlichkeit
- Auswirkung
- betroffene Bereiche
- Absicherung oder nächste Maßnahme

## OpenRouter-API und Modellverfügbarkeit

- Risiko: Modell-IDs, Preise, Provider, Limits oder Verfügbarkeit bei
  OpenRouter können sich ändern.
- Eintrittswahrscheinlichkeit: Mittel.
- Auswirkung: Lokale Demo kann fehlschlagen oder unerwartete Kosten auslösen.
- Betroffene Bereiche: `DEMOS/OPENROUTER/`, Kosten- und Datenschutzbewertung.
- Absicherung oder nächste Maßnahme: Vor produktiver Nutzung aktuelle
  OpenRouter-Dokumentation prüfen, Kostenlimit definieren und keine sensiblen
  Daten in Testprompts verwenden.

## Frühere verschachtelte Git-Struktur

- Risiko: Die frühere innere Git-Historie von `DOKUMENTATION/` könnte
  mit dem neuen Root-Git verwechselt werden.
- Eintrittswahrscheinlichkeit: Niedrig.
- Auswirkung: Alte Einzelhistorie der Dokumentation ist nicht mehr separat
  verfügbar.
- Betroffene Bereiche: Projektroot, Projektdokumentation.
- Absicherung oder nächste Maßnahme: Das Root-Git ist die aktive und
  maßgebliche Versionierung. Relevante Strukturänderungen werden zusätzlich in
  Entscheidungen, Versionen und Backups dokumentiert.

## Lokaler Paketmanager auf Workspace-Volume

- Risiko: NPM-Installationen auf dem aktuellen Workspace-Volume können hängen
  oder unvollständige `node_modules`-Zustände erzeugen.
- Eintrittswahrscheinlichkeit: Mittel.
- Auswirkung: Abhängigkeiten könnten beschädigt werden oder ungetrackte
  Entwicklungsdateien während eines abgebrochenen Installationslaufs entfernen.
- Betroffene Bereiche: `PROJEKT/WORKSPACE/`, lokale Tests,
  spätere SDK-Integration.
- Absicherung oder nächste Maßnahme: Phase 1 nutzt keine NPM-Abhängigkeiten.
  Vor späterer SDK-Nutzung zuerst Paketinstallation und Lockfile in einem
  isolierten Testlauf prüfen.

## Safety-Check ist kein vollständiger DLP-Ersatz

- Risiko: Der lokale Safety-Check erkennt offensichtliche Secrets, kann aber
  nicht alle personenbezogenen Daten oder vertraulichen Inhalte sicher finden.
- Eintrittswahrscheinlichkeit: Mittel.
- Auswirkung: Agenten könnten trotz Prüfung sensible Inhalte in Markdown oder
  JSON-State eintragen.
- Betroffene Bereiche: `agent_comms.md`, `agent_comms.state.json`, Aufgaben,
  Chat, Logs und Übergaben.
- Absicherung oder nächste Maßnahme: Agentenregeln und Dokumentation betonen
  Datenminimierung. Sensible Details sollen als neutraler Blocker beschrieben
  statt gespeichert werden.
