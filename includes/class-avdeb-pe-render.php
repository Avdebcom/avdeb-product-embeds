<?php
/**
 * HTML for product cards, grids and links.
 *
 * @package AvdebProductEmbeds
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rendering helpers. Output is plain links and images — no scripts, no cookies, no tracking.
 */
final class AVDEB_PE_Render {

	/**
	 * Whether a URL points at avdeb.com.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_avdeb_url( $url ) {
		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		$host   = wp_parse_url( $url, PHP_URL_HOST );
		if ( 'https' !== $scheme || ! is_string( $host ) ) {
			return false;
		}
		$host = strtolower( $host );
		return 'avdeb.com' === $host || 'www.avdeb.com' === $host;
	}

	/**
	 * Absolute avdeb.com URL with the referral code added, or '' for other sites.
	 *
	 * @param string $url Absolute avdeb.com URL or a path like /store/handle/.
	 * @return string
	 */
	public static function link( $url ) {
		$url = trim( (string) $url );
		if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			$url = 'https://avdeb.com' . $url;
		}
		if ( ! self::is_avdeb_url( $url ) ) {
			return '';
		}
		$ref = self::ref();
		return '' !== $ref ? add_query_arg( 'ref', $ref, $url ) : $url;
	}

	/**
	 * The site owner's referral code: the API key owner's when a key is saved, else the manual code.
	 *
	 * @return string
	 */
	public static function ref() {
		if ( '' !== AVDEB_PE_API::api_key() ) {
			$info = AVDEB_PE_API::key_info();
			return is_array( $info ) && $info['ok'] ? $info['owner']['ref'] : '';
		}
		return AVDEB_PE_Settings::clean_ref( AVDEB_PE_Settings::get( 'ref_code' ) );
	}

	/**
	 * Link attributes: rel="sponsored" when the link carries a referral code.
	 *
	 * @param string $url The link (defaults to checking the site's referral code).
	 * @return string Escaped attribute string starting with a space.
	 */
	public static function link_attrs( $url = '' ) {
		$sponsored = '' !== $url ? false !== strpos( (string) wp_parse_url( $url, PHP_URL_QUERY ), 'ref=' ) : '' !== self::ref();
		$rel       = $sponsored ? 'sponsored noopener' : 'noopener';
		$attrs = ' rel="' . esc_attr( $rel ) . '"';
		if ( (int) AVDEB_PE_Settings::get( 'new_tab' ) ) {
			$attrs .= ' target="_blank"';
		}
		return $attrs;
	}

	/**
	 * Localized price.
	 *
	 * @param float|null $amount   Amount.
	 * @param string     $currency ISO 4217 code.
	 * @return string
	 */
	public static function price( $amount, $currency ) {
		if ( null === $amount ) {
			return '';
		}
		$symbols = array(
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'CAD' => 'CA$',
			'AUD' => 'A$',
		);
		$number = number_format_i18n( (float) $amount, 2 );
		return isset( $symbols[ $currency ] ) ? $symbols[ $currency ] . $number : $number . ' ' . $currency;
	}

	/**
	 * One product card.
	 *
	 * @param array<string, mixed> $p Product.
	 * @return string
	 */
	public static function card( $p ) {
		// The API already added the referral code (key owner's, or the manual one passed as ?ref=).
		$href = (string) $p['url'];
		if ( ! self::is_avdeb_url( $href ) ) {
			return '';
		}
		$html  = '<li class="avdeb-pe__item"><a class="avdeb-pe__card" href="' . esc_url( $href ) . '"' . self::link_attrs( $href ) . '>';
		$html .= '<span class="avdeb-pe__media">';
		if ( ! empty( $p['image'] ) ) {
			$html .= '<img src="' . esc_url( $p['image'] ) . '" alt="' . esc_attr( $p['title'] ) . '" loading="lazy" decoding="async" />';
		}
		$html .= '</span>';
		$html .= '<span class="avdeb-pe__title">' . esc_html( $p['title'] ) . '</span>';
		if ( ! empty( $p['creator']['name'] ) ) {
			/* translators: %s: designer name */
			$html .= '<span class="avdeb-pe__by">' . esc_html( sprintf( __( 'Designed by %s', 'avdeb-product-embeds' ), $p['creator']['name'] ) ) . '</span>';
		}
		$price = self::price( $p['price'], $p['currency'] );
		if ( '' !== $price ) {
			$html .= '<span class="avdeb-pe__price">';
			if ( null !== $p['compare_at_price'] && $p['compare_at_price'] > $p['price'] ) {
				$html .= '<del>' . esc_html( self::price( $p['compare_at_price'], $p['currency'] ) ) . '</del> ';
			}
			$html .= esc_html( $price ) . '</span>';
		}
		$html .= '<span class="avdeb-pe__cta">' . esc_html__( 'View product', 'avdeb-product-embeds' ) . '</span>';
		$html .= '</a></li>';
		return $html;
	}

	/**
	 * A grid of products (with the disclosure). Empty results render nothing for visitors and a hint for editors.
	 *
	 * @param array<int, array<string, mixed>> $products Products.
	 * @param int                              $columns  Columns (0 = default setting).
	 * @return string
	 */
	public static function grid( $products, $columns = 0 ) {
		if ( empty( $products ) ) {
			return current_user_can( 'edit_posts' )
				? '<p class="avdeb-pe__empty">' . esc_html__( 'No AVDEB products match these settings (only editors see this note).', 'avdeb-product-embeds' ) . '</p>'
				: '';
		}
		wp_enqueue_style( 'avdeb-product-embeds' );
		$columns = $columns > 0 ? $columns : (int) AVDEB_PE_Settings::get( 'columns' );
		$columns = min( 6, max( 1, min( $columns, count( $products ) ) ) );

		$html = '<div class="avdeb-pe" style="--avdeb-pe-cols:' . (int) $columns . '"><ul class="avdeb-pe__grid">';
		foreach ( $products as $p ) {
			$html .= self::card( $p );
		}
		return $html . '</ul>' . self::disclosure() . '</div>';
	}

	/**
	 * Disclosure note (only when a referral code is set and the setting is on).
	 *
	 * @return string
	 */
	public static function disclosure() {
		if ( '' === self::ref() || ! (int) AVDEB_PE_Settings::get( 'show_disclosure' ) ) {
			return '';
		}
		$text = (string) AVDEB_PE_Settings::get( 'disclosure_text' );
		return '' !== $text ? '<p class="avdeb-pe__disclosure">' . esc_html( $text ) . '</p>' : '';
	}
}
