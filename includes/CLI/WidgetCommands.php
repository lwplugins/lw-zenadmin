<?php
/**
 * WP-CLI Widget Commands.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\CLI;

use LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets;
use LightweightPlugins\ZenAdmin\Features\WidgetManager;
use LightweightPlugins\ZenAdmin\Options;
use WP_CLI;

/**
 * Manage dashboard widget visibility via WP-CLI.
 *
 * ## EXAMPLES
 *
 *     wp lw-zenadmin widget list
 *     wp lw-zenadmin widget show dashboard_quick_press
 *     wp lw-zenadmin widget hide dashboard_primary
 */
final class WidgetCommands {

	/**
	 * List all discovered widgets and their visibility.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, csv, json, yaml). Default: table.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin widget list
	 *     wp lw-zenadmin widget list --format=json
	 *
	 * @subcommand list
	 * @param array<string>              $args       Positional arguments.
	 * @param array<string, string|bool> $assoc_args Named arguments.
	 */
	public function list_widgets( array $args, array $assoc_args ): void {
		$discovered = Options::get_discovered_widgets();

		if ( empty( $discovered ) ) {
			WP_CLI::warning( 'No widgets discovered yet. Visit the Dashboard in browser first.' );
			return;
		}

		$settings = Options::get_widget_settings();
		$items    = [];

		foreach ( $discovered as $widget_id => $title ) {
			$items[] = [
				'widget_id' => $widget_id,
				'name'      => $title,
				'visible'   => WidgetManager::is_widget_visible( $widget_id, $settings ) ? 'yes' : 'no',
				'group'     => CoreWidgets::get_group( $widget_id ),
			];
		}

		$format = $assoc_args['format'] ?? 'table';
		WP_CLI\Utils\format_items( $format, $items, [ 'widget_id', 'name', 'visible', 'group' ] );
	}

	/**
	 * Show a widget on the dashboard.
	 *
	 * ## OPTIONS
	 *
	 * <widget_id>
	 * : The widget ID to show.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin widget show dashboard_quick_press
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function show( array $args ): void {
		$this->set_visibility( $args[0], true );
	}

	/**
	 * Hide a widget from the dashboard.
	 *
	 * ## OPTIONS
	 *
	 * <widget_id>
	 * : The widget ID to hide.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin widget hide dashboard_primary
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function hide( array $args ): void {
		$this->set_visibility( $args[0], false );
	}

	/**
	 * Show all widgets on the dashboard.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin widget show-all
	 *
	 * @subcommand show-all
	 */
	public function show_all(): void {
		$discovered = Options::get_discovered_widgets();

		if ( empty( $discovered ) ) {
			WP_CLI::error( 'No widgets discovered yet. Visit the Dashboard in browser first.' );
		}

		Options::save_widget_settings( array_keys( $discovered ) );
		WP_CLI::success( 'All widgets are now visible.' );
	}

	/**
	 * Hide all widgets from the dashboard.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin widget hide-all
	 *
	 * @subcommand hide-all
	 */
	public function hide_all(): void {
		Options::save_widget_settings( [] );
		WP_CLI::success( 'All widgets are now hidden.' );
	}

	/**
	 * Reset widget visibility to defaults.
	 *
	 * Core and WooCommerce widgets become visible, third-party hidden.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin widget reset
	 */
	public function reset(): void {
		delete_option( Options::WIDGET_SETTINGS );
		WP_CLI::success( 'Widget visibility reset to defaults.' );
	}

	/**
	 * Set widget visibility.
	 *
	 * @param string $widget_id Widget identifier.
	 * @param bool   $visible   Whether to show or hide.
	 */
	private function set_visibility( string $widget_id, bool $visible ): void {
		$discovered = Options::get_discovered_widgets();

		if ( ! isset( $discovered[ $widget_id ] ) ) {
			WP_CLI::error(
				"Unknown widget: {$widget_id}. Use 'wp lw-zenadmin widget list' to see available widgets."
			);
		}

		$settings = Options::get_widget_settings();
		$enabled  = false !== $settings
			? $settings
			: array_filter( array_keys( $discovered ), [ CoreWidgets::class, 'is_default' ] );

		if ( $visible ) {
			$enabled = array_unique( array_merge( $enabled, [ $widget_id ] ) );
		} else {
			$enabled = array_values( array_diff( $enabled, [ $widget_id ] ) );
		}

		Options::save_widget_settings( $enabled );

		$action = $visible ? 'shown' : 'hidden';
		WP_CLI::success( "Widget '{$discovered[ $widget_id ]}' ({$widget_id}) is now {$action}." );
	}
}
