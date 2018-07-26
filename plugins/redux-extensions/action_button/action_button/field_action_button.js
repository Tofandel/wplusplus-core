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
				var button = $(el).find('button.redux-action-button');

				$.each(button, function (key, value) {
					$(value).on("click", function (e) {
						// Not really needed, but just in case.
						e.preventDefault();
						var field_id = $(this).closest('.redux-action-button-container').data('id'),
							button_id = $(this).data('id'),
							data = {
								action: 'redux_action_button_' + field_id,
								nonce: redux.field_objects.action_button.nonce,
								button_id: button_id
							};
						$.post(redux_ajax_script.ajaxurl, data, function (response) {
						});
					});
				});
			}
		);
	};

})(jQuery);