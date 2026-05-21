<?php
/**
 * Plugin Name:       Betterplace Donation Embed
 * Plugin URI:        https://github.com/s-a-s-k-i-a/betterplace-donation-embed
 * Description:       Bindet das betterplace.org-Spendenformular sauber per Shortcode und Gutenberg-Block ein — ohne den fragilen Upstream-JS-Loader, der den globalen Lexical-Scope verschmutzt.
 * Version:           0.1.1
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

define( 'BPDE_VERSION', '0.1.1' );
define( 'BPDE_PLUGIN_FILE', __FILE__ );
define( 'BPDE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPDE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BPDE_BLOCK_DIR', BPDE_PLUGIN_DIR . 'blocks/donation' );

// EDD store endpoint that serves updates (free download, no license required).
define( 'BPDE_EDD_STORE_URL', 'https://isla-stud.io' );
define( 'BPDE_EDD_ITEM_ID', 3610 );

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

	new EDD_SL_Plugin_Updater(
		BPDE_EDD_STORE_URL,
		BPDE_PLUGIN_FILE,
		array(
			'version' => BPDE_VERSION,
			'license' => '',
			'item_id' => BPDE_EDD_ITEM_ID,
			'author'  => 'wp-studio.dev',
			'beta'    => false,
		)
	);
}
