<?php
/**
 * Tests for Betterplace_Donation_Embed_Renderer.
 *
 * @package Betterplace_Donation_Embed
 */

/**
 * @coversDefaultClass Betterplace_Donation_Embed_Renderer
 */
class Test_Renderer extends WP_UnitTestCase {

	/**
	 * @return Betterplace_Donation_Embed_Renderer
	 */
	private function renderer( array $defaults = array() ) {
		return new Betterplace_Donation_Embed_Renderer( $defaults );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_returns_empty_when_project_id_missing() {
		$this->assertSame( '', $this->renderer()->build_url( array() ) );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_matches_upstream_shape_for_basic_call() {
		$url = $this->renderer()->build_url( array( 'project_id' => 4667 ) );

		$this->assertStringStartsWith( 'https://www.betterplace.org/de/donate/iframe/projects/4667?', $url );
		$this->assertStringContainsString( 'background_color=ffffff', $url );
		$this->assertStringContainsString( 'color=6c9c2e', $url );
		$this->assertStringContainsString( 'donation_amount=10', $url );
		$this->assertStringContainsString( 'bottom_logo=true', $url );
		$this->assertStringContainsString( 'default_interval=single', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_uses_explicit_attributes_over_defaults() {
		$url = $this->renderer()->build_url(
			array(
				'project_id'       => 1234,
				'color'            => 'ff0000',
				'background_color' => '000000',
				'default_amount'   => 25,
				'default_interval' => 'monthly',
				'lang'             => 'en',
			)
		);

		$this->assertStringContainsString( '/en/donate/iframe/projects/1234?', $url );
		$this->assertStringContainsString( 'color=ff0000', $url );
		$this->assertStringContainsString( 'background_color=000000', $url );
		$this->assertStringContainsString( 'donation_amount=25', $url );
		$this->assertStringContainsString( 'default_interval=monthly', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_applies_settings_defaults_when_attribute_missing() {
		$url = $this->renderer( array( 'project_id' => 4667, 'color' => 'abcdef' ) )
			->build_url( array() );

		$this->assertStringContainsString( '/projects/4667?', $url );
		$this->assertStringContainsString( 'color=abcdef', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_falls_back_to_project_id_when_receiver_id_missing() {
		$url = $this->renderer()->build_url( array( 'project_id' => 7777 ) );
		$this->assertStringContainsString( '/projects/7777?', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_prefers_explicit_receiver_id_over_project_id() {
		$url = $this->renderer()->build_url(
			array(
				'project_id'  => 1,
				'receiver_id' => 9999,
			)
		);
		$this->assertStringContainsString( '/projects/9999?', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_remaps_eft_to_stripe_sepa_debit() {
		$url = $this->renderer()->build_url(
			array(
				'project_id'             => 4667,
				'default_payment_method' => 'eft',
			)
		);
		$this->assertStringContainsString( 'default_payment_method=stripe_sepa_debit', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_rejects_unknown_payment_method() {
		$url = $this->renderer()->build_url(
			array(
				'project_id'             => 4667,
				'default_payment_method' => 'evil_method"<script>',
			)
		);
		$this->assertStringContainsString( 'default_payment_method=&', $url );
		$this->assertStringNotContainsString( 'script', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_clamps_amount_to_valid_range() {
		$too_high = $this->renderer()->build_url( array( 'project_id' => 4667, 'default_amount' => 999 ) );
		$this->assertStringContainsString( 'donation_amount=99', $too_high );

		$too_low = $this->renderer()->build_url( array( 'project_id' => 4667, 'default_amount' => 0 ) );
		$this->assertStringContainsString( 'donation_amount=1', $too_low );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_sanitizes_color_hex() {
		$bad = $this->renderer()->build_url( array( 'project_id' => 4667, 'color' => '<script>' ) );
		$this->assertStringContainsString( 'color=6c9c2e', $bad ); // fell back to default

		$with_hash = $this->renderer()->build_url( array( 'project_id' => 4667, 'color' => '#AABBCC' ) );
		$this->assertStringContainsString( 'color=aabbcc', $with_hash );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_rejects_invalid_interval() {
		$url = $this->renderer()->build_url(
			array(
				'project_id'       => 4667,
				'default_interval' => 'hourly',
			)
		);
		$this->assertStringContainsString( 'default_interval=single', $url );
	}

	/**
	 * @covers ::build_url
	 */
	public function test_build_url_bottom_logo_serializes_as_string() {
		$true_url  = $this->renderer()->build_url( array( 'project_id' => 4667, 'bottom_logo' => true ) );
		$false_url = $this->renderer()->build_url( array( 'project_id' => 4667, 'bottom_logo' => false ) );

		$this->assertStringContainsString( 'bottom_logo=true', $true_url );
		$this->assertStringContainsString( 'bottom_logo=false', $false_url );
	}

	/**
	 * @covers ::render
	 */
	public function test_render_emits_iframe_with_url() {
		$html = $this->renderer()->render( array( 'project_id' => 4667 ) );

		$this->assertStringContainsString( '<iframe ', $html );
		$this->assertStringContainsString( 'src="https://www.betterplace.org/de/donate/iframe/projects/4667?', $html );
		$this->assertStringContainsString( 'loading="lazy"', $html );
		$this->assertStringContainsString( 'referrerpolicy="strict-origin-when-cross-origin"', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_render_includes_fallback_link() {
		$html = $this->renderer()->render( array( 'project_id' => 4667 ) );
		$this->assertStringContainsString( 'href="https://www.betterplace.org/de/donate/platform/projects/4667"', $html );
		$this->assertStringContainsString( 'target="_blank"', $html );
		$this->assertStringContainsString( 'rel="noopener"', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_render_escapes_attributes() {
		$html = $this->renderer()->render(
			array(
				'project_id' => 4667,
				'color'      => '<script>alert(1)</script>', // should be rejected -> default
			)
		);
		$this->assertStringNotContainsString( '<script>', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_render_shows_admin_notice_when_project_id_missing_and_user_can_edit() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		$html = $this->renderer()->render( array() );

		$this->assertStringContainsString( 'bpde-embed--missing-id', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_render_hides_notice_from_subscribers() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->assertSame( '', $this->renderer()->render( array() ) );
	}

	/**
	 * @covers ::render
	 */
	public function test_render_clamps_iframe_dimensions() {
		$html = $this->renderer()->render(
			array(
				'project_id' => 4667,
				'width'      => 10,    // clamped to 200
				'height'     => 10000, // clamped to 2000
			)
		);
		$this->assertStringContainsString( 'width:200px', $html );
		$this->assertStringContainsString( 'height:2000px', $html );
	}

	/**
	 * Regression (v0.1.3): the wrapper must use explicit `width` (not just
	 * `max-width`) so the iframe stays at the configured size inside flex
	 * containers (Divi Pixel popups etc.) where block descendants would
	 * otherwise shrink to intrinsic content width.
	 *
	 * @covers ::render
	 */
	public function test_render_wrapper_uses_explicit_width_and_responsive_max_width() {
		$html = $this->renderer()->render( array( 'project_id' => 4667, 'width' => 600 ) );

		$this->assertStringContainsString( 'width:600px;max-width:100%', $html );
		$this->assertStringNotContainsString( 'max-width:600px', $html );
	}

	/**
	 * v0.1.4: the wrapper must carry a per-instance class plus an inline
	 * media query that collapses width to 100% when the viewport is narrower
	 * than the user-configured width (smartphone responsiveness).
	 *
	 * @covers ::render
	 */
	public function test_render_emits_responsive_media_query_for_configured_width() {
		$html = $this->renderer()->render( array( 'project_id' => 4667, 'width' => 720 ) );

		// One per-instance class on the wrapper.
		$this->assertMatchesRegularExpression( '/class="bpde-embed bpde-embed--i\d+"/', $html );

		// Scoped media query overrides width to 100% below the configured width.
		$this->assertMatchesRegularExpression(
			'/<style>@media\s*\(max-width:720px\)\{\.bpde-embed--i\d+\{width:100%;\}\}<\/style>/',
			$html
		);
	}

	/**
	 * Two renders on the same request must get different per-instance
	 * classes — otherwise their media queries would conflict.
	 *
	 * @covers ::render
	 */
	public function test_render_instance_classes_are_unique_per_call() {
		$r = $this->renderer();
		$one = $r->render( array( 'project_id' => 4667, 'width' => 600 ) );
		$two = $r->render( array( 'project_id' => 4667, 'width' => 800 ) );

		preg_match( '/bpde-embed--(i\d+)/', $one, $m1 );
		preg_match( '/bpde-embed--(i\d+)/', $two, $m2 );

		$this->assertNotEmpty( $m1[1] );
		$this->assertNotEmpty( $m2[1] );
		$this->assertNotSame( $m1[1], $m2[1] );
	}
}
