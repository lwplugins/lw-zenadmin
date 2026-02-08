<?php
/**
 * Main Plugin class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin;

use LightweightPlugins\ZenAdmin\Admin\SettingsPage;
use LightweightPlugins\ZenAdmin\CLI\Commands as CLICommands;
use LightweightPlugins\ZenAdmin\CLI\WidgetCommands as CLIWidgetCommands;
use LightweightPlugins\ZenAdmin\Features\NoticeCollector;
use LightweightPlugins\ZenAdmin\Features\NoticePanel;
use LightweightPlugins\ZenAdmin\Features\WidgetManager;

/**
 * Main plugin class.
 */
final class Plugin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		$this->init_features();
		$this->init_admin();
		$this->init_cli();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'init', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Initialize features.
	 *
	 * @return void
	 */
	private function init_features(): void {
		if ( ! is_admin() ) {
			return;
		}

		if ( Options::get( 'notices_enabled' ) ) {
			new NoticeCollector();
			new NoticePanel();
		}

		if ( Options::get( 'widgets_enabled' ) ) {
			new WidgetManager();
		}
	}

	/**
	 * Initialize admin components.
	 *
	 * @return void
	 */
	private function init_admin(): void {
		if ( is_admin() ) {
			new SettingsPage();
		}
	}

	/**
	 * Initialize CLI commands.
	 *
	 * @return void
	 */
	private function init_cli(): void {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'lw-zenadmin', CLICommands::class );
			\WP_CLI::add_command( 'lw-zenadmin widget', CLIWidgetCommands::class );
		}
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'lw-zenadmin',
			false,
			dirname( plugin_basename( LW_ZENADMIN_FILE ) ) . '/languages'
		);
	}
}
