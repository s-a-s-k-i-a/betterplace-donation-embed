=== Betterplace Donation Embed ===
Contributors:      saskialund
Tags:              betterplace, donation, spende, spendenformular, iframe, shortcode, gutenberg, block
Requires at least: 6.0
Tested up to:      6.6
Requires PHP:      7.4
Stable tag:        0.1.3
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Bindet das betterplace.org-Spendenformular sauber per Shortcode oder Gutenberg-Block ein — ohne den fragilen Upstream-JS-Loader.

== Description ==

Das Plugin rendert einen statischen `<iframe>` mit der gleichen URL, die der offizielle betterplace-Loader (`load_donation_iframe.js`) sonst dynamisch baut — verzichtet aber komplett auf das JS-Snippet. Damit verschwinden zwei bekannte Probleme:

1. **`SyntaxError: Identifier '$' has already been declared`** in der Browser-Console, ausgelöst dadurch, dass der Loader auf Top-Level eine bare `$`-Variable in den globalen Lexical-Scope schreibt.
2. **Spinner-Endlos-Popup**, wenn der Loader durch den Parse-Fehler abbricht, bevor er den Iframe erzeugt.

= Funktionen =

* Shortcode `[betterplace_donation project_id="4667"]`
* Gutenberg-Block „Betterplace-Spendenformular" mit Live-Vorschau im Editor
* Settings-Seite unter „Einstellungen → Spendenformular" für Site-weite Defaults
* Vollständige Attribut-Konfiguration: Projekt-ID, Farbe, Hintergrund, Standardbetrag, Intervall, Zahlungsart, Sprache, Iframe-Größe
* Lazy-Loading, sichere `referrerpolicy`, Fallback-Link für Adblocker/CSP-Probleme
* Sauberes Output-Escaping (WPCS-konform)

= Anforderungen =

* WordPress 6.0+
* PHP 7.4+

== Installation ==

1. Plugin-Ordner nach `wp-content/plugins/` kopieren oder per Git klonen.
2. Im WordPress-Backend unter „Plugins" aktivieren.
3. Standardwerte unter „Einstellungen → Spendenformular" pflegen (optional).
4. Shortcode oder Block in eine Seite/Post einfügen.

== Frequently Asked Questions ==

= Welche Projekt-ID brauche ich? =

Die numerische ID aus der betterplace-URL, z. B. `4667` aus `https://www.betterplace.org/de/projects/4667-tierheim-hannover`.

= Funktioniert das mit Spendenaktionen statt Projekten? =

Ja — `receiver_type="fundraising_event"` oder `receiver_type="organisation"` setzen und `receiver_id` mit der passenden ID belegen.

= Warum kein iframe-resizer? =

Der iframe-resizer ist genau der Code, der den `$`-Konflikt im Upstream-Loader verursacht. Stattdessen wird hier eine feste Höhe gesetzt (Default 800 px), die für Step 1 + Spenderdaten ausreicht.

== Changelog ==

= 0.1.3 =
* Bug-Fix: das Iframe wurde in `display: flex`-Containern (z. B. Divi-Pixel-Popups) auf die intrinsische Breite des Fallback-Links geschrumpft (~320 px) statt der konfigurierten Breite (z. B. 600 px). Der Wrapper nutzt jetzt `width: <px>` statt `max-width: <px>`, sodass die Breite die Container-Kette nach oben propagiert. `max-width: 100%` sorgt weiterhin für Responsiveness auf schmalen Viewports.

= 0.1.2 =
* Zero-Config-Auto-Update: das Plugin enthält ab sofort einen Shared-License-Key für die EDD-Software-Licensing-Update-API. Es ist kein manueller Lizenzschlüssel-Eintrag durch die Nutzer:innen mehr nötig — Updates werden automatisch im WP-Backend angezeigt und können wie bei jedem anderen Plugin direkt installiert werden.
* Override: Self-Hoster können `define( 'BPDE_EDD_LICENSE_KEY', '…' );` setzen oder den Filter `bpde_license_key` nutzen.

= 0.1.1 =
* Auto-Update-Client via Easy Digital Downloads Software Licensing: das Plugin fragt nun bei `https://isla-stud.io` (Item-ID 3610) nach neuen Versionen, sodass Updates im WP-Backend wie bei jedem anderen Plugin angezeigt und installiert werden können. Kein Lizenzschlüssel erforderlich (kostenloser Download).
* Opt-out: `define( 'BPDE_DISABLE_UPDATER', true );` in `wp-config.php` oder Filter `bpde_disable_updater`.

= 0.1.0 =
* Erstveröffentlichung: Shortcode, Block, Settings-Page, PHPUnit + Smoke + Playwright-E2E-Tests.
