/**
 * Redux Users
 * Dependencies      : jquery
 * Created by        : Dovy Paukstys
 * Date              : 19 Feb. 2016
 */

/* global reduxUsers, redux */

jQuery(
	function ($) {
		"use strict";

		$.reduxUsers = $.reduxUsers || {};

		$(document).ready(
			function () {
				$.reduxUsers.init();
			}
		);

		$.reduxUsers.init = function () {
			var reduxObject;
			var optName = $('.redux-container').data('opt-name');

			if (redux.args === undefined) {
				reduxObject = redux.optName;
			} else {
				reduxObject = redux;
			}

			$.reduxUsers.notLoaded = true;
			$.redux.initFields();

			reduxObject.args.ajax_save = 0;
			reduxObject.args.disable_save_warn = true;
		};

		// Check for successful element added since WP ajax doesn't have a callback.
		$.reduxUsers.editCount = $('#the-list tr');
		$.reduxUsers.editCheck = function () {
			if ($('#ajax-response .error').length) {
				return false;
			}
			if ($('#the-list tr').length > $.reduxUsers.editCount) {
				window.location.reload();
				return false;
			}
			setTimeout($.reduxUsers.editCheck, 100);
			$.reduxUsers.editCount = $('#the-list tr').length;
		};

		$('#submit').click(
			function () {
				window.onbeforeunload = null;
				$.reduxUsers.editCount = $('#the-list tr').length;
				$(document).ajaxSuccess(
					function () {
						$.reduxUsers.editCheck();
					}
				);
			}
		);
	}
);