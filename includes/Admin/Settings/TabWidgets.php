<?php
/**
 * Widget Manager Settings Tab.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

use LightweightPlugins\ZenAdmin\Features\Data\CoreWidgets;
use LightweightPlugins\ZenAdmin\Features\WidgetManager;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Handles the Dashboard Widgets settings tab.
 */
final class TabWidgets implements TabInterface {

	use FieldRendererTrait;

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'widgets';
	}

	/**
	 * Get the tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Widgets', 'lw-zenadmin' );
	}

	/**
	 * Get the tab icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-screenoptions';
	}

	/**
	 * Render the tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<h2><?php esc_html_e( 'Dashboard Widget Manager', 'lw-zenadmin' ); ?></h2>

		<div class="lw-zenadmin-section-description">
			<p>
				<?php esc_html_e( 'Control which widgets appear on your WordPress dashboard. Core and WooCommerce widgets are enabled by default.', 'lw-zenadmin' ); ?>
			</p>
		</div>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable', 'lw-zenadmin' ); ?></th>
				<td>
					<?php
					$this->render_checkbox_field(
						[
							'name'        => 'widgets_enabled',
							'label'       => __( 'Manage dashboard widget visibility', 'lw-zenadmin' ),
							'description' => __( 'Hide or show individual dashboard widgets based on your preferences.', 'lw-zenadmin' ),
						]
					);
					?>
				</td>
			</tr>
		</table>

		<?php $this->render_widget_table(); ?>
		<?php
	}

	/**
	 * Render the widget visibility table.
	 *
	 * @return void
	 */
	private function render_widget_table(): void {
		$discovered = Options::get_discovered_widgets();

		if ( empty( $discovered ) ) {
			?>
			<p><em><?php esc_html_e( 'No widgets discovered yet. Visit the Dashboard once so the plugin can detect them.', 'lw-zenadmin' ); ?></em></p>
			<?php
			return;
		}

		$settings = Options::get_widget_settings();
		$groups   = $this->group_widgets( $discovered );

		?>
		<h3><?php esc_html_e( 'Widget Visibility', 'lw-zenadmin' ); ?></h3>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width:40px;"><?php esc_html_e( 'Show', 'lw-zenadmin' ); ?></th>
					<th><?php esc_html_e( 'Widget Name', 'lw-zenadmin' ); ?></th>
					<th><?php esc_html_e( 'Widget ID', 'lw-zenadmin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $groups as $group ) : ?>
					<?php if ( empty( $group['widgets'] ) ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<tr>
						<th colspan="3" style="background:#e0e0e0;">
							<strong><?php echo esc_html( $group['label'] ); ?></strong>
						</th>
					</tr>
					<?php foreach ( $group['widgets'] as $widget_id => $title ) : ?>
						<tr>
							<td>
								<input
									type="checkbox"
									name="lw_zenadmin_widgets[]"
									value="<?php echo esc_attr( $widget_id ); ?>"
									<?php checked( WidgetManager::is_widget_visible( $widget_id, $settings ) ); ?>
								/>
							</td>
							<td><?php echo esc_html( $title ); ?></td>
							<td><code><?php echo esc_html( $widget_id ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Group widgets by source.
	 *
	 * @param array<string, string> $discovered All discovered widgets.
	 * @return array<string, array{label: string, widgets: array<string, string>}>
	 */
	private function group_widgets( array $discovered ): array {
		$groups = [
			'core'        => [
				'label'   => __( 'WordPress Core', 'lw-zenadmin' ),
				'widgets' => [],
			],
			'woocommerce' => [
				'label'   => __( 'WooCommerce', 'lw-zenadmin' ),
				'widgets' => [],
			],
			'third_party' => [
				'label'   => __( 'Third-party', 'lw-zenadmin' ),
				'widgets' => [],
			],
		];

		foreach ( $discovered as $widget_id => $title ) {
			if ( CoreWidgets::is_core( $widget_id ) ) {
				$groups['core']['widgets'][ $widget_id ] = $title;
			} elseif ( CoreWidgets::is_woocommerce( $widget_id ) ) {
				$groups['woocommerce']['widgets'][ $widget_id ] = $title;
			} else {
				$groups['third_party']['widgets'][ $widget_id ] = $title;
			}
		}

		return $groups;
	}
}
