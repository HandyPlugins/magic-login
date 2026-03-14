<?php
/**
 * Security functionality
 *
 * @package MagicLogin
 */

namespace MagicLogin\Security;

use const MagicLogin\Constants\LOGIN_REQUEST_FAILSAFE_TRANSIENT_PREFIX;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Default setup routine.
 *
 * @return void
 */
function setup() {
	add_filter( 'magic_login_pre_send_login_link', __NAMESPACE__ . '\\maybe_enforce_login_request_failsafe', 1, 2 );
	add_action( 'magic_login_send_login_link', __NAMESPACE__ . '\\observe_login_request_failsafe', 10, 1 );
}

/**
 * Apply a per-user failsafe for login emails.
 *
 * @param \WP_Error|null $result Current result.
 * @param \WP_User|null  $user   User object.
 *
 * @return \WP_Error|null
 */
function maybe_enforce_login_request_failsafe( $result, $user = null ) {
	if ( ! is_null( $result ) ) {
		return $result;
	}

	if ( ! is_a( $user, '\WP_User' ) ) {
		return $result;
	}

	$limit = defined( 'MAGIC_LOGIN_REQUEST_FAILSAFE_LIMIT' ) ? absint( MAGIC_LOGIN_REQUEST_FAILSAFE_LIMIT ) : 60;
	$limit = absint( apply_filters( 'magic_login_request_failsafe_limit', $limit, $user ) );

	if ( $limit < 1 ) {
		return $result;
	}

	$minute_time   = (int) floor( time() / MINUTE_IN_SECONDS );
	$transient_key = LOGIN_REQUEST_FAILSAFE_TRANSIENT_PREFIX . $user->ID;
	$send_arr      = get_site_transient( $transient_key );
	$total_sent    = 0;

	if ( ! is_array( $send_arr ) ) {
		$send_arr = [];
	}

	for ( $i = $minute_time; $i > $minute_time - 60; $i-- ) {
		if ( isset( $send_arr[ $i ] ) ) {
			$total_sent += absint( $send_arr[ $i ] );
		}
	}

	if ( $total_sent >= $limit ) {
		return new \WP_Error( 'magic_login_request_failsafe_block', esc_html__( 'We have already sent several login emails recently. Please check your inbox and spam folder before requesting another link.', 'magic-login' ) );
	}

	return $result;
}

/**
 * Observe sent login emails for the per-user failsafe.
 *
 * @param \WP_User|null $user User object.
 *
 * @return void
 */
function observe_login_request_failsafe( $user = null ) {
	if ( ! is_a( $user, '\WP_User' ) ) {
		return;
	}

	$limit = defined( 'MAGIC_LOGIN_REQUEST_FAILSAFE_LIMIT' ) ? absint( MAGIC_LOGIN_REQUEST_FAILSAFE_LIMIT ) : 60;
	$limit = absint( apply_filters( 'magic_login_request_failsafe_limit', $limit, $user ) );

	if ( $limit < 1 ) {
		return;
	}

	$minute_time      = (int) floor( time() / MINUTE_IN_SECONDS );
	$transient_key    = LOGIN_REQUEST_FAILSAFE_TRANSIENT_PREFIX . $user->ID;
	$send_arr         = get_site_transient( $transient_key );
	$updated_send_arr = [];

	if ( ! is_array( $send_arr ) ) {
		$send_arr = [];
	}

	$send_arr[ $minute_time ] = isset( $send_arr[ $minute_time ] ) ? absint( $send_arr[ $minute_time ] ) + 1 : 1;

	for ( $i = $minute_time; $i > $minute_time - 60; $i-- ) {
		if ( isset( $send_arr[ $i ] ) ) {
			$updated_send_arr[ $i ] = absint( $send_arr[ $i ] );
		}
	}

	set_site_transient( $transient_key, $updated_send_arr, HOUR_IN_SECONDS );
}
