#!/usr/bin/env bash
#
# Smoke test: brings up wp-env, activates plugin, creates a test post with the
# shortcode, fetches it via HTTP, and asserts that the expected iframe markup
# is rendered. Fails (exit 1) on any mismatch.
#
# Usage:   bash tests/smoke/smoke.sh
# Requires: docker, node, npx, @wordpress/env
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

WP_ENV_URL="http://localhost:8888"
WP_CLI="npx wp-env run cli wp"
PROJECT_ID="${BPDE_SMOKE_PROJECT_ID:-4667}"

red()   { printf '\033[1;31m%s\033[0m\n' "$*"; }
green() { printf '\033[1;32m%s\033[0m\n' "$*"; }
blue()  { printf '\033[1;34m%s\033[0m\n' "$*"; }

cleanup() {
	blue "Smoke: cleaning up test post..."
	$WP_CLI post delete "$POST_ID" --force >/dev/null 2>&1 || true
}

blue "Smoke: starting wp-env..."
npx wp-env start

blue "Smoke: activating plugin..."
$WP_CLI plugin activate betterplace-donation-embed

blue "Smoke: creating test post with shortcode..."
POST_ID=$(
	$WP_CLI post create \
		--post_type=post \
		--post_status=publish \
		--post_title='BPDE Smoke Test' \
		--post_content="[betterplace_donation project_id=\"$PROJECT_ID\" default_amount=\"25\" color=\"ff0000\"]" \
		--porcelain
)
trap cleanup EXIT
green "Smoke: created post ID $POST_ID"

POST_URL="$WP_ENV_URL/?p=$POST_ID"
blue "Smoke: fetching $POST_URL ..."
sleep 2  # let WP rewrite cache settle

BODY=$(curl -fsSL "$POST_URL")

fail() { red "Smoke FAIL: $*"; exit 1; }
expect_match() {
	if grep -q -F "$1" <<<"$BODY"; then
		green "Smoke OK: contains \"$1\""
	else
		fail "expected body to contain \"$1\""
	fi
}

expect_match '<iframe '
expect_match "/donate/iframe/projects/$PROJECT_ID?"
expect_match 'color=ff0000'
expect_match 'donation_amount=25'
expect_match 'loading="lazy"'
expect_match 'referrerpolicy="strict-origin-when-cross-origin"'

# Verify NO references to the upstream JS loader (whole point of this plugin).
if grep -q -F 'load_donation_iframe.js' <<<"$BODY"; then
	fail "body unexpectedly contains 'load_donation_iframe.js' — plugin should not load betterplace's JS bundle"
fi
green "Smoke OK: no load_donation_iframe.js reference (no global-scope pollution)"

green "Smoke: all checks passed."
