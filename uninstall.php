<?php
/**
 * Uninstall script for LW ZenAdmin.
 *
 * Removes every option the plugin persists. Mirrors the constants in
 * includes/Options.php — keep in sync.
 *
 * @package LightweightPlugins\ZenAdmin
 */

// Exit if not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$lw_zenadmin_options = [
	'lw_zenadmin_options',
	'lw_zenadmin_widget_settings',
	'lw_zenadmin_discovered_widgets',
	'lw_zenadmin_menu_settings',
	'lw_zenadmin_discovered_menus',
	'lw_zenadmin_adminbar_settings',
	'lw_zenadmin_discovered_adminbar',
];

foreach ( $lw_zenadmin_options as $lw_zenadmin_option ) {
	delete_option( $lw_zenadmin_option );
}
