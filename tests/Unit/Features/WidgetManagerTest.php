<?php
/**
 * Tests for WidgetManager visibility resolution.
 *
 * Dashboard widgets have no "protected" concept (unlike menus and admin bar
 * nodes), so there is no lock-out guard to exercise here -- only the
 * default-on-for-core/WooCommerce vs. never-saved vs. saved-list contract.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Tests\Unit\Features;

use LightweightPlugins\ZenAdmin\Features\WidgetManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LightweightPlugins\ZenAdmin\Features\WidgetManager
 */
final class WidgetManagerTest extends TestCase {

	/**
	 * @dataProvider provide_visibility_cases
	 *
	 * @param array<string>|false $settings Saved settings passed to is_widget_visible().
	 */
	public function test_is_widget_visible_resolves_from_saved_settings_or_default(
		$settings,
		string $widget_id,
		bool $expected
	): void {
		$this->assertSame( $expected, WidgetManager::is_widget_visible( $widget_id, $settings ) );
	}

	/**
	 * @return array<string, array{0: array<string>|false, 1: string, 2: bool}>
	 */
	public static function provide_visibility_cases(): array {
		return [
			'never saved, core widget -> visible by default' => [ false, 'dashboard_right_now', true ],
			'never saved, woocommerce widget -> visible by default' => [ false, 'woocommerce_dashboard_status', true ],
			'never saved, third-party widget -> hidden by default' => [ false, 'acme_dashboard_widget', false ],
			'saved empty -> hidden even for a core widget' => [ [], 'dashboard_right_now', false ],
			'saved and present -> visible'                 => [ [ 'acme_dashboard_widget' ], 'acme_dashboard_widget', true ],
			'saved but different widget -> hidden'         => [ [ 'dashboard_activity' ], 'dashboard_right_now', false ],
		];
	}
}
