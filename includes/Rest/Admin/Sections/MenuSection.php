<?php
/**
 * Admin menu visibility section.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin\Sections;

use LightweightPlugins\ZenAdmin\Admin\Settings\MenuGrouperTrait;
use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;
use LightweightPlugins\ZenAdmin\Features\MenuManager;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Discovered menu items (submenus as "parent::child", right after their
 * parent) grouped Core / WooCommerce / LW Plugins / Third-party.
 */
final class MenuSection extends AbstractSection {

	use MenuGrouperTrait;

	/**
	 * Section key.
	 *
	 * @return string
	 */
	public function key(): string {
		return 'menus';
	}

	/**
	 * Discovered menu slugs and submenu keys.
	 *
	 * @return array<int, string>
	 */
	public function discovered_ids(): array {
		return array_map( 'strval', array_keys( Options::get_discovered_menus() ) );
	}

	/**
	 * Protected top-level slugs, and submenus whose parent and slug are both
	 * protected (the rule MenuManager applies when filtering).
	 *
	 * @param string $id Menu slug or submenu key.
	 * @return bool
	 */
	public function is_protected( string $id ): bool {
		if ( str_contains( $id, '::' ) ) {
			$parts = explode( '::', $id, 2 );
			return CoreMenuItems::is_protected( $parts[0] ) && CoreMenuItems::is_protected( $parts[1] );
		}

		return CoreMenuItems::is_protected( $id );
	}

	/**
	 * Effective visibility.
	 *
	 * @param string $id Menu slug or submenu key.
	 * @return bool
	 */
	public function is_visible( string $id ): bool {
		return MenuManager::is_menu_visible( $id, $this->saved() );
	}

	/**
	 * Persist the visible menu list.
	 *
	 * @param array<int, string> $visible Menu slugs and submenu keys.
	 * @return void
	 */
	public function save( array $visible ): void {
		Options::save_menu_settings( $visible );
	}

	/**
	 * Saved list.
	 *
	 * @return array<string>|false
	 */
	protected function read_saved(): array|false {
		return Options::get_menu_settings();
	}

	/**
	 * Menus by source, submenus at depth 1.
	 *
	 * @return array<string, array{label: string, items: array<array-key, array<string, mixed>>}>
	 */
	protected function grouped(): array {
		$groups = $this->group_menus( Options::get_discovered_menus() );

		foreach ( $groups as $group_id => $group ) {
			foreach ( $group['items'] as $slug => $data ) {
				$groups[ $group_id ]['items'][ $slug ]['depth'] = empty( $data['is_sub'] ) ? 0 : 1;
			}
		}

		return $groups;
	}
}
