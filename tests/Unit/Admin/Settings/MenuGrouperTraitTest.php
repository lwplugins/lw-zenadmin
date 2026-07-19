<?php
/**
 * Tests for MenuGrouperTrait::group_menus().
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Admin\Settings;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Admin\Settings\MenuGrouperTrait;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Admin\Settings\MenuGrouperTrait
 */
final class MenuGrouperTraitTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( '__' )->returnArg( 1 );
	}

	/**
	 * The trait only has private methods, so it is exercised through a tiny
	 * local class that uses it and exposes a thin public wrapper -- the
	 * normal way to unit test a trait without reflection.
	 */
	private function make_grouper(): object {
		return new class() {
			use MenuGrouperTrait;

			/**
			 * @param array<string, array{title: string, icon: string}> $discovered Discovered menus.
			 * @return array<string, array{label: string, items: array<string, array<string, mixed>>}>
			 */
			public function call_group_menus( array $discovered ): array {
				return $this->group_menus( $discovered );
			}
		};
	}

	public function test_groups_a_core_item_under_wordpress_core(): void {
		$discovered = [
			'index.php' => [
				'title' => 'Dashboard',
				'icon'  => '',
			],
		];

		$groups = $this->make_grouper()->call_group_menus( $discovered );

		$this->assertArrayHasKey( 'index.php', $groups['core']['items'] );
		$this->assertFalse( $groups['core']['items']['index.php']['is_sub'] );
	}

	public function test_groups_an_lw_prefixed_item_under_lw_plugins(): void {
		$discovered = [
			'lw-seo' => [
				'title' => 'LW SEO',
				'icon'  => '',
			],
		];

		$groups = $this->make_grouper()->call_group_menus( $discovered );

		$this->assertArrayHasKey( 'lw-seo', $groups['lw_plugins']['items'] );
	}

	public function test_groups_an_unrecognised_slug_under_third_party(): void {
		$discovered = [
			'acme-plugin' => [
				'title' => 'Acme',
				'icon'  => '',
			],
		];

		$groups = $this->make_grouper()->call_group_menus( $discovered );

		$this->assertArrayHasKey( 'acme-plugin', $groups['third_party']['items'] );
	}

	public function test_nests_a_submenu_under_its_parent_in_the_same_group(): void {
		$discovered = [
			'tools.php'             => [
				'title' => 'Tools',
				'icon'  => '',
			],
			'tools.php::import.php' => [
				'title' => 'Import',
				'icon'  => '',
			],
		];

		$groups = $this->make_grouper()->call_group_menus( $discovered );

		$this->assertArrayHasKey( 'tools.php::import.php', $groups['core']['items'] );
		$this->assertTrue( $groups['core']['items']['tools.php::import.php']['is_sub'] );
	}

	/**
	 * The group_menus() method only attaches a submenu key ("parent::child")
	 * while iterating its PARENT's own top-level entry. If the parent slug is not
	 * itself a key in $discovered -- e.g. discovery data from an older
	 * plugin version, or a parent menu that has since disappeared -- the
	 * child is never visited by that loop and silently vanishes from every
	 * group, instead of falling back to "third_party". See the report: this
	 * is flagged as a suspected bug, not fixed here.
	 */
	public function test_a_submenu_whose_parent_was_never_discovered_is_silently_dropped(): void {
		$discovered = [
			'orphan.php::child.php' => [
				'title' => 'Orphaned Child',
				'icon'  => '',
			],
		];

		$groups = $this->make_grouper()->call_group_menus( $discovered );

		$all_items = array_merge(
			$groups['core']['items'],
			$groups['woocommerce']['items'],
			$groups['lw_plugins']['items'],
			$groups['third_party']['items']
		);

		// TODO: gyanús viselkedés -- szándékos? See report.
		$this->assertArrayNotHasKey( 'orphan.php::child.php', $all_items );
	}
}
