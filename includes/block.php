<?php
/**
 * Block
 *
 * @package MagicLogin
 */

namespace MagicLogin\Block;

// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment
use function MagicLogin\Core\script_url;
use function MagicLogin\Login\process_login_request;

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	add_action( 'init', __NAMESPACE__ . '\\register_blocks' );
}

/**
 * Register blocks
 *
 * @since 1.2
 */
function register_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	wp_register_script(
		'magic-login-block-editor',
		script_url( 'block-editor', 'admin' ),
		[
			'wp-i18n',
			'wp-components',
			'wp-element',
			'wp-server-side-render',
		],
		MAGIC_LOGIN_VERSION,
		true
	);

	wp_set_script_translations(
		'magic-login-block-editor',
		'magic-login',
		plugin_dir_path( MAGIC_LOGIN_PLUGIN_FILE ) . 'languages'
	);

	$deps = is_admin() ? [ 'wp-edit-blocks' ] : [];

	wp_register_style(
		'magic-login-login-block',
		MAGIC_LOGIN_URL . 'dist/css/login-block-style.css',
		$deps,
		MAGIC_LOGIN_VERSION
	);

	register_block_type(
		'magic-login/login-block',
		[
			'attributes'      => [
				'title'               => [
					'type'    => 'string',
					'default' => __( 'Login with Email', 'magic-login' ),
				],
				'description'         => [
					'type'    => 'string',
					'default' => __( 'Please enter your username or email address. You will receive an email message to log in.', 'magic-login' ),
				],
				'loginLabel'          => [
					'type'    => 'string',
					'default' => __( 'Username or Email Address', 'magic-login' ),
				],
				'buttonLabel'         => [
					'type'    => 'string',
					'default' => __( 'Send me the link', 'magic-login' ),
				],
				'redirectTo'          => [
					'type' => 'string',
				],
				'hideLoggedIn'        => [
					'type'    => 'boolean',
					'default' => true,
				],
				'hideFormAfterSubmit' => [
					'type'    => 'boolean',
					'default' => true,
				],
			],
			'editor_script'   => 'magic-login-block-editor',
			'editor_style'    => 'magic-login-login-block',
			'style'           => 'magic-login-login-block',
			'render_callback' => __NAMESPACE__ . '\\render_login_block',
		]
	);
}


/**
 * Render Login Block
 *
 * @param array $args Block Attributes
 *
 * @return false|string|void
 * @since 1.2
 */
function render_login_block( $args ) {

	$form_action = apply_filters( 'magic_login_login_block_form_action', '' );

	$class = 'magic-login-login-block';
	if ( ! empty( $atts['align'] ) ) {
		$class .= ' align' . $atts['align'];
	}

	if ( ! empty( $args['className'] ) ) {
		$class .= ' ' . esc_attr( $args['className'] );
	}

	$atts = wp_parse_args(
		$args,
		[
			'redirectTo' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		]
	);

	if ( ! defined( 'REST_REQUEST' ) && ! is_admin() && is_user_logged_in() && $atts['hideLoggedIn'] ) { // already logged-in dont show
		return;
	}

	ob_start();
	$login_request = process_login_request();
	if ( false === $login_request['show_form'] && ! $atts['hideFormAfterSubmit'] ) {
		$login_request['show_form'] = true;
	}

	?>

	<?php
	$error_messages = '';
	$login_errors   = $login_request['errors'];
	// error messages
	if ( ! empty( $login_errors ) && is_wp_error( $login_errors ) && $login_errors->has_errors() ) {
		foreach ( $login_errors->get_error_codes() as $code ) {
			foreach ( $login_errors->get_error_messages( $code ) as $message ) {
				$error_messages .= $message . "<br />\n";
			}
		}
	}
	?>

	<div id="magic-login-login-block" class="<?php echo esc_attr( $class ); ?>">
		<?php if ( ! empty( $atts['title'] ) ) : ?>
			<h2 id="magic-login-block-title"><?php echo esc_html( $atts['title'] ); ?></h2>
		<?php endif; ?>

		<?php
		if ( ! empty( $error_messages ) ) :
			printf( '<div class="magic_login_block_login_error">%s</div>', wp_kses_post( $error_messages ) );
		endif;
		?>

		<?php if ( $login_request['is_processed'] ) : ?>
			<?php echo wp_kses_post( $login_request['info'] ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $atts['description'] ) && $login_request['show_form'] ) : ?>
			<p class="magic-login-block-description"><?php echo esc_html( $atts['description'] ); ?></p>
		<?php endif; ?>

		<?php if ( $login_request['show_form'] ) : ?>
			<form name="magicloginform" class="block-login-form" id="magicloginform" action="<?php echo esc_attr( $form_action ); ?>" method="post" autocomplete="off">
				<div class="magicloginform-inner">
					<?php if ( ! empty( $atts['loginLabel'] ) ) : ?>
						<label for="user_login"><?php echo esc_html( $atts['loginLabel'] ); ?></label>
					<?php endif; ?>

					<input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" required />
					<?php do_action( 'magic_login_form' ); ?>
					<?php if ( ! empty( $atts['buttonLabel'] ) ) : ?>
						<input type="submit" name="wp-submit" id="wp-submit" class="magic-login-submit button button-primary button-large" value="<?php echo esc_attr( $atts['buttonLabel'] ); ?>" />
					<?php endif; ?>

					<?php if ( ! empty( $atts['redirectTo'] ) ) : ?>
						<input type="hidden" name="redirect_to" value="<?php echo esc_url( $atts['redirectTo'] ); ?>" />
					<?php endif; ?>
					<input type="hidden" name="testcookie" value="1" />
				</div>
			</form>
		<?php endif; ?>
	</div>

	<?php
	return ob_get_clean();
}
