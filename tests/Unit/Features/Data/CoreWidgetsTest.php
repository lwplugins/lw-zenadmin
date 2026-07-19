<?php
/**
 * Tests for the CoreWidgets registry (default-on classification for widgets).
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features\Data;

use LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets
 */
final class CoreWidgetsTest extends TestCase {

	/**
	 * @dataProvider provide_default_on_widgets
	 */
	public function test_is_default_true_for_core_and_woocommerce_widgets( string $widget_id ): void {
		$this->assertTrue( CoreWidgets::is_default( $widget_id ) );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public static function provide_default_on_widgets(): array {
		return [
			'core widget'           => [ 'dashboard_right_now' ],
			'woocommerce_ prefixed' => [ 'woocommerce_dashboard_status' ],
			'wc_ prefixed'          => [ 'wc_admin_notes' ],
		];
	}

	public function test_is_default_false_for_a_third_party_widget(): void {
		$this->assertFalse( CoreWidgets::is_default( 'acme_dashboard_widget' ) );
	}

	/**
	 * A hyphenated "wc-" id is not recognised: WooCommerce dashboard widget
	 * IDs use an underscore ("wc_"/"woocommerce_"), unlike its menu slugs and
	 * admin bar node IDs which use a hyphen. Confirms the boundary is exact.
	 */
	public function test_hyphenated_wc_prefix_is_not_treated_as_woocommerce(): void {
		$this->assertFalse( CoreWidgets::is_woocommerce( 'wc-not-a-real-widget-id' ) );
	}

	/**
	 * @dataProvider provide_group_cases
	 */
	public function test_get_group_classifies_a_widget( string $widget_id, string $expected_group ): void {
		$this->assertSame( $expected_group, CoreWidgets::get_group( $widget_id ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public static function provide_group_cases(): array {
		return [
			'core'        => [ 'dashboard_activity', 'core' ],
			'woocommerce' => [ 'woocommerce_dashboard_status', 'woocommerce' ],
			'third-party' => [ 'acme_dashboard_widget', 'third-party' ],
		];
	}
}
