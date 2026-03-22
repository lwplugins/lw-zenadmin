<?php
/**
 * ZenAdmin Ability Definitions for LW Site Manager.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\SiteManager;

/**
 * Registers ZenAdmin-specific abilities with the WordPress Abilities API.
 */
final class ZenAdminAbilities {

	/**
	 * Register all ZenAdmin abilities.
	 *
	 * @param object $permissions Permission manager instance.
	 * @return void
	 */
	public static function register( object $permissions ): void {
		self::register_options_abilities( $permissions );
		self::register_widget_abilities( $permissions );
	}

	/**
	 * Register options get/set abilities.
	 *
	 * @param object $permissions Permission manager instance.
	 * @return void
	 */
	private static function register_options_abilities( object $permissions ): void {
		wp_register_ability(
			'lw-zenadmin/get-options',
			[
				'label'               => __( 'Get ZenAdmin Options', 'lw-zenadmin' ),
				'description'         => __( 'Get global LW ZenAdmin settings.', 'lw-zenadmin' ),
				'category'            => 'zenadmin',
				'execute_callback'    => [ ZenAdminService::class, 'get_options' ],
				'permission_callback' => $permissions->callback( 'can_manage_options' ),
				'input_schema'        => [
					'type'    => 'object',
					'default' => [],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
						'options' => [ 'type' => 'object' ],
					],
				],
				'meta'                => self::readonly_meta(),
			]
		);

		wp_register_ability(
			'lw-zenadmin/set-options',
			[
				'label'               => __( 'Set ZenAdmin Options', 'lw-zenadmin' ),
				'description'         => __( 'Update LW ZenAdmin settings.', 'lw-zenadmin' ),
				'category'            => 'zenadmin',
				'execute_callback'    => [ ZenAdminService::class, 'set_options' ],
				'permission_callback' => $permissions->callback( 'can_manage_options' ),
				'input_schema'        => [
					'type'       => 'object',
					'required'   => [ 'options' ],
					'properties' => [
						'options' => [
							'type'        => 'object',
							'description' => __( 'Settings to update: notices_enabled, widgets_enabled, menu_enabled, adminbar_enabled.', 'lw-zenadmin' ),
						],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
						'message' => [ 'type' => 'string' ],
						'updated' => [
							'type'  => 'array',
							'items' => [ 'type' => 'string' ],
						],
					],
				],
				'meta'                => self::write_meta(),
			]
		);
	}

	/**
	 * Register dashboard widget list ability.
	 *
	 * @param object $permissions Permission manager instance.
	 * @return void
	 */
	private static function register_widget_abilities( object $permissions ): void {
		wp_register_ability(
			'lw-zenadmin/list-widgets',
			[
				'label'               => __( 'List Dashboard Widgets', 'lw-zenadmin' ),
				'description'         => __( 'List all discovered dashboard widgets and their visibility status.', 'lw-zenadmin' ),
				'category'            => 'zenadmin',
				'execute_callback'    => [ ZenAdminService::class, 'list_widgets' ],
				'permission_callback' => $permissions->callback( 'can_manage_options' ),
				'input_schema'        => [
					'type'    => 'object',
					'default' => [],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
						'widgets' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'id'      => [ 'type' => 'string' ],
									'title'   => [ 'type' => 'string' ],
									'group'   => [ 'type' => 'string' ],
									'visible' => [ 'type' => 'boolean' ],
								],
							],
						],
					],
				],
				'meta'                => self::readonly_meta(),
			]
		);
	}

	/**
	 * Read-only ability metadata.
	 *
	 * @return array<string, mixed>
	 */
	private static function readonly_meta(): array {
		return [
			'show_in_rest' => true,
			'annotations'  => [
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			],
		];
	}

	/**
	 * Write ability metadata.
	 *
	 * @return array<string, mixed>
	 */
	private static function write_meta(): array {
		return [
			'show_in_rest' => true,
			'annotations'  => [
				'readonly'    => false,
				'destructive' => false,
				'idempotent'  => true,
			],
		];
	}
}
