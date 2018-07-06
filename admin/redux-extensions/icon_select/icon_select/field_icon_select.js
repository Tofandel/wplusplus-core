/* global confirm, redux, redux_change */

(function ($) {
	"use strict";

	redux.field_objects = redux.field_objects || {};
	redux.field_objects.icon_select = redux.field_objects.icon_select || {};

	redux.field_objects.icon_select.init = function (selector) {
		if (!selector) {
			selector = $(document).find(".redux-group-tab:visible").find('.redux-container-icon_select:visible');
		}

		$(selector).each(
			function () {
				var el = $(this);
				var parent = el;

				if (!el.hasClass('redux-field-container')) {
					parent = el.parents('.redux-field-container:first');
				}

				if (parent.is(":hidden")) { // Skip hidden fields
					return;
				}

				if (parent.hasClass('redux-field-init')) {
					parent.removeClass('redux-field-init');
				} else {
					return;
				}

				// On label click, change the input and class
				el.find('.redux-icon-select label i, .redux-icon-select label .tiles').click(function (e) {
					var id = $(this).closest('label').attr('for');
					$(this).parents("fieldset:first").find('.redux-icon-select-selected').removeClass('redux-icon-select-selected');
					$(this).closest('label').find('input[type="radio"]').prop('checked');

					redux_change($(this).closest('label').find('input[type="radio"]'));

					el.find('label[for="' + id + '"]').addClass('redux-icon-select-selected').find("input[type='radio']").attr("checked", true);
				});
			}
		);
	};
})(jQuery);