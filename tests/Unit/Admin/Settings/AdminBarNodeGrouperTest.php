<?php
/**
 * Tests for AdminBarNodeGrouper::group_nodes().
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Admin\Settings;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Admin\Settings\AdminBarNodeGrouper;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Admin\Settings\AdminBarNodeGrouper
 */
final class AdminBarNodeGrouperTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( '__' )->returnArg( 1 );
	}

	/**
	 * The hierarchy from issue #4: my-account > user-actions > user-info /
	 * edit-profile / logout, plus an unrelated top-level node.
	 *
	 * @return array<string, array{title: string, parent: string}>
	 */
	private static function issue_hierarchy(): array {
		return [
			'my-account'   => [
				'title'  => 'Howdy, admin',
				'parent' => '',
			],
			'user-actions' => [
				'title'  => 'user-actions',
				'parent' => 'my-account',
			],
			'user-info'    => [
				'title'  => 'admin',
				'parent' => 'user-actions',
			],
			'edit-profile' => [
				'title'  => 'Edit Profile',
				'parent' => 'user-actions',
			],
			'logout'       => [
				'title'  => 'Log Out',
				'parent' => 'user-actions',
			],
			'search'       => [
				'title'  => 'Search',
				'parent' => '',
			],
		];
	}

	/**
	 * Every item across all groups, in rendered order.
	 *
	 * @param array<string, array{label: string, items: array<array-key, array<string, mixed>>}> $groups Grouped nodes.
	 * @return array<array-key, array<string, mixed>>
	 */
	private static function all_items( array $groups ): array {
		$items = [];
		foreach ( $groups as $group ) {
			foreach ( $group['items'] as $node_id => $data ) {
				$items[ $node_id ] = $data;
			}
		}
		return $items;
	}

	/**
	 * Issue #4 reproduction: the depth-2 nodes were discovered but never
	 * emitted, so they never got a checkbox.
	 */
	public function test_lists_depth_two_nodes_of_the_my_account_menu(): void {
		$groups = ( new AdminBarNodeGrouper() )->group_nodes( self::issue_hierarchy() );

		$this->assertArrayHasKey( 'user-info', $groups['core']['items'] );
		$this->assertArrayHasKey( 'edit-profile', $groups['core']['items'] );
		$this->assertArrayHasKey( 'logout', $groups['core']['items'] );
	}

	public function test_orders_every_node_depth_first_directly_after_its_parent(): void {
		$groups = ( new AdminBarNodeGrouper() )->group_nodes( self::issue_hierarchy() );

		$this->assertSame(
			[ 'my-account', 'user-actions', 'user-info', 'edit-profile', 'logout', 'search' ],
			array_keys( $groups['core']['items'] )
		);
	}

	/**
	 * @dataProvider provide_depths
	 */
	public function test_annotates_each_node_with_its_depth_and_sub_flag( string $node_id, int $depth, bool $is_sub ): void {
		$items = ( new AdminBarNodeGrouper() )->group_nodes( self::issue_hierarchy() )['core']['items'];

		$this->assertSame(
			[
				'depth'  => $depth,
				'is_sub' => $is_sub,
			],
			[
				'depth'  => $items[ $node_id ]['depth'],
				'is_sub' => $items[ $node_id ]['is_sub'],
			]
		);
	}

	/**
	 * @return array<string, array{0: string, 1: int, 2: bool}>
	 */
	public static function provide_depths(): array {
		return [
			'top level'  => [ 'my-account', 0, false ],
			'child'      => [ 'user-actions', 1, true ],
			'grandchild' => [ 'user-info', 2, true ],
		];
	}

	/**
	 * The real WordPress tree (wp-includes/admin-bar.php): my-account hangs
	 * off the top-secondary group, so user-info / logout sit at depth 3.
	 * Root nodes are discovered with parent `false` (WP_Admin_Bar's default).
	 */
	public function test_lists_the_real_wordpress_account_tree_down_to_depth_three(): void {
		$discovered = [
			'top-secondary' => [
				'title'  => 'top-secondary',
				'parent' => false,
			],
			'my-account'    => [
				'title'  => 'Howdy, admin',
				'parent' => 'top-secondary',
			],
			'user-actions'  => [
				'title'  => 'user-actions',
				'parent' => 'my-account',
			],
			'user-info'     => [
				'title'  => 'admin',
				'parent' => 'user-actions',
			],
			'logout'        => [
				'title'  => 'Log Out',
				'parent' => 'user-actions',
			],
			'search'        => [
				'title'  => 'Search',
				'parent' => 'top-secondary',
			],
		];

		$items = ( new AdminBarNodeGrouper() )->group_nodes( $discovered )['core']['items'];

		$this->assertSame(
			[ 'top-secondary', 'my-account', 'user-actions', 'user-info', 'logout', 'search' ],
			array_keys( $items )
		);
		$this->assertSame( 3, $items['user-info']['depth'] );
		$this->assertSame( '', $items['top-secondary']['parent'] );
	}

	public function test_a_node_whose_parent_was_never_discovered_is_listed_top_level(): void {
		$discovered = [
			'acme-child' => [
				'title'  => 'Acme Child',
				'parent' => 'acme-gone',
			],
		];

		$items = ( new AdminBarNodeGrouper() )->group_nodes( $discovered )['third_party']['items'];

		$this->assertArrayHasKey( 'acme-child', $items );
		$this->assertFalse( $items['acme-child']['is_sub'] );
	}

	/**
	 * A parent loop never reaches a root. Its nodes must still be listed,
	 * each exactly once, and the walk must terminate.
	 */
	public function test_nodes_in_a_parent_cycle_are_each_listed_exactly_once(): void {
		$discovered = [
			'acme-a'    => [
				'title'  => 'A',
				'parent' => 'acme-b',
			],
			'acme-b'    => [
				'title'  => 'B',
				'parent' => 'acme-a',
			],
			'acme-self' => [
				'title'  => 'Self',
				'parent' => 'acme-self',
			],
		];

		$items = self::all_items( ( new AdminBarNodeGrouper() )->group_nodes( $discovered ) );

		$this->assertSame( [ 'acme-a', 'acme-b', 'acme-self' ], array_keys( $items ) );
		$this->assertSame( 0, $items['acme-a']['depth'] );
		$this->assertSame( 1, $items['acme-b']['depth'] );
	}

	public function test_every_discovered_node_is_listed_exactly_once(): void {
		$discovered = self::issue_hierarchy() + [
			'wc-child' => [
				'title'  => 'Store',
				'parent' => 'search',
			],
			'orphan'   => [
				'title'  => 'Orphan',
				'parent' => 'missing',
			],
		];

		$listed = [];
		foreach ( ( new AdminBarNodeGrouper() )->group_nodes( $discovered ) as $group ) {
			$listed = array_merge( $listed, array_keys( $group['items'] ) );
		}

		$this->assertEqualsCanonicalizing( array_keys( $discovered ), $listed );
	}

	public function test_descendants_stay_in_their_root_nodes_group(): void {
		$discovered = [
			'site-name'  => [
				'title'  => 'My Site',
				'parent' => '',
			],
			'appearance' => [
				'title'  => 'appearance',
				'parent' => 'site-name',
			],
			'wc-themes'  => [
				'title'  => 'Store themes',
				'parent' => 'appearance',
			],
		];

		$groups = ( new AdminBarNodeGrouper() )->group_nodes( $discovered );

		$this->assertSame( [ 'site-name', 'appearance', 'wc-themes' ], array_keys( $groups['core']['items'] ) );
		$this->assertSame( [], $groups['woocommerce']['items'] );
	}

	/**
	 * PHP turns a numeric-string array key into an int; with strict types
	 * that must not blow up the grouping (and with it the whole tab).
	 */
	public function test_a_numeric_node_id_is_grouped_without_a_type_error(): void {
		$discovered = [
			'123' => [
				'title'  => 'Numeric',
				'parent' => '',
			],
			'456' => [
				'title'  => 'Numeric child',
				'parent' => '123',
			],
		];

		$items = ( new AdminBarNodeGrouper() )->group_nodes( $discovered )['third_party']['items'];

		$this->assertSame( [ 123, 456 ], array_keys( $items ) );
		$this->assertSame( 1, $items[456]['depth'] );
	}
}
