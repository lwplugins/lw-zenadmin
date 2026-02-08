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
			'notices_enabled' => true,
			'widgets_enabled' => true,
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
		self::$options = null;
	}
}
