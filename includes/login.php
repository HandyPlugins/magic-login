<?php
/**
 * Login functionality
 *
 * @deprecated 2.4
 * @package    MagicLogin
 */

namespace MagicLogin\Login;

use MagicLogin\CodeLogin;
use MagicLogin\LoginManager;
use function MagicLogin\Utils\get_email_placeholders_by_user;
use function MagicLogin\Utils\get_user_by_log_input;
use function MagicLogin\Utils\get_wp_login_url;
use function MagicLogin\Utils\get_ttl_by_user;
use const MagicLogin\Constants\CRON_HOOK_NAME;
use const MagicLogin\Constants\TOKEN_USER_META;
use function MagicLogin\Utils\create_login_link;
use function MagicLogin\Utils\get_allowed_intervals;
use function MagicLogin\Utils\get_ttl_with_interval;
use function MagicLogin\Utils\get_user_default_redirect;
use function MagicLogin\Utils\get_user_tokens;
use \WP_Error as WP_Error;

// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::setup' );
}

/**
 * Process login request
 *
 * @param array $args  Custom messages - Added in 2.1
 *                     eg: [
 *                     'info_message' => 'Custom message'
 *                     'error_message' => 'Custom message'
 *                     'success_message' => 'Custom message'
 *                     ]
 *
 * @return array
 */
function process_login_request( $args = array() ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::process_login_request' );

	return LoginManager::process_login_request( $args );
}


/**
 * Handle login submission POST request
 *
 * @param array $args Arguments
 *
 * @return array
 * @since 2.2
 */
function handle_login_submission( $args ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::process_login_form_submission' );

	return LoginManager::process_login_form_submission( $args );
}


/**
 * Login form actions
 */
function action_magic_login() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::wp_login_action' );

	LoginManager::wp_login_action();
}

/**
 * Send magic link to user
 *
 * @param object            $user       \WP_User object
 * @param mixed|string|bool $login_link use given link when it provided. @since 1.9
 * @param mixed|string|bool $code_login create login link with code. @since 2.4
 *
 * @return bool
 */
function send_login_link( $user, $login_link = false, $code_login = false ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::send_login_link' );

	return LoginManager::send_login_link( $user, $login_link, $code_login );
}


/**
 * login form
 */
function login_form() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::login_form' );

	LoginManager::login_form();
}


/**
 * Redirect to magic login page once it used as default login method
 */
function maybe_redirect() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::maybe_redirect' );

	LoginManager::maybe_redirect();
}

/**
 * Handle login request
 */
function handle_login_request() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::handle_login_request' );

	LoginManager::handle_login_request();
}

/**
 * Handle cleanup process for expired tokens
 *
 * @param int $user_id user id
 */
function cleanup_expired_tokens( $user_id ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::clear_expired_tokens' );

	LoginManager::clear_expired_tokens( $user_id );
}

/**
 * Add login button to wp-login.php
 */
function print_login_button() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::print_login_button' );

	LoginManager::print_login_button();
}

/**
 * Add small tweaks to login form
 */
function login_css() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::login_css' );

	LoginManager::login_css();
}


/**
 * Maybe add login link to outgoing email
 *
 * @param array $atts wp_mail args
 *
 * @return mixed
 * @since 1.6
 */
function maybe_add_auto_login_link( $atts ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::maybe_add_auto_login_link' );

	return LoginManager::maybe_add_auto_login_link( $atts );
}

/**
 * Add auto login link to message
 *
 * @param array    $args wp mail content
 * @param \WP_User $user User Object
 *
 * @return string
 * @since 1.6
 */
function add_auto_login_link_to_message( $args, $user ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::add_auto_login_link_to_message' );

	return LoginManager::add_auto_login_link_to_message( $args, $user );
}

/**
 * Check if auto login link is excluded for given mail
 *
 * @param array $args wp mail args
 *
 * @return bool
 * @since 1.6
 */
function is_auto_login_link_excluded_mail( $args ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::is_auto_login_link_excluded_mail' );

	return LoginManager::is_auto_login_link_excluded_mail( $args );
}


/**
 * Ajax callback for login requests
 *
 * @return void
 */
function ajax_request() {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::ajax_request' );

	LoginManager::ajax_request();
}

/**
 * Replace {{MAGIC_LINK}} placeholder with login link for all outgoing emails
 *
 * @param array $atts wp_mail args
 *
 * @return mixed
 * @since 2.0.0
 */
function replace_magic_link_in_wp_mail( $atts ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::replace_magic_link_in_wp_mail' );

	return LoginManager::replace_magic_link_in_wp_mail( $atts );
}

/**
 * Check if email has single recipient
 *
 * @param array $atts wp_mail args
 *
 * @return bool
 * @since 2.0.0
 */
function has_single_recipient( $atts ) {
	_deprecated_function( __FUNCTION__, '2.4', 'LoginManager::is_single_recipient' );

	return LoginManager::is_single_recipient( $atts );
}

