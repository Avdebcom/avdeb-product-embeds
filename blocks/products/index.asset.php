<?php
/**
 * Editor script dependencies (the script uses WordPress globals — no build step).
 *
 * @package AvdebProductEmbeds
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
	'version'      => '0.1.0',
);
