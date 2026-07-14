/**
 * Booking social-share card.
 * Fills #mep-sc-card from window.mepSocialCardData, then rasterizes it to a PNG with
 * html2canvas so the visitor can download it or share it to Instagram. The
 * Facebook/Twitter/WhatsApp/LinkedIn buttons are plain links rendered server-side and
 * need none of this — they work even if this script or html2canvas fails to load.
 */
( function () {
	'use strict';

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) {
			fn();
		} else {
			document.addEventListener( 'DOMContentLoaded', fn );
		}
	}

	function setText( id, value ) {
		var el = document.getElementById( id );
		if ( el ) {
			el.textContent = value || '';
		}
		return el;
	}

	function fillCard( data ) {
		setText( 'mep-sc-name', data.name );
		setText( 'mep-sc-headline', data.statusLabel );
		setText( 'mep-sc-event-name', data.eventName );
		setText( 'mep-sc-brand-name', data.siteName );
		setText( 'mep-sc-footer-site', data.siteName );
		setText( 'mep-sc-ticket', data.ticketType );
		setText( 'mep-sc-date', data.eventDate );

		var ticketRow = document.getElementById( 'mep-sc-ticket-row' );
		if ( ticketRow ) {
			ticketRow.hidden = ! data.ticketType;
		}
		var dateRow = document.getElementById( 'mep-sc-date-row' );
		if ( dateRow ) {
			dateRow.hidden = ! data.eventDate;
		}

		var avatar = document.getElementById( 'mep-sc-avatar' );
		if ( avatar && data.avatarUrl ) {
			avatar.src = data.avatarUrl;
		}

		var logo = document.getElementById( 'mep-sc-logo' );
		if ( logo && data.siteLogo ) {
			logo.src = data.siteLogo;
			logo.hidden = false;
			logo.addEventListener( 'error', function () {
				logo.hidden = true;
			} );
		}
	}

	function setStatus( message, isError ) {
		var el = document.getElementById( 'mep-sc-status' );
		if ( el ) {
			el.textContent = message || '';
			el.classList.toggle( 'mep-sc-status--error', !! isError );
		}
		if ( isError && message && window.console && console.error ) {
			console.error( '[mep-social-card] ' + message );
		}
	}

	function renderToCanvas() {
		var card = document.getElementById( 'mep-sc-card' );
		if ( ! card ) {
			return Promise.reject( new Error( 'Card element not found on the page.' ) );
		}
		if ( typeof window.html2canvas === 'undefined' ) {
			return Promise.reject( new Error( 'html2canvas failed to load (blocked script, ad blocker, or offline CDN font request).' ) );
		}
		var fontsReady = ( document.fonts && document.fonts.ready ) ? document.fonts.ready : Promise.resolve();
		return fontsReady.then( function () {
			return window.html2canvas( card, {
				scale: Math.max( 2, window.devicePixelRatio || 1 ),
				useCORS: true,
				backgroundColor: null,
				logging: false,
			} );
		} );
	}

	function canvasToBlob( canvas ) {
		return new Promise( function ( resolve, reject ) {
			canvas.toBlob( function ( blob ) {
				if ( blob ) {
					resolve( blob );
				} else {
					reject( new Error( 'canvas.toBlob() returned no data.' ) );
				}
			}, 'image/png' );
		} );
	}

	function downloadBlob( blob, filename ) {
		var url = URL.createObjectURL( blob );
		var link = document.createElement( 'a' );
		link.href = url;
		link.download = filename;
		document.body.appendChild( link );
		link.click();
		document.body.removeChild( link );
		setTimeout( function () {
			URL.revokeObjectURL( url );
		}, 4000 );
	}

	function slugify( text ) {
		return ( text || 'registration' )
			.toString()
			.toLowerCase()
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /(^-|-$)/g, '' ) || 'registration';
	}

	ready( function () {
		var data = window.mepSocialCardData;
		if ( ! data ) {
			return;
		}

		fillCard( data );

		var downloadBtn = document.getElementById( 'mep-sc-download' );
		var instagramBtn = document.getElementById( 'mep-sc-share-instagram' );
		var filename = 'registration-' + slugify( data.eventName ) + '.png';

		function generateAndDownload( busyBtn, busyMessage, doneMessage, onSuccess ) {
			setStatus( busyMessage );
			if ( busyBtn ) {
				busyBtn.disabled = true;
			}
			renderToCanvas()
				.then( canvasToBlob )
				.then( function ( blob ) {
					downloadBlob( blob, filename );
					setStatus( doneMessage || '' );
					if ( onSuccess ) {
						onSuccess();
					}
				} )
				.catch( function ( err ) {
					setStatus( 'Could not generate the image. ' + ( err && err.message ? err.message : 'Please try again.' ), true );
				} )
				.finally( function () {
					if ( busyBtn ) {
						busyBtn.disabled = false;
					}
				} );
		}

		if ( downloadBtn ) {
			downloadBtn.addEventListener( 'click', function () {
				generateAndDownload( downloadBtn, 'Generating image…', '' );
			} );
		}

		if ( instagramBtn ) {
			instagramBtn.addEventListener( 'click', function () {
				generateAndDownload(
					instagramBtn,
					'Preparing image for Instagram…',
					'Image downloaded — open Instagram and share it from your gallery.',
					function () {
						window.open( 'https://www.instagram.com/', '_blank', 'noopener' );
					}
				);
			} );
		}
	} );
} )();
