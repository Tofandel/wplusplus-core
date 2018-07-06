/*global jQuery*/

(function ($) {
	"use strict";

	redux.field_objects = redux.field_objects || {};
	redux.field_objects.datetime = redux.field_objects.datetime || {};

	redux.field_objects.datetime.init = function (selector) {
		if (!selector) {
			selector = $(document).find(".redux-group-tab:visible").find('.redux-container-datetime:visible');
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

				el.find('.redux-date-picker').each(function () {
					var mainID = $(this).parents('.redux-datetime-container:first').attr('id');

					// dev_mode
					var dev_mode = $('#' + mainID).data('dev-mode');

					// Add tag to footer, dev mode only.
					if (dev_mode == true) {
						var ver = $('#' + mainID).data('version');
						var dev_html = $('div.redux-timer').html();

						if (dev_html !== undefined) {
							var pos = dev_html.indexOf('Date/Time');

							if (pos === -1) {
								$('div.redux-timer').html(dev_html + '<br/>Date/Time Picker extension v.' + ver);
							}
						}
					}

					var dateFormat = $('#' + mainID).data('date-format');
					dateFormat = String((dateFormat === '') ? 'mm-dd-yy' : dateFormat);

					var timeFormat = $('#' + mainID).data('time-format');
					timeFormat = String((timeFormat === '') ? 'h:mm TT' : timeFormat);

					var separator = $('#' + mainID).data('separator');
					separator = String((separator === '') ? ' ' : separator);

					var rtl = $('#' + mainID).data('rtl');
					rtl = Boolean((rtl === '') ? false : rtl);

					var numOfMonths = $('#' + mainID).data('num-of-months');

					var hourMin = $('#' + mainID).data('hour-min');
					var hourMax = $('#' + mainID).data('hour-max');
					var minuteMin = $('#' + mainID).data('minute-min');
					var minuteMax = $('#' + mainID).data('minute-max');

					var controlType = $('#' + mainID).data('control-type');
					controlType = String((controlType === '') ? 'slider' : controlType);

					var datePicker = $('#' + mainID).data('date-picker');
					datePicker = Boolean((datePicker === '') ? false : datePicker);

					var timePicker = $('#' + mainID).data('time-picker');
					timePicker = Boolean((timePicker === '') ? false : timePicker);

					var timeOnly = false;
					if (datePicker === false) {
						timeOnly = true;
					}

					var timezoneList = $('#' + mainID).data('timezone-list');
					timezoneList = decodeURIComponent(timezoneList);
					timezoneList = JSON.parse(timezoneList);

					var dateMin = $('#' + mainID).data('date-min');
					dateMin = decodeURIComponent(dateMin);
					dateMin = JSON.parse(dateMin);

					var minDate;
					if (dateMin === -1) {
						minDate = null;
					} else if (typeof dateMin === 'object') {
						minDate = new Date(dateMin.year, dateMin.month, dateMin.day);
					} else {
						minDate = dateMin;
					}

					var dateMax = $('#' + mainID).data('date-max');
					dateMax = decodeURIComponent(dateMax);
					dateMax = JSON.parse(dateMax);

					var maxDate;
					if (dateMax === -1) {
						maxDate = null;
					} else if (typeof dateMax === 'object') {
						maxDate = new Date(dateMax.year, dateMax.month, dateMax.day);
					} else {
						maxDate = dateMax;
					}

					var timezone = $('#' + mainID).data('timezone');

					var split = $('#' + mainID).data('mode');

					split = Boolean((split === '') ? false : split);

					var altField = '';
					if (split === true) {
						var timePickerID = el.find('input.redux-time-picker').data('id');
						console.log('#' + timePickerID + '-time');
						altField = '#' + timePickerID + '-time'; // '.redux-time-picker';
					}

					$(this).datetimepicker({
						beforeShow: function (input, instance) {
							var el = $('#ui-datepicker-div');
							//$.datepicker._pos = $.datepicker._findPos(input); //this is the default position
							var popover = instance.dpDiv;
							$('.redux-container:first').append(el);
							$('#ui-datepicker-div').hide();
							setTimeout(function () {
								popover.position({
									my: 'left top',
									at: 'left bottom',
									collision: 'none',
									of: input
								});
							}, 1);
						},
						altField: altField,
						dateFormat: dateFormat,
						timeFormat: timeFormat,
						separator: separator,
						showTimepicker: timePicker,
						timeOnly: timeOnly,
						controlType: controlType,
						isRTL: rtl,
						timezoneList: timezoneList,
						timezone: timezone,
						hourMin: hourMin,
						hourMax: hourMax,
						minuteMin: minuteMin,
						minuteMax: minuteMax,
						minDate: minDate,
						maxDate: maxDate,
						numberOfMonths: numOfMonths
					});
				});
			}
		);
	};
})(jQuery);