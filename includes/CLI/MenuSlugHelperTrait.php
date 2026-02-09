<?php
/**
 * Menu Slug Helper Trait for CLI.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\CLI;

use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;

/**
 * Helper methods for resolving menu slugs in CLI commands.
 */
trait MenuSlugHelperTrait {

	/**
	 * Get the base slug (without parent:: prefix for submenus).
	 *
	 * @param string $slug Menu slug or submenu key.
	 * @return string
	 */
	private function get_base_slug( string $slug ): string {
		if ( str_contains( $slug, '::' ) ) {
			return explode( '::', $slug, 2 )[0];
		}

		return $slug;
	}

	/**
	 * Get group for a slug, handling submenu keys.
	 *
	 * @param string $slug Menu slug or submenu key.
	 * @return string
	 */
	private function get_slug_group( string $slug ): string {
		return CoreMenuItems::get_group( $this->get_base_slug( $slug ) );
	}
}
