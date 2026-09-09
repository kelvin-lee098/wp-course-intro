( function () {
	'use strict';

	/**
	 * Lightweight, dependency-free course carousel.
	 * - Horizontal scroll-snap track
	 * - Prev/next buttons (delegated from the section wrapper)
	 * - Optional gentle autoplay that pauses on hover / focus / touch / offscreen
	 * - Drag / swipe to scroll
	 */
	function initCarousel( root ) {
		var track = root.querySelector( '.wci-carousel__track' );
		if ( ! track ) {
			return;
		}

		var autoplay = root.getAttribute( 'data-autoplay' ) === 'true';
		var section = root.closest( '.wci-related' ) || root.parentNode;
		var timer = null;
		var paused = false;

		function step( dir ) {
			var card = track.querySelector( '.wci-card' );
			var amount = card ? card.getBoundingClientRect().width + 20 : track.clientWidth * 0.8;
			track.scrollBy( { left: dir * amount, behavior: 'smooth' } );
		}

		function atEnd() {
			return track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
		}

		function tick() {
			if ( paused ) {
				return;
			}
			if ( atEnd() ) {
				track.scrollTo( { left: 0, behavior: 'smooth' } );
			} else {
				step( 1 );
			}
		}

		function start() {
			if ( ! autoplay || timer || prefersReducedMotion() ) {
				return;
			}
			timer = window.setInterval( tick, 3500 );
		}

		function stop() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		function prefersReducedMotion() {
			return window.matchMedia
				&& window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		}

		// Nav buttons (buttons live in .wci-related__controls when rendered by the template).
		if ( section ) {
			section.querySelectorAll( '.wci-related__nav' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					step( btn.getAttribute( 'data-dir' ) === 'prev' ? -1 : 1 );
				} );
			} );
		}

		// Pause interactions.
		[ 'mouseenter', 'focusin', 'touchstart' ].forEach( function ( evt ) {
			root.addEventListener( evt, function () {
				paused = true;
			}, { passive: true } );
		} );
		[ 'mouseleave', 'focusout', 'touchend' ].forEach( function ( evt ) {
			root.addEventListener( evt, function () {
				paused = false;
			}, { passive: true } );
		} );

		// Pause when offscreen.
		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						start();
					} else {
						stop();
					}
				} );
			}, { threshold: 0.2 } ).observe( root );
		} else {
			start();
		}

		// Drag to scroll.
		var isDown = false;
		var startX = 0;
		var startScroll = 0;

		track.addEventListener( 'pointerdown', function ( e ) {
			isDown = true;
			startX = e.pageX;
			startScroll = track.scrollLeft;
			track.classList.add( 'is-dragging' );
		} );
		window.addEventListener( 'pointerup', function () {
			isDown = false;
			track.classList.remove( 'is-dragging' );
		} );
		window.addEventListener( 'pointermove', function ( e ) {
			if ( ! isDown ) {
				return;
			}
			track.scrollLeft = startScroll - ( e.pageX - startX );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-wci-carousel]' ).forEach( initCarousel );
	} );
}() );
