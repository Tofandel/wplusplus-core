/**
 * This handles management of the serialized_repeater field.
 *
 * @package     Redux Framework
 * @subpackage  Serialized Repeater
 * @version     1.0.5
 * @author      Shannon Little <codingman@yahoo.com>
 * @license     GPL-2.0
 */

/* global redux_change, redux */

/*
 
 Repeater field tag structure
 
 fieldset.redux-container-serialized_repeater    Repeater field container
 div.redux-serialized-repeater-row-container   Container which holds all the repeatable rows
 div.redux-serialized-repeater-row           Repeatable rows (these are added/removed dynamically)
 h3 (if using accordion display type)      Accordion header
 fieldset.redux-field-container            Field container, also it's what the accordion opens/closes
 h4                                      Field title, if present
 span                                    Field description/subtitle, if present
 <sub-field which renders itself inside a fieldset tag>
 a.redux-serialized-repeater-delete      Button which deletes the current .redux-serialized-repeater-row
 a.redux-serialized-repeater-add                 Button which adds a new .redux-serialized-repeater-row
 
 
 
 Fields that work                        Bind_Title works?
 background                              Partially           Data needs formatting.  Field needs .trigger('change') added to work properly
 border                                  Partially           Data needs formatting.  Field needs .trigger('change') added to work properly
 button_set (single & multi)             Yes
 checkbox (single & multi)               Yes
 ckeditor                                No
 color                                   Yes
 color_gradient                          Partially           Data needs formatting.  Field needs .trigger('change') added to work properly
 date                                    Yes
 dimensions                              Yes
 formatted_text                          Yes
 gallery                                 No                  Data is just ID's, so not useful as a title anyways.  Field needs .trigger('change') added to work properly.
 image_select                            Yes                 With patched version.
 js_button                               N/A
 link_color                              No                  Works initially, but never updates afterwards.  Field needs .trigger('change') added to work properly.
 media                                   No                  Data is just URL and ID, not useful as title.  Field needs .trigger('change') added to work properly.
 multi_text                              Partially           Doesn't update if a row is added/deleted.
 option_slider                           Partially           Two handled version is broken when creating new rows
 palette                                 Yes
 password                                Yes
 radio                                   Yes
 raw                                     N/A                 Default selectors would apply if any fields were present.
 select (single & multi)                 Yes
 select_image                            Yes
 serialized_repeater                     N/A
 slider (single & two handles)           Yes
 spacing                                 Yes
 sortable                                Partially           Doesn't update if rows are dragged & dropped. Also pressing Spacebar doesn't show the checkbox as selected.
 spinner                                 Yes                 With patched version.
 switch                                  Yes                 With patched version.
 text                                    Yes
 textarea                                Yes
 typography                              Yes
 
 
 Fields that don't work:
 Editor - It saves the text, but all the formatting tools and tabs are broken.
 The mceInit is being passed 'adventure-top_repeater-{{index-0}}-editor' as the first one, and it's breaking
 tinyMCEPreInit = {
 baseURL: "http://adventure.dev/wp/wp-includes/js/tinymce",
 suffix: ".min",
 mceInit: {'adventure-top_repeater-{{index-0}}-editor':{theme:"modern",skin:"lightgray",language:"en",formats:{
 ace_editor - Doesn't work on new rows. Error: ace.edit can't find div #adventure-top_repeater-{{index-0}}-ace-editor-css-editor
 color_rgba - Won't work unless using the patched version.  The old version generated it's own name (unlike all the other fields) and won't work
 divide     - Breaks tag structure
 info       - Breaks tag structure
 sorter     - Javascript error
 
 
 
 TODO:
 See if it's possible to fix 'required'
 
 
 BUGS:
 Using 'required' breaks the entire panel
 Selected text in the ace_editor causes the row to drag & drop
 
 
 Should I disable displaying fields that break the tag structure? Or swap in compatible replacements.
 */

/*@cc_on
 // conditional IE < 9 only fix for IE lacking the 3rd argument in setTimeout()
 // https://developer.mozilla.org/en-US/docs/Web/API/WindowTimers/setTimeout
 @if (@_jscript_version <= 9)
 (function(f){
 window.setTimeout =f(window.setTimeout);
 window.setInterval =f(window.setInterval);
 })(function(f){return function(c,t){var a=[].slice.call(arguments,2);return f(function(){c.apply(this,a)},t)}});
 @end
 @*/

