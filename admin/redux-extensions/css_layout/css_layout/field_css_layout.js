/* global redux_change, redux */

/**
 * CSS Layout
 * Dependencies:        jquery, select2, wp-colorpicker
 * Feature added by:    Kevin Provance (kprovance)
 * Date:                06.13.2014
 */

(function ($) {
	"use strict";

	// Declarations
	redux.field_objects = redux.field_objects || {};
	redux.field_objects.css_layout = redux.field_objects.css_layout || {};

	// Initialize
	redux.field_objects.css_layout.init = function (selector) {
		if (!selector) {
			selector = $(document).find(".redux-group-tab:visible").find('.redux-container-css_layout:visible');
		}

		// Enum through all layout fields
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

				// Do module init
				redux.field_objects.css_layout.modInit(el);
			}
		);
	};

	/*
	 * Module initialize
	 */
	redux.field_objects.css_layout.modInit = function (el) {

		// dev_mode
		var dev_mode = Boolean(el.find('.redux-css-layout-container').data('dev-mode'));

		// Add tag to footer, dev mode only.
		if (dev_mode == true) {
			var ver = el.find('.redux-css-layout-container').data('version');
			var dev_html = $('div.redux-timer').html();

			if (dev_html !== undefined) {
				var pos = dev_html.indexOf('CSS Layout');

				if (pos === -1) {
					$('div.redux-timer').html(dev_html + '<br/>CSS Layout extension v.' + ver);
				}
			}
		}

		// Select2 defaults
		var default_params = {
			triggerChange: true,
			allowClear: false
		};

		// Get user specified select2 prefs
		var select2_handle = el.find('.redux-container-css_layout').find('.select2_params');

		// If select2 options exist
		if (select2_handle.size() > 0) {

			// Get the value
			var select2_params = select2_handle.val();

			// Parse the JSON
			select2_params = JSON.parse(select2_params);

			// Push to default_param array
			default_params = $.extend({}, default_params, select2_params);
		}

		// Apply select2
		el.find(".redux-style-css-layout-border").select2(default_params);

		// Color picker callback
		var mainID = el.attr('data-id');
		el.find('#' + mainID + ' .redux-color-css-layout-border').wpColorPicker({
			change: function (event, ui) {

				// Notify compiler
				redux_change($(this));

				// Get selected colour
				var color = ui.color.toString();

				// Apply color to border section
				el.find('#' + mainID).find('.redux-css-layout-border').css({'border-color': color});
			}
		});

		// Style select change action
		el.find('.redux-style-css-layout-border').on('change', function (e) {

			// Notify compiler
			redux_change(el.find('.redux-css-layout-container'));

			// Get selected value
			var style = $(this).val();

			// Apply style to border section
			var mainID = el.attr('data-id');
			el.find('#' + mainID).find('.redux-css-layout-border').css({'border-style': style});
		});

		// Input fields change action
		el.find('.css-layout-input').on('change', function (e) {

			// Get the value
			var value = $(this).val();

			// Get the unit value from the input, if any
			var localUnit = redux.field_objects.css_layout.getUnit(value, el);

			// Apply either found local unit, or apply default unit value
			var unit = (localUnit !== '') ? localUnit : redux.field_objects.css_layout.defUnitVal($(this), el);

			// Strip unit value off of numerical value
			value = value.replace(/[^\d.-]/g, '');

			// If value exists and value is a number...
			if (value !== '' && $.isNumeric(value)) {

				// Notify compiler
				redux_change(el.find('.redux-css-layout-container'));

				// Empty input value
				$(this).val('');

				// Replace with value and proper unit
				$(this).val(value + unit);
			} else {

				// Otherwise, empty the value, because it's junk.
				$(this).val('');
			}

			redux.field_objects.css_layout.updateShorthand($(this), el);

			//  If border radius input has changed
			if ($(this).hasClass('redux-css-layout-input-radius')) {

				// Get radius value
				var radius = $(this).val();

				// Apply radius style to border section
				var mainID = el.attr('data-id');
				el.find('#' + mainID).find('.redux-css-layout-border').css({'border-radius': radius});
			}
		});
	};

	redux.field_objects.css_layout.updateShorthand = function (selector, el) {

		var levelArr = ['margin', 'border', 'padding'];
		var posArr = ['top', 'right', 'bottom', 'left'];
		var finalArr = [];
		var mainID = el.attr('data-id');

		$.each(levelArr, function (idx, val) {
			if (selector.hasClass('redux-css-' + val)) {
				$.each(posArr, function (index, value) {
					finalArr[index] = el.find('#' + mainID).find('.redux-css-' + val + '-' + value).val();
					finalArr[index] = (finalArr[index] === '') ? '0' : finalArr[index];
				});

				var shorthand = finalArr[0] + ' ' + finalArr[1] + ' ' + finalArr[2] + ' ' + finalArr[3];
				el.find('#' + mainID).find('.redux-css-' + val + '-shorthand').val(shorthand);
			}
		});
	};

	redux.field_objects.css_layout.getUnit = function (str, el) {
		var res = '';

		// Make string lowercase
		var s = str.toLowerCase();

		// Get permitted units array
		var unitArr = $('#' + el.attr('data-id')).data('units');

		// Decode URI
		unitArr = decodeURIComponent(unitArr);

		// Parse JSON
		unitArr = $.parseJSON(unitArr);

		// Loop through units array
		$.each(unitArr, function (index, value) {

			// Check for existance of unit value
			var len = s.indexOf(value);

			// Found?  Assign value and return false (end the loop)
			if (len !== -1) {
				res = value;
				return false;
			}
		});

		// Return found unit value
		return res;
	};


	/*
	 *  Gets default values
	 */
	redux.field_objects.css_layout.defUnitVal = function (selector, el) {

		// Set mainID
		var mainID = el.attr('data-id');
		var unitVal;

		// If margin section exists, get default value
		if (el.find(selector).hasClass('redux-css-margin')) {
			unitVal = el.find('#' + mainID).data('margin-unit');
		}

		// If border section exists, get default value
		if (el.find(selector).hasClass('redux-css-border')) {
			unitVal = el.find('#' + mainID).data('border-unit');
		}

		// If padding section exists, get default value
		if (el.find(selector).hasClass('redux-css-padding')) {
			unitVal = el.find('#' + mainID).data('padding-unit');
		}

		// If radius section exists, get default value
		if (el.find(selector).hasClass('redux-css-layout-input-radius')) {
			unitVal = el.find('#' + mainID).data('radius-unit');
		}

		// Let is ride!
		return unitVal;
	};
})(jQuery);