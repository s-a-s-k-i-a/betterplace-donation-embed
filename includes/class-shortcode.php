<?php
/**
 * [betterplace_donation] shortcode.
 *
 * @package Betterplace_Donation_Embed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the shortcode and delegates rendering to the Renderer.
 */
class Betterplace_Donation_Embed_Shortcode {

	const TAG = 'betterplace_donation';

	/**
	 * Shared renderer instance.
	 *
	 * @var Betterplace_Donation_Embed_Renderer
	 */
	private $renderer;

	/**
	 * Construct the shortcode handler.
	 *
	 * @param Betterplace_Donation_Embed_Renderer $renderer Shared renderer.
	 */
	public function __construct( Betterplace_Donation_Embed_Renderer $renderer ) {
		$this->renderer = $renderer;
	}

	/**
	 * Register the shortcode.
	 */
	public function register() {
		add_shortcode( self::TAG, array( $this, 'render' ) );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array<string,string>|string $atts    Shortcode attributes.
	 * @param string|null                 $content Enclosed content (unused).
	 * @return string Rendered HTML.
	 */
	public function render( $atts, $content = null ) {
		unset( $content );

		$atts = shortcode_atts(
			array(
				'project_id'             => '',
				'receiver_id'            => '',
				'receiver_type'          => '',
				'lang'                   => '',
				'color'                  => '',
				'background_color'       => '',
				'default_amount'         => '',
				'default_interval'       => '',
				'default_payment_method' => '',
				'bottom_logo'            => '',
				'width'                  => '',
				'height'                 => '',
			),
			is_array( $atts ) ? $atts : array(),
			self::TAG
		);

		return $this->renderer->render( $atts );
	}
}
