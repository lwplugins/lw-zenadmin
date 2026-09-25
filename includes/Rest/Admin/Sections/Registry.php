<?php
/**
 * Visibility sections registry.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin\Sections;

/**
 * Fresh section instances keyed by their REST key. Instances cache the
 * saved list they read, so build a new set after a write.
 */
final class Registry {

	/**
	 * All sections.
	 *
	 * @return array<string, SectionInterface>
	 */
	public static function all(): array {
		$sections = [];

		foreach ( [ new WidgetSection(), new MenuSection(), new AdminBarSection() ] as $section ) {
			$sections[ $section->key() ] = $section;
		}

		return $sections;
	}
}
