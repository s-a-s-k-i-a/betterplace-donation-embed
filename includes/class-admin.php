<?php
/**
 * Minimal admin settings page.
 *
 * @package Betterplace_Donation_Embed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin screen under Settings → Spendenformular.
 */
class Betterplace_Donation_Embed_Admin {

	const OPTION_GROUP = 'bpde_settings';
	const OPTION_NAME  = 'bpde_settings';
	const PAGE_SLUG    = 'betterplace-donation-embed';

	/**
	 * Register hooks.
	 */
	public function register() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( BPDE_PLUGIN_FILE ), array( $this, 'add_action_link' ) );
	}

	/**
	 * Static accessor for the merged defaults — used by the Renderer.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_defaults() {
		$saved = get_option( self::OPTION_NAME, array() );
		return is_array( $saved ) ? $saved : array();
	}

	/**
	 * Register option, sections, fields.
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'bpde_defaults',
			__( 'Standardwerte', 'betterplace-donation-embed' ),
			function () {
				echo '<p>' . esc_html__( 'Diese Werte gelten als Fallback, wenn Shortcode oder Block kein eigenes Attribut setzen.', 'betterplace-donation-embed' ) . '</p>';
			},
			self::PAGE_SLUG
		);

		$this->add_text_field( 'project_id', __( 'Standard-Projekt-ID', 'betterplace-donation-embed' ), 'z. B. 4667', 'number' );
		$this->add_text_field( 'color', __( 'Akzentfarbe (Hex, ohne #)', 'betterplace-donation-embed' ), '6c9c2e' );
		$this->add_text_field( 'background_color', __( 'Hintergrund (Hex, ohne #)', 'betterplace-donation-embed' ), 'ffffff' );
		$this->add_text_field( 'default_amount', __( 'Standard-Spendenbetrag (€)', 'betterplace-donation-embed' ), '10', 'number' );
		$this->add_select_field(
			'default_interval',
			__( 'Standard-Intervall', 'betterplace-donation-embed' ),
			array(
				'single'  => __( 'Einmalig', 'betterplace-donation-embed' ),
				'monthly' => __( 'Monatlich', 'betterplace-donation-embed' ),
				'yearly'  => __( 'Jährlich', 'betterplace-donation-embed' ),
			)
		);
		$this->add_text_field( 'width', __( 'Iframe-Breite (px)', 'betterplace-donation-embed' ), '600', 'number' );
		$this->add_text_field( 'height', __( 'Iframe-Höhe (px)', 'betterplace-donation-embed' ), '800', 'number' );
	}

	/**
	 * Register a text field.
	 *
	 * @param string $key         Option key.
	 * @param string $label       Human label.
	 * @param string $placeholder Placeholder text.
	 * @param string $type        HTML input type.
	 */
	private function add_text_field( $key, $label, $placeholder = '', $type = 'text' ) {
		add_settings_field(
			'bpde_' . $key,
			$label,
			function () use ( $key, $placeholder, $type ) {
				$saved = self::get_defaults();
				$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
				printf(
					'<input type="%1$s" id="bpde_%2$s" name="%3$s[%2$s]" value="%4$s" placeholder="%5$s" class="regular-text" />',
					esc_attr( $type ),
					esc_attr( $key ),
					esc_attr( self::OPTION_NAME ),
					esc_attr( $value ),
					esc_attr( $placeholder )
				);
			},
			self::PAGE_SLUG,
			'bpde_defaults'
		);
	}

	/**
	 * Register a select field.
	 *
	 * @param string               $key     Option key.
	 * @param string               $label   Human label.
	 * @param array<string,string> $options Value => Label map.
	 */
	private function add_select_field( $key, $label, array $options ) {
		add_settings_field(
			'bpde_' . $key,
			$label,
			function () use ( $key, $options ) {
				$saved = self::get_defaults();
				$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
				printf( '<select id="bpde_%1$s" name="%2$s[%1$s]">', esc_attr( $key ), esc_attr( self::OPTION_NAME ) );
				foreach ( $options as $opt_value => $opt_label ) {
					printf(
						'<option value="%1$s" %3$s>%2$s</option>',
						esc_attr( $opt_value ),
						esc_html( $opt_label ),
						selected( $value, $opt_value, false )
					);
				}
				echo '</select>';
			},
			self::PAGE_SLUG,
			'bpde_defaults'
		);
	}

	/**
	 * Sanitize submitted settings.
	 *
	 * @param array<string,mixed>|mixed $raw Raw submitted data.
	 * @return array<string,mixed> Sanitized data.
	 */
	public function sanitize( $raw ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$out = array();

		if ( isset( $raw['project_id'] ) && '' !== $raw['project_id'] ) {
			$out['project_id'] = absint( $raw['project_id'] );
		}
		if ( isset( $raw['color'] ) && preg_match( '/^#?[0-9a-fA-F]{6}$/', $raw['color'] ) ) {
			$out['color'] = ltrim( strtolower( $raw['color'] ), '#' );
		}
		if ( isset( $raw['background_color'] ) && preg_match( '/^#?[0-9a-fA-F]{6}$/', $raw['background_color'] ) ) {
			$out['background_color'] = ltrim( strtolower( $raw['background_color'] ), '#' );
		}
		if ( isset( $raw['default_amount'] ) && '' !== $raw['default_amount'] ) {
			$out['default_amount'] = max( 1, min( 99, absint( $raw['default_amount'] ) ) );
		}
		if ( isset( $raw['default_interval'] ) && in_array( $raw['default_interval'], array( 'single', 'monthly', 'yearly' ), true ) ) {
			$out['default_interval'] = $raw['default_interval'];
		}
		if ( isset( $raw['width'] ) && '' !== $raw['width'] ) {
			$out['width'] = max( 200, min( 1200, absint( $raw['width'] ) ) );
		}
		if ( isset( $raw['height'] ) && '' !== $raw['height'] ) {
			$out['height'] = max( 400, min( 2000, absint( $raw['height'] ) ) );
		}

		return $out;
	}

	/**
	 * Add the menu entry under Settings.
	 */
	public function add_menu() {
		add_options_page(
			__( 'Betterplace-Spendenformular', 'betterplace-donation-embed' ),
			__( 'Spendenformular', 'betterplace-donation-embed' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap bpde-settings-wrap">
			<h1><?php esc_html_e( 'Betterplace-Spendenformular', 'betterplace-donation-embed' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Bindet das Spendenformular von betterplace.org als statischen Iframe ein — ohne den fragilen JS-Loader.', 'betterplace-donation-embed' ); ?>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Verwendung', 'betterplace-donation-embed' ); ?></h2>
			<div class="bpde-usage">
				<h3><?php esc_html_e( 'Shortcode', 'betterplace-donation-embed' ); ?></h3>
				<pre><code>[betterplace_donation project_id="4667" color="6c9c2e" default_amount="10"]</code></pre>

				<h3><?php esc_html_e( 'Gutenberg-Block', 'betterplace-donation-embed' ); ?></h3>
				<p>
					<?php
					printf(
						/* translators: %s: block name */
						esc_html__( 'Im Block-Editor: „%s" suchen und einfügen. Einstellungen in der rechten Sidebar.', 'betterplace-donation-embed' ),
						esc_html__( 'Betterplace-Spendenformular', 'betterplace-donation-embed' )
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin styles only on our settings page.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_admin_styles( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style(
			'bpde-admin',
			BPDE_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			BPDE_VERSION
		);
	}

	/**
	 * Add a "Settings" link on the plugins page.
	 *
	 * @param array<string,string> $links Existing links.
	 * @return array<string,string>
	 */
	public function add_action_link( $links ) {
		$url   = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
		$label = __( 'Einstellungen', 'betterplace-donation-embed' );
		array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $label ) ) );
		return $links;
	}
}
