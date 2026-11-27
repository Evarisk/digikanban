/* Javascript library of module Saturne */

/**
 * @namespace Saturne_Framework_Init
 *
 * @author Evarisk <technique@evarisk.com>
 * @copyright 2015-2023 Evarisk
 */

if ( ! window.digikanban ) {
	/**
	 * [digikanban description]
	 *
	 * @memberof Saturne_Framework_Init
	 *
	 * @type {Object}
	 */
	window.digikanban = {};

	/**
	 * [scriptsLoaded description]
	 *
	 * @memberof Saturne_Framework_Init
	 *
	 * @type {Boolean}
	 */
	window.digikanban.scriptsLoaded = false;
}

if ( ! window.digikanban.scriptsLoaded ) {
	/**
	 * [description]
	 *
	 * @memberof Saturne_Framework_Init
	 *
	 * @returns {void} [description]
	 */
	window.digikanban.init = function() {
		window.digikanban.load_list_script();
	};

	/**
	 * [description]
	 *
	 * @memberof Saturne_Framework_Init
	 *
	 * @returns {void} [description]
	 */
	window.digikanban.load_list_script = function() {
		if ( ! window.digikanban.scriptsLoaded) {
			var key = undefined, slug = undefined;
			for ( key in window.digikanban ) {

				if ( window.digikanban[key].init ) {
					window.digikanban[key].init();
				}

				for ( slug in window.digikanban[key] ) {

					if ( window.digikanban[key] && window.digikanban[key][slug] && window.digikanban[key][slug].init ) {
						window.digikanban[key][slug].init();
					}

				}
			}

			window.digikanban.scriptsLoaded = true;
		}
	};

	/**
	 * [description]
	 *
	 * @memberof Saturne_Framework_Init
	 *
	 * @returns {void} [description]
	 */
	window.digikanban.refresh = function() {
		var key = undefined;
		var slug = undefined;
		for ( key in window.digikanban ) {
			if ( window.digikanban[key].refresh ) {
				window.digikanban[key].refresh();
			}

			for ( slug in window.digikanban[key] ) {

				if ( window.digikanban[key] && window.digikanban[key][slug] && window.digikanban[key][slug].refresh ) {
					window.digikanban[key][slug].refresh();
				}
			}
		}
	};

	$( document ).ready( window.digikanban.init );
}

