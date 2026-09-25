/**
 * WordPress dependencies
 */
import { Card, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon } from '@wordpress/icons';

/**
 * Feature state bar: icon, what the switch means right now, and the switch
 * itself (a card-level control, so it lives in this bar and never in the
 * right column of a settings row).
 *
 * @param {Object}   props
 * @param {Object}   props.icon     Icon.
 * @param {boolean}  props.checked  Feature on.
 * @param {Function} props.onChange Receives the new boolean.
 * @param {string}   props.onTitle  Headline when on.
 * @param {string}   props.offTitle Headline when off.
 * @param {string}   props.onText   Explanation when on.
 * @param {string}   props.offText  Explanation when off.
 * @param {string[]} props.errors   Server validation messages.
 */
export default function FeatureBar( {
	icon,
	checked,
	onChange,
	onTitle,
	offTitle,
	onText,
	offText,
	errors = [],
} ) {
	return (
		<Card
			className={ `lw-admin-section lw-zen-feature${
				checked ? '' : ' is-off'
			}` }
		>
			<div className="lw-zen-feature__bar">
				<span className="lw-zen-feature__icon" aria-hidden="true">
					<Icon icon={ icon } size={ 26 } />
				</span>
				<div className="lw-zen-feature__text">
					<strong>{ checked ? onTitle : offTitle }</strong>
					<span>{ checked ? onText : offText }</span>
				</div>
				<ToggleControl
					__nextHasNoMarginBottom
					label={
						checked
							? __( 'On', 'lw-zenadmin' )
							: __( 'Off', 'lw-zenadmin' )
					}
					checked={ checked }
					onChange={ onChange }
				/>
			</div>
			{ errors.length > 0 && (
				<ul className="lw-admin-fielderror lw-zen-feature__errors">
					{ errors.map( ( message ) => (
						<li key={ message }>{ message }</li>
					) ) }
				</ul>
			) }
		</Card>
	);
}
