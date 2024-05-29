<?php
/**
 * Core plugin functionality.
 *
 * @package MagicLogin
 */

namespace MagicLogin\Core;

use function MagicLogin\Utils\is_magic_login_settings_screen;
use \WP_Error as WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\\i18n' );
	add_action( 'init', __NAMESPACE__ . '\\init' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_scripts' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\admin_styles' );

	do_action( 'magic_login_loaded' );
}

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'magic-login' );
	load_textdomain( 'magic-login', WP_LANG_DIR . '/magic-login/magic-login-' . $locale . '.mo' );
	load_plugin_textdomain( 'magic-login', false, plugin_basename( MAGIC_LOGIN_PATH ) . '/languages/' );
}

/**
 * Initializes the plugin and fires an action other plugins can hook into.
 *
 * @return void
 */
function init() {
	do_action( 'magic_login_init' );
}

/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
	return [ 'admin', 'frontend', 'shared', 'shortcode', 'block-editor' ];
}

/**
 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $script Script file name (no .js extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string|WP_Error URL
 */
function script_url( $script, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in MagicLogin script loader.' );
	}

	return MAGIC_LOGIN_URL . "dist/js/{$script}.js";

}

/**
 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $stylesheet Stylesheet file name (no .css extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string URL
 */
function style_url( $stylesheet, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in MagicLogin stylesheet loader.' );
	}

	return MAGIC_LOGIN_URL . "dist/css/{$stylesheet}.css";

}


/**
 * Enqueue scripts for admin.
 *
 * @return void
 */
function admin_scripts() {
	if ( ! is_magic_login_settings_screen() ) {
		return;
	}

	wp_enqueue_script(
		'magic_login_admin',
		script_url( 'admin', 'admin' ),
		[],
		MAGIC_LOGIN_VERSION,
		true
	);
}

/**
 * Enqueue styles for admin.
 *
 * @return void
 */
function admin_styles() {

	if ( ! is_magic_login_settings_screen() ) {
		return;
	}

	wp_enqueue_style(
		'magic_login_admin',
		style_url( 'admin-style', 'admin' ),
		[],
		MAGIC_LOGIN_VERSION
	);

}
