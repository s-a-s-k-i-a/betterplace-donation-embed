<?php
/**
 * Tests for the [betterplace_donation] shortcode.
 *
 * @package Betterplace_Donation_Embed
 */

/**
 * @coversDefaultClass Betterplace_Donation_Embed_Shortcode
 */
class Test_Shortcode extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// Ensure the plugin's hooks fired (plugins_loaded ran via test bootstrap).
		Betterplace_Donation_Embed_Plugin::instance()->init();
	}

	/**
	 * @covers ::register
	 */
	public function test_shortcode_is_registered() {
		$this->assertTrue( shortcode_exists( 'betterplace_donation' ) );
	}

	/**
	 * @covers ::render
	 */
	public function test_shortcode_renders_iframe_for_valid_project_id() {
		$html = do_shortcode( '[betterplace_donation project_id="4667"]' );

		$this->assertStringContainsString( '<iframe ', $html );
		$this->assertStringContainsString( '/donate/iframe/projects/4667?', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_shortcode_supports_color_and_amount_overrides() {
		$html = do_shortcode( '[betterplace_donation project_id="4667" color="ff0000" default_amount="50"]' );

		$this->assertStringContainsString( 'color=ff0000', $html );
		$this->assertStringContainsString( 'donation_amount=50', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_shortcode_in_post_content_renders_via_the_content_filter() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => '[betterplace_donation project_id="4667"]',
				'post_status'  => 'publish',
			)
		);

		$post = get_post( $post_id );
		$html = apply_filters( 'the_content', $post->post_content );

		$this->assertStringContainsString( '<iframe ', $html );
		$this->assertStringContainsString( '/projects/4667?', $html );
	}

	/**
	 * @covers ::render
	 */
	public function test_shortcode_without_project_id_renders_nothing_for_subscriber() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
		$html = do_shortcode( '[betterplace_donation]' );
		$this->assertSame( '', $html );
	}
}
