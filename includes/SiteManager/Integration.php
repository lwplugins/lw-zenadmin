<?php
/**
 * LW Site Manager Integration.
 *
 * Registers ZenAdmin abilities when LW Site Manager is active.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\SiteManager;

/**
 * Hooks into LW Site Manager to register ZenAdmin abilities.
 */
final class Integration {

	/**
	 * Initialize hooks. Safe to call even if Site Manager is not active.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'lw_site_manager_register_categories', [ self::class, 'register_category' ] );
		add_action( 'lw_site_manager_register_abilities', [ self::class, 'register_abilities' ] );
	}

	/**
	 * Register the ZenAdmin ability category.
	 *
	 * @return void
	 */
	public static function register_category(): void {
		wp_register_ability_category(
			'zenadmin',
			[
				'label'       => __( 'ZenAdmin', 'lw-zenadmin' ),
				'description' => __( 'Admin UI management abilities', 'lw-zenadmin' ),
			]
		);
	}

	/**
	 * Register ZenAdmin abilities.
	 *
	 * @param object $permissions Permission manager from Site Manager.
	 * @return void
	 */
	public static function register_abilities( object $permissions ): void {
		ZenAdminAbilities::register( $permissions );
	}
}
