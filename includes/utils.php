<?php
/**
 * Common utilities and functions
 *
 * @package MagicLogin
 */

namespace MagicLogin\Utils;

use const MagicLogin\Constants\SETTING_OPTION;
use const MagicLogin\Constants\TOKEN_USER_META;

/**
 * Create token
 *
 * @param object $user \WP_User object
 *
 * @return string
 */
function create_user_token( $user ) {
	$settings  = get_settings(); // phpcs:ignore
	$tokens    = get_user_meta( $user->ID, TOKEN_USER_META, true );
	$tokens    = is_string( $tokens ) ? array( $tokens ) : $tokens;
	$new_token = sha1( wp_generate_password() );

	$ip = sha1( get_client_ip() );
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		$ip = 'cli';
	}

	$tokens[] = [
		'token'   => $new_token,
		'time'    => time(),
		'ip_hash' => $ip,
	];

	update_user_meta( $user->ID, TOKEN_USER_META, $tokens );
	wp_schedule_single_event( time() + ( $settings['token_ttl'] * MINUTE_IN_SECONDS ), 'magic_login_cleanup_expired_tokens', array( $user->ID, $new_token ) );

	return $new_token;
}


/**
 * Create login link for given user
 *
 * @param object $user WP_User object
 *
 * @return mixed|string
 */
function create_login_link( $user ) {
	$token = create_user_token( $user );

	$query_args = array(
		'user_id' => $user->ID,
		'token'   => $token,
	);

	if ( ! empty( $_POST['redirect_to'] ) ) {
		$query_args['redirect_to'] = esc_url_raw( $_POST['redirect_to'] );
	}

	$login_url = esc_url_raw( add_query_arg( $query_args, wp_login_url() ) );

	return $login_url;
}

/**
 * Get client raw ip
 * this should be hashed
 *
 * @return mixed
 */
function get_client_ip() {
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	return $_SERVER['REMOTE_ADDR'];
}

/**
 * Get settings with defaults
 *
 * @return array
 * @since  1.0
 */
function get_settings() {
	$defaults = [
		'is_default'                    => false,
		'token_ttl'                     => 5,
		'enable_brute_force_protection' => false,
		'brute_force_bantime'           => 60, // in minutes
		'brute_force_login_attempt'     => 10,
		'brute_force_login_time'        => 5, // in minutes
		'enable_login_throttling'       => false,
		'login_throttling_limit'        => 10,
		'login_throttling_time'         => 15, // in minutes
		'enable_ip_check'               => false,
		'enable_domain_restriction'     => false,
		'allowed_domains'               => '',
		'login_email'                   => get_default_login_email_text(),
		'enable_login_redirection'      => false,
		'default_redirection_url'       => '',
		'enable_wp_login_redirection'   => false,
		'enable_role_based_redirection' => false,
		'role_based_redirection_rules'  => [],
	];

	if ( MAGIC_LOGIN_IS_NETWORK ) {
		$settings = get_site_option( SETTING_OPTION, [] );
	} else {
		$settings = get_option( SETTING_OPTION, [] );
	}

	$settings = wp_parse_args( $settings, $defaults );

	return $settings;
}

/**
 * Default login email message
 *
 * @return mixed|string|void
 */
function get_default_login_email_text() {
	/* translators: Do not translate USERNAME, SITENAME,EXPIRES, MAGIC_LINK, SITENAME, SITEURL: those are placeholders. */
	$email_text = __(
		'Hi {{USERNAME}},

Click and confirm that you want to log in to {{SITENAME}}. This link will expire in {{EXPIRES}} minutes and can only be used once:

<a href="{{MAGIC_LINK}}" target="_blank" rel="noreferrer noopener">Log In</a>

Need the link? {{MAGIC_LINK}}


You can safely ignore and delete this email if you do not want to log in.

Regards,
All at {{SITENAME}}
{{SITEURL}}',
		'magic-login'
	);

	return $email_text;
}

/**
 * Is plugin activated network wide?
 *
 * @param string $plugin_file file path
 *
 * @return bool
 * @since 1.0
 */
function is_network_wide( $plugin_file ) {
	if ( ! is_multisite() ) {
		return false;
	}

	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	return is_plugin_active_for_network( plugin_basename( $plugin_file ) );
}

/**
 * Get login link
 *
 * @return mixed|string
 */
function get_magic_login_url() {
	return esc_url_raw( site_url( 'wp-login.php?action=magic_login', 'login_post' ) );
}

/**
 * Get user tokens
 *
 * @param int  $user_id       User ID
 * @param bool $clear_expired flag for clean-up expired tokens
 *
 * @return array|mixed
 */
function get_user_tokens( $user_id, $clear_expired = false ) {
	$tokens = get_user_meta( $user_id, TOKEN_USER_META, true );
	$tokens = is_string( $tokens ) ? array( $tokens ) : $tokens;

	if ( $clear_expired ) {
		$settings = get_settings(); //phpcs:ignore
		$ttl      = absint( $settings['token_ttl'] );

		foreach ( $tokens as $index => $token_data ) {
			if ( empty( $token_data ) ) {
				unset( $tokens[ $index ] );
				continue;
			}

			if ( time() > absint( $token_data['time'] ) + ( $ttl * MINUTE_IN_SECONDS ) ) {
				unset( $tokens[ $index ] );
			}
		}
		update_user_meta( $user_id, TOKEN_USER_META, $tokens );
	}

	return $tokens;
}

/**
 * Get default redirect url for given user
 *
 * @param \WP_User $user User object
 *
 * @return string|void
 */
function get_user_default_redirect( $user ) {
	if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {
		$redirect_to = user_admin_url();
	} elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {
		$redirect_to = get_dashboard_url( $user->ID );
	} elseif ( ! $user->has_cap( 'edit_posts' ) ) {
		$redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
	} else {
		$redirect_to = admin_url();
	}

	return $redirect_to;
}

/**
 * Delete all token meta
 */
function delete_all_tokens() {
	global $wpdb;

	return $wpdb->delete(
		$wpdb->usermeta,
		[
			'meta_key' => TOKEN_USER_META,
		]
	);
}
