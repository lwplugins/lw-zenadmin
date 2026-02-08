<?php
/**
 * Core Dashboard Widget IDs.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features\Data;

/**
 * Registry of WordPress core dashboard widget IDs.
 */
final class CoreWidgets {

	/**
	 * WordPress core dashboard widget IDs.
	 */
	private const CORE_IDS = [
		'dashboard_right_now',
		'dashboard_activity',
		'dashboard_quick_press',
		'dashboard_primary',
		'dashboard_site_health',
		'dashboard_browser_nag',
		'dashboard_php_nag',
		'network_dashboard_right_now',
	];

	/**
	 * Check if a widget ID is a core WordPress widget.
	 *
	 * @param string $widget_id Widget identifier.
	 * @return bool
	 */
	public static function is_core( string $widget_id ): bool {
		return in_array( $widget_id, self::CORE_IDS, true );
	}

	/**
	 * Check if a widget ID is a WooCommerce widget.
	 *
	 * @param string $widget_id Widget identifier.
	 * @return bool
	 */
	public static function is_woocommerce( string $widget_id ): bool {
		return str_starts_with( $widget_id, 'woocommerce_' )
			|| str_starts_with( $widget_id, 'wc_' );
	}

	/**
	 * Check if a widget is a default-on widget (core or WooCommerce).
	 *
	 * @param string $widget_id Widget identifier.
	 * @return bool
	 */
	public static function is_default( string $widget_id ): bool {
		return self::is_core( $widget_id ) || self::is_woocommerce( $widget_id );
	}

	/**
	 * Get the group name for a widget.
	 *
	 * @param string $widget_id Widget identifier.
	 * @return string core, woocommerce, or third-party.
	 */
	public static function get_group( string $widget_id ): string {
		if ( self::is_core( $widget_id ) ) {
			return 'core';
		}

		return self::is_woocommerce( $widget_id ) ? 'woocommerce' : 'third-party';
	}
}
