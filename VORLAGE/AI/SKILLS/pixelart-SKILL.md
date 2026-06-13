---
name: premium-pixel-art
description: Use when creating, regenerating, reviewing, or briefing detailed pixel-art game assets such as icons, sprites, tiles, buildings, inventory items, map markers, transparent PNG cutouts, cohesive asset sets, or image-generation prompts for a premium indie-game look.
---

# Premium Pixel Art

## Ziel

Dieser Skill erzeugt oder beurteilt hochwertige Pixel-Art-Assets für Spiele:
sichtbare Pixel-Cluster, klare Silhouetten, liebevolle Materialdetails,
kontrolliertes Dithering, starke Lesbarkeit bei kleinen Größen und ein
einheitlicher Stil über ein ganzes Asset-Set.

Nutze ihn besonders, wenn der gewünschte Look eher **Stardew Valley, Eastward,
Owlboy, Sea of Stars** oder hochwertiger Indie-Pixel-Art entspricht und nicht
nach glatter Vektorgrafik, 3D-Render oder billigem Retro-Icon aussehen soll.

## Wann verwenden

- neue Pixel-Art Icons, Items, Rohstoffe, Gebäude, Charaktere oder Tiles
- bestehende Platzhalter durch hochwertige Pixelgrafiken ersetzen
- transparente PNG-Assets für Mediathek, Game-Editor oder App erzeugen
- mehrere Motive in einem konsistenten Stil erstellen
- Prompts für KI-Bildgenerierung schreiben oder schärfen
- Asset-Qualität prüfen: Pixelcluster, Alpha, Größe, Lesbarkeit, Stiltreue

Nicht verwenden für:

- reine SVG-/Vektor-Icons
- fotorealistische Produktbilder
- UI-Layouts ohne gemalte Spielgrafiken
- prozedurale Ersatzgrafiken, wenn ausdrücklich KI-/Artist-Grafik gefordert ist

## Grundprinzip

Pixel-Art wirkt hochwertig, wenn sie **bewusst reduziert und trotzdem reich**
ist:

- starke Silhouette vor Detailmenge
- 3-5 Tonstufen pro Material
- kleine, saubere Pixelcluster statt glatter Airbrush-Flächen
- kontrolliertes Dithering nur an Übergängen
- dunkle Outline, aber selten reines Schwarz
- Lichtführung klar von oben links
- Glow und Effekte sparsam, damit die Pixelstruktur lesbar bleibt
- Motiv zentriert, ungefähr 75-85 Prozent der Fläche
- bei 64 px muss das Hauptmotiv noch eindeutig sein

## Standard-Workflow

1. **Asset-Liste klären:** Name, Zielpfad, Zielgröße, Format, Limit, Motiv.
2. **Stilanker definieren:** Projektwelt, Palette, Licht, Materialien, No-Gos.
3. **Pro Motiv einzeln prompten:** Keine Sammelbilder, wenn einzelne Dateien gebraucht werden.
4. **KI-Bildgenerierung nutzen:** Für artistische Pixel-Art keine Pillow-/Canvas-Ersatzgrafiken bauen, wenn der Auftrag echte Bildgenerierung verlangt.
5. **Transparenz sauber lösen:** Native Transparenz nutzen, falls verfügbar; sonst Chroma-Key-Hintergrund erzeugen und lokal entfernen.
6. **Technisch ausgeben:** Auf Zielgröße skalieren, transparent speichern, Dateilimit prüfen.
7. **Visuell prüfen:** Kontaktbogen oder Einzelansicht auf Schachbrett; bei Ausreißern neu generieren.
8. **Übergabe dokumentieren:** Methode, Dateien, Prüfungen, offene Punkte.

## Prompt-Baukasten

Nutze für jedes Motiv einen konkreten Prompt nach diesem Muster:

```text
Create a high-quality detailed premium pixel-art game asset: <SUBJECT>.
Use case: <inventory icon / building / map marker / tile / character sprite>.
Style: modern premium pixel art like Stardew Valley / Eastward / Owlboy,
visible clean pixel clusters, rich 4-tone material shading, subtle dithering,
dark blue-anthracite 1-2 px outline, top-left light, no smooth vector look,
no photorealism, no 3D render.
Subject: centered <OBJECT DETAILS>, strong silhouette, fills about 80% of the
square, readable at 64 px.
World/material mood: <PROJECT WORLD, MATERIALS, PALETTE, GLOW>.
Background: perfectly flat solid <KEY COLOR> chroma-key only, no shadows,
no gradients, no floor, no texture.
Do not use <KEY COLOR> in the subject. No text, no watermark.
```

