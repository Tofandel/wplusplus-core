(function ($) {
	"use strict";

	$(document).ready(
		function () {
			var arr = color_scheme.data;
			var opt_name = color_scheme.opt_name;

			$.each(arr, function (idx, val) {
				if (val['title'] !== undefined) {
					var title = val['title'];
					var selector = val['selector'];
					var mode = val['mode'];
					var id = val['id'];

					wp.customize(opt_name + '[' + id + ']', function (value) {
						value.bind(function (to) {
							$(selector).css(mode, to ? to : '');
						});
					});
				}
			});


		}
	);
})(jQuery);