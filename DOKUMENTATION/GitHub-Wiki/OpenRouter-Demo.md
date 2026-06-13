# OpenRouter-Demo

Die OpenRouter-Demo liegt in:

```text
DEMOS/OPENROUTER/
```

Sie ist bewusst vom eigentlichen Projektbereich getrennt.

## Zweck

- API-Aufrufe testen
- Modellrouting ausprobieren
- Kosten- und Datenschutzfragen früh sichtbar machen

## Wichtig

- echte API-Schlüssel nur lokal in `.env`
- `.env` nicht versionieren
- keine sensiblen Daten in Testprompts verwenden
- vor produktiver Nutzung Preise, Limits und Anbieterbedingungen prüfen

## Prüfung

```bash
npm --prefix DEMOS/OPENROUTER run check
```
