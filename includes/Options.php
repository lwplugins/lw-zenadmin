<?php
/**
 * Options management class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin;

/**
 * Handles plugin options and settings.
 */
final class Options {

	/**
	 * Option name in database.
	 */
	public const OPTION_NAME = 'lw_zenadmin_options';

	/**
	 * Widget settings option name.
	 */
	public const WIDGET_SETTINGS = 'lw_zenadmin_widget_settings';

	/**
	 * Discovered widgets option name.
	 */
	public const DISCOVERED_WIDGETS = 'lw_zenadmin_discovered_widgets';

	/**
	 * Menu settings option name.
	 */
	public const MENU_SETTINGS = 'lw_zenadmin_menu_settings';

	/**
	 * Discovered menus option name.
	 */
	public const DISCOVERED_MENUS = 'lw_zenadmin_discovered_menus';

	/**
	 * Admin bar settings option name.
	 */
	public const ADMINBAR_SETTINGS = 'lw_zenadmin_adminbar_settings';

	/**
	 * Discovered admin bar nodes option name.
	 */
	public const DISCOVERED_ADMINBAR = 'lw_zenadmin_discovered_adminbar';

	/**
	 * Cached options.
	 *
	 * @var array<string, mixed>|null
	 */
	private static ?array $options = null;

	/**
	 * Get default options.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_defaults(): array {
		return [
			'notices_enabled'  => true,
			'widgets_enabled'  => true,
			'menu_enabled'     => false,
			'adminbar_enabled' => false,
		];
	}

	/**
	 * Get all options.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_all(): array {
		if ( null === self::$options ) {
			$saved         = get_option( self::OPTION_NAME, [] );
			self::$options = wp_parse_args( $saved, self::get_defaults() );
		}

		return self::$options;
	}

	/**
	 * Get a single option.
	 *
	 * @param string $key Option key.
	 * @return mixed
	 */
	public static function get( string $key ): mixed {
		$options = self::get_all();

		return $options[ $key ] ?? ( self::get_defaults()[ $key ] ?? null );
	}

	/**
	 * Save all options.
	 *
	 * @param array<string, mixed> $options Options to save.
	 * @return bool
	 */
	public static function save( array $options ): bool {
		self::$options = $options;
		return update_option( self::OPTION_NAME, $options );
	}

	/**
	 * Get widget visibility settings.
	 *
	 * @return array<string>|false False if never saved.
	 */
	public static function get_widget_settings(): array|false {
		return get_option( self::WIDGET_SETTINGS, false );
	}

	/**
	 * Save widget visibility settings.
	 *
	 * @param array<string> $enabled List of enabled widget IDs.
	 * @return bool
	 */
	public static function save_widget_settings( array $enabled ): bool {
		return update_option( self::WIDGET_SETTINGS, $enabled, false );
	}

	/**
	 * Get discovered widgets.
	 *
	 * @return array<string, string> Widget ID => title map.
	 */
	public static function get_discovered_widgets(): array {
		return (array) get_option( self::DISCOVERED_WIDGETS, [] );
	}

	/**
	 * Save discovered widgets.
	 *
	 * @param array<string, string> $widgets Widget ID => title map.
	 * @return bool
	 */
	public static function save_discovered_widgets( array $widgets ): bool {
		return update_option( self::DISCOVERED_WIDGETS, $widgets, false );
	}

	/**
	 * Get menu visibility settings.
	 *
	 * @return array<string>|false False if never saved.
	 */
	public static function get_menu_settings(): array|false {
		return get_option( self::MENU_SETTINGS, false );
	}

	/**
	 * Save menu visibility settings.
	 *
	 * @param array<string> $visible List of visible menu slugs.
	 * @return bool
	 */
	public static function save_menu_settings( array $visible ): bool {
		return update_option( self::MENU_SETTINGS, $visible, false );
	}

	/**
	 * Get discovered menus.
	 *
	 * @return array<string, array{title: string, icon: string}> Slug => data map.
	 */
	public static function get_discovered_menus(): array {
		return (array) get_option( self::DISCOVERED_MENUS, [] );
	}

	/**
	 * Save discovered menus.
	 *
	 * @param array<string, array{title: string, icon: string}> $menus Slug => data map.
	 * @return bool
	 */
	public static function save_discovered_menus( array $menus ): bool {
		return update_option( self::DISCOVERED_MENUS, $menus, false );
	}

	/**
	 * Get admin bar visibility settings.
	 *
	 * @return array<string>|false False if never saved.
	 */
	public static function get_adminbar_settings(): array|false {
		return get_option( self::ADMINBAR_SETTINGS, false );
	}

	/**
	 * Save admin bar visibility settings.
	 *
	 * @param array<string> $visible List of visible node IDs.
	 * @return bool
	 */
	public static function save_adminbar_settings( array $visible ): bool {
		return update_option( self::ADMINBAR_SETTINGS, $visible, false );
	}

	/**
	 * Get discovered admin bar nodes.
	 *
	 * @return array<string, array{title: string, parent: string}> Node ID => data map.
	 */
	public static function get_discovered_adminbar(): array {
		return (array) get_option( self::DISCOVERED_ADMINBAR, [] );
	}

	/**
	 * Save discovered admin bar nodes.
	 *
	 * @param array<string, array{title: string, parent: string}> $nodes Node ID => data map.
	 * @return bool
	 */
	public static function save_discovered_adminbar( array $nodes ): bool {
		return update_option( self::DISCOVERED_ADMINBAR, $nodes, false );
	}

	/**
	 * Clear options cache.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		self::$options = null;
	}

	/**
	 * Delete all plugin options (for uninstall).
	 *
	 * @return void
	 */
	public static function delete_all(): void {
		delete_option( self::OPTION_NAME );
		delete_option( self::WIDGET_SETTINGS );
		delete_option( self::DISCOVERED_WIDGETS );
		delete_option( self::MENU_SETTINGS );
		delete_option( self::DISCOVERED_MENUS );
		delete_option( self::ADMINBAR_SETTINGS );
		delete_option( self::DISCOVERED_ADMINBAR );
		self::$options = null;
	}
}
