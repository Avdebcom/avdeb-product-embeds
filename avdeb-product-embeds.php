<?php
/**
 * Plugin Name:       AVDEB Product Embeds
 * Plugin URI:        https://avdeb.com/developers/#wordpress
 * Description:       Show AVDEB products — a creator's shop, a category or single products — as a block or shortcode, with your affiliate referral code on every link.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            AVDEB
 * Author URI:        https://avdeb.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       avdeb-product-embeds
 *
 * @package AvdebProductEmbeds
 */

defined( 'ABSPATH' ) || exit;

define( 'AVDEB_PE_VERSION', '0.1.0' );
define( 'AVDEB_PE_FILE', __FILE__ );
define( 'AVDEB_PE_DIR', plugin_dir_path( __FILE__ ) );

require_once AVDEB_PE_DIR . 'includes/class-avdeb-pe-settings.php';
require_once AVDEB_PE_DIR . 'includes/class-avdeb-pe-api.php';
require_once AVDEB_PE_DIR . 'includes/class-avdeb-pe-render.php';
require_once AVDEB_PE_DIR . 'includes/class-avdeb-pe-shortcodes.php';

/**
 * Register the stylesheet, shortcodes and block.
 */
function avdeb_pe_init() {
	wp_register_style( 'avdeb-product-embeds', plugins_url( 'assets/style.css', AVDEB_PE_FILE ), array(), AVDEB_PE_VERSION );
	AVDEB_PE_Shortcodes::register();
	register_block_type( AVDEB_PE_DIR . 'blocks/products' );
}
add_action( 'init', 'avdeb_pe_init' );

AVDEB_PE_Settings::init();

/**
 * "Settings" link on the Plugins screen.
 *
 * @param string[] $links Existing action links.
 * @return string[]
 */
function avdeb_pe_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=' . AVDEB_PE_Settings::PAGE );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'avdeb-product-embeds' ) . '</a>' );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( AVDEB_PE_FILE ), 'avdeb_pe_action_links' );
