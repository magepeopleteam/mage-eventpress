/**
 * Event Booking Dashboard JavaScript
 * Enhanced My Account Dashboard for Event Bookings
 */

(function($) {
	'use strict';
	
	const MPWEM_Dashboard = {
		
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
					search: searchValue
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
			$('#mpwem-booking-details-modal').fadeOut(300);
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
