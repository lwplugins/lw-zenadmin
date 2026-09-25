/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Copy of the three visibility tabs. Functions, so __() runs after the
 * translations are loaded. The list descriptions and empty messages are the
 * classic screen's strings.
 */
export const COPY = {
	widgets: () => ( {
		onTitle: __( 'The widget manager is on', 'lw-zenadmin' ),
		offTitle: __( 'The widget manager is off', 'lw-zenadmin' ),
		onText: __(
			'Unchecked widgets are removed from the Dashboard.',
			'lw-zenadmin'
		),
		offText: __( 'Every widget shows on the Dashboard.', 'lw-zenadmin' ),
		listTitle: __( 'Widget Visibility', 'lw-zenadmin' ),
		description: __(
			'Control which widgets appear on your WordPress dashboard. Core and WooCommerce widgets are enabled by default.',
			'lw-zenadmin'
		),
		empty: __(
			'No widgets discovered yet. Visit the Dashboard once so the plugin can detect them.',
			'lw-zenadmin'
		),
		detect: 'dashboard',
	} ),
	menu: () => ( {
		onTitle: __( 'The menu manager is on', 'lw-zenadmin' ),
		offTitle: __( 'The menu manager is off', 'lw-zenadmin' ),
		onText: __(
			'Unchecked items are removed from the admin sidebar.',
			'lw-zenadmin'
		),
		offText: __(
			'Every menu item shows in the admin sidebar.',
			'lw-zenadmin'
		),
		listTitle: __( 'Menu Visibility', 'lw-zenadmin' ),
		description: __(
			'Control which menu items appear in the admin sidebar. Protected items (Dashboard, Settings, Plugins, LW Plugins) cannot be hidden.',
			'lw-zenadmin'
		),
		empty: __(
			'No menu items discovered yet. Enable the feature and visit any admin page.',
			'lw-zenadmin'
		),
		detect: 'reload',
	} ),
	adminbar: () => ( {
		onTitle: __( 'The admin bar manager is on', 'lw-zenadmin' ),
		offTitle: __( 'The admin bar manager is off', 'lw-zenadmin' ),
		onText: __(
			'Unchecked items are removed from the admin bar.',
			'lw-zenadmin'
		),
		offText: __( 'Every item shows in the admin bar.', 'lw-zenadmin' ),
		listTitle: __( 'Admin Bar Visibility', 'lw-zenadmin' ),
		description: __(
			'Control which items appear in the WordPress admin bar. Protected items (My Account, Logout) cannot be hidden.',
			'lw-zenadmin'
		),
		empty: __(
			'No admin bar items discovered yet. Enable the feature and visit any page.',
			'lw-zenadmin'
		),
		detect: 'reload',
	} ),
};
