# Ordnerstruktur

Die Vorlage trennt bewusst zwischen öffentlichem Einstieg, Vorlagenlogik,
konkretem Projektcode, Dokumentation, Demos und lokalen Backups.

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

## Root

Der Root bleibt ruhig. Dort liegen nur Dateien, die Menschen, GitHub oder
KI-Werkzeuge direkt erwarten.

## VORLAGE

Hier liegen Regeln, Agenten, Skills und Tooling-Hinweise. Dieser Bereich
beschreibt, wie gearbeitet wird.

## PROJEKT/WORKSPACE

Hier entsteht das konkrete neue Projekt. Code, Apps, Websites, Plugins,
Services, Konfigurationen und Assets gehören hier hinein.

## DOKUMENTATION

Hier werden Entscheidungen, Risiken, Setup, Versionen, Rechtliches und offene
Punkte gepflegt.

## DEMOS

Hier liegen getrennte Demo- und Testbereiche, zum Beispiel die OpenRouter-Demo.

## BACKUPS

Hier können lokale Sicherungen abgelegt werden. Backup-Inhalte werden
standardmäßig nicht versioniert.
