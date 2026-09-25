<?php
/**
 * Tests for the admin REST route registration.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Rest\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Rest\Admin\Routes;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Rest\Admin\Routes
 */
final class RoutesTest extends MonkeyTestCase {

	public function test_the_settings_route_needs_manage_options_for_every_method(): void {
		$routes = [];
		Functions\when( 'register_rest_route' )->alias(
			function ( $ns, $path, $endpoints ) use ( &$routes ): void {
				$routes[ $ns . $path ] = $endpoints;
			}
		);

		Routes::register_routes();

		$this->assertSame( [ 'lw-zenadmin/v1/admin/settings' ], array_keys( $routes ) );
		foreach ( $routes['lw-zenadmin/v1/admin/settings'] as $endpoint ) {
			$this->assertSame( [ Routes::class, 'can_manage' ], $endpoint['permission_callback'] );
		}
	}

	/**
	 * @dataProvider provide_capabilities
	 *
	 * @param bool $can Whether the user has manage_options.
	 */
	public function test_can_manage_follows_manage_options( bool $can ): void {
		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( $can );

		$this->assertSame( $can, Routes::can_manage() );
	}

	/**
	 * @return array<string, array{0: bool}>
	 */
	public static function provide_capabilities(): array {
		return [
			'administrator' => [ true ],
			'editor'        => [ false ],
		];
	}
}
