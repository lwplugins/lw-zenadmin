/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { isKeyboardEvent } from '@wordpress/keycodes';

/**
 * Cmd/Ctrl+S saves (and never opens the browser's "Save page" dialog).
 *
 * Only listens on tabs that edit settings (`active`); elsewhere the browser
 * keeps its own shortcut.
 *
 * @param {Function} onSave  Save callback.
 * @param {boolean}  enabled Whether saving is possible right now.
 * @param {boolean}  active  Whether the current tab edits settings.
 */
export default function useSaveShortcut( onSave, enabled, active ) {
	useEffect( () => {
		if ( ! active ) {
			return;
		}
		const onKeyDown = ( event ) => {
			if ( ! isKeyboardEvent.primary( event, 's' ) ) {
				return;
			}
			event.preventDefault();
			if ( enabled ) {
				onSave();
			}
		};
		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
	}, [ onSave, enabled, active ] );
}
