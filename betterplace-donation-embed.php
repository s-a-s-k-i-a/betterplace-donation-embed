<?php
/**
 * Plugin Name:       Betterplace Donation Embed
 * Plugin URI:        https://github.com/s-a-s-k-i-a/betterplace-donation-embed
 * Description:       Bindet das betterplace.org-Spendenformular sauber per Shortcode und Gutenberg-Block ein — ohne den fragilen Upstream-JS-Loader, der den globalen Lexical-Scope verschmutzt.
 * Version:           0.1.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            wp-studio.dev
 * Author URI:        https://wp-studio.dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       betterplace-donation-embed
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @package Betterplace_Donation_Embed
 */

defined( 'ABSPATH' ) || exit;

define( 'BPDE_VERSION', '0.1.2' );
define( 'BPDE_PLUGIN_FILE', __FILE__ );
define( 'BPDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPDE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BPDE_BLOCK_DIR', BPDE_PLUGIN_DIR . 'blocks/donation' );

// EDD store endpoint that serves updates.
define( 'BPDE_EDD_STORE_URL', 'https://isla-stud.io' );
define( 'BPDE_EDD_ITEM_ID', 3610 );

/*
 * Shared license key for the free auto-update flow.
 *
 * The plugin is distributed for free and intentionally bakes in a single
 * shared license key (configured at isla-stud.io with unlimited activations
 * and never-expires). This removes the "paste your license key here" step
 * that EDD Software Licensing otherwise requires before serving the update
 * package URL — every install of this plugin can fetch updates out of the
 * box, without any UI configuration by the end user.
 *
 * The key is not a secret: anyone can read it in this file. It only grants
 * read access to a public download we already give away for free, so there
 * is nothing to abuse. Self-hosters who want to point at a different store
 * or use their own license can override it via the `bpde_license_key`
 * filter or by defining BPDE_EDD_LICENSE_KEY before this file is loaded.
 */
if ( ! defined( 'BPDE_EDD_LICENSE_KEY' ) ) {
	define( 'BPDE_EDD_LICENSE_KEY', 'd700ee51c8b5c1b68ec8b4e46b39f949' );
}

require_once BPDE_PLUGIN_DIR . 'includes/class-renderer.php';
require_once BPDE_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once BPDE_PLUGIN_DIR . 'includes/class-block.php';
require_once BPDE_PLUGIN_DIR . 'includes/class-admin.php';
require_once BPDE_PLUGIN_DIR . 'includes/class-plugin.php';

add_action(
	'plugins_loaded',
	static function () {
		Betterplace_Donation_Embed_Plugin::instance()->init();
		bpde_init_updater();
	}
);

/**
 * Wire up the EDD Software Licensing plugin updater so installed copies
 * receive update notifications from the isla-stud.io store endpoint.
 *
 * Disable in wp-config.php with `define( 'BPDE_DISABLE_UPDATER', true );`
 * or via the `bpde_disable_updater` filter.
 */
function bpde_init_updater() {
	if ( defined( 'BPDE_DISABLE_UPDATER' ) && BPDE_DISABLE_UPDATER ) {
		return;
	}

	/**
	 * Filter whether the EDD updater is wired up.
	 *
	 * @param bool $disabled Default false — updater is wired.
	 */
	if ( apply_filters( 'bpde_disable_updater', false ) ) {
		return;
	}

	if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
		require_once BPDE_PLUGIN_DIR . 'includes/class-edd-sl-plugin-updater.php';
	}

	/**
	 * Filter the license key used for update requests.
	 *
	 * Override to point a self-hosted fork at a different EDD store.
	 *
	 * @param string $license Default shared license key.
	 */
	$license = apply_filters( 'bpde_license_key', BPDE_EDD_LICENSE_KEY );

	new EDD_SL_Plugin_Updater(
		BPDE_EDD_STORE_URL,
		BPDE_PLUGIN_FILE,
		array(
			'version' => BPDE_VERSION,
			'license' => $license,
			'item_id' => BPDE_EDD_ITEM_ID,
			'author'  => 'wp-studio.dev',
			'beta'    => false,
		)
	);
}
