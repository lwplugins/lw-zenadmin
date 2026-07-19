<?php
/**
 * Minimal WP_Admin_Bar test double.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

if ( ! class_exists( 'WP_Admin_Bar' ) ) {
	/**
	 * Stand-in for WordPress core's WP_Admin_Bar.
	 *
	 * Exposes only what Features\AdminBarManager::filter_nodes() touches:
	 * get_nodes() and remove_node(). Seeded nodes and removed IDs are plain
	 * arrays so a test can assert on them directly.
	 */
	class WP_Admin_Bar {

		/**
		 * Seeded nodes.
		 *
		 * @var array<int, object>
		 */
		private array $nodes = [];

		/**
		 * IDs passed to remove_node().
		 *
		 * @var array<int, string>
		 */
		private array $removed_ids = [];

		/**
		 * Seed the nodes returned by get_nodes().
		 *
		 * @param array<int, object> $nodes Nodes, each with id/title/parent.
		 * @return void
		 */
		public function seed_nodes( array $nodes ): void {
			$this->nodes = $nodes;
		}

		/**
		 * Mimic WP_Admin_Bar::get_nodes().
		 *
		 * @return array<int, object>
		 */
		public function get_nodes(): array {
			return $this->nodes;
		}

		/**
		 * Mimic WP_Admin_Bar::remove_node().
		 *
		 * @param string $id Node ID.
		 * @return void
		 */
		public function remove_node( $id ): void {
			$this->removed_ids[] = $id;
		}

		/**
		 * IDs that were passed to remove_node(), in call order.
		 *
		 * @return array<int, string>
		 */
		public function get_removed_ids(): array {
			return $this->removed_ids;
		}
	}
}
