<?php
/**
 * Tests for AdminBarManager visibility resolution and the protected-item guard.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Features\AdminBarManager;
use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;
use WP_Admin_Bar;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\AdminBarManager
 */
final class AdminBarManagerTest extends MonkeyTestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['wp_admin_bar'] );
		parent::tearDown();
	}

	/**
	 * @dataProvider provide_visibility_cases
	 *
	 * @param array<string>|false $settings Saved settings passed to is_node_visible().
	 */
	public function test_is_node_visible_resolves_from_saved_settings(
		$settings,
		string $node_id,
		bool $expected
	): void {
		$this->assertSame( $expected, AdminBarManager::is_node_visible( $node_id, $settings ) );
	}

	/**
	 * @return array<string, array{0: array<string>|false, 1: string, 2: bool}>
	 */
	public static function provide_visibility_cases(): array {
		return [
			'never saved -> visible by default'  => [ false, 'search', true ],
			'saved empty -> hidden'              => [ [], 'search', false ],
			'saved and present -> visible'       => [ [ 'search' ], 'search', true ],
			'saved but different node -> hidden' => [ [ 'comments' ], 'search', false ],
		];
	}

	/**
	 * Mirrors the MenuManager lock-out test: is_node_visible() alone has no
	 * notion of "protected", so this exercises filter_nodes() itself -- the
	 * real place the guard lives -- to prove a protected node (my-account)
	 * survives even when the saved settings say to hide everything.
	 */
	public function test_filter_nodes_keeps_a_protected_node_visible_when_settings_hide_everything(): void {
		Functions\when( 'add_action' )->justReturn( null );
		Functions\when( 'wp_strip_all_tags' )->alias( static fn ( $text ) => $text );
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				if ( Options::ADMINBAR_SETTINGS === $name ) {
					// Explicitly saved: nothing is visible.
					return [];
				}
				return $default;
			}
		);
		Functions\when( 'update_option' )->justReturn( true );

		$admin_bar = new WP_Admin_Bar();
		$admin_bar->seed_nodes(
			[
				(object) [
					'id'     => 'my-account',
					'title'  => 'Howdy',
					'parent' => '',
				],
				(object) [
					'id'     => 'search',
					'title'  => 'Search',
					'parent' => '',
				],
			]
		);

		$GLOBALS['wp_admin_bar'] = $admin_bar;

		( new AdminBarManager() )->filter_nodes();

		$this->assertSame( [ 'search' ], $admin_bar->get_removed_ids() );
	}
}
