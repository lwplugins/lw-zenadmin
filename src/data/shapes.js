/**
 * Response adapters: the ONLY place that knows the backend's field names
 * (lw-zenadmin/v1/admin/settings — includes/Rest/Admin/*). The UI reads the
 * camelCase objects built here.
 */
/**
 * Internal dependencies
 */
import { DOCS_URL } from './boot';

const obj = ( value ) =>
	value && typeof value === 'object' && ! Array.isArray( value ) ? value : {};
const list = ( value ) => ( Array.isArray( value ) ? value : [] );
const str = ( value ) =>
	value === null || value === undefined ? '' : String( value );

export const OPTION_KEYS = [
	'notices_enabled',
	'widgets_enabled',
	'menu_enabled',
	'adminbar_enabled',
];
export const SECTION_KEYS = [ 'widgets', 'menus', 'adminbar' ];

/**
 * One visibility section: { saved, count, groups: [ { id, label, items } ] }.
 *
 * @param {Object} data Raw section.
 * @return {Object} Section.
 */
function toSection( data ) {
	const section = obj( data );
	const groups = list( section.groups )
		.map( ( group ) => ( {
			id: str( group?.id ),
			label: str( group?.label ),
			items: list( group?.items ).map( ( item ) => ( {
				id: str( item?.id ),
				title: str( item?.title ) || str( item?.id ),
				visible: !! item?.visible,
				protected: !! item?.protected,
				depth: Math.max( 0, Number( item?.depth ) || 0 ),
			} ) ),
		} ) )
		.filter( ( group ) => group.items.length > 0 );

	return {
		saved: !! section.saved,
		count: groups.reduce( ( sum, group ) => sum + group.items.length, 0 ),
		groups,
	};
}

/**
 * GET/POST /admin/settings → { options, sections, meta }.
 *
 * @param {Object} data Response.
 * @return {Object} Settings.
 */
export function toSettings( data ) {
	const options = obj( data?.options );
	const sections = obj( data?.sections );
	const meta = obj( data?.meta );

	return {
		options: Object.fromEntries(
			OPTION_KEYS.map( ( key ) => [ key, !! options[ key ] ] )
		),
		sections: Object.fromEntries(
			SECTION_KEYS.map( ( key ) => [ key, toSection( sections[ key ] ) ] )
		),
		meta: {
			dashboardUrl: str( meta.dashboard_url ),
			docsUrl: str( meta.docs_url ) || DOCS_URL,
		},
	};
}

/**
 * Item ID => visible map of a section (the draft's starting point).
 *
 * @param {Object} section Section from toSettings().
 * @return {Object} { id: boolean }.
 */
export const visibilityOf = ( section ) =>
	Object.fromEntries(
		section.groups.flatMap( ( group ) =>
			group.items.map( ( item ) => [ item.id, item.visible ] )
		)
	);
