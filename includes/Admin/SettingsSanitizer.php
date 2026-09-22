<?php
/**
 * Settings Sanitizer class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin;

use LightweightPlugins\ZenAdmin\Features\AdminBarManager;
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

	/**
	 * Carry over the saved state of admin bar nodes the submitted form never rendered.
	 *
	 * The visible list is rebuilt from the checked boxes, so a discovered node
	 * without a row in the submitted form (discovered after the page was
	 * rendered, a truncated POST, or a node the tab failed to list) would
	 * otherwise be hidden by the save with no way to see why. Such a node
	 * keeps its current visibility instead.
	 *
	 * @param array<int, string>  $visible        Sanitized checked node IDs.
	 * @param array<int, mixed>   $raw_rendered   Raw (unslashed) `lw_zenadmin_adminbar_rendered` submission.
	 * @param array<int, string>  $discovered_ids All discovered node IDs.
	 * @param array<string>|false $previous       Saved visible list, false if never saved.
	 * @return array<int, string>
	 */
	public static function keep_unrendered_adminbar_nodes( array $visible, array $raw_rendered, array $discovered_ids, array|false $previous ): array {
		$rendered = [];

		foreach ( $raw_rendered as $node_id ) {
			if ( is_scalar( $node_id ) ) {
				$rendered[ sanitize_text_field( (string) $node_id ) ] = true;
			}
		}

		foreach ( $discovered_ids as $node_id ) {
			if ( isset( $rendered[ $node_id ] ) || in_array( $node_id, $visible, true ) ) {
				continue;
			}

			if ( AdminBarManager::is_node_visible( $node_id, $previous ) ) {
				$visible[] = $node_id;
			}
		}

		return $visible;
	}
}
