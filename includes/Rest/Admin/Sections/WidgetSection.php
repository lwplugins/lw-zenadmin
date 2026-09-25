<?php
/**
 * Dashboard widgets visibility section.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin\Sections;

use LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets;
use LightweightPlugins\ZenAdmin\Features\WidgetManager;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Discovered dashboard widgets grouped Core / WooCommerce / Third-party.
 * No widget is protected; a never-saved list shows Core + WooCommerce only.
 */
final class WidgetSection extends AbstractSection {

	/**
	 * Section key.
	 *
	 * @return string
	 */
	public function key(): string {
		return 'widgets';
	}

	/**
	 * Discovered widget IDs.
	 *
	 * @return array<int, string>
	 */
	public function discovered_ids(): array {
		return array_map( 'strval', array_keys( Options::get_discovered_widgets() ) );
	}

	/**
	 * Widgets are never protected.
	 *
	 * @param string $id Widget ID.
	 * @return bool
	 */
	public function is_protected( string $id ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Interface signature.
		return false;
	}

	/**
	 * Effective visibility.
	 *
	 * @param string $id Widget ID.
	 * @return bool
	 */
	public function is_visible( string $id ): bool {
		return WidgetManager::is_widget_visible( $id, $this->saved() );
	}

	/**
	 * Persist the enabled widget list.
	 *
	 * @param array<int, string> $visible Widget IDs.
	 * @return void
	 */
	public function save( array $visible ): void {
		Options::save_widget_settings( $visible );
	}

	/**
	 * Saved list.
	 *
	 * @return array<string>|false
	 */
	protected function read_saved(): array|false {
		return Options::get_widget_settings();
	}

	/**
	 * Widgets by source, in discovery order.
	 *
	 * @return array<string, array{label: string, items: array<array-key, array<string, mixed>>}>
	 */
	protected function grouped(): array {
		$groups = [
			'core'        => [
				'label' => __( 'WordPress Core', 'lw-zenadmin' ),
				'items' => [],
			],
			'woocommerce' => [
				'label' => __( 'WooCommerce', 'lw-zenadmin' ),
				'items' => [],
			],
			'third_party' => [
				'label' => __( 'Third-party', 'lw-zenadmin' ),
				'items' => [],
			],
		];

		foreach ( Options::get_discovered_widgets() as $widget_id => $title ) {
			$widget_id = (string) $widget_id;
			$group     = str_replace( '-', '_', CoreWidgets::get_group( $widget_id ) );

			$groups[ $group ]['items'][ $widget_id ] = [
				'title' => $title,
				'depth' => 0,
			];
		}

		return $groups;
	}
}
