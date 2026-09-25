/**
 * Case- and accent-insensitive form ("Bővítmények" → "bovitmenyek").
 *
 * @param {string} text Text.
 * @return {string} Folded text.
 */
export const fold = ( text ) =>
	String( text ).normalize( 'NFD' ).replace( /\p{M}/gu, '' ).toLowerCase();

/**
 * Groups narrowed to the items whose title or ID contains the query. A
 * matching child keeps its parents (items are flat, depth-first, so a
 * parent is the nearest earlier item one level up). Groups without a match
 * are dropped. Each returned group carries `matched`: the IDs that matched
 * themselves (parents kept only for context are not in it).
 *
 * @param {Object[]} groups Section groups ({ id, label, items }).
 * @param {string}   query  Search text.
 * @return {Object[]} Filtered groups, or the input when the query is blank.
 */
export default function filterGroups( groups, query ) {
	const needle = fold( query.trim() );

	if ( ! needle ) {
		return groups;
	}

	return groups
		.map( ( group ) => {
			const keep = new Set();
			const matched = new Set();
			const path = [];

			group.items.forEach( ( item, index ) => {
				path.length = item.depth;
				path[ item.depth ] = index;

				if (
					fold( item.title ).includes( needle ) ||
					fold( item.id ).includes( needle )
				) {
					matched.add( item.id );
					path.forEach(
						( at ) => at !== undefined && keep.add( at )
					);
				}
			} );

			return {
				...group,
				items: group.items.filter( ( item, index ) =>
					keep.has( index )
				),
				matched,
			};
		} )
		.filter( ( group ) => group.items.length > 0 );
}
