<?php
/**
 * Settings Saver class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin;

use LightweightPlugins\ZenAdmin\Options;

/**
 * Handles saving settings form data.
 */
final class SettingsSaver {

	/**
	 * Handle form submission.
	 *
	 * @return void
	 */
	public static function maybe_save(): void {
		if ( ! isset( $_POST['lw_zenadmin_save'] ) ) {
			return;
		}

		if (
			! isset( $_POST['_lw_zenadmin_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( $_POST['_lw_zenadmin_nonce'] ), 'lw_zenadmin_save' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::save_options();
		self::save_widget_settings();

		$active_tab = isset( $_POST['lw_zenadmin_active_tab'] )
			? sanitize_key( $_POST['lw_zenadmin_active_tab'] )
			: '';

		wp_safe_redirect(
			add_query_arg(
				[
					'page'    => SettingsPage::SLUG,
					'updated' => '1',
				],
				admin_url( 'admin.php' )
			) . ( $active_tab ? '#' . $active_tab : '' )
		);
		exit;
	}

	/**
	 * Save main options.
	 *
	 * @return void
	 */
	private static function save_options(): void {
		$defaults  = Options::get_defaults();
		$sanitized = [];
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in maybe_save(). Sanitized on next line.
		$raw       = isset( $_POST['lw_zenadmin_options'] ) ? wp_unslash( (array) $_POST['lw_zenadmin_options'] ) : [];
		$post_data = array_map( 'sanitize_text_field', $raw );

		foreach ( $defaults as $key => $default_val ) {
			$sanitized[ $key ] = ! empty( $post_data[ $key ] );
		}

		Options::save( $sanitized );
	}

	/**
	 * Save widget visibility settings.
	 *
	 * @return void
	 */
	private static function save_widget_settings(): void {
		$enabled = [];

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in maybe_save().
		$raw_widgets = isset( $_POST['lw_zenadmin_widgets'] )
			? array_map( 'sanitize_key', wp_unslash( (array) $_POST['lw_zenadmin_widgets'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			: [];

		foreach ( $raw_widgets as $widget_id ) {
			$enabled[] = sanitize_key( $widget_id );
		}

		Options::save_widget_settings( $enabled );
	}
}
