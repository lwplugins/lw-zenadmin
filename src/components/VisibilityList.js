/**
 * WordPress dependencies
 */
import { SearchControl } from '@wordpress/components';
import { useMemo, useState } from '@wordpress/element';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import filterGroups from '../data/filterGroups';
import Section from './Section';
import StatusBadge from './StatusBadge';
import VisibilityGroup from './VisibilityGroup';

/**
 * Grouped checklist of discovered items (checked = shown), with a search
 * field that only narrows what is listed (the draft is never touched).
 *
 * @param {Object}                                    props
 * @param {string}                                    props.title       Card heading.
 * @param {string}                                    props.description Card lead text.
 * @param {Object}                                    props.section     Section from the server (groups).
 * @param {Object}                                    props.draft       { id: visible } draft map.
 * @param {Object}                                    props.saved       { id: visible } saved map.
 * @param {(id: string, visible: boolean) => void}    props.onChange    Toggles one item.
 * @param {(values: Object<string, boolean>) => void} props.onBulk      Sets many items ({ id: visible }).
 * @param {string[]}                                  props.errors      Server validation messages.
 */
export default function VisibilityList( {
	title,
	description,
	section,
	draft,
	saved,
	onChange,
	onBulk,
	errors = [],
} ) {
	const [ query, setQuery ] = useState( '' );
	const groups = useMemo(
		() => filterGroups( section.groups, query ),
		[ section.groups, query ]
	);
	const totals = Object.fromEntries(
		section.groups.map( ( group ) => [ group.id, group.items.length ] )
	);
	const hidden = section.groups.reduce(
		( sum, group ) =>
			sum +
			group.items.filter(
				( item ) => ! item.protected && ! draft[ item.id ]
			).length,
		0
	);

	return (
		<Section
			title={ title }
			description={ description }
			badge={
				<StatusBadge status={ hidden ? 'info' : 'idle' }>
					{ sprintf(
						/* translators: 1: hidden items, 2: all items. */
						_n(
							'%1$d of %2$d hidden',
							'%1$d of %2$d hidden',
							hidden,
							'lw-zenadmin'
						),
						hidden,
						section.count
					) }
				</StatusBadge>
			}
		>
			{ errors.length > 0 && (
				<ul className="lw-admin-fielderror">
					{ errors.map( ( message ) => (
						<li key={ message }>{ message }</li>
					) ) }
				</ul>
			) }
			<SearchControl
				__nextHasNoMarginBottom
				className="lw-zen-search"
				label={ __( 'Filter items', 'lw-zenadmin' ) }
				placeholder={ __( 'Filter by name or ID…', 'lw-zenadmin' ) }
				value={ query }
				onChange={ setQuery }
			/>
			{ groups.map( ( group ) => (
				<VisibilityGroup
					key={ group.id }
					group={ group }
					total={ totals[ group.id ] }
					draft={ draft }
					saved={ saved }
					onChange={ onChange }
					onBulk={ onBulk }
				/>
			) ) }
			<p className="lw-zen-nomatch" aria-live="polite">
				{ groups.length === 0 &&
					sprintf(
						/* translators: %s: the search text. */
						__( 'No items match “%s”.', 'lw-zenadmin' ),
						query.trim()
					) }
			</p>
		</Section>
	);
}
