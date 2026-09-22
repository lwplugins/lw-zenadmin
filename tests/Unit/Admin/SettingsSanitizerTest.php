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

	/**
	 * Issue #4, defence in depth: the visible list is rebuilt from the
	 * checked boxes, so a discovered node the submitted form never rendered
	 * must keep its saved state instead of being hidden by the save.
	 *
	 * @dataProvider provide_unrendered_node_cases
	 *
	 * @param array<int, string>  $checked  Checked node IDs.
	 * @param array<int, string>  $rendered Node IDs the form rendered.
	 * @param array<string>|false $previous Saved visible list.
	 * @param array<int, string>  $expected Visible list to persist.
	 */
	public function test_keep_unrendered_adminbar_nodes_preserves_the_state_of_nodes_the_form_never_showed(
		array $checked,
		array $rendered,
		array|false $previous,
		array $expected
	): void {
		$discovered = [ 'search', 'comments', 'user-info' ];

		$result = SettingsSanitizer::keep_unrendered_adminbar_nodes( $checked, $rendered, $discovered, $previous );

		$this->assertSame( $expected, $result );
	}

	/**
	 * @return array<string, array{0: array<int, string>, 1: array<int, string>, 2: array<string>|false, 3: array<int, string>}>
	 */
	public static function provide_unrendered_node_cases(): array {
		return [
			'unrendered, saved visible -> stays visible'  => [ [ 'search' ], [ 'search', 'comments' ], [ 'search', 'comments', 'user-info' ], [ 'search', 'user-info' ] ],
			'unrendered, never saved -> stays visible'    => [ [ 'search' ], [ 'search', 'comments' ], false, [ 'search', 'user-info' ] ],
			'unrendered, saved hidden -> stays hidden'    => [ [ 'search' ], [ 'search', 'comments' ], [ 'search', 'comments' ], [ 'search' ] ],
			'rendered and unchecked -> hidden'            => [ [], [ 'search', 'comments', 'user-info' ], false, [] ],
			'rendered and checked -> listed once'         => [ [ 'user-info' ], [ 'search', 'comments', 'user-info' ], [ 'user-info' ], [ 'user-info' ] ],
			'no table rendered at all -> nothing changes' => [ [], [], [ 'comments' ], [ 'comments' ] ],
		];
	}

	public function test_keep_unrendered_adminbar_nodes_skips_non_scalar_rendered_entries(): void {
		$result = SettingsSanitizer::keep_unrendered_adminbar_nodes( [], [ [ 'search' ], 'comments' ], [ 'comments' ], [ 'comments' ] );

		$this->assertSame( [], $result );
	}
}
