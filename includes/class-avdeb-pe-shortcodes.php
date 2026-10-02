<?php
/**
 * Shortcodes.
 *
 * @package AvdebProductEmbeds
 */

defined( 'ABSPATH' ) || exit;

/**
 * [avdeb_products], [avdeb_product], [avdeb_link].
 */
final class AVDEB_PE_Shortcodes {

	/**
	 * Register all shortcodes.
	 */
	public static function register() {
		add_shortcode( 'avdeb_products', array( __CLASS__, 'products' ) );
		add_shortcode( 'avdeb_product', array( __CLASS__, 'product' ) );
		add_shortcode( 'avdeb_link', array( __CLASS__, 'link' ) );
	}

	/**
	 * [avdeb_products creator="slug" tag="slug" q="search" limit="6" columns="3"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public static function products( $atts ) {
		$a = shortcode_atts(
			array(
				'creator' => '',
				'tag'     => '',
				'q'       => '',
				'limit'   => 6,
				'columns' => 0,
			),
			$atts,
			'avdeb_products'
		);
		return AVDEB_PE_Render::grid( AVDEB_PE_API::products( $a ), absint( $a['columns'] ) );
	}

	/**
	 * [avdeb_product handle="product-handle"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public static function product( $atts ) {
		$a = shortcode_atts( array( 'handle' => '' ), $atts, 'avdeb_product' );
		if ( '' === $a['handle'] ) {
			return '';
		}
		return AVDEB_PE_Render::grid( AVDEB_PE_API::products( array( 'handle' => $a['handle'] ) ), 1 );
	}

	/**
	 * [avdeb_link url="/store/handle/"]text[/avdeb_link]
	 *
	 * @param array|string $atts    Attributes.
	 * @param string|null  $content Link text.
	 * @return string
	 */
	public static function link( $atts, $content = null ) {
		$a    = shortcode_atts( array( 'url' => '/' ), $atts, 'avdeb_link' );
		$href = AVDEB_PE_Render::link( $a['url'] );
		$text = null !== $content && '' !== trim( $content ) ? $content : __( 'Shop on AVDEB', 'avdeb-product-embeds' );
		if ( '' === $href ) {
			return esc_html( $text );
		}
		return '<a class="avdeb-pe-link" href="' . esc_url( $href ) . '"' . AVDEB_PE_Render::link_attrs( $href ) . '>' . esc_html( $text ) . '</a>';
	}
}
