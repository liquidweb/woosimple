/* global pagenow, postboxes */

(function () {
	'use strict';

	var advancedMetaBoxes = [
		'commentsdiv',
		'postexcerpt',
		'product_catdiv',
		'tagsdiv-product_tag',
		'woocommerce-product-data',
		'woocommerce-product-images'
	],
	advancedElements = [
		'catalog-visibility',
		'visibility'
	],
	simpleElements = [
		'woosimple-price'
	],
	toggleButton = document.getElementById('woosimple-toggle-switch'),
	defaultPrice = document.getElementById('_regular_price'),
	customPrice = document.getElementById('woosimple_regular_price'),
	cache = {};

	/**
	 * Retrieve the checkbox and meta box elements, retrieving from the cache when possible.
	 *
	 * @param  {string} id - The meta box ID.
	 *
	 * @return {object} An object with two properties: toggle and metabox.
	 */
	function getMetaBox(id) {
		if (! cache.hasOwnProperty(id)) {
			cache[id] = {
				toggle: document.getElementById(id + '-hide') || {},
				metabox: document.getElementById(id) || document.createElement('div')
			};
		}

		return cache[id];
	}

	/**
	 * Hide a meta box by ID.
	 *
	 * @param {string} id - The meta box ID.
	 */
	function hideMetabox(id) {
		var box = getMetaBox(id);

		box.toggle.checked = false;
		box.metabox.setAttribute('hidden', true);
		box.metabox.style.display = 'none';
	}

	/**
	 * Show a meta box by ID.
	 *
	 * @param {string} id - The meta box ID.
	 */
	function showMetabox(id) {
		var box = getMetaBox(id);

		box.toggle.checked = true;
		box.metabox.removeAttribute('hidden');
		box.metabox.style.display = '';
	}

	/**
	 * Hide an element by ID.
	 *
	 * @param {string} id - The element ID.
	 */
	function hideElement(id) {
		document.getElementById(id).setAttribute('hidden', true);
	}

	/**
	 * Show an element by ID.
	 *
	 * @param {string} id - The element ID.
	 */
	function showElement(id) {
		document.getElementById(id).removeAttribute('hidden');
	}

	/**
	 * Set the values of both pricing fields to match that of the one that just changed.
	 *
	 * @param {Event} e - The change event.
	 */
	function syncPrices(e) {
		defaultPrice.value = e.target.value;
		customPrice.value = e.target.value;
	}

	defaultPrice.addEventListener('change', syncPrices);
	customPrice.addEventListener('change', syncPrices);

	/**
	 * Master toggle function.
	 */
	function toggleMode() {
		if (toggleButton.checked) {
			advancedMetaBoxes.map(hideMetabox);
			advancedElements.map(hideElement);
			simpleElements.map(showElement);
		} else {
			advancedMetaBoxes.map(showMetabox);
			advancedElements.map(showElement);
			simpleElements.map(hideElement);
		}
	}

	toggleMode();
	toggleButton.addEventListener('change', toggleMode);
}());
