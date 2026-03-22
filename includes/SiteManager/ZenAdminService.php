<?php
/**
 * ZenAdmin Service for LW Site Manager abilities.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\SiteManager;

use LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets;
use LightweightPlugins\ZenAdmin\Features\WidgetManager;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Executes ZenAdmin abilities for the Site Manager.
 */
final class ZenAdminService {

	/**
	 * Allowed option keys that can be set via the API.
	 */
	private const ALLOWED_OPTIONS = [
		'notices_enabled',
		'widgets_enabled',
		'menu_enabled',
		'adminbar_enabled',
	];

	/**
	 * Get global LW ZenAdmin options.
	 *
	 * @param array<string, mixed> $input Input parameters.
	 * @return array<string, mixed>
	 */
	public static function get_options( array $input ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by ability callback interface.
		return [
			'success' => true,
			'options' => Options::get_all(),
		];
	}

	/**
	 * Update LW ZenAdmin options.
	 *
	 * @param array<string, mixed> $input Input parameters.
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function set_options( array $input ): array|\WP_Error {
		$incoming = $input['options'] ?? [];

		if ( ! is_array( $incoming ) || empty( $incoming ) ) {
			return new \WP_Error(
				'missing_options',
				__( 'Provide an options object with at least one key.', 'lw-zenadmin' ),
				[ 'status' => 400 ]
			);
		}

		$current = Options::get_all();
		$updated = [];

		foreach ( $incoming as $key => $value ) {
			if ( ! in_array( $key, self::ALLOWED_OPTIONS, true ) ) {
				continue;
			}
			$current[ $key ] = (bool) $value;
			$updated[]       = $key;
		}

		if ( empty( $updated ) ) {
			return new \WP_Error(
				'no_valid_keys',
				__( 'No valid option keys provided.', 'lw-zenadmin' ),
				[ 'status' => 400 ]
			);
		}

		Options::save( $current );

		return [
			'success' => true,
			'message' => sprintf(
				/* translators: %d: number of options updated */
				__( '%d option(s) updated.', 'lw-zenadmin' ),
				count( $updated )
			),
			'updated' => $updated,
		];
	}

	/**
	 * List all discovered dashboard widgets with their visibility status.
	 *
	 * @param array<string, mixed> $input Input parameters.
	 * @return array<string, mixed>
	 */
	public static function list_widgets( array $input ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by ability callback interface.
		$discovered = Options::get_discovered_widgets();
		$settings   = Options::get_widget_settings();
		$widgets    = [];

		foreach ( $discovered as $id => $title ) {
			$widgets[] = [
				'id'      => $id,
				'title'   => $title,
				'group'   => CoreWidgets::get_group( $id ),
				'visible' => WidgetManager::is_widget_visible( $id, $settings ),
			];
		}

		return [
			'success' => true,
			'widgets' => $widgets,
		];
	}
}
