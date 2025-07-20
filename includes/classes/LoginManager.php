<?php
/**
 * Login functionality
 *
 * @package MagicLogin
 */

namespace MagicLogin;

use function MagicLogin\Utils\get_email_placeholders_by_user;
use function MagicLogin\Utils\get_token_validity_by_user;
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

/**
 * Login Manager
 */
class LoginManager {
	/**
	 * Singleton instance
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Return an instance of the current class
	 */
	public static function setup() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'login_form_magic_login', [ self::class, 'wp_login_action' ] );
		add_action( 'login_form_login', [ self::class, 'maybe_redirect' ] );
		add_action( 'init', [ self::class, 'handle_login_request' ], 1 );
		add_action( CRON_HOOK_NAME, [ self::class, 'clear_expired_tokens' ] );
		add_action( 'login_footer', [ self::class, 'print_login_button' ] );
		add_action( 'login_head', [ self::class, 'login_css' ] );
		add_action( 'wp_ajax_magic_login_ajax_request', [ self::class, 'ajax_request' ] );
		add_action( 'wp_ajax_nopriv_magic_login_ajax_request', [ self::class, 'ajax_request' ] );
		add_filter( 'wp_mail', [ self::class, 'maybe_add_auto_login_link' ], 999 );
		add_filter( 'wp_mail', [ self::class, 'replace_magic_link_in_wp_mail' ], 999 );
	}

	/**
	 * Replace {{MAGIC_LINK}} placeholder with login link for all outgoing emails
	 *
	 * @param array $atts wp_mail args
	 *
	 * @return mixed
	 * @since 2.0.0
	 */
	public static function replace_magic_link_in_wp_mail( $atts ) {
		if ( ! is_array( $atts ) || empty( $atts['message'] ) ) {
			return $atts;
		}

		// replace encoded placeholder
		$atts['message'] = str_replace( '%7B%7BMAGIC_LINK%7D%7D', '{{MAGIC_LINK}}', $atts['message'] );

		if ( false === strpos( $atts['message'], '{{MAGIC_LINK}}' ) ) {
			return $atts;
		}

		$magic_link = '';

		if ( self::is_single_recipient( $atts ) ) {
			$to   = $atts['to'];
			$to   = is_array( $to ) ? array_shift( $to ) : $to;
			$user = get_user_by( 'email', $to );

			if ( $user ) {

				/**
				 * Filter magic_login_replace_magic_link_in_wp_mail
				 *
				 * @param bool     $status false to exclude, default true
				 * @param array    $atts   wp_mail args
				 * @param \WP_User $user   user object
				 *
				 * @since 2.0.0
				 */
				$replace_magic_link = apply_filters( 'magic_login_replace_magic_link_in_wp_mail', true, $atts, $user );
				if ( $replace_magic_link ) {
					$magic_link = create_login_link( $user );
				}
			}
		}

		/**
		 * Filter magic login replace
		 *
		 * @param string $magic_link login link
		 * @param array  $atts       wp_mail args
		 *
		 * @since 2.0.0
		 */
		$magic_link      = apply_filters( 'magic_login_replace_magic_link_in_wp_mail_message', $magic_link, $atts );
		$atts['message'] = str_replace( '{{MAGIC_LINK}}', $magic_link, $atts['message'] );

		return $atts;
	}


	/**
	 * Ajax callback for login requests
	 *
	 * @return void
	 */
	public static function ajax_request() {

		if ( ! isset( $_POST['data'] ) ) {
			wp_send_json_error(
				[
					'message'                => esc_html__( 'Invalid request', 'magic-login' ),
					'show_form'              => true,
					'show_registration_form' => false,
				]
			);
		}

		parse_str( wp_unslash( $_POST['data'] ), $form_data ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$global_data = [ 'log', 'redirect_to', 'g-recaptcha-response', 'cf-turnstile-response' ];

		// populate super global $_POST with form data
		foreach ( $global_data as $key ) {
			if ( isset( $form_data[ $key ] ) ) {
				$_POST[ $key ] = $form_data[ $key ];
			}
		}

		$args = []; // pass custom messages to backend
		if ( ! empty( $form_data['messages'] ) ) {
			foreach ( $form_data['messages'] as $key => $value ) {
				$args[ $key . '_message' ] = wp_kses_post( $value );
			}
		}

		$login_request  = self::process_login_request( $args );
		$settings       = \MagicLogin\Utils\get_settings();
		$error_messages = '';
		$login_errors   = $login_request['errors'];

		if ( $login_request['show_registration_form'] ) {
			$email = isset( $_POST['log'] ) && is_email( wp_unslash( $_POST['log'] ) ) ? sanitize_email( wp_unslash( $_POST['log'] ) ) : '';
			if ( 'auto' === $settings['registration']['mode'] && $settings['registration']['fallback_email_field'] ) {
				$shortcode = sprintf( '[magic_login_registration_form show_name="false" show_terms="false" email="%s"]', $email );
			} else {
				$shortcode = sprintf( '[magic_login_registration_form email="%s"]', $email );
			}

			$registration_form = do_shortcode( $shortcode );

			wp_send_json_success(
				[
					'message'           => $login_request['info'],
					'show_form'         => $login_request['show_form'],
					'code_login'        => $login_request['code_login'],
					'phone_login'       => $login_request['phone_login'],
					'registration_form' => $registration_form,
				]
			);
		}

		// error messages
		if ( ! empty( $login_errors ) && is_wp_error( $login_errors ) && $login_errors->has_errors() ) {
			foreach ( $login_errors->get_error_codes() as $code ) {
				foreach ( $login_errors->get_error_messages( $code ) as $message ) {
					$error_messages .= $message . "<br />\n";
				}
			}
		}

		if ( ! empty( $error_messages ) ) {
			$error_message = sprintf( '<div id="login_error" class="magic_login_block_login_error">%s</div>', wp_kses_post( $error_messages ) );

			wp_send_json_error(
				[
					'message'     => $error_message,
					'show_form'   => $login_request['show_form'],
					'code_login'  => $login_request['code_login'],
					'phone_login' => $login_request['phone_login'],
				]
			);
		}

		if ( $login_request['code_login'] ) {
			ob_start();
			CodeLogin::code_form();
			$code_login_form = ob_get_clean();
			wp_send_json_success(
				[
					'info'        => $login_request['info'],
					'errors'      => $login_request['errors'],
					'message'     => $login_request['info'],
					'show_form'   => $login_request['show_form'],
					'code_login'  => $login_request['code_login'],
					'phone_login' => $login_request['phone_login'],
					'code_form'   => $code_login_form,
				]
			);
		}

		if ( ! empty( $login_request['info'] ) ) {
			wp_send_json_success(
				[
					'code_login'  => $login_request['code_login'],
					'phone_login' => $login_request['phone_login'],
					'message'     => $login_request['info'],
					'show_form'   => $login_request['show_form'],
				]
			);
		}
	}


	/**
	 * Maybe add login link to outgoing email
	 *
	 * @param array $atts wp_mail args
	 *
	 * @return mixed
	 * @since 1.6
	 */
	public static function maybe_add_auto_login_link( $atts ) {
		$settings = \MagicLogin\Utils\get_settings();

		if ( ! $settings['auto_login_links'] ) {
			return $atts;
		}

		$to = $atts['to'];

		if ( ! self::is_single_recipient( $atts ) ) {
			return $atts;
		}

		$to   = is_array( $to ) ? array_shift( $to ) : $to;
		$user = get_user_by( 'email', $to );

		if ( ! $user ) {
			return $atts;
		}

		if ( self::is_auto_login_link_excluded_mail( $atts ) ) {
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

		$atts['message'] = self::add_auto_login_link_to_message( $atts, $user );

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
	public static function add_auto_login_link_to_message( $args, $user ) {
		$ttl                                   = get_ttl_by_user( $user->ID );
		list( $token_ttl, $selected_interval ) = get_ttl_with_interval( $ttl );
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
	public static function is_auto_login_link_excluded_mail( $args ) {
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
				__( 'Your login confirmation code' ),
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


	/**
	 * Check if email has single recipient
	 *
	 * @param array $atts wp_mail args
	 *
	 * @return bool
	 * @since 2.0.0
	 * @since 2.4 Previously named `has_single_recipient`
	 */
	public static function is_single_recipient( $atts ) {
		$to = $atts['to'];

		if ( empty( $to ) ) {
			return false;
		}

		if ( is_array( $to ) && 1 !== count( $to ) ) {
			return false;
		}

		$to = is_array( $to ) ? array_shift( $to ) : $to;

		if ( is_string( $to ) && false !== strpos( $to, ',' ) ) {
			return false;
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
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * Add small tweaks to login form
	 */
	public static function login_css() {
		$settings = \MagicLogin\Utils\get_settings();

		if ( ! $settings['add_login_button'] ) {
			return;
		}

		?>
		<style>

			form[name="validate_2fa_form"] #magic-login-button,
			form[name="validate_2fa_form"] .magic-login-or-separator {
				display: none;
			}

			form[name="validate_2fa_form"] .submit {
				display: none;
			}

			.two-factor-email-resend input[type="submit"] {
				width: 100%;
				margin: auto;
				display: block;
				text-align: center;
				padding: 0 36px;
				min-height: 46px;
			}


			#loginform #wp-submit {
				display: none;
			}

			.magic-login-normal-login {
				width: 100%;
				margin: auto;
				padding-top: 10px;
				display: block;
				text-align: center;
				clear: both;
			}

			.magic-login-normal-login .button,
			#magic-login-button {
				width: 100%;
				float: none !important;
			}

			#magic-login-button {
				padding: unset !important;
			}

			.continue-with-magic-login {
				width: 100%;
				margin: auto;
				display: block;
				text-align: center;
			}

			.continue-with-magic-login .button {
				float: none;
			}

			.magic-login-or-separator {
				display: block;
				text-align: center;
				position: relative;
				margin: 10px auto;
				width: 100%;
			}

			.magic-login-or-separator:before {
				content: "<?php esc_html_e( 'or', 'magic-login' ); ?>";
				background-color: #fff;
				font-size: 13px;
				color: #9b9b9b;
				display: inline-block;
				width: 62px;
				position: relative;
				z-index: 1;
			}

			.magic-login-or-separator:after {
				content: "";
				width: 100%;
				position: absolute;
				left: 0;
				top: 50%;
				height: 1px;
				margin-top: -0.5px;
				background-color: #d8d8d8;
			}

			.magic-login-captcha-wrapper {
				text-align: center;
				margin: 10px 0;
				width: 100%;
			}

			/* Target reCAPTCHA v2 checkbox (not invisible) */
			.magic-login-captcha-wrapper .g-recaptcha:not([data-size="invisible"]) {
				transform: scale(0.90);
				transform-origin: 0 0;
				display: inline-block;
			}

			/* No scaling for invisible reCAPTCHA */
			.magic-login-captcha-wrapper .g-recaptcha[data-size="invisible"] {
				transform: none;
				display: inline;
			}

			.magic-login-captcha-wrapper iframe,
			.magic-login-captcha-wrapper .grecaptcha-badge {
				width: 100% !important;
			}

			.magic-login-captcha-error {
				color: #d63638;
			}

			#cf-turnstile-container {
				width: 100%;
				transform: scale(0.9);
				transform-origin: top left;
			}

		</style>
		<?php
	}


	/**
	 * Add login button to wp-login.php
	 */
	public static function print_login_button() {
		$settings = \MagicLogin\Utils\get_settings();

		if ( ! $settings['add_login_button'] ) {
			return;
		}

		$login_url = get_wp_login_url();

		if ( isset( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$login_url = esc_url_raw( add_query_arg( 'redirect_to', urlencode( $_GET['redirect_to'] ), $login_url ) ); // phpcs:ignore
		}

		?>
		<script type="text/javascript">
			(function () {
				let loginForm = document.getElementById('loginform');

				if (loginForm) {
					loginForm.insertAdjacentHTML(
						'beforeend',
						'<div class="magic-login-normal-login">' +
						'<button type="submit" name="wp-submit" id="wp-login-submit" class="button button-primary button-hero magic-login-submit" value="<?php esc_attr_e( 'Log In', 'magic-login' ); // phpcs:ignore ?>"><?php esc_attr_e( 'Log In', 'magic-login' ); ?></button>' +
						'</div>' +
						'<span class="magic-login-or-separator"></span>' +
						'<div id="continue-with-magic-login" class="continue-with-magic-login">' +
						'<button type="button" value="<?php echo esc_url( $login_url ); ?>" class="button button-primary button-hero" id="magic-login-button">' +
						'<?php esc_html_e( 'Send me the login link', 'magic-login' ); ?>' +
						'</button>' +
						'</div>'
					);

					document.getElementById('magic-login-button').onclick = function () {
						let loginInput = document.getElementById('user_login');
						if (loginInput != null && loginInput.value.length > 0) {
							let frm = document.getElementById('loginform') || null;
							if (frm) {
								frm.action = "<?php echo esc_url_raw( $login_url ); ?>";
								frm.submit();
							}
						} else {
							location.href = "<?php echo esc_url_raw( $login_url ); ?>";
						}
					}
				}
			})();
		</script>
		<?php

	}


	/**
	 * Handle cleanup process for expired tokens
	 *
	 * @param int $user_id user id
	 * @since 2.4 Previously named cleanup_expired_tokens
	 */
	public static function clear_expired_tokens( $user_id ) {
		$ttl         = get_ttl_by_user( $user_id );
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
	 * Handle login request
	 */
	public static function handle_login_request() {
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

		nocache_headers();
		do_action( 'magic_login_handle_login_request' );
		// Use a generic error message to ensure user ids can't be sniffed
		$user_id = (int) $_GET['user_id']; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token   = $_GET['token']; //phpcs:ignore
		self::authenticate_user_token( $user_id, $token );
	}

	/**
	 * Authenticate the user with the token.
	 *
	 * @param int    $user_id    User ID.
	 * @param string $token      Login token.
	 * @param bool   $code_login Code login or not.
	 *
	 * @return void|string Returns error message or redirect info if $code_login is true.
	 * @since 2.4
	 */
	public static function authenticate_user_token( $user_id, $token, $code_login = false ) {
		// Load settings once.
		$settings = \MagicLogin\Utils\get_settings();

		// Prepare the default error message.
		if ( is_user_logged_in() ) {
			/* translators: 1: User login 2: Dashboard URL */
			$error = sprintf(
				__( 'Invalid magic login token, but you are logged in as \'%1$s\'. <a href="%2$s">Go to the dashboard instead</a>?', 'magic-login' ),
				wp_get_current_user()->user_login,
				admin_url()
			);
		} else {
			/* translators: %s: Login URL */
			$error = sprintf(
				__( 'Invalid magic login token. <a href="%s">Try signing in instead</a>?', 'magic-login' ),
				wp_login_url()
			);
			if ( $settings['is_default'] ) {
				/* translators: %s: Magic Login URL */
				$login_url = esc_url( add_query_arg( 'action', 'magic_login', wp_login_url() ) );
				$error     = sprintf(
					__( 'Invalid magic login token. Please try to create <a href="%s">a new login link</a>?', 'magic-login' ),
					$login_url
				);
			}
		}

		// Override error message if we're in code login mode.
		if ( $code_login ) {
			$error = esc_html__( 'Invalid login code.', 'magic-login' );
		}

		// Early return if the user does not exist.
		$user = get_user_by( 'id', $user_id ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $user ) {
			do_action( 'magic_login_invalid_user' );
			$error = apply_filters( 'magic_login_error_message', $error, 'invalid_user' );
			if ( $code_login ) {
				return wp_kses_post( $error );
			}
			wp_die( wp_kses_post( $error ) );
		}

		// Validate token.
		$token_validity = get_token_validity_by_user( $user->ID );
		$tokens         = get_user_tokens( $user->ID, true );
		$is_valid       = false;
		$current_token  = null;

		foreach ( $tokens as $i => $token_data ) {
			if ( empty( $token_data ) || ! is_array( $token_data ) || ! isset( $token_data['token'] ) ) {
				unset( $tokens[ $i ] );
				continue;
			}

			// Verify the token using a secure comparison.
			if ( hash_equals( $token_data['token'], hash_hmac( 'sha256', $token, wp_salt() ) ) ) { // phpcs:ignore
				$is_valid                    = true;
				$current_token               = $token_data;
				$token_usage_count           = isset( $token_data['usage_count'] ) ? absint( $token_data['usage_count'] ) + 1 : 1;
				$tokens[ $i ]['usage_count'] = $token_usage_count;

				// Remove token if usage exceeds validity.
				if ( 0 !== $token_validity && $token_validity <= $token_usage_count ) {
					unset( $tokens[ $i ] );
				}
				break;
			}
		}

		if ( ! $is_valid ) {
			do_action( 'magic_login_invalid_token' );
			$error = apply_filters( 'magic_login_invalid_token_error_message', $error );
			$error = apply_filters( 'magic_login_error_message', $error, 'invalid_token' );
			if ( $code_login ) {
				return $error;
			}
			wp_die( wp_kses_post( $error ) );
		}

		// Proceed with login if token is valid.
		if ( headers_sent() ) {
			error_log( 'Magic Login: Headers already sent. Cannot set auth cookie.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}

		// Trigger actions before login.
		do_action( 'magic_login_before_login', $user, $current_token );

		// Update token metadata.
		update_user_meta( $user->ID, TOKEN_USER_META, $tokens );

		// Set authentication cookie.
		wp_set_auth_cookie( $user->ID, true, is_ssl() );

		// Trigger post-login actions.
		do_action( 'magic_login_logged_in', $user, $current_token );
		do_action( 'wp_login', $user->user_login, $user );

		// Determine redirect URL.
		$redirect_to           = get_user_default_redirect( $user );
		$requested_redirect_to = isset( $_REQUEST['redirect_to'] ) && is_string( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : ''; // phpcs:ignore

		$redirect_to    = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $user );
		$login_redirect = apply_filters( 'magic_login_redirect', $redirect_to, $user );

		// Return early if in code login mode.
		if ( $code_login ) {
			return [ 'redirect_to' => $login_redirect ];
		}

		// Perform final redirection.
		wp_safe_redirect( $login_redirect );
		exit;
	}


	/**
	 * Login form actions
	 */
	public static function wp_login_action() {
		$login_request = self::process_login_request();

		login_header( esc_html__( 'Log in', 'magic-login' ), $login_request['info'], $login_request['errors'] );

		if ( $login_request['code_login'] ) {
			CodeLogin::code_form(); // phpcs:ignore
		} elseif ( $login_request['show_form'] ) {
			self::login_form();
		}

		login_footer();
		exit;
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
	public static function process_login_request( $args = [] ) {
		$defaults = [
			'info_message' => '',
		];
		$args     = wp_parse_args( $args, $defaults );

		$response = [
			'show_form'              => true,
			'errors'                 => new WP_Error(),
			'info'                   => '',
			'is_processed'           => false,
			'show_registration_form' => false,
			'code_login'             => false,
			'phone_login'            => false,
		];

		// Default info message
		$response['info'] = defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) && MAGIC_LOGIN_USERNAME_ONLY
			? '<p class="message">' . __( 'Please enter your username. You will receive an email message to log in.', 'magic-login' ) . '</p>'
			: '<p class="message">' . __( 'Please enter your username or email address. You will receive an email message to log in.', 'magic-login' ) . '</p>';

		// Allow customization of info message
		$response['info'] = apply_filters( 'magic_login_info_message', $response['info'], $args );

		// Override info message if provided
		if ( ! empty( $args['info_message'] ) ) {
			$response['info'] = '<p class="message">' . esc_html( $args['info_message'] ) . '</p>';
		}

		// Handle form submission
		if ( self::is_login_form_submission() ) {
			$response = self::process_login_form_submission( $args );
		}

		// Handle magic link login request
		if ( ! empty( $_GET['magic-registration'] ) ) { // phpcs:ignore
			$response['show_registration_form'] = true;
		}

		/**
		 * Filter the result of the login process
		 *
		 * @hook  magic_login_process_login_request_result
		 * @param array $response Result of the login process
		 * @param array $args     Arguments passed to the function
		 * @since 2.4
		 */
		return apply_filters( 'magic_login_process_login_request_result', $response, $args );
	}

	/**
	 * login form
	 */
	public static function login_form() {
		$user_login = '';

		if ( isset( $_POST['log'] ) && is_string( $_POST['log'] ) ) {
			$user_login = wp_unslash( $_POST['log'] ); // phpcs:ignore
		}

		?>
		<form
			name="magicloginform"
			id="magicloginform"
			action="<?php echo esc_url( get_wp_login_url() ); ?>"
			method="post"
			autocomplete="off"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-ajax-spinner="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>"
			data-ajax-sending-msg="<?php esc_attr_e( 'Sending...', 'magic-login' ); ?>"
			data-spam-protection-msg="<?php esc_attr_e( 'Please verify that you are not a robot.', 'magic-login' ); ?>"
		>
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
				<?php if ( isset( $_GET['redirect_to'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $_GET['redirect_to'] ); // phpcs:ignore ?>">
				<?php endif; ?>
				<input type="hidden" name="testcookie" value="1" />
			</p>
		</form>
		<?php
	}


	/**
	 * Redirect to magic login page once it used as default login method
	 */
	public static function maybe_redirect() {
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
	 * Handle login submission POST request
	 *
	 * @param array $args Arguments
	 *
	 * @return array
	 * @since 2.2
	 */
	public static function process_login_form_submission( $args ) {
		$show_form              = true;
		$show_registration_form = false;
		$code_login             = false;
		$user_name              = isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : '';
		$user                   = get_user_by_log_input( $user_name );
		$is_processed           = true;
		$phone_login            = false;
		$info                   = '';

		$settings = \MagicLogin\Utils\get_settings();

		if ( false !== strpos( $settings['login_email'], '{{MAGIC_LOGIN_CODE}}' ) ) {
			$code_login = true;
		}

		// Apply pre-process filter
		$result = apply_filters( 'magic_login_pre_process_login_request', null );

		if ( is_wp_error( $result ) ) {
			return [
				'is_processed'           => $is_processed,
				'errors'                 => $result,
				'info'                   => $info,
				'show_form'              => $show_form,
				'show_registration_form' => $show_registration_form,
				'code_login'             => $code_login,
				'phone_login'            => $phone_login,
			];
		}

		// Apply pre-send link filter
		$send_link = apply_filters( 'magic_login_pre_send_login_link', null, $user );
		$errors    = null;

		if ( ! is_a( $user, '\WP_User' ) ) {
			$error_code = 'missing_user';

			if ( defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) && MAGIC_LOGIN_USERNAME_ONLY ) {
				$error_message = esc_html__( 'There is no account with that username.', 'magic-login' );
			} else {
				$error_message = esc_html__( 'There is no account with that username or email address.', 'magic-login' );
			}

			if ( $code_login && $phone_login ) {
				$error_message = esc_html__( 'Unable to process your request. Please try again.', 'magic-login' );
			}

			if ( ! empty( $args['error_message'] ) ) {
				$error_message = $args['error_message'];
			}

			/**
			 * Filter the error message when user is missing
			 *
			 * @hook  magic_login_missing_user_error_message
			 *       $error_message string
			 *       $error_code string
			 *       $args array
			 * @since 2.4
			 */
			$error_message = apply_filters( 'magic_login_missing_user_error_message', $error_message, $error_code, $args );

			$errors      = new WP_Error( $error_code, $error_message );
			$show_form   = true;
			$code_login  = false;
			$phone_login = false;
		} elseif ( null !== $send_link ) {
			$errors      = $send_link;
			$show_form   = false;
			$code_login  = false;
			$phone_login = false;
		} else {
			global $magic_login_code_login_result, $magic_login_link;
			if ( ! empty( $magic_login_code_login_result ) ) {
				// after post request, if code login failed, show error message
				$errors = $magic_login_code_login_result;
			} else {
				$errors = self::send_login_link( $user, false, $code_login );
			}
		}

		if ( ! is_wp_error( $errors ) ) {
			$show_form = false;
			$info      = '<p class="message magic_login_block_login_success">' . __( 'Please check your inbox for the login link. If you did not receive a login email, check your spam folder too.', 'magic-login' ) . '</p>';

			if ( $phone_login ) {
				$info = '<p class="message magic_login_block_login_success">' . __( 'Please check your phone for the login link.', 'magic-login' ) . '</p>';
			}

			if ( ! empty( $args['success_message'] ) ) {
				$info = '<p class="message magic_login_block_login_success">' . $args['success_message'] . '</p>';
			}

			if ( $code_login ) {
				$info = '<p class="message magic_login_block_login_success">' . __( 'Please enter the code sent to your email.', 'magic-login' ) . '</p>';

				if ( ! empty( $args['code_success_message'] ) ) {
					$info = '<p class="message magic_login_block_login_success">' . $args['code_success_message'] . '</p>';
				}
			}
		}

		return [
			'is_processed'           => $is_processed,
			'errors'                 => $errors,
			'info'                   => $info,
			'show_form'              => $show_form,
			'show_registration_form' => $show_registration_form,
			'code_login'             => $code_login,
			'phone_login'            => $phone_login,
		];
	}


	/**
	 * Send magic link to user
	 *
	 * @param \WP_User          $user       User  object
	 * @param mixed|string|bool $login_link use given link when it provided. @since 1.9
	 * @param mixed|string|bool $code_login create login link with code. @since 2.4
	 *
	 * @return bool
	 */
	public static function send_login_link( $user, $login_link = false, $code_login = false ) {
		if ( ! $login_link ) {
			$context    = $code_login ? 'email_code' : 'email';
			$login_link = create_login_link( $user, $context );
		}

		$settings      = \MagicLogin\Utils\get_settings();
		$login_email   = $settings['login_email'];
		$email_subject = $settings['email_subject'];

		$placeholder_values = get_email_placeholders_by_user( $user, $login_link );

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
	 * Determines if the current request is a login form submission.
	 *
	 * @return bool
	 * @since 2.4
	 */
	private static function is_login_form_submission() {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['log'] );
	}


}
