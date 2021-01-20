<?php
/**
 * Plugin Name:       Magic Login
 * Plugin URI:        https://handyplugins.co/magic-login-pro/
 * Description:       Passwordless login for WordPress.
 * Version:           1.0.1
 * Requires at least: 5.0
 * Requires PHP:      5.6
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

// Useful global constants.
define( 'MAGIC_LOGIN_VERSION', '1.0.1' );
define( 'MAGIC_LOGIN_PLUGIN_FILE', __FILE__ );
define( 'MAGIC_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MAGIC_LOGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAGIC_LOGIN_INC', MAGIC_LOGIN_PATH . 'includes/' );


// deactivate pro
if ( defined( 'MAGIC_LOGIN_PRO_PLUGIN_FILE' ) ) {
	deactivate_plugins( plugin_basename( MAGIC_LOGIN_PRO_PLUGIN_FILE ) );

	return;
}

// Require Composer autoloader if it exists.
if ( file_exists( MAGIC_LOGIN_PATH . '/vendor/autoload.php' ) ) {
	require_once MAGIC_LOGIN_PATH . 'vendor/autoload.php';
}

// Include files.
require_once MAGIC_LOGIN_INC . 'constants.php';
require_once MAGIC_LOGIN_INC . 'utils.php';
require_once MAGIC_LOGIN_INC . 'core.php';
require_once MAGIC_LOGIN_INC . 'login.php';
require_once MAGIC_LOGIN_INC . 'settings.php';

$network_activated = Utils\is_network_wide( MAGIC_LOGIN_PLUGIN_FILE );
if ( ! defined( 'MAGIC_LOGIN_IS_NETWORK' ) ) {
	define( 'MAGIC_LOGIN_IS_NETWORK', $network_activated );
}

// Bootstrap.
Core\setup();
Login\setup();
Settings\setup();
