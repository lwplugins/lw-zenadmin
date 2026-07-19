<?php
/**
 * Settings Sanitizer class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin;

use LightweightPlugins\ZenAdmin\Options;

/**
 * Pure transformations deciding what gets persisted for each settings group.
 *
 * Every method here takes already-unslashed raw submitted input and returns
 * the value that should be persisted. No $_POST access, no update_option(),
 * no exit, no other WordPress side effects -- this is the part of the write
 * path that can be unit tested without touching WordPress state.
 */
final class SettingsSanitizer {

	/**
	 * Decide the main options to persist from raw submitted values.
	 *
	 * @param array<string, mixed> $raw Raw (unslashed) `lw_zenadmin_options` submission.
	 * @return array<string, bool>
	 */
	public static function sanitize_options( array $raw ): array {
		$defaults  = Options::get_defaults();
		$sanitized = [];
		$post_data = array_map( 'sanitize_text_field', $raw );

		foreach ( array_keys( $defaults ) as $key ) {
			$sanitized[ $key ] = ! empty( $post_data[ $key ] );
		}

		return $sanitized;
	}

	/**
	 * Decide the widget IDs to persist as enabled.
	 *
	 * @param array<int, mixed> $raw Raw (unslashed) `lw_zenadmin_widgets` submission.
	 * @return array<int, string>
	 */
	public static function sanitize_widget_settings( array $raw ): array {
		$enabled = [];

		foreach ( $raw as $widget_id ) {
			// A hand-crafted POST can nest arrays here; casting one to string
			// would emit a warning and persist the useless value "Array".
			if ( ! is_scalar( $widget_id ) ) {
				continue;
			}

			$enabled[] = sanitize_key( (string) $widget_id );
		}

		return $enabled;
	}

	/**
	 * Decide the menu slugs to persist as visible.
	 *
	 * @param array<int, mixed> $raw Raw (unslashed) `lw_zenadmin_menus` submission.
	 * @return array<int, string>
	 */
	public static function sanitize_menu_settings( array $raw ): array {
		$visible = [];

		foreach ( $raw as $slug ) {
			$visible[] = sanitize_text_field( (string) $slug );
		}

		return $visible;
	}

	/**
	 * Decide the admin bar node IDs to persist as visible.
	 *
	 * @param array<int, mixed> $raw Raw (unslashed) `lw_zenadmin_adminbar` submission.
	 * @return array<int, string>
	 */
	public static function sanitize_adminbar_settings( array $raw ): array {
		$visible = [];

		foreach ( $raw as $node_id ) {
			$visible[] = sanitize_text_field( (string) $node_id );
		}

		return $visible;
	}
}
