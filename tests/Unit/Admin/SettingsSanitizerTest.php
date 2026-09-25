<?php
/**
 * Tests for SettingsSanitizer's pure settings-transformation methods.
 *
 * Note on the "protected item" guard mentioned in the task brief: menu/
 * admin-bar protection (Dashboard, Settings, Plugins, LW Plugins, My
 * Account, Logout) is enforced in MenuManager::filter_menus()/
 * filter_submenus() and AdminBarManager via CoreMenuItems::is_protected()/
 * CoreAdminBarItems::is_protected() -- those checks run regardless of what
 * is in the saved settings option, so a protected item cannot be hidden
 * even if its slug/id is missing from the persisted "visible" list. The
 * guard does not live in this layer, so no such test is added here; see
 * the report for the same conclusion.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Admin\SettingsSanitizer;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Admin\SettingsSanitizer
 */
final class SettingsSanitizerTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'sanitize_text_field' )->alias( static fn ( $value ) => trim( (string) $value ) );
	}

	public function test_sanitize_options_turns_everything_off_when_nothing_is_submitted(): void {
		$result = SettingsSanitizer::sanitize_options( [] );

		$this->assertSame(
			[
				'notices_enabled'  => false,
				'widgets_enabled'  => false,
				'menu_enabled'     => false,
				'adminbar_enabled' => false,
			],
			$result
		);
	}

	public function test_sanitize_options_drops_unknown_keys_from_the_submission(): void {
		$result = SettingsSanitizer::sanitize_options(
			[
				'notices_enabled' => '1',
				'evil_key'        => '1',
			]
		);

		$this->assertArrayNotHasKey( 'evil_key', $result );
	}

	public function test_sanitize_options_coerces_submitted_values_to_bool(): void {
		$result = SettingsSanitizer::sanitize_options(
			[
				'notices_enabled' => '1',
				'widgets_enabled' => '',
			]
		);

		$this->assertTrue( $result['notices_enabled'] );
		$this->assertFalse( $result['widgets_enabled'] );
	}

	/**
	 * Only the changed items are submitted; every other discovered item keeps
	 * its effective visibility (the classic "rendered marker" rule), so an
	 * item discovered after the screen loaded is never hidden by a save.
	 *
	 * @dataProvider provide_merge_cases
	 *
	 * @param array<string, bool> $changes  Submitted changes.
	 * @param array<int, string>  $visible  Currently visible IDs.
	 * @param array<int, string>  $expected Visible list to persist.
	 */
	public function test_merge_visibility_applies_changes_and_keeps_everything_else( array $changes, array $visible, array $expected ): void {
		$result = SettingsSanitizer::merge_visibility(
			$changes,
			[ 'search', 'comments', 'user-info' ],
			static fn ( string $id ): bool => in_array( $id, $visible, true ),
			static fn (): bool => false
		);

		$this->assertSame( $expected, $result );
	}

	/**
	 * @return array<string, array{0: array<string, bool>, 1: array<int, string>, 2: array<int, string>}>
	 */
	public static function provide_merge_cases(): array {
		return [
			'nothing changed -> current state'     => [ [], [ 'comments' ], [ 'comments' ] ],
			'hide one visible item'                => [ [ 'comments' => false ], [ 'search', 'comments' ], [ 'search' ] ],
			'show one hidden item'                 => [ [ 'user-info' => true ], [ 'search' ], [ 'search', 'user-info' ] ],
			'unmentioned hidden item stays hidden' => [ [ 'search' => true ], [], [ 'search' ] ],
			'showing a visible item lists it once' => [ [ 'search' => true ], [ 'search' ], [ 'search' ] ],
		];
	}

	public function test_merge_visibility_leaves_protected_items_out_of_the_list(): void {
		$result = SettingsSanitizer::merge_visibility(
			[ 'logout' => true ],
			[ 'logout', 'search' ],
			static fn (): bool => true,
			static fn ( string $id ): bool => 'logout' === $id
		);

		$this->assertSame( [ 'search' ], $result );
	}

	public function test_merge_visibility_drops_ids_that_are_no_longer_discovered(): void {
		$result = SettingsSanitizer::merge_visibility(
			[ 'gone' => true ],
			[ 'search' ],
			static fn (): bool => true,
			static fn (): bool => false
		);

		$this->assertSame( [ 'search' ], $result );
	}

	public function test_merge_visibility_stringifies_numeric_ids(): void {
		$result = SettingsSanitizer::merge_visibility(
			[ '7' => true ],
			[ 7 ],
			static fn (): bool => false,
			static fn (): bool => false
		);

		$this->assertSame( [ '7' ], $result );
	}
}
