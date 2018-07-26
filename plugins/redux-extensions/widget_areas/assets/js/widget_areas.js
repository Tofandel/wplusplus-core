(function ($) {
	"use strict";

	var ReduxWidget_Areas = function () {
		this.widget_wrap = $('.sidebars-column-1');
		this.widget_area = $('#widgets-right');
		this.parent_area = $('.widget-liquid-right');
		this.widget_template = $('#redux-add-widget-template');
		this.add_form_html();
		this.add_del_button();
		this.bind_events();
	};

	ReduxWidget_Areas.prototype = {

		add_form_html: function () {

			this.widget_wrap.append(this.widget_template.html());
			this.widget_name = this.widget_wrap.find('input[name="redux-add-widget-input"]');
			this.nonce = this.widget_wrap.find('input[name="redux-nonce"]').val();
		},

		add_del_button: function () {
			var i = 0;
			this.widget_area.find('.sidebar-redux-custom .widgets-sortables').each(function () {
				if (i >= reduxWidgetAreasLocalize.count) {
					$(this).append('<div class="redux-widget-area-edit"><a href="#" class="redux-widget-area-delete button-primary">' + reduxWidgetAreasLocalize.delete + '</a><a href="#" class="redux-widget-area-delete-cancel button-secondary">' + reduxWidgetAreasLocalize.cancel + '</a><a href="#" class="redux-widget-area-delete-confirm button-primary">' + reduxWidgetAreasLocalize.confirm + '</a></div>')
				}
				i++;
			});
		},

		bind_events: function () {
			this.parent_area.on('click', 'a.redux-widget-area-delete', function (event) {
				event.preventDefault();
				$(this).hide();
				$(this).next('a.redux-widget-area-delete-cancel').show().next('a.redux-widget-area-delete-confirm').show();
			});
			this.parent_area.on('click', 'a.redux-widget-area-delete-cancel', function (event) {
				event.preventDefault();
				$(this).hide();
				$(this).prev('a.redux-widget-area-delete').show();
				$(this).next('a.redux-widget-area-delete-confirm').hide();
			});
			this.parent_area.on('click', 'a.redux-widget-area-delete-confirm', $.proxy(this.delete_widget_area, this));
			//this.parent_area.on('click', '.addWidgetArea-button', $.proxy( this.add_widget_area, this));
			$("#addWidgetAreaForm").submit(function () {
				var spinner = $('#redux-add-widget').find('.spinner');

				spinner.css('display', 'inline-block');
				spinner.css('visibility', 'visible');

				$.proxy(this.add_widget_area, this)
			});
		},

		add_widget_area: function (e) {
			e.preventDefault();
			//      	console.log(e);
			//      	alert('yo'+$('#redux-add-widget-input').val());
			return false;
		},

		//delete the widget_area area with all widgets within, then re calculate the other widget_area ids and re save the order
		delete_widget_area: function (e) {
			var widget = $(e.currentTarget).parents('.widgets-holder-wrap:eq(0)'),
				title = widget.find('.sidebar-name h2'),
				spinner = title.find('.spinner'),
				widget_name = $.trim(title.text()),
				obj = this;
			widget.addClass('closed');
			spinner.css('display', 'inline-block');
			spinner.css('visibility', 'visible');
			$.ajax({
				type: "POST",
				url: window.ajaxurl,
				data: {
					action: 'redux_delete_widget_area',
					name: widget_name,
					_wpnonce: obj.nonce
				},

				success: function (response) {
					if (response.trim() == 'widget_area-deleted') {
						widget.slideUp(200).remove();
					}
				}
			});
		}
	};

	$(function () {
		new ReduxWidget_Areas();
	});

})(jQuery);  