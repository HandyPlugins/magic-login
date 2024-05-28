<?php
/**
 * Settings Page
 *
 * @package MagicLogin
 */

namespace MagicLogin\Admin\Dashboard;

use const MagicLogin\Constants\SETTING_OPTION;
use function MagicLogin\Utils\delete_all_tokens;
use function MagicLogin\Utils\get_allowed_intervals;

// phpcs:disable WordPress.WhiteSpace.PrecisionAlignment.Found
// phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
// phpcs:disable WordPress.WP.I18n.MissingTranslatorsComment

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Default setup routine
 *
 * @return void
 */
function setup() {
	if ( MAGIC_LOGIN_IS_NETWORK ) {
		add_action( 'network_admin_menu', __NAMESPACE__ . '\\admin_menu' );
	} else {
		add_action( 'admin_menu', __NAMESPACE__ . '\\admin_menu' );
	}

	add_action( 'admin_init', __NAMESPACE__ . '\\save_settings' );
	add_filter( 'admin_body_class', __NAMESPACE__ . '\\add_sui_admin_body_class' );
}

/**
 * Add required class for shared UI
 *
 * @param string $classes css classes for admin area
 *
 * @return string
 * @see https://wpmudev.github.io/shared-ui/installation/
 */
function add_sui_admin_body_class( $classes ) {
	$classes .= ' sui-2-12-24 ';

	return $classes;
}


/**
 * Add menu item
 */
function admin_menu() {
	$parent = MAGIC_LOGIN_IS_NETWORK ? 'settings.php' : 'options-general.php';

	add_submenu_page(
		$parent,
		esc_html__( 'Magic Login', 'magic-login' ),
		esc_html__( 'Magic Login', 'magic-login' ),
		apply_filters( 'magic_login_admin_menu_cap', 'manage_options' ),
		'magic-login',
		__NAMESPACE__ . '\settings_page'
	);
}

/**
 * Settings page
 */
function settings_page() {
	$settings = \MagicLogin\Utils\get_settings();
	?>
	<?php if ( is_network_admin() ) : ?>
		<?php settings_errors(); ?>
	<?php endif; ?>

	<main class="sui-wrap">
		<?php include MAGIC_LOGIN_INC . 'admin/partials/header.php'; ?>
		<?php include MAGIC_LOGIN_INC . 'admin/partials/settings.php'; ?>
		<?php include MAGIC_LOGIN_INC . 'admin/partials/footer.php'; ?>
	</main>

	<?php
}

/**
 * Save settings
 */
function save_settings() {

	if ( ! is_user_logged_in() ) {
		return;
	}

	$nonce = filter_input( INPUT_POST, 'magic_login_settings', FILTER_SANITIZE_SPECIAL_CHARS );
	if ( wp_verify_nonce( $nonce, 'magic_login_settings' ) ) {

		if ( isset( $_POST['reset_tokens'] ) ) {
			if ( false !== delete_all_tokens() ) {
				add_settings_error( SETTING_OPTION, 'magic-login', esc_html__( 'Tokens has been removed.', 'magic-login' ), 'success' );
			} else {
				add_settings_error( SETTING_OPTION, 'magic-login', esc_html__( 'Tokens could not be removed.', 'magic-login' ), 'error' );
			}

			return;
		}

		$settings                     = [];
		$settings['is_default']       = boolval( filter_input( INPUT_POST, 'is_default' ) );
		$settings['add_login_button'] = boolval( filter_input( INPUT_POST, 'add_login_button' ) );
		$settings['token_ttl']        = absint( filter_input( INPUT_POST, 'token_ttl' ) );
		$settings['token_validity']   = absint( filter_input( INPUT_POST, 'token_validity' ) );
		$settings['auto_login_links'] = boolval( filter_input( INPUT_POST, 'auto_login_links' ) );
		$settings['enable_ajax']      = boolval( filter_input( INPUT_POST, 'enable_ajax' ) );

		// convert TTL in minute
		if ( isset( $_POST['token_ttl'] ) && $_POST['token_ttl'] > 0 && isset( $_POST['token_interval'] ) ) {
			switch ( $_POST['token_interval'] ) {
				case 'DAY':
					$settings['token_ttl'] = absint( $_POST['token_ttl'] ) * 1440;
					break;
				case 'HOUR':
					$settings['token_ttl'] = absint( $_POST['token_ttl'] ) * 60;
					break;
				case 'MINUTE':
				default:
					$settings['token_ttl'] = absint( $_POST['token_ttl'] ) * 1;
			}
		}

		$token_interval    = sanitize_text_field( filter_input( INPUT_POST, 'token_interval' ) );
		$allowed_intervals = get_allowed_intervals();

		if ( isset( $allowed_intervals[ $token_interval ] ) ) {
			$settings['token_interval'] = $token_interval;
		}

		if ( MAGIC_LOGIN_IS_NETWORK ) {
			update_site_option( SETTING_OPTION, $settings );
		} else {
			update_option( SETTING_OPTION, $settings );
		}

		add_settings_error( SETTING_OPTION, 'magic-login', esc_html__( 'Settings saved.', 'magic-login' ), 'success' );

		return;
	}

}
