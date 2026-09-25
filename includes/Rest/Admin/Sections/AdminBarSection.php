<?php
/**
 * Admin bar visibility section.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Rest\Admin\Sections;

use LightweightPlugins\ZenAdmin\Admin\Settings\AdminBarNodeGrouper;
use LightweightPlugins\ZenAdmin\Features\AdminBarManager;
use LightweightPlugins\ZenAdmin\Features\Data\CoreAdminBarItems;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Discovered admin bar nodes, flattened depth-first and grouped
 * Core / WooCommerce / Third-party.
 */
final class AdminBarSection extends AbstractSection {

	/**
	 * Section key.
	 *
	 * @return string
	 */
	public function key(): string {
		return 'adminbar';
	}

	/**
	 * Discovered node IDs.
	 *
	 * @return array<int, string>
	 */
	public function discovered_ids(): array {
		return array_map( 'strval', array_keys( Options::get_discovered_adminbar() ) );
	}

	/**
	 * My Account, the secondary bar, user actions and Log Out stay.
	 *
	 * @param string $id Node ID.
	 * @return bool
	 */
	public function is_protected( string $id ): bool {
		return CoreAdminBarItems::is_protected( $id );
	}

	/**
	 * Effective visibility.
	 *
	 * @param string $id Node ID.
	 * @return bool
	 */
	public function is_visible( string $id ): bool {
		return AdminBarManager::is_node_visible( $id, $this->saved() );
	}

	/**
	 * Persist the visible node list.
	 *
	 * @param array<int, string> $visible Node IDs.
	 * @return void
	 */
	public function save( array $visible ): void {
		Options::save_adminbar_settings( $visible );
	}

	/**
	 * Saved list.
	 *
	 * @return array<string>|false
	 */
	protected function read_saved(): array|false {
		return Options::get_adminbar_settings();
	}

	/**
	 * Nodes by source, depth-first.
	 *
	 * @return array<string, array{label: string, items: array<array-key, array<string, mixed>>}>
	 */
	protected function grouped(): array {
		return ( new AdminBarNodeGrouper() )->group_nodes( Options::get_discovered_adminbar() );
	}
}
