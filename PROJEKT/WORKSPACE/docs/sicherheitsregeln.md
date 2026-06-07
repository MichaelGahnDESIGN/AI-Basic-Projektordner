# Sicherheitsregeln

## Grundsatz

Das MCP darf keine sensiblen Daten speichern, loggen oder an andere Systeme
weitergeben. Es arbeitet lokal und schreibt nur in Dateien im gewählten
Projektordner.

## Blockierte Muster

Der Safety-Check blockiert unter anderem:

- echte `.env`-Hinweise
- API-Key-, Secret- oder Access-Token-Hinweise
- private Schlüssel
- Passwort-Zuweisungen
- Token-Zuweisungen
- Datenbank-URLs
- SSH-Schlüsselmaterial
- Dump- und Backup-Dateien mit Datenbankbezug

## Was Agenten nicht eintragen sollen

Agenten sollen keine Inhalte schreiben wie:

- Passwörter
- Tokens
- private Schlüssel
- personenbezogene Detaildaten
- Zahlungsdaten
- echte Kundendaten
- Sessiondaten
- Datenbank-Zugangsdaten
- private Rechnungs- oder Vertragsdaten

## Grenzen

Die Prüfung ist ein lokaler Schutz gegen offensichtliche Fehler. Sie ist kein
vollständiger Ersatz für Datenschutzprüfung, Code-Review, Secret-Scanning oder
Rechtsberatung.

## Empfohlene Arbeitsweise

- Aufgaben knapp und ohne sensible Details formulieren.
- Pfade zu echten Secret-Dateien vermeiden.
- Testergebnisse ohne private Logauszüge dokumentieren.
- Bei Unsicherheit lieber einen Blocker anlegen statt Details zu speichern.
