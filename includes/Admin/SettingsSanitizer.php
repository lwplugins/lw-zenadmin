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
 * Every method here takes already-validated input and returns the value that
 * should be persisted. No request access, no update_option(), no other
 * WordPress side effects -- this is the part of the write path that can be
 * unit tested without touching WordPress state.
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
	 * Rebuild a section's visible list from a partial change set.
	 *
	 * Only the items the user changed are submitted. Every other discovered
	 * item keeps its current effective visibility, so an item discovered after
	 * the screen was loaded is never hidden by a save. Protected items are left
	 * out of the list (they are always shown), and IDs that are no longer
	 * discovered are dropped -- the list the classic form rebuilt from its
	 * checkboxes had the same shape.
	 *
	 * @param array<string, bool>    $changes        Item ID => visible, already validated.
	 * @param array<int, string>     $discovered_ids All discovered item IDs, in order.
	 * @param callable(string): bool $is_visible     Current effective visibility.
	 * @param callable(string): bool $is_protected   Whether an item can never be hidden.
	 * @return array<int, string>
	 */
	public static function merge_visibility( array $changes, array $discovered_ids, callable $is_visible, callable $is_protected ): array {
		$visible = [];

		foreach ( $discovered_ids as $id ) {
			$id = (string) $id;

			if ( $is_protected( $id ) ) {
				continue;
			}

			$show = array_key_exists( $id, $changes ) ? (bool) $changes[ $id ] : $is_visible( $id );

			if ( $show ) {
				$visible[] = $id;
			}
		}

		return $visible;
	}
}
