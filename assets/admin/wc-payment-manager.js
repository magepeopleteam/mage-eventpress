/* global mepWcPaymentManager, jQuery */
( function ( $ ) {
	'use strict';

	var cfg = window.mepWcPaymentManager || {};
	var i18n = cfg.i18n || {};

	function ajax( data ) {
		return $.ajax( {
			url: cfg.ajaxUrl,
			method: 'POST',
			data: $.extend( { nonce: cfg.nonce }, data ),
		} );
	}

	$( function () {
		var $manager = $( '.mep-wc-payment-manager' ).first();
		if ( ! $manager.length ) {
			return;
		}

		// -----------------------------------------------------------
		// Keep the manager inside its settings-table row so the
		// "WooCommerce Payment Methods" accordion (built by the payment
		// settings panel) can group it. Full width is handled by the CSS
		// `:has()` fallback that hides the empty label cell and widens the
		// content cell — no DOM break-out needed. We still tag the row with
		// `woocommerce-field` so the existing payment-tab show/hide logic
		// continues to manage its visibility.
		// -----------------------------------------------------------
		if ( ! $manager.data( 'mep-relocated' ) ) {
			$manager.closest( 'tr' ).addClass( 'woocommerce-field' );
			$manager.data( 'mep-relocated', true );
		}

		// -----------------------------------------------------------
		// Expand / collapse a gateway's native settings form
		// -----------------------------------------------------------
		$manager.on( 'click', '.mep-gw-configure-btn', function () {
			var $card = $( this ).closest( '.mep-gw-card' );
			var $body = $card.find( '.mep-gw-body' );
			var open = $body.is( ':visible' );

			// Collapse others for a clean view.
			$manager.find( '.mep-gw-body' ).slideUp( 150 );
			$manager.find( '.mep-gw-configure-btn' ).text( i18n.configure || 'Configure' );

			if ( ! open ) {
				$body.slideDown( 150 );
				$( this ).text( i18n.close || 'Close' );
			}
		} );

		// -----------------------------------------------------------
		// Quick enable/disable toggle in the card header
		// -----------------------------------------------------------
		$manager.on( 'change', '.mep-gw-toggle-input', function () {
			var $input     = $( this );
			var $card      = $input.closest( '.mep-gw-card' );
			var gatewayId  = $input.data( 'gateway-id' );
			var enabled    = $input.is( ':checked' ) ? 'yes' : 'no';
			var source     = $card.data( 'gateway-source' ) || 'wc';
			var ajaxAction = source === 'builtin' ? 'mep_toggle_builtin_gateway' : 'mep_wc_toggle_gateway';

			$input.prop( 'disabled', true );

			ajax( {
				action:     ajaxAction,
				gateway_id: gatewayId,
				enabled:    enabled,
			} )
				.done( function ( res ) {
					if ( res && res.success ) {
						// Reflect the gateway's REAL state — WooCommerce may have
						// refused to enable it (e.g. unsupported store currency), in
						// which case the checkbox snaps back and we explain why.
						var reallyOn = !! ( res.data && res.data.enabled === 'yes' );
						$input.prop( 'checked', reallyOn );
						applyEnabledState( $card, reallyOn );
						if ( res.data && res.data.notice ) {
							window.alert( res.data.notice );
						}
						$( document ).trigger( 'mep:gateways-refresh' );
					} else {
						$input.prop( 'checked', ! $input.is( ':checked' ) );
						window.alert( ( res && res.data ) || i18n.error );
					}
				} )
				.fail( function () {
					$input.prop( 'checked', ! $input.is( ':checked' ) );
					window.alert( i18n.error );
				} )
				.always( function () {
					$input.prop( 'disabled', false );
				} );
		} );

		// -----------------------------------------------------------
		// Save a gateway's native form (process_admin_options)
		// -----------------------------------------------------------
		$manager.on( 'submit', '.mep-gw-form', function ( e ) {
			e.preventDefault();

			var $form = $( this );
			var $card = $form.closest( '.mep-gw-card' );
			var gatewayId = $form.data( 'gateway-id' );
			var $btn = $form.find( '.mep-gw-save-btn' );
			var $status = $form.find( '.mep-gw-status' );

			// Native WC field names are woocommerce_{id}_{field}; submit as-is.
			var payload = { action: 'mep_wc_save_gateway', gateway_id: gatewayId };
			$.each( $form.find( ':input' ).serializeArray(), function ( i, f ) {
				payload[ f.name ] = f.value;
			} );

			$btn.prop( 'disabled', true );
			$status.removeClass( 'is-success is-error' ).text( i18n.saving || 'Saving…' );

			ajax( payload )
				.done( function ( res ) {
					if ( res && res.success ) {
						$status.addClass( 'is-success' ).text( res.data.message || i18n.saved );
						applyEnabledState( $card, res.data.enabled === 'yes' );
						// Sync the header toggle with the saved Enable checkbox.
						$card.find( '.mep-gw-toggle-input' ).prop( 'checked', res.data.enabled === 'yes' );
						setTimeout( function () {
							$status.removeClass( 'is-success' ).text( '' );
						}, 2500 );
					} else {
						$status.addClass( 'is-error' ).text( ( res && res.data ) || i18n.error );
					}
				} )
				.fail( function () {
					$status.addClass( 'is-error' ).text( i18n.error );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );

		function applyEnabledState( $card, isOn ) {
			$card.toggleClass( 'is-enabled', isOn ).toggleClass( 'is-disabled', ! isOn );
			$card.find( '.mep-gw-badge' ).text( isOn ? ( i18n.enabled || 'Enabled' ) : ( i18n.disabled || 'Disabled' ) );
		}

		// Initialise WC enhanced selects / tooltips inside the forms.
		try {
			if ( $.fn.selectWoo ) {
				$manager.find( 'select.wc-enhanced-select' ).selectWoo();
			} else if ( $.fn.select2 ) {
				$manager.find( 'select.wc-enhanced-select' ).select2();
			}
			$( document.body ).trigger( 'init_tooltips' );
		} catch ( err ) {
			/* non-fatal — fields still work as plain inputs */
		}
	} );
} )( jQuery );
