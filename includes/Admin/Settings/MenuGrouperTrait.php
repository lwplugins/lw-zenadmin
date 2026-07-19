<?php
/**
 * Menu Grouper Trait.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;

/**
 * Groups discovered menus by source (Core, WooCommerce, LW Plugins, Third-party).
 */
trait MenuGrouperTrait {

	/**
	 * Group menus by source, with submenus nested under parents.
	 *
	 * @param array<string, array{title: string, icon: string}> $discovered All discovered menus.
	 * @return array<string, array{label: string, items: array<string, array{title: string, icon: string, is_sub: bool}>}>
	 */
	private function group_menus( array $discovered ): array {
		$groups = [
			'core'        => [
				'label' => __( 'WordPress Core', 'lw-zenadmin' ),
				'items' => [],
			],
			'woocommerce' => [
				'label' => __( 'WooCommerce', 'lw-zenadmin' ),
				'items' => [],
			],
			'lw_plugins'  => [
				'label' => __( 'LW Plugins', 'lw-zenadmin' ),
				'items' => [],
			],
			'third_party' => [
				'label' => __( 'Third-party', 'lw-zenadmin' ),
				'items' => [],
			],
		];

		$parents  = [];
		$children = [];

		foreach ( $discovered as $slug => $data ) {
			if ( str_contains( $slug, '::' ) ) {
				$children[ $slug ] = $data;
			} else {
				$parents[ $slug ] = $data;
			}
		}

		foreach ( $parents as $slug => $data ) {
			$group                              = CoreMenuItems::get_group( $slug );
			$data['is_sub']                     = false;
			$groups[ $group ]['items'][ $slug ] = $data;

			foreach ( $children as $sub_key => $sub_data ) {
				if ( str_starts_with( $sub_key, $slug . '::' ) ) {
					$sub_data['is_sub']                    = true;
					$groups[ $group ]['items'][ $sub_key ] = $sub_data;
				}
			}
		}

		// A submenu whose parent slug is absent from the discovered data (e.g.
		// stale/older discovery data, or a parent menu that has since
		// disappeared) is never visited by the loop above. List it under
		// "third_party" as a sub-item instead of silently dropping it, so it
		// stays manageable in the settings UI.
		foreach ( $children as $sub_key => $sub_data ) {
			$parent_slug = explode( '::', $sub_key, 2 )[0];

			if ( isset( $parents[ $parent_slug ] ) ) {
				continue;
			}

			$sub_data['is_sub']                         = true;
			$groups['third_party']['items'][ $sub_key ] = $sub_data;
		}

		return $groups;
	}
}
