/*global redux_change, redux*/

/**
 * Typography
 * Dependencies:        google.com, jquery, select2
 * Feature added by:    Dovy Paukstys - http://simplerain.com/
 * Date:                06.14.2013
 *
 * Rewrite:             Kevin Provance (kprovance)
 * Date:                May 25, 2014
 * And again on:        April 4, 2017 for v4.0
 *
 */

(function( $ ) {
    "use strict";

    redux.field_objects = redux.field_objects || {};
    redux.field_objects.typography = redux.field_objects.typography || {};

    var selVals     = [];
    var isSelecting = false;
    var proLoaded   = true;

    redux.field_objects.typography.init = function( selector, skipCheck ) {
        if ( !selector ) {
            selector = $( document ).find( ".redux-group-tab:visible" ).find( '.redux-container-typography:visible' );
        }

        $( selector ).each(
            function() {
                var el = $( this );
                var parent = el;

                if ( !el.hasClass( 'redux-field-container' ) ) {
                    parent = el.parents( '.redux-field-container:first' );
                }

                if ( parent.is( ":hidden" ) ) { // Skip hidden fields
                    return;
                }

                if ( parent.hasClass( 'redux-field-init' ) ) {
                    parent.removeClass( 'redux-field-init' );
                } else {
                    return;
                }

                if (redux.field_objects.pro === undefined) {
                    proLoaded = false;
                }

                el.each(
                    function() {
                        // init each typography field
                        var optName = el.parents( '.redux-container' ).data( 'opt-name' );

                        $( this ).find( '.redux-typography-container' ).each(
                            function() {
                                var family = $( this ).find( '.redux-typography-family' );
                                var familyData = family.data( 'value' );

                                if ( familyData === undefined ) {
                                    family = $( this );
                                } else if ( familyData !== "" ) {
                                    $( family ).val( familyData );
                                }

                                var data = [{id: 'none', text: 'none'}];
                                var thisID = $( this ).find( '.redux-typography-family' ).parents(
                                    '.redux-container-typography:first' ).data( 'id' );

                                // User included fonts?
                                var isUserFonts = $( '#' + thisID + ' .redux-typography-font-family' ).data(
                                    'user-fonts' );

                                isUserFonts = isUserFonts ? 1 : 0;

                                // Google font isn use?
                                var usingGoogleFonts = $( '#' + thisID + ' .redux-typography-google' ).val();
                                usingGoogleFonts = usingGoogleFonts ? 1 : 0;

                                // Set up data array
                                var buildData = [];

                                // If custom fonts, push onto array
                                if ( redux.optName.customfonts !== undefined ) {
                                    buildData.push( redux.optName.customfonts );
                                }

                                // If typekit fonts, push onto array
                                if ( redux.optName.typekitfonts !== undefined ) {
                                    buildData.push( redux.optName.typekitfonts );
                                }

                                // If standard fonts, push onto array
                                if ( redux.optName.stdfonts !== undefined && isUserFonts === 0 ) {
                                    buildData.push( redux.optName.stdfonts );
                                }

                                // If user fonts, pull from localize and push into array
                                if ( isUserFonts === 1 ) {
                                    var fontKids = [];

                                    // <option>
                                    for ( var key in redux.optName.typography[thisID] ) {
                                        var obj = redux.optName.typography[thisID].std_font;

                                        for ( var prop in obj ) {
                                            if ( obj.hasOwnProperty( prop ) ) {
                                                fontKids.push(
                                                    {
                                                        id: prop,
                                                        text: prop,
                                                        'data-google': 'false'
                                                    }
                                                );
                                            }
                                        }
                                    }

                                    // <optgroup>
                                    var fontData = {
                                        text: 'Standard Fonts',
                                        children: fontKids
                                    };

                                    buildData.push( fontData );
                                }

                                // If googfonts on and had data, push into array
                                if ( usingGoogleFonts === 1 || usingGoogleFonts === true && redux.optName.googlefonts !== undefined ) {
                                    buildData.push( redux.optName.googlefonts );
                                }

                                // output data to drop down
                                data = buildData;

                                var val = $( this ).find( ".redux-typography-family" ).data( 'value' );

                                $( this ).find( ".redux-typography-family" ).select2( {data: data} );
                                $( this ).find( ".redux-typography-family" ).val( val ).trigger( 'change' );

                                var xx = el.find( ".redux-typography-family" );
                                if ( !xx.hasClass( 'redux-typography-family' ) ) {
                                    el.find( ".redux-typography-style" ).select2();
                                }

                                $( this ).find( ".redux-typography-align" ).select2();
                                $( this ).find( ".redux-typography-family-backup" ).select2();
                                $( this ).find( ".redux-typography-transform" ).select2();
                                $( this ).find( ".redux-typography-font-variant" ).select2();
                                $( this ).find( ".redux-typography-decoration" ).select2();

                                // Init select2 for indicated fields
                                redux.field_objects.typography.select( family, true, false, null, true );

                                //init when value is changed
                                $( this ).find(
                                    '.redux-typography-family, .redux-typography-family-backup, .redux-typography-style, .redux-typography-subsets, .redux-typography-align' ).on(
                                    'change', function( val ) {
                                        var thisID = $( this ).attr( 'id' ),
                                            that = $( '#' + thisID );

                                        if ( $( this ).hasClass( 'redux-typography-family' ) ) {
                                            if ( that.val() ) {
                                                var getVals = $( this ).select2( 'data' );
                                                var fontName;

                                                if ( getVals ) {
                                                    fontName = getVals[0].text;
                                                } else {
                                                    fontName = null;
                                                }

                                                that.data( 'value', fontName );

                                                selVals = getVals[0];

                                                isSelecting = true;

                                                redux.field_objects.typography.select(
                                                    that, true, false, fontName, true );
                                            }
                                        } else {
                                            val = that.val();

                                            that.data( 'value', val );

                                            if ( $( this ).hasClass( 'redux-typography-align' ) || $( this ).hasClass(
                                                    'redux-typography-subsets' ) || $( this ).hasClass(
                                                    'redux-typography-family-backup' ) || $( this ).hasClass(
                                                    'redux-typography-transform' ) || $( this ).hasClass(
                                                    'redux-typography-font-variant' ) || $( this ).hasClass(
                                                    'redux-typography-decoration' ) ) {
                                                that.find( 'option[selected="selected"]' ).removeAttr(
                                                    'selected' );
                                                that.find('option[value="' + val + '"]' ).attr(
                                                    'selected', 'selected' );
                                            }

                                            if ( $( this ).hasClass( 'redux-typography-subsets' ) ) {
                                                that.siblings( '.typography-subsets' ).val( val );
                                            }

                                            redux.field_objects.typography.select(
                                                $( this ), true, false, null, false );
                                        }
                                    }
                                );

                                //init when value is changed
                                $( this ).find(
                                    '.redux-typography-size, .redux-typography-height, .redux-typography-word, .redux-typography-letter' ).keyup(
                                    function() {
                                        redux.field_objects.typography.select(
                                            $( this ).parents( '.redux-container-typography:first' ) );
                                    }
                                );

                                if (proLoaded) {
                                    redux.field_objects.pro.typography.fieldChange($(this));
                                    redux.field_objects.pro.typography.colorPicker ($(this));
                                }

                                // Have to redeclare the wpColorPicker to get a callback function
                                $( this ).find(
                                    '.redux-typography-color' ).wpColorPicker( {
                                    change: function( e, ui ) {
                                        $( this ).val( ui.color.toString() );
                                        redux.field_objects.typography.select(
                                            $( this ).parents( '.redux-container-typography:first' ) );
                                    }
                                } );

                                // Don't allow negative numbers for size field
                                $( this ).find( ".redux-typography-size" ).numeric( {allowMinus: false} );

                                // Allow negative numbers for indicated fields
                                $( this ).find(
                                    ".redux-typography-height, .redux-typography-word, .redux-typography-letter" ).numeric(
                                    {allowMinus: true} );

                                var reduxTypography = $( this ).find( ".redux-typography" );
                                reduxTypography.on(
                                    'select2:unselecting', function() {
                                        var opts = $(this).data('select2').options;

                                        opts.set('disabled', true);
                                        setTimeout(function() {
                                            opts.set('disabled', false);
                                        }, 1);

                                        var thisID = $( this ).attr( 'id' ),
											that = $( '#' + thisID );

                                        that.data( 'value', '' );

                                        if ( $( this ).hasClass( 'redux-typography-family' ) ) {
                                            $( this ).val( null ).trigger( 'change' );

                                            redux.field_objects.typography.select(
                                                that, true, false, null, true );
                                        } else {
                                            if ( $( this ).hasClass( 'redux-typography-align' ) || $( this ).hasClass(
                                                    'redux-typography-subsets' ) || $( this ).hasClass(
                                                    'redux-typography-family-backup' ) || $( this ).hasClass(
                                                    'redux-typography-transform' ) || $( this ).hasClass(
                                                    'redux-typography-font-variant' ) || $( this ).hasClass(
                                                    'redux-typography-decoration' ) ) {
                                                $( '#' + thisID + ' option[selected="selected"]' ).removeAttr(
                                                    'selected' );
                                            }

                                            if ( $( this ).hasClass( 'redux-typography-subsets' ) ) {
                                                that.siblings( '.typography-subsets' ).val( '' );
                                            }

                                            if ( $( this ).hasClass( 'redux-typography-family-backup' ) ) {
                                                that.val( null ).trigger( 'change' );
                                            }

                                            redux.field_objects.typography.select(
                                                $( this ), true, false, null, false );
                                        }
                                    }
                                );
                                redux.field_objects.typography.updates( $( this ) );

                                window.onbeforeunload = null;
                            }
                        );
                    }
                );
            }
        );
    };

    redux.field_objects.typography.updates = function( obj ) {
        obj.find( '.update-google-fonts' ).bind( "click", function( e ) {
            var $action = $( this ).data( 'action' );
            var $update_parent = $( this ).parent().parent();
            var $nonce = $update_parent.attr( "data-nonce" );

            // $( this ).parent().parent().addClass( 'updating-message' );
            $update_parent.find( 'p' ).text( redux_ajax_script.update_google_fonts.updating );
            $update_parent.find( 'p' ).attr( 'aria-label', redux_ajax_script.update_google_fonts.updating );
            // ''
            $update_parent.removeClass(
                'updating-message updated-message notice-success notice-warning notice-error'
            ).addClass(
                'update-message notice-warning updating-message'
            );
            $.ajax(
                {
                    type: "post",
                    dataType: "json",
                    url: redux_ajax_script.ajaxurl,
                    data: {
                        action: "redux_update_google_fonts",
                        nonce: $nonce,
                        data: $action
                    },
                    error: function( response ) {
                        console.log( response );
                        $update_parent.removeClass(
                            'notice-warning updating-message updated-message notice-success'
                        ).addClass(
                            'notice-error'
                        );
                        var msg = response.error;
                        if (msg) {
                          msg = ': "'+msg+'"';
                        }
                        $update_parent.find( 'p' ).html(
                            redux_ajax_script.update_google_fonts.error.replace( '%s', $action ).replace('|msg', msg)
                        );
                        $update_parent.find( 'p' ).attr( 'aria-label', redux_ajax_script.update_google_fonts.error );
                        redux.field_objects.typography.updates( obj );
                    },
                    success: function( response ) {
                        console.log( response );

                        if ( response.status === "success" ) {
                            $update_parent.find( 'p' ).html( redux_ajax_script.update_google_fonts.success );
                            $update_parent.find( 'p' ).attr(
                                'aria-label', redux_ajax_script.update_google_fonts.success );
                            $update_parent.removeClass(
                                'updating-message notice-warning'
                            ).addClass(
                                'updated-message notice-success'
                            );
                            $('.redux-update-google-fonts').not(".notice-success").remove();

                        } else {
                            $update_parent.removeClass(
                                'notice-warning updating-message updated-message notice-success'
                            ).addClass(
                                'notice-error'
                            );
                            var msg = response.error;
                            if (msg) {
                              msg = ': "'+msg+'"';
                            }
                            $update_parent.find( 'p' ).html(
                                redux_ajax_script.update_google_fonts.error.replace( '%s', $action ).replace('|msg', msg) );
                            $update_parent.find( 'p' ).attr(
                                'aria-label', redux_ajax_script.update_google_fonts.error );
                            redux.field_objects.typography.updates( obj );
                        }
                    }
                }
            );

            e.preventDefault();
            return false;
        } );
    };

    // Return font size
    redux.field_objects.typography.size = function( obj ) {
        var size = 0;
        var key;

        for ( key in obj ) {
            if ( obj.hasOwnProperty( key ) ) {
                size++;
            }
        }

        return size;
    };

    // Return proper bool value
    redux.field_objects.typography.makeBool = function( val ) {
        if ( val === 'false' || val === '0' || val === false || val === 0 ) {
            return false;
        } else if ( val === 'true' || val === '1' || val === true || val === 1 ) {
            return true;
        }
    };

    redux.field_objects.typography.contrastColour = function( hexcolour ) {
        // default value is black.
        var retVal = '#444444';

        // In case - for some reason - a blank value is passed.
        // This should *not* happen.  If a function passing a value
        // is canceled, it should pass the current value instead of
        // a blank.  This is how the Windows Common Controls do it.  :P
        if ( hexcolour !== '' ) {

            // Replace the hash with a blank.
            hexcolour = hexcolour.replace( '#', '' );

            var r = parseInt( hexcolour.substr( 0, 2 ), 16 );
            var g = parseInt( hexcolour.substr( 2, 2 ), 16 );
            var b = parseInt( hexcolour.substr( 4, 2 ), 16 );
            var res = ((r * 299) + (g * 587) + (b * 114)) / 1000;

            // Instead of pure black, I opted to use WP 3.8 black, so it looks uniform.  :) - kp
            retVal = (res >= 128) ? '#444444' : '#ffffff';
        }

        return retVal;
    };


    //  Sync up font options
    redux.field_objects.typography.select = function( selector, skipCheck, destroy, fontName, active ) {
        var mainID;

        // Main id for selected field
        mainID = $( selector ).parents( '.redux-container-typography:first' ).data( 'id' );
        if ( mainID === undefined ) {
            mainID = $( selector ).data( 'id' );
        }
        var that = $( '#' + mainID),
		    family = $( '#' + mainID + '-family' ).val();

        if ( !family ) {
            family = null; //"inherit";
        }

        if ( fontName ) {
            family = fontName;
        }

        var familyBackup = that.find('select.redux-typography-family-backup' ).val();
        var size = that.find('.redux-typography-size' ).val();
        var height = that.find('.redux-typography-height' ).val();
        var word = that.find('.redux-typography-word' ).val();
        var letter = that.find('.redux-typography-letter' ).val();
        var align = that.find('select.redux-typography-align' ).val();
        var transform = that.find('select.redux-typography-transform' ).val();
        var fontVariant = that.find('select.redux-typography-font-variant' ).val();
        var decoration = that.find('select.redux-typography-decoration' ).val();
        var style = that.find('select.redux-typography-style' ).val();
        var script = that.find('select.redux-typography-subsets' ).val();
        var color = that.find('.redux-typography-color' ).val();
        var units = that.data( 'units' );

        var google;

        // Is selected font a google font?
        if ( isSelecting === true ) {
            google = redux.field_objects.typography.makeBool( selVals['data-google'] );
            that.find('.redux-typography-google-font' ).val( google );
        } else {
            google = redux.field_objects.typography.makeBool(
                that.find('.redux-typography-google-font' ).val()
            ); // Check if font is a google font
        }

        if ( active ) {

            // Page load. Speeds things up memory wise to offload to client
            if ( !that.hasClass( 'typography-initialized' ) ) {
                style = that.find('select.redux-typography-style' ).data( 'value' );
                script = that.find('select.redux-typography-subsets' ).data( 'value' );

                if ( style !== "" ) {
                    style = String( style );
                }

                if ( typeof (script) !== undefined ) {
                    script = String( script );
                }
            }

            // Something went wrong trying to read google fonts, so turn google off
            if ( redux.optName.fonts.google === undefined ) {
                google = false;
            }

            var typekit = false;

            // Get font details
            var details = '';
            if ( google === true && ( family in redux.optName.fonts.google) ) {
                details = redux.optName.fonts.google[family];
            } else {
                if ( redux.optName.fonts.typekit !== undefined && ( family in redux.optName.fonts.typekit) ) {
                    typekit = true;
                    details = redux.optName.fonts.typekit[family];
                } else {
                    details = {
                        '400': 'Normal 400',
                        '700': 'Bold 700',
                        '400italic': 'Normal 400 Italic',
                        '700italic': 'Bold 700 Italic'
                    };
                }
            }

            if ( $( selector ).hasClass( 'redux-typography-subsets' ) ) {
                that.find('input.typography-subsets' ).val( script );
            }

            // If we changed the font
            if ( $( selector ).hasClass( 'redux-typography-family' ) ) {
                var html = '<option value=""></option>';
                var selected = "";

                // Google specific stuff
                if ( google === true ) {

                    // STYLES
                    $.each(
                        details.variants, function( index, variant ) {
                            if ( variant.id === style || redux.field_objects.typography.size(
                                    details.variants ) === 1 ) {
                                selected = ' selected="selected"';
                                style = variant.id;
                            } else {
                                selected = "";
                            }

                            html += '<option value="' + variant.id + '"' + selected + '>' + variant.name.replace(
                                    /\+/g, " "
                                ) + '</option>';
                        }
                    );

                    // destroy select2
                    if ( destroy ) {
                        that.find('.redux-typography-style' ).select2( "destroy" );
                    }

                    // Instert new HTML
                    that.find('.redux-typography-style' ).html( html ).select2();

                    // SUBSETS
                    selected = "";
                    html = '<option value=""></option>';

                    $.each(
                        details.subsets, function( index, subset ) {
                            if ( subset.id === script || redux.field_objects.typography.size(
                                    details.subsets ) === 1 ) {
                                selected = ' selected="selected"';
                                script = subset.id;
                                that.find('input.typography-subsets' ).val( script );
                            } else {
                                selected = "";
                            }
                            html += '<option value="' + subset.id + '"' + selected + '>' + subset.name.replace(
                                    /\+/g, " "
                                ) + '</option>';
                        }
                    );

                    // Destroy select2
                    if ( destroy ) {
                        that.find('.redux-typography-subsets' ).select2( "destroy" );
                    }

                    // Inset new HTML
                    that.find('.redux-typography-subsets' ).html( html ).select2();

                    that.find('.redux-typography-subsets' ).parent().fadeIn( 'fast' );
                    that.find('.typography-family-backup' ).fadeIn( 'fast' );
                } else if ( typekit === true ) {
                    $.each(
                        details.variants, function( index, variant ) {
                            if ( variant.id === style || redux.field_objects.typography.size(
                                    details.variants ) === 1 ) {
                                selected = ' selected="selected"';
                                style = variant.id;
                            } else {
                                selected = "";
                            }

                            html += '<option value="' + variant.id + '"' + selected + '>' + variant.name.replace(
                                    /\+/g, " "
                                ) + '</option>';
                        }
                    );

                    // destroy select2
                    that.find('.redux-typography-style' ).select2( "destroy" );

                    // Instert new HTML
                    that.find('.redux-typography-style' ).html( html ).select2();

                    // Prettify things
                    that.find('.redux-typography-subsets' ).parent().fadeOut( 'fast' );
                    that.find('.typography-family-backup' ).fadeOut( 'fast' );
                } else {
                    if ( details ) {
                        $.each(
                            details, function( index, value ) {
                                if ( index === style || index === "normal" ) {
                                    selected = ' selected="selected"';
                                    that.find('.typography-style select2-selection__rendered' ).text( value );
                                } else {
                                    selected = "";
                                }

                                html += '<option value="' + index + '"' + selected + '>' + value.replace(
                                        '+', ' '
                                    ) + '</option>';
                            }
                        );

                        // Destory select2
                        if ( destroy ) {
                            that.find('.redux-typography-style' ).select2( "destroy" );
                        }

                        // Insert new HTML
                        that.find('.redux-typography-style' ).html( html ).select2();

                        // Prettify things
                        that.find('.redux-typography-subsets' ).parent().fadeOut( 'fast' );
                        that.find('.typography-family-backup' ).fadeOut( 'fast' );
                    }
                }

                that.find('.redux-typography-font-family' ).val( family );
            } else if ( $( selector ).hasClass( 'redux-typography-family-backup' ) && familyBackup !== "" ) {
                that.find('.redux-typography-font-family-backup' ).val( familyBackup );
            }
        }

        if ( active ) {
            // Check if the selected value exists. If not, empty it. Else, apply it.
            if ( that.find("select.redux-typography-style option[value='" + style + "']" ).length === 0 ) {
                style = "";
                that.find('select.redux-typography-style' ).val( '' ).trigger( 'change' );
            } else if ( style === "400" ) {
                that.find('select.redux-typography-style' ).val( style ).trigger( 'change' );
            }

            // Handle empty subset select
            if ( that.find("select.redux-typography-subsets option[value='" + script + "']" ).length === 0 ) {
                script = "";
                that.find('select.redux-typography-subsets' ).val( '' ).trigger( 'change' );
                that.find('input.typography-subsets' ).val( script );
            }
        }

        var _linkclass = 'style_link_' + mainID;

        //remove other elements crested in <head>
        $( '.' + _linkclass ).remove();
        if ( family !== null && family !== "inherit" && that.hasClass( 'typography-initialized' ) ) {

            //replace spaces with "+" sign
            var the_font = family.replace( /\s+/g, '+' );
            if ( google === true ) {

                //add reference to google font family
                var link = the_font;

                if ( style && style !== "" ) {
                    link += ':' + style.replace( /\-/g, " " );
                }

                if ( script && script !== "" ) {
                    link += '&subset=' + script;
                }

                if ( isSelecting === false ) {
                    if ( typeof (WebFont) !== "undefined" && WebFont ) {
                        WebFont.load( {google: {families: [link]}} );
                    }
                }
                that.find('.redux-typography-google' ).val( true );
            } else {
                that.find('.redux-typography-google' ).val( false );
            }
        }

        // Weight and italic
        if ( style.indexOf( "italic" ) !== -1 ) {
            that.find('.typography-preview' ).css( 'font-style', 'italic' );
            that.find('.typography-font-style' ).val( 'italic' );
            style = style.replace( 'italic', '' );
        } else {
            that.find('.typography-preview' ).css( 'font-style', "normal" );
            that.find('.typography-font-style' ).val( '' );
        }

        that.find('.typography-font-weight' ).val( style );

        if ( !height ) {
            height = size;
        }

        if ( size === '' || size === undefined ) {
            that.find('.typography-font-size' ).val( '' );
        } else {
            that.find('.typography-font-size' ).val( size + units );
        }

        if ( height === '' || height === undefined ) {
            that.find('.typography-line-height' ).val( '' );
        } else {
            that.find('.typography-line-height' ).val( height + units );
        }

        if ( word === '' || word === undefined ) {
            that.find('.typography-word-spacing' ).val( '' );
        } else {
            that.find('.typography-word-spacing' ).val( word + units );
        }

        if ( letter === '' || letter === undefined ) {
            that.find('.typography-letter-spacing' ).val( '' );
        } else {
            that.find('.typography-letter-spacing' ).val( letter + units );
        }

        if (proLoaded) {
            redux.field_objects.pro.typography.select(mainID);
        }

        // Show more preview stuff
        if ( that.hasClass( 'typography-initialized' ) ) {
            var isPreviewSize = that.find('.typography-preview' ).data( 'preview-size' );

            if ( isPreviewSize === 0 ) {
                that.find('.typography-preview' ).css( 'font-size', size + units );
            }

            that.find('.typography-preview' ).css( {'font-weight': style, 'text-align': align, 'font-family': family + ', sans-serif' } );

            if ( family === 'none' && family === '' ) {
                //if selected is not a font remove style "font-family" at preview box
                that.find('.typography-preview' ).css( 'font-family', 'inherit' );
            }

            that.find('.typography-preview' ).css( {'line-height': height + units, 'word-spacing': word + units, 'letter-spacing': letter + units} );

            if ( color ) {
                that.find('.typography-preview' ).css( 'color', color );
                //                that.find('.typography-preview' ).css(
                //                    'background-color', redux.field_objects.typography.contrastColour( color )
                //                );
            }

            if (proLoaded) {
                redux.field_objects.typography.previewShadow(mainID);
            }

            that.find('.typography-style select2-selection__rendered' ).text(
                that.find('.redux-typography-style option:selected' ).text()
            );

            that.find('.typography-script select2-selection__rendered' ).text(
                that.find('.redux-typography-subsets option:selected' ).text()
            );

            if ( align ) {
                that.find('.typography-preview' ).css( 'text-align', align );
            }

            if ( transform ) {
                that.find('.typography-preview' ).css( 'text-transform', transform );
            }

            if ( fontVariant ) {
                that.find('.typography-preview' ).css( 'font-variant', fontVariant );
            }

            if ( decoration ) {
                that.find('.typography-preview' ).css( 'text-decoration', decoration );
            }
            that.find('.typography-preview' ).slideDown();
        }
        // end preview stuff

        // if not preview showing, then set preview to show
        if ( !that.hasClass( 'typography-initialized' ) ) {
            that.addClass( 'typography-initialized' );
        }

        isSelecting = false;

        if ( !skipCheck ) {
            redux_change( selector );
        }
    };
})( jQuery );