<?php
/**
 * Notice Panel feature.
 *
 * @package LightweightPlugins\ZenAdmin
 */

declare(strict_types=1);

namespace LightweightPlugins\ZenAdmin\Features;

/**
 * Renders the sidebar notice panel HTML in the admin footer.
 */
final class NoticePanel {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_footer', [ $this, 'render' ] );
	}

	/**
	 * Render the sidebar panel HTML.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<div id="lw-zenadmin-overlay" class="lw-zenadmin-overlay"></div>
		<div id="lw-zenadmin-panel" class="lw-zenadmin-panel">
			<div class="lw-zenadmin-panel-header">
				<h2><?php esc_html_e( 'Notices', 'lw-zenadmin' ); ?></h2>
				<button type="button" class="lw-zenadmin-close" aria-label="<?php esc_attr_e( 'Close', 'lw-zenadmin' ); ?>">
					&times;
				</button>
			</div>
			<div class="lw-zenadmin-panel-body">
				<p class="lw-zenadmin-empty">
					<?php esc_html_e( 'No notices.', 'lw-zenadmin' ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
