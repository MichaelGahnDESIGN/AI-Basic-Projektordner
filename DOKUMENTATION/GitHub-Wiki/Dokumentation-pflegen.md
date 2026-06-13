# Dokumentation Pflegen

Dokumentation ist ein Kernbestandteil der Vorlage.

## Wichtige Dateien

- `DOKUMENTATION/Informationen/Entscheidungen.md`
- `DOKUMENTATION/Informationen/Risiken.md`
- `DOKUMENTATION/Informationen/Kosten_und_Lizenzen.md`
- `DOKUMENTATION/Projektbetrieb/Setup.md`
- `DOKUMENTATION/Projektbetrieb/Deployment.md`
- `DOKUMENTATION/Projektbetrieb/Versionen.md`
- `DOKUMENTATION/ToDo/Offene_Punkte.md`

## Nach Strukturänderungen

```bash
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
```

Danach prüfen:

```bash
git diff --check
git status --short --branch
```

## Gute Dokumentation

Gute Dokumentation erklärt:

- was geändert wurde
- warum es geändert wurde
- welche Dateien betroffen sind
- welche Risiken bleiben
- welche Prüfung durchgeführt wurde
