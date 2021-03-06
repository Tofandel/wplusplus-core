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
						var that = $(this),
							field = $(this).closest('.redux-action-button-container'),
							button_id = $(this).data('id'),
							data = {
								action: 'redux_action_button_' + field.data('id'),
								nonce: field.data('nonce'),
								field_id: field.data('id'),
								button_id: button_id
							};

						that.attr('disabled', true);
						$('.redux-action_bar .spinner').addClass('is-active');
						var save_warn = $('.redux-save-warn').is(':visible');
						if (save_warn) {
							$('.redux-save-warn').slideUp();
						}
						$.post(redux_ajax_script.ajaxurl, data, function (response) {
							that.removeAttr('disabled');
							$('.redux-action_bar .spinner').removeClass('is-active');
							if (save_warn) {
								$('.redux-save-warn').slideDown('fast');
							}
							if ('success' in response && response.success) {
								var $notification_bar = jQuery(document.getElementById('redux_notification_bar'));
								$notification_bar.append('<div class="executed_notice admin-notice notice-green">' + response.message + '</div>').slideDown('fast');
								setTimeout(function () {
									$notification_bar.find('.executed_notice').slideUp('fast', function () {
										$notification_bar.find('.executed_notice').remove();
									});
								}, 5000);
							}
						});
					});
				});
			}
		);
	};

})(jQuery);