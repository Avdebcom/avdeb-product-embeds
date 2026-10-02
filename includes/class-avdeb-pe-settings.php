<?php
/**
 * Settings → AVDEB.
 *
 * @package AvdebProductEmbeds
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin options (one array option) and the settings screen.
 */
final class AVDEB_PE_Settings {

	const OPTION = 'avdeb_pe_settings';
	const PAGE   = 'avdeb-product-embeds';

	/**
	 * Defaults for every option.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults() {
		return array(
			'api_key'         => '',
			'ref_code'        => '',
			'cache_hours'     => 6,
			'columns'         => 3,
			'new_tab'         => 0,
			'show_disclosure' => 1,
			'disclosure_text' => __( 'As an AVDEB affiliate, I earn a commission from qualifying purchases.', 'avdeb-product-embeds' ),
		);
	}

	/**
	 * Read one option.
	 *
	 * @param string $key Option key.
	 * @return mixed
	 */
	public static function get( $key ) {
		$saved = get_option( self::OPTION, array() );
		$all   = array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	/**
	 * Referral codes are letters, digits, "-" and "_" (as issued in the AVDEB affiliate dashboard).
	 *
	 * @param mixed $code Raw code.
	 * @return string
	 */
	public static function clean_ref( $code ) {
		return substr( preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $code ), 0, 40 );
	}

