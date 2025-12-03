/**
 * Passive Events Polyfill for Dynamic Pricing LatePoint
 *
 * This script ensures that scroll-blocking event listeners (touchstart, wheel)
 * are registered with the { passive: true } flag to improve scrolling performance
 * on mobile devices and in browser compatibility with modern performance standards.
 *
 * @version 1.0.0
 * @author TechXela
 *
 * Reference: https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
 */

(function () {
	'use strict';

	// Store original addEventListener method
	const originalAddEventListener = EventTarget.prototype.addEventListener;

	// List of scroll-blocking events that should be passive
	const scrollBlockingEvents = [
		'touchstart',
		'touchmove',
		'wheel',
		'mousewheel'
	];

	/**
	 * Override addEventListener to force passive flag on scroll-blocking events
	 * This prevents the browser from blocking scroll while waiting for preventDefault()
	 */
	EventTarget.prototype.addEventListener = function (type, listener, options) {
		// Convert boolean capture flag to options object if needed
		if (typeof options === 'boolean') {
			options = { capture: options };
		}

		// Ensure options is an object
		if (!options) {
			options = {};
		}

		// Force passive: true for scroll-blocking events
		if (scrollBlockingEvents.indexOf(type) !== -1) {
			options = Object.assign({}, options, { passive: true });
		}

		// Call original addEventListener with modified options
		return originalAddEventListener.call(this, type, listener, options);
	};

	// Log that polyfill is active (only in development)
	if (
		typeof console !== 'undefined' &&
		console.log &&
		window.DYNAMIC_PRICING_DEBUG
	) {
		console.log('[Dynamic Pricing LatePoint] Passive events polyfill loaded');
	}
})();
