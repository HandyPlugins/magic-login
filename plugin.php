<?php
/**
 * Plugin Name:       Magic Login
 * Plugin URI:        https://handyplugins.co/magic-login-pro/
 * Description:       Passwordless login for WordPress.
 * Version:           2.3.1
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            HandyPlugins
 * Author URI:        https://handyplugins.co/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       magic-login
 * Domain Path:       /languages
 *
 * @package           MagicLogin
 */

namespace MagicLogin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Useful global constants.
define( 'MAGIC_LOGIN_VERSION', '2.3.1' );
define( 'MAGIC_LOGIN_PLUGIN_FILE', __FILE__ );
define( 'MAGIC_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MAGIC_LOGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAGIC_LOGIN_INC', MAGIC_LOGIN_PATH . 'includes/' );

if ( ! defined( 'MAGIC_LOGIN_USERNAME_ONLY' ) ) {
	define( 'MAGIC_LOGIN_USERNAME_ONLY', false );
}

// deactivate pro
if ( defined( 'MAGIC_LOGIN_PRO_PLUGIN_FILE' ) ) {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	deactivate_plugins( plugin_basename( MAGIC_LOGIN_PRO_PLUGIN_FILE ) );

	return;
}

// Require Composer autoloader if it exists.
if ( file_exists( MAGIC_LOGIN_PATH . '/vendor/autoload.php' ) ) {
	require_once MAGIC_LOGIN_PATH . 'vendor/autoload.php';
}


/**
 * PSR-4-ish autoloading
 *
 * @since 2.0
 */
spl_autoload_register(
	function ( $class ) {
		// project-specific namespace prefix.
		$prefix = 'MagicLogin\\';

		// base directory for the namespace prefix.
		$base_dir = __DIR__ . '/includes/classes/';

		// does the class use the namespace prefix?
		$len = strlen( $prefix );

		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );

		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// if the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

// Include files.
require_once MAGIC_LOGIN_INC . 'constants.php';
require_once MAGIC_LOGIN_INC . 'utils.php';
require_once MAGIC_LOGIN_INC . 'core.php';
require_once MAGIC_LOGIN_INC . 'login.php';
require_once MAGIC_LOGIN_INC . 'admin/dashboard.php';
require_once MAGIC_LOGIN_INC . 'shortcode.php';
require_once MAGIC_LOGIN_INC . 'block.php';

$network_activated = Utils\is_network_wide( MAGIC_LOGIN_PLUGIN_FILE );
if ( ! defined( 'MAGIC_LOGIN_IS_NETWORK' ) ) {
	define( 'MAGIC_LOGIN_IS_NETWORK', $network_activated );
}

/**
 * Setup routine
 *
 * @return void
 * @since 1.5 bootstrapping with plugins_loaded hook
 */
function setup_magic_login() {
	// Bootstrap.
	Core\setup();
	Login\setup();
	Shortcode\setup();
	Block\setup();
	Admin\Dashboard\setup();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup_magic_login' );
