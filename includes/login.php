<?php
/**
 * Login functionality
 *
 * @package MagicLogin
 */

namespace MagicLogin\Login;

use const MagicLogin\Constants\TOKEN_USER_META;
use function MagicLogin\Utils\create_login_link;
use function MagicLogin\Utils\get_user_default_redirect;
use function MagicLogin\Utils\get_user_tokens;
use \WP_Error as WP_Error;

// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'login_form_magic_login', $n( 'action_magic_login' ) );
	add_action( 'login_form_login', $n( 'maybe_redirect' ) );
	add_action( 'init', $n( 'handle_login_request' ) );
	add_action( 'magic_login_cleanup_expired_tokens', $n( 'cleanup_expired_tokens' ), 10, 2 );
}

/**
 * Login form actions
 */
function action_magic_login() {
	$show_form = true;
	$errors    = new WP_Error();
	$info      = '<p class="message">' . __( 'Please enter your username or email address. You will receive an email message to log in.', 'magic-login' ) . '</p>';

	// process form request
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['log'] ) ) {
		$user_name = sanitize_user( wp_unslash( $_POST['log'] ) );
		$user      = get_user_by( 'login', $user_name );

		if ( ! $user && strpos( $user_name, '@' ) ) {
			$user = get_user_by( 'email', $user_name );
		}

		$send_link = apply_filters( 'magic_login_pre_send_login_link', null, $user );

		if ( null !== $send_link ) {
			$info      = '';
			$errors    = $send_link;
			$show_form = false;
		} else {
			$errors = send_login_link( $user );
		}

		if ( ! is_wp_error( $errors ) ) {
			$show_form = false;
			$info      = '<p class="message">' . __( 'Please check your inbox for login link. If you did not receive an login email, check your spam folder too.', 'magic-login' ) . '</p>';
		}
	}

	login_header( esc_html__( 'Log in', 'magic-login' ), $info, $errors );

	if ( $show_form ) {
		login_form();
	}

	login_footer();
	exit;
}


/**
 * Send magic link to user
 *
 * @param object $user \WP_User object
 *
 * @return bool
 */
function send_login_link( $user ) {
	if ( is_multisite() ) {
		$site_name = get_network()->site_name;
	} else {
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	$settings    = \MagicLogin\Utils\get_settings();
	$login_link  = create_login_link( $user );
	$login_email = $settings['login_email'];

	$placeholder_values = [
		'{{SITEURL}}'    => home_url(),
		'{{USERNAME}}'   => $user->user_login,
		'{{SITENAME}}'   => $site_name,
		'{{EXPIRES}}'    => $settings['token_ttl'],
		'{{MAGIC_LINK}}' => $login_link,
	];

	// convert line breaks to br
	$login_email = nl2br( $login_email );

	$login_email   = str_replace( array_keys( $placeholder_values ), $placeholder_values, $login_email );
	$email_subject = sprintf( esc_html__( 'Log in to %s', 'magic-login' ), $site_name ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment

	$login_email   = apply_filters( 'magic_login_email_content', $login_email, $placeholder_values );
	$email_subject = apply_filters( 'magic_login_email_subject', $email_subject );

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	do_action( 'magic_login_send_login_link', $user );

	return wp_mail( $user->user_email, $email_subject, $login_email, $headers );
}


/**
 * login form
 */
function login_form() {
	?>
	<form name="magicloginform" id="magicloginform" action="<?php echo esc_url( site_url( 'wp-login.php?action=magic_login', 'login_post' ) ); ?>" method="post" autocomplete="off">
		<p>
			<label for="user_login"><?php esc_html_e( 'Username or Email Address', 'magic-login' ); ?></label>
			<input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" />
		</p>
		<?php

		/**
		 * Fires following the 'email' field in the login form.
		 *
		 * @since 1.0
		 */
		do_action( 'magic_login_form' );

		?>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit" style="float: none;width: 100%;" class="magic-login-submit button button-primary button-large" value="<?php esc_attr_e( 'Send me the link', 'magic-login' ); ?>" />
			<input type="hidden" name="testcookie" value="1" />
		</p>
	</form>
	<?php
}

/**
 * Redirect to magic login page once it used as default login method
 */
function maybe_redirect() {
	global $pagenow;

	if ( 'wp-login.php' !== $pagenow ) {
		return;
	}

	if ( ! empty( ( $_POST ) ) ) {
		return;
	}

	if ( isset( $_REQUEST['interim-login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	/**
	 * short-circuit if want to interrupt redirect
	 */
	if ( null !== apply_filters( 'magic_login_before_login_form_redirect', null ) ) {
		return;
	}

	$settings = \MagicLogin\Utils\get_settings();

	if ( true === $settings['is_default'] ) {
		wp_safe_redirect( esc_url_raw( add_query_arg( 'action', 'magic_login' ) ) );
		exit;
	}
}

/**
 * Handle login request
 */
function handle_login_request() {
	global $pagenow;

	if ( 'wp-login.php' !== $pagenow || empty( $_GET['user_id'] ) || empty( $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	do_action( 'magic_login_handle_login_request' );

	if ( is_user_logged_in() ) {
		$error = sprintf( __( 'Invalid magic login token, but you are logged in as \'%1$s\'. <a href="%2$s">Go to the dashboard instead</a>?', 'magic-login' ), wp_get_current_user()->user_login, admin_url() );
	} else {
		$error = sprintf( __( 'Invalid magic login token. <a href="%s">Try signing in instead</a>?', 'magic-login' ), wp_login_url() );
	}

	// Use a generic error message to ensure user ids can't be sniffed
	$user = get_user_by( 'id', (int) $_GET['user_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $user ) {
		do_action( 'magic_login_invalid_user' );
		wp_die( $error ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	$tokens        = get_user_tokens( $user->ID, true );
	$is_valid      = false;
	$current_token = null;
	foreach ( $tokens as $i => $token_data ) {
		if ( hash_equals( $token_data['token'], $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_valid      = true;
			$current_token = $token_data;
			unset( $tokens[ $i ] );
			break;
		}
	}

	if ( ! $is_valid ) {
		do_action( 'magic_login_invalid_token' );
		wp_die( wp_kses_post( $error ) );
	}

	/**
	 * Fires before setting up auth cookie
	 *
	 * @since 1.0
	 */
	do_action( 'magic_login_before_login', $user, $current_token );

	update_user_meta( $user->ID, TOKEN_USER_META, $tokens );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );

	/**
	 * Fires after setting up auth cookie
	 *
	 * @since 1.0
	 */
	do_action( 'magic_login_logged_in', $user );

	$default_redirect = get_user_default_redirect( $user );
	$login_redirect   = apply_filters( 'magic_login_redirect', $default_redirect, $user );
	wp_safe_redirect( $login_redirect );
	exit;
}

/**
 * Handle cleanup process for expired tokens
 *
 * @param int   $user_id        user id
 * @param array $expired_tokens expired tokens
 */
function one_time_login_cleanup_expired_tokens( $user_id, $expired_tokens ) {
	$tokens     = get_user_meta( $user_id, TOKEN_USER_META, true );
	$tokens     = is_string( $tokens ) ? array( $tokens ) : $tokens;
	$new_tokens = array();
	foreach ( $tokens as $token ) {
		if ( ! in_array( $token, $expired_tokens, true ) ) {
			$new_tokens[] = $token;
		}
	}
	update_user_meta( $user_id, TOKEN_USER_META, $new_tokens );
}
