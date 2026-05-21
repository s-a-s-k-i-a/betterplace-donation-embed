# Developer / Technical Documentation

This is the deep-dive for developers, sysadmins and contributors. If you just want to **use** the plugin, read the main [README.md](../README.md) instead.

---

## Why this plugin exists (technical background)

When the betterplace portal generates an iframe-donation-form snippet (Project → Manage → Iframe donation form), you get:

```html
<script type="text/javascript">
  var _bp_iframe = _bp_iframe || {};
  _bp_iframe.project_id = 4667;
  // ... config ...
  (function() {
    var bp = document.createElement('script');
    bp.src = 'https://betterplace-assets.betterplace.org/assets/load_donation_iframe.js';
    // ...
  })();
</script>
<div id="betterplace_donation_iframe">…spinner…</div>
```

The remote `load_donation_iframe.js` is a bundled (non-module) JS file. **Inside that bundle** is a copy of [`iframe-resizer` 5.5.9](https://github.com/davidjbradshaw/iframe-resizer) compiled as a flat ES sequence of top-level `const` declarations. Line 130 of the loader contains:

```js
const o = "5.5.9", r = "iframeResizer", a = ":", s = "autoResize", l = "init",
      c = "iframeReady", u = "load", d = "message", f = "onload", p = "pageInfo",
      h = "parentInfo", m = "reset", g = "resize", y = "scroll", b = "\n",
      v = "child", w = "parent", z = "string",
      $ = "object",  // <-- bare $ declared at top level
      j = "function", k = "auto", T = "none", x = "both", /* ... */;
```

Because the script is loaded as `type="text/javascript"` (a classic script), every top-level `let`, `const`, and `class` declaration lands in the page's [global lexical environment](https://tc39.es/ecma262/#sec-global-environment-records) — **shared across all classic scripts in the realm**. So this loader effectively reserves `$`, `j`, `k`, `o`, `r`, `s`, `c`, `u`, `d`, `f`, `p`, `h` and ~140 other one-character names globally.

If any other classic script on the page also declares e.g. `let $ = …` at top level (a browser extension's content script, a second iframe-resizer instance, another plugin's build artifact), parsing of the second script throws:

```
Uncaught SyntaxError: Identifier '$' has already been declared
```

And because that error is a *parse-time* failure, the failing script never runs. In some configurations the betterplace loader itself never reaches its last line, so the iframe never gets created — the popup spins forever.

This plugin sidesteps the whole problem by **never loading the upstream JS**. It just builds the same iframe URL that `getIframeSource()` in the loader would have built, and renders a plain `<iframe>` element.

### Upstream documentation PR

Filed at [betterplace/betterplace_apidocs#11](https://github.com/betterplace/betterplace_apidocs/pull/11). Documents the global-lexical-pollution mechanism + suggests an IIFE wrap as the upstream fix.

---

## Architecture

```
betterplace-donation-embed/
├── betterplace-donation-embed.php   # Bootstrap (WP header, constants, hook registration)
├── includes/
│   ├── class-plugin.php             # Singleton orchestrator
│   ├── class-renderer.php           # URL builder + iframe HTML output (the core)
│   ├── class-shortcode.php          # [betterplace_donation] shortcode
│   ├── class-block.php              # Gutenberg block server-side registration
│   ├── class-admin.php              # Settings page under Settings → Spendenformular
│   └── class-edd-sl-plugin-updater.php  # Vendored EDD-SL update client (v1.9.1)
├── blocks/donation/
│   ├── block.json                   # Block manifest (apiVersion 3)
│   ├── edit.js                      # Editor preview + InspectorControls (no build step)
│   └── style.css                    # Frontend + editor styles
├── assets/css/admin.css             # Settings page styling
├── languages/                       # .pot (to be generated)
├── readme.txt                       # WordPress-style readme (for "View details" modal)
└── README.md                        # User-facing GitHub readme
```

**Key design choices:**

- **Build-step-free Gutenberg block.** `blocks/donation/edit.js` uses `window.wp.*` globals directly (`wp.element.createElement`, `wp.blockEditor.InspectorControls`, etc.) instead of JSX + `@wordpress/scripts`. Easier to review, no `node_modules/` baggage in source, no CI build step needed for releases.
- **Shared Renderer between shortcode and block.** Both call `Betterplace_Donation_Embed_Renderer::render( $atts )`. The Renderer owns sanitization, default-merging, URL building, and HTML output. Adding new entry points (REST endpoint, widget) means one more thin wrapper around the same Renderer.
- **Per-instance scoped media query.** Each render emits its own `<style>` block with a per-instance class (`bpde-embed--i1`, `--i2`, …) so the smartphone-fallback breakpoint matches the per-instance configured width. Without per-instance scoping, two blocks with different configured widths would conflict.

---

## URL shape

The Renderer mirrors `getIframeSource()` from the upstream loader exactly:

```
https://www.betterplace.org/<lang>/donate/iframe/<receiver_type>s/<receiver_id>
  ?background_color=<hex>
  &color=<hex>
  &donation_amount=<1–99>
  &bottom_logo=<true|false>
  &default_payment_method=<""|paypal|stripe|stripe_sepa_debit|apple_pay|google_pay>
  &default_interval=<single|monthly|yearly>
```

`default_payment_method=eft` is remapped to `stripe_sepa_debit` to match upstream's `getIframeSource()` behaviour.

---

## Shortcode and block attributes

All attributes accepted by `[betterplace_donation]` and the Gutenberg block:

| Attribute | Default | Notes |
|---|---|---|
| `project_id` | (required) | Numeric betterplace project ID (e.g. `4667` for "Tierheim Hannover"). |
| `receiver_id` | = `project_id` | Override if `receiver_type` is not `project`. |
| `receiver_type` | `project` | `project`, `fundraising_event`, or `organisation`. |
| `lang` | `de` | `de` or `en`. |
| `color` | `6c9c2e` | Accent color, 6-digit hex without `#`. |
| `background_color` | `ffffff` | 6-digit hex without `#`. |
| `default_amount` | `10` | Pre-filled amount, integer 1–99. |
| `default_interval` | `single` | `single`, `monthly`, or `yearly`. |
| `default_payment_method` | (none) | `paypal`, `stripe`, `stripe_sepa_debit`, `apple_pay`, `google_pay`, or `eft` (remapped). |
| `bottom_logo` | `true` | `true` / `false`. |
| `width` | `600` | Iframe max-width in px (200–1200). |
| `height` | `800` | Iframe height in px (400–2000). |

Sanitization: hex validated against `/^[0-9a-fA-F]{6}$/`, dimensions clamped, enums whitelisted. All output is escaped via `esc_url()`, `esc_attr()`, `esc_html()`.

---

## Settings page

`Settings → Spendenformular` (capability: `manage_options`) holds site-wide defaults. Stored in option `bpde_settings`. Per-instance attributes (block/shortcode) always override site-wide defaults; site-wide defaults override the hardcoded fallbacks in `Renderer`.

Resolution order:

```
hardcoded defaults  <  bpde_settings (admin)  <  shortcode/block attributes
```

---

## Auto-updates

The plugin ships with the standard EDD Software Licensing client (`EDD_SL_Plugin_Updater`, vendored from [`awesomemotive/easy-digital-downloads`](https://github.com/awesomemotive/easy-digital-downloads) v1.9.1, GPL-2+) wired against `https://isla-stud.io` (item_id `3610`).

To remove friction for free users, a **single shared license key** is baked into the plugin source (`BPDE_EDD_LICENSE_KEY`). The store is configured with unlimited activations and never-expires, so the key only ever grants access to a free download we're already giving away.

On first `admin_init`, the plugin calls `edd_action=activate_license` against the store with `home_url()` as the site URL. EDD Software Licensing requires this activation step before the `package_download` endpoint will serve the update ZIP (HTTP 401 otherwise). The activation result is cached in option `bpde_license_activation`; re-runs only on URL change or after 30 days.

### Opt-out

```php
// in wp-config.php
define( 'BPDE_DISABLE_UPDATER', true );
// or via filter
add_filter( 'bpde_disable_updater', '__return_true' );
```

### Self-hosted override

Point at a different EDD store / use your own license:

```php
define( 'BPDE_EDD_LICENSE_KEY', 'your-own-key' );
// or
add_filter( 'bpde_license_key', fn() => 'your-own-key' );
```

(`BPDE_EDD_STORE_URL` and `BPDE_EDD_ITEM_ID` are also constants but currently not made filterable. Patch welcome.)

---

## Constants and filters

| Constant | Default | Purpose |
|---|---|---|
| `BPDE_VERSION` | (current release) | Plugin version. |
| `BPDE_PLUGIN_FILE` | `__FILE__` | Main plugin file path. |
| `BPDE_PLUGIN_DIR` | `plugin_dir_path( __FILE__ )` | Plugin directory path. |
| `BPDE_PLUGIN_URL` | `plugin_dir_url( __FILE__ )` | Plugin directory URL. |
| `BPDE_BLOCK_DIR` | `BPDE_PLUGIN_DIR . 'blocks/donation'` | Path to the block directory. |
| `BPDE_EDD_STORE_URL` | `https://isla-stud.io` | EDD store endpoint for updates. |
| `BPDE_EDD_ITEM_ID` | `3610` | EDD product ID. |
| `BPDE_EDD_LICENSE_KEY` | (hardcoded) | Shared license key (overridable). |
| `BPDE_DISABLE_UPDATER` | unset | Define as `true` to disable the EDD updater. |

| Filter | Default | Purpose |
|---|---|---|
| `bpde_disable_updater` | `false` | Return `true` to disable the EDD updater. |
| `bpde_license_key` | `BPDE_EDD_LICENSE_KEY` | Override the license key used for update requests. |

---

## Testing

Three test layers, all wired up via GitHub Actions:

| Layer | Runner | What it covers |
|---|---|---|
| **PHPUnit** | `composer test` | Renderer URL builder (sanitization, defaults, edge cases) + shortcode integration with WP's `do_shortcode()` and `the_content` filter. 14-combo matrix: PHP 7.4 – 8.3 × WP 6.0 / 6.5 / latest. |
| **Smoke** | `bash tests/smoke/smoke.sh` | Spins up `wp-env`, activates the plugin, creates a post with the shortcode, fetches it via HTTP, asserts the rendered HTML contains the expected iframe and **does not** reference `load_donation_iframe.js`. |
| **Playwright E2E** | `npm test` (in `tests/e2e/`) | Frontend rendering (iframe present, attributes set, fallback link, no upstream JS). Editor-flow tests intentionally skipped — too brittle across Gutenberg versions. |
| **Lint** | `composer lint` | WPCS via `phpcs.xml.dist`. |

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

### CI badges

[![PHPUnit](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/phpunit.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/phpunit.yml)
[![Playwright E2E](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/playwright.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/playwright.yml)
[![Lint (WPCS)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/lint.yml/badge.svg)](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/actions/workflows/lint.yml)

---

## Distribution and release

Two distribution channels:

1. **Primary: [isla studio EDD store](https://isla-stud.io/downloads/betterplace-donation-formular-fuer-wordpress/)** — free download, ZIP comes via Easy Digital Downloads + the EDD Git Download Updater addon. Auto-updates land in **Plugins → Installierte Plugins** like for any other WP plugin.
2. **Secondary: [GitHub release page](https://github.com/s-a-s-k-i-a/betterplace-donation-embed/releases)** — same ZIP as the store, no auto-updates unless installed from the store version.

### Release workflow (for maintainers)

Every release needs **two** state changes:

1. **GitHub side:**
   - bump version in `betterplace-donation-embed.php` (header + `BPDE_VERSION`), `blocks/donation/block.json`, `readme.txt` (Stable tag + Changelog)
   - commit, push, wait for CI green
   - build distribution ZIP via the `rsync` + `zip` recipe at the bottom of this file
   - `git tag -a vX.Y.Z`, push tag, `gh release create vX.Y.Z` with the ZIP attached

2. **isla studio EDD side** (otherwise EDD-SL keeps serving the previous version):
   - Files tab → Version Tag → vX.Y.Z → Fetch Now → select the matching `.zip` asset → Save
   - Licensing Settings → Versions → **Version Number** → set to `X.Y.Z` (this is the one EDD-SL serves as `new_version`) → Save
   - **Cache buster:** the EDD Git Download Updater addon caches the downloaded ZIP at `wp-content/uploads/<YYYY>/<MM>/<file>.zip`. Until that file is deleted manually (SSH or CloudPanel file manager), the package_download endpoint keeps serving the previously-cached ZIP. Verify the served version via:
     ```bash
     curl -sI "$(curl -s -X POST https://isla-stud.io/ -d 'edd_action=get_version&license=…&item_id=3610&version=0.1.0&slug=betterplace-donation-embed&url=https://example.com' | python3 -c "import sys,json;print(json.loads(sys.stdin.read())['package'])")" | grep -i 'content-disposition'
     ```
     If it doesn't show the version you just released, the disk cache is stale — clear it via SSH or use the `edd_git_upload_path` filter to add a hash suffix.

### Release ZIP builder

```bash
cd /tmp && rm -rf bpde-release && mkdir -p bpde-release && cd <plugin-repo> &&
rsync -a \
  --exclude='.git' --exclude='.github' --exclude='tests' --exclude='bin' \
  --exclude='vendor' --exclude='node_modules' \
  --exclude='composer.json' --exclude='composer.lock' \
  --exclude='package.json' --exclude='package-lock.json' \
  --exclude='phpunit.xml.dist' --exclude='phpcs.xml.dist' \
  --exclude='.wp-env.json' --exclude='.editorconfig' --exclude='.gitignore' --exclude='.gitattributes' \
  --exclude='.phpunit.result.cache' --exclude='.DS_Store' \
  . /tmp/bpde-release/betterplace-donation-embed/ &&
cd /tmp/bpde-release &&
zip -r9 /tmp/betterplace-donation-embed-X.Y.Z.zip betterplace-donation-embed/
```

---

## License

GPL-2.0-or-later — see [LICENSE](../LICENSE). The vendored `EDD_SL_Plugin_Updater` class is also GPL-2.0+.

This plugin is independent of betterplace.org and is not endorsed by or affiliated with gut.org gAG.
