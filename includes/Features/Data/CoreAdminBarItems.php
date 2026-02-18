<?php
/**
 * Core Admin Bar Items data.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features\Data;

/**
 * Registry of WordPress core admin bar node IDs.
 */
final class CoreAdminBarItems {

	/**
	 * WordPress core top-level admin bar node IDs.
	 */
	private const CORE_IDS = [
		'wp-logo',
		'site-name',
		'comments',
		'new-content',
		'updates',
		'search',
		'my-account',
		'top-secondary',
		'menu-toggle',
		'user-actions',
		'user-info',
		'edit-profile',
		'logout',
	];

	/**
	 * Node IDs that cannot be hidden (safety).
	 */
	private const PROTECTED_IDS = [
		'my-account',
		'top-secondary',
		'user-actions',
		'logout',
	];

	/**
	 * WooCommerce admin bar prefixes.
	 */
	private const WOOCOMMERCE_PREFIXES = [
		'wc-',
		'woocommerce',
	];

	/**
	 * Check if a node ID is a core WordPress item.
	 *
	 * @param string $node_id Node identifier.
	 * @return bool
	 */
	public static function is_core( string $node_id ): bool {
		return in_array( $node_id, self::CORE_IDS, true );
	}

	/**
	 * Check if a node ID is protected (cannot be hidden).
	 *
	 * @param string $node_id Node identifier.
	 * @return bool
	 */
	public static function is_protected( string $node_id ): bool {
		return in_array( $node_id, self::PROTECTED_IDS, true );
	}

	/**
	 * Check if a node ID is a WooCommerce item.
	 *
	 * @param string $node_id Node identifier.
	 * @return bool
	 */
	public static function is_woocommerce( string $node_id ): bool {
		foreach ( self::WOOCOMMERCE_PREFIXES as $prefix ) {
			if ( str_starts_with( $node_id, $prefix ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the group key for a node ID.
	 *
	 * @param string $node_id Node identifier.
	 * @return string core, woocommerce, or third_party.
	 */
	public static function get_group( string $node_id ): string {
		if ( self::is_core( $node_id ) ) {
			return 'core';
		}

		return self::is_woocommerce( $node_id ) ? 'woocommerce' : 'third_party';
	}
}
