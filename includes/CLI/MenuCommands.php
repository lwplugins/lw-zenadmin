<?php
/**
 * WP-CLI Menu Commands.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\CLI;

use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;
use LightweightPlugins\ZenAdmin\Features\MenuManager;
use LightweightPlugins\ZenAdmin\Options;
use WP_CLI;

/**
 * Manage admin menu visibility via WP-CLI.
 *
 * ## EXAMPLES
 *
 *     wp lw-zenadmin menu list
 *     wp lw-zenadmin menu show edit.php
 *     wp lw-zenadmin menu hide tools.php
 */
final class MenuCommands {

	use MenuSlugHelperTrait;

	/**
	 * List all discovered menus and their visibility.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, csv, json, yaml). Default: table.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin menu list
	 *     wp lw-zenadmin menu list --format=json
	 *
	 * @subcommand list
	 * @param array<string>              $args       Positional arguments.
	 * @param array<string, string|bool> $assoc_args Named arguments.
	 */
	public function list_menus( array $args, array $assoc_args ): void {
		$discovered = Options::get_discovered_menus();

		if ( empty( $discovered ) ) {
			WP_CLI::warning( 'No menus discovered yet. Visit the admin in browser first.' );
			return;
		}

		$settings = Options::get_menu_settings();
		$items    = [];

		foreach ( $discovered as $slug => $data ) {
			$items[] = [
				'slug'      => $slug,
				'name'      => $data['title'],
				'visible'   => MenuManager::is_menu_visible( $slug, $settings ) ? 'yes' : 'no',
				'group'     => $this->get_slug_group( $slug ),
				'protected' => CoreMenuItems::is_protected( $this->get_base_slug( $slug ) ) ? 'yes' : 'no',
			];
		}

		$format = $assoc_args['format'] ?? 'table';
		WP_CLI\Utils\format_items( $format, $items, [ 'slug', 'name', 'visible', 'group', 'protected' ] );
	}

	/**
	 * Show a menu item in the admin sidebar.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The menu slug to show.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin menu show tools.php
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function show( array $args ): void {
		$this->set_visibility( $args[0], true );
	}

	/**
	 * Hide a menu item from the admin sidebar.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The menu slug to hide.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin menu hide tools.php
	 *
	 * @param array<string> $args Positional arguments.
	 */
	public function hide( array $args ): void {
		$this->set_visibility( $args[0], false );
	}

	/**
	 * Show all menu items.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin menu show-all
	 *
	 * @subcommand show-all
	 */
	public function show_all(): void {
		$discovered = Options::get_discovered_menus();

		if ( empty( $discovered ) ) {
			WP_CLI::error( 'No menus discovered yet. Visit the admin in browser first.' );
		}

		Options::save_menu_settings( array_keys( $discovered ) );
		WP_CLI::success( 'All menu items are now visible.' );
	}

	/**
	 * Hide all non-protected menu items.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin menu hide-all
	 *
	 * @subcommand hide-all
	 */
	public function hide_all(): void {
		$discovered = Options::get_discovered_menus();
		$protected  = [];

		foreach ( array_keys( $discovered ) as $slug ) {
			if ( CoreMenuItems::is_protected( $this->get_base_slug( $slug ) ) ) {
				$protected[] = $slug;
			}
		}

		Options::save_menu_settings( $protected );
		WP_CLI::success( 'All non-protected menu items are now hidden.' );
	}

	/**
	 * Reset menu visibility (show all by default).
	 *
	 * ## EXAMPLES
	 *
	 *     wp lw-zenadmin menu reset
	 */
	public function reset(): void {
		delete_option( Options::MENU_SETTINGS );
		WP_CLI::success( 'Menu visibility reset to defaults (all visible).' );
	}

	/**
	 * Set menu visibility.
	 *
	 * @param string $slug    Menu slug.
	 * @param bool   $visible Whether to show or hide.
	 */
	private function set_visibility( string $slug, bool $visible ): void {
		$discovered = Options::get_discovered_menus();

		if ( ! isset( $discovered[ $slug ] ) ) {
			WP_CLI::error(
				"Unknown menu: {$slug}. Use 'wp lw-zenadmin menu list' to see available menus."
			);
		}

		$base_slug = $this->get_base_slug( $slug );
		if ( ! $visible && CoreMenuItems::is_protected( $base_slug ) ) {
			WP_CLI::error( "Cannot hide protected menu: {$slug}." );
		}

		$settings = Options::get_menu_settings();
		$enabled  = false !== $settings
			? $settings
			: array_keys( $discovered );

		if ( $visible ) {
			$enabled = array_unique( array_merge( $enabled, [ $slug ] ) );
		} else {
			$enabled = array_values( array_diff( $enabled, [ $slug ] ) );
		}

		Options::save_menu_settings( $enabled );

		$action = $visible ? 'shown' : 'hidden';
		WP_CLI::success( "Menu '{$discovered[ $slug ]['title']}' ({$slug}) is now {$action}." );
	}
}
