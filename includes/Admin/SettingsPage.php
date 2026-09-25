<?php
/**
 * Settings Page class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin;

use LightweightPlugins\ZenAdmin\Rest\Admin\Routes;
use LightweightPlugins\ZenAdmin\Rest\Admin\SettingsResponse;

/**
 * The ZenAdmin screen: a mount point for the React admin (build/index),
 * which reads and writes through the lw-zenadmin/v1 REST routes.
 *
 * Loading this screen is itself what refreshes the menu and admin bar
 * discovery (MenuManager and AdminBarManager run on every admin page while
 * enabled), before the app asks the REST route for the lists.
 */
final class SettingsPage {

	/**
	 * Settings page slug.
	 */
	public const SLUG = 'lw-zenadmin';

	/**
	 * Script and style handle.
	 */
	private const HANDLE = 'lw-zenadmin-admin-app';

	/**
	 * Hook suffix returned by add_submenu_page().
	 *
	 * Assets are keyed on it rather than on a hard-coded
	 * "lw-plugins_page_lw-zenadmin": WordPress derives that prefix from the
	 * translated parent menu title, so a locale that translates "LW Plugins"
	 * would silently stop the screen from loading.
	 *
	 * @var string
	 */
	private string $hook_suffix = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		ParentPage::maybe_register();

		$hook = add_submenu_page(
			ParentPage::SLUG,
			__( 'ZenAdmin', 'lw-zenadmin' ),
			__( 'ZenAdmin', 'lw-zenadmin' ),
			'manage_options',
			self::SLUG,
			[ $this, 'render' ]
		);

		$this->hook_suffix = is_string( $hook ) ? $hook : '';
	}

	/**
	 * Enqueue the React app on the settings screen.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( '' === $this->hook_suffix || $hook !== $this->hook_suffix ) {
			return;
		}

		if ( ! BuildAssets::enqueue( 'index', self::HANDLE ) ) {
			return;
		}

		wp_add_inline_script(
			self::HANDLE,
			'window.lwZenAdminAdmin = ' . wp_json_encode(
				[
					'version'   => LW_ZENADMIN_VERSION,
					'namespace' => Routes::NAMESPACE,
					'docsUrl'   => SettingsResponse::DOCS_URL,
				]
			) . ';',
			'before'
		);
	}

	/**
	 * Mark the settings screen body for the app's styles.
	 *
	 * @param string $classes Space-separated body classes.
	 * @return string
	 */
	public function body_class( string $classes ): string {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( '' === $this->hook_suffix || ! $screen || $screen->id !== $this->hook_suffix ) {
			return $classes;
		}

		return $classes . ' lw-zenadmin-screen';
	}

	/**
	 * Render the mount point (or a notice when the build is missing).
	 *
	 * The mount point sits outside .wrap so NoticeManager's direct-child
	 * notice rules never reach the app; the missing-build notice carries
	 * `lw-notice` so it is not hidden.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! BuildAssets::exists( 'index' ) ) {
			printf(
				'<div class="wrap"><h1>%s</h1><div class="notice notice-error lw-notice"><p>%s</p></div></div>',
				esc_html__( 'Lightweight ZenAdmin', 'lw-zenadmin' ),
				esc_html__( 'The settings screen files are missing. Re-install the plugin from a release ZIP, or run "npm install && npm run build" in the plugin directory.', 'lw-zenadmin' )
			);
			return;
		}

		echo '<div id="lw-zenadmin-root" class="lw-zenadmin-root"></div>';
	}
}
