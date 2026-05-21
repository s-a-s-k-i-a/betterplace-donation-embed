# Betterplace Donation Embed

A small, focused WordPress plugin that embeds the [betterplace.org](https://www.betterplace.org/) donation form via a **shortcode** and a **Gutenberg block** — without the upstream JavaScript loader.

> **TL;DR:** The official `load_donation_iframe.js` snippet that betterplace's portal hands out is a classic-script bundle that declares ~150 single-letter `const` identifiers at top level — including a bare **`$`**. Those identifiers leak into the page's global lexical environment and clash with any other script on the page that also declares `let/const/class $` at top level. The result: a `SyntaxError: Identifier '$' has already been declared` in the console and (often) a donation popup that's stuck on the loading spinner.
>
> This plugin renders a plain, static `<iframe>` with the same URL the loader would have built — no JS, no global pollution, no race condition, no spinner-of-doom.

[![PHPUnit](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/phpunit.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/phpunit.yml)
[![Playwright E2E](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/playwright.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/playwright.yml)
[![Lint (WPCS)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/lint.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/lint.yml)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Why this exists

When you generate an iframe-donation-form snippet in the betterplace portal (Project → Manage → Iframe donation form), you get a block like:

```html
<script type="text/javascript">
  var _bp_iframe = _bp_iframe || {};
  _bp_iframe.project_id = 4667;
  // ... more config ...
  (function() {
    var bp = document.createElement('script');
    bp.src = 'https://betterplace-assets.betterplace.org/assets/load_donation_iframe.js';
    // ...
  })();
</script>
<div id="betterplace_donation_iframe">…spinner…</div>
```

The remote `load_donation_iframe.js` is a bundled (non-module) JS file. **Inside that bundle** is a copy of [`iframe-resizer` 5.5.9](https://github.com/davidjbradshaw/iframe-resizer) compiled as a flat ES sequence of top-level `const` declarations. One of them, in line 130 of the loader, looks like:

```js
const o = "5.5.9", r = "iframeResizer", a = ":", s = "autoResize", l = "init",
      c = "iframeReady", u = "load", d = "message", f = "onload", p = "pageInfo",
      h = "parentInfo", m = "reset", g = "resize", y = "scroll", b = "\n",
      v = "child", w = "parent", z = "string",
      $ = "object",  // <-- bare $ declared at top level
      j = "function", k = "auto", T = "none", x = "both", /* ... */;
```

Because the script is loaded as `type="text/javascript"` (a classic script), every top-level `let`, `const`, and `class` declaration lands in the page's [global lexical environment](https://tc39.es/ecma262/#sec-global-environment-records), which is **shared across all classic scripts** in the realm. So this loader effectively reserves the identifiers `$`, `j`, `k`, `o`, `r`, … globally.

If any other script on the same page also declares e.g. `let $ = …` at top level (a browser extension's content script, a second iframe-resizer instance, a build artifact from another plugin), parsing of the second script throws:

```
Uncaught SyntaxError: Identifier '$' has already been declared
```

And because that error is a *parse-time* failure, the failing script never runs. In some configurations the betterplace loader itself never reaches its last line, so the iframe never gets created — the popup spins forever.

This plugin sidesteps the whole problem by **never loading the upstream JS**. It just builds the same iframe URL that `getIframeSource()` in the loader would have built, and renders a plain `<iframe>` element. No global pollution, no spinner-of-doom, no race conditions.

## Installation

### From source

```bash
cd wp-content/plugins
git clone https://github.com/s-a-s-k-i-a/betterplace-donation-embed.git
```

Then activate **Betterplace Donation Embed** in **Plugins → Installed Plugins**.

### Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer

## Usage

### Shortcode

```text
[betterplace_donation project_id="4667"]
```

All attributes:

| Attribute | Default | Notes |
|---|---|---|
| `project_id` | (required) | The numeric betterplace project ID (e.g. `4667` for "Tierheim Hannover"). |
| `receiver_id` | = `project_id` | Override if `receiver_type` is not `project`. |
| `receiver_type` | `project` | `project`, `fundraising_event`, or `organisation`. |
| `lang` | `de` | `de` or `en`. |
| `color` | `6c9c2e` | Accent color, 6-digit hex without `#`. |
| `background_color` | `ffffff` | 6-digit hex without `#`. |
| `default_amount` | `10` | Pre-filled amount, integer 1–99. |
| `default_interval` | `single` | `single`, `monthly`, or `yearly`. |
| `default_payment_method` | (none) | `paypal`, `stripe`, `stripe_sepa_debit`, `apple_pay`, `google_pay`, or `eft` (mapped to `stripe_sepa_debit` to match upstream). |
| `bottom_logo` | `true` | `true` / `false`. |
| `width` | `600` | Iframe max-width in px (200–1200). |
| `height` | `800` | Iframe height in px (400–2000). |

### Gutenberg block

In the editor, search for **"Betterplace-Spendenformular"** in the block inserter. All attributes above are available in the right-hand sidebar.

### Settings page

Under **Settings → Spendenformular** you can configure site-wide defaults (project ID, color, default amount, etc.) that are used whenever a shortcode or block instance doesn't override them.

## Testing

The plugin ships with three test layers, all wired up via GitHub Actions:

| Layer | Runner | What it covers |
|---|---|---|
| **PHPUnit** | `composer test` | Renderer URL builder (sanitization, defaults, edge cases) + shortcode integration with WP's `do_shortcode()` and `the_content` filter. |
| **Smoke** | `bash tests/smoke/smoke.sh` | Spins up `wp-env`, activates the plugin, creates a post with the shortcode, fetches it via HTTP, and asserts the rendered HTML contains the expected iframe and **does not** reference `load_donation_iframe.js`. |
| **Playwright E2E** | `npm test` (in `tests/e2e/`) | Frontend rendering (iframe present, attributes set, fallback link, no upstream JS) + editor flow (block can be inserted from the inserter and shows a live preview). |

CI matrix: PHP 7.4 – 8.3 × WP 6.0 / 6.5 / latest.

### Run locally

```bash
# PHPUnit (requires MySQL/MariaDB)
composer install
bash bin/install-wp-tests.sh wordpress_test root '' 127.0.0.1 latest
composer test

# Smoke + E2E (requires Docker)
npm install
npm run test:smoke
npm run test:e2e:install
npm run test:e2e
```

## Upstream

The upstream loader behavior is documented in a docs-only PR at [betterplace/betterplace_apidocs#11](https://github.com/betterplace/betterplace_apidocs/pull/11). The recommended upstream fix is to wrap the loader bundle in an IIFE (`(function(){…})();`) before serving it, which moves all declarations into a function scope and avoids global lexical pollution entirely. Serving the loader as `<script type="module">` would also work.

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).

This plugin is independent of betterplace.org and is not endorsed by or affiliated with gut.org gAG.
