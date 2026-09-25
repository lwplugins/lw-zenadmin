<?php
/**
 * Notice Collector feature.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features;

use LightweightPlugins\ZenAdmin\Admin\NoticeManager;

/**
 * Adds admin bar button and early notice hiding.
 *
 * Collects admin notices into a sidebar panel via JS. On LW Plugins screens
 * it stands down: NoticeManager keeps other plugins' and themes' notices off
 * those screens (with or without ZenAdmin), and collecting would move the
 * markup it hides into the visible panel.
 */
final class NoticeCollector {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_node' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_head', [ $this, 'hide_notices_early' ], -9999 );
	}

	/**
	 * Whether notices are collected into the panel on the current screen.
	 * Not on LW Plugins screens, which NoticeManager keeps notice-free.
	 *
	 * @return bool
	 */
	public static function collects_here(): bool {
		return ! NoticeManager::is_lw_page();
	}

	/**
	 * Add the Notices button to the admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 * @return void
	 */
	public function add_admin_bar_node( \WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			[
				'id'     => 'lw-zenadmin',
				'parent' => 'top-secondary',
				'title'  => '<span class="lw-zenadmin-label">'
					. esc_html__( 'Notices', 'lw-zenadmin' )
					. '</span>'
					. '<span class="lw-zenadmin-badge update-plugins count-0" style="display:none;">'
					. '<span class="update-count">0</span></span>',
				'meta'   => [
					'class' => 'lw-zenadmin-trigger',
					'title' => __( 'Admin Notices', 'lw-zenadmin' ),
				],
			]
		);
	}

	/**
	 * Enqueue notice panel assets.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_enqueue_style(
			'lw-zenadmin',
			LW_ZENADMIN_URL . 'assets/css/zenadmin.css',
			[],
			LW_ZENADMIN_VERSION
		);

		wp_enqueue_script(
			'lw-zenadmin',
			LW_ZENADMIN_URL . 'assets/js/zenadmin.js',
			[ 'jquery' ],
			LW_ZENADMIN_VERSION,
			true
		);

		if ( ! self::collects_here() ) {
			wp_add_inline_script( 'lw-zenadmin', 'window.lwZenAdmin = { collect: false };', 'before' );
		}
	}

	/**
	 * Hide all notices immediately via early inline CSS.
	 *
	 * Prevents the brief flash of notices before the JS collects them.
	 *
	 * @return void
	 */
	public function hide_notices_early(): void {
		if ( ! self::collects_here() ) {
			return; // NoticeManager's stylesheet hides them on LW Plugins screens.
		}

		echo '<style id="lw-zenadmin-early-hide">'
			. '#wpbody-content > .notice:not(.lw-notice),'
			. '#wpbody-content > .updated:not(.lw-notice),'
			. '#wpbody-content > .error:not(.lw-notice),'
			. '#wpbody-content > .update-nag:not(.lw-notice),'
			. '#wpbody-content > div[class*="review-notice"]:not(.lw-notice),'
			. '.wrap > .notice:not(.lw-notice),'
			. '.wrap > .updated:not(.lw-notice),'
			. '.wrap > .error:not(.lw-notice),'
			. '.wrap > .update-nag:not(.lw-notice),'
			. '.wrap > div[class*="review-notice"]:not(.lw-notice)'
			. '{display:none!important}'
			. '</style>';
	}
}
