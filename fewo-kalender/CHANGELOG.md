# Changelog

## 1.1.2 - 2026-04-15
- Live-Highlight fuer Design-Vorschau im Admin umgesetzt.
- Beim Wechsel im Design-Select wird die passende Vorschauchip automatisch hervorgehoben.

## 1.1.1 - 2026-04-15
- Design-Vorschauchips im Admin beim Kalender-Anlegen und -Bearbeiten hinzugefuegt.
- Versions- und Readme-Informationen aktualisiert.

## 1.1.0 - 2026-04-15
- Frontend-Design modernisiert (abgerundet, klarere Typografie, lebendigere Oberflaeche).
- Drei waehlbare Kalender-Designs hinzugefuegt: Modern, Ocean, Terracotta.
- Design ist pro Kalender im Backend speicherbar.
- Optionaler Shortcode-Override fuer Design: [fewo_kalender id="X" design="ocean"].
- Upgrade-Logik fuer bestehende Installationen ergaenzt (DB-Feld `design`).

## 1.0.0 - 2026-04-15
- Initiale Version.
- Mehrere Kalender im Admin anlegbar, bearbeitbar und loeschbar.
- Monatsansicht im Admin mit klickbaren Tagesstatus (frei, belegt, Wechseltag).
- Speicherung nur fuer Tage, die von frei abweichen.
- Shortcode [fewo_kalender id="X"] fuer Frontend-Ausgabe.
- Responsive Frontend-Kalender mit Legende und Monatsnavigation.
- Aktivierungslogik fuer eigene Datenbanktabellen und uninstall cleanup.
