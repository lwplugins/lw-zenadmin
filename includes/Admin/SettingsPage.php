<?php
/**
 * Settings Page class.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin;

use LightweightPlugins\ZenAdmin\Admin\Settings\TabInterface;
use LightweightPlugins\ZenAdmin\Admin\Settings\TabMenu;
use LightweightPlugins\ZenAdmin\Admin\Settings\TabNotices;
use LightweightPlugins\ZenAdmin\Admin\Settings\TabWidgets;

/**
 * Handles the plugin settings page.
 */
final class SettingsPage {

	/**
	 * Settings page slug.
	 */
	public const SLUG = 'lw-zenadmin';

	/**
	 * Registered tabs.
	 *
	 * @var array<TabInterface>
	 */
	private array $tabs = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->tabs = [
			new TabNotices(),
			new TabWidgets(),
			new TabMenu(),
		];

		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ SettingsSaver::class, 'maybe_save' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add menu page.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		ParentPage::maybe_register();

		add_submenu_page(
			ParentPage::SLUG,
			__( 'ZenAdmin', 'lw-zenadmin' ),
			__( 'ZenAdmin', 'lw-zenadmin' ),
			'manage_options',
			self::SLUG,
			[ $this, 'render' ]
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin page.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		$valid_hooks = [
			'toplevel_page_' . ParentPage::SLUG,
			ParentPage::SLUG . '_page_' . self::SLUG,
		];

		if ( ! in_array( $hook, $valid_hooks, true ) ) {
			return;
		}

		wp_enqueue_style(
			'lw-zenadmin-admin',
			LW_ZENADMIN_URL . 'assets/css/admin.css',
			[],
			LW_ZENADMIN_VERSION
		);

		wp_enqueue_script(
			'lw-zenadmin-admin',
			LW_ZENADMIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			LW_ZENADMIN_VERSION,
			true
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( isset( $_GET['updated'] ) && '1' === $_GET['updated'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success lw-notice is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'lw-zenadmin' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'lw_zenadmin_save', '_lw_zenadmin_nonce' ); ?>
				<input type="hidden" name="lw_zenadmin_active_tab" value="" />

				<div class="lw-zenadmin-settings">
					<?php $this->render_tabs_nav(); ?>

					<div class="lw-zenadmin-tab-content">
						<?php $this->render_tabs_content(); ?>
						<?php submit_button( __( 'Save Changes', 'lw-zenadmin' ), 'primary', 'lw_zenadmin_save' ); ?>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render tabs navigation.
	 *
	 * @return void
	 */
	private function render_tabs_nav(): void {
		?>
		<ul class="lw-zenadmin-tabs">
			<?php foreach ( $this->tabs as $index => $tab ) : ?>
				<li>
					<a href="#<?php echo esc_attr( $tab->get_slug() ); ?>" <?php echo 0 === $index ? 'class="active"' : ''; ?>>
						<span class="dashicons <?php echo esc_attr( $tab->get_icon() ); ?>"></span>
						<?php echo esc_html( $tab->get_label() ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	/**
	 * Render tabs content.
	 *
	 * @return void
	 */
	private function render_tabs_content(): void {
		foreach ( $this->tabs as $index => $tab ) {
			$active_class = 0 === $index ? ' active' : '';
			printf(
				'<div id="tab-%s" class="lw-zenadmin-tab-panel%s">',
				esc_attr( $tab->get_slug() ),
				esc_attr( $active_class )
			);
			$tab->render();
			echo '</div>';
		}
	}
}
