<?php
/**
 * Server render for the avdeb/products block.
 *
 * @package AvdebProductEmbeds
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$avdeb_pe_source = isset( $attributes['source'] ) ? $attributes['source'] : 'creator';
$avdeb_pe_value  = isset( $attributes[ $avdeb_pe_source ] ) ? trim( (string) $attributes[ $avdeb_pe_source ] ) : '';

if ( in_array( $avdeb_pe_source, array( 'creator', 'tag', 'handle', 'q' ), true ) && '' !== $avdeb_pe_value ) {
	$avdeb_pe_html = AVDEB_PE_Render::grid(
		AVDEB_PE_API::products(
			array(
				$avdeb_pe_source => $avdeb_pe_value,
				'limit'          => isset( $attributes['limit'] ) ? (int) $attributes['limit'] : 6,
			)
		),
		'handle' === $avdeb_pe_source ? 1 : ( isset( $attributes['columns'] ) ? (int) $attributes['columns'] : 0 )
	);
} else {
	$avdeb_pe_html = current_user_can( 'edit_posts' )
		? '<p class="avdeb-pe__empty">' . esc_html__( 'Choose a creator, category, product or search in the block settings.', 'avdeb-product-embeds' ) . '</p>'
		: '';
}

if ( '' !== $avdeb_pe_html ) {
	echo '<div ' . get_block_wrapper_attributes() . '>' . $avdeb_pe_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in AVDEB_PE_Render.
}
