<?php
/**
 * Tests for the SettingsSaver write-path guard clauses.
 *
 * Only the three early-return guards are covered here. maybe_save() calls
 * exit() unconditionally once all three guards pass (after wp_safe_redirect),
 * so the success path -- where the actual sanitization in save_options(),
 * save_widget_settings(), save_menu_settings(), and save_adminbar_settings()
 * runs -- cannot be exercised through the public API without terminating the
 * PHPUnit process. See the report for this as a flagged testability issue.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Admin\SettingsSaver;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Admin\SettingsSaver
 */
final class SettingsSaverTest extends MonkeyTestCase {

	protected function tearDown(): void {
		$_POST = [];
		parent::tearDown();
	}

	public function test_does_nothing_when_the_save_flag_is_absent(): void {
		$_POST = [];

		Functions\expect( 'wp_verify_nonce' )->never();

		// maybe_save() is void; reaching this line at all (instead of the
		// unconditional exit() on the success path) is itself proof the
		// early return fired.
		$this->assertNull( SettingsSaver::maybe_save() );
	}

	public function test_does_nothing_when_the_nonce_is_missing(): void {
		$_POST = [ 'lw_zenadmin_save' => '1' ];

		Functions\expect( 'current_user_can' )->never();

		$this->assertNull( SettingsSaver::maybe_save() );
	}

	public function test_does_nothing_when_the_nonce_is_invalid(): void {
		$_POST = [
			'lw_zenadmin_save'   => '1',
			'_lw_zenadmin_nonce' => 'bad-nonce',
		];

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( false );
		Functions\expect( 'current_user_can' )->never();

		$this->assertNull( SettingsSaver::maybe_save() );
	}

	public function test_does_nothing_when_the_user_lacks_manage_options(): void {
		$_POST = [
			'lw_zenadmin_save'   => '1',
			'_lw_zenadmin_nonce' => 'good-nonce',
		];

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'update_option' )->never();

		$this->assertNull( SettingsSaver::maybe_save() );
	}
}
