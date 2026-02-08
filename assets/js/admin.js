/**
 * LW ZenAdmin - Admin JavaScript
 *
 * @package LightweightPlugins\ZenAdmin
 */

(function ($) {
	'use strict';

	/**
	 * Initialize admin functionality.
	 */
	function init() {
		initTabs();
		initFormHashPreserver();
	}

	/**
	 * Initialize tab navigation.
	 */
	function initTabs() {
		var $tabs   = $( '.lw-zenadmin-tabs a' );
		var $panels = $( '.lw-zenadmin-tab-panel' );

		$tabs.on(
			'click',
			function (e) {
				e.preventDefault();

				var target = $( this ).attr( 'href' ).replace( '#', '' );

				// Update active tab.
				$tabs.removeClass( 'active' );
				$( this ).addClass( 'active' );

				// Update active panel.
				$panels.removeClass( 'active' );
				$( '#tab-' + target ).addClass( 'active' );

				// Save to URL hash.
				if (history.pushState) {
					history.pushState( null, null, '#' + target );
				}
			}
		);

		// Check URL hash on load.
		var hash = window.location.hash.replace( '#', '' );
		if (hash) {
			var $targetTab = $tabs.filter( '[href="#' + hash + '"]' );
			if ($targetTab.length) {
				$targetTab.trigger( 'click' );
			}
		}
	}

	/**
	 * Preserve the active tab hash across form save.
	 */
	function initFormHashPreserver() {
		$( '.lw-zenadmin-settings' ).closest( 'form' ).on(
			'submit',
			function () {
				var hash = window.location.hash;
				var $tab = $( this ).find( 'input[name="lw_zenadmin_active_tab"]' );

				if (hash && $tab.length) {
					$tab.val( hash.replace( '#', '' ) );
				}
			}
		);
	}

	// Initialize on document ready.
	$( document ).ready( init );

})( jQuery );
