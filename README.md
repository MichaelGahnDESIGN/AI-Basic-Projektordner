# AI Basic Projektordner

Eine saubere, deutsch dokumentierte Projektvorlage für KI-gestützte Arbeit mit
Claude Code, Claude Cowork, ChatGPT Codex und ähnlichen Agenten.

Diese Vorlage ist dafür gedacht, als kompletter Projektordner heruntergeladen,
kopiert und für neue Projekte wiederverwendet zu werden. Menschen sollen sich
schnell orientieren können, und KI-Agenten sollen klare Startdateien,
Arbeitsregeln, Skills, Dokumentationsbereiche und Sicherheitsgrenzen finden.

Wichtig: Das ist keine App-, Framework- oder Website-Vorlage. Es ist eine
neutrale KI-Projektordner-Vorlage, die vor dem eigentlichen Bauen Ordnung,
Kontext, Dokumentation und Arbeitsregeln bereitstellt.

## Für Wen Ist Diese Vorlage Gedacht?

- Menschen, die neue Software-, Web-, App-, Automations- oder KI-Projekte
  sauber starten möchten.
- Teams, die mit Claude Code, Claude Cowork oder ChatGPT Codex arbeiten.
- Agenten, die eine eindeutige Ordnerstruktur, klare Anweisungen und eine
  nachvollziehbare Dokumentation benötigen.
- Projekte, bei denen Datenschutz, Sicherheit, Wartbarkeit und verständliche
  Ablage wichtiger sind als schnelle Sammeldateien.

## Was Ist Enthalten?

- Startdateien für Menschen, Claude und Codex.
- Eine klare Trennung zwischen Vorlage, Projektcode, Dokumentation, Demos und
  Backups.
- Wiederverwendbare Agentenregeln und Skills.
- Eine HTML-Dokumentationsübersicht.
- Eine separate OpenRouter-Demo als Beispiel für API-Tests.
- Sicherheitsregeln für Secrets, personenbezogene Daten, Zahlungen,
  Authentifizierung, Logs und Admin-Funktionen.
- GitHub-Dateien für Prüfung, Release-Hinweise, Sicherheitsrichtlinie und
  öffentliche Nutzung.
- GitHub Wiki mit Schnellstart, Ordnerstruktur, Prompt-Cheatsheet,
  Sicherheits-, Backup- und Release-Hinweisen.

## Ordnerstruktur

```text
.
├── README.md
├── LICENSE
├── CHANGELOG.md
├── VERSION
├── SECURITY.md
├── index.md
├── CLAUDE.md
├── claude.md
├── AGENTS.md
├── .agents/
├── .claude/
├── .codex/
├── .github/
├── VORLAGE/
├── PROJEKT/
├── DOKUMENTATION/
├── DEMOS/
└── BACKUPS/
```

## Die Wichtigsten Bereiche

| Bereich | Zweck |
| --- | --- |
| `README.md` | Öffentliche Erklärung für GitHub und Menschen. |
| `SECURITY.md` | Sicherheitsrichtlinie und Meldeweg. |
| `index.md` | Kurzer Einstieg in die lokale Vorlage. |
| `CLAUDE.md` | Automatisch erkannter Einstieg für Claude Code. |
| `claude.md` | Menschenfreundliche Claude-Erklärung. |
| `AGENTS.md` | Startanweisung für ChatGPT Codex. |
| `.agents/skills/` | Repo-Skills für Codex-kompatible Arbeitsabläufe. |
| `.claude/` | Claude-spezifische Adapter und Hilfen. |
| `.codex/` | Codex-Konfiguration. |
| `.github/` | GitHub-Workflows, Release-Konfiguration und Issue-Vorlagen. |
| `VORLAGE/` | Regeln, Agenten, Skills und Tooling-Dokumentation. |
| `PROJEKT/WORKSPACE/` | Hier entsteht der konkrete Projektcode. |
| `DOKUMENTATION/` | Entscheidungen, Risiken, Setup, Versionen und Rechtliches. |
| `DEMOS/OPENROUTER/` | Separater Demo- und Testbereich für OpenRouter. |
| `BACKUPS/` | Lokale Sicherungen, standardmäßig nicht versioniert. |

## Schnellstart

1. Repository herunterladen oder klonen.
2. Ordner für dein neues Projekt kopieren.
3. Projektordner passend umbenennen.
4. `index.md` lesen.
5. Je nach Tool zusätzlich `CLAUDE.md`, `claude.md` oder `AGENTS.md` lesen
   lassen.
6. Projektkontext in `VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md` ausfüllen.
7. Freigaben und Grenzen in
   `VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md` dokumentieren.
8. Eigenen Code ausschließlich in `PROJEKT/WORKSPACE/` anlegen.
9. Relevante Entscheidungen, Risiken und Versionen in `DOKUMENTATION/`
   pflegen.

### Prompt-Beispiele Für Den Projektstart

Die folgenden Prompts können nach dem Kopieren der Vorlage direkt im jeweiligen
KI-Werkzeug verwendet werden. Ersetze die Platzhalter durch dein echtes
Projektziel.

#### Claude Cowork

```text
Bitte richte dieses Projekt als neue Arbeitsgrundlage ein.

Lies zuerst claude.md, index.md und
DOKUMENTATION/Informationen/Start_und_Orientierung.md.

Ziel des neuen Projekts:
<kurze Beschreibung des Projekts>

Bitte prüfe die vorhandene Ordnerstruktur, fülle den Projektkontext in
VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md sinnvoll vor, dokumentiere offene
Fragen in DOKUMENTATION/ToDo/Offene_Punkte.md und lege noch keinen Produktcode
an, bevor der Arbeitskontext klar ist.
```

#### Claude Code

```text
Starte dieses Projekt aus der Vorlage heraus.

Lies CLAUDE.md, claude.md, index.md, VORLAGE/REGELN/GRUNDREGELN.md und
VORLAGE/AI/PROJEKTREGELN/PROJEKTREGELN.md.

Projektidee:
<kurze Beschreibung des Projekts>

Bitte bereite PROJEKT/WORKSPACE/ als sauberen Projektarbeitsbereich vor,
notiere Entscheidungen in DOKUMENTATION/Informationen/Entscheidungen.md,
beachte Datenschutz und Sicherheit, und halte alle neuen Dateien in passenden
Ordnern.
```

#### ChatGPT Codex

```text
Nutze diese Vorlage als Startpunkt für ein neues Projekt.

Lies zuerst AGENTS.md, index.md,
DOKUMENTATION/Informationen/Start_und_Orientierung.md und die Regeln unter
VORLAGE/AI/PROJEKTREGELN/.

Projektziel:
<kurze Beschreibung des Projekts>

Bitte analysiere die Struktur, erstelle einen kurzen Umsetzungsplan, fülle
fehlende Projektkontext-Dateien soweit möglich allgemein aus und beginne erst
danach mit Code in PROJEKT/WORKSPACE/. Keine Secrets oder personenbezogenen
Daten speichern oder ausgeben.
```

## Prompt-Cheatsheet Für Die Arbeit Mit Der Vorlage

Diese Prompts helfen beim täglichen Arbeiten mit der Ordnerstruktur. Sie sind
bewusst allgemein formuliert und können in Claude Cowork, Claude Code oder
ChatGPT Codex angepasst verwendet werden.

### Struktur Prüfen Und Aufräumen

```text
Bitte prüfe die komplette Projektstruktur.

Ziel:
- Root nur mit erlaubten Start- und GitHub-Dateien
- Projektcode nur in PROJEKT/WORKSPACE/
- Dokumentation sauber in DOKUMENTATION/
- Demos getrennt in DEMOS/
- keine Secrets, Logs, .env-Dateien, node_modules oder privaten Pfade

Erstelle zuerst eine kurze Fundliste, behebe eindeutige Strukturfehler und
dokumentiere relevante Änderungen in DOKUMENTATION/Informationen/Entscheidungen.md.
```

### Projektkontext Nachschärfen

```text
Bitte hilf mir, den Projektkontext sauber auszufüllen.

Lies VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md und
VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md.

Stelle mir nacheinander kurze Fragen zu Ziel, Zielgruppe, Technik, Grenzen,
Risiken, Datenschutz, Budget und Erfolgskriterien. Aktualisiere die Dateien
erst, wenn die Antworten klar genug sind.
```

### Neuen Skill Erstellen

```text
Bitte erstelle einen neuen wiederverwendbaren Skill für diese Vorlage.

Skill-Zweck:
<Was soll der Skill leisten?>

Bitte lege den Skill in einem passenden Ordner unter VORLAGE/AI/SKILLS/ an,
schreibe ihn auf Deutsch, erkläre Auslöser, Ablauf, Sicherheitsregeln und
Prüfschritte. Ergänze bei Bedarf auch einen Codex-kompatiblen Repo-Skill unter
.agents/skills/.
```

### Skill Für Backup Erstellen

```text
Bitte erstelle einen Skill "Backup erstellen".

Der Skill soll erklären:
- wann ein Backup sinnvoll ist
- welche Ordner gesichert werden sollen
- welche Dateien nie ins Backup gehören, zum Beispiel echte .env-Dateien
- wie ein Backup benannt wird
- wie das Backup in DOKUMENTATION/Projektbetrieb/Backups.md dokumentiert wird
- welche Prüfung nach dem Backup erfolgen soll

Bitte schreibe alles so, dass auch Nicht-Programmierer den Ablauf verstehen.
```

### Backup Erstellen

```text
Bitte erstelle ein lokales Backup dieses Projektordners.

Wichtig:
- keine echten .env-Dateien, node_modules, Logs oder temporären Dateien sichern
- Backup sauber benennen
- Backup nicht ungefragt in Git aufnehmen
- Ergebnis in DOKUMENTATION/Projektbetrieb/Backups.md dokumentieren
- nach dem Backup git status --short --branch prüfen
```

### Backup Laden Oder Wiederherstellen

```text
Bitte hilf mir, ein Backup kontrolliert wiederherzustellen.

Backup:
<Name oder Pfad des Backups>

Bitte prüfe zuerst den aktuellen Git-Status, erkläre mögliche Risiken, erstelle
einen Wiederherstellungsplan und überschreibe keine vorhandenen Änderungen ohne
ausdrückliche Freigabe.
```

### Dokumentation Aktualisieren

```text
Bitte aktualisiere die Projektdokumentation nach den letzten Änderungen.

Prüfe:
- DOKUMENTATION/Informationen/Entscheidungen.md
- DOKUMENTATION/Informationen/Risiken.md
- DOKUMENTATION/Projektbetrieb/Versionen.md
- DOKUMENTATION/ToDo/Offene_Punkte.md
- README.md, falls die Änderung für Menschen wichtig ist

Führe danach den Dokumentationsgenerator aus und prüfe, ob die generierten Daten
reproduzierbar bleiben.
```

### Versionierung Und Changelog Pflegen

```text
Bitte bereite eine neue Version vor.

Neue Version:
<Versionsnummer>

Bitte prüfe die bisherigen Änderungen, schlage eine sinnvolle Kurzbeschreibung
vor, aktualisiere VERSION, CHANGELOG.md und
DOKUMENTATION/Projektbetrieb/Versionen.md. Erstelle danach einen Commit-Vorschlag
und nenne die passenden Prüfkommandos.
```

### Release Für GitHub Vorbereiten

```text
Bitte bereite einen GitHub-Release vor.

Version:
<Versionsnummer>

Bitte prüfe:
- Arbeitsbaum sauber?
- VERSION und CHANGELOG.md aktuell?
- README.md verständlich?
- GitHub Actions grün?
- keine Secrets oder privaten Pfade?

Erstelle danach den Tag, pushe Branch und Tag und erstelle den GitHub-Release
mit klaren Release Notes.
```

### Sicherheitsprüfung

```text
Bitte führe eine Sicherheits- und Datenschutzprüfung für dieses Projekt durch.

Suche nach:
- Secrets, Tokens, Passwörtern und API-Schlüsseln
- personenbezogenen Daten
- Zahlungs-, Rechnungs- oder Kundendaten
- unsicheren Logs
- privaten lokalen Pfaden
- riskanten Drittanbieter-Abhängigkeiten

Bitte nichts Sensibles ausgeben. Fasse nur Fundorte, Risiko und empfohlene
Maßnahmen zusammen.
```

### OpenRouter-Demo Prüfen

```text
Bitte prüfe die OpenRouter-Demo.

Wichtig:
- DEMOS/OPENROUTER/ bleibt getrennt von PROJEKT/WORKSPACE/
- echte API-Schlüssel bleiben nur lokal in .env
- .env darf nicht versioniert werden
- npm --prefix DEMOS/OPENROUTER run check ausführen
- Risiken oder Kostenhinweise in DOKUMENTATION/Informationen/Risiken.md und
  DOKUMENTATION/Informationen/Kosten_und_Lizenzen.md dokumentieren
```

### Übergabe An Einen Anderen KI-Agenten

```text
Bitte erstelle eine kurze Übergabe für einen anderen KI-Agenten.

Die Übergabe soll enthalten:
- Ziel des Projekts
- aktueller Stand
- wichtige Dateien
- offene Aufgaben
- Risiken
- zuletzt ausgeführte Prüfungen
- klare nächste Schritte

Keine Secrets, privaten Pfade oder personenbezogenen Daten aufnehmen.
```

### Code Oder App Im Workspace Starten

```text
Bitte starte eine neue technische Umsetzung in PROJEKT/WORKSPACE/.

Projektidee:
<kurze Beschreibung>

Bitte lies zuerst die Regeln, schlage eine klare Ordnerstruktur vor, lege
Funktionen, Komponenten, Views, Services und Konfigurationen in getrennten
Dateien an und dokumentiere Architekturentscheidungen verständlich auf Deutsch.
```

### Projekt Für Kopie Bereinigen

```text
Bitte bereite dieses Projekt darauf vor, als Vorlage kopiert zu werden.

Entferne oder ignoriere:
- node_modules
- Build-Ausgaben
- Logs
- echte .env-Dateien
- lokale Backups
- temporäre Dateien
- private Pfade

Prüfe danach Root-Struktur, Dokumentationsstruktur, Git-Status und README.
```

## Arbeiten Mit KI-Agenten

### ChatGPT Codex

Codex startet über `AGENTS.md`. Zusätzlich sind die Repo-Skills unter
`.agents/skills/` für wiederkehrende Aufgaben vorbereitet.

### Claude Code Und Claude Cowork

Claude Code erkennt `CLAUDE.md` automatisch. Diese Datei importiert `AGENTS.md`
und verweist zusätzlich auf `claude.md`, damit die gemeinsame Agentenlogik nicht
doppelt gepflegt werden muss. `claude.md` bleibt als menschenfreundliche
Erklärung erhalten.

### Gemeinsame Agentenlogik

Die operative Agentenlogik liegt in `VORLAGE/AI/AGENTEN/`. Regeln und
projektspezifische Grenzen liegen in `VORLAGE/AI/PROJEKTREGELN/`.

## Dokumentation

Die zentrale Leseseite für Menschen und KI-Agenten liegt hier:

```text
DOKUMENTATION/Informationen/Start_und_Orientierung.md
```

Die visuelle HTML-Übersicht liegt hier:

```text
DOKUMENTATION/index.html
```

Nach Strukturänderungen können die Dokumentationsdaten aktualisiert werden:

```bash
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
```

## Sicherheit Und Datenschutz

Diese Vorlage ist bewusst sicherheitsorientiert. Folgende Inhalte dürfen nicht
in Code, Dokumentation, Logs, Prompts oder Git abgelegt werden:

- API-Schlüssel
- Tokens
- Passwörter
- Sessiondaten
- Zahlungsdaten
- personenbezogene Daten
- Kundendaten
- private Rechnungs- oder Vertragsdaten

Lokale `.env`-Dateien bleiben lokal. Öffentliche Beispiele dürfen nur
Platzhalter enthalten.

## OpenRouter-Demo

Die OpenRouter-Demo liegt bewusst getrennt vom eigentlichen Projektbereich:

```text
DEMOS/OPENROUTER/
```

Sie dient als isolierter Testbereich und ist kein Pflichtbestandteil eines
neuen Projekts. Vor produktiver Nutzung müssen Kosten, Datenschutz,
Modellverfügbarkeit und Anbieterbedingungen geprüft werden.

Prüfung:

```bash
npm --prefix DEMOS/OPENROUTER run check
```

## GitHub Und Releases

Diese Vorlage enthält GitHub-Dateien für öffentliche Nutzung:

- `.github/workflows/quality-check.yml`: prüft Struktur, sensible lokale
  Artefakte, generierte Dokumentationsdaten und die OpenRouter-Demo.
- `.github/release.yml`: Konfiguration für automatisch erzeugte Release Notes.
- `.github/ISSUE_TEMPLATE/`: einfache Vorlagen für Fehler und Vorschläge.
- `SECURITY.md`: Sicherheitsrichtlinie und Kontaktweg für Sicherheitsprobleme.

Wiki:

```text
https://github.com/MichaelGahnDESIGN/AI-Basic-Projektordner/wiki
```

Versionierte Wiki-Quellen:

```text
DOKUMENTATION/GitHub-Wiki/
```

Aktuelle Version:

```text
1.0.1
```

## Lizenz

Diese Projektvorlage steht unter der MIT-Lizenz. Details stehen in `LICENSE`.

Abhängigkeiten, Dienste und Drittanbieter, die in Beispielen erwähnt werden,
unterliegen ihren eigenen Lizenz- und Nutzungsbedingungen.

## Impressum

Angaben gemäß § 5 DDG (Digitale-Dienste-Gesetz)

Michael Gahn DESIGN  
Michael Gahn  
Dr.-Theodor-Brugsch Str. 12  
08529 Plauen  
Sachsen  
Deutschland

Tel.: +49 (0) 176 557 647 48  
E-Mail: Anfrage@Michael-Gahn.de

Umsatzsteuer-Identifikationsnummer gemäß §27 a Umsatzsteuergesetz:  
Steuernummer: 223/222/02451  
Ust-ID: DE288143343
