( function () {
	'use strict';

	/**
	 * Paged, dependency-free course carousel.
	 *
	 * - Shows N cards per view (N comes from CSS: --wci-per-view, capped per
	 *   breakpoint). JS measures the rendered card width, so paging always
	 *   matches whatever the CSS decided.
	 * - Autoplay advances exactly one page every `data-interval` ms, then
	 *   loops back to the start.
	 * - Prev / next buttons and pagination dots move one page at a time.
	 * - Pauses on hover / focus / touch and while off-screen; respects
	 *   prefers-reduced-motion.
	 * - Drag / swipe to scroll freely.
	 */
	function initCarousel( root ) {
		var track = root.querySelector( '.wci-carousel__track' );
		if ( ! track ) {
			return;
		}

		var scope = root.closest( '.wci-related, .wci-carousel-wrap' ) || root.parentNode;
		var autoplay = root.getAttribute( 'data-autoplay' ) !== 'false';
		var interval = parseInt( root.getAttribute( 'data-interval' ), 10 );
		if ( isNaN( interval ) || interval < 1500 ) {
			interval = 5000;
		}

		var timer = null;
		var paused = false;
		var dotsWrap = null;

		function gap() {
			var style = getComputedStyle( track );
			var g = parseFloat( style.columnGap || style.gap );
			return isNaN( g ) ? 20 : g;
		}

		function cards() {
			return track.querySelectorAll( '.wci-card' );
		}

		/** Width of one card including the gap after it. */
		function unit() {
			var card = track.querySelector( '.wci-card' );
			if ( ! card ) {
				return track.clientWidth || 1;
			}
			return card.getBoundingClientRect().width + gap();
		}

		/** How many whole cards currently fit in the viewport. */
		function perView() {
			return Math.max( 1, Math.round( ( track.clientWidth + gap() ) / unit() ) );
		}

		function pageWidth() {
			return unit() * perView();
		}

		function pageCount() {
			return Math.max( 1, Math.ceil( cards().length / perView() ) );
		}

		function currentPage() {
			return Math.round( track.scrollLeft / pageWidth() );
		}

		function isScrollable() {
			return track.scrollWidth - track.clientWidth > 4;
		}

		function goToPage( index, smooth ) {
			var pages = pageCount();
			if ( index < 0 ) {
				index = pages - 1;
			} else if ( index >= pages ) {
				index = 0;
			}
			track.scrollTo( {
				left: Math.round( index * pageWidth() ),
				behavior: smooth === false ? 'auto' : 'smooth'
			} );
		}

		function next() {
			goToPage( currentPage() + 1 );
		}

		function prev() {
			goToPage( currentPage() - 1 );
		}

		function tick() {
			if ( paused || ! isScrollable() ) {
				return;
			}
			if ( track.scrollLeft + track.clientWidth >= track.scrollWidth - 4 ) {
				goToPage( 0 );
			} else {
				next();
			}
		}

		function prefersReducedMotion() {
			return window.matchMedia
				&& window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		}

		function start() {
			if ( ! autoplay || timer || prefersReducedMotion() || ! isScrollable() ) {
				return;
			}
			timer = window.setInterval( tick, interval );
		}

		function stop() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		/* ----- Pagination dots ----- */
		function buildDots() {
			if ( ! dotsWrap ) {
				dotsWrap = document.createElement( 'div' );
				dotsWrap.className = 'wci-carousel__dots';
				root.appendChild( dotsWrap );
				dotsWrap.addEventListener( 'click', function ( e ) {
					var dot = e.target.closest( '.wci-carousel__dot' );
					if ( dot ) {
						goToPage( parseInt( dot.getAttribute( 'data-page' ), 10 ) );
					}
				} );
			}

			var pages = pageCount();
			dotsWrap.innerHTML = '';

			if ( pages < 2 || ! isScrollable() ) {
				dotsWrap.hidden = true;
				return;
			}

			dotsWrap.hidden = false;
			for ( var i = 0; i < pages; i++ ) {
				var b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'wci-carousel__dot';
				b.setAttribute( 'data-page', i );
				b.setAttribute( 'aria-label', 'Trang ' + ( i + 1 ) );
				dotsWrap.appendChild( b );
			}
			syncDots();
		}

		function syncDots() {
			if ( ! dotsWrap || dotsWrap.hidden ) {
				return;
			}
			var cur = currentPage();
			Array.prototype.forEach.call( dotsWrap.children, function ( dot, i ) {
				dot.setAttribute( 'aria-current', i === cur ? 'true' : 'false' );
			} );
		}

		/* ----- Controls ----- */
		var controls = scope.querySelector( '.wci-related__controls, .wci-carousel__controls' );

		function syncControls() {
			if ( controls ) {
				controls.style.display = isScrollable() ? '' : 'none';
			}
		}

		scope.querySelectorAll( '.wci-related__nav, .wci-carousel__nav' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				if ( btn.getAttribute( 'data-dir' ) === 'prev' ) {
					prev();
				} else {
					next();
				}
			} );
		} );

		/* ----- Pause on interaction ----- */
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

		track.addEventListener( 'scroll', syncDots, { passive: true } );

		/* ----- Recompute on resize ----- */
		var resizeTimer;
		window.addEventListener( 'resize', function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( function () {
				buildDots();
				syncControls();
				stop();
				start();
			}, 200 );
		} );

		/* ----- Pause while off-screen ----- */
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

		/* ----- Drag to scroll ----- */
		var isDown = false;
		var dragging = false;
		var startX = 0;
		var startScroll = 0;

		track.addEventListener( 'pointerdown', function ( e ) {
			isDown = true;
			dragging = false;
			startX = e.pageX;
			startScroll = track.scrollLeft;
		} );
		window.addEventListener( 'pointerup', function () {
			if ( ! isDown ) {
				return;
			}
			isDown = false;
			if ( dragging ) {
				dragging = false;
				track.classList.remove( 'is-dragging' );
				goToPage( currentPage() ); // snap to nearest page after a drag
			}
		} );
		window.addEventListener( 'pointermove', function ( e ) {
			if ( ! isDown ) {
				return;
			}
			var delta = e.pageX - startX;
			if ( ! dragging && Math.abs( delta ) < 6 ) {
				return; // ignore micro-movement so clicks still register
			}
			dragging = true;
			track.classList.add( 'is-dragging' );
			track.scrollLeft = startScroll - delta;
		} );

		/* ----- Init ----- */
		buildDots();
		syncControls();
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-wci-carousel]' ).forEach( initCarousel );
	} );
}() );
