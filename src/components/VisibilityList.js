/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { Icon, lock } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Section from './Section';
import StatusBadge from './StatusBadge';

/**
 * One item: checkbox + title (indented by depth), protected badge, ID.
 *
 * @param {Object}   props
 * @param {Object}   props.item     { id, title, protected, depth }.
 * @param {boolean}  props.checked  Draft visibility.
 * @param {boolean}  props.changed  Differs from the saved state.
 * @param {Function} props.onChange Receives the new boolean.
 */
function Item( { item, checked, changed, onChange } ) {
	return (
		<li
			className={ `lw-zen-item${ item.depth ? '' : ' is-top' }${
				changed ? ' is-changed' : ''
			}` }
			style={ { '--lw-zen-depth': item.depth } }
		>
			<CheckboxControl
				__nextHasNoMarginBottom
				label={ item.title }
				checked={ item.protected || checked }
				disabled={ item.protected }
				onChange={ onChange }
			/>
			{ item.protected && (
				<StatusBadge status="idle">
					<span className="lw-zen-protected">
						<Icon icon={ lock } size={ 14 } />
						{ __( 'Protected', 'lw-zenadmin' ) }
					</span>
				</StatusBadge>
			) }
			<code className="lw-admin-code lw-zen-item__id">{ item.id }</code>
		</li>
	);
}

/**
 * Grouped checklist of discovered items (checked = shown).
 *
 * @param {Object}   props
 * @param {string}   props.title       Card heading.
 * @param {string}   props.description Card lead text.
 * @param {Object}   props.section     Section from the server (groups).
 * @param {Object}   props.draft       { id: visible } draft map.
 * @param {Object}   props.saved       { id: visible } saved map.
 * @param {Function} props.onChange    ( id, visible ) => void.
 * @param {string[]} props.errors      Server validation messages.
 */
export default function VisibilityList( {
	title,
	description,
	section,
	draft,
	saved,
	onChange,
	errors = [],
} ) {
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
			{ section.groups.map( ( group ) => (
				<div key={ group.id } className="lw-zen-group">
					<h4 className="lw-zen-group__title">
						<span>{ group.label }</span>
						<span className="lw-admin-hint">
							{ String( group.items.length ) }
						</span>
					</h4>
					<ul className="lw-zen-items">
						{ group.items.map( ( item ) => (
							<Item
								key={ item.id }
								item={ item }
								checked={ !! draft[ item.id ] }
								changed={
									draft[ item.id ] !== saved[ item.id ]
								}
								onChange={ ( value ) =>
									onChange( item.id, value )
								}
							/>
						) ) }
					</ul>
				</div>
			) ) }
		</Section>
	);
}
