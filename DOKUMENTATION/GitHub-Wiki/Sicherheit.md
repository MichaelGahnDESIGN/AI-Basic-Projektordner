# Sicherheit

Sicherheit und Datenschutz sind feste Bestandteile der Vorlage.

## Nie Versionieren

- API-Schlüssel
- Tokens
- Passwörter
- Sessiondaten
- Zahlungsdaten
- personenbezogene Daten
- Kundendaten
- private Rechnungs- oder Vertragsdaten
- echte `.env`-Dateien

## Sicherheitsdatei

Die öffentliche Sicherheitsrichtlinie steht in:

```text
SECURITY.md
```

## Für KI-Agenten

KI-Agenten sollen sensible Werte nicht vollständig zitieren. Stattdessen:

- Fundort nennen
- Risiko einordnen
- Maßnahme empfehlen
- sensible Inhalte nicht wiederholen

## Besonders Kritische Bereiche

- Authentifizierung
- Zahlung
- Uploads
- Logs
- Admin-Funktionen
- Drittanbieter-APIs
- KI-Dienste mit externen Providern
