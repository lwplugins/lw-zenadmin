<?php
/**
 * Admin Bar Manager Settings Tab.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

use LightweightPlugins\ZenAdmin\Features\AdminBarManager;
use LightweightPlugins\ZenAdmin\Features\Data\CoreAdminBarItems;
use LightweightPlugins\ZenAdmin\Options;

/**
 * Handles the Admin Bar settings tab.
 */
final class TabAdminBar implements TabInterface {

	use FieldRendererTrait;

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'adminbar';
	}

	/**
	 * Get the tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Admin Bar', 'lw-zenadmin' );
	}

	/**
	 * Get the tab icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-admin-generic';
	}

	/**
	 * Render the tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<h2><?php esc_html_e( 'Admin Bar Manager', 'lw-zenadmin' ); ?></h2>

		<div class="lw-zenadmin-section-description">
			<p>
				<?php esc_html_e( 'Control which items appear in the WordPress admin bar. Protected items (My Account, Logout) cannot be hidden.', 'lw-zenadmin' ); ?>
			</p>
		</div>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable', 'lw-zenadmin' ); ?></th>
				<td>
					<?php
					$this->render_checkbox_field(
						[
							'name'        => 'adminbar_enabled',
							'label'       => __( 'Manage admin bar visibility', 'lw-zenadmin' ),
							'description' => __( 'Hide or show individual admin bar items based on your preferences.', 'lw-zenadmin' ),
						]
					);
					?>
				</td>
			</tr>
		</table>

		<?php $this->render_node_table(); ?>
		<?php
	}

	/**
	 * Render the admin bar node visibility table.
	 *
	 * @return void
	 */
	private function render_node_table(): void {
		$discovered = Options::get_discovered_adminbar();

		if ( empty( $discovered ) ) {
			?>
			<p><em><?php esc_html_e( 'No admin bar items discovered yet. Enable the feature and visit any page.', 'lw-zenadmin' ); ?></em></p>
			<?php
			return;
		}

		$settings = Options::get_adminbar_settings();
		$groups   = $this->group_nodes( $discovered );

		?>
		<h3><?php esc_html_e( 'Admin Bar Visibility', 'lw-zenadmin' ); ?></h3>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width:40px;"><?php esc_html_e( 'Show', 'lw-zenadmin' ); ?></th>
					<th><?php esc_html_e( 'Item', 'lw-zenadmin' ); ?></th>
					<th><?php esc_html_e( 'Node ID', 'lw-zenadmin' ); ?></th>
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
					<?php foreach ( $group['items'] as $node_id => $data ) : ?>
						<?php $this->render_node_row( $node_id, $data, $settings ); ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render a single node row.
	 *
	 * @param string                                             $node_id  Node identifier.
	 * @param array{title: string, parent: string, is_sub: bool} $data     Node data.
	 * @param array<string>|false                                $settings Saved settings.
	 */
	private function render_node_row( string $node_id, array $data, array|false $settings ): void {
		$is_sub     = $data['is_sub'] ?? false;
		$is_protect = CoreAdminBarItems::is_protected( $node_id );
		$visible    = $is_protect ? true : AdminBarManager::is_node_visible( $node_id, $settings );
		$title      = $is_sub ? '— ' . $data['title'] : $data['title'];
		$style      = $is_sub ? 'padding-left:20px;' : 'font-weight:600;';

		?>
		<tr>
			<td>
				<input
					type="checkbox"
					name="lw_zenadmin_adminbar[]"
					value="<?php echo esc_attr( $node_id ); ?>"
					<?php checked( $visible ); ?>
					<?php disabled( $is_protect ); ?>
				/>
			</td>
			<td style="<?php echo esc_attr( $style ); ?>">
				<?php echo esc_html( $title ); ?>
				<?php if ( $is_protect ) : ?>
					<em>(<?php esc_html_e( 'protected', 'lw-zenadmin' ); ?>)</em>
				<?php endif; ?>
			</td>
			<td><code><?php echo esc_html( $node_id ); ?></code></td>
		</tr>
		<?php
	}

	/**
	 * Group nodes by source with children nested under parents.
	 *
	 * @param array<string, array{title: string, parent: string}> $discovered All discovered nodes.
	 * @return array<string, array{label: string, items: array<string, array{title: string, parent: string, is_sub: bool}>}>
	 */
	private function group_nodes( array $discovered ): array {
		$groups = [
			'core'        => [
				'label' => __( 'WordPress Core', 'lw-zenadmin' ),
				'items' => [],
			],
			'woocommerce' => [
				'label' => __( 'WooCommerce', 'lw-zenadmin' ),
				'items' => [],
			],
			'third_party' => [
				'label' => __( 'Third-party', 'lw-zenadmin' ),
				'items' => [],
			],
		];

		$parents  = [];
		$children = [];

		foreach ( $discovered as $node_id => $data ) {
			$parent = $data['parent'] ?? '';
			if ( '' === $parent || ! isset( $discovered[ $parent ] ) ) {
				$parents[ $node_id ] = $data;
			} else {
				$children[ $node_id ] = $data;
			}
		}

		foreach ( $parents as $node_id => $data ) {
			$group                                 = CoreAdminBarItems::get_group( $node_id );
			$data['is_sub']                        = false;
			$groups[ $group ]['items'][ $node_id ] = $data;

			foreach ( $children as $child_id => $child_data ) {
				if ( ( $child_data['parent'] ?? '' ) === $node_id ) {
					$child_data['is_sub']                   = true;
					$groups[ $group ]['items'][ $child_id ] = $child_data;
				}
			}
		}

		return $groups;
	}
}
