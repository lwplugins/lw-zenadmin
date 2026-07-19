<?php
/**
 * Tests for the CoreAdminBarItems registry, including the lock-out guard.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features\Data;

use LightweightPlugins\ZenAdmin\Features\Data\CoreAdminBarItems;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\Data\CoreAdminBarItems
 */
final class CoreAdminBarItemsTest extends TestCase {

	/**
	 * @dataProvider provide_protected_ids
	 */
	public function test_is_protected_true_for_nodes_that_must_never_be_hidden( string $node_id ): void {
		$this->assertTrue( CoreAdminBarItems::is_protected( $node_id ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function provide_protected_ids(): array {
		return [
			'my account'    => [ 'my-account' ],
			'top secondary' => [ 'top-secondary' ],
			'user actions'  => [ 'user-actions' ],
			'logout'        => [ 'logout' ],
		];
	}

	/**
	 * @dataProvider provide_unprotected_ids
	 */
	public function test_is_protected_false_for_ordinary_nodes( string $node_id ): void {
		$this->assertFalse( CoreAdminBarItems::is_protected( $node_id ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function provide_unprotected_ids(): array {
		return [
			'core but not protected (search)'  => [ 'search' ],
			'core but not protected (wp-logo)' => [ 'wp-logo' ],
			'third-party node'                 => [ 'some-plugin-node' ],
		];
	}

	/**
	 * @dataProvider provide_group_cases
	 */
	public function test_get_group_classifies_a_node( string $node_id, string $expected_group ): void {
		$this->assertSame( $expected_group, CoreAdminBarItems::get_group( $node_id ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_group_cases(): array {
		return [
			'core node'            => [ 'my-account', 'core' ],
			'woocommerce prefixed' => [ 'wc-pending-orders', 'woocommerce' ],
			'woocommerce bare'     => [ 'woocommerce', 'woocommerce' ],
			'third-party node'     => [ 'some-plugin-node', 'third_party' ],
		];
	}
}
