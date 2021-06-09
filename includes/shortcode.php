<?php
/**
 * Shortcode functionality
 *
 * @package MagicLogin
 */

namespace MagicLogin\Shortcode;

use function MagicLogin\Core\style_url;
use function MagicLogin\Login\process_login_request;

/**
 * Default setup routine
 *
 * @return void
 * @since 1.1
 */
function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_shortcode( 'magic_login_form', $n( 'shortcode_login_form' ) );
}

/**
 * This form needs to be compatible with various themes as much as possible
 * not like the form in login.php which designed to fit on the standard login screen
 */
function shortcode_login_form() {
	ob_start();

	wp_enqueue_style(
		'magic_login_admin',
		style_url( 'shortcode-style', 'shortcode' ),
		[],
		MAGIC_LOGIN_VERSION
	);

	$form_action = apply_filters( 'magic_login_shortcode_form_action', '' );

	$login_request = process_login_request();
	?>
	<div id="magic-login-shortcode">
		<?php
		$login_errors = $login_request['errors'];

		// error messages
		if ( ! empty( $login_errors ) && is_wp_error( $login_errors ) && $login_errors->has_errors() ) {
			$error_messages = '';

			foreach ( $login_errors->get_error_codes() as $code ) {
				foreach ( $login_errors->get_error_messages( $code ) as $message ) {
					$error_messages .= $message . "<br />\n";
				}
			}

			if ( ! empty( $error_messages ) ) {
				printf( '<div id="login_error">%s</div>', wp_kses_post( $error_messages ) );
			}
		}

		// display info messages
		if ( ! empty( $login_request['info'] ) ) {
			echo wp_kses_post( $login_request['info'] );
		}
		?>


		<?php if ( $login_request['show_form'] ) : ?>
			<form name="magicloginform" class="magic-login-inline-login-form" id="magicloginform" action="<?php echo esc_attr( $form_action ); ?>" method="post" autocomplete="off">
				<p>
					<label for="user_login"><?php esc_html_e( 'Username or Email Address', 'magic-login' ); ?></label>
					<input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" required />
					<?php

					/**
					 * Fires following the 'email' field in the login form.
					 *
					 * @since 1.0
					 */
					do_action( 'magic_login_form' );

					?>
					<input type="submit" name="wp-submit" id="wp-submit" class="magic-login-submit button button-primary button-large" value="<?php esc_attr_e( 'Send me the link', 'magic-login' ); ?>" />
					<input type="hidden" name="testcookie" value="1" />
			</form>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
