# Security Policy

Diese Vorlage legt besonderen Wert auf Datenschutz, Sicherheit und saubere
Trennung zwischen öffentlichen Projektdateien und lokalen Geheimnissen.

## Grundregeln

- Keine API-Schlüssel, Tokens, Passwörter oder privaten Zugangsdaten ins
  Repository schreiben.
- Keine personenbezogenen Daten, Kundendaten, Rechnungsdaten oder Zahlungsdaten
  in Beispielen, Logs, Prompts oder Dokumentation ablegen.
- Echte `.env`-Dateien bleiben lokal und dürfen nicht versioniert werden.
- Öffentliche Beispieldateien dürfen nur Platzhalter enthalten.
- Sicherheitsrelevante Änderungen müssen nachvollziehbar dokumentiert werden.

## Sicherheitsrelevante Bereiche

Besonders vorsichtig arbeiten bei:

- Authentifizierung und Session-Verwaltung
- Zahlungsfunktionen und Webhooks
- Uploads und Dateiverarbeitung
- Admin-Funktionen
- Logs, Bugreports und Monitoring
- Drittanbieter-APIs und KI-Diensten

## Meldung Von Sicherheitsproblemen

Bitte melde Sicherheitsprobleme nicht in öffentlichen Issues, wenn dabei
sensible Details sichtbar würden.

Kontakt:

- E-Mail: Anfrage@Michael-Gahn.de

Bitte beschreibe:

- betroffener Bereich
- nachvollziehbare Schritte
- mögliches Risiko
- ob sensible Daten betroffen sein könnten

## Für KI-Agenten

KI-Agenten sollen Sicherheitsfunde nicht vollständig mit sensiblen Inhalten
zitieren. Stattdessen nur Fundort, Risikoklasse und empfohlene Maßnahme
zusammenfassen.
