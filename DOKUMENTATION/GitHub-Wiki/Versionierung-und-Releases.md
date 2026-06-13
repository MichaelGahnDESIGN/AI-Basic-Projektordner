# Versionierung Und Releases

Die Vorlage nutzt Git und GitHub Releases.

## Wichtige Dateien

- `VERSION`
- `CHANGELOG.md`
- `DOKUMENTATION/Projektbetrieb/Versionen.md`
- `.github/release.yml`

## Vor Einem Release Prüfen

```bash
git status --short --branch
python3 DOKUMENTATION/Dokumentation-Skills/generate_dokumentationsdaten.py
npm --prefix DEMOS/OPENROUTER run check
git diff --check
```

## Release-Ablauf

1. Version festlegen.
2. `VERSION` aktualisieren.
3. `CHANGELOG.md` aktualisieren.
4. `DOKUMENTATION/Projektbetrieb/Versionen.md` aktualisieren.
5. Änderungen committen.
6. Tag erstellen.
7. Branch und Tag pushen.
8. GitHub Release erstellen.
9. GitHub Actions prüfen.

## Wichtig

Ein Release sollte nur erstellt werden, wenn keine privaten Pfade, Secrets oder
lokalen Artefakte enthalten sind.
