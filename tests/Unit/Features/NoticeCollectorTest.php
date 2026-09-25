<?php
/**
 * NoticeCollector unit tests: how it works alongside NoticeManager.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features;

use Brain\Monkey\Functions;
use LightweightPlugins\ZenAdmin\Features\NoticeCollector;
use LightweightPlugins\ZenAdmin\Tests\Unit\MonkeyTestCase;

final class NoticeCollectorTest extends MonkeyTestCase {

	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'LW_ZENADMIN_URL' ) ) {
			define( 'LW_ZENADMIN_URL', 'https://example.test/wp-content/plugins/lw-zenadmin/' );
		}

		if ( ! defined( 'LW_ZENADMIN_VERSION' ) ) {
			define( 'LW_ZENADMIN_VERSION', '0.0.0' );
		}

		Functions\when( 'wp_enqueue_style' )->justReturn();
		Functions\when( 'wp_enqueue_script' )->justReturn();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['plugin_page'] );
		parent::tearDown();
	}

	public function test_does_not_collect_on_lw_plugins_screens(): void {
		$this->on_page( 'lw-seo', 'lw-plugins' );

		$this->assertFalse( NoticeCollector::collects_here() );
	}

	public function test_collects_on_other_admin_screens(): void {
		$this->on_page( 'woocommerce', 'woocommerce' );

		$this->assertTrue( NoticeCollector::collects_here() );
	}

	public function test_tells_the_script_to_stand_down_on_lw_plugins_screens(): void {
		$this->on_page( 'lw-zenadmin', 'lw-plugins' );

		Functions\expect( 'wp_add_inline_script' )
			->once()
			->with( 'lw-zenadmin', 'window.lwZenAdmin = { collect: false };', 'before' );

		( new NoticeCollector() )->enqueue_assets();
	}

	public function test_lets_the_script_collect_on_other_admin_screens(): void {
		$GLOBALS['plugin_page'] = '';

		Functions\expect( 'wp_add_inline_script' )->never();

		( new NoticeCollector() )->enqueue_assets();
	}

	public function test_leaves_early_hiding_to_notice_manager_on_lw_plugins_screens(): void {
		$this->on_page( 'lw-plugins', '' );

		$this->expectOutputString( '' );

		( new NoticeCollector() )->hide_notices_early();
	}

	public function test_hides_notices_early_on_other_admin_screens(): void {
		$GLOBALS['plugin_page'] = '';

		$this->expectOutputRegex( '/<style id="lw-zenadmin-early-hide">/' );

		( new NoticeCollector() )->hide_notices_early();
	}

	/**
	 * @param string $page   Current `page` query arg.
	 * @param string $parent Its parent menu slug.
	 */
	private function on_page( string $page, string $parent ): void {
		$GLOBALS['plugin_page'] = $page;
		Functions\when( 'get_admin_page_parent' )->justReturn( $parent );
	}
}
