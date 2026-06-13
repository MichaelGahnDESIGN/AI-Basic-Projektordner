# Prompt-Cheatsheet

Diese Prompts können direkt kopiert und angepasst werden.

## Struktur Prüfen

```text
Bitte prüfe die komplette Projektstruktur.

Achte auf:
- erlaubte Root-Dateien
- Projektcode nur in PROJEKT/WORKSPACE/
- Dokumentation in DOKUMENTATION/
- Demos in DEMOS/
- keine Secrets, Logs, echten .env-Dateien oder privaten Pfade

Behebe eindeutige Strukturfehler und dokumentiere relevante Änderungen.
```

## Dokumentation Aktualisieren

```text
Bitte aktualisiere die Projektdokumentation nach den letzten Änderungen.

Prüfe Entscheidungen, Risiken, Setup, Versionen, offene Punkte und README.
Führe danach den Dokumentationsgenerator aus und prüfe den Git-Status.
```

## Backup Erstellen

```text
Bitte erstelle ein lokales Backup.

Keine echten .env-Dateien, node_modules, Logs oder temporären Dateien sichern.
Backup sauber benennen und in DOKUMENTATION/Projektbetrieb/Backups.md
dokumentieren.
```

## Backup Wiederherstellen

```text
Bitte hilf mir, ein Backup kontrolliert wiederherzustellen.

Backup:
<Name oder Pfad>

Prüfe zuerst Git-Status und Risiken. Überschreibe keine Änderungen ohne
ausdrückliche Freigabe.
```

## Neuen Skill Erstellen

```text
Bitte erstelle einen neuen Skill.

Skill-Zweck:
<Beschreibung>

Lege ihn passend unter VORLAGE/AI/SKILLS/ an. Beschreibe Auslöser, Ablauf,
Sicherheitsregeln und Prüfungen auf Deutsch.
```

## Version Vorbereiten

```text
Bitte bereite eine neue Version vor.

Version:
<Versionsnummer>

Aktualisiere VERSION, CHANGELOG.md und
DOKUMENTATION/Projektbetrieb/Versionen.md. Prüfe danach Git-Status und
Quality-Checks.
```

## Sicherheitsprüfung

```text
Bitte führe eine Sicherheits- und Datenschutzprüfung durch.

Suche nach Secrets, Tokens, personenbezogenen Daten, Zahlungsdaten, privaten
Pfaden, unsicheren Logs und riskanten Drittanbieter-Abhängigkeiten.

Gib keine sensiblen Werte aus. Nenne nur Fundort, Risiko und Maßnahme.
```

## Übergabe An Anderen Agenten

```text
Bitte erstelle eine kurze Übergabe für einen anderen KI-Agenten.

Enthalten sein sollen Ziel, aktueller Stand, wichtige Dateien, offene Aufgaben,
Risiken, ausgeführte Prüfungen und nächste Schritte.
```