	/**
	 * Hook into wp-admin.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_post_avdeb_pe_clear_cache', array( __CLASS__, 'clear_cache' ) );
		add_action( 'update_option_' . self::OPTION, array( 'AVDEB_PE_API', 'flush' ) );
	}

	/**
	 * Add Settings → AVDEB.
	 */
	public static function menu() {
		add_options_page(
			__( 'AVDEB Product Embeds', 'avdeb-product-embeds' ),
			__( 'AVDEB', 'avdeb-product-embeds' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the option and its fields.
	 */
	public static function register() {
		register_setting(
			self::PAGE,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section( 'avdeb_pe_main', '', '__return_false', self::PAGE );

		$fields = array(
			'api_key'         => __( 'API key', 'avdeb-product-embeds' ),
			'ref_code'        => __( 'Referral code', 'avdeb-product-embeds' ),
			'columns'         => __( 'Default columns', 'avdeb-product-embeds' ),
			'new_tab'         => __( 'Open links in a new tab', 'avdeb-product-embeds' ),
			'show_disclosure' => __( 'Affiliate disclosure', 'avdeb-product-embeds' ),
			'cache_hours'     => __( 'Refresh products every', 'avdeb-product-embeds' ),
		);
		foreach ( $fields as $key => $label ) {
			add_settings_field( $key, $label, array( __CLASS__, 'field_' . $key ), self::PAGE, 'avdeb_pe_main', array( 'label_for' => 'avdeb-pe-' . $key ) );
		}
	}

	/**
	 * Sanitize the whole option array.
	 *
	 * @param mixed $input Submitted values.
	 * @return array<string, mixed>
	 */
	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$d     = self::defaults();
		$text  = isset( $input['disclosure_text'] ) ? sanitize_text_field( wp_unslash( $input['disclosure_text'] ) ) : '';

		// The key field is left empty on purpose (never echoed back): empty = keep the saved key.
		$api_key = (string) self::get( 'api_key' );
		$new_key = isset( $input['api_key'] ) ? trim( sanitize_text_field( wp_unslash( $input['api_key'] ) ) ) : '';
		if ( ! empty( $input['remove_api_key'] ) ) {
			$api_key = '';
		} elseif ( '' !== $new_key ) {
			if ( preg_match( AVDEB_PE_API::SECRET_KEY_RE, $new_key ) ) {
				$api_key = $new_key;
			} elseif ( 0 === strpos( $new_key, 'avdeb_pk_' ) ) {
				add_settings_error( self::OPTION, 'avdeb_pe_pk', __( 'That is a publishable key (for the JavaScript embed). The plugin needs a secret key that starts with avdeb_sk_.', 'avdeb-product-embeds' ) );
			} else {
				add_settings_error( self::OPTION, 'avdeb_pe_key', __( 'That does not look like an AVDEB secret key (avdeb_sk_ followed by 32 letters and digits).', 'avdeb-product-embeds' ) );
			}
		}

		return array(
			'api_key'         => $api_key,
			'ref_code'        => self::clean_ref( isset( $input['ref_code'] ) ? wp_unslash( $input['ref_code'] ) : '' ),
			'cache_hours'     => min( 48, max( 1, absint( isset( $input['cache_hours'] ) ? $input['cache_hours'] : $d['cache_hours'] ) ) ),
			'columns'         => min( 6, max( 1, absint( isset( $input['columns'] ) ? $input['columns'] : $d['columns'] ) ) ),
			'new_tab'         => empty( $input['new_tab'] ) ? 0 : 1,
			'show_disclosure' => empty( $input['show_disclosure'] ) ? 0 : 1,
			'disclosure_text' => '' !== $text ? substr( $text, 0, 300 ) : $d['disclosure_text'],
		);
	}

	/**
	 * Input name for a key.
	 *
	 * @param string $key Option key.
	 * @return string
	 */
	private static function name( $key ) {
		return self::OPTION . '[' . $key . ']';
	}

	/** API key field (never echoed back) + connection status. */
	public static function field_api_key() {
		$saved = AVDEB_PE_API::api_key();
		printf(
			'<input type="password" id="avdeb-pe-api_key" name="%s" value="" class="regular-text" autocomplete="off" spellcheck="false" placeholder="%s" />',
			esc_attr( self::name( 'api_key' ) ),
			esc_attr( '' !== $saved ? 'avdeb_sk_…' . substr( $saved, -4 ) . ' — ' . __( 'saved; paste a new key to replace it', 'avdeb-product-embeds' ) : 'avdeb_sk_…' )
		);
		if ( '' !== $saved ) {
			$info = AVDEB_PE_API::key_info();
			echo '<p>';
			if ( is_array( $info ) && $info['ok'] ) {
				printf(
					/* translators: 1: partner name, 2: partner type (affiliate/creator), 3: referral code */
					esc_html__( '✓ Connected as %1$s (%2$s). Links carry referral code %3$s.', 'avdeb-product-embeds' ),
					'<strong>' . esc_html( $info['owner']['name'] ) . '</strong>',
					esc_html( $info['owner']['type'] ),
					'' !== $info['owner']['ref'] ? '<code>' . esc_html( $info['owner']['ref'] ) . '</code>' : esc_html__( '(none yet)', 'avdeb-product-embeds' )
				);
			} else {
				echo '<span style="color:#b32d2e">' . esc_html( sprintf(
					/* translators: %s: error message from AVDEB */
					__( '✗ Key not accepted: %s', 'avdeb-product-embeds' ),
					is_array( $info ) ? $info['error'] : ''
				) ) . '</span>';
			}
			echo '</p>';
			printf(
				'<label><input type="checkbox" name="%s" value="1" /> %s</label>',
				esc_attr( self::name( 'remove_api_key' ) ),
				esc_html__( 'Remove the saved key', 'avdeb-product-embeds' )
			);
		}
		echo '<p class="description">';
		printf(
			/* translators: %s: link to the AVDEB partner dashboard */
			esc_html__( 'Optional. AVDEB affiliates and creators can create a secret key under %s: your referral code is then added automatically and more products can be shown. Without a key the plugin still works.', 'avdeb-product-embeds' ),
			'<a href="https://avdeb.com/affiliate/dashboard/developers/" target="_blank" rel="noopener">' . esc_html__( 'Partner dashboard → Embeds & API', 'avdeb-product-embeds' ) . '</a>'
		);
		echo '</p>';
	}

	/** Referral code field. */
	public static function field_ref_code() {
		printf(
			'<input type="text" id="avdeb-pe-ref_code" name="%s" value="%s" class="regular-text" maxlength="40" pattern="[A-Za-z0-9_-]*" />',
			esc_attr( self::name( 'ref_code' ) ),
			esc_attr( self::get( 'ref_code' ) )
		);
		echo '<p class="description">';
		printf(
			/* translators: %s: link to the AVDEB affiliate program */
			esc_html__( 'Only used without an API key: added as ?ref= to every AVDEB link. Find it in your affiliate dashboard, or leave it empty. Not a partner yet? %s', 'avdeb-product-embeds' ),
			'<a href="https://avdeb.com/affiliate/" target="_blank" rel="noopener">' . esc_html__( 'Join the affiliate program', 'avdeb-product-embeds' ) . '</a>'
		);
		echo '</p>';
	}

	/** Columns field. */
	public static function field_columns() {
		printf(
			'<input type="number" id="avdeb-pe-columns" name="%s" value="%d" min="1" max="6" class="small-text" />',
			esc_attr( self::name( 'columns' ) ),
			(int) self::get( 'columns' )
		);
	}

	/** New tab field. */
	public static function field_new_tab() {
		printf(
			'<label><input type="checkbox" id="avdeb-pe-new_tab" name="%s" value="1" %s /> %s</label>',
			esc_attr( self::name( 'new_tab' ) ),
			checked( 1, (int) self::get( 'new_tab' ), false ),
			esc_html__( 'Open product links in a new browser tab', 'avdeb-product-embeds' )
		);
	}

	/** Disclosure fields. */
	public static function field_show_disclosure() {
		printf(
			'<label><input type="checkbox" id="avdeb-pe-show_disclosure" name="%s" value="1" %s /> %s</label><br />',
			esc_attr( self::name( 'show_disclosure' ) ),
			checked( 1, (int) self::get( 'show_disclosure' ), false ),
			esc_html__( 'Show this note under embeds when a referral code is set', 'avdeb-product-embeds' )
		);
		printf(
			'<input type="text" name="%s" value="%s" class="large-text" maxlength="300" aria-label="%s" />',
			esc_attr( self::name( 'disclosure_text' ) ),
			esc_attr( self::get( 'disclosure_text' ) ),
			esc_attr__( 'Disclosure text', 'avdeb-product-embeds' )
		);
		echo '<p class="description">' . esc_html__( 'Many countries (for example the US FTC rules) require you to disclose affiliate links.', 'avdeb-product-embeds' ) . '</p>';
	}

	/** Cache field. */
	public static function field_cache_hours() {
		printf(
			'<input type="number" id="avdeb-pe-cache_hours" name="%s" value="%d" min="1" max="48" class="small-text" /> %s',
			esc_attr( self::name( 'cache_hours' ) ),
			(int) self::get( 'cache_hours' ),
			esc_html__( 'hours', 'avdeb-product-embeds' )
		);
	}

	/**
	 * The settings screen.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'AVDEB Product Embeds', 'avdeb-product-embeds' ); ?></h1>
			<?php if ( isset( $_GET['avdeb_pe_cleared'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Product cache cleared.', 'avdeb-product-embeds' ); ?></p></div>
			<?php endif; ?>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::PAGE );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>

			<h2><?php esc_html_e( 'Usage', 'avdeb-product-embeds' ); ?></h2>
			<p><?php esc_html_e( 'Add the "AVDEB Products" block in the editor, or use a shortcode:', 'avdeb-product-embeds' ); ?></p>
			<p><code>[avdeb_products creator="your-creator-slug" limit="6"]</code> — <?php esc_html_e( 'a creator\'s products', 'avdeb-product-embeds' ); ?></p>
			<p><code>[avdeb_products tag="stickers" limit="8" columns="4"]</code> — <?php esc_html_e( 'a category', 'avdeb-product-embeds' ); ?></p>
			<p><code>[avdeb_products q="cat stickers" limit="6"]</code> — <?php esc_html_e( 'search results', 'avdeb-product-embeds' ); ?></p>
			<p><code>[avdeb_product handle="product-handle"]</code> — <?php esc_html_e( 'one product', 'avdeb-product-embeds' ); ?></p>
			<p><code>[avdeb_link url="/store/product-handle/"]Get yours[/avdeb_link]</code> — <?php esc_html_e( 'a text link', 'avdeb-product-embeds' ); ?></p>
			<p>
				<?php
				printf(
					/* translators: %s: documentation link */
					esc_html__( 'Full documentation: %s', 'avdeb-product-embeds' ),
					'<a href="https://avdeb.com/developers/#wordpress" target="_blank" rel="noopener">avdeb.com/developers</a>'
				);
				?>
			</p>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<input type="hidden" name="action" value="avdeb_pe_clear_cache" />
				<?php wp_nonce_field( 'avdeb_pe_clear_cache' ); ?>
				<?php submit_button( __( 'Refresh products now', 'avdeb-product-embeds' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * "Refresh products now" button.
	 */
	public static function clear_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'avdeb-product-embeds' ) );
		}
		check_admin_referer( 'avdeb_pe_clear_cache' );
		AVDEB_PE_API::flush();
		wp_safe_redirect( admin_url( 'options-general.php?page=' . self::PAGE . '&avdeb_pe_cleared=1' ) );
		exit;
	}
}
