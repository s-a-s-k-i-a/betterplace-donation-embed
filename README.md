# Betterplace Spendenformular für WordPress

**Spendenformular von betterplace.org direkt in deiner WordPress-Seite einbinden — in einer Minute, ohne technisches Wissen, ohne Konsolen-Fehler, ohne endlos drehende Lade-Spinner.**

Kostenlos. Mit automatischen Updates. Made für deutsche Vereine, Stiftungen und Initiativen.

---

## Für wen ist dieses Plugin gemacht?

Dieses Plugin ist für dich, wenn …

- … dein Verein oder deine Organisation Spenden über [betterplace.org](https://www.betterplace.org/) sammelt
- … deine Website auf WordPress läuft
- … du das Spendenformular **direkt auf deiner eigenen Seite** zeigen willst (in einem Popup, auf einer Spendenseite, in der Sidebar — wo immer du es brauchst), statt deine Besucher:innen zu betterplace weiterzuleiten
- … du das offizielle Code-Snippet von betterplace ausprobiert hast und festgestellt hast, dass es entweder gar nicht lädt, ewig den Spinner zeigt oder seltsame Fehler in der Browser-Konsole verursacht

Wenn du dich in einem oder mehreren dieser Punkte wiederfindest: willkommen, hier bist du richtig.

## Was bringt mir das?

- **Mehr Spenden.** Studien zeigen seit Jahren: jede zusätzliche Klick-Hürde zwischen "ich möchte spenden" und "Spende abgeschickt" senkt die Conversion. Wenn das Spendenformular direkt auf deiner Seite ist, statt einen Klick weg auf betterplace.org, bleiben mehr Spender:innen dabei.
- **Volle Kontrolle über den Auftritt.** Farbe, Standard-Betrag, einmalig/monatlich/jährlich, vorausgewählte Zahlungsart — alles über die Plugin-Einstellungen anpassbar, ohne dass du Code schreiben musst.
- **Keine technischen Stolpersteine.** Das Plugin umgeht einen bekannten Bug im offiziellen betterplace-JavaScript-Loader, der dazu führt, dass das Spendenformular in manchen Setups gar nicht erscheint oder die ganze Seite verlangsamt. Details dazu unten unter "Warum es dieses Plugin gibt".
- **Auto-Updates.** Wenn eine neue Plugin-Version verfügbar ist, siehst du das wie bei jedem anderen WordPress-Plugin im Backend und kannst es mit einem Klick installieren. Kein Lizenzschlüssel-Eintrag nötig.
- **Mobile-tauglich.** Auf Smartphones passt sich das Formular automatisch an die Bildschirmbreite an.

## Installation in 3 Schritten

1. **ZIP herunterladen** über [isla studio](https://isla-stud.io/downloads/betterplace-donation-formular-fuer-wordpress/) (kostenlos, einfache E-Mail-Eingabe).
2. **In WordPress hochladen:** Plugins → Installieren → Plugin hochladen → ZIP-Datei wählen → Jetzt installieren.
3. **Aktivieren** — fertig. Optional: unter **Einstellungen → Spendenformular** Standardwerte hinterlegen (z. B. deine Projekt-ID), damit du sie nicht jedes Mal eintragen musst.

> **Wo finde ich meine Projekt-ID?**
> Die Nummer am Anfang der URL deines betterplace-Projekts. Beispiel: bei `https://www.betterplace.org/de/projects/4667-tierheim-hannover` ist die Projekt-ID `4667`.

## So fügst du das Spendenformular auf einer Seite ein

### Variante A: Im Block-Editor (Gutenberg, der moderne WP-Editor)

1. Seite oder Beitrag bearbeiten
2. Plus-Symbol klicken (neuen Block einfügen), nach **„Betterplace-Spendenformular"** suchen
3. Block einfügen
4. In der rechten Seitenleiste deine Projekt-ID + gewünschte Einstellungen eintragen
5. Seite veröffentlichen oder aktualisieren — fertig

### Variante B: Mit dem Shortcode (für klassische Editoren oder spezielle Bereiche)

Diesen kurzen Code an die gewünschte Stelle einfügen — funktioniert in WordPress-Beiträgen, in Sidebar-Widgets, in vielen Page-Buildern (z. B. Divi, Elementor) und in Popups:

```
[betterplace_donation project_id="4667"]
```

Statt `4667` natürlich deine eigene Projekt-ID einsetzen. Wenn du mehr anpassen willst, kannst du weitere Optionen ergänzen:

```
[betterplace_donation project_id="4667" color="6c9c2e" default_amount="25" default_interval="monthly"]
```

(Eine vollständige Liste aller Optionen mit Erklärung findest du auf der Einstellungen-Seite des Plugins im WordPress-Backend.)

## Anpassungen über die Einstellungen

Unter **Einstellungen → Spendenformular** kannst du Standardwerte hinterlegen, die für *jeden* Shortcode und Block automatisch greifen, ohne dass du sie jedes Mal eintragen musst:

- Projekt-ID (für deine Hauptkampagne)
- Akzentfarbe (passend zu deinem Vereins-Design)
- Standard-Spendenbetrag
- Standard-Intervall (einmalig, monatlich, jährlich)
- Iframe-Breite und -Höhe

So musst du im Block oder Shortcode nur noch eintragen, was vom Default abweichen soll.

## Häufige Fragen

**Brauche ich einen Lizenzschlüssel?**
Nein. Das Plugin ist kostenlos. Du brauchst weder einen Account bei uns noch ein Lizenz-Feld auszufüllen. Einfach herunterladen, hochladen, aktivieren.

**Bekomme ich Updates automatisch?**
Ja. Sobald wir eine neue Version veröffentlichen, erscheint die Update-Benachrichtigung wie bei jedem anderen Plugin in deinem WordPress-Backend.

**Was passiert mit Daten meiner Spender:innen?**
Das Plugin sammelt **keine** Daten — es zeigt das offizielle Spendenformular von betterplace.org in einem Iframe. Die Spende, die Eingaben, die Zahlung — alles läuft direkt zwischen Spender:in und betterplace, deine Website ist nur der Anzeige-Rahmen. Damit gelten dieselben Datenschutz-Bedingungen, die du auch bei einem Link zu betterplace hättest.

**Was sieht meine Website an Daten?**
Nur den Klick auf das Spendenformular (wie bei jedem Element auf deiner Seite). Das Plugin selbst sendet einmal pro Monat einen Update-Check an `isla-stud.io` mit der URL deiner Website — vergleichbar mit dem, was WordPress selbst gegenüber wordpress.org tut. Mehr Details + Opt-out: siehe [Entwickler-Doku](docs/DEVELOPERS.md#auto-updates).

**Funktioniert das auch mit Spendenaktionen statt einzelnen Projekten?**
Ja. Zusätzliches Attribut `receiver_type="fundraising_event"` oder `receiver_type="organisation"` setzen + die passende ID. Bei einer Spendenaktion ist die ID aus deren betterplace-URL ablesbar.

**Mein Verein nutzt englischsprachige Spender:innen — geht das?**
Ja, `lang="en"` als Attribut setzen, dann lädt das Formular auf Englisch.

**Mein Spendenformular wird auf dem Smartphone nicht richtig angezeigt.**
Sollte mit Plugin-Version 0.1.5 oder neuer automatisch funktionieren. Falls nicht: prüfe, ob du wirklich auf 0.1.5+ aktualisiert hast.

**Kann ich mehrere Spendenformulare auf einer Seite einbinden (z. B. eines pro Projekt)?**
Ja. Jeder Block und jeder Shortcode kann eine andere Projekt-ID nutzen — das Plugin sorgt automatisch dafür, dass sich die Formulare nicht in die Quere kommen.

**Funktioniert das auch im Divi-Popup oder mit anderen Popup-Plugins?**
Ja. Speziell für die Kombination mit Divi Pixel Popups wurde das Plugin getestet und ein bekannter Layout-Bug behoben.

**Bug gefunden oder Wunsch?**
[GitHub-Issue öffnen](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/issues/new) oder eine Mail an `hello@isla-stud.io`. Wir schauen drauf.

## Voraussetzungen

- WordPress 6.0 oder neuer
- PHP 7.4 oder neuer

Beides ist seit Jahren WP-Standard — wenn deine Website regelmäßig WordPress-Updates bekommt, hast du das schon.

## Warum es dieses Plugin gibt

Das offizielle Code-Snippet, das du im betterplace-Portal („Spendenformular → Iframe-Code") angezeigt bekommst, lädt ein zentral gehostetes JavaScript-Snippet. Dieses Skript hat einen technischen Konflikt mit anderen Skripten auf typischen WordPress-Seiten, der unter bestimmten Bedingungen dazu führt, dass:

- in der Browser-Konsole der Fehler `Identifier '$' has already been declared` erscheint,
- das Spendenformular *gar nicht* eingeblendet wird und statt dessen nur ein Lade-Spinner endlos dreht,
- und die Ursache aus der Fehlermeldung allein nicht offensichtlich ist (der Fehler wird einer ganz anderen Datei zugeschrieben).

Dieses Plugin umgeht das, indem es das Formular auf die einfachstmögliche Art einbindet — als statischen Iframe mit derselben URL, die betterplaces eigener Loader im Hintergrund auch aufgebaut hätte. Kein zusätzliches Skript, kein Konflikt, kein Spinner-of-doom.

Wir haben den Bug auch dem betterplace-Team in einem Pull Request zur offiziellen Dokumentation gemeldet: [betterplace_apidocs#11](https://github.com/betterplace/betterplace_apidocs/pull/11). Wer Lust hat, kann den dortigen Vorschlag (IIFE-Wrap des betterplace-Loaders) unterstützen — dann verschwindet das Problem irgendwann auch ganz ohne dieses Plugin.

## Über das Plugin

Entwickelt von [isla studio](https://isla-stud.io) — wir bauen WordPress-Websites und -Plugins, schwerpunktmäßig für deutsche Vereine und Nonprofits. Das Plugin entstand aus konkreter Arbeit für den [Tierschutzverein Hannover](https://www.tierheim-hannover.de/).

Wenn du WordPress-Hilfe brauchst und keinen technischen Background hast: schreib uns einfach. Wir können auch direkt einbauen oder migrieren.

## Lizenz

GPL-2.0-or-later — du darfst das Plugin frei benutzen, anpassen und weitergeben, solange du Änderungen unter der gleichen Lizenz veröffentlichst. Siehe [LICENSE](LICENSE) für den Volltext.

Das Plugin ist eigenständig entwickelt und wird **nicht** von betterplace.org / gut.org gAG herausgegeben oder offiziell unterstützt.

---

### Für Entwickler:innen

Technische Details — Architektur, API, Tests, Self-Hosting, Release-Workflow, Auto-Update-Mechanik, vollständige Attribut-Referenz, Filter-Liste:
→ [docs/DEVELOPERS.md](docs/DEVELOPERS.md)

[![PHPUnit](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/phpunit.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/phpunit.yml)
[![Playwright E2E](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/playwright.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/playwright.yml)
[![Lint (WPCS)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/lint.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/lint.yml)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
