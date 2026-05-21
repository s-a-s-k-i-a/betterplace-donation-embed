=== Betterplace Spendenformular für WordPress ===
Contributors:      saskialund
Tags:              betterplace, spende, spendenformular, donation, gemeinnützig, verein, nonprofit, gutenberg, shortcode
Requires at least: 6.0
Tested up to:      6.6
Requires PHP:      7.4
Stable tag:        0.1.5
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Spendenformular von betterplace.org direkt in deiner WordPress-Seite einbinden — kostenlos, ohne Konsolen-Fehler, ohne endlos drehende Lade-Spinner.

== Description ==

**Für wen ist dieses Plugin?**

Du sammelst über [betterplace.org](https://www.betterplace.org/) Spenden für deinen Verein, deine Stiftung oder dein Projekt — und betreibst eine WordPress-Website? Dann hilft dir dieses Plugin, das betterplace-Spendenformular direkt auf einer Seite, in einem Beitrag oder in einem Popup auf deiner Website zu zeigen.

So müssen deine Besucher:innen nicht erst zu betterplace klicken, um zu spenden — sie können direkt bei dir spenden. Erfahrungsgemäß steigert das die Spendenbereitschaft, weil weniger Klicks zwischen "ich will spenden" und "abgeschickt" liegen.

**Was das Plugin macht (und was es bewusst NICHT macht)**

Es bindet das offizielle Spendenformular von betterplace.org in einem Iframe ein. Sämtliche Spende, Bezahlung, Quittungs-Mail läuft direkt zwischen Spender:in und betterplace — genauso wie wenn du nur einen Link gesetzt hättest. Deine Website ist nur der Anzeige-Rahmen, sie speichert weder Spenden noch Zahlungsdaten.

**Was du gegenüber dem offiziellen betterplace-Snippet gewinnst**

* Keine Konsolen-Fehler mehr ("Identifier '$' has already been declared"), die in manchen WordPress-Setups durch den betterplace-Loader entstehen
* Keine endlos drehenden Lade-Spinner, weil der Loader-JS aufgrund desselben Bugs vorzeitig abbricht
* Volle Kontrolle über Farbe, Standard-Betrag, Intervall, Zahlungsart, Spracheinstellung — über die Plugin-Einstellungen, nicht über Code
* Automatische Anpassung der Breite auf Smartphones (mobile-tauglich out of the box)
* Auto-Updates direkt im WordPress-Backend, ohne dass du einen Lizenzschlüssel eintragen musst

**Funktionen**

* **Gutenberg-Block** „Betterplace-Spendenformular" mit Live-Vorschau im Editor
* **Shortcode** `[betterplace_donation project_id="…"]` für klassische Editoren, Sidebars, Popups und Page-Builder
* **Einstellungs-Seite** unter „Einstellungen → Spendenformular" für Site-weite Standardwerte
* Konfigurierbar: Projekt-ID, Akzentfarbe, Hintergrund, Standard-Betrag, Intervall (einmalig/monatlich/jährlich), vorausgewählte Zahlungsart, Sprache (Deutsch/Englisch), Iframe-Größe
* Funktioniert mit Spendenprojekten, Spendenaktionen und Organisations-Spenden
* Funktioniert mit gängigen Popup-Lösungen, auch in Kombination mit Divi Pixel
* Auto-Update ohne Lizenzschlüssel-Eingabe

**Voraussetzungen**

* WordPress 6.0 oder neuer
* PHP 7.4 oder neuer

(Beides ist bei aktuellen WordPress-Installationen Standard.)

**Wer steckt dahinter?**

[isla studio](https://isla-stud.io) — wir bauen WordPress-Websites und -Plugins, schwerpunktmäßig für deutsche Vereine und Nonprofits. Das Plugin entstand aus konkreter Arbeit für den Tierschutzverein Hannover.

== Installation ==

1. Plugin-ZIP von [isla-stud.io](https://isla-stud.io/downloads/betterplace-donation-formular-fuer-wordpress/) herunterladen
2. WordPress-Backend → Plugins → Installieren → Plugin hochladen → ZIP wählen → Jetzt installieren
3. Aktivieren
4. (Optional) Unter „Einstellungen → Spendenformular" deine Standard-Projekt-ID und Farben eintragen
5. Block oder Shortcode auf eine Seite einfügen — fertig

== Frequently Asked Questions ==

= Brauche ich einen Lizenzschlüssel? =

Nein. Das Plugin ist kostenlos und kommt mit eingebauten Auto-Updates. Einfach installieren und nutzen.

= Wo finde ich meine Projekt-ID? =

In der URL deines betterplace-Projekts. Beispiel: bei `https://www.betterplace.org/de/projects/4667-tierheim-hannover` ist die Projekt-ID `4667`.

= Funktioniert das auch mit Spendenaktionen oder Organisations-Spenden statt einzelnen Projekten? =

Ja. Im Block-Editor kannst du den Empfängertyp wählen (Projekt, Spendenaktion, Organisation). Beim Shortcode: `receiver_type="fundraising_event"` oder `receiver_type="organisation"` setzen und die passende ID hinterlegen.

= Was passiert mit Daten meiner Spender:innen? =

Das Plugin sammelt **keine** Daten — es zeigt das offizielle Spendenformular von betterplace.org in einem Iframe. Die Spende, die Eingaben, die Zahlung laufen direkt zwischen Spender:in und betterplace. Datenschutzrechtlich genauso wie wenn du einen Link zu betterplace setzt.

= Kann ich mehrere Spendenformulare auf einer Seite einbinden? =

Ja. Jeder Block und jeder Shortcode kann eine andere Projekt-ID und andere Einstellungen nutzen — sie kommen sich nicht in die Quere.

= Funktioniert das auf Smartphones? =

Ja. Ab Version 0.1.5 passt sich die Breite des Iframes automatisch an die Bildschirmgröße an.

= Funktioniert das mit Divi-Popups, Elementor-Popups oder Popup Maker? =

Ja. Speziell für Divi Pixel Popups wurde das Plugin getestet und ein bekannter Layout-Bug behoben.

= Wer hilft mir, wenn etwas nicht funktioniert? =

[GitHub-Issue öffnen](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/issues/new) oder eine Mail an `hello@isla-stud.io`.

= Ich bin Entwickler:in und brauche tiefere Doku =

Komplette technische Doku (Architektur, API, Filter, Konstanten, Tests, CI) liegt in [docs/DEVELOPERS.md](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/blob/main/docs/DEVELOPERS.md) im Quellcode-Repo.

== Screenshots ==

1. Das Spendenformular auf einer WordPress-Seite (Desktop-Ansicht).
2. Dasselbe Formular auf einem Smartphone — automatisch angepasst.
3. Gutenberg-Block-Einstellungen im Editor (Projekt-ID, Farben, Betrag, Intervall, …).
4. Einstellungs-Seite unter „Einstellungen → Spendenformular" für Site-weite Defaults.

== Changelog ==

= 0.1.5 =
* Smartphone-Anpassung greift jetzt zuverlässig. Auf schmalen Bildschirmen füllt das Spendenformular automatisch die verfügbare Breite, statt mit fester Pixelgröße über den Rand hinauszulaufen.

= 0.1.4 =
* Auto-Update-Fix: Updates werden jetzt zuverlässig im WordPress-Backend angezeigt und installiert, ohne dass du einen Lizenzschlüssel eintragen musst.
* Smartphone-Anpassung eingeführt (siehe auch 0.1.5).

= 0.1.3 =
* Layout-Fix für Divi-Pixel-Popups: das Formular wird nun in voller konfigurierter Breite gezeigt, statt auf ~320 px geschrumpft zu werden.

= 0.1.2 =
* Auto-Updates ohne Lizenzschlüssel-Eingabe: einmal installieren, danach kommen Updates direkt im WordPress-Backend.

= 0.1.1 =
* Update-Mechanismus über isla-stud.io eingebaut.

= 0.1.0 =
* Erstveröffentlichung: Shortcode, Gutenberg-Block, Einstellungs-Seite, Test-Setup.

== Upgrade Notice ==

= 0.1.5 =
Smartphone-Anpassung greift jetzt zuverlässig. Empfohlen für alle, die das Formular in Popups oder schmalen Spalten einbinden.

= 0.1.4 =
Auto-Updates funktionieren jetzt direkt aus dem WordPress-Backend. Empfohlenes Update für alle Installationen.
