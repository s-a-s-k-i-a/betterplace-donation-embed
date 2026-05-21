<?php
/**
 * Main plugin orchestrator.
 *
 * @package Betterplace_Donation_Embed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Singleton that boots the plugin's subsystems.
 */
final class Betterplace_Donation_Embed_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Shared renderer used by shortcode + block.
	 *
	 * @var Betterplace_Donation_Embed_Renderer
	 */
	private $renderer;

	/**
	 * Get the singleton.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->renderer = new Betterplace_Donation_Embed_Renderer( Betterplace_Donation_Embed_Admin::get_defaults() );
	}

	/**
	 * Wire up hooks.
	 */
	public function init() {
		load_plugin_textdomain( 'betterplace-donation-embed', false, dirname( plugin_basename( BPDE_PLUGIN_FILE ) ) . '/languages' );

		( new Betterplace_Donation_Embed_Shortcode( $this->renderer ) )->register();
		( new Betterplace_Donation_Embed_Block( $this->renderer ) )->register();
		( new Betterplace_Donation_Embed_Admin() )->register();
	}

	/**
	 * Expose the renderer for tests and external code.
	 *
	 * @return Betterplace_Donation_Embed_Renderer
	 */
	public function renderer() {
		return $this->renderer;
	}
}