(function ($) {
	"use strict";

	redux.field_objects = redux.field_objects || {};

	var reduxObject;

	if (!redux.field_objects.serialized_repeater) {
		redux.field_objects.serialized_repeater = {
			animationDuration: 350, // Milliseconds, needs to match what's in the SCSS file
			dragAndDropInProgress: false,
			isIE9: /MSIE 9/i.test(navigator.userAgent)
		};
	}


	/**
	 * Initialize the serialized_repeater field.
	 *
	 * This is called by $.redux.initFields() in redux.js.
	 * It calls init() for each instance of the field it finds which has the class 'redux-field-init' and is visible.
	 *
	 * @since 1.0.0
	 */
	redux.field_objects.serialized_repeater.init = function (selector, initHidden) {
		// console.log('serialized_repeater.init', selector, initHidden);

		if (!initHidden) {
			initHidden = false;
		}

		if (!selector) {
			selector = $(document).find('.redux-group-tab:visible .redux-field-init.redux-container-serialized_repeater:visible');
		}

		// Initialize all serialized repeater fields
		selector.each(
			function (index) {
				// console.log("serialized_repeater.init EACH");
				// console.log(this);

				var $this = $(this);

				// I've never seen this happen before, but since it was in the original repeater field, I've left it in just in case
				if (!$this.hasClass('redux-field-container')) {
					// console.log("Init not first");

					$this = $this.parents('.redux-field-container:first');

					if (!$this) {
						// console.log('Error: Could not find .redux-field-container:first');
						return;
					}
				}

				var optName = $this.parents('.redux-container').data('opt-name');

				if (optName === undefined) {
					reduxObject = redux;
				} else {
					reduxObject = redux.optName;
				}

				// Only visible fields are initialized unless the title is bound to them
				// When this field is visible later it will be initialized then
				if (!initHidden && $this.is(":hidden")) {
					// console.log("BAIL hidden");
					return;
				}

				// The presence of this class means the field has not yet been initialized
				if ($this.hasClass('redux-field-init')) {
					// console.log('Removing redux-field-init from', $this);

					$this.removeClass('redux-field-init');

					// Removes the more advanced CSS styling that look bad or are broken in old IEs
					if (redux.field_objects.serialized_repeater.isIE9) {
						$this.addClass('redux-field-old-ie');
					}
				} else {
					// console.log("BAIL already initialized");
					return;
				}

				// console.log('updateInteractionHandlers', $this.find('> .redux-serialized-repeater-row-container'));

				redux.field_objects.serialized_repeater.updateInteractionHandlers($this.find('> .redux-serialized-repeater-row-container'));

				// console.log("Field data");
				// console.log(redux.serialized_repeater[rootId]);

				$this.on('click', 'a.redux-serialized-repeater-add', redux.field_objects.serialized_repeater.onAddClick);
				$this.on('click', 'a.redux-serialized-repeater-delete', redux.field_objects.serialized_repeater.onDeleteClick);
			}
		);
	};


	/**
	 * Called when a field value changes for a field that is bound to the title.
	 * Updates the pane's title with its value, with special cases for different field types.
	 * The event.target is the form field whose value changed.
	 *
	 * @since 1.0.0
	 *
	 * @param object event DOM Event object
	 */
	redux.field_objects.serialized_repeater.onBoundTitleValueChange = function (event) {
		function appendBindTitle(newTitle) {
			// console.log('appendBindTitle', newTitle);

			if (titleLength >= maxTitleLength - prefix.length - postfix.length) {
				// No need to keep adding strings if they're just gonna get clipped
				// Returning false exits the .each() loop
				return false;
			}

			title.push(newTitle);
			titleLength += newTitle.length + separator.length;

			return true;
		}

		// console.log('onBoundTitleValueChange');
		// console.log(this);
		// console.log(event);
		// console.log(event.target);

		var $field = $(event.target);
		var $fieldContainer = $field.closest('fieldset.redux-field-container');
		var $addButton = $fieldContainer.closest('.redux-container-serialized_repeater').find('.redux-serialized-repeater-add');
		var separator = $addButton.attr('data-bindtitleseparator');
		var prefix = $addButton.attr('data-bindtitleprefix');
		var postfix = $addButton.attr('data-bindtitlepostfix');
		var title = [];
		var titleLength = 0;
		var maxTitleLength = Number($addButton.attr('data-bindtitlelimit'));

		// console.log('Field type: ' + $fieldContainer.attr('data-type'));

		// Field specific title formatting
		switch ($fieldContainer.attr('data-type')) {
			case 'radio':
			case 'checkbox':
				// Combine all the field labels together
				$field.closest('ul').find('label input:checked').each(function () {
					return appendBindTitle($(this).parent().text());
				});

				break;

			case 'button_set':
				// Combine the label after the checked input
				$fieldContainer.find('input:checked + label > span').each(function () {
					return appendBindTitle($(this).text());
				});

				break;

			case 'image_select':
				// Radio buttons in disguise
				// Uses the alt attribute on the selected image to show in the title
				$field.closest('ul').find('label input:checked').each(function () {
					return appendBindTitle($(this).parent().find('img').attr('alt'));
				});

				break;

			case 'sortable':
				// Combine all the field labels together

				// Sortable can use either textboxes or check boxes
				// console.log('Sortable type: ' + $field.prop('type'));

				if ($field.prop('type') == 'text') {
					$field.closest('ul').find('input').each(function () {
						return appendBindTitle($(this).val());
					});
				} else {
					// Checkbox
					$field.closest('ul').find('input:checked').each(function () {
						return appendBindTitle($(this).parent().text());
					});
				}

				break;

			case 'password':
				// Normally the password field would be displayed along with the username
				if ($field.attr('type') == 'text') {
					title.push($field.val());
				} else {
					return;
				}

				break;

			case 'select':
			case 'select_image':
				$field.find('option:selected').each(function () {
					return appendBindTitle($(this).text());
				});

				break;

			case 'slider':
				// Slider can present data in two forms, select boxes or hidden fields
				var $selectedOptions = $fieldContainer.find('select option:selected');

				if ($selectedOptions.length) {
					$selectedOptions.each(function () {
						return appendBindTitle($(this).val());
					});
				} else {
					$fieldContainer.find('input:hidden').each(function () {
						return appendBindTitle($(this).val());
					});
				}

				break;

			case 'switch':
				$fieldContainer.find('label.selected span').each(function () {
					return appendBindTitle($(this).text());
				});

				break;

			case 'option_slider':
				// INFO: This doesn't handle images, only text
				$fieldContainer.find('.redux-option-slider-label').each(function () {
					return appendBindTitle($(this).text());
				});

				break;

			case 'palette':  // Radio buttons in disguise
				$fieldContainer.find('input:checked').each(function () {
					return appendBindTitle($(this).val());
				});

				break;

			case 'textarea':
				title.push($field.val());

				break;

			case 'dimensions':
			case 'spacing':
			case 'typography':
				$fieldContainer.find('input[type="hidden"],select:hidden > option:selected').each(function () {
					var $thisField = $(this);

					if (!(
							$thisField.hasClass('field-units') ||
							$thisField.hasClass('redux-font-clear') ||
							$thisField.hasClass('redux-typography-google') ||
							$thisField.hasClass('redux-typography-google-font')
						)) {
						if ($thisField.prop('tagName') == 'OPTION') {
							return appendBindTitle($(this).text());
						} else {
							return appendBindTitle($(this).val());
						}
					}
				});

				break;

			case 'color':
				if ($field.prop('checked')) {
					// Grab the Transparent label text since it may be translated
					title.push($field.parent().text());
				} else {
					// Show the hex color
					// TODO: Add an option to color the header bar or put a color chip
					title.push($fieldContainer.find('input.redux-color').val());
				}
				break;

			case 'ace_editor':
				// While bind_title works, the field itself doesn't when creating a new row
				title.push($field.closest('.ace-wrapper').find('> textarea.ace-editor').val());

				break;

			// bind_title is disabled for these fields
			case 'ckeditor':
				break;

			default:
				// Combine all the field values together
				$field.closest('fieldset').find('input[type!="hidden"],textarea').each(function () {
					return appendBindTitle($(this).val());
				});

				break;
		}

		if (title.constructor === Array) {
			// Remove empty items
			title = title.filter(function (e) {
				return e
			});
			title = title.join(separator);
		}

		if (title) {
			title = prefix + redux.field_objects.serialized_repeater.trim(title, maxTitleLength, $addButton.attr('data-bindtitlemore')) + postfix;

			$field.closest('.redux-serialized-repeater-row').find('> h3 .title').text(title);
		} else {
			$field.closest('.redux-serialized-repeater-row').find('> h3 .title').html('&nbsp;');
		}

		return false;
	};


	// redux.field_objects.serialized_repeater.addAllItemsToArray = function (selector, length, more) {

	// };


	/**
	 * If the string length is > length, trims the string to specified length (minus the length of more)
	 * and adds the more character to the end.
	 *
	 * @param  string string String to trim
	 * @param  int    length Length to trim string to
	 * @param  string more   String to put at the end of the string to indicate it was trigger
	 * @return string        [description]
	 */
	redux.field_objects.serialized_repeater.trim = function (string, length, more) {
		if (string.length > length - more.length) {
			return string.substr(0, length - more.length) + more;
		}

		return string;
	};


	/**
	 * Called whenever a row in a row-container has been added or deleted and when the field is initialized
	 * This updates the interaction handlers like Accordion or Sortable so they'll work with the new rows
	 * This is not recursive, it only updates the handlers for the one container
	 *
	 * @since 1.0.0
	 *
	 * @param object $rowContainer jQuery object of the row container
	 */
	redux.field_objects.serialized_repeater.updateInteractionHandlers = function ($rowContainer) {
		// console.log('updateInteractionHandlers()');
		// console.log($rowContainer);

		// console.log("Init accordion");
		// console.log("Found " + $rowContainer.find("> .redux-serialized-repeater-row:not(.ui-accordion)").length + " accordions to init");

		// The accordion Interaction will add the class .ui-accordion to the rows that it has initialized
		// Select all rows which don't have that class
		var $accordionRows = $rowContainer.find('> .redux-serialized-repeater-row:not(.ui-accordion)');

		if ($accordionRows.length) {
			var $addButton = $rowContainer.parent().find('> .redux-serialized-repeater-add');
			var style = $addButton.attr('data-accordionstyle');
			var state = $addButton.attr('data-accordionstate');
			var targetFieldActive = false;

			redux.field_objects.serialized_repeater.forceAccordionPaneClosing = false;

			// console.log('type: ' + $addButton.attr('data-displaytype'));
			// console.log('style: ' + style);
			// console.log('state: ' + state);
			// console.log('$accordionRows', $accordionRows);

			$accordionRows.each(function () {
				// console.log($addButton);

				// False = pane is closed, int = index of open pane
				var active = false;

				switch (state) {
					case 'all':
						active = 0;
						break;

					case 'first':
						if (!targetFieldActive) {
							targetFieldActive = true;
							active = 0;
						}

						break;

					case 'closed':
						break;
				}

				// console.log(targetFieldActive);
				$(this).accordion({
					header: '> h3',
					collapsible: true, // This is handled by this script
					active: active,
					animate: 200,
					heightStyle: "content",
					activate: function (event, ui) {
						// console.log('activate');

						// Init fields that may have been hidden earlier by collapsed accordion panels
						$.redux.initFields();
					},
					// Triggered by the user clicking on the header and when programmatically set as active
					// Returning false prevents the pane from expanding
					beforeActivate: function (event, ui) {
						// console.log('beforeActivate');
						// console.log('Force close: ' + redux.field_objects.serialized_repeater.forceAccordionPaneClosing);

						// Prevent the panel from opening/closing if it was being dragged & dropped
						if (redux.field_objects.serialized_repeater.dragAndDropInProgress) {
							// console.log('Canceling');
							return false;
						}

						// Flag that overrides the logic normally on the panes
						if (redux.field_objects.serialized_repeater.forceAccordionPaneClosing) {
							// console.log('Force closed by script');
							return true;
						}

						var $row = $(this);
						var isActive = $row.accordion('option', 'active') !== false;
						var $addButton = $row.closest('.redux-container-serialized_repeater').find('> .redux-serialized-repeater-add');
						var style = $addButton.attr('data-accordionstyle');
						var state = $addButton.attr('data-accordionstate');

						// console.log($row);
						// console.log(style);
						// console.log(isActive);

						// Check which display mode is being used and close other accordions in this row-container as needed
						if (style === 'single' && isActive) {
							// The active pane in a 'single' accordion can't be closed
							// console.log('The active pane in a \'single\' accordion can\'t be closed');
							return false;
						}

						if (style === 'single' || style === 'collapsible') {
							// console.log('Closing panes');
							redux.field_objects.serialized_repeater.closeAllAccordionPanes($row.parent(), $row);
						}
					} // beforeActivate
				}).removeClass('ui-widget');  // Remove this class which jQuery UI adds which (stupidly) overrides the current styles
			});
		}


		// console.log("Init sortable");

		if ($rowContainer.hasClass('ui-sortable')) {
			// Refresh allows sortable to recognize new rows
			// console.log("Refreshing sortable");
			$rowContainer.sortable('refresh');
		} else {
			// console.log("Setting up sorting");

			// INFO: This was initially tried, but sortable doesn't work correctly with only some rows as sortable
			//       Would need to recursively go over every repeater and apply sortable to each as needed

			// Check if this repeater has sorting enabled
			// var $addButton = $rowContainer.parent().find('> .redux-serialized-repeater-add');
			// var sortable   = $addButton.attr('data-sortable') === 'true' ? true : false;

			// console.log($rowContainer);
			// console.log($addButton);
			// console.log(sortable);

			// if (sortable) {
			// console.log("Init sorting");

			$rowContainer.sortable({
				start: redux.field_objects.serialized_repeater.onSortableStart,
				update: redux.field_objects.serialized_repeater.onSortableUpdate,
				stop: redux.field_objects.serialized_repeater.onSortableStop,
				placeholder: 'sortable-placeholder',
				handle: '> .sort-handle, > h3 > .sort-handle',
				revert: 120,
				items: "> .redux-serialized-repeater-row",
				tolerance: "pointer",
				distance: 3,
				delay: 100,
				scroll: false,
				// cursorAt    : { left: 10, top: 50 },
				// containment : $rowContainer,
				// axis        : 'y',
			});
			// } else {
			// console.log("Sorting is disabled");
			// }
		}

		// console.log('Init bind title');

		// Add listeners for form value changes to the fieldset of the field whose value should update the accordion title
		$rowContainer.find('> .redux-serialized-repeater-row.bind-title').each(function () {
			$(this).find('> fieldset > fieldset').each(function () {
				var $this = $(this);

				// Remove the redux-field-init class from fields which can't or don't need to (like the text field)
				// If it's present, updateBindTitleHandlers() will constantly poll it waiting for it to be ready to bind
				// to bind the title to (the redux-field-init class will be gone when it is ready)
				// console.log('Field type:', $this.attr('data-type'));
				// console.log($this);

				if (['text', 'textarea', 'password', 'radio'].indexOf($this.attr('data-type')) !== -1) {
					$this.removeClass('redux-field-init');
				}

				if ($this.hasClass('bind-title')) {
					// console.log('field.bind-title');
					// console.log($(this));
					redux.field_objects.serialized_repeater.updateBindTitleHandlers($(this));
				}
			});
		});


	};


	/**
	 * Updates the bind title handlers so they'll work with new row and to initially set
	 * the title when the page is loaded.
	 * Normally fields aren't initialized if they aren't visible.  Since bind title often requires fields to
	 * be initialize before it can pull data from them, this will initialize hidden field that bind title requires.
	 *
	 * @since 1.0.0
	 *
	 * @param object $fieldContainer jQuery object of the fieldset of the field to update the bind title handlers on
	 */
	redux.field_objects.serialized_repeater.updateBindTitleHandlers = function ($fieldContainer) {
		// console.log("updateBindTitleHandlers");
		// console.log($fieldContainer);

		if ($fieldContainer.hasClass('redux-field-init')) {
			// Field isn't ready, set a timer to check back later
			// If we tried to update the title now, its hidden form fields (which are usually filled by jQuery), would be blank
			// console.log("Field not ready");

			if (!$fieldContainer.attr('data-updateinteractioninterval')) {
				// If the field is hidden, initialize it
				if ($fieldContainer.is(':hidden')) {
					// console.log("Field is not visible");
					// console.log($fieldContainer);

					redux.field_objects.serialized_repeater.init($fieldContainer, true);
				}

				// console.log("Setting up interval");

				$fieldContainer.attr('data-updateinteractioninterval', setInterval(function () {
					redux.field_objects.serialized_repeater.updateBindTitleHandlers($fieldContainer);
				}, 1000));
			}
		} else {
			// console.log("Field ready");
			var updateInterval = $fieldContainer.attr('data-updateinteractioninterval');

			if (updateInterval) {
				// console.log("Clearing int " + updateInterval);
				clearInterval(updateInterval);
				$fieldContainer.removeAttr('data-updateinteractioninterval');
			}

			// Check if the event handler is already bound
			var hasHandler = false;
			var eventHandlers = $._data($fieldContainer.get(0), "events");

			if (eventHandlers) {
				for (var handler in eventHandlers) {
					if ((handler === 'change' || handler === 'keyup') && eventHandlers.hasOwnProperty(handler)) {
						// There is a handler bound, check if it's ours
						if (eventHandlers[handler][0] && eventHandlers[handler][0].handler === redux.field_objects.serialized_repeater.onBoundTitleValueChange) {
							hasHandler = true;
							break;
						}
					}
				}
			}

			if (!hasHandler) {
				// console.log('Adding bind title handler');

				// TODO: Look into throttling or delaying the actual updating of the title until the events stop rapid firing
				$fieldContainer.on('change keyup', redux.field_objects.serialized_repeater.onBoundTitleValueChange);
				// The trigger causes the new field's bound title to be updated, it's not initially set in the template
				$fieldContainer.find('input:first,textarea:first,select:first').trigger('change');

				// TODO: Special extra hooks for fields that manipulate the DOM, like sortable, multi_text
				//       or modify those fields so they fire a change event on the new field
				// } else {
				// console.log('Bind title handler already present');
			}
		}
	};


	/**
	 * Opens the row's accordion pane.
	 * Will close the row's other panes if the accordion style is 'single' or 'collapsible', which only allow 1 open pane at a time.
	 *
	 * @since 1.0.0
	 *
	 * @param object $row jQuery object of the row to open.
	 */
	redux.field_objects.serialized_repeater.openAccordionPane = function ($row) {
		// console.log('openAccordionPane()');

		var isActive = $row.accordion('option', 'active') !== false;
		var $addButton = $row.closest('.redux-container-serialized_repeater').find('> .redux-serialized-repeater-add');
		var style = $addButton.attr('data-accordionstyle');
		var state = $addButton.attr('data-accordionstate');

		// console.log($row);
		// console.log(style);
		// console.log(isActive);

		if (!isActive) {
			// console.log('Opening closed pane');
			$row.accordion('option', 'active', 0);
		}

		if (style === 'single' || style === 'collapsible') {
			// console.log('Closing panes');
			redux.field_objects.serialized_repeater.closeAllAccordionPanes($row.parent(), $row);
		}
	};


	/**
	 * Closes the row's accordion pane.
	 * The pane won't close if its style is 'single' unless forceClose is true.
	 *
	 * @since 1.0.0
	 *
	 * @param object  $rowContainer  jQuery object of the row container of the panes to close.
	 * @param boolean force          Whether or not to force close the pane, regardless of the accordion's style.
	 */
	redux.field_objects.serialized_repeater.closeAccordionPane = function ($row, forceClose) {
		// console.log('closeAccordionPane(' + force + ')');

		// Setting the force flag here will cause other accordions
		redux.field_objects.serialized_repeater.forceAccordionPaneClosing = forceClose;

		$row.accordion('option', 'active', false);

		redux.field_objects.serialized_repeater.forceAccordionPaneClosing = false;
	};


	/**
	 * Closes all the panes in the row-container.
	 * Option to close all but a certain page.
	 *
	 * @since 1.0.0
	 *
	 * @param object $rowContainer  jQuery object of the row container of the panes to close.
	 * @param object $rowToKeepOpen jQuery object of the row containing the pane to keep open.
	 */
	redux.field_objects.serialized_repeater.closeAllAccordionPanes = function ($rowContainer, $rowToKeepOpen) {
		// console.log('closeAllAccordionPanes()');
		// console.log('Found ' + $rowContainer.find('> .redux-serialized-repeater-row').length + ' panes to check to close');
		// console.log($rowContainer.find('> .redux-serialized-repeater-row'));

		// Close the other panes
		$rowContainer.find('> .redux-serialized-repeater-row').each(function () {
			// console.log('Checking each row');
			// console.log($(this));

			var $this = $(this);

			if (!$this.is($rowToKeepOpen)) {
				// console.log('Closing pane');
				redux.field_objects.serialized_repeater.closeAccordionPane($this, true);
			} else {
				// console.log('Skipping current pane');
			}
		});
	};


	/**
	 * Fired at the start of a sort.
	 *
	 * @since 1.0.0
	 *
	 * @param object event DOM Event object
	 * @param object ui    Sortable UI object
	 */
	redux.field_objects.serialized_repeater.onSortableStart = function (event, ui) {
		// console.log("Sort start");
		// console.log(event);
		// console.log(ui.item);

		// This flag is set so the accordion header won't open/close at the end of the sort
		redux.field_objects.serialized_repeater.dragAndDropInProgress = true;

		ui.item.parent().addClass('sorting').css('padding-bottom', ui.item.height() + 'px');
		ui.placeholder.append('<div class="content"></div>');
	};


	/**
	 * Fired when the user has stopped sorting and the DOM position has changed.
	 *
	 * @since 1.0.0
	 *
	 * @param object event DOM Event object
	 * @param object ui    Sortable UI object
	 */
	redux.field_objects.serialized_repeater.onSortableUpdate = function (event, ui) {
		// console.log("Sort update");

		// All the fields' name attributes need to be renumbered after every sort or their new positions won't be saved
		var $addButton = ui.item.closest('.redux-container-serialized_repeater').find('> .redux-serialized-repeater-add');
		var rootId = $addButton.attr('data-rootid');
		var settingsId = $addButton.attr('data-settingsid');
		var $rootRepeater = $('#' + settingsId + '-' + rootId);

		redux.field_objects.serialized_repeater.renumberRows($rootRepeater.find('> .redux-serialized-repeater-row-container'));
	};


	/**
	 * Fired when sorting has stopped, after onSortableUpdate.
	 *
	 * @since 1.0.0
	 *
	 * @param object event DOM Event object
	 * @param object ui    Sortable UI object
	 */
	redux.field_objects.serialized_repeater.onSortableStop = function (event, ui) {
		// console.log("Sort stop");

		redux.field_objects.serialized_repeater.dragAndDropInProgress = false;

		var $rowContainer = ui.item.parent();

		// Adding then removing the sort-stop class later prevents a jump in the CSS transition
		$rowContainer.removeClass('sorting size-small size-medium size-large').addClass('sort-stop').css('padding-bottom', 0);

		setTimeout(function ($rowContainer) {
			// console.log("Timeout");
			$rowContainer.removeClass('sort-stop');
		}, 310, $rowContainer);
	};


	/**
	 * Removes brackets that contain numbers from the string.
	 * optname[repeater1][0][repeater2][1][subfield] becomes optname[repeater1][repeater2][subfield]
	 *
	 * @since 1.0.0
	 *
	 * @param  string string The string to operate on
	 * @return string        The string with brackets that contain numbers removed
	 */
	redux.field_objects.serialized_repeater.removeNumberedBrackets = function (string) {
		return string.replace(/\[(\d+)\]/g, '');
	};


	/**
	 * Returns the matching row for the field name.
	 * Recursively searches sub-repeaters.
	 *
	 * @param  object $fieldContainer jQuery object of the field container to search
	 * @param  string fieldName       The field name to search for
	 * @return object|boolean         If a matching row was found, the jQuery object of it is returned, otherwise false
	 */
	redux.field_objects.serialized_repeater.getRowToInsert = function ($fieldContainer, fieldName) {
		// console.log('getRowToInsert()');
		// console.log($fieldContainer);

		// Check if the current fieldset contains the target row to duplicate
		var currentFieldName = $fieldContainer.find('> .redux-serialized-repeater-row-container').attr('data-fieldname');

		// The names can't be directly compared, except for the very first level, because of the changing index
		// First level:  optname[top_repeater] == optname[top_repeater]
		// Other levels: optname[top_repeater][99999][sub_repeater] == optname[top_repeater][0][sub_repeater]
		if (fieldName != currentFieldName) {
			// Remove all the brackets that just contain numbers, then the strings can be compared
			currentFieldName = redux.field_objects.serialized_repeater.removeNumberedBrackets(currentFieldName);
		}

		// console.log(currentFieldName + " == " + fieldName);

		if (fieldName == currentFieldName) {
			// console.log("This is the correct field");

			// Found the correct row
			// Remove any existing rows within any sub-repeaters
			// They are just placeholder/template data for the sub-repeaters
			var $rows = $fieldContainer.find('> .redux-serialized-repeater-row-container  > .redux-serialized-repeater-row');

			$rows.find('> fieldset > fieldset[data-type="serialized_repeater"] > .redux-serialized-repeater-row-container > .redux-serialized-repeater-row').remove();

			return $rows;
		}

		// console.log("Checking subrows");
		// console.log($fieldContainer);
		// console.log($fieldContainer.find('> div > div > fieldset > fieldset[data-type="serialized_repeater"]'));

		// This isn't the repeater field we're looking for
		// Loop through this repeaters sub-repeaters looking for a match
		var subRepeaters = $fieldContainer.find('> .redux-serialized-repeater-row-container > .redux-serialized-repeater-row > fieldset > fieldset[data-type="serialized_repeater"]');

		for (var i = 0, count = subRepeaters.length; i < count; i++) {
			// {{field-XX}} template tags have not been replaced
			// console.log("ROW");
			// console.log(this);
			// console.log($(this));
			// console.log('calling');
			var results = redux.field_objects.serialized_repeater.getRowToInsert($(subRepeaters[i]), fieldName);

			if (results !== false) {
				return results;
			}
		}

		// console.log("Matching fieldName not found");

		return false;
	};


	/**
	 * Fired when the Add button has been clicked.
	 * Adds a new row to the current row container.
	 *
	 * @since 1.0.0
	 *
	 * @param object event DOM Event object
	 */
	redux.field_objects.serialized_repeater.onAddClick = function (event) {
		// console.log("onAddClick()");

		event.stopImmediatePropagation();

		var $this = $(this);

		var rootId = $this.attr('data-rootid');
		var settingsId = $this.attr('data-settingsid');
		var fieldName = $this.attr('data-fieldname');
		var limit = Number($this.attr('data-limit'));
		var level = Number($this.attr('data-level'));
		var count = Number($this.attr('data-count'));    // Holds the current # of rows (this # goes up and down)
		var counter = Number($this.attr('data-counter'));  // Holds the # of rows ever created (this # only goes up)
		var displayType = $this.attr('data-displaytype');

		// console.log($this);
		// console.log("RootID:     " + rootId);
		// console.log("SettingsId: " + settingsId);
		// console.log("FieldName:  " + fieldName);
		// console.log("Limit:      " + limit);
		// console.log("Level:      " + level);
		// console.log("Count:      " + count);
		// console.log("Counter:    " + counter);

		if (limit > 0 && count >= limit) {
			// Max number of rows already present
			// console.log("BAIL Button is disabled");
			return false;
		}

		// This shouldn't happen unless localize() failed
		if (!reduxObject.serialized_repeater[rootId]) {
			// console.log("Error: Serialized Repeater root ID not found: " + rootId);
			return false;
		}

		var fieldSettings = reduxObject.serialized_repeater[rootId];

		// Flag as to whether data-new="true" has been added to all the rows in the template HTML (only needs to be done once)
		if (!fieldSettings.htmlDataNewReplacementDone) {
			// console.log('htmlDataNewReplacement');

			fieldSettings.htmlDataNewReplacementDone = true;
			fieldSettings.html = $($.parseHTML(fieldSettings.html)[0]);
			fieldSettings.html.find('.redux-serialized-repeater-row').attr('data-new', 'true');
		}

		// var html          = fieldSettings.html;
		var $rootRepeater = $('#' + settingsId + '-' + rootId);

		// console.log('html');
		// console.log(html);

		// var $newRow = $($.parseHTML(html)[0]);
		var $newRow = fieldSettings.html.clone();

		// console.log('$newRow');
		// console.log($newRow);

		// Picks the correct row from the template to insert into the form
		$newRow = redux.field_objects.serialized_repeater.getRowToInsert($newRow, redux.field_objects.serialized_repeater.removeNumberedBrackets(fieldName));

		// console.log('$newRow');
		// console.log($newRow);

		// Append after the other rows
		var $rowContainer = $this.parent().find('> .redux-serialized-repeater-row-container');

		$rowContainer.append($newRow);

		// Renumber fields so they are in order and update the counters
		// The renumbering has to start from the root repeater no matter at which level the new row was added
		redux.field_objects.serialized_repeater.renumberRows($rootRepeater.find('> .redux-serialized-repeater-row-container'));
		redux.field_objects.serialized_repeater.updateInteractionHandlers($rowContainer);

		if (displayType == 'accordion') {
			// console.log('Setting new pane as active');
			// console.log($newRow);

			redux.field_objects.serialized_repeater.openAccordionPane($newRow);
		}

		// Animate the new row appearing

		// The CSS property perspective causes an unknown top offset when dragging & dropping
		// so this class is only applied while animating
		$rowContainer.addClass('animating');
		$newRow.addClass('initially-created');

		setTimeout(function () {
			$newRow.addClass('making-visible');
		}, 1);

		setTimeout(function () {
			$newRow.removeClass('initially-created making-visible');

			// console.log($rowContainer);
			// console.log($rowContainer.find('> .redux-serialized-repeater-row.initially-created, > .redux-serialized-repeater-row.initially-deleted'));

			// Check if any other rows are animating
			if ($rowContainer.find('> .redux-serialized-repeater-row.initially-created, > .redux-serialized-repeater-row.initially-deleted').length == 0) {
				$rowContainer.removeClass('animating');
			}
		}, redux.field_objects.serialized_repeater.animationDuration + 10);

		// Initialize the newly created fields
		$.redux.initFields();

		// Disable the Add button if the number of rows is at or above the limit
		if (limit > 0 && count >= limit - 1) {
			// console.log("Add button disabled");

			if ($this.hasClass('button')) {
				$this.addClass('button-disabled');
			}

			$this.attr('data-disabled', 'true');
		}

		return false;
	};


	/**
	 * Fired when the Delete button has been clicked.
	 *
	 * @since 1.0.0
	 *
	 * @param object event DOM Event object
	 */
	redux.field_objects.serialized_repeater.onDeleteClick = function (event) {
		// console.log("onDeleteClick()");

		event.stopImmediatePropagation();

		redux_change($(this));

		var $this = $(this);    // Referrers to the Delete button that was clicked

		if ($this.attr('data-disabled') == 'true') {
			// console.log("BAIL Button is disabled");
			return false;
		}

		var $addButton = $this.closest('.redux-container-serialized_repeater').find('> .redux-serialized-repeater-add');
		var rootId = $addButton.attr('data-rootid');
		var settingsId = $addButton.attr('data-settingsid');
		var limit = Number($addButton.attr('data-limit'));
		var level = Number($addButton.attr('data-level'));
		var count = Number($addButton.attr('data-count'));    // Holds the current # of rows (this # goes up and down)
		var counter = Number($addButton.attr('data-counter'));  // Holds the # of rows ever created (this # only goes up)
		var displayType = $addButton.attr('data-displaytype');
		var $rootRepeater = $('#' + settingsId + '-' + rootId);
		var $row = $this.closest('.redux-serialized-repeater-row');
		var $rowContainer = $row.parent();

		// console.log($this);
		// console.log($addButton);
		// console.log("rootId:     " + rootId);
		// console.log("settingsId: " + settingsId);
		// console.log("Limit:      " + limit);
		// console.log("Level:      " + level);
		// console.log("Count:      " + count);
		// console.log("Counter:    " + counter);
		// console.log($rootRepeater);
		// console.log($rootRepeater.find('> .redux-serialized-repeater-row-container'));

		if ($this.hasClass('button')) {
			$this.addClass('button-disabled');
		}

		$this.attr('data-disabled', 'true');
		$addButton.attr('data-count', --count);

		// console.log("Count now: " + count);

		// Open the next accordion pane
		// The function is delayed so the animation looks better
		if (displayType == 'accordion') {
			if ($row.siblings().length) {
				var $nextRow = $row.next();

				if (!$nextRow.length) {
					$nextRow = $row.prev();
				}

				// console.log('Opening the next pane');
				// console.log($row);
				// console.log($nextRow);

				setTimeout(function () {
					redux.field_objects.serialized_repeater.openAccordionPane($nextRow);
				}, redux.field_objects.serialized_repeater.animationDuration / 4);
			}
		}

		// Animate the row disappearing
		$rowContainer.addClass('animating');
		$row.addClass('initially-deleting');
		$row.find('> fieldset').delay(100).slideUp(redux.field_objects.serialized_repeater.animationDuration);

		setTimeout(function () {
			$row.addClass('making-invisible');
		}, 1);

		setTimeout(function () {
			$row.remove();
			redux.field_objects.serialized_repeater.renumberRows($rootRepeater.find('> .redux-serialized-repeater-row-container'));

			if (limit > 0 && count < limit) {
				$addButton.removeClass('button-disabled');
			}

			// Check if any other rows are animating, if not, remove the animating class
			if ($rowContainer.find('> .redux-serialized-repeater-row.initially-created, > .redux-serialized-repeater-row.initially-deleting').length == 0) {
				$rowContainer.removeClass('animating');
			}
		}, redux.field_objects.serialized_repeater.animationDuration + 100);

		return false;
	};


	/*
	 optname[repeater1][0][name]
	 optname[repeater1][0][phone]
	 optname[repeater1][0][contact-rep][0][name]
	 optname[repeater1][0][contact-rep][0][phone]
	 optname[repeater1][0][contact-rep][1][name]
	 optname[repeater1][0][contact-rep][1][phone]
	 optname[repeater1][1][name]
	 optname[repeater1][1][phone]
	 optname[repeater1][1][contact-rep][0][name]
	 optname[repeater1][1][contact-rep][0][phone]
	 optname[repeater1][1][contact-rep][1][name]
	 optname[repeater1][1][contact-rep][1][phone]
	 optname[repeater1][1][contact-rep][2][name]
	 optname[repeater1][1][contact-rep][2][phone]
	 */

	/**
	 * Rewrites the names of each form element in each row so they are in order.
	 * Replaces any placeholder strings (in new rows) in the ID's so they are always unique.
	 * Otherwise there will be gaps in the array keys of the form data that can't be fixed without modifying the core
	 * to allow fields a chance to filter the data before it is saved.
	 * This only needs to handle the fields on the current row.  It will call itself again for any sub-repeater rows.
	 *
	 * @param object $rowContainer jQuery object of the row container for all the rows to be renumbered.
	 */
	redux.field_objects.serialized_repeater.renumberRows = function ($rowContainer) {
		// console.log('renumberRows()');

		var $addButton = $rowContainer.parent().find('> .redux-serialized-repeater-add');

		// console.log("rowContainer");
		// console.log($rowContainer);   // div.redux-serialized-repeater-row-container
		// console.log("Referring to button");
		// console.log($addButton);

		var rootId = $addButton.attr('data-rootid');
		var settingsId = $addButton.attr('data-settingsid');
		var limit = Number($addButton.attr('data-limit'));
		var level = Number($addButton.attr('data-level'));
		var count = Number($addButton.attr('data-count'));    // Holds the current # of rows (this # goes up and down)
		var counter = Number($addButton.attr('data-counter'));  // Holds the # of rows ever created (this # only goes up)

		// console.log("rootId:     " + rootId);
		// console.log("settingsId: " + settingsId);
		// console.log("Limit:      " + limit);
		// console.log("Level:      " + level);
		// console.log("Count:      " + count);
		// console.log("Counter:    " + counter);

		// This will store the data for the path currently taken
		var fieldSettings = reduxObject.serialized_repeater[rootId];

		fieldSettings.level = fieldSettings.level || [];
		fieldSettings.level[level] = {
			'$addButton': $addButton,
			'currentRowNumber': 0
		};

		// console.log(fieldSettings);

		// console.log( $rowContainer.find('> .redux-serialized-repeater-row').length + " rows found");

		$rowContainer.find('> .redux-serialized-repeater-row').each(
			function (rowNumber) {
				// Replaces the {{index-XX}} placeholder with the appropriate number for that level
				function replaceId(id) {
					var regex = /{{index-(\d+)}}/g;

					id = id.replace(regex, function (match, p1) {
						// Replace each index with the appropriate number (pulled from the addButton) for the level

						// console.log("Match: " + match);
						// console.log("   p1: " + p1);
						// console.log(fieldSettings);

						var currentLevelCount = fieldSettings.level[p1].$addButton.attr('data-count');
						var currentLevelCounter = fieldSettings.level[p1].$addButton.attr('data-counter');

						// Check if the current row count matches the stored value
						// If not update and increment the counter

						return currentLevelCounter;
					});

					return id;
				}

				// console.log("START of row loop");

				var isNewRow = false;
				var $thisRow = $(this);

				// console.log("  ROW " + rowNumber);
				// console.log($thisRow);

				// Fields in child repeaters will use this when rewriting their names
				fieldSettings.level[level].currentRowNumber = rowNumber;

				// console.log("Setting level " + level + "'s currentRow to " + rowNumber);
				// console.log(fieldSettings.level);
				// console.log(JSON.parse(JSON.stringify(fieldSettings.level)));

				// Check if this row is new
				if ($thisRow.attr('data-new') == 'true') {
					// console.log("This row is new");

					$thisRow.removeAttr('data-new');
					isNewRow = true;

					// Update the counters
					$addButton.attr('data-count', ++count);
					$addButton.attr('data-counter', ++counter);
				}

				// console.log("Count:     " + count);
				// console.log("Counter:   " + counter);

				// console.log($thisRow.find('input,select'));

				// Get the inputs in this level's rows, rejecting fields that aren't on the current level
				// Those fields will be inside a div.redux-serialized-repeater-row
				var $inputs = $thisRow.find('input,select,textarea').filter(function () {
					// How many rows are found indicates the level of the field since this is
					// counting how many parents there are
					// console.log("INSIDE FILTER");
					// console.log(this);
					// console.log($(this).parents('.redux-serialized-repeater-row').length);
					// console.log('Level: ' + level);
					// console.log($(this).parents('.redux-serialized-repeater-row').length == (level + 1));
					return $(this).parents('.redux-serialized-repeater-row').length == (level + 1);
				});

				// console.log("  " + $inputs.length + " form inputs found for this level");
				// console.log($inputs);

				// Renumber each form element
				$inputs.each(
					function (index) {
						// console.log("  START of input loop " + index);

						var $thisInput = $(this);
						var $thisLevelAddButton = $thisInput.closest('.redux-container-serialized_repeater').find('> .redux-serialized-repeater-add');
						var fieldType = $thisInput.closest('.redux-field-container').attr('data-type');

						// console.log("    Level for element: " + $thisLevelAddButton.attr('data-level'));
						// console.log("    Current level: " + level);
						// console.log("    FIELD level: " + level + "   row: " + rowNumber);
						// console.log(this);

						var name = $thisInput.attr('name');

						if (name) {
							// console.log("    Field name: " + name);

							var nameSuffix = '';

							// Check if this field is part of an auto-numbered array.  The Multi Text field uses these.
							// optname[repeater1][0][name][]
							if (name.substr(-2) == '[]') {
								nameSuffix = '[]';
							}

							// Parse the tokens from the name
							// adventure[rep1][0][contact-rep][0][phone]
							// ['adventure', 'rep1', '0', 'contact-rep', '0', 'phone']
							var regex = /\[([^\]]+)\]/g;
							var matches = [];
							var match = regex.exec(name);

							while (match != null) {
								matches.push(match[1]);
								match = regex.exec(name);
							}

							// console.log("    " + matches.length + " matches found");
							// console.log(matches);

							// Build up the new name string, only replacing the rowNumber when we're at the same level
							var newName = settingsId;
							var matchLevel = -1;
							var parentLevel = 0;

							// console.log("    Walking name path");

							for (var i = 0, count = matches.length; i < count; i++) {
								// console.log("Match #" + i);
								var name = matches[i];

								if ($.isNumeric(matches[i])) {
									// console.log("      IS NUMERIC");

									// The naming pattern is (almost) always string, number, so every number we find means we're another level down
									// However some fields add an index to their name because they store multiple values, need to check for that and
									// make sure not to rewrite it

									// Check if there is another level after this number
									// If there's not, then this is a field with multiple values
									if (i + 1 < matches.length) {
										// This is a repeater level
										// console.log('This is a repeater level');
										++matchLevel;

										if (matchLevel == level) {
											// console.log("      MATCH LEVEL");
											// console.log("      Row number changes from "  + matches[i] + " to " + rowNumber);
											name = rowNumber;
											// console.log("      This field's name is: " + matches[i+1]);
										} else {
											// Use parent row's number
											// console.log("      Using parentLevel " + parentLevel + "'s currentRowNumber of " + fieldSettings.level[parentLevel].currentRowNumber);

											name = fieldSettings.level[parentLevel++].currentRowNumber;
										}
									} else {
										// This is a field with multiple values
										// console.log('This is a field with multiple values');
									}
								}

								newName += '[' + name + ']';

								// console.log("      Current name path: " + newName);
							}

							newName += nameSuffix;

							// console.log("    Done walking name path");
							$thisInput.attr('name', newName);

							// console.log('fieldType');
							// console.log(fieldType);

							// MultiText field stores a copy of the name in data-name
							if (fieldType == 'multi_text') {
								// This is inefficient since it runs for every input
								var $multiTextAddButton = $thisInput.closest('.redux-field-container').find('.redux-multi-text-add');

								if ($multiTextAddButton.attr('data-name').indexOf('[99999]') !== -1) {
									$multiTextAddButton.attr('data-name', newName);
								}
							}

							// console.log("    New Field name: " + newName);
						} // if name


						// Replace all the {{index-X}} placeholders in the ids too
						// Only has to be done when new rows are added
						if (isNewRow) {
							// console.log("  NEW row found");

							var $parentContainer = $thisInput.closest('.redux-field-container');

							// console.log('  $parentContainer');
							// console.log($parentContainer);

							if ($parentContainer.prop('id')) {
								// console.log("Replace id");
								$parentContainer.prop('id', replaceId($parentContainer.prop('id')));
							}

							if ($parentContainer.attr('data-id')) {
								// console.log("Replace data-id");
								$parentContainer.attr('data-id', replaceId($parentContainer.attr('data-id')));
							}

							// console.log("  START Replace ID loop");

							// console.log("  Found " + $parentContainer.find('*[id*="{{index-"]').length + " items that need their id's updated");
							// console.log(             $parentContainer.find('*[id*="{{index-"],*[for*="{{index-"],*[rel*="{{index-"],*[class*="{{index-"]'));

							// Replace the template tags on every element that has them
							// TODO: Modify some of the fields to be consistent in their use of the ids so fewer attributes have to be searched/replaced
							//       Slider, MultiText
							$parentContainer.find('*[id*="{{index-"],*[for*="{{index-"],*[rel*="{{index-"],*[class*="{{index-"],*[data-id*="{{index-"]').each(function () {
								// console.log($(this));

								var $thisElement = $(this);
								var id = $thisElement.prop('id');
								var dataId = $thisElement.attr('data-id');
								var dataBlockId = $thisElement.attr('data-block-id');
								var forProp = $thisElement.prop('for');
								var rel = $thisElement.attr('rel');
								var classProp = $thisElement.prop('class');

								if (id) {
									// console.log("    ID: " + id);
									$thisElement.prop('id', replaceId(id));
									// console.log("    New ID: " + $thisElement.prop('id'));
								}

								if (dataId) {
									// console.log("    Data-id: " + dataId);
									$thisElement.attr('data-id', replaceId(dataId));
									// console.log("    New Data-id: " + $thisElement.attr('data-id'));
								}

								if (dataBlockId) {
									// console.log("    Data-block-id: " + dataBlockId);
									$thisElement.attr('data-block-id', replaceId(dataBlockId));
									// console.log("    New Data-block-id: " + $thisElement.attr('data-block-id'));
								}

								if (forProp) {
									// console.log("    For: " + forProp);
									$thisElement.prop('for', replaceId(forProp));
									// console.log("    New For: " + $thisElement.prop('for'));
								}

								if (rel) {
									// console.log("    Rel: " + rel);
									$thisElement.attr('rel', replaceId(rel));
									// console.log("    New Rel: " + $thisElement.attr('rel'));
								}

								if (classProp) {
									// console.log("    Class: " + classProp);
									$thisElement.prop('class', replaceId(classProp));
									// console.log("    New Class: " + $thisElement.prop('class'));
								}
							});

							// console.log("  END of Replace ID loop");
						} // if newRow

						// console.log("  END of input loop");
						// console.log(this);
					}
				); // $inputs.each()

				// Check if there are any sub-repeaters
				// console.log("  This row contains " + $(this).find('> fieldset > fieldset.redux-container-serialized_repeater').length + " repeaters");

				$(this).find('> fieldset > fieldset.redux-container-serialized_repeater').each(function (index) {
					// console.log("  Sub-repeater: " + index);
					var $thisRepeater = $(this);
					// console.log($(this).find('> .redux-serialized-repeater-row-container'));

					if ($thisRepeater.prop('id')) {
						// console.log("Replace id");
						$thisRepeater.prop('id', replaceId($thisRepeater.prop('id')));
					}

					if ($thisRepeater.attr('data-id')) {
						// console.log("Replace data-id");
						$thisRepeater.attr('data-id', replaceId($thisRepeater.attr('data-id')));
					}

					redux.field_objects.serialized_repeater.renumberRows($(this).find('> .redux-serialized-repeater-row-container'));
				});

				// console.log("END of row loop");
			}
		);

		if (level === 0) {
			// console.log("Wiped fieldSettings");
			fieldSettings.level = [];
		}

		// console.log("ADD DONE");
	};

})(jQuery);
