/**
 * Redux Metaboxes
 * Dependencies      : jquery
 * Created by        : Dovy Paukstys
 * Date              : 19 Feb. 2014
 */

/* global reduxMetaboxes, redux */

jQuery(function ($) {
	"use strict";

	$.reduxMetaBoxes = $.reduxMetaBoxes || {};

	$(document).ready(function () {
		$.reduxMetaBoxes.init();
	});

	$.reduxMetaBoxes.init = function () {
		$.reduxMetaBoxes.notLoaded = true;
		$.reduxMetaBoxes.checkBoxVisibility();

		$('#page_template').change(function () {
			$.reduxMetaBoxes.checkBoxVisibility('page_template');
		});

		$('input[name="post_format"]:radio').change(function () {
			$.reduxMetaBoxes.checkBoxVisibility('post_format');
		});

		$.redux.initFields();
	};

	$('#publishing-action .button, #save-action .button').click(function () {
		window.onbeforeunload = null;
	});

	var testValue;

	$.reduxMetaBoxes.checkBoxVisibility = function (fieldID) {
		if (reduxMetaboxes.length !== 0) {
			$.each(reduxMetaboxes, function (box, values) {
				$.each(values, function (field, v) {
					if (field === fieldID || !fieldID) {
						if (field === "post_format") {
							testValue = $("input:radio[name='post_format']:checked").val();
						} else {
							testValue = $('#' + field).val();
						}

						if (testValue) {
							var visible = false;

							$.each(v, function (key, val) {
								if (val === testValue) {
									visible = true;
								}
							});

							if (!visible && !$.reduxMetaBoxes.notLoaded) {
								$('#' + box).hide();
							} else if (!visible) {
								$('#' + box).hide();
							} else {
								$('#' + box).fadeIn('300');
								$.redux.initFields();
							}
						}
					}
				});
			});
		}
	};
});