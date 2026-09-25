/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { bell } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Callout from '../components/Callout';
import FeatureBar from '../components/FeatureBar';
import Section from '../components/Section';

/**
 * Notices: the notice collector switch and what it does.
 *
 * @param {Object} props
 * @param {Object} props.store Settings store.
 */
export default function NoticesTab( { store } ) {
	const on = store.draft.options.notices_enabled;

	return (
		<>
			<FeatureBar
				icon={ bell }
				checked={ on }
				onChange={ ( value ) =>
					store.setOption( 'notices_enabled', value )
				}
				onTitle={ __( 'The notice collector is on', 'lw-zenadmin' ) }
				offTitle={ __( 'The notice collector is off', 'lw-zenadmin' ) }
				onText={ __(
					'Adds a Notices button to the admin bar. All notices are moved into a slide-in panel.',
					'lw-zenadmin'
				) }
				offText={ __(
					'Admin notices show on every page, as WordPress prints them.',
					'lw-zenadmin'
				) }
				errors={ store.errors.notices_enabled }
			/>
			<Section
				title={ __( 'How it works', 'lw-zenadmin' ) }
				description={ __(
					'Collect all admin notices into a sidebar panel accessible from the admin bar. Keeps your admin pages clean and distraction-free.',
					'lw-zenadmin'
				) }
			>
				<ul className="lw-zen-facts">
					<li>
						{ __(
							'The Notices button shows a live count of the collected notices.',
							'lw-zenadmin'
						) }
					</li>
					<li>
						{ __(
							'Notices are hidden before the page is drawn, so they never flash.',
							'lw-zenadmin'
						) }
					</li>
					<li>
						{ __(
							'Close the panel with Escape, the close button or a click outside it.',
							'lw-zenadmin'
						) }
					</li>
				</ul>
				<Callout>
					{ __(
						'Notices from other plugins and themes are not shown on LW Plugins screens.',
						'lw-zenadmin'
					) }
				</Callout>
			</Section>
		</>
	);
}
