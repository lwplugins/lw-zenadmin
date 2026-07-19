<?php
/**
 * Tests for MenuManager visibility resolution and the protected-item guard.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Features\MenuManager;
use LightweightPlugins\ZenAdmin\Options;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\MenuManager
 */
final class MenuManagerTest extends MonkeyTestCase {

	protected function tearDown(): void {
		unset( $GLOBALS['menu'], $GLOBALS['submenu'] );
		parent::tearDown();
	}

	/**
	 * @dataProvider provide_visibility_cases
	 *
	 * @param array<string>|false $settings Saved settings passed to is_menu_visible().
	 */
	public function test_is_menu_visible_resolves_from_saved_settings(
		$settings,
		string $slug,
		bool $expected
	): void {
		$this->assertSame( $expected, MenuManager::is_menu_visible( $slug, $settings ) );
	}

	/**
	 * @return array<string, array{0: array<string>|false, 1: string, 2: bool}>
	 */
	public static function provide_visibility_cases(): array {
		return [
			'never saved -> visible by default'  => [ false, 'tools.php', true ],
			'saved empty -> hidden'              => [ [], 'tools.php', false ],
			'saved and present -> visible'       => [ [ 'tools.php' ], 'tools.php', true ],
			'saved but different slug -> hidden' => [ [ 'edit.php' ], 'tools.php', false ],
			'submenu key matched exactly'        => [ [ 'tools.php::import.php' ], 'tools.php::import.php', true ],
		];
	}

	/**
	 * The pure is_menu_visible() helper has no notion of "protected" at all --
	 * on its own it reports a protected slug as hidden once settings are
	 * saved without it. The actual lock-out guard lives in filter_menus(),
	 * which skips the visibility check entirely for protected slugs. This
	 * test exercises that real code path (not a re-implementation of it) to
	 * prove a protected item survives even when the saved settings list is
	 * empty (i.e. "hide everything").
	 */
	public function test_filter_menus_keeps_a_protected_item_visible_when_settings_hide_everything(): void {
		Functions\when( 'add_action' )->justReturn( null );
		Functions\when( 'wp_strip_all_tags' )->alias( static fn ( $text ) => $text );
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				if ( Options::MENU_SETTINGS === $name ) {
					// Explicitly saved: nothing is visible.
					return [];
				}
				return $default;
			}
		);
		Functions\when( 'update_option' )->justReturn( true );

		Functions\expect( 'remove_menu_page' )->once()->with( 'tools.php' );

		$GLOBALS['menu']    = [
			[ 'Dashboard', 'read', 'index.php', '', '', '', '' ],
			[ 'Settings', 'manage_options', 'options-general.php', '', '', '', 'dashicons-admin-settings' ],
			[ 'Tools', 'manage_options', 'tools.php', '', '', '', 'dashicons-admin-tools' ],
		];
		$GLOBALS['submenu'] = [];

		// filter_menus() is void; reaching this assertion (instead of an
		// uncaught error from the remove_menu_page expectation above firing
		// with the wrong argument) confirms the run completed cleanly.
		$this->assertNull( ( new MenuManager() )->filter_menus() );
	}
}
