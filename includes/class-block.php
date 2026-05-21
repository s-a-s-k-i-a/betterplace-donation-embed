<?php
/**
 * Gutenberg block registration (server-rendered).
 *
 * @package Betterplace_Donation_Embed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the betterplace-embed/donation block.
 */
class Betterplace_Donation_Embed_Block {

	/**
	 * Shared renderer instance.
	 *
	 * @var Betterplace_Donation_Embed_Renderer
	 */
	private $renderer;

	/**
	 * Construct the block registrar.
	 *
	 * @param Betterplace_Donation_Embed_Renderer $renderer Shared renderer.
	 */
	public function __construct( Betterplace_Donation_Embed_Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Register the block from block.json.
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			BPDE_BLOCK_DIR,
			array(
				'render_callback' => array( $this, 'render_block' ),
			)
		);
	}

	/**
	 * Server-side render callback.
	 *
	 * @param array<string,mixed> $attributes Block attributes from block.json.
	 * @return string Rendered HTML.
	 */
	public function render_block( $attributes ) {
		return $this->renderer->render( is_array( $attributes ) ? $attributes : array() );
	}
}
