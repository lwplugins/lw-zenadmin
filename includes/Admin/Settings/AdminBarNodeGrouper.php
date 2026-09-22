<?php
/**
 * Admin Bar Node Grouper.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

use LightweightPlugins\ZenAdmin\Features\Data\CoreAdminBarItems;

/**
 * Turns the flat discovered admin bar node map into grouped, ordered rows.
 *
 * WordPress nests admin bar nodes to arbitrary depth (top-secondary >
 * my-account > user-actions > user-info), so the hierarchy is walked
 * depth-first: every node is emitted directly after its parent, in the
 * group of its root node, annotated with its depth. Every discovered node
 * is emitted exactly once -- a node that never gets a row never gets a
 * checkbox, and a save would then silently hide it.
 */
final class AdminBarNodeGrouper {

	/**
	 * Group nodes by source, each subtree flattened depth-first.
	 *
	 * @param array<array-key, array<string, mixed>> $discovered All discovered nodes (node ID => data).
	 * @return array<string, array{label: string, items: array<array-key, array<string, mixed>>}> Items carry title, parent, is_sub and depth.
	 */
	public function group_nodes( array $discovered ): array {
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

		[ $roots, $children ] = $this->split_roots( $discovered );
		$emitted              = [];

		// Roots first. Then any node still not emitted -- only possible when
		// its parent chain loops (a > b > a) and never reaches a root --
		// starts a subtree of its own instead of being silently dropped.
		foreach ( array_merge( $roots, array_map( 'strval', array_keys( $discovered ) ) ) as $start_id ) {
			$group = CoreAdminBarItems::get_group( $start_id );

			foreach ( $this->flatten_subtree( $start_id, 0, $children, $emitted ) as $node_id => $depth ) {
				$groups[ $group ]['items'][ $node_id ] = $this->build_row( $discovered[ $node_id ], $depth );
			}
		}

		return $groups;
	}

	/**
	 * Split nodes into roots and a parent => children map, in discovery order.
	 *
	 * A node is a root when it has no parent or its parent was never
	 * discovered (e.g. a node whose parent has since disappeared).
	 *
	 * @param array<array-key, array<string, mixed>> $discovered All discovered nodes.
	 * @return array{0: array<int, string>, 1: array<array-key, array<int, string>>}
	 */
	private function split_roots( array $discovered ): array {
		$roots    = [];
		$children = [];

		foreach ( $discovered as $node_id => $data ) {
			$node_id = (string) $node_id;
			$parent  = $this->parent_of( $data );

			// array_key_exists, not isset: a null entry in the stored option
			// still means the parent was discovered.
			if ( '' === $parent || ! array_key_exists( $parent, $discovered ) ) {
				$roots[] = $node_id;
			} else {
				$children[ $parent ][] = $node_id;
			}
		}

		return [ $roots, $children ];
	}

	/**
	 * Flatten a node and its descendants depth-first.
	 *
	 * The shared $emitted set makes every node appear once and stops a
	 * parent cycle from recursing forever.
	 *
	 * @param string                               $node_id  Subtree root.
	 * @param int                                  $depth    Depth of $node_id.
	 * @param array<array-key, array<int, string>> $children Parent => children map.
	 * @param array<string, true>                  $emitted  Already emitted node IDs (by reference).
	 * @return array<array-key, int> Node ID => depth, in render order.
	 */
	private function flatten_subtree( string $node_id, int $depth, array $children, array &$emitted ): array {
		if ( isset( $emitted[ $node_id ] ) ) {
			return [];
		}

		$emitted[ $node_id ] = true;
		$rows                = [ $node_id => $depth ];

		foreach ( $children[ $node_id ] ?? [] as $child_id ) {
			$rows += $this->flatten_subtree( $child_id, $depth + 1, $children, $emitted );
		}

		return $rows;
	}

	/**
	 * Build the row data for one node.
	 *
	 * @param array<string, mixed> $data  Discovered node data.
	 * @param int                  $depth Depth in the hierarchy (0 = top level).
	 * @return array<string, mixed>
	 */
	private function build_row( array $data, int $depth ): array {
		$data['parent'] = $this->parent_of( $data );
		$data['is_sub'] = $depth > 0;
		$data['depth']  = $depth;

		return $data;
	}

	/**
	 * Normalised parent ID of a node ('' for none).
	 *
	 * WP_Admin_Bar defaults a missing parent to `false`, which discovery
	 * stores as-is.
	 *
	 * @param array<string, mixed> $data Discovered node data.
	 * @return string
	 */
	private function parent_of( array $data ): string {
		$parent = $data['parent'] ?? '';

		return is_string( $parent ) || is_int( $parent ) ? (string) $parent : '';
	}
}