### Starke TINTLING-Variante

Für den epischen Unterwasser-Look:

```text
World/material mood: dark bioluminescent underwater fantasy world,
broken light from above-left, cyan and warm gold glow accents, deep navy
shadows, ink violet, shell, coral, pearl, wet stone, driftwood, algae traces,
tiny suspended motes only when appropriate.
```

Für Land-/Ufersaum-Rohstoffe in diesem Stil:

```text
Land/shore resource: same premium pixel-art palette and top-left light, but no
underwater floating particles. Add shore logic such as wet clay, driftwood,
warm broken stone, herbs from the waterline, shell flecks, or subtle cyan/gold
magic.
```

## Transparenz-Regel

Wenn native Transparenz nicht zuverlässig verfügbar ist:

- Verwende `#00ff00` als Chroma-Key.
- Für grüne Motive verwende `#ff00ff`.
- Der Hintergrund muss perfekt flach sein: keine Schatten, keine Textur, keine Lichtverläufe.
- Das Motiv darf die Key-Farbe nicht enthalten.
- Nach dem Entfernen: Ecken prüfen, Alpha-Kanal prüfen, sichtbare Halo-Ränder prüfen.

Beispiel für lokalen Chroma-Key-Helper:

```bash
python3 "${CODEX_HOME:-$HOME/.codex}/skills/.system/imagegen/scripts/remove_chroma_key.py" \
  --input source.png \
  --out final-alpha.png \
  --auto-key border \
  --soft-matte \
  --transparent-threshold 12 \
  --opaque-threshold 220 \
  --despill
```

Lokale Nachbearbeitung darf freistellen, skalieren, zuschneiden und validieren.
Sie darf nicht das Motiv prozedural neu zeichnen, wenn echte KI-/Artist-Grafik
gefordert ist.

## Zielgrößen

Typische Größen:

| Asset-Typ | Quelle/Ziel | Hinweis |
|---|---:|---|
| Inventar-Icon | 256x256 PNG | Muss bei 32-64 px lesbar bleiben |
| Rezept-Icon | 256x256 PNG | Darf Pergament/Blueprint-Element haben |
| Gebäude | 512x512 PNG | 3/4-Ansicht, klare Standfläche |
| Marker | 384x384 oder 512x512 PNG | Aura erlaubt, aber Alpha sauber |
| Tile | 512x512 PNG | Kachelbarkeit wichtiger als Einzelmotiv |

Für scharfe Pixelkanten beim Finalisieren bevorzugt Nearest-Neighbor-Skalierung.

## Qualitätscheck

Vor Abschluss prüfen:

- Datei liegt am exakten Zielpfad.
- Dateiname entspricht der technischen Vorgabe.
- PNG hat echten Alpha-Kanal.
- Ecken sind transparent.
- Keine grünen/magentafarbenen Chroma-Key-Ränder.
- Maße und Dateigrößenlimits stimmen.
- Motiv füllt etwa 75-85 Prozent der Fläche.
- Motiv ist bei 64 px noch erkennbar.
- Pixelcluster und Materialtexturen sind sichtbar.
- Stil ist über alle Motive konsistent.
- Kein Text, kein Wasserzeichen, kein generischer 3D-/Vektorlook.

## Häufige Fehler

- **Zu glatt:** Prompt stärker auf „visible pixel clusters, no smooth vector, no 3D“ setzen.
- **Zu flach:** Materialdetails, 3-5 Tonstufen und Dithering explizit verlangen.
- **Zu voll:** Nebenobjekte reduzieren; ein Hauptmotiv pro Icon.
- **Halo-Ränder:** Chroma-Key-Prompt verschärfen oder Key-Entfernung mit engeren Parametern wiederholen.
- **Unlesbar bei 64 px:** Silhouette vereinfachen, Objekt größer, weniger Kleinteile.
- **Stilbruch im Set:** gleiche Palette, Lichtquelle und Materialwörter in jedem Prompt wiederholen.

## Übergabeformat

Beim Abschluss knapp dokumentieren:

- verwendetes Bildmodell/Tool
- ob native Transparenz oder Chroma-Key verwendet wurde
- Liste der erzeugten Dateien
- technische Prüfung: Maße, Dateigröße, Alpha
- visuelle Prüfung: Lesbarkeit, Pixelcluster, Stiltreue
- offene Punkte oder Motive, die nochmal künstlerisch nachgezogen werden sollten
