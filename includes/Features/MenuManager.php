<?php
/**
 * Menu Manager feature.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features;

use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Manages admin menu visibility.
 *
 * Discovers all registered menus and hides disabled ones.
 */
final class MenuManager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'filter_menus' ], 9999 );
	}

	/**
	 * Filter admin menus based on saved settings.
	 *
	 * @return void
	 */
	public function filter_menus(): void {
		global $menu, $submenu;

		if ( empty( $menu ) || ! is_array( $menu ) ) {
			return;
		}

		$settings   = Options::get_menu_settings();
		$discovered = Options::get_discovered_menus();
		$changed    = false;

		foreach ( $menu as $item ) {
			if ( empty( $item[2] ) ) {
				continue;
			}

			$slug  = $item[2];
			$title = ! empty( $item[0] ) ? wp_strip_all_tags( $item[0] ) : $slug;
			$icon  = $item[6] ?? '';

			if ( ! isset( $discovered[ $slug ] ) || $discovered[ $slug ]['title'] !== $title ) {
				$discovered[ $slug ] = [
					'title' => $title,
					'icon'  => $icon,
				];
				$changed             = true;
			}

			$this->discover_submenus( $slug, $submenu, $discovered, $changed );

			if ( CoreMenuItems::is_protected( $slug ) ) {
				continue;
			}

			if ( ! self::is_menu_visible( $slug, $settings ) ) {
				remove_menu_page( $slug );
			}
		}

		$this->filter_submenus( $submenu, $settings );

		if ( $changed ) {
			Options::save_discovered_menus( $discovered );
		}
	}

	/**
	 * Discover submenus for a parent.
	 *
	 * @param string                                             $parent_slug Parent menu slug.
	 * @param array<string, array<int, array<int, string>>>|null $submenu     Global submenu.
	 * @param array<string, array{title: string, icon: string}>  $discovered  Discovered menus (by ref).
	 * @param bool                                               $changed     Changed flag (by ref).
	 */
	private function discover_submenus(
		string $parent_slug,
		array|null $submenu,
		array &$discovered,
		bool &$changed
	): void {
		if ( empty( $submenu[ $parent_slug ] ) ) {
			return;
		}

		foreach ( $submenu[ $parent_slug ] as $sub ) {
			if ( empty( $sub[2] ) ) {
				continue;
			}

			$sub_key   = $parent_slug . '::' . $sub[2];
			$sub_title = ! empty( $sub[0] ) ? wp_strip_all_tags( $sub[0] ) : $sub[2];

			if ( ! isset( $discovered[ $sub_key ] ) || $discovered[ $sub_key ]['title'] !== $sub_title ) {
				$discovered[ $sub_key ] = [
					'title' => $sub_title,
					'icon'  => '',
				];
				$changed                = true;
			}
		}
	}

	/**
	 * Filter submenus based on saved settings.
	 *
	 * @param array<string, array<int, array<int, string>>>|null $submenu_global Global submenu.
	 * @param array<string>|false                                $settings       Saved settings.
	 */
	private function filter_submenus(
		array|null $submenu_global,
		array|false $settings
	): void {
		if ( empty( $submenu_global ) ) {
			return;
		}

		foreach ( $submenu_global as $parent_slug => $subs ) {
			foreach ( $subs as $sub ) {
				if ( empty( $sub[2] ) ) {
					continue;
				}

				$sub_key = $parent_slug . '::' . $sub[2];

				if ( CoreMenuItems::is_protected( $parent_slug ) && CoreMenuItems::is_protected( $sub[2] ) ) {
					continue;
				}

				if ( ! self::is_menu_visible( $sub_key, $settings ) ) {
					remove_submenu_page( $parent_slug, $sub[2] );
				}
			}
		}
	}

	/**
	 * Determine if a menu item should be visible.
	 *
	 * @param string              $slug     Menu slug or submenu key.
	 * @param array<string>|false $settings Saved settings or false if never saved.
	 * @return bool
	 */
	public static function is_menu_visible( string $slug, array|false $settings ): bool {
		if ( false === $settings ) {
			return true;
		}

		return in_array( $slug, $settings, true );
	}
}
