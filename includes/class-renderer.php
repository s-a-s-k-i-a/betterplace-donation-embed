<?php
/**
 * Builds the betterplace donation iframe URL and HTML.
 *
 * Mirrors the URL shape that betterplace's official load_donation_iframe.js
 * builds in getIframeSource(), but renders a static iframe — no JS loader,
 * no global lexical scope pollution.
 *
 * @package Betterplace_Donation_Embed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renderer for the betterplace donation iframe.
 */
class Betterplace_Donation_Embed_Renderer {

	const DOMAIN          = 'https://www.betterplace.org';
	const FALLBACK_PATH   = '/de/donate/platform/projects/';
	const DEFAULT_LANG    = 'de';
	const DEFAULT_TYPE    = 'project';
	const DEFAULT_COLOR   = '6c9c2e';
	const DEFAULT_BG      = 'ffffff';
	const DEFAULT_AMOUNT  = 10;
	const DEFAULT_INT     = 'single';
	const DEFAULT_WIDTH   = 600;
	const DEFAULT_HEIGHT  = 800;

	/**
	 * Site-wide defaults from the settings page.
	 *
	 * @var array<string,mixed>
	 */
	private $defaults;

	/**
	 * @param array<string,mixed> $defaults Settings-page defaults.
	 */
	public function __construct( array $defaults = array() ) {
		$this->defaults = $defaults;
	}

	/**
	 * Build the iframe URL.
	 *
	 * @param array<string,mixed> $atts User attributes.
	 * @return string Sanitized URL, empty string if project_id missing.
	 */
	public function build_url( array $atts ) {
		$cfg = $this->normalize( $atts );

		if ( empty( $cfg['receiver_id'] ) ) {
			return '';
		}

		$path = sprintf(
			'/%s/donate/iframe/%ss/%d',
			rawurlencode( $cfg['lang'] ),
			rawurlencode( $cfg['receiver_type'] ),
			$cfg['receiver_id']
		);

		$query = array(
			'background_color'       => $cfg['background_color'],
			'color'                  => $cfg['color'],
			'donation_amount'        => $cfg['default_amount'],
			'bottom_logo'            => $cfg['bottom_logo'] ? 'true' : 'false',
			'default_payment_method' => $cfg['default_payment_method'],
			'default_interval'       => $cfg['default_interval'],
		);

		return esc_url_raw( self::DOMAIN . $path . '?' . http_build_query( $query ) );
	}

	/**
	 * Render the full iframe HTML (wrapper + iframe + fallback link).
	 *
	 * Output is fully escaped and safe for direct echo in shortcode/block render.
	 *
	 * @param array<string,mixed> $atts User attributes.
	 * @return string HTML markup or empty string if project_id missing.
	 */
	public function render( array $atts ) {
		$cfg = $this->normalize( $atts );
		$url = $this->build_url( $atts );

		if ( '' === $url ) {
			return $this->render_missing_id_notice();
		}

		$title = sprintf(
			/* translators: %d: betterplace project ID. */
			__( 'Spendenformular (betterplace-Projekt %d)', 'betterplace-donation-embed' ),
			$cfg['receiver_id']
		);

		$fallback_url   = self::DOMAIN . self::FALLBACK_PATH . $cfg['receiver_id'];
		$fallback_label = __( 'Alternativ direkt auf betterplace.org spenden', 'betterplace-donation-embed' );

		$wrapper_style = sprintf( 'max-width:%dpx;margin:0 auto;', (int) $cfg['width'] );
		$iframe_style  = sprintf(
			'display:block;border:0;width:100%%;height:%dpx;background:transparent;',
			(int) $cfg['height']
		);

		return sprintf(
			'<div class="bpde-embed" data-project-id="%1$d" style="%2$s"><iframe src="%3$s" title="%4$s" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" style="%5$s"></iframe><p class="bpde-fallback" style="text-align:center;margin:.75em 0 0;font-size:.9em;"><a href="%6$s" target="_blank" rel="noopener">%7$s</a></p></div>',
			(int) $cfg['receiver_id'],
			esc_attr( $wrapper_style ),
			esc_url( $url ),
			esc_attr( $title ),
			esc_attr( $iframe_style ),
			esc_url( $fallback_url ),
			esc_html( $fallback_label )
		);
	}

