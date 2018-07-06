/* global $, redux_change */
(function ($) {

	"use strict";

	$.redux = $.redux || {};

	$(document).ready(function () {
		$('textarea.redux_codemirror').not(".redux-groups-dummy-group textarea.redux_codemirror").each(function () {
			$.redux.field_codemirror(this);
		});
	});

	$.redux.field_codemirror = function (element) {

		//codemirror init
		var t = 0;
		var $this = jQuery(element);
		var codemirror = redux.codemirror[$this.attr('id')];

		if (codemirror.editor_options.hint && codemirror.editor_options.autohint) {
			codemirror.editor_options.onKeyEvent = function (editor, e) {

				if (e.type == "keyup") {
					// fix for arrow keys, shift, ctrl, alt
					if ([37, 38, 39, 40, 16, 17, 18, 91, 93, 224].indexOf(e.keyCode) > -1 || e.shiftKey || e.ctrlKey || e.altKey || e.metaKey) {
						return true;
					}

					clearTimeout(t);
					var cur = editor.getCursor();
					var cm = editor;
					cm.setCursor(cur);
					// If the key is a letter or period we want hints!
					if ((e.keyCode >= 65 && e.keyCode <= 90) || e.keyCode == 190) {
						if (!editor.state.completionActive) {
							CodeMirror.commands.autocomplete(editor, null, {completeSingle: false});
						}
						// If it is not a letter or period we don't want hints!
					} else if (document.querySelector('div.CodeMirror-completions')) {
						var complete = document.querySelector('div.CodeMirror-completions');
						complete.parentNode.removeChild(complete);
						editor.focus();
					}
				}
			};
		}

		codemirror.editor_options = updateMaskedValues(codemirror.editor_options);
		var editor = CodeMirror.fromTextArea($("#" + element.id).get(0), codemirror.editor_options);
		editor.on("change", function (cm) {
			var myTextArea = jQuery("#" + jQuery(cm.getTextArea()).attr("id"));
			redux_change(myTextArea);
		});

		// hook an event listener to fadeIn event to refresh the codemirror instance
		var _old = jQuery.fn.fadeIn;

		jQuery.fn.fadeIn = function () {
			return _old.apply(this, arguments).trigger("fadeIn");
		};

		jQuery(".redux-groups-accordion-group > h3").live("click", function () {
			jQuery(this).parent().find('.CodeMirror').each(function (i, el) {
				el.CodeMirror.refresh();
			});
		});

		jQuery("*[id$='_section_group']").on("fadeIn", function (cm) {
			jQuery(this).find('.CodeMirror').each(function (i, el) {
				el.CodeMirror.refresh();
			});
		});
	};

	var updateMaskedValues = function (obj) {
		var output = {};
		for (var i in obj) {
			if (typeof(obj[i]) === "object" && obj[i] !== null) {
				if (i != "gutters") {
					output[i] = updateMaskedValues(obj[i]);
				} else {
					output[i] = [obj[i]];
					for (var p in obj[i]) {
						if (obj[i][p].substring(0, 10) === "[function]") {
							output[i][p] = new Function("return (" + obj[i][p].replace("[function]", "") + ")")();
						} else if (obj[i][p].substring(0, 8) === "[regexp]") {
							output[i][p] = new RegExp(obj[i][p].replace("[regexp]", ""));
						} else if (obj[i][p].substring(0, 8) === "[object]") {
							output[i][p] = this[obj[i][p].replace("[object]", "")];
						} else {
							output[i][p] = obj[i][p];
						}
					}
				}
			} else if (typeof(obj[i]) === "string") {
				if (obj[i].substring(0, 10) === "[function]") {
					output[i] = new Function("return (" + obj[i].replace("[function]", "") + ")")();
				} else if (obj[i].substring(0, 8) === "[regexp]") {
					output[i] = new RegExp(obj[i].replace("[regexp]", ""), "g");
				} else if (obj[i].substring(0, 8) === "[object]") {
					output[i] = this[obj[i].replace("[object]", "")];
				} else {
					output[i] = obj[i];
				}
			} else {
				output[i] = obj[i];
			}
		}
		return output;
	};

})(jQuery);
