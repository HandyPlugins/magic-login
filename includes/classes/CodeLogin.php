<?php
/**
 * Code based login functionality.
 *
 * @package MagicLogin
 */

namespace MagicLogin;

use function MagicLogin\Utils\get_user_by_log_input;
use \WP_Error as WP_Error;
use function MagicLogin\Utils\get_wp_login_url;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class CodeLogin
 */
class CodeLogin {
	/**
	 * Return an instance of the current class
	 *
	 * @since
	 */
	public static function setup() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'handle_code_login' ] );
		add_action( 'wp_ajax_magic_login_code_login', [ $this, 'handle_code_login' ] );
		add_action( 'wp_ajax_nopriv_magic_login_code_login', [ $this, 'handle_code_login' ] );
	}


	/**
	 * Handle code login
	 *
	 * @return void|string|WP_Error
	 * @since 2.4
	 */
	public function handle_code_login() {
		global $magic_login_code_login_result;

		if ( ! isset( $_POST['magic_login_token'], $_POST['log'] ) ) {
			return;
		}

		if ( ! isset( $_POST['magic_login_code_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['magic_login_code_form_nonce'] ) ), 'magic-login-code-login' ) ) {
			$nonce_err = new WP_Error( 'invalid_nonce', esc_html__( 'Invalid nonce', 'magic-login' ) );

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( [ 'message' => $nonce_err->get_error_message() ] );
			}

			$magic_login_code_login_result = $nonce_err;

			return $nonce_err;
		}

		/**
		 * Pre process code login request
		 *
		 * @hook  magic_login_pre_process_code_login_request
		 * @return WP_Error|null
		 * @since 2.4
		 */
		$result = apply_filters( 'magic_login_pre_process_code_login_request', null );

		if ( is_wp_error( $result ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( [ 'message' => $result->get_error_message() ] );
			}

			$magic_login_code_login_result = $result;

			return $result;
		}

		$log   = isset( $_POST['log'] ) ? sanitize_user( wp_unslash( $_POST['log'] ) ) : ''; // phpcs:ignore
		$user  = get_user_by_log_input( $log );
		$token = sanitize_text_field( wp_unslash( $_POST['magic_login_token'] ) );

		/**
		 * Pre authenticate code login
		 *
		 * @hook   magic_login_pre_code_login
		 *
		 * @param  {boolean} $do_authenticate Authenticate or not
		 * @param  {object} $user User object
		 * @param  {string} $token Token
		 *
		 * @return {bool|WP_Error}
		 * @since  2.4
		 */
		$do_login = apply_filters( 'magic_login_pre_code_login', true, $user, $token );

		if ( is_wp_error( $do_login ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( [ 'message' => $do_login->get_error_message() ] );
			}

			$magic_login_code_login_result = $do_login;

			return $do_login;
		}

		$magic_login_code_login_result = LoginManager::authenticate_user_token( $user->ID, $token, true );

		if ( ! empty( $magic_login_code_login_result ) && ! is_array( $magic_login_code_login_result ) ) { // check if it's url

			/**
			 * Fires when the code login is invalid
			 *
			 * @hook  magic_login_invalid_code
			 * @since 2.4
			 */
			do_action( 'magic_login_invalid_code' );

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json_error( [ 'message' => $magic_login_code_login_result ] );
			}

			$magic_login_code_login_result = new WP_Error( 'invalid_code', $magic_login_code_login_result );

			return $magic_login_code_login_result;
		}

		/**
		 * Apply login action
		 *
		 * @hook  magic_login_code_login
		 * @since 2.4
		 */
		do_action( 'magic_login_code_login' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$redirect_to = $magic_login_code_login_result['redirect_to'] ?? get_wp_login_url();
			wp_send_json_success(
				[
					'message'     => esc_html__( 'Login successful! You will be redirected shortly.', 'magic-login' ),
					'redirect_to' => $redirect_to,
				]
			);
		}

		wp_safe_redirect( $magic_login_code_login_result['redirect_to'] );
		exit;
	}

	/**
	 * Code based login form
	 *
	 * @return void
	 */
	public static function code_form() {
		$log        = '';
		$cancel_url = home_url( add_query_arg( null, null ) );

		if ( isset( $_POST['log'] ) && is_string( $_POST['log'] ) ) {
			$log = wp_unslash( $_POST['log'] ); // phpcs:ignore
		}
		?>
		<form
			name="magiclogincodeform"
			id="magiclogincodeform"
			class="magic-login-code-form"
			method="post"
			autocomplete="off"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-ajax-spinner="<?php echo esc_url( get_admin_url() . 'images/spinner.gif' ); ?>"
			data-ajax-sending-msg="<?php esc_attr_e( 'Sending...', 'magic-login' ); ?>"
			data-spam-protection-msg="<?php esc_attr_e( 'Please verify that you are not a robot.', 'magic-login' ); ?>"
		>
			<div class="magic-login-code-form-header">
				<div class="info" style="display: none;"></div>
				<div class="ajax-result" style="display: none;"></div>
				<div class="spinner" style="display: none;"></div>
			</div>
			<p>
				<label for="magic_login_token"><?php esc_html_e( 'Code', 'magic-login' ); ?></label>
				<input type="text" name="magic_login_token" id="magic_login_token" class="input" value="" size="20" autocapitalize="off" required />
			</p>
			<?php

			/**
			 * Fires following the 'code' field in the login form.
			 *
			 * @since 2.4
			 */
			do_action( 'magic_login_code_form' );

			?>
			<p class="submit">
				<input type="submit" name="wp-submit" id="wp-submit" style="float: none;width: 100%;" class="magic-login-code-submit button button-primary button-hero" value="<?php esc_attr_e( 'Submit', 'magic-login' ); ?>" />
				<?php if ( ! empty( $cancel_url ) ) : ?>
					<a href="<?php echo esc_url( $cancel_url ); ?>" class="button button-secondary button-hero magic-login-code-cancel" style="float: none;width: 100%;margin-top: 10px;text-align:center;"><?php esc_html_e( 'Cancel', 'magic-login' ); ?></a>
				<?php endif; ?>

				<?php if ( isset( $_POST['redirect_to'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $_POST['redirect_to'] ); // phpcs:ignore ?>">
				<?php endif; ?>
				<input type="hidden" name="log" value="<?php echo esc_attr( $log ); ?>" />
				<input type="hidden" name="testcookie" value="1" />
				<?php wp_nonce_field( 'magic-login-code-login', 'magic_login_code_form_nonce' ); ?>
			</p>
		</form>
		<?php
	}

}
