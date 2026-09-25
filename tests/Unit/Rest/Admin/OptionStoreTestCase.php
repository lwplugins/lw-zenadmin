<?php
/**
 * Base case for the admin REST tests: options live in an in-memory array.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Rest\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * Stubs get_option()/update_option() over $this->store, plus the few
 * formatting helpers the REST layer calls.
 */
abstract class OptionStoreTestCase extends MonkeyTestCase {

	/**
	 * Option name => value.
	 *
	 * @var array<string, mixed>
	 */
	protected array $store = [];

	/**
	 * Option names written during the test, in order.
	 *
	 * @var array<int, string>
	 */
	protected array $written = [];

	protected function setUp(): void {
		parent::setUp();
		Options::clear_cache();
		Functions\stubTranslationFunctions();

		Functions\when( 'get_option' )->alias(
			fn ( $name, $fallback = false ) => array_key_exists( $name, $this->store ) ? $this->store[ $name ] : $fallback
		);
		Functions\when( 'update_option' )->alias(
			function ( $name, $value ): bool {
				$this->store[ $name ] = $value;
				$this->written[]      = $name;
				return true;
			}
		);
		Functions\when( 'wp_parse_args' )->alias(
			static fn ( $args, $defaults = [] ): array => array_merge( (array) $defaults, (array) $args )
		);
		Functions\when( 'sanitize_text_field' )->alias( static fn ( $value ): string => trim( (string) $value ) );
		Functions\when( 'admin_url' )->alias( static fn ( $path = '' ): string => 'https://example.test/wp-admin/' . $path );
	}

	protected function tearDown(): void {
		Options::clear_cache();
		parent::tearDown();
	}

	/**
	 * Seed a typical discovered state.
	 *
	 * @return void
	 */
	protected function seed_discovery(): void {
		$this->store[ Options::DISCOVERED_WIDGETS ]  = [
			'dashboard_activity' => 'Activity',
			'wc_admin_dashboard' => 'WooCommerce Setup',
			'acme_stats'         => 'Acme Stats',
		];
		$this->store[ Options::DISCOVERED_MENUS ]    = [
			'index.php'             => [ 'title' => 'Dashboard', 'icon' => '' ],
			'tools.php'             => [ 'title' => 'Tools', 'icon' => '' ],
			'tools.php::import.php' => [ 'title' => 'Import', 'icon' => '' ],
			'lw-plugins'            => [ 'title' => 'LW Plugins', 'icon' => '' ],
			'acme'                  => [ 'title' => 'Acme', 'icon' => '' ],
		];
		$this->store[ Options::DISCOVERED_ADMINBAR ] = [
			'wp-logo'       => [ 'title' => 'About WordPress', 'parent' => false ],
			'top-secondary' => [ 'title' => '', 'parent' => false ],
			'my-account'    => [ 'title' => 'Howdy, admin', 'parent' => 'top-secondary' ],
			'logout'        => [ 'title' => 'Log Out', 'parent' => 'my-account' ],
			'acme-bar'      => [ 'title' => 'Acme', 'parent' => false ],
		];
	}
}
