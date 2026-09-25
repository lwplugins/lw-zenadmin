/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import { api, errorMessage, fieldErrors } from './api';
import { SECTION_KEYS, visibilityOf } from './shapes';

/**
 * Draft from a server payload: the options plus one { id: visible } map per
 * visibility section.
 *
 * @param {Object} data toSettings() result.
 * @return {Object} { options, visibility }.
 */
const draftOf = ( data ) => ( {
	options: { ...data.options },
	visibility: Object.fromEntries(
		SECTION_KEYS.map( ( key ) => [
			key,
			visibilityOf( data.sections[ key ] ),
		] )
	),
} );

/**
 * Only what differs from the server: changed option keys and, per section,
 * the items whose checkbox changed. Unsent items keep their state on the
 * server, so an item discovered meanwhile is never touched.
 *
 * @param {Object} server Server payload.
 * @param {Object} draft  Draft.
 * @return {Object} POST body.
 */
export function patchOf( server, draft ) {
	const patch = {};
	const base = draftOf( server );
	const options = Object.fromEntries(
		Object.entries( draft.options ).filter(
			( [ key, value ] ) => value !== base.options[ key ]
		)
	);

	if ( Object.keys( options ).length ) {
		patch.options = options;
	}

	SECTION_KEYS.forEach( ( key ) => {
		const changed = Object.fromEntries(
			Object.entries( draft.visibility[ key ] ).filter(
				( [ id, value ] ) => value !== base.visibility[ key ][ id ]
			)
		);
		if ( Object.keys( changed ).length ) {
			patch[ key ] = changed;
		}
	} );

	return patch;
}

/**
 * Rebase the live draft onto a save response: start from the saved payload
 * and keep every value the user changed after the save was sent (it differs
 * from the snapshot that was sent), so edits made mid-save are not lost.
 *
 * @param {Object} data Save response (toSettings() result).
 * @param {Object} sent Draft snapshot the save was built from.
 * @param {Object} live Current draft.
 * @return {Object} Rebased draft.
 */
export function rebaseDraft( data, sent, live ) {
	const next = draftOf( data );
	const keep = ( target, before, now ) =>
		Object.keys( now ).forEach( ( key ) => {
			if ( now[ key ] !== before[ key ] ) {
				target[ key ] = now[ key ];
			}
		} );

	keep( next.options, sent.options, live.options );
	SECTION_KEYS.forEach( ( key ) =>
		keep(
			next.visibility[ key ],
			sent.visibility[ key ],
			live.visibility[ key ]
		)
	);

	return next;
}

/**
 * The whole settings screen state: feature switches and the three visibility
 * lists, saved together (atomic). A `400 lw_zenadmin_invalid` keeps the draft
 * and puts `data.fields` next to each field; the server saved nothing.
 *
 * @return {Object} Store.
 */
export default function useSettingsStore() {
	const [ server, setServer ] = useState( null );
	const [ draft, setDraft ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ errors, setErrors ] = useState( {} );
	const [ isSaving, setIsSaving ] = useState( false );
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const apply = useCallback( ( data ) => {
		setServer( data );
		setDraft( draftOf( data ) );
		setErrors( {} );
	}, [] );

	const reload = useCallback( () => {
		setError( null );
		return api
			.settings()
			.then( apply, ( e ) => setError( errorMessage( e ) ) );
	}, [ apply ] );

	useEffect( () => {
		reload();
	}, [ reload ] );

	const patch = server && draft ? patchOf( server, draft ) : {};
	const hasEdits = Object.keys( patch ).length > 0;

	const clearError = ( key ) =>
		setErrors( ( prev ) => {
			if ( ! ( key in prev ) ) {
				return prev;
			}
			const next = { ...prev };
			delete next[ key ];
			return next;
		} );

	const setOption = ( key, value ) => {
		setDraft( ( prev ) => ( {
			...prev,
			options: { ...prev.options, [ key ]: value },
		} ) );
		clearError( key );
	};

	const setVisibleMany = ( section, values ) => {
		setDraft( ( prev ) => ( {
			...prev,
			visibility: {
				...prev.visibility,
				[ section ]: { ...prev.visibility[ section ], ...values },
			},
		} ) );
		clearError( section );
	};

	const setVisible = ( section, id, value ) =>
		setVisibleMany( section, { [ id ]: value } );

	const save = async () => {
		if ( ! hasEdits || isSaving ) {
			return false;
		}
		setIsSaving( true );
		const sent = draft;
		let ok = false;
		try {
			const data = await api.saveSettings( patch );
			setServer( data );
			setDraft( ( live ) => rebaseDraft( data, sent, live ) );
			setErrors( {} );
			ok = true;
			createSuccessNotice( __( 'Settings saved.', 'lw-zenadmin' ), {
				type: 'snackbar',
			} );
		} catch ( e ) {
			const fields = fieldErrors( e );
			if ( fields ) {
				setErrors( fields );
			}
			createErrorNotice(
				fields
					? __(
							'Nothing was saved. Fix the highlighted settings and save again.',
							'lw-zenadmin'
						)
					: errorMessage( e ),
				{ type: 'snackbar' }
			);
		}
		setIsSaving( false );
		return ok;
	};

	return {
		server,
		draft,
		meta: server?.meta,
		isLoading: ! draft && ! error,
		error,
		reload,
		errors,
		setOption,
		setVisible,
		setVisibleMany,
		hasEdits,
		isSaving,
		discard: () => {
			setDraft( draftOf( server ) );
			setErrors( {} );
		},
		save,
	};
}