	/**
	 * Render a friendly notice when the project_id is missing.
	 *
	 * Shown to editors only; hidden on the public frontend.
	 *
	 * @return string HTML or empty string.
	 */
	private function render_missing_id_notice() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		return sprintf(
			'<div class="bpde-embed bpde-embed--missing-id" style="padding:1em;border:1px dashed #c00;background:#fff5f5;color:#900;">%s</div>',
			esc_html__( 'Betterplace-Spendenformular: bitte project_id setzen (Shortcode-Attribut oder Block-Einstellung).', 'betterplace-donation-embed' )
		);
	}

	/**
	 * Normalize + apply defaults to user attributes.
	 *
	 * @param array<string,mixed> $atts Raw attributes.
	 * @return array<string,mixed> Normalized attributes.
	 */
	private function normalize( array $atts ) {
		$merged = array_merge(
			array(
				'project_id'             => '',
				'receiver_id'            => '',
				'receiver_type'          => self::DEFAULT_TYPE,
				'lang'                   => self::DEFAULT_LANG,
				'color'                  => self::DEFAULT_COLOR,
				'background_color'       => self::DEFAULT_BG,
				'default_amount'         => self::DEFAULT_AMOUNT,
				'default_interval'       => self::DEFAULT_INT,
				'default_payment_method' => '',
				'bottom_logo'            => true,
				'width'                  => self::DEFAULT_WIDTH,
				'height'                 => self::DEFAULT_HEIGHT,
			),
			$this->defaults,
			array_filter(
				$atts,
				static function ( $v ) {
					return null !== $v && '' !== $v;
				}
			)
		);

		// receiver_id falls back to project_id (matches upstream behavior).
		if ( empty( $merged['receiver_id'] ) && ! empty( $merged['project_id'] ) ) {
			$merged['receiver_id'] = $merged['project_id'];
		}

		// Coerce types.
		$merged['receiver_id']    = absint( $merged['receiver_id'] );
		$merged['default_amount'] = max( 1, min( 99, absint( $merged['default_amount'] ) ) );
		$merged['width']          = max( 200, min( 1200, absint( $merged['width'] ) ) );
		$merged['height']         = max( 400, min( 2000, absint( $merged['height'] ) ) );

		// Sanitize color hex (6 chars, no #).
		$merged['color']            = $this->sanitize_hex( $merged['color'], self::DEFAULT_COLOR );
		$merged['background_color'] = $this->sanitize_hex( $merged['background_color'], self::DEFAULT_BG );

		// Whitelisted enums.
		$merged['lang']                   = in_array( $merged['lang'], array( 'de', 'en' ), true ) ? $merged['lang'] : self::DEFAULT_LANG;
		$merged['receiver_type']          = in_array( $merged['receiver_type'], array( 'project', 'fundraising_event', 'organisation' ), true ) ? $merged['receiver_type'] : self::DEFAULT_TYPE;
		$merged['default_interval']       = in_array( $merged['default_interval'], array( 'single', 'monthly', 'yearly' ), true ) ? $merged['default_interval'] : self::DEFAULT_INT;
		$merged['default_payment_method'] = $this->sanitize_payment_method( $merged['default_payment_method'] );

		// bottom_logo: accept bool, '1'/'0', 'true'/'false'.
		$merged['bottom_logo'] = filter_var( $merged['bottom_logo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
		if ( null === $merged['bottom_logo'] ) {
			$merged['bottom_logo'] = true;
		}

		return $merged;
	}

	/**
	 * Sanitize a hex color string.
	 *
	 * @param string $value    Color value (with or without leading #).
	 * @param string $fallback Default if invalid.
	 * @return string Six lowercase hex chars, no leading #.
	 */
	private function sanitize_hex( $value, $fallback ) {
		$value = ltrim( (string) $value, '#' );
		return preg_match( '/^[0-9a-fA-F]{6}$/', $value ) ? strtolower( $value ) : $fallback;
	}

	/**
	 * Sanitize the default_payment_method to upstream-supported values.
	 *
	 * Upstream maps "eft" -> "stripe_sepa_debit"; we do the same.
	 *
	 * @param string $value Raw value.
	 * @return string Sanitized value, empty if unknown.
	 */
	private function sanitize_payment_method( $value ) {
		$value = strtolower( trim( (string) $value ) );
		if ( 'eft' === $value ) {
			return 'stripe_sepa_debit';
		}
		$allowed = array( '', 'paypal', 'stripe', 'stripe_sepa_debit', 'sofort', 'apple_pay', 'google_pay' );
		return in_array( $value, $allowed, true ) ? $value : '';
	}
}
