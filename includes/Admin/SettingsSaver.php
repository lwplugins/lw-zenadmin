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
		self::save_menu_settings();
		self::save_adminbar_settings();

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
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in maybe_save(). Sanitized in SettingsSanitizer::sanitize_options().
		$raw = isset( $_POST['lw_zenadmin_options'] ) ? wp_unslash( (array) $_POST['lw_zenadmin_options'] ) : [];

		Options::save( SettingsSanitizer::sanitize_options( $raw ) );
	}

	/**
	 * Save widget visibility settings.
	 *
	 * @return void
	 */
	private static function save_widget_settings(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in maybe_save(). Sanitized in SettingsSanitizer::sanitize_widget_settings().
		$raw_widgets = isset( $_POST['lw_zenadmin_widgets'] ) ? wp_unslash( (array) $_POST['lw_zenadmin_widgets'] ) : [];

		Options::save_widget_settings( SettingsSanitizer::sanitize_widget_settings( $raw_widgets ) );
	}

	/**
	 * Save menu visibility settings.
	 *
	 * @return void
	 */
	private static function save_menu_settings(): void {
		// Don't save if no menus discovered yet — avoids saving empty array on first enable.
		if ( empty( Options::get_discovered_menus() ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in maybe_save(). Sanitized in SettingsSanitizer::sanitize_menu_settings().
		$raw_menus = isset( $_POST['lw_zenadmin_menus'] ) ? wp_unslash( (array) $_POST['lw_zenadmin_menus'] ) : [];

		Options::save_menu_settings( SettingsSanitizer::sanitize_menu_settings( $raw_menus ) );
	}

	/**
	 * Save admin bar visibility settings.
	 *
	 * @return void
	 */
	private static function save_adminbar_settings(): void {
		if ( empty( Options::get_discovered_adminbar() ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in maybe_save(). Sanitized in SettingsSanitizer::sanitize_adminbar_settings().
		$raw_nodes = isset( $_POST['lw_zenadmin_adminbar'] ) ? wp_unslash( (array) $_POST['lw_zenadmin_adminbar'] ) : [];

		Options::save_adminbar_settings( SettingsSanitizer::sanitize_adminbar_settings( $raw_nodes ) );
	}
}
