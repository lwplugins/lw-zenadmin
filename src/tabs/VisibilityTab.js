/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Callout from '../components/Callout';
import FeatureBar from '../components/FeatureBar';
import Section from '../components/Section';
import VisibilityList from '../components/VisibilityList';
import { visibilityOf } from '../data/shapes';
import { COPY } from './visibilityCopy';

/**
 * Nothing discovered yet: the classic guidance plus the one action that
 * helps (open the Dashboard / reload so this page's own load detects).
 *
 * @param {Object}  props
 * @param {Object}  props.copy     Tab copy.
 * @param {boolean} props.savedOn  Feature switched on on the server.
 * @param {string}  props.dashUrl  Dashboard URL.
 * @param {boolean} props.hasEdits Unsaved edits (reloading would lose them).
 */
function EmptyState( { copy, savedOn, dashUrl, hasEdits } ) {
	let action = null;

	if ( savedOn && copy.detect === 'dashboard' && dashUrl ) {
		action = (
			<Button __next40pxDefaultSize variant="secondary" href={ dashUrl }>
				{ __( 'Open the Dashboard', 'lw-zenadmin' ) }
			</Button>
		);
	} else if ( savedOn && copy.detect === 'reload' ) {
		action = (
			<Button
				__next40pxDefaultSize
				variant="secondary"
				disabled={ hasEdits }
				accessibleWhenDisabled
				onClick={ () => window.location.reload() }
			>
				{ __( 'Reload page', 'lw-zenadmin' ) }
			</Button>
		);
	}

	return (
		<Section title={ copy.listTitle }>
			<Callout>
				{ copy.empty }{ ' ' }
				{ ! savedOn &&
					__(
						'Detection only runs while the manager is on and saved.',
						'lw-zenadmin'
					) }
			</Callout>
			{ action && <div className="lw-admin-inline">{ action }</div> }
		</Section>
	);
}

/**
 * Widgets / Menus / Admin Bar: the feature switch and the grouped list of
 * discovered items (checked = shown).
 *
 * @param {Object} props
 * @param {Object} props.tab   Tab from the registry.
 * @param {Object} props.store Settings store.
 */
export default function VisibilityTab( { tab, store } ) {
	const copy = COPY[ tab.id ]();
	const on = store.draft.options[ tab.option ];
	const savedOn = store.server.options[ tab.option ];
	const section = store.server.sections[ tab.section ];

	return (
		<>
			<FeatureBar
				icon={ tab.icon }
				label={ tab.title }
				checked={ on }
				onChange={ ( value ) => store.setOption( tab.option, value ) }
				onTitle={ copy.onTitle }
				offTitle={ copy.offTitle }
				onText={ copy.onText }
				offText={ copy.offText }
				errors={ store.errors[ tab.option ] }
			/>
			{ ! on && section.count > 0 && (
				<Callout>
					{ __(
						'The manager is off, so these choices do not apply yet. They are kept and take effect once you turn it on and save.',
						'lw-zenadmin'
					) }
				</Callout>
			) }
			{ section.count > 0 ? (
				<VisibilityList
					title={ copy.listTitle }
					description={ copy.description }
					section={ section }
					draft={ store.draft.visibility[ tab.section ] }
					saved={ visibilityOf( section ) }
					onChange={ ( id, value ) =>
						store.setVisible( tab.section, id, value )
					}
					onBulk={ ( values ) =>
						store.setVisibleMany( tab.section, values )
					}
					errors={ store.errors[ tab.section ] }
				/>
			) : (
				<EmptyState
					copy={ copy }
					savedOn={ savedOn }
					dashUrl={ store.meta.dashboardUrl }
					hasEdits={ store.hasEdits }
				/>
			) }
		</>
	);
}
