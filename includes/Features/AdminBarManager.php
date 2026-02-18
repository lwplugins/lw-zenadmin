<?php
/**
 * Admin Bar Manager feature.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features;

use LightweightPlugins\ZenAdmin\Features\Data\CoreAdminBarItems;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Manages admin bar node visibility.
 *
 * Discovers all registered nodes and hides disabled ones.
 */
final class AdminBarManager {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_before_admin_bar_render', [ $this, 'filter_nodes' ], 9999 );
	}

	/**
	 * Filter admin bar nodes based on saved settings.
	 *
	 * @return void
	 */
	public function filter_nodes(): void {
		global $wp_admin_bar;

		if ( ! $wp_admin_bar instanceof \WP_Admin_Bar ) {
			return;
		}

		$nodes      = $wp_admin_bar->get_nodes();
		$settings   = Options::get_adminbar_settings();
		$discovered = Options::get_discovered_adminbar();
		$changed    = false;

		if ( ! is_array( $nodes ) ) {
			return;
		}

		foreach ( $nodes as $node ) {
			$title = ! empty( $node->title ) ? wp_strip_all_tags( $node->title ) : $node->id;

			if ( ! isset( $discovered[ $node->id ] ) || $discovered[ $node->id ]['title'] !== $title ) {
				$discovered[ $node->id ] = [
					'title'  => $title,
					'parent' => $node->parent ?? '',
				];
				$changed                 = true;
			}

			if ( CoreAdminBarItems::is_protected( $node->id ) ) {
				continue;
			}

			if ( ! self::is_node_visible( $node->id, $settings ) ) {
				$wp_admin_bar->remove_node( $node->id );
			}
		}

		if ( $changed ) {
			Options::save_discovered_adminbar( $discovered );
		}
	}

	/**
	 * Determine if a node should be visible.
	 *
	 * @param string              $node_id  Node identifier.
	 * @param array<string>|false $settings Saved settings or false if never saved.
	 * @return bool
	 */
	public static function is_node_visible( string $node_id, array|false $settings ): bool {
		if ( false === $settings ) {
			return true;
		}

		return in_array( $node_id, $settings, true );
	}
}
