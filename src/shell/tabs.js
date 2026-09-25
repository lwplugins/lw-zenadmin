/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { bell, header, menu, widget } from '@wordpress/icons';

/**
 * Tab registry: the classic hash slugs and order (#notices, #widgets, #menu,
 * #adminbar). `option` is the feature switch of the tab, `section` its
 * visibility list; `fields` are the error keys a failed save can flag.
 */
export const TABS = [
	{
		id: 'notices',
		label: __( 'Notices', 'lw-zenadmin' ),
		title: __( 'Notice Collector', 'lw-zenadmin' ),
		icon: bell,
		option: 'notices_enabled',
		fields: [ 'notices_enabled' ],
	},
	{
		id: 'widgets',
		label: __( 'Widgets', 'lw-zenadmin' ),
		title: __( 'Dashboard Widget Manager', 'lw-zenadmin' ),
		icon: widget,
		option: 'widgets_enabled',
		section: 'widgets',
		fields: [ 'widgets_enabled', 'widgets' ],
	},
	{
		id: 'menu',
		label: __( 'Menus', 'lw-zenadmin' ),
		title: __( 'Admin Menu Manager', 'lw-zenadmin' ),
		icon: menu,
		option: 'menu_enabled',
		section: 'menus',
		fields: [ 'menu_enabled', 'menus' ],
	},
	{
		id: 'adminbar',
		label: __( 'Admin Bar', 'lw-zenadmin' ),
		title: __( 'Admin Bar Manager', 'lw-zenadmin' ),
		icon: header,
		option: 'adminbar_enabled',
		section: 'adminbar',
		fields: [ 'adminbar_enabled', 'adminbar' ],
	},
];

/**
 * Tab id that holds an error key (for error flags in the nav). Unknown keys
 * land on the first tab so they are never invisible.
 *
 * @param {string} field Error key.
 * @return {string} Tab id.
 */
export const tabOfField = ( field ) =>
	( TABS.find( ( tab ) => tab.fields.includes( field ) ) || TABS[ 0 ] ).id;
