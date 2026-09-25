/**
 * Every REST call the admin makes, in one place (lw-zenadmin/v1, prefix
 * /admin). Responses go through ./shapes before the UI reads them, so a
 * backend shape change is a one-file fix there.
 */
/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { NAMESPACE } from './boot';
import { toSettings } from './shapes';

const path = ( route ) => `/${ NAMESPACE }/admin${ route }`;

export const api = {
	// GET → { options, sections, meta }.
	settings: () =>
		apiFetch( { path: path( '/settings' ) } ).then( toSettings ),
	// POST any subset ({ options, widgets, menus, adminbar }; atomic) → same shape.
	saveSettings: ( patch ) =>
		apiFetch( {
			path: path( '/settings' ),
			method: 'POST',
			data: patch,
		} ).then( toSettings ),
};

/**
 * Human message of a failed request.
 *
 * @param {Object} error apiFetch rejection.
 * @return {string} Message.
 */
export const errorMessage = ( error ) =>
	error?.message ||
	__(
		'That did not work. Please reload the page and try again.',
		'lw-zenadmin'
	);

/**
 * Per-field validation errors of a `400 lw_zenadmin_invalid` response.
 *
 * @param {Object} error apiFetch rejection.
 * @return {Object|null} { key: [ messages ] } or null.
 */
export function fieldErrors( error ) {
	const fields = error?.data?.fields;
	if ( ! fields || typeof fields !== 'object' ) {
		return null;
	}
	return Object.fromEntries(
		Object.entries( fields ).map( ( [ key, messages ] ) => [
			key,
			( Array.isArray( messages ) ? messages : [ messages ] ).map(
				String
			),
		] )
	);
}
