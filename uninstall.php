<?php
/**
 * Remove plugin data on uninstall.
 *
 * @package AvdebProductEmbeds
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

delete_option( 'avdeb_pe_settings' );
delete_option( 'avdeb_pe_cache_v' );

// Cached API responses (transients named avdeb_pe_<version>_<hash>[_s]).
// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- one-time cleanup on uninstall.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_avdeb_pe_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_avdeb_pe_' ) . '%'
	)
);
