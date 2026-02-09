<?php
/**
 * Core Admin Menu Items data.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features\Data;

/**
 * Registry of WordPress core admin menu slugs.
 */
final class CoreMenuItems {

	/**
	 * WordPress core top-level menu slugs.
	 */
	private const CORE_SLUGS = [
		'index.php',
		'edit.php',
		'upload.php',
		'edit-comments.php',
		'themes.php',
		'plugins.php',
		'users.php',
		'tools.php',
		'options-general.php',
		'profile.php',
		'separator1',
		'separator2',
		'separator-last',
	];

	/**
	 * WooCommerce-related menu prefixes.
	 */
	private const WOOCOMMERCE_PREFIXES = [
		'woocommerce',
		'wc-',
		'edit.php?post_type=product',
		'edit.php?post_type=shop_order',
		'edit.php?post_type=shop_coupon',
	];

	/**
	 * Slugs that can never be hidden.
	 */
	private const PROTECTED_SLUGS = [
		'index.php',
		'options-general.php',
		'plugins.php',
		'lw-plugins',
	];

	/**
	 * Check if a slug is a core WordPress menu item.
	 *
	 * @param string $slug Menu slug.
	 * @return bool
	 */
	public static function is_core( string $slug ): bool {
		return in_array( $slug, self::CORE_SLUGS, true );
	}

	/**
	 * Check if a slug is a WooCommerce menu item.
	 *
	 * @param string $slug Menu slug.
	 * @return bool
	 */
	public static function is_woocommerce( string $slug ): bool {
		foreach ( self::WOOCOMMERCE_PREFIXES as $prefix ) {
			if ( str_starts_with( $slug, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a slug is an LW Plugins menu item.
	 *
	 * @param string $slug Menu slug.
	 * @return bool
	 */
	public static function is_lw_plugin( string $slug ): bool {
		return str_starts_with( $slug, 'lw-' );
	}

	/**
	 * Get the group key for a menu slug.
	 *
	 * @param string $slug Menu slug.
	 * @return string core, woocommerce, lw_plugins, or third_party.
	 */
	public static function get_group( string $slug ): string {
		if ( self::is_core( $slug ) ) {
			return 'core';
		}

		if ( self::is_woocommerce( $slug ) ) {
			return 'woocommerce';
		}

		return self::is_lw_plugin( $slug ) ? 'lw_plugins' : 'third_party';
	}

	/**
	 * Check if a slug is protected (cannot be hidden).
	 *
	 * @param string $slug Menu slug.
	 * @return bool
	 */
	public static function is_protected( string $slug ): bool {
		return in_array( $slug, self::PROTECTED_SLUGS, true ) || self::is_lw_plugin( $slug );
	}
}
