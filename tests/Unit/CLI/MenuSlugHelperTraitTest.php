<?php
/**
 * Tests for MenuSlugHelperTrait (CLI slug/group resolution).
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\CLI;

use LightweightPlugins\ZenAdmin\CLI\MenuSlugHelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\CLI\MenuSlugHelperTrait
 */
final class MenuSlugHelperTraitTest extends TestCase {

	/**
	 * The trait only has private methods, so it is exercised through a tiny
	 * local class that uses it and exposes thin public wrappers -- the
	 * normal way to unit test a trait without reflection.
	 */
	private function make_helper(): object {
		return new class() {
			use MenuSlugHelperTrait;

			public function call_get_base_slug( string $slug ): string {
				return $this->get_base_slug( $slug );
			}

			public function call_get_slug_group( string $slug ): string {
				return $this->get_slug_group( $slug );
			}
		};
	}

	/**
	 * @dataProvider provide_base_slug_cases
	 */
	public function test_get_base_slug_strips_the_submenu_suffix( string $slug, string $expected ): void {
		$this->assertSame( $expected, $this->make_helper()->call_get_base_slug( $slug ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_base_slug_cases(): array {
		return [
			'top-level slug is unchanged'       => [ 'tools.php', 'tools.php' ],
			'submenu key returns the parent'    => [ 'tools.php::import.php', 'tools.php' ],
			// explode() with a limit of 2 keeps only the first "::" as the
			// split point, so a key with multiple "::" still resolves to a
			// single, first-segment base slug.
			'multiple "::" keeps first segment' => [ 'a::b::c', 'a' ],
		];
	}

	public function test_get_slug_group_classifies_a_top_level_slug(): void {
		$this->assertSame( 'core', $this->make_helper()->call_get_slug_group( 'index.php' ) );
	}

	public function test_get_slug_group_classifies_a_submenu_key_by_its_parent(): void {
		$this->assertSame( 'lw_plugins', $this->make_helper()->call_get_slug_group( 'lw-seo::settings' ) );
	}
}
