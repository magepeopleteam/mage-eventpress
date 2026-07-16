/* global mepOrders, jQuery */
( function ( $ ) {
	'use strict';

	var currentPage    = 1;
	var filterTimeout  = null;
	var activeFilters  = {};

	/* ---- Collect current filter values ---- */
	function getFilters() {
		return {
			mep_search    : $( '#mep-search'    ).val() || '',
			mep_event_id  : $( '#mep-event-id'  ).val() || '',
			mep_status    : $( '#mep-status'    ).val() || '',
			mep_gateway   : $( '#mep-gateway'   ).val() || '',
			mep_source    : $( '#mep-source'    ).val() || 'all',
			mep_date_from : $( '#mep-date-from' ).val() || '',
			mep_date_to   : $( '#mep-date-to'   ).val() || '',
		};
	}

	/* ---- AJAX fetch ---- */
	function fetchOrders( page ) {
		page = page || 1;
		currentPage = page;

		var filters = getFilters();
		activeFilters = filters;

		$( '#mep-table-loader' ).show();
		$( '#mep-orders-tbody' ).css( 'opacity', '0.4' );

		$.ajax( {
			url    : mepOrders.ajaxUrl,
			method : 'POST',
			data   : $.extend( {}, filters, {
				action : 'mep_orders_filter',
				nonce  : mepOrders.nonce,
				paged  : page,
			} ),
			success: function ( res ) {
				if ( ! res.success ) {
					alert( mepOrders.i18n.error );
					return;
				}
				var d = res.data;

				$( '#mep-orders-tbody' ).html( d.tbody ).css( 'opacity', '1' );

				// Stats
				$( '#mep-stat-total' ).text( d.total );
				$( '#mep-stat-revenue' ).html( d.revenue );

				// Result count text
				var label = d.total === 1
					? d.total + ' order found'
					: d.total + ' orders found';
				$( '.mep-result-count' ).text( label );

				// Pagination
				renderPagination( page, d.pages, d.total );

				// Add data-labels for mobile
				addDataLabels();

				// The tbody was replaced — reset bulk selection state.
				$( '#mep-check-all' ).prop( 'checked', false );
				updateBulkState();
			},
			error: function () {
				alert( mepOrders.i18n.error );
			},
			complete: function () {
				$( '#mep-table-loader' ).hide();
				$( '#mep-orders-tbody' ).css( 'opacity', '1' );
			}
		} );
	}

	/* ---- Render pagination ---- */
	function renderPagination( current, totalPages, totalItems ) {
		var $pag  = $( '#mep-pagination' );
		var perPage = 20;
		var start = ( ( current - 1 ) * perPage ) + 1;
		var end   = Math.min( current * perPage, totalItems );
		var html  = '';

		html += '<div class="mep-pagination-info">Showing ' + start + '–' + end + ' of ' + totalItems + ' orders</div>';

		if ( totalPages > 1 ) {
			var disFirst = current <= 1 ? 'mep-page-btn-disabled" disabled' : '"';
			var disLast  = current >= totalPages ? 'mep-page-btn-disabled" disabled' : '"';
			var disPrev  = current <= 1 ? 'mep-page-btn-disabled" disabled' : '"';
			var disNext  = current >= totalPages ? 'mep-page-btn-disabled" disabled' : '"';

			html += '<div class="mep-pagination-links">';

			// First
			html += '<button type="button" class="mep-page-btn mep-page-edge ' + disFirst + ' data-page="1" title="First page"><span class="dashicons dashicons-controls-skipback"></span></button>';
			// Prev
			html += '<button type="button" class="mep-page-btn ' + disPrev + ' data-page="' + ( current - 1 ) + '"><span class="dashicons dashicons-arrow-left-alt2"></span></button>';

			// Leading ellipsis
			if ( current > 3 ) {
				html += '<button type="button" class="mep-page-btn" data-page="1">1</button>';
				if ( current > 4 ) { html += '<span class="mep-page-ellipsis">…</span>'; }
			}

			// Numbered range
			var range = 2;
			for ( var i = Math.max( 1, current - range ); i <= Math.min( totalPages, current + range ); i++ ) {
				var active = i === current ? ' mep-page-btn-active' : '';
				html += '<button type="button" class="mep-page-btn' + active + '" data-page="' + i + '">' + i + '</button>';
			}

			// Trailing ellipsis
			if ( current < totalPages - 2 ) {
				if ( current < totalPages - 3 ) { html += '<span class="mep-page-ellipsis">…</span>'; }
				html += '<button type="button" class="mep-page-btn" data-page="' + totalPages + '">' + totalPages + '</button>';
			}

			// Next
			html += '<button type="button" class="mep-page-btn ' + disNext + ' data-page="' + ( current + 1 ) + '"><span class="dashicons dashicons-arrow-right-alt2"></span></button>';
			// Last
			html += '<button type="button" class="mep-page-btn mep-page-edge ' + disLast + ' data-page="' + totalPages + '" title="Last page"><span class="dashicons dashicons-controls-skipforward"></span></button>';

			html += '</div>';

			// Jump to page
			html += '<div class="mep-pagination-jump">'
				+ '<label>Go to</label>'
				+ '<input type="number" id="mep-page-jump" min="1" max="' + totalPages + '" placeholder="' + current + '" />'
				+ '<button type="button" id="mep-page-jump-btn" class="mep-btn mep-btn-outline mep-btn-sm" data-total-pages="' + totalPages + '">Go</button>'
				+ '</div>';
		}

		$pag.html( html );
	}

	/* ---- Add data-label attributes for mobile responsive table ---- */
	function addDataLabels() {
		var headers = [];
		$( '.mep-orders-table thead th' ).each( function () {
			headers.push( $( this ).text().trim() );
		} );

		$( '.mep-orders-table tbody tr' ).each( function () {
			$( this ).find( 'td' ).each( function ( i ) {
				$( this ).attr( 'data-label', headers[ i ] || '' );
			} );
		} );
	}

	/* ---- Bulk actions ---- */
	// Refresh the bulk toolbar: how many rows are selected, the select-all state,
	// and whether the Apply button is usable.
	function updateBulkState() {
		var $rows    = $( '.mep-row-check' );
		var $checked = $rows.filter( ':checked' );
		var count    = $checked.length;

		$( '#mep-check-all' ).prop( 'checked', count > 0 && count === $rows.length );
		$( '#mep-bulk-apply' ).prop( 'disabled', count === 0 || ! $( '#mep-bulk-status' ).val() );
		$( '.mep-bulk-count' ).text( count ? count + ' selected' : '' );
	}

	function applyBulkStatus() {
		var ids = $( '.mep-row-check:checked' ).map( function () { return $( this ).val(); } ).get();
		var status = $( '#mep-bulk-status' ).val();
		if ( ! ids.length || ! status ) {
			return;
		}
		var label = $( '#mep-bulk-status option:selected' ).text();
		if ( ! window.confirm( 'Change status of ' + ids.length + ' order(s) to "' + label + '"?' ) ) {
			return;
		}

		var $apply = $( '#mep-bulk-apply' ).prop( 'disabled', true ).text( 'Updating…' );
		$( '#mep-table-loader' ).show();

		$.ajax( {
			url    : mepOrders.ajaxUrl,
			method : 'POST',
			data   : {
				action    : 'mep_orders_bulk_status',
				nonce     : mepOrders.nonce,
				status    : status,
				order_ids : ids,
			},
			success: function ( res ) {
				if ( ! res.success ) {
					alert( ( res.data && res.data.message ) ? res.data.message : mepOrders.i18n.error );
					return;
				}
				$( '#mep-bulk-status' ).val( '' );
				fetchOrders( currentPage ); // reload the current page with the new statuses
			},
			error: function () {
				alert( mepOrders.i18n.error );
			},
			complete: function () {
				$apply.text( 'Apply' );
				$( '#mep-table-loader' ).hide();
				updateBulkState();
			}
		} );
	}

	/* ---- Debounced filter trigger ---- */
	function triggerFilter( delay ) {
		clearTimeout( filterTimeout );
		filterTimeout = setTimeout( function () {
			fetchOrders( 1 );
		}, delay || 0 );
	}

	/* ---- CSV Export ---- */
	function exportCsv() {
		var filters  = activeFilters && Object.keys( activeFilters ).length ? activeFilters : getFilters();
		var params   = $.extend( {}, filters, {
			action : 'mep_orders_export_csv',
			nonce  : mepOrders.nonce,
		} );
		var query    = $.param( params );
		var url      = mepOrders.ajaxUrl + '?' + query;
		window.location.href = url;
	}

	/* ---- Init ---- */
	$( document ).ready( function () {

		// Filter inputs — live update
		$( '#mep-search' ).on( 'input', function () {
			triggerFilter( 400 );
		} );

		$( '#mep-event-id, #mep-status, #mep-gateway, #mep-source, #mep-date-from, #mep-date-to' ).on( 'change', function () {
			triggerFilter( 50 );
		} );

		// Reset filters
		$( '#mep-reset-filters' ).on( 'click', function () {
			$( '#mep-search'    ).val( '' );
			$( '#mep-event-id'  ).val( '' );
			$( '#mep-status'    ).val( '' );
			$( '#mep-gateway'   ).val( '' );
			$( '#mep-source'    ).val( 'all' );
			$( '#mep-date-from' ).val( '' );
			$( '#mep-date-to'   ).val( '' );
			fetchOrders( 1 );
		} );

		// Pagination — page buttons (delegated)
		$( document ).on( 'click', '.mep-page-btn:not([disabled])', function () {
			var page = parseInt( $( this ).data( 'page' ), 10 );
			if ( page ) {
				fetchOrders( page );
				$( 'html, body' ).animate( { scrollTop: $( '.mep-table-wrap' ).offset().top - 40 }, 200 );
			}
		} );

		// Pagination — jump to page
		$( document ).on( 'click', '#mep-page-jump-btn', function () {
			var totalPages = parseInt( $( this ).data( 'total-pages' ), 10 ) || 1;
			var page       = parseInt( $( '#mep-page-jump' ).val(), 10 );
			if ( page && page >= 1 && page <= totalPages ) {
				fetchOrders( page );
				$( 'html, body' ).animate( { scrollTop: $( '.mep-table-wrap' ).offset().top - 40 }, 200 );
			}
		} );

		$( document ).on( 'keydown', '#mep-page-jump', function ( e ) {
			if ( e.key === 'Enter' ) {
				$( '#mep-page-jump-btn' ).trigger( 'click' );
			}
		} );

		// Export CSV
		$( '#mep-export-csv' ).on( 'click', function () {
			exportCsv();
		} );

		// Order detail: print / download (save as PDF via print dialog)
		$( document ).on( 'click', '#mep-print-order, #mep-download-order', function () {
			window.print();
		} );

		// Filter panel toggle
		$( '#mep-filter-toggle' ).on( 'click', function () {
			var $body = $( '#mep-filter-body' );
			var $btn  = $( this );
			$body.toggleClass( 'mep-hidden' );
			$btn.toggleClass( 'collapsed' );
			$btn.attr( 'aria-expanded', ! $body.hasClass( 'mep-hidden' ) );
		} );

		// Add data-labels on initial load
		addDataLabels();

		/* =========================================================
		 * Bulk status change
		 * ========================================================= */
		$( document ).on( 'change', '#mep-check-all', function () {
			$( '.mep-row-check' ).prop( 'checked', $( this ).prop( 'checked' ) );
			updateBulkState();
		} );
		$( document ).on( 'change', '.mep-row-check', updateBulkState );
		$( '#mep-bulk-status' ).on( 'change', updateBulkState );
		$( '#mep-bulk-apply' ).on( 'click', applyBulkStatus );

		/* =========================================================
		 * Order Detail — Status Change
		 * ========================================================= */
		$( document ).on( 'click', '#mep-update-status', function() {
			var $btn      = $( this );
			var orderId   = $btn.data( 'order-id' );
			var newStatus = $( '#mep-order-status-select' ).val();
			var $feedback = $( '#mep-status-feedback' );

			$btn.prop( 'disabled', true ).text( 'Updating…' );
			$feedback.text( '' ).removeClass( 'mep-feedback-success mep-feedback-error' );

			$.ajax( {
				url    : mepOrders.ajaxUrl,
				method : 'POST',
				data   : {
					action    : 'mep_order_update_status',
					nonce     : mepOrders.nonce,
					order_id  : orderId,
					status    : newStatus,
				},
				success: function( res ) {
					if ( res.success ) {
						$feedback.text( 'Status updated!' ).addClass( 'mep-feedback-success' );
						// Update the header status pill
						$( '.mep-status-inline' )
							.removeClass( 'mep-status-publish mep-status-pending mep-status-processing mep-status-on-hold mep-status-cancelled mep-status-failed mep-status-refunded' )
							.addClass( 'mep-status-' + res.data.new_status )
							.text( res.data.label );
						// Reload notes list to show the system note
						location.reload();
					} else {
						$feedback.text( res.data || 'Error updating status' ).addClass( 'mep-feedback-error' );
					}
				},
				error: function() {
					$feedback.text( 'Request failed' ).addClass( 'mep-feedback-error' );
				},
				complete: function() {
					$btn.prop( 'disabled', false ).text( 'Update' );
					setTimeout( function() { $feedback.fadeOut( 300, function() { $( this ).show().text( '' ); } ); }, 3000 );
				}
			} );
		} );

		/* =========================================================
		 * Order Detail — Resend PDF ticket
		 * ========================================================= */
		$( document ).on( 'click', '#mep-resend-pdf', function () {
			var $btn = $( this );
			if ( $btn.prop( 'disabled' ) ) { return; }
			var orderId  = $btn.data( 'order' );
			var original = $btn.html();

			$btn.prop( 'disabled', true ).html( '<span class="dashicons dashicons-email-alt"></span> ' + 'Sending…' );

			$.ajax( {
				url    : mepOrders.ajaxUrl,
				method : 'POST',
				data   : {
					action   : 'mep_order_resend_pdf',
					nonce    : mepOrders.nonce,
					order_id : orderId
				},
				success: function ( res ) {
					var msg = ( res && res.data && res.data.message )
						? res.data.message
						: ( res && res.success ? 'PDF ticket re-sent.' : mepOrders.i18n.error );
					window.alert( msg );
				},
				error: function () {
					window.alert( mepOrders.i18n.error );
				},
				complete: function () {
					$btn.prop( 'disabled', false ).html( original );
				}
			} );
		} );

		/* =========================================================
		 * Order Detail — Add Note
		 * ========================================================= */
		$( document ).on( 'click', '#mep-add-note-btn', function() {
			var $btn     = $( this );
			var orderId  = $btn.data( 'order-id' );
			var noteText = $( '#mep-new-note' ).val().trim();
			var noteType = $( '#mep-note-type' ).val();

			if ( ! noteText ) {
				$( '#mep-new-note' ).focus();
				return;
			}

			$btn.prop( 'disabled', true );

			$.ajax( {
				url    : mepOrders.ajaxUrl,
				method : 'POST',
				data   : {
					action    : 'mep_order_add_note',
					nonce     : mepOrders.nonce,
					order_id  : orderId,
					note      : noteText,
					note_type : noteType,
				},
				success: function( res ) {
					if ( res.success ) {
						// Remove "no notes" message
						$( '.mep-no-notes' ).remove();
						// Prepend new note
						$( '#mep-notes-list' ).prepend( res.data.html );
						// Clear form
						$( '#mep-new-note' ).val( '' );
					} else {
						alert( res.data || 'Failed to add note' );
					}
				},
				error: function() {
					alert( 'Request failed' );
				},
				complete: function() {
					$btn.prop( 'disabled', false );
				}
			} );
		} );

		/* =========================================================
		 * Order Detail — Delete Note
		 * ========================================================= */
		$( document ).on( 'click', '.mep-note-delete', function( e ) {
			e.preventDefault();
			if ( ! confirm( 'Delete this note?' ) ) {
				return;
			}

			var $note = $( this ).closest( '.mep-note-item' );
			var noteId = $( this ).data( 'note-id' );

			$.ajax( {
				url    : mepOrders.ajaxUrl,
				method : 'POST',
				data   : {
					action   : 'mep_order_delete_note',
					nonce    : mepOrders.nonce,
					note_id  : noteId,
				},
				success: function( res ) {
					if ( res.success ) {
						$note.slideUp( 200, function() {
							$( this ).remove();
							// Show "no notes" if list is empty
							if ( $( '#mep-notes-list .mep-note-item' ).length === 0 ) {
								$( '#mep-notes-list' ).html(
									'<li class="mep-no-notes"><span class="dashicons dashicons-format-chat"></span><p>No notes yet. Add the first note above.</p></li>'
								);
							}
						} );
					} else {
						alert( res.data || 'Failed to delete note' );
					}
				},
				error: function() {
					alert( 'Request failed' );
				}
			} );
		} );

	} );

} )( jQuery );
