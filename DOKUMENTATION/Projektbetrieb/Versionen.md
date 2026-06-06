# Versionen

Startversion dieser Vorlage pro neuem Projekt:

- Datum: `YYYY-MM-DD`
- Version: `0.0.1`
- Beschreibung: `Projektstart / Vorlage übernommen`
- Begründung: `Initialer Projektstand`
- Betroffene Bereiche: `Grundstruktur`
- Rücknahme oder Wiederherstellung: `Aus Vorlage erneut erzeugbar`

## 0.0.2 - Struktur- und Sicherheitsbereinigung

- Datum: `2026-06-07`
- Version: `0.0.2`
- Beschreibung: Projektidentität ergänzt, Git am Root initialisiert,
  OpenRouter-Demo als separater Bereich dokumentiert und Sicherheitsregeln für
  lokale Secrets geschärft. Die frühere innere Git-Historie der
  Projektdokumentation wurde gesichert, damit die Vorlage als ein sauberes
  Root-Repository funktioniert. Zusätzlich wurden Tool-Adapter für Claude Code,
  Claude Cowork und ChatGPT Codex ergänzt.
- Begründung: Die Vorlage enthielt noch Platzhalter und die Trennung zwischen
  `PROJEKT/WORKSPACE/` und `DEMOS/OPENROUTER/` war nicht eindeutig dokumentiert.
- Betroffene Bereiche: Projektroot, `VORLAGE/AI/PROJEKTREGELN/`,
  `DOKUMENTATION/`, `BACKUPS/`, `.claude/`, `.codex/`,
  `.agents/skills/`, `VORLAGE/AI/TOOLING/`, `DEMOS/OPENROUTER/`.
- Rücknahme oder Wiederherstellung: Über Git-Diff nachvollziehbar. Bei Bedarf
  können die geänderten Dokumentationsdateien auf den vorherigen Stand
  zurückgeführt werden.

## 0.0.3 - Zentrale Start- und Orientierungsdokumentation

- Datum: `2026-06-07`
- Version: `0.0.3`
- Beschreibung: Eine zentrale, gut lesbare Orientierung für Menschen und
  KI-Agenten wurde unter
  `DOKUMENTATION/Informationen/Start_und_Orientierung.md` ergänzt und in den
  Root-Einstiegen verlinkt.
- Begründung: Die Vorlage soll als kopierbarer Projektstart schneller
  verständlich, nutzerfreundlicher und für KI-Agenten eindeutiger sein.
- Betroffene Bereiche: `index.md`, `AGENTS.md`, `claude.md`,
  `DOKUMENTATION/Informationen/`, `DOKUMENTATION/Dokumentation-Skills/`.
- Rücknahme oder Wiederherstellung: Über Git-Diff nachvollziehbar. Die Datei
  kann entfernt werden, wenn eine andere zentrale Übersichtsstruktur eingeführt
  wird.

## 0.0.4 - Finale Vorlagenbereinigung

- Datum: `2026-06-07`
- Version: `0.0.4`
- Beschreibung: Lokale Artefakte wurden entfernt, damit neue Projekte keine
  unnötigen Abhängigkeiten, Secrets, temporären Backups oder veralteten
  Strukturhinweise übernehmen. Entfernt wurden unter anderem
  `DEMOS/OPENROUTER/node_modules/`, `DEMOS/OPENROUTER/.env`, das temporäre
  Doku-Git-Backup und eine alte Historien-Datei.
- Begründung: Die Vorlage soll als sauberer, sicherer und direkt kopierbarer
  Startpunkt für Claude Code, Claude Cowork und ChatGPT Codex dienen.
- Betroffene Bereiche: `DEMOS/OPENROUTER/`, `BACKUPS/`, `VORLAGE/`,
  `DOKUMENTATION/`.
- Rücknahme oder Wiederherstellung: Über Git-Diff nachvollziehbar. Gelöschte
  lokale Abhängigkeiten können mit `npm --prefix DEMOS/OPENROUTER install`
  erneut erzeugt werden.

## 1.0.1 - Öffentliche GitHub-Vorlage

- Datum: `2026-06-07`
- Version: `1.0.1`
- Beschreibung: Die Vorlage wurde für die öffentliche Bereitstellung als
  GitHub-Repository vorbereitet. Ergänzt wurden `README.md`, `LICENSE`,
  `CHANGELOG.md`, `VERSION`, GitHub-Workflow, Release-Konfiguration und
  Issue-Vorlagen. Lokale absolute Beispielpfade wurden entfernt.
- Begründung: Die Ordnerstruktur soll allgemein verständlich, herunterladbar
  und ohne private lokale Pfade als Vorlage nutzbar sein.
- Betroffene Bereiche: Root-Dateien, `.github/`, `DOKUMENTATION/`,
  `VORLAGE/AI/SKILLS/`, Dokumentationsgenerator.
- Rücknahme oder Wiederherstellung: Über Git-Diff nachvollziehbar. Die
  GitHub-spezifischen Dateien können entfernt werden, wenn die Vorlage nicht
  öffentlich bereitgestellt werden soll.
