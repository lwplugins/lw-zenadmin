<?php
/**
 * Tests for the Options class (defaults, caching, and the "never saved" contract).
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Options;

/**
 * @covers \LightweightPlugins\ZenAdmin\Options
 */
final class OptionsTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Options::clear_cache();
	}

	protected function tearDown(): void {
		Options::clear_cache();
		parent::tearDown();
	}

	public function test_get_defaults_returns_the_four_feature_toggles(): void {
		$this->assertSame(
			[
				'notices_enabled'  => true,
				'widgets_enabled'  => true,
				'menu_enabled'     => false,
				'adminbar_enabled' => false,
			],
			Options::get_defaults()
		);
	}

	public function test_get_all_falls_back_to_defaults_when_nothing_saved(): void {
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'wp_parse_args' )->alias(
			static fn ( $args, $defaults = [] ): array => array_merge( (array) $defaults, (array) $args )
		);

		$this->assertSame( Options::get_defaults(), Options::get_all() );
	}

	public function test_get_all_backfills_a_key_missing_from_older_saved_data(): void {
		// Simulates data written by a version of the plugin that predates the
		// "adminbar_enabled" toggle: the saved array simply doesn't have it.
		Functions\when( 'get_option' )->justReturn( [ 'menu_enabled' => true ] );
		Functions\when( 'wp_parse_args' )->alias(
			static fn ( $args, $defaults = [] ): array => array_merge( (array) $defaults, (array) $args )
		);

		$options = Options::get_all();

		$this->assertTrue( $options['menu_enabled'] );
		$this->assertFalse( $options['adminbar_enabled'] );
	}

	public function test_get_returns_null_for_a_completely_unknown_key(): void {
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'wp_parse_args' )->alias(
			static fn ( $args, $defaults = [] ): array => array_merge( (array) $defaults, (array) $args )
		);

		$this->assertNull( Options::get( 'no_such_option' ) );
	}

	public function test_save_persists_via_update_option_and_returns_its_result(): void {
		Functions\expect( 'update_option' )
			->once()
			->with( Options::OPTION_NAME, [ 'notices_enabled' => false ] )
			->andReturn( true );

		$this->assertTrue( Options::save( [ 'notices_enabled' => false ] ) );
	}

	public function test_save_updates_the_in_memory_cache_so_get_all_skips_get_option(): void {
		Functions\when( 'update_option' )->justReturn( true );
		Options::save( [ 'notices_enabled' => false ] );

		// If get_all() re-read from the DB instead of the cache it would need
		// get_option() again; asserting it is never called proves the cache
		// (not a fresh lookup) is what backs the next get_all() call.
		Functions\expect( 'get_option' )->never();

		$this->assertSame( [ 'notices_enabled' => false ], Options::get_all() );
	}

	/**
	 * @dataProvider provide_never_saved_visibility_getters
	 */
	public function test_visibility_getters_return_false_sentinel_when_never_saved( callable $getter ): void {
		Functions\when( 'get_option' )->alias(
			static fn ( string $name, $default = false ) => $default
		);

		$this->assertFalse( $getter() );
	}

	/**
	 * @return array<string, array{0: callable}>
	 */
	public static function provide_never_saved_visibility_getters(): array {
		return [
			'widget settings'   => [ [ Options::class, 'get_widget_settings' ] ],
			'menu settings'     => [ [ Options::class, 'get_menu_settings' ] ],
			'adminbar settings' => [ [ Options::class, 'get_adminbar_settings' ] ],
		];
	}

	public function test_delete_all_removes_every_stored_option(): void {
		// Seven distinct option keys back this plugin; uninstall must clear
		// all of them, not just the main settings blob.
		Functions\expect( 'delete_option' )->times( 7 );

		$this->assertNull( Options::delete_all() );
	}
}
