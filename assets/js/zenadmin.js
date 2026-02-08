/**
 * LW ZenAdmin - Notice collector and sidebar panel.
 *
 * Runs AFTER WordPress common.js has relocated notices.
 * Collects all notice elements, moves them into the sidebar panel,
 * and updates the admin bar count badge.
 *
 * @package LightweightPlugins\ZenAdmin
 */

(function ($) {
	'use strict';

	var $panel    = $( '#lw-zenadmin-panel' ),
		$overlay  = $( '#lw-zenadmin-overlay' ),
		$body     = $( '#lw-zenadmin-panel .lw-zenadmin-panel-body' ),
		$empty    = $body.find( '.lw-zenadmin-empty' ),
		$trigger  = $( '#wp-admin-bar-lw-zenadmin' ),
		$badge    = $trigger.find( '.lw-zenadmin-badge' ),
		$count    = $badge.find( '.update-count' ),
		selectors = 'div.notice, div.updated, div.error, div.update-nag',
		collected = 0;

	/**
	 * Collect all admin notices from the page and move them into the panel.
	 */
	function collectNotices() {
		$( selectors ).not( '.inline, .below-h2, .hidden, .lw-zenadmin-collected, .lw-notice' ).each(
			function () {
				var $notice = $( this );

				// Skip notices already inside our panel.
				if ( $notice.closest( '#lw-zenadmin-panel' ).length ) {
						return;
				}

				// Detach from page, add panel class, append to panel body.
				$notice
				.addClass( 'lw-zenadmin-collected' )
				.detach()
				.appendTo( $body );

				collected++;
			}
		);

		updateCount();
	}

	/**
	 * Update the admin bar count badge.
	 */
	function updateCount() {
		if ( collected > 0 ) {
			$badge.removeClass(
				function ( i, cls ) {
					return ( cls.match( /count-\d+/g ) || [] ).join( ' ' );
				}
			).addClass( 'count-' + collected ).show();
			$count.text( collected );
			$empty.hide();
		} else {
			$badge.hide();
			$empty.show();
		}
	}

	/**
	 * Open the sidebar panel.
	 */
	function openPanel() {
		$panel.addClass( 'is-open' );
		$overlay.addClass( 'is-open' );
		$( 'body' ).addClass( 'lw-zenadmin-panel-open' );
	}

	/**
	 * Close the sidebar panel.
	 */
	function closePanel() {
		$panel.removeClass( 'is-open' );
		$overlay.removeClass( 'is-open' );
		$( 'body' ).removeClass( 'lw-zenadmin-panel-open' );
	}

	// Toggle panel on admin bar click.
	$trigger.on(
		'click',
		function ( e ) {
			e.preventDefault();
			e.stopPropagation();

			if ( $panel.hasClass( 'is-open' ) ) {
				closePanel();
			} else {
				openPanel();
			}
		}
	);

	// Close panel on overlay click.
	$overlay.on( 'click', closePanel );

	// Close on X button.
	$panel.find( '.lw-zenadmin-close' ).on( 'click', closePanel );

	// Close on Escape key.
	$( document ).on(
		'keydown',
		function ( e ) {
			if ( 27 === e.keyCode && $panel.hasClass( 'is-open' ) ) {
				closePanel();
			}
		}
	);

	// Collect notices after a short delay so common.js finishes relocating them.
	setTimeout( collectNotices, 100 );

	// Also listen for dynamically added notices.
	$( document ).on(
		'wp-updates-notice-added wp-plugin-install-error wp-plugin-update-error wp-plugin-delete-error wp-theme-install-error wp-theme-delete-error wp-notice-added',
		function () {
			setTimeout( collectNotices, 50 );
		}
	);

})( jQuery );
