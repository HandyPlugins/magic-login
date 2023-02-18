<?php
/**
 * Login functionality
 *
 * @package MagicLogin
 */

namespace MagicLogin\Login;

use const MagicLogin\Constants\CRON_HOOK_NAME;
use const MagicLogin\Constants\TOKEN_USER_META;
use function MagicLogin\Utils\create_login_link;
use function MagicLogin\Utils\get_allowed_intervals;
use function MagicLogin\Utils\get_ttl_with_interval;
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
	add_action( CRON_HOOK_NAME, $n( 'cleanup_expired_tokens' ) );
	add_action( 'login_footer', $n( 'print_login_button' ) );
	add_action( 'login_head', $n( 'login_css' ) );
	add_filter( 'wp_mail', $n( 'maybe_add_auto_login_link' ), 999 );
}


/**
 * Process login request
 *
 * @return array
 */
function process_login_request() {
	$show_form    = true;
	$errors       = new WP_Error();
	$info         = '<p class="message">' . __( 'Please enter your username or email address. You will receive an email message to log in.', 'magic-login' ) . '</p>';
	$is_processed = false;

	// process form request
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['log'] ) ) {
		$user_name = sanitize_user( wp_unslash( $_POST['log'] ) );
		$user      = get_user_by( 'login', $user_name );

		if ( ! defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) || false === MAGIC_LOGIN_USERNAME_ONLY ) {
			if ( ! $user && strpos( $user_name, '@' ) ) {
				$user = get_user_by( 'email', $user_name );
			}
		}

		$is_processed = true;

		/**
		 * Short circuit to prevent unwanted requests
		 */
		$send_link = apply_filters( 'magic_login_pre_send_login_link', null, $user );

		if ( ! is_a( $user, '\WP_User' ) ) {
			$info = '';
			if ( defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) && MAGIC_LOGIN_USERNAME_ONLY ) {
				$errors = new WP_Error( 'missing_user', esc_html__( 'There is no account with that username.', 'magic-login' ) );
			} else {
				$errors = new WP_Error( 'missing_user', esc_html__( 'There is no account with that username or email address.', 'magic-login' ) );
			}
			$show_form = true;
		} elseif ( null !== $send_link ) {
			$info      = '';
			$errors    = $send_link;
			$show_form = false;
		} else {
			$errors = send_login_link( $user );
		}

		if ( ! is_wp_error( $errors ) ) {
			$show_form = false;
			$info      = '<p class="message magic_login_block_login_success">' . __( 'Please check your inbox for the login link. If you did not receive a login email, check your spam folder too.', 'magic-login' ) . '</p>';
		}
	}

	return [
		'show_form'    => $show_form,
		'errors'       => $errors,
		'info'         => $info,
		'is_processed' => $is_processed,
	];
}

/**
 * Login form actions
 */
