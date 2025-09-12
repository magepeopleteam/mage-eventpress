/**
 * Enhanced Google Maps Integration for Mage EventPress
 * Professional Google Maps handling with improved error handling and coordinate persistence
 * 
 * @version 1.0.0
 * @author Cascade AI Assistant
 */

(function($) {
    'use strict';

    var MEP_GoogleMaps = {
        map: null,
        marker: null,
        geocoder: null,
        autocomplete: null,
        
        init: function() {
            this.initializeGoogleMaps();
            this.bindEvents();
        },
        
        initializeGoogleMaps: function() {
            // Initialize geocoder
            if (typeof google !== 'undefined' && google.maps) {
                this.geocoder = new google.maps.Geocoder();
            }
        },
        
        bindEvents: function() {
            var self = this;
            
            // Enhanced location input handling
            $('[name="mep_location_venue"], [name="mep_street"], [name="mep_city"], [name="mep_state"], [name="mep_postcode"], [name="mep_country"]')
                .on('input', this.debounce(function() {
                    self.geocodeAddress();
                }, 1000));
            
            // Form submission validation
            $('form').on('submit', function() {
                self.validateCoordinates();
            });
            
            // Coordinate field change handlers
            $('#latitude, #longitude').on('change', function() {
                self.updateMapFromCoordinates();
            });
        },
        
        geocodeAddress: function() {
            if (!this.geocoder) {
                console.warn('Google Maps Geocoder not available');
                return;
            }
            
            var locationInput = $('[name="mep_location_venue"]').val().trim();
            
            // Check if input looks like coordinates (lat,lng format) - flexible spacing
            var coordinatePattern = /^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/;
            var coordinateMatch = locationInput.match(coordinatePattern);
            
            if (coordinateMatch) {
                // Handle direct coordinate input
                var lat = parseFloat(coordinateMatch[1]);
                var lng = parseFloat(coordinateMatch[2]);
                
                if (this.validateCoordinateRanges(lat, lng)) {
                    this.updateCoordinates(lat, lng);
                    this.updateMapPosition(lat, lng);
                    
                    // Coordinates processed successfully
                    this.showNotification('Coordinates updated successfully', 'success');
                } else {
                    console.warn('MEP Enhanced: Invalid coordinate ranges:', lat, lng);
                    this.showNotification('Invalid coordinate ranges. Latitude must be -90 to 90, Longitude must be -180 to 180', 'error');
                }
            } else {
                // Handle regular address input
                var address = this.buildAddressString();
                
                // Only geocode if we have a meaningful address
                if (address.replace(/,\s*/g, '').trim().length > 3) {
                    this.geocoder.geocode({
                        'address': address
                    }, this.handleGeocodeResponse.bind(this));
                }
            }
        },
        
        buildAddressString: function() {
            var addressParts = [
                $('[name="mep_location_venue"]').val(),
                $('[name="mep_street"]').val(),
                $('[name="mep_city"]').val(),
                $('[name="mep_state"]').val(),
                $('[name="mep_postcode"]').val(),
                $('[name="mep_country"]').val()
            ];
            
            return addressParts.filter(function(part) {
                return part && part.trim().length > 0;
            }).join(', ');
        },
        
        handleGeocodeResponse: function(results, status) {
            if (status === google.maps.GeocoderStatus.OK && results[0]) {
                var location = results[0].geometry.location;
                var lat = location.lat();
                var lng = location.lng();
                
                this.updateCoordinates(lat, lng);
                this.updateMapPosition(lat, lng);
            } else {
                console.warn('Geocoding failed:', status);
                this.handleGeocodeError(status);
            }
        },
        
        updateCoordinates: function(lat, lng) {
            // Update hidden fields
            $('#latitude').val(lat).trigger('change');
            $('#longitude').val(lng).trigger('change');
            
            // Also update any other coordinate inputs that might exist
            $('input[name="latitude"]').val(lat);
            $('input[name="longitude"]').val(lng);
        },
        
        updateMapPosition: function(lat, lng) {
            if (this.map && this.marker) {
                var position = new google.maps.LatLng(lat, lng);
                this.map.setCenter(position);
                this.marker.setPosition(position);
            }
        },
        
        updateMapFromCoordinates: function() {
            var lat = parseFloat($('#latitude').val());
            var lng = parseFloat($('#longitude').val());
            
            if (!isNaN(lat) && !isNaN(lng)) {
                this.updateMapPosition(lat, lng);
            }
        },
        
        validateCoordinates: function() {
            var lat = $('#latitude').val();
            var lng = $('#longitude').val();
            
            if (lat && lng) {
                var latNum = parseFloat(lat);
                var lngNum = parseFloat(lng);
                
                if (isNaN(latNum) || isNaN(lngNum)) {
                    console.warn('Invalid coordinates detected, clearing...');
                    $('#latitude, #longitude').val('');
                }
                
                // Validate coordinate ranges
                if (!this.validateCoordinateRanges(latNum, lngNum)) {
                    console.warn('Coordinates out of valid range, clearing...');
                    $('#latitude, #longitude').val('');
                }
            }
        },
        
        validateCoordinateRanges: function(lat, lng) {
            return (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180);
        },
        
        handleGeocodeError: function(status) {
            var errorMessages = {
                'ZERO_RESULTS': 'No results found for this address',
                'OVER_QUERY_LIMIT': 'Google Maps API query limit exceeded',
                'REQUEST_DENIED': 'Google Maps API request denied',
                'INVALID_REQUEST': 'Invalid geocoding request',
                'UNKNOWN_ERROR': 'Unknown error occurred during geocoding'
            };
            
            var message = errorMessages[status] || 'Geocoding failed with status: ' + status;
            console.warn('Geocoding Error:', message);
            
            // Optionally show user-friendly error message
            this.showNotification(message, 'warning');
        },
        
        showNotification: function(message, type) {
            // Create a simple notification system
            var notification = $('<div class="mep-notification mep-notification-' + type + '">')
                .text(message)
                .css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    padding: '10px 15px',
                    backgroundColor: type === 'error' ? '#f44336' : '#ff9800',
                    color: 'white',
                    borderRadius: '4px',
                    zIndex: 9999,
                    fontSize: '14px'
                });
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.fadeOut(function() {
                    notification.remove();
                });
            }, 5000);
        },
        
        // Utility function for debouncing
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        // Wait for Google Maps API to load
        if (typeof google !== 'undefined' && google.maps) {
            MEP_GoogleMaps.init();
        } else {
            // Retry after a short delay if Google Maps isn't loaded yet
            setTimeout(function() {
                if (typeof google !== 'undefined' && google.maps) {
                    MEP_GoogleMaps.init();
                }
            }, 1000);
        }
    });
    
    // Make it globally accessible
    window.MEP_GoogleMaps = MEP_GoogleMaps;
    
})(jQuery);
