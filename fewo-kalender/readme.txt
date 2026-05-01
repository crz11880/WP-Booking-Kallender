=== Fewo Kalender ===
Contributors: your-name
Tags: calendar, booking, vacation rental
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.2.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ein einfaches WordPress-Plugin fuer mehrere Ferienwohnungs-Belegungskalender.

== Description ==
- Beliebig viele Kalender erstellen
- Tagesstatus: frei, belegt, Wechseltag, Halber Tag (belegt/frei), Halber Tag (frei/belegt)
- Backend Monatsansicht mit Klick-Bedienung
- Frontend Anzeige per Shortcode
- Optionales Buchungsanfrage-Formular unter dem Kalender (Name, Vorname, E-Mail, Von/Bis)
- Design pro Kalender waehlbar (Modern, Ocean, Terracotta)
- Responsive Layout, keine Buchungsfunktion

== Installation ==
1. Plugin als ZIP hochladen oder in wp-content/plugins entpacken.
2. Plugin in WordPress aktivieren.
3. Unter "Fewo Kalender" Kalender anlegen.
4. Shortcode verwenden: [fewo_kalender id="1"]
5. Optionales Design im Shortcode: [fewo_kalender id="1" design="ocean"]

== Support ==
Wenn dir dieses Plugin hilft, kannst du mich hier unterstuetzen:
https://buymeacoffee.com/worklessit

== Changelog ==
= 1.2.5 =
* Spam-Schutz fuer Buchungsformular: Honeypot + Zeitcheck (mind. 3 Sekunden).
* SMTP-Konfiguration im Backend: Host, Port, Verschluesselung, Benutzername, Passwort, Absender.
* Test-E-Mail Funktion unter E-Mail Einstellungen.
* Buchungsformular: AJAX-Versand ohne Seitenwechsel, Feldfehler werden inline angezeigt.
* Datumsauswahl bleibt bei Monatswechsel erhalten.
* Monatsuebergreifende Buchungen moeglich (z.B. 31.1 bis 10.2).
* Halber-Tag-Labels: rot/gruen ersetzt durch belegt/frei.
* Pflichtfeld-Markierung mit Sternchen im Buchungsformular.

= 1.2.0 =
* Optionales Buchungsanfrage-Formular im Frontend hinzugefuegt.
* Datumsbereich (Von/Bis) wird per Klick im Kalender markiert und mitgesendet.
* Versand der Anfrage per E-Mail an pro Kalender hinterlegte Empfaenger-Adresse.
* Formular kann in "Kalender bearbeiten" pro Kalender ein- und ausgeschaltet werden.

= 1.1.8 =
* Zusaetzlicher Tagesstatus "Halber Tag (gruen/rot)" hinzugefuegt (invertierte Diagonale).

= 1.1.7 =
* Neuer Tagesstatus "Halber Tag" hinzugefuegt (diagonal geteilt: oben belegt, unten frei).

= 1.1.6 =
* Firmen-Werbeblock im Backend visuell aufgewertet (Logo-Badge, klarerer CTA, modernes Layout).

= 1.1.5 =
* Firmen-Werbeflaeche im Backend hinzugefuegt (Work Less IT): https://work-less.it/

= 1.1.4 =
* Support-Link jetzt sichtbar direkt im Plugin-Backend (Fewo Kalender + Bearbeiten).
* Der Admin-Menuepunkt fewo-kalender-edit ist nicht mehr sichtbar und nur noch intern aufrufbar.

= 1.1.3 =
* README/Support-Bereich erweitert.
* Buy Me a Coffee Link hinzugefuegt: https://buymeacoffee.com/worklessit

= 1.1.2 =
* Live-Highlight der Design-Vorschau im Admin beim Wechsel der Design-Auswahl.

= 1.1.1 =
* Design-Vorschau im Admin beim Anlegen und Bearbeiten eines Kalenders hinzugefuegt.

= 1.1.0 =
* Moderneres Frontend-Design.
* Design-Auswahl pro Kalender im Admin.
* Drei Designvarianten: Modern, Ocean, Terracotta.
* Upgrade-Logik fuer bestehende Installationen (neues Feld "design").

= 1.0.0 =
* Erste stabile Version.