function action_magic_login() {

	$login_request = process_login_request();

	login_header( esc_html__( 'Log in', 'magic-login' ), $login_request['info'], $login_request['errors'] );

	if ( $login_request['show_form'] ) {
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

	$settings      = \MagicLogin\Utils\get_settings();
	$login_link    = create_login_link( $user );
	$login_email   = $settings['login_email'];
	$email_subject = $settings['email_subject'];

	list( $token_ttl, $selected_interval ) = get_ttl_with_interval( $settings['token_ttl'] );
	$selected_interval_str                 = strtolower( $selected_interval );

	$allowed_intervals = get_allowed_intervals();

	if ( isset( $allowed_intervals[ $selected_interval ] ) ) {
		$selected_interval_str = strtolower( $allowed_intervals[ $selected_interval ] ); // translated interval
	}

	$placeholder_values = [
		'{{SITEURL}}'               => home_url(),
		'{{USERNAME}}'              => $user->user_login,
		'{{SITENAME}}'              => $site_name,
		'{{EXPIRES}}'               => $settings['token_ttl'],
		'{{EXPIRES_WITH_INTERVAL}}' => $token_ttl . ' ' . $selected_interval_str,
		'{{MAGIC_LINK}}'            => $login_link,
		'{{TOKEN_VALIDITY_COUNT}}'  => $settings['token_validity'],
	];

	$login_email   = str_replace( array_keys( $placeholder_values ), $placeholder_values, $login_email );
	$email_subject = str_replace( array_keys( $placeholder_values ), $placeholder_values, $email_subject );

	$login_email   = apply_filters( 'magic_login_email_content', $login_email, $placeholder_values );
	$email_subject = apply_filters( 'magic_login_email_subject', $email_subject, $placeholder_values );

	$headers = apply_filters( 'magic_login_email_headers', array( 'Content-Type: text/html; charset=UTF-8' ) );

	foreach ( (array) $headers as $header ) {
		if ( false !== stripos( $header, 'text/html' ) ) {
			// convert line breaks to br when content type is html but
			// input doesn't contain HTML tags (adding <br/> can ruin the templating)
			if ( strip_tags( $login_email, '<a>' ) === $login_email ) {
				$login_email = nl2br( $login_email );
			}
			break;
		}
	}

	/**
	 * Send the email only once at a run.
	 * Eg: when having login block in a page, and shortcode at some other part of the page.
	 * It will send the email twice due to the way we handle the request.
	 */
	if ( did_action( 'magic_login_send_login_link' ) ) {
		return true;
	}

	do_action( 'magic_login_send_login_link', $user );

	return wp_mail( $user->user_email, $email_subject, $login_email, $headers );
}


/**
 * login form
 */
function login_form() {
	$user_login = '';

	if ( isset( $_POST['log'] ) && is_string( $_POST['log'] ) ) {
		$user_login = wp_unslash( $_POST['log'] );
	}
	?>
	<form name="magicloginform" id="magicloginform" action="<?php echo esc_url( site_url( 'wp-login.php?action=magic_login', 'login_post' ) ); ?>" method="post" autocomplete="off">
		<p>
			<?php if ( defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) && MAGIC_LOGIN_USERNAME_ONLY ) : ?>
				<label for="user_login"><?php esc_html_e( 'Username', 'magic-login' ); ?></label>
			<?php else : ?>
				<label for="user_login"><?php esc_html_e( 'Username or Email Address', 'magic-login' ); ?></label>
			<?php endif; ?>
			<input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr( $user_login ); ?>" size="20" autocapitalize="off" autocomplete="username" required />
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
			<input type="submit" name="wp-submit" id="wp-submit" style="float: none;width: 100%;" class="magic-login-submit button button-primary button-hero" value="<?php esc_attr_e( 'Send me the link', 'magic-login' ); ?>" />
			<?php if ( isset( $_GET['redirect_to'] ) ) :  // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $_GET['redirect_to'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>">
			<?php endif; ?>

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

	/**
	 * Since 1.2.2 $pagenow control has been deprecated
	 * in favor compatibility with 3rd party plugins
	 */
	if ( 'wp-login.php' !== $pagenow && empty( $_GET['magic-login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( empty( $_GET['user_id'] ) || empty( $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	do_action( 'magic_login_handle_login_request' );

	if ( is_user_logged_in() ) {
		/* translators: 1: User login 2: Dashboard URL */
		$error = sprintf( __( 'Invalid magic login token, but you are logged in as \'%1$s\'. <a href="%2$s">Go to the dashboard instead</a>?', 'magic-login' ), wp_get_current_user()->user_login, admin_url() );
	} else {
		/* translators: %s: Login URL */
		$error = sprintf( __( 'Invalid magic login token. <a href="%s">Try signing in instead</a>?', 'magic-login' ), wp_login_url() );
	}

	// Use a generic error message to ensure user ids can't be sniffed
	$user = get_user_by( 'id', (int) $_GET['user_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $user ) {
		do_action( 'magic_login_invalid_user' );
		wp_die( $error ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	$settings       = \MagicLogin\Utils\get_settings();
	$token_validity = $settings['token_validity'];

	$tokens        = get_user_tokens( $user->ID, true );
	$is_valid      = false;
	$current_token = null;
	foreach ( $tokens as $i => $token_data ) {
		if ( empty( $token_data ) || ! is_array( $token_data ) || ! isset( $token_data['token'] ) ) {
			unset( $tokens[ $i ] );
			continue;
		}

		if ( hash_equals( $token_data['token'], hash_hmac( 'sha256', $_GET['token'], wp_salt() ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_valid          = true;
			$current_token     = $token_data;
			$token_usage_count = isset( $token_data['usage_count'] ) ? absint( $token_data['usage_count'] ) + 1 : 1;

			$tokens[ $i ]['usage_count'] = $token_usage_count;
			if ( 0 !== $token_validity && $token_validity <= $token_usage_count ) {
				unset( $tokens[ $i ] );
			}

			break;
		}
	}

	if ( ! $is_valid ) {
		do_action( 'magic_login_invalid_token' );

		/**
		 * Invalid token error message.
		 * Since 1.2
		 */
		$error_message = apply_filters( 'magic_login_invalid_token_error_message', $error );
		wp_die( wp_kses_post( $error_message ) );
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
	 * @since 1.5.1 array $current_token added
	 */
	do_action( 'magic_login_logged_in', $user, $current_token );

	/**
	 * Some plugins integrated with core's wp_login hook.
	 * So fire it here too.
	 *
	 * @since 1.3
	 */
	do_action( 'wp_login', $user->user_login, $user );

	$default_redirect = get_user_default_redirect( $user );
	$login_redirect   = apply_filters( 'magic_login_redirect', $default_redirect, $user );
	wp_safe_redirect( $login_redirect );
	exit;
}

/**
 * Handle cleanup process for expired tokens
 *
 * @param int $user_id user id
 */
function cleanup_expired_tokens( $user_id ) {
	$settings    = \MagicLogin\Utils\get_settings();
	$ttl         = absint( $settings['token_ttl'] );
	$tokens      = get_user_meta( $user_id, TOKEN_USER_META, true );
	$tokens      = is_string( $tokens ) ? array( $tokens ) : $tokens;
	$live_tokens = array();

	foreach ( $tokens as $token ) {
		if ( empty( $token ) || ! isset( $token['time'] ) ) {
			continue;
		}

		// not expired yet
		if ( absint( $token['time'] ) + ( $ttl * MINUTE_IN_SECONDS ) > time() ) {
			$live_tokens[] = $token;
		}
	}

	update_user_meta( $user_id, TOKEN_USER_META, $live_tokens );
}

/**
 * Add login button to wp-login.php
 */
function print_login_button() {
	$settings = \MagicLogin\Utils\get_settings();

	if ( ! $settings['add_login_button'] ) {
		return;
	}

	$login_url = site_url( 'wp-login.php?action=magic_login', 'login_post' );

	if ( isset( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$login_url = esc_url( add_query_arg( 'redirect_to', $_GET['redirect_to'], $login_url ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	?>
	<script type="text/javascript">
		(function () {
			let loginForm = document.getElementById('loginform');

			if( loginForm ){
				loginForm.insertAdjacentHTML(
					'beforeend',
					'<div class="magic-login-normal-login">' +
					'<button type="submit" name="wp-submit" id="wp-login-submit" class="button button-primary button-hero" value="<?php esc_attr_e( 'Log In' ); ?>"><?php esc_attr_e( 'Log In' ); ?></button>'+
					'</div>'+
					'<span class="magic-login-or-seperator"></span>' +
					'<div id="continue-with-magic-login" class="continue-with-magic-login">' +
					'<button type="button" value="<?php echo esc_url( $login_url ); ?>" class="button button-primary button-hero" id="magic-login-button">' +
					'<?php esc_html_e( 'Send me the login link', 'magic-login' ); ?>' +
					'</button>'+
					'</a>' +
					'</div>'
				);

				document.getElementById('magic-login-button').onclick = function () {
					let loginInput = document.getElementById('user_login');
					if ( loginInput.value.length > 0 ) {
						let frm = document.getElementById('loginform') || null;
						if ( frm ) {
							frm.action = "<?php echo esc_url( $login_url ); ?>";
							frm.submit();
						}
					}else{
						location.href = "<?php echo esc_url( $login_url ); ?>";
					}
				}
			}
		})();
	</script>
	<?php

}

/**
 * Add small tweaks to login form
 */
function login_css() {
	$settings = \MagicLogin\Utils\get_settings();

	if ( ! $settings['add_login_button'] ) {
		return;
	}

	?>
	<style>
		#loginform  #wp-submit {
			display: none;
		}

		.magic-login-normal-login{
			width: 80%;
			margin: auto;
			padding-top:10px;
			display: block;
			text-align: center;
			clear:both;
		}

		.magic-login-normal-login .button,
		#magic-login-button {
			width: 100%;
			float: none!important;
		}

		#magic-login-button {
			padding: unset !important;
		}

		.continue-with-magic-login {
			width: 80%;
			margin: auto;
			display: block;
			text-align: center;
		}

		.continue-with-magic-login .button {
			float: none;
		}

		.magic-login-or-seperator {
			display: block;
			text-align: center;
			position: relative;
			margin: 10px auto;
			width: 80%;
		}

		.magic-login-or-seperator:before {
			content: "<?php esc_html_e( 'or', 'magic-login' ); ?>";
			background-color: #fff;
			font-size: 13px;
			color: #9b9b9b;
			display: inline-block;
			width: 62px;
			position: relative;
			z-index: 1;
		}

		.magic-login-or-seperator:after {
			content: "";
			width: 100%;
			position: absolute;
			left: 0;
			top: 50%;
			height: 1px;
			margin-top: -0.5px;
			background-color: #d8d8d8;
		}

	</style>
	<?php
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
	$settings = \MagicLogin\Utils\get_settings();

	if ( ! $settings['auto_login_links'] ) {
		return $atts;
	}

	$to = $atts['to'];

	if ( empty( $to ) ) {
		return $atts;
	}

	if ( is_array( $to ) && 1 !== count( $to ) ) {
		return $atts;
	}

	$to = is_array( $to ) ? array_shift( $to ) : $to;

	if ( is_string( $to ) && false !== strpos( $to, ',' ) ) {
		return $atts;
	}

	/**
	 * Check bcc/cc
	 * Login links are personal, so we don't want to send them to other people.
	 */
	if ( ! empty( $atts['headers'] ) ) {
		$headers = $atts['headers'];

		if ( is_string( $headers ) ) {
			$headers = [ $headers ];
		}

		foreach ( $headers as $header ) {
			if ( 1 === preg_match( '/(bcc|cc):/i', $header ) ) {
				return $atts;
			}
		}
	}

	$user = get_user_by( 'email', $to );

	if ( ! $user ) {
		return $atts;
	}

	if ( is_auto_login_link_excluded_mail( $atts ) ) {
		return $atts;
	}

	/**
	 * Filter auto login link
	 *
	 * @param bool     $status false to exclude, default true
	 * @param array    $atts   wp_mail args
	 * @param \WP_User $user   user object
	 *
	 * @since 1.6.0
	 */
	$add_login_link = apply_filters( 'magic_login_add_auto_login_link', true, $atts, $user );

	if ( ! $add_login_link ) {
		return $atts;
	}

	$atts['message'] = add_auto_login_link_to_message( $atts, $user );

	return $atts;
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
	$settings                              = \MagicLogin\Utils\get_settings();
	list( $token_ttl, $selected_interval ) = get_ttl_with_interval( $settings['token_ttl'] );
	$selected_interval_str                 = strtolower( $selected_interval );
	$allowed_intervals                     = get_allowed_intervals();
	if ( isset( $allowed_intervals[ $selected_interval ] ) ) {
		$selected_interval_str = strtolower( $allowed_intervals[ $selected_interval ] ); // translated interval
	}

	$message = $args['message'];
	$is_html = ! empty( $args['headers'] ) && false !== strpos( implode( '|', (array) $args['headers'] ), 'text/html' );

	$link = create_login_link( $user );

	if ( $is_html ) {
		$login_message = '<br>';
		/* translators: %s: The magic login link */
		$login_message .= sprintf( __( '<a href="%s" target="_blank" rel="noopener">Click here to login</a>.', 'magic-login' ), $link );
	} else {
		$login_message = PHP_EOL;
		/* translators: %s: The magic login link */
		$login_message .= sprintf( __( 'Auto Login: %s', 'magic-login' ), $link );
	}

	if ( $token_ttl > 0 ) {
		$login_message .= $is_html ? '<br>' : PHP_EOL;

		/* translators: 1: TTL value (number) 2: Unit (minute(s), hour(s), days(s)) */ // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		$login_message .= sprintf( __( 'Login link will expire in %1$s %2$s.', 'magic-login' ), $token_ttl, $selected_interval_str );
	}

	/**
	 * Filter login message
	 *
	 * @param string   $login_message Appended message for the login
	 * @param string   $link          Login URL
	 * @param \WP_User $user          User Object
	 *
	 * @since 1.6
	 */
	$login_message = apply_filters( 'magic_login_auto_login_link_message', $login_message, $link, $user );

	$email_message = $message . $login_message;

	/**
	 * Filter email message
	 *
	 * @param string   $email_message Email message
	 * @param string   $message       Email content before appending login link
	 * @param string   $login_message Login message
	 * @param array    $args          WP Mail args
	 * @param string   $link          Login URL
	 * @param \WP_User $user          User Object
	 *
	 * @since 1.6
	 */
	return apply_filters( 'magic_login_auto_login_link_email_message', $email_message, $message, $login_message, $args, $link, $user );
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
	$is_excluded = false;

	/**
	 * Exclude some of the emails
	 * Copy emails as is, for covering in translated versions
	 *
	 * @link https://github.com/johnbillion/wp_mail
	 */
	$excluded_subjects = apply_filters(
		'magic_login_auto_login_excluded_subjects',
		[
			__( '[%s] New Admin Email Address' ),
			__( '[%s] Network Admin Email Change Request' ),
			__( '[%s] Admin Email Changed' ),
			__( '[%s] Notice of Network Admin Email Change' ),
			__( '[%s] Login Details' ),
			__( '[%s] Password Reset' ),
			__( '[%s] Password Changed' ),
			__( '[%s] Email Change Request' ),
		]
	);

	// remove [%s] from subjects
	$normalize_email_title = preg_replace( '#\[.*?\]#s', ' ', $args['subject'] ); // remove placeholders
	foreach ( $excluded_subjects as $subject ) {
		$subject = preg_replace( '#\[.*?\]#s', ' ', $subject ); // remove placeholders
		if ( false !== strpos( $normalize_email_title, $subject ) ) {
			$is_excluded = true;
			break;
		}
	}

	// no need to add for login email itself
	if ( did_action( 'magic_login_send_login_link' ) ) {
		$is_excluded = true;
	}

	/**
	 * Filter if auto login link is excluded for given mail
	 *
	 * @param bool  $is_excluded whether the email is excluded or not
	 * @param array $args        wp_mail args
	 *
	 * @since 1.6
	 */
	return (bool) apply_filters( 'magic_login_auto_login_link_excluded', $is_excluded, $args );
}
