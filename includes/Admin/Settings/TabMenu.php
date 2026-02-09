<?php
/**
 * Menu Manager Settings Tab.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

use LightweightPlugins\ZenAdmin\Features\Data\CoreMenuItems;
use LightweightPlugins\ZenAdmin\Features\MenuManager;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Handles the Admin Menu settings tab.
 */
final class TabMenu implements TabInterface {

	use FieldRendererTrait;
	use MenuGrouperTrait;

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'menu';
	}

	/**
	 * Get the tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Menus', 'lw-zenadmin' );
	}

	/**
	 * Get the tab icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-menu';
	}

	/**
	 * Render the tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<h2><?php esc_html_e( 'Admin Menu Manager', 'lw-zenadmin' ); ?></h2>

		<div class="lw-zenadmin-section-description">
			<p>
				<?php esc_html_e( 'Control which menu items appear in the admin sidebar. Protected items (Dashboard, Settings, Plugins, LW Plugins) cannot be hidden.', 'lw-zenadmin' ); ?>
			</p>
		</div>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable', 'lw-zenadmin' ); ?></th>
				<td>
					<?php
					$this->render_checkbox_field(
						[
							'name'        => 'menu_enabled',
							'label'       => __( 'Manage admin menu visibility', 'lw-zenadmin' ),
							'description' => __( 'Hide or show individual admin menu items based on your preferences.', 'lw-zenadmin' ),
						]
					);
					?>
				</td>
			</tr>
		</table>

		<?php $this->render_menu_table(); ?>
		<?php
	}

	/**
	 * Render the menu visibility table.
	 *
	 * @return void
	 */
	private function render_menu_table(): void {
		$discovered = Options::get_discovered_menus();

		if ( empty( $discovered ) ) {
			?>
			<p><em><?php esc_html_e( 'No menu items discovered yet. Enable the feature and visit any admin page.', 'lw-zenadmin' ); ?></em></p>
			<?php
			return;
		}

		$settings = Options::get_menu_settings();
		$groups   = $this->group_menus( $discovered );

		?>
		<h3><?php esc_html_e( 'Menu Visibility', 'lw-zenadmin' ); ?></h3>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width:40px;"><?php esc_html_e( 'Show', 'lw-zenadmin' ); ?></th>
					<th><?php esc_html_e( 'Menu Item', 'lw-zenadmin' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'lw-zenadmin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $groups as $group ) : ?>
					<?php if ( empty( $group['items'] ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<tr>
						<th colspan="3" style="background:#e0e0e0;">
							<strong><?php echo esc_html( $group['label'] ); ?></strong>
						</th>
					</tr>
					<?php foreach ( $group['items'] as $slug => $data ) : ?>
						<?php $this->render_menu_row( $slug, $data, $settings ); ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render a single menu row (top-level or submenu).
	 *
	 * @param string                                           $slug     Menu slug or submenu key.
	 * @param array{title: string, icon: string, is_sub: bool} $data     Menu data.
	 * @param array<string>|false                              $settings Saved settings.
	 */
	private function render_menu_row( string $slug, array $data, array|false $settings ): void {
		$is_sub      = $data['is_sub'] ?? false;
		$is_protect  = $this->is_row_protected( $slug );
		$visible     = $is_protect ? true : MenuManager::is_menu_visible( $slug, $settings );
		$title       = $is_sub ? '— ' . $data['title'] : $data['title'];
		$title_style = $is_sub ? 'padding-left:20px;' : 'font-weight:600;';

		?>
		<tr>
			<td>
				<input
					type="checkbox"
					name="lw_zenadmin_menus[]"
					value="<?php echo esc_attr( $slug ); ?>"
					<?php checked( $visible ); ?>
					<?php disabled( $is_protect ); ?>
				/>
			</td>
			<td style="<?php echo esc_attr( $title_style ); ?>">
				<?php echo esc_html( $title ); ?>
				<?php if ( $is_protect ) : ?>
					<em>(<?php esc_html_e( 'protected', 'lw-zenadmin' ); ?>)</em>
				<?php endif; ?>
			</td>
			<td><code><?php echo esc_html( $slug ); ?></code></td>
		</tr>
		<?php
	}

	/**
	 * Check if a row is protected.
	 *
	 * @param string $slug Menu slug or submenu key.
	 * @return bool
	 */
	private function is_row_protected( string $slug ): bool {
		if ( str_contains( $slug, '::' ) ) {
			$parts = explode( '::', $slug, 2 );
			return CoreMenuItems::is_protected( $parts[0] ) && CoreMenuItems::is_protected( $parts[1] );
		}

		return CoreMenuItems::is_protected( $slug );
	}
}
