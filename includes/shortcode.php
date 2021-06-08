<?php
/**
 * Shortcode functionality
 *
 * @package MagicLogin
 */

namespace MagicLogin\Shortcode;

use function MagicLogin\Core\style_url;

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
	wp_enqueue_style(
		'magic_login_admin',
		style_url( 'shortcode-style', 'shortcode' ),
		[],
		MAGIC_LOGIN_VERSION
	);

	?>
	<form name="magicloginform" class="magic-login-inline-login-form" id="magicloginform" action="<?php echo esc_url( site_url( 'wp-login.php?action=magic_login', 'login_post' ) ); ?>" method="post" autocomplete="off">
		<p>
			<label for="user_login"><?php esc_html_e( 'Username or Email Address', 'magic-login' ); ?></label>
			<input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" />
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
	<?php
}
