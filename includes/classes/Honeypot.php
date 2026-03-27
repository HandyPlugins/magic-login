<?php
/**
 * Native honeypot functionality.
 *
 * @package MagicLogin
 */

namespace MagicLogin;

use function MagicLogin\Utils\get_settings;
use function MagicLogin\Utils\get_user_by_log_input;
use const MagicLogin\Constants\HONEYPOT_BAIT_FIELD_PREFIX;
use const MagicLogin\Constants\HONEYPOT_PAYLOAD_FIELD;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Honeypot
 */
class Honeypot {
	/**
	 * Login request context.
	 */
	private const CONTEXT_LOGIN_REQUEST = 'login_request';

	/**
	 * Code login context.
	 */
	private const CONTEXT_CODE_LOGIN = 'code_login';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Boot the honeypot layer.
	 *
	 * @return self
	 */
	public static function setup() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check whether the honeypot feature is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$settings = get_settings();

		return ! empty( $settings['spam_protection']['enable_honeypot'] );
	}

	/**
	 * Whether login requests should mask account existence.
	 *
	 * @return bool
	 */
	public static function should_mask_login_request() {
		return self::is_enabled();
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'magic_login_form', [ $this, 'render_login_fields' ] );
		add_action( 'magic_login_code_form', [ $this, 'render_code_fields' ] );
		add_action( 'login_footer', [ $this, 'inject_wp_login_form_fields' ] );
		add_filter( 'magic_login_pre_process_login_request', [ $this, 'capture_login_request_decision' ], 1 );
		add_filter( 'magic_login_pre_process_code_login_request', [ $this, 'verify_code_login_request' ], 1 );
		add_filter( 'magic_login_process_login_request_result', [ $this, 'maybe_convert_silent_success_response' ], 10, 2 );
	}

	/**
	 * Render honeypot fields for the login request form.
	 *
	 * @return void
	 */
	public function render_login_fields() {
		echo $this->get_fields_html( self::CONTEXT_LOGIN_REQUEST ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render honeypot fields for the code login form.
	 *
	 * @return void
	 */
	public function render_code_fields() {
		echo $this->get_fields_html( self::CONTEXT_CODE_LOGIN ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Inject honeypot fields into the default wp-login form so the "Send me the login link"
	 * shortcut keeps working without false positives.
	 *
	 * @return void
	 */
	public function inject_wp_login_form_fields() {
		if ( ! self::is_enabled() ) {
			return;
		}

		$settings = get_settings();
		if ( empty( $settings['add_login_button'] ) ) {
			return;
		}

		$fields_html = wp_json_encode( $this->get_fields_html( self::CONTEXT_LOGIN_REQUEST ) );
		?>
		<script type="text/javascript">
			(function () {
				var loginForm = document.getElementById('loginform');
				if (!loginForm || loginForm.querySelector('input[name="<?php echo esc_js( HONEYPOT_PAYLOAD_FIELD ); ?>"]')) {
					return;
				}

				loginForm.insertAdjacentHTML('beforeend', <?php echo $fields_html; ?>);
			})();
		</script>
		<?php
	}

	/**
	 * Capture the decision for the login request flow.
	 *
	 * @param mixed $result Existing filter result.
	 *
	 * @return mixed
	 */
	public function capture_login_request_decision( $result ) {
		if ( ! is_null( $result ) || ! self::is_enabled() ) {
			return $result;
		}

		$decision = $this->validate_context( self::CONTEXT_LOGIN_REQUEST );

		if ( 'silent_success' === $decision['type'] ) {
			return new \WP_Error( 'magic_login_honeypot_silent_success', $this->get_generic_success_message() );
		}

		$identifier = $this->normalize_login_identifier();
		if ( self::should_mask_login_request() && ! is_a( get_user_by_log_input( $identifier ), '\WP_User' ) ) {
			return new \WP_Error( 'magic_login_honeypot_silent_success', $this->get_generic_success_message() );
		}

		return $result;
	}

	/**
	 * Verify the code login request.
	 *
	 * @param mixed $result Existing filter result.
	 *
	 * @return mixed
	 */
	public function verify_code_login_request( $result ) {
		if ( ! is_null( $result ) || ! self::is_enabled() ) {
			return $result;
		}

		$decision = $this->validate_context( self::CONTEXT_CODE_LOGIN );

		if ( 'allow' === $decision['type'] ) {
			return $result;
		}

		return new \WP_Error( 'magic_login_honeypot_code_login', $decision['message'] );
	}

	/**
	 * Convert silent-success honeypot responses into the normal success payload.
	 *
	 * @param array $response Existing login response.
	 * @param array $args     Request args.
	 *
	 * @return array
	 */
	public function maybe_convert_silent_success_response( $response, $args ) {
		if ( empty( $response['errors'] ) || ! is_wp_error( $response['errors'] ) ) {
			return $response;
		}

		if ( ! in_array( 'magic_login_honeypot_silent_success', $response['errors']->get_error_codes(), true ) ) {
			return $response;
		}

		$response['errors']                 = new \WP_Error();
		$response['info']                   = '<p class="message magic_login_block_login_success">' . esc_html( $this->get_generic_success_message() ) . '</p>';
		$response['show_form']              = false;
		$response['show_registration_form'] = false;
		$response['code_login']             = false;
		$response['phone_login']            = false;

		return $response;
	}

	/**
	 * Get the rendered honeypot fields HTML.
	 *
	 * @param string $context Honeypot context.
	 *
	 * @return string
	 */
	private function get_fields_html( $context ) {
		if ( ! self::is_enabled() ) {
			return '';
		}

		$rendered_at = time();
		$signature   = $this->get_signature( $context, $rendered_at );
		$payload     = $this->get_payload_value( $rendered_at, $signature );
		$field_name  = $this->get_field_name( $signature );

		ob_start();
		?>
		<div class="magic-login-form-state" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
			<label for="<?php echo esc_attr( $field_name ); ?>"><?php esc_html_e( 'Leave this field empty', 'magic-login' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="" tabindex="-1" autocomplete="off">
		</div>
		<input type="hidden" name="<?php echo esc_attr( HONEYPOT_PAYLOAD_FIELD ); ?>" value="<?php echo esc_attr( $payload ); ?>">
		<?php

		return (string) ob_get_clean();
	}

	/**
	 * Validate the honeypot field and timing for a context.
	 *
	 * @param string $context Honeypot context.
	 *
	 * @return array
	 */
	private function validate_context( $context ) {
		if ( self::CONTEXT_LOGIN_REQUEST === $context && $this->is_standard_wp_login_handoff() ) {
			return $this->allow_decision();
		}

		$payload     = isset( $_POST[ HONEYPOT_PAYLOAD_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ HONEYPOT_PAYLOAD_FIELD ] ) ) : '';
		$payload     = $this->parse_payload_value( $payload );
		$rendered_at = $payload['rendered_at'];
		$signature   = $payload['signature'];

		if ( empty( $rendered_at ) || empty( $signature ) || ! hash_equals( $this->get_signature( $context, $rendered_at ), $signature ) ) {
			return $this->invalid_decision( $context );
		}

		$field_name = $this->get_field_name( $signature );
		if ( ! isset( $_POST[ $field_name ] ) ) {
			return $this->invalid_decision( $context );
		}

		$field_value = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
		if ( '' !== trim( $field_value ) ) {
			return $this->invalid_decision( $context );
		}

		$config = $this->get_config();
		$age    = time() - $rendered_at;

		if ( $this->should_validate_timing( $context ) && $age < absint( $config['min_render_age'] ) ) {
			return $this->invalid_decision( $context );
		}

		if ( $this->should_validate_timing( $context ) && ! empty( $config['max_render_age'] ) && $age > absint( $config['max_render_age'] ) ) {
			return $this->invalid_decision( $context );
		}

		return $this->allow_decision();
	}

	/**
	 * Detect the standard wp-login handoff flow where the normal login form is submitted
	 * directly to Magic Login.
	 *
	 * @return bool
	 */
	private function is_standard_wp_login_handoff() {
		return isset( $_POST['pwd'] ) && isset( $_POST['log'] ) && ! isset( $_POST[ HONEYPOT_PAYLOAD_FIELD ] );
	}

	/**
	 * Whether timing validation should run for the current request.
	 *
	 * The standard wp-login.php shortcut submits the existing core login form
	 * after the user clicks the Magic Login button, so it can legitimately be
	 * much faster than the standalone Magic Login forms.
	 *
	 * @param string $context Honeypot context.
	 *
	 * @return bool
	 */
	private function should_validate_timing( $context ) {
		if ( self::CONTEXT_LOGIN_REQUEST !== $context ) {
			return true;
		}

		return ! isset( $_POST['pwd'] );
	}

	/**
	 * Build an invalid submission decision for a context.
	 *
	 * @param string $context Honeypot context.
	 *
	 * @return array
	 */
	private function invalid_decision( $context ) {
		if ( self::CONTEXT_LOGIN_REQUEST === $context ) {
			return [
				'type'    => 'silent_success',
				'message' => $this->get_generic_success_message(),
			];
		}

		return [
			'type'    => 'error',
			'message' => esc_html__( 'Invalid login code.', 'magic-login' ),
		];
	}

	/**
	 * Default allow decision.
	 *
	 * @return array
	 */
	private function allow_decision() {
		return [
			'type'    => 'allow',
			'message' => '',
		];
	}

	/**
	 * Get the HMAC signature for a rendered form.
	 *
	 * @param string $context     Honeypot context.
	 * @param int    $rendered_at Render timestamp.
	 *
	 * @return string
	 */
	private function get_signature( $context, $rendered_at ) {
		return hash_hmac( 'sha256', $context . '|' . absint( $rendered_at ), wp_salt( 'nonce' ) );
	}

	/**
	 * Get the dynamic field name for a signature.
	 *
	 * @param string $signature Signed form token.
	 *
	 * @return string
	 */
	private function get_field_name( $signature ) {
		return HONEYPOT_BAIT_FIELD_PREFIX . substr( sanitize_key( $signature ), 0, 12 );
	}

	/**
	 * Build the compact payload value.
	 *
	 * @param int    $rendered_at Render timestamp.
	 * @param string $signature   Signed form token.
	 *
	 * @return string
	 */
	private function get_payload_value( $rendered_at, $signature ) {
		return absint( $rendered_at ) . '.' . $signature;
	}

	/**
	 * Parse the compact payload value.
	 *
	 * @param string $payload Payload string from the form.
	 *
	 * @return array
	 */
	private function parse_payload_value( $payload ) {
		$parts = explode( '.', $payload, 2 );

		if ( 2 !== count( $parts ) ) {
			return [
				'rendered_at' => 0,
				'signature'   => '',
			];
		}

		return [
			'rendered_at' => absint( $parts[0] ),
			'signature'   => sanitize_key( $parts[1] ),
		];
	}

	/**
	 * Normalize the current login identifier.
	 *
	 * @return string
	 */
	private function normalize_login_identifier() {
		$identifier = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';

		return strtolower( trim( $identifier ) );
	}

	/**
	 * Get the generic success message used for masked responses.
	 *
	 * @return string
	 */
	private function get_generic_success_message() {
		return __( 'If an account matches your request, please check your inbox for the login link. If you did not receive a login email, check your spam folder too.', 'magic-login' );
	}

	/**
	 * Get the minimal honeypot config.
	 *
	 * @return array
	 */
	private function get_config() {
		$config = [
			'min_render_age' => 2,
			'max_render_age' => HOUR_IN_SECONDS,
		];

		return (array) apply_filters( 'magic_login_honeypot_config', $config );
	}
}
