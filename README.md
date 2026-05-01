=== Fewo Kalender ===
Contributors: Christian Hilgenberg
Tags: calendar, booking, vacation rental
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ein einfaches WordPress-Plugin fuer mehrere Ferienwohnungs-Belegungskalender.

== Description ==
- Beliebig viele Kalender erstellen
- Tagesstatus: frei, belegt, Wechseltag, Halber Tag (rot/gruen), Halber Tag (gruen/rot)
- Backend Monatsansicht mit Klick-Bedienung
- Frontend Anzeige per Shortcode
- Optionales Buchungsanfrage-Formular mit Datumsauswahl (Von/Bis) und Mailversand
- Design pro Kalender waehlbar (Modern, Ocean, Terracotta)
- Responsive Layout, keine Buchungsfunktion

== Installation ==
1. Plugin als ZIP hochladen oder in wp-content/plugins entpacken.
2. Plugin in WordPress aktivieren.
3. Unter "Fewo Kalender" Kalender anlegen.
4. Shortcode verwenden: [fewo_kalender id="1"]
5. Optionales Design im Shortcode: [fewo_kalender id="1" design="ocean"]

== Changelog ==
= 1.2.0 =
* Optionales Buchungsanfrage-Formular im Frontend hinzugefuegt.
* Datumsbereich (Von/Bis) wird per Klick im Kalender markiert und mitgesendet.
* Versand der Anfrage per E-Mail an pro Kalender hinterlegte Empfaenger-Adresse.
* Formular kann in "Kalender bearbeiten" pro Kalender ein- und ausgeschaltet werden.

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
