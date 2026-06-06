# Tauri App kompilieren (generisches Template)

## Voraussetzungen

- Node.js + npm installiert
- Rust + Cargo installiert
- Tauri CLI (`@tauri-apps/cli` als devDependency)

## Projektverzeichnis

Ersetze `<APP_NAME>` mit deinem Projektnamen:

```
<PROJEKT_ROOT>/PROJEKT/WORKSPACE/<APP_NAME>
```

## Build-Befehle

### Kompletter Build (App + DMG)

```bash
cd "<PROJEKT_PFAD>"
npm run tauri build
```

Das erzeugt:
- `.app` unter `src-tauri/target/release/bundle/macos/`
- `.dmg` unter `src-tauri/target/release/bundle/dmg/`

### Nur DMG neu bauen

```bash
npx tauri build --bundles dmg
```

### Nur App (ohne DMG)

```bash
npx tauri build --bundles app
```

## Export nach APP-Export

Nach dem Build die Dateien kopieren:

```bash
# .app kopieren
rm -rf "APP-Export/<APP_NAME>.app"
cp -R "src-tauri/target/release/bundle/macos/<APP_NAME>.app" "APP-Export/"

# .dmg kopieren (Versionsnummer anpassen!)
cp -f "src-tauri/target/release/bundle/dmg/<APP_NAME>_<VERSION>_aarch64.dmg" "APP-Export/"
```

## Variablen

| Variable | Beispiel | Beschreibung |
|----------|----------|-------------|
| `<PROJEKT_PFAD>` | `<PROJEKT_ROOT>/PROJEKT/WORKSPACE/Meine-App` | Vollständiger Pfad zum App-Verzeichnis |
| `<APP_NAME>` | `Meine App` oder `MyApp` | Name der App (muss mit `tauri.conf.json` übereinstimmen) |
| `<VERSION>` | `0.2.0` oder `1.0.0` | Versionsnummer aus `tauri.conf.json` und `package.json` |

## Hinweise

- Die Version wird in `src-tauri/tauri.conf.json` und `package.json` gepflegt
- Der DMG-Dateiname enthält Version und Architektur (`aarch64` = Apple Silicon)
- Falls der DMG-Build fehlschlägt: erneut ausführen -- oft ein transienter Fehler
- Der Build-Prozess führt automatisch `npm run build` (TypeScript + Vite) vor dem nativen Compile aus
- Stelle sicher, dass der `<APP_NAME>` exakt mit dem Namen in `tauri.conf.json` übereinstimmt
