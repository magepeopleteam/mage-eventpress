/**
 * Event Booking Dashboard JavaScript
 * Enhanced My Account Dashboard for Event Bookings
 */

(function($) {
	'use strict';
	
	const MPWEM_Dashboard = {
		
		// Current filter state
		currentFilter: 'all',
		
		/**
		 * Initialize
		 */
		init: function() {
			this.bindEvents();
		},
		
		/**
		 * Bind events
		 */
		bindEvents: function() {
			// Search button
			$(document).on('click', '#mpwem-search-btn', this.searchBookings.bind(this));
			
			// Reset button
			$(document).on('click', '#mpwem-reset-btn', this.resetSearch.bind(this));
			
			// Enter key in search
			$(document).on('keypress', '#mpwem-search-order', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					MPWEM_Dashboard.searchBookings();
				}
			});
			
			// Stats filter click
			$(document).on('click', '.mpwem-stat-clickable', this.filterByStats.bind(this));
			
			// View booking details
			$(document).on('click', '.mpwem-btn-view', this.viewBookingDetails.bind(this));
			
			// Close modal
			$(document).on('click', '.mpwem-modal-close', this.closeModal.bind(this));
			$(document).on('click', '.mpwem-modal', function(e) {
				if ($(e.target).hasClass('mpwem-modal')) {
					MPWEM_Dashboard.closeModal();
				}
			});
			
			// ESC key to close modal
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape') {
					MPWEM_Dashboard.closeModal();
				}
			});
			
			// Cancel request button
			$(document).on('click', '.mpwem-btn-cancel:not(.mpwem-btn-disabled)', this.openCancelModal.bind(this));
		},
		
		/**
		 * Filter bookings by stats
		 */
		filterByStats: function(e) {
			const filter = $(e.currentTarget).data('filter');
			
			// Update active state
			$('.mpwem-stat-clickable').removeClass('active');
			$(e.currentTarget).addClass('active');
			
			// Update current filter
			this.currentFilter = filter;
			
			// Load filtered bookings
			this.searchBookings();
		},
		
		/**
		 * Search bookings
		 */
		searchBookings: function() {
			const searchValue = $('#mpwem-search-order').val().trim();
			
			$('#mpwem-bookings-list').html('<tr><td colspan="6" class="mpwem-loading"><span class="spinner is-active"></span> ' + 
				'Loading...</td></tr>');
			
			$.ajax({
				url: mpwem_account_vars.ajax_url,
				type: 'POST',
				data: {
					action: 'mpwem_search_bookings',
					nonce: mpwem_account_vars.nonce,
					search: searchValue,
					filter: this.currentFilter
				},
				success: function(response) {
					if (response.success) {
						$('#mpwem-bookings-list').html(response.data.html);
					} else {
						MPWEM_Dashboard.showNotification('error', response.data.message || 'An error occurred');
					}
				},
				error: function() {
					MPWEM_Dashboard.showNotification('error', 'Failed to search bookings. Please try again.');
					$('#mpwem-bookings-list').html('<tr><td colspan="6" class="mpwem-no-bookings">Failed to load bookings.</td></tr>');
				}
			});
		},
		
		/**
		 * Reset search
		 */
		resetSearch: function() {
			$('#mpwem-search-order').val('');
			$('.mpwem-stat-clickable').removeClass('active');
			$('.mpwem-stat-clickable[data-filter="all"]').addClass('active');
			this.currentFilter = 'all';
			this.searchBookings();
		},
		
		/**
		 * View booking details
		 */
		viewBookingDetails: function(e) {
			e.preventDefault();
			
			const orderId = $(e.currentTarget).data('order-id');
			
			if (!orderId) {
				this.showNotification('error', 'Invalid order ID');
				return;
			}
			
			// Show modal with loading state
			$('#mpwem-booking-details-modal').fadeIn(300);
			$('#mpwem-booking-details-content').html(
				'<div class="mpwem-loading"><span class="spinner is-active"></span> Loading booking details...</div>'
			);
			
			// Fetch booking details
			$.ajax({
				url: mpwem_account_vars.ajax_url,
				type: 'POST',
				data: {
					action: 'mpwem_get_booking_details',
					nonce: mpwem_account_vars.nonce,
					order_id: orderId
				},
				success: function(response) {
					if (response.success) {
						$('#mpwem-booking-details-content').html(response.data.html);
						MPWEM_Dashboard.scrollToTop('#mpwem-booking-details-modal .mpwem-modal-content');
					} else {
						$('#mpwem-booking-details-content').html(
							'<div class="mpwem-error" style="padding: 40px 20px; text-align: center; color: #d63638;">' +
							'<p>' + (response.data.message || 'Failed to load booking details') + '</p>' +
							'</div>'
						);
					}
				},
				error: function() {
					$('#mpwem-booking-details-content').html(
						'<div class="mpwem-error" style="padding: 40px 20px; text-align: center; color: #d63638;">' +
						'<p>Failed to load booking details. Please try again.</p>' +
						'</div>'
					);
				}
			});
		},
		
		/**
		 * Close modal
		 */
		closeModal: function() {
			$('.mpwem-modal').fadeOut(300);
		},
			
		/**
		 * Open cancel request modal
		 */
		openCancelModal: function(e) {
			e.preventDefault();
				
			const cancelUrl = $(e.currentTarget).attr('href');
			if (!cancelUrl || cancelUrl === '#') {
				return;
			}
				
			// Parse URL to get order details
			const urlParams = new URLSearchParams(cancelUrl.split('?')[1]);
			const orderId = urlParams.get('serial');
				
			if (!orderId) {
				this.showNotification('error', 'Invalid order ID');
				return;
			}
				
			// Build modal content
			const modalContent = `
				<div class="mpwem-cancel-request-form">
					<h3>Order Cancellation Request</h3>
					<form id="mpwem-cancel-form" method="post">
						<div class="mpwem-form-group">
							<label><strong>Order No:</strong> #${orderId}</label>
						</div>
						<div class="mpwem-form-group">
							<label for="mpwem_order_cancel_reason">
								<strong>Cancel Reason</strong>
								<p style="margin: 5px 0; color: #666; font-size: 13px;">Please write details about why you want to cancel.</p>
							</label>
							<textarea 
								name="mep_order_cancel_reason" 
								id="mpwem_order_cancel_reason" 
								class="mpwem-textarea" 
								rows="6" 
								required
								placeholder="Enter your reason for cancellation..."
							></textarea>
						</div>
						<input type="hidden" name="action" value="mep_cancel_req_submit">
						<input type="hidden" name="order_id" value="${orderId}">
						<div class="mpwem-form-actions">
							<button type="submit" class="mpwem-btn mpwem-btn-primary" id="mpwem-submit-cancel">
								<span class="dashicons dashicons-yes"></span>
								Send Request
							</button>
							<button type="button" class="mpwem-btn mpwem-btn-secondary mpwem-modal-close">
								<span class="dashicons dashicons-no"></span>
								Cancel
							</button>
						</div>
					</form>
				</div>
			`;
				
			// Show modal
			$('#mpwem-cancel-modal-content').html(modalContent);
			$('#mpwem-cancel-modal').fadeIn(300);
				
			// Focus on textarea
			setTimeout(function() {
				$('#mpwem_order_cancel_reason').focus();
			}, 350);
				
			// Handle form submission
			$('#mpwem-cancel-form').off('submit').on('submit', function(e) {
				e.preventDefault();
				MPWEM_Dashboard.submitCancelRequest($(this));
			});
		},
			
		/**
		 * Submit cancel request
		 */
		submitCancelRequest: function($form) {
			const reason = $('#mpwem_order_cancel_reason').val().trim();
				
			if (!reason) {
				this.showNotification('error', 'Please provide a reason for cancellation');
				return;
			}
				
			const $submitBtn = $('#mpwem-submit-cancel');
			const originalText = $submitBtn.html();
				
			$submitBtn.prop('disabled', true).html('<span class="spinner is-active" style="float:none; margin:0;"></span> Sending...');
				
			$.ajax({
				url: window.location.href.split('?')[0],
				type: 'POST',
				data: $form.serialize(),
				success: function(response) {
					// Close modal
					MPWEM_Dashboard.closeModal();
						
					// Show success message
					MPWEM_Dashboard.showNotification('success', 'Your cancellation request has been received');
						
					// Reload page after a short delay
					setTimeout(function() {
						window.location.href = window.location.href.split('?')[0];
					}, 2000);
				},
				error: function() {
					$submitBtn.prop('disabled', false).html(originalText);
					MPWEM_Dashboard.showNotification('error', 'Failed to submit request. Please try again.');
				}
			});
		},
		
		/**
		 * Show notification
		 */
		showNotification: function(type, message) {
			// Remove existing notifications
			$('.mpwem-notification').remove();
			
			const typeClass = type === 'error' ? 'mpwem-notification-error' : 'mpwem-notification-success';
			const icon = type === 'error' ? '<span class="dashicons dashicons-warning"></span>' : '<span class="dashicons dashicons-yes"></span>';
			
			const notification = $('<div class="mpwem-notification ' + typeClass + '">' + icon + ' ' + message + '</div>');
			
			notification.css({
				position: 'fixed',
				top: '20px',
				right: '20px',
				padding: '15px 20px',
				background: type === 'error' ? '#d63638' : '#00a32a',
				color: '#fff',
				borderRadius: '4px',
				boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
				zIndex: 999999,
				display: 'flex',
				alignItems: 'center',
				gap: '10px',
				fontSize: '14px',
				fontWeight: '500',
				animation: 'slideInRight 0.3s ease'
			});
			
			$('body').append(notification);
			
			setTimeout(function() {
				notification.fadeOut(300, function() {
					$(this).remove();
				});
			}, 5000);
		},
		
		/**
		 * Scroll to top of element
		 */
		scrollToTop: function(element) {
			const $element = $(element);
			if ($element.length) {
				$element.animate({
					scrollTop: 0
				}, 300);
			}
		},
		
		/**
		 * Scroll page to element
		 */
		scrollToElement: function(element, offset) {
			offset = offset || 100;
			$('html, body').animate({
				scrollTop: $(element).offset().top - offset
			}, 500);
		}
	};
	
	// Initialize when document is ready
	$(document).ready(function() {
		MPWEM_Dashboard.init();
		
		// Set default active state for "Total Bookings"
		$('.mpwem-stat-clickable[data-filter="all"]').addClass('active');
		
		// Check if cancel request was triggered from URL
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('action') === 'mep_order_cancel_req') {
			// Find the cancel button for this order and trigger click
			const orderId = urlParams.get('serial');
			if (orderId) {
				setTimeout(function() {
					$('.mpwem-btn-cancel[href*="serial=' + orderId + '"]').trigger('click');
				}, 500);
			}
		}
	});
	
	// Add CSS animation
	const style = document.createElement('style');
	style.textContent = `
		@keyframes slideInRight {
			from {
				transform: translateX(100%);
				opacity: 0;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
	`;
	document.head.appendChild(style);
	
})(jQuery);
