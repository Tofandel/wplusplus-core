/*global jQuery*/

(function ($) {
	"use strict";
	$(document).ready(function () {
		$('.support-faq-accordion').accordion({
			heightStyle: "content",
			icons: {"header": "ui-icon-plus", "activeHeader": "ui-icon-minus"}
		});
	})
})(jQuery);