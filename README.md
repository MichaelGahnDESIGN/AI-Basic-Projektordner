# AI Basic Projektordner

Eine saubere, deutsch dokumentierte Projektvorlage für KI-gestützte Arbeit mit
Claude Code, Claude Cowork, ChatGPT Codex und ähnlichen Agenten.

Diese Vorlage ist dafür gedacht, als kompletter Projektordner heruntergeladen,
kopiert und für neue Projekte wiederverwendet zu werden. Menschen sollen sich
schnell orientieren können, und KI-Agenten sollen klare Startdateien,
Arbeitsregeln, Skills, Dokumentationsbereiche und Sicherheitsgrenzen finden.

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
- GitHub-Dateien für Prüfung, Release-Hinweise und öffentliche Nutzung.

## Ordnerstruktur

```text
.
├── README.md
├── LICENSE
├── CHANGELOG.md
├── VERSION
├── index.md
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
| `index.md` | Kurzer Einstieg in die lokale Vorlage. |
| `claude.md` | Startanweisung für Claude Code und Claude Cowork. |
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
5. Je nach Tool zusätzlich `claude.md` oder `AGENTS.md` lesen lassen.
6. Projektkontext in `VORLAGE/AI/PROJEKTREGELN/ARBEITSKONTEXT.md` ausfüllen.
7. Freigaben und Grenzen in
   `VORLAGE/AI/PROJEKTREGELN/FREIGABEN_UND_GRENZEN.md` dokumentieren.
8. Eigenen Code ausschließlich in `PROJEKT/WORKSPACE/` anlegen.
9. Relevante Entscheidungen, Risiken und Versionen in `DOKUMENTATION/`
   pflegen.

## Arbeiten Mit KI-Agenten

### ChatGPT Codex

Codex startet über `AGENTS.md`. Zusätzlich sind die Repo-Skills unter
`.agents/skills/` für wiederkehrende Aufgaben vorbereitet.

### Claude Code Und Claude Cowork

Claude-basierte Werkzeuge starten über `claude.md`. Die Datei verweist auf die
wichtigsten Regeln, Arbeitsorte und Dokumentationspflichten.

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
