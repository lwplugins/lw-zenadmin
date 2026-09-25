/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Icon, caution } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import StatusBadge from '../components/StatusBadge';
import { TABS, tabOfField } from './tabs';

/**
 * Nav extras: "Off" on a tab whose feature is switched off (in the draft),
 * and a flag on every tab holding a field the last save rejected.
 *
 * @param {Object}      props
 * @param {Object}      props.errors  Field errors { key: [ messages ] }.
 * @param {Object|null} props.options Draft options.
 * @return {Object} { tabId: node }.
 */
export default function navMeta( { errors, options } ) {
	const meta = {};

	if ( options ) {
		TABS.forEach( ( tab ) => {
			if ( ! options[ tab.option ] ) {
				meta[ tab.id ] = (
					<StatusBadge status="idle">
						{ __( 'Off', 'lw-zenadmin' ) }
					</StatusBadge>
				);
			}
		} );
	}

	Object.keys( errors ).forEach( ( key ) => {
		meta[ tabOfField( key ) ] = (
			<span className="lw-admin-navflag">
				<Icon icon={ caution } size={ 18 } />
				<span className="screen-reader-text">
					{ __( 'Has invalid settings', 'lw-zenadmin' ) }
				</span>
			</span>
		);
	} );

	return meta;
}
