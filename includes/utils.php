<?php
/**
 * Common utilities and functions
 *
 * @package MagicLogin
 */

namespace MagicLogin\Utils;

use MagicLogin\Encryption;
use const MagicLogin\Constants\CRON_HOOK_NAME;
use const MagicLogin\Constants\SETTING_OPTION;
use const MagicLogin\Constants\TOKEN_USER_META;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create token
 *
 * @param object $user \WP_User object
 * @param string $context Context (email|email_code|sms|sms_code) @since 2.4
 *
 * @return string
 */
function create_user_token( $user, $context = 'email' ) {
	$settings  = get_settings(); // phpcs:ignore
	$tokens    = get_user_meta( $user->ID, TOKEN_USER_META, true );
	$tokens    = is_string( $tokens ) ? array( $tokens ) : $tokens;
	$new_token = sha1( wp_generate_password() );

	switch ( $context ) {
		case 'sms':
			// helps to keep url link short due to 300 char limit for most of the SMS providers
			$new_token = substr( $new_token, 0, 12 );
			break;
		case 'sms_code':
			// 6-digit PIN for SMS
			$new_token = wp_rand( 100000, 999999 );
			break;
		case 'email_code':
			$new_token = strtoupper( substr( str_shuffle( 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' ), 0, 10 ) );
			break;
	}

	/**
	 * Filter the token
	 *
	 * @hook   magic_login_create_user_token
	 *
	 * @param  {string} $new_token New token
	 * @param  {int} $user->ID User ID
	 * @param  {string} $context Context
	 *
	 * @return {string} New value
	 * @since  2.4
	 */
	$new_token = apply_filters( 'magic_login_create_user_token', $new_token, $user->ID, $context );

	$hashed_token = hash_hmac( 'sha256', $new_token, wp_salt() );

	$ip = sha1( get_client_ip() );
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		$ip = 'cli';
	}

	$tokens[] = [
		'token'   => $hashed_token,
		'time'    => time(),
		'ip_hash' => $ip,
	];

	update_user_meta( $user->ID, TOKEN_USER_META, $tokens );

	if ( absint( $settings['token_ttl'] ) > 0 ) { // eternal token
		wp_schedule_single_event( time() + ( $settings['token_ttl'] * MINUTE_IN_SECONDS ), CRON_HOOK_NAME, array( $user->ID ) );
	}

	return $new_token;
}


/**
 * Create login link for given user
 *
 * @param object $user    WP_User object
 * @param string $context Context (email|email_code|sms|sms_code) @since 2.4
 * @param string $redirect_to Redirect URL
 *
 * @return mixed|string
 */
function create_login_link( $user, $context = 'email', $redirect_to = null ) {
	global $magic_login_token;
	$token             = create_user_token( $user, $context );
	$magic_login_token = $token;

	$query_args = array(
		'user_id'     => $user->ID,
		'token'       => $token,
		'magic-login' => 1,
	);

	if ( ! empty( $_POST['redirect_to'] ) ) {
		$query_args['redirect_to'] = urlencode( wp_unslash( $_POST['redirect_to'] ) ); // phpcs:ignore
	}

	if ( ! empty( $redirect_to ) ) {
		$query_args['redirect_to'] = urlencode( $redirect_to );
	}

	$login_url = esc_url_raw( add_query_arg( $query_args, wp_login_url() ) );

	/**
	 * Filter the login URL
	 *
	 * @hook   magic_login_create_login_link
	 *
	 * @param  {string} $login_url Login URL
	 * @param  {object} $user WP_User object
	 * @param  {string} $context Context
	 * @param  {string} $redirect_to Redirect URL
	 *
	 * @return {string} New value
	 * @since  2.4
	 */
	$login_url = apply_filters( 'magic_login_create_login_link', $login_url, $user, $context, $redirect_to );

	return $login_url;
}

/**
 * Get client raw ip
 * this should be hashed
 *
 * @return mixed
 */
function get_client_ip() {
	/**
	 * `HTTP_X_FORWARDED_FOR` removed in 1.5
	 * Filters the ip address
	 *
	 * @hook   magic_login_client_ip
	 *
	 * @param  {string} REMOTE_ADDR
	 *
	 * @return {string} New value.
	 * @since  1.5
	 */
	return apply_filters( 'magic_login_client_ip', $_SERVER['REMOTE_ADDR'] ); // phpcs:ignore
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
		'add_login_button'              => true,
		'token_ttl'                     => 5,
		'token_validity'                => 1,
		'token_interval'                => 'MINUTE',
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
		'enforce_redirection_rules'     => true,
		'enable_wp_login_redirection'   => false,
		'enable_role_based_redirection' => false,
		'role_based_redirection_rules'  => [],
		'email_subject'                 => __( 'Log in to {{SITENAME}}', 'magic-login' ),
		'auto_login_links'              => false,
		'enable_ajax'                   => false,
		'enable_woo_integration'        => false,
		'woo_position'                  => 'before',
		'enable_woo_customer_login'     => false,
		'woo_customer_login_position'   => 'before',
		'enable_edd_checkout'           => false,
		'edd_checkout_position'         => 'edd_before_purchase_form',
		'enable_edd_login'              => false,
		'edd_login_position'            => 'before',
		'registration'                  => [
			'enable'                    => false,
			'mode'                      => 'auto', // or standard|shortcode
			'fallback_email_field'      => true, // show email field on auto registration mode as a fallback
			'show_name_field'           => true,
			'require_name_field'        => true,
			'show_terms_field'          => false,
			'require_terms_field'       => false,
			'terms'                     => '',
			'send_email'                => true,
			'email_subject'             => esc_html__( 'Welcome to {{SITENAME}}', 'magic-login' ),
			'email_content'             => get_default_registration_email_text(),
			'enable_domain_restriction' => false,
			'allowed_domains'           => '',
			'role'                      => '',
		],
		'spam_protection'               => [
			'service'             => 'recaptcha',
			'enable_login'        => false,
			'enable_registration' => false,
		],
		'recaptcha'                     => [
			'type'         => 'v3', // which version to use
			'v2_checkbox'  => [
				'site_key'   => '',
				'secret_key' => '',
			],
			'v2_invisible' => [
				'site_key'   => '',
				'secret_key' => '',
			],
			'v3'           => [
				'site_key'   => '',
				'secret_key' => '',
			],
		],
		'cf_turnstile'                  => [
			'site_key'   => '',
			'secret_key' => '',
		],
		'enable_rest_api'               => false,
		'sms'                           => [
			'enable'                           => false,
			'provider'                         => 'twilio',
			'twilio'                           => [
				'account_sid' => '',
				'auth_token'  => '',
				'from'        => '',
			],
			/**
			 * phone_only: Only send SMS if the user enters their phone number (instead of email) when logging in.
			 * sms_and_email: If the user has a phone number linked to their account, send both SMS and email.
			 * sms_or_email: Always send SMS if the user has a phone number, but do not send email notifications.
			 */
			'sms_sending_strategy'             => 'phone_only',
			'wp_registration'                  => false,
			'wp_require_phone'                 => false,
			'magic_registration'               => false,
			'magic_registration_require_phone' => false,
			'login_message'                    => esc_html__( 'Your login code is here: {{MAGIC_LOGIN_CODE}} Your code will expire in {{EXPIRES_WITH_INTERVAL}}', 'magic-login' ),
			'send_registration_message'        => false,
			'registration_message'             => esc_html__( 'Welcome {{FULL_NAME}}! 🎉 Your account on {{SITENAME}} has been created. You can login here: {{MAGIC_LINK}}.', 'magic-login' ),
		],
	];

	if ( MAGIC_LOGIN_IS_NETWORK ) {
		$settings = get_site_option( SETTING_OPTION, [] );
	} else {
		$settings = get_option( SETTING_OPTION, [] );
	}

	// Merge settings with defaults, ensuring new additions and nested arrays are included
	$settings = array_replace_recursive( $defaults, $settings );

	return $settings;
}

/**
 * Default login email message
 *
 * @return mixed|string|void
 */
function get_default_login_email_text() {
	/* translators: Do not translate USERNAME, SITENAME,EXPIRES, MAGIC_LINK, SITENAME, SITEUR, EXPIRES_WITH_INTERVAL: those are placeholders. */
	$email_text = __(
		'Hi {{USERNAME}},

Click and confirm that you want to log in to {{SITENAME}}. This link will expire in {{EXPIRES_WITH_INTERVAL}} and can only be used once:

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
	_deprecated_function( __FUNCTION__, '2.3.4', __NAMESPACE__ . '\get_wp_login_url' );

	return get_wp_login_url();
}

/**
 * Get login link
 *
 * @return mixed|string
 */
function get_wp_login_url() {
	$url = site_url( 'wp-login.php?action=magic_login', 'login_post' );
	/**
	 * Filter the login URL for magic login
	 *
	 * @hook  magic_login_get_wp_login_url
	 * @since 2.3.4
	 */
	$url = apply_filters( 'magic_login_get_wp_login_url', $url );

	return esc_url_raw( $url );
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
	$tokens = is_array( $tokens ) ? $tokens : [];

	/**
	 * Filter user tokens
	 *
	 * @hook   magic_login_user_tokens
	 *
	 * @param  {array} $tokens User tokens.
	 * @param  {int} $user_id User ID.
	 * @param  {boolean} $clear_expired Whether to clear expired tokens or not.
	 *
	 * @return {array} New value
	 * @since  2.1
	 */
	$tokens = (array) apply_filters( 'magic_login_user_tokens', $tokens, $user_id, $clear_expired );

	if ( $clear_expired ) {
		$ttl = get_ttl_by_user( $user_id );

		if ( 0 === $ttl ) { // means token lives forever till used
			return $tokens;
		}

		foreach ( $tokens as $index => $token_data ) {
			if ( empty( $token_data ) || ! isset( $token_data['time'] ) ) {
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

	return $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->usermeta,
		[
			'meta_key' => TOKEN_USER_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		]
	);
}


/**
 * Allowed intervals for TTL.
 *
 * @return array
 * @since 1.2
 */
function get_allowed_intervals() {
	return [
		'MINUTE' => esc_html__( 'Minute(s)', 'magic-login' ),
		'HOUR'   => esc_html__( 'Hour(s)', 'magic-login' ),
		'DAY'    => esc_html__( 'Day(s)', 'magic-login' ),
	];
}

/**
 * Convert minutes to possible time format
 *
 * @param int $timeout_in_minutes TTL in minutes
 *
 * @return array
 * @since 1.2
 */
function get_ttl_with_interval( $timeout_in_minutes ) {
	$ttl      = $timeout_in_minutes;
	$interval = 'MINUTE';

	if ( $ttl > 0 ) {
		if ( 0 === (int) ( $ttl % 1440 ) ) {
			$ttl      = $ttl / 1440;
			$interval = 'DAY';
		} elseif ( 0 === (int) ( $ttl % 60 ) ) {
			$ttl      = $ttl / 60;
			$interval = 'HOUR';
		}
	}

	return array(
		$ttl,
		$interval,
	);
}


/**
 * Get the documentation url
 *
 * @param string $path     The path of documentation
 * @param string $fragment URL Fragment
 *
 * @return string final URL
 */
function get_doc_url( $path = null, $fragment = '' ) {
	$doc_base       = 'https://handyplugins.co/';
	$utm_parameters = '?utm_source=wp_admin&utm_medium=plugin&utm_campaign=settings_page';

	if ( ! empty( $path ) ) {
		$doc_base .= ltrim( $path, '/' );
	}

	$doc_url = trailingslashit( $doc_base ) . $utm_parameters;

	if ( ! empty( $fragment ) ) {
		$doc_url .= '#' . $fragment;
	}

	return $doc_url;
}

/**
 * Check whether current screen is magic login settings page or not
 *
 * @return bool
 * @since 1.2.1
 */
function is_magic_login_settings_screen() {
	$current_screen = get_current_screen();

	if ( ! is_a( $current_screen, '\WP_Screen' ) ) {
		return false;
	}

	if ( false !== strpos( $current_screen->base, 'magic-login' ) ) {
		return true;
	}

	return false;
}

/**
 * Mask given string
 *
 * @param string $input_string  String
 * @param int    $unmask_length The length of unmask
 *
 * @return string
 * @since 2.2
 */
function mask_string( $input_string, $unmask_length ) {
	$output_string = substr( $input_string, 0, $unmask_length );

	if ( strlen( $input_string ) > $unmask_length ) {
		$output_string .= str_repeat( '*', strlen( $input_string ) - $unmask_length );
	}

	return $output_string;
}


/**
 * Check if the given value is masked
 *
 * @param string $value       The value to check
 * @param int    $mask_length The length of the mask
 *
 * @return bool
 * @since 2.2
 */
function is_masked_value( $value, $mask_length = 3 ) {
	// Get the last characters of the string
	$last_chars = substr( $value, - $mask_length );

	// Check if the last characters are asterisks
	return str_repeat( '*', $mask_length ) === $last_chars;
}

/**
 * Get email placeholders by user
 *
 * @param \WP_User $user User object
 *
 * @return array
 * @since 2.2
 */
function get_email_placeholders_by_user( $user ) {
	if ( is_multisite() ) {
		$site_name = get_network()->site_name;
	} else {
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	$settings = \MagicLogin\Utils\get_settings();
	$ttl      = get_ttl_by_user( $user->ID );

	list( $token_ttl, $selected_interval ) = get_ttl_with_interval( $ttl );
	$selected_interval_str                 = strtolower( $selected_interval );
	$allowed_intervals                     = get_allowed_intervals();

	if ( isset( $allowed_intervals[ $selected_interval ] ) ) {
		$selected_interval_str = strtolower( $allowed_intervals[ $selected_interval ] ); // translated interval
	}

	$placeholders = [
		'{{SITEURL}}'               => home_url(),
		'{{USERNAME}}'              => $user->user_login,
		'{{FIRST_NAME}}'            => $user->first_name,
		'{{LAST_NAME}}'             => $user->last_name,
		'{{FULL_NAME}}'             => $user->first_name . ' ' . $user->last_name,
		'{{DISPLAY_NAME}}'          => $user->display_name,
		'{{USER_EMAIL}}'            => $user->user_email,
		'{{SITENAME}}'              => $site_name,
		'{{EXPIRES}}'               => $ttl,
		'{{EXPIRES_WITH_INTERVAL}}' => $token_ttl . ' ' . $selected_interval_str,
		'{{TOKEN_VALIDITY_COUNT}}'  => $settings['token_validity'],
	];

	return $placeholders;
}

/**
 * Get decrypted value
 *
 * @param string $value encrypted value
 *
 * @return bool|mixed|string
 * @since 2.2
 */
function get_decrypted_value( $value ) {
	$encryption      = new Encryption();
	$decrypted_value = $encryption->decrypt( $value );

	if ( false !== $decrypted_value ) {
		return $decrypted_value;
	}

	return $value;
}

/**
 * Get the token TTL by user
 *
 * @param int $user_id User ID
 *
 * @return int TTL in minutes
 * @since 2.2
 */
function get_ttl_by_user( $user_id ) {
	$settings = \MagicLogin\Utils\get_settings();
	$ttl      = $settings['token_ttl'];

	/**
	 * Filter the token TTL by user
	 *
	 * @hook   magic_login_token_ttl_by_user
	 *
	 * @param  {int} $ttl TTL in minutes
	 * @param  {int} $user_id User ID
	 *
	 * @return {int} New value
	 * @since  2.2
	 */
	return apply_filters( 'magic_login_token_ttl_by_user', $ttl, $user_id );
}


/**
 * Default registration email message
 *
 * @return mixed|string|void
 * @since 2.2
 */
function get_default_registration_email_text() {
	$email_text = __(
		'Hi there,
<br><br>
Thank you for signing up to {{SITENAME}}! We are excited to have you on board.
<br>
To get started, simply use the magic link below to log in:
<br><br>
<a href="{{MAGIC_LINK}}" target="_blank" rel="noreferrer noopener">Click here to log in</a>
<br><br>
If the button above does not work, you can also copy and paste the following URL into your browser:
<br>
{{MAGIC_LINK}}
<br><br>
We hope you enjoy your experience with us. If you have any questions or need assistance, feel free to reach out.
<br><br>
Regards,<br>
All at {{SITENAME}}<br>
{{SITEURL}}',
		'magic-login'
	);

	return $email_text;
}


/**
 * Get user by log input
 *
 * @param string $input Input. It can be username, email or phone number
 *
 * @return false|mixed|\WP_User|null
 * @since 2.4
 */
function get_user_by_log_input( $input ) {
	$user = get_user_by( 'login', $input );

	if ( ! defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) || false === MAGIC_LOGIN_USERNAME_ONLY ) {
		if ( ! $user && strpos( $input, '@' ) ) {
			$user = get_user_by( 'email', $input );
		}
	}

	return $user;
}
