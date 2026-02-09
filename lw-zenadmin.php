<?php
/**
 * Plugin Name:       LW ZenAdmin
 * Plugin URI:        https://github.com/lwplugins/lw-zenadmin
 * Description:       Clean up your WordPress admin — notices sidebar, dashboard widget manager, and admin menu manager.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            LW Plugins
 * Author URI:        https://lwplugins.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lw-zenadmin
 * Domain Path:       /languages
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'LW_ZENADMIN_VERSION', '1.1.0' );
define( 'LW_ZENADMIN_FILE', __FILE__ );
define( 'LW_ZENADMIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'LW_ZENADMIN_URL', plugin_dir_url( __FILE__ ) );

// Autoloader (required for PSR-4 class loading).
if ( file_exists( LW_ZENADMIN_PATH . 'vendor/autoload.php' ) ) {
	require_once LW_ZENADMIN_PATH . 'vendor/autoload.php';
}

/**
 * Returns the main plugin instance.
 *
 * @return Plugin
 */
function lw_zenadmin(): Plugin {
	static $instance = null;

	if ( null === $instance ) {
		$instance = new Plugin();
	}

	return $instance;
}

// Initialize the plugin.
add_action( 'plugins_loaded', __NAMESPACE__ . '\\lw_zenadmin' );
