<?php
/**
 * Notices Settings Tab.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Admin\Settings;

/**
 * Handles the Notices settings tab.
 */
final class TabNotices implements TabInterface {

	use FieldRendererTrait;

	/**
	 * Get the tab slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return 'notices';
	}

	/**
	 * Get the tab label.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Notices', 'lw-zenadmin' );
	}

	/**
	 * Get the tab icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-bell';
	}

	/**
	 * Render the tab content.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<h2><?php esc_html_e( 'Notice Collector', 'lw-zenadmin' ); ?></h2>

		<div class="lw-zenadmin-section-description">
			<p>
				<?php esc_html_e( 'Collect all admin notices into a sidebar panel accessible from the admin bar. Keeps your admin pages clean and distraction-free.', 'lw-zenadmin' ); ?>
			</p>
		</div>

		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable', 'lw-zenadmin' ); ?></th>
				<td>
					<?php
					$this->render_checkbox_field(
						[
							'name'        => 'notices_enabled',
							'label'       => __( 'Collect notices into sidebar panel', 'lw-zenadmin' ),
							'description' => __( 'Adds a Notices button to the admin bar. All notices are moved into a slide-in panel.', 'lw-zenadmin' ),
						]
					);
					?>
				</td>
			</tr>
		</table>
		<?php
	}
}
