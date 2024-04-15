<?php
/**
 * Uninstall Magic Login
 * Deletes all plugin related data and configurations
 *
 * @package MagicLogin
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// don't perform uninstallation routine if pro version is active
if ( defined( 'MAGIC_LOGIN_PRO_PLUGIN_FILE' ) ) {
	return;
}

require_once 'plugin.php';

// delete plugin settings
delete_option( \MagicLogin\Constants\SETTING_OPTION );
delete_site_option( \MagicLogin\Constants\SETTING_OPTION );

// clean-up tokens
MagicLogin\Utils\delete_all_tokens();

// clean-up scheduled cron
wp_clear_scheduled_hook( 'magic_login_cleanup_expired_tokens' );


