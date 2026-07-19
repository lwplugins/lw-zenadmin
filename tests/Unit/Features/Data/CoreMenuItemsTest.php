<?php
/**
 * Tests for the CoreMenuItems registry, including the lock-out guard.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features\Data;

use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems
 */
final class CoreMenuItemsTest extends TestCase {

	/**
	 * @dataProvider provide_protected_slugs
	 */
	public function test_is_protected_true_for_slugs_that_must_never_be_hidden( string $slug ): void {
		$this->assertTrue( CoreMenuItems::is_protected( $slug ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function provide_protected_slugs(): array {
		return [
			'dashboard'              => [ 'index.php' ],
			'settings'               => [ 'options-general.php' ],
			'plugins'                => [ 'plugins.php' ],
			'lw plugins hub'         => [ 'lw-plugins' ],
			'any lw-prefixed plugin' => [ 'lw-seo' ],
		];
	}

	/**
	 * @dataProvider provide_unprotected_slugs
	 */
	public function test_is_protected_false_for_ordinary_menu_items( string $slug ): void {
		$this->assertFalse( CoreMenuItems::is_protected( $slug ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function provide_unprotected_slugs(): array {
		return [
			'core but not protected' => [ 'tools.php' ],
			'woocommerce'            => [ 'woocommerce' ],
			'third-party plugin'     => [ 'some-random-plugin' ],
		];
	}

	/**
	 * @dataProvider provide_group_cases
	 */
	public function test_get_group_classifies_a_slug( string $slug, string $expected_group ): void {
		$this->assertSame( $expected_group, CoreMenuItems::get_group( $slug ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_group_cases(): array {
		return [
			'core slug'                               => [ 'index.php', 'core' ],
			'woocommerce top-level menu'              => [ 'woocommerce', 'woocommerce' ],
			'woocommerce order post type edit screen' => [ 'edit.php?post_type=shop_order', 'woocommerce' ],
			'lw plugin'                               => [ 'lw-seo', 'lw_plugins' ],
			'unrelated third-party plugin'            => [ 'acme-plugin', 'third_party' ],
			// "edit.php" alone is core, NOT the woocommerce product screen
			// ("edit.php?post_type=product") -- the prefix must not match
			// the bare core slug.
			'bare edit.php is core, not woocommerce'  => [ 'edit.php', 'core' ],
		];
	}
}
