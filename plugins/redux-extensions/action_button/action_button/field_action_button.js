/**
 * JS Button library
 *
 * @author Kevin Provance (kprovance)
 */

(function ($) {
	'use strict';

	redux.field_objects = redux.field_objects || {};
	redux.field_objects.action_button = redux.field_objects.action_button || {};

	/*******************************************************************************
	 * init Function
	 *
	 * Runs when library is loaded.
	 ******************************************************************************/
	redux.field_objects.action_button.init = function (selector) {

		// If no selector is passed, grab one from the HTML
		if (!selector) {
			selector = $(document).find('.redux-container-action_button');
		}

		// Enum instances of our object
		$(selector).each(
			function () {
				var el = $(this);
				var parent = el;

				if (!el.hasClass('redux-field-container')) {
					parent = el.parents('.redux-field-container:first');
				}

				if (parent.hasClass('redux-field-init')) {
					parent.removeClass('redux-field-init');
				} else {
					return;
				}

				// Get the button handle
				var button = $(el).find('input#' + redux.field_objects.action_button.mainID + '_input');

				$.each(button, function (key, value) {
					$(value).on("click", function (e) {
						var data = {
							action: 'redux_action_button',
							nonce: redux.field_objects.action_button.nonce,
							field_id: field_id
						};
						$.post(redux_ajax_script.ajaxurl, data, function (response) {
						});
						var funcName = $(value).data('function');

						// Not really needed, but just in case.
						e.preventDefault();

						if (funcName !== '') {
							// Ensure custom function exists
							if (typeof(window[funcName]) === "function") {

								// Add it to the window object and execute
								window[funcName]();
							} else {

								// Let the dev know he fucked up someplace.
								throw("JS Button Error.  Function " + funcName + " does not exist.");
							}
						}
					});
				});
			}
		);
	};

})(jQuery);