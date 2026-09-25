/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, lock } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import StatusBadge from './StatusBadge';

/**
 * One item: checkbox + title (indented by depth), protected badge, ID.
 *
 * @param {Object}                     props
 * @param {Object}                     props.item     { id, title, protected, depth }.
 * @param {boolean}                    props.checked  Draft visibility.
 * @param {boolean}                    props.changed  Differs from the saved state.
 * @param {boolean}                    props.context  Shown only as the parent of a match.
 * @param {(checked: boolean) => void} props.onChange Receives the new boolean.
 */
export default function VisibilityItem( {
	item,
	checked,
	changed,
	context,
	onChange,
} ) {
	const classes = [
		'lw-zen-item',
		item.depth ? '' : 'is-top',
		changed ? 'is-changed' : '',
		context ? 'is-context' : '',
	];

	return (
		<li
			className={ classes.filter( Boolean ).join( ' ' ) }
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
