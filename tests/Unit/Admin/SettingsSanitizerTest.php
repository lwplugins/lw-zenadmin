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
		Functions\when( 'sanitize_key' )->alias( static fn ( $key ) => strtolower( (string) $key ) );
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

	public function test_sanitize_widget_settings_returns_empty_array_for_empty_submission(): void {
		$this->assertSame( [], SettingsSanitizer::sanitize_widget_settings( [] ) );
	}

	public function test_sanitize_widget_settings_sanitizes_and_stringifies_each_id(): void {
		$result = SettingsSanitizer::sanitize_widget_settings( [ 'DASHBOARD_Widget', 42 ] );

		$this->assertSame( [ 'dashboard_widget', '42' ], $result );
	}

	/**
	 * A hand-crafted POST can nest an array under lw_zenadmin_widgets[]. Casting
	 * one to string raises "Array to string conversion" and persists "array".
	 */
	public function test_sanitize_widget_settings_skips_non_scalar_input(): void {
		$result = SettingsSanitizer::sanitize_widget_settings( [ 'dashboard_activity', [ 'nested' ] ] );

		$this->assertSame( [ 'dashboard_activity' ], $result );
	}

	public function test_sanitize_menu_settings_returns_empty_array_for_empty_submission(): void {
		$this->assertSame( [], SettingsSanitizer::sanitize_menu_settings( [] ) );
	}

	public function test_sanitize_menu_settings_coerces_non_string_slugs_to_string(): void {
		$result = SettingsSanitizer::sanitize_menu_settings( [ 123 ] );

		$this->assertSame( [ '123' ], $result );
	}

	public function test_sanitize_adminbar_settings_returns_empty_array_for_empty_submission(): void {
		$this->assertSame( [], SettingsSanitizer::sanitize_adminbar_settings( [] ) );
	}

	public function test_sanitize_adminbar_settings_coerces_non_string_node_ids_to_string(): void {
		$result = SettingsSanitizer::sanitize_adminbar_settings( [ 7 ] );

		$this->assertSame( [ '7' ], $result );
	}
}
