/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import VisibilityItem from './VisibilityItem';

/**
 * One source group: heading with count, "show all / hide all" for the listed
 * items (protected ones and parents kept only for context are skipped), and
 * the items.
 *
 * @param {Object}                                    props
 * @param {Object}                                    props.group    { id, label, items, matched? }.
 * @param {number}                                    props.total    Items in the unfiltered group.
 * @param {Object}                                    props.draft    { id: visible } draft map.
 * @param {Object}                                    props.saved    { id: visible } saved map.
 * @param {(id: string, visible: boolean) => void}    props.onChange Toggles one item.
 * @param {(values: Object<string, boolean>) => void} props.onBulk   Sets many items ({ id: visible }).
 */
export default function VisibilityGroup( {
	group,
	total,
	draft,
	saved,
	onChange,
	onBulk,
} ) {
	const targets = group.items.filter(
		( item ) =>
			! item.protected &&
			( ! group.matched || group.matched.has( item.id ) )
	);
	const setAll = ( value ) =>
		onBulk(
			Object.fromEntries( targets.map( ( item ) => [ item.id, value ] ) )
		);
	const count =
		group.items.length === total
			? String( total )
			: `${ group.items.length } / ${ total }`;

	return (
		<div className="lw-zen-group">
			<div className="lw-zen-group__head">
				<h4 className="lw-zen-group__title">
					<span>{ group.label }</span>
					<span className="lw-admin-hint">{ count }</span>
				</h4>
				{ targets.length > 0 && (
					<div className="lw-zen-group__actions">
						<Button
							size="small"
							variant="tertiary"
							disabled={ targets.every(
								( item ) => draft[ item.id ]
							) }
							accessibleWhenDisabled
							label={ sprintf(
								/* translators: %s: group name, e.g. "WordPress Core". */
								__(
									'Show every listed item in %s',
									'lw-zenadmin'
								),
								group.label
							) }
							showTooltip={ false }
							onClick={ () => setAll( true ) }
						>
							{ __( 'Show all', 'lw-zenadmin' ) }
						</Button>
						<Button
							size="small"
							variant="tertiary"
							disabled={ targets.every(
								( item ) => ! draft[ item.id ]
							) }
							accessibleWhenDisabled
							label={ sprintf(
								/* translators: %s: group name, e.g. "WordPress Core". */
								__(
									'Hide every listed item in %s',
									'lw-zenadmin'
								),
								group.label
							) }
							showTooltip={ false }
							onClick={ () => setAll( false ) }
						>
							{ __( 'Hide all', 'lw-zenadmin' ) }
						</Button>
					</div>
				) }
			</div>
			<ul className="lw-zen-items">
				{ group.items.map( ( item ) => (
					<VisibilityItem
						key={ item.id }
						item={ item }
						checked={ !! draft[ item.id ] }
						changed={ draft[ item.id ] !== saved[ item.id ] }
						context={
							!! group.matched && ! group.matched.has( item.id )
						}
						onChange={ ( value ) => onChange( item.id, value ) }
					/>
				) ) }
			</ul>
		</div>
	);
}
