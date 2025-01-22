jQuery( document ).ready( ( $ ) => {
    // Code Editors
    const header_scripts_el = jQuery( '#_cfw__settingheader_scriptsstring' );
    const footer_scripts_el = jQuery( '#_cfw__settingfooter_scriptsstring' );
    const custom_css_el = jQuery( '#cfw_css_editor textarea.wp-editor-area' );
    const php_snippets_el = jQuery( '#_cfw__settingphp_snippetsstring' );

    // wp.CodeMirror.defineMode('php-snippet', function ( config ) {
    //     return wp.CodeMirror.getMode( config, { name: 'application/x-httpd-php', startOpen: true } );
    // });

    if ( header_scripts_el.length ) {
        const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
            },
        );
        wp.codeEditor.initialize( header_scripts_el, editorSettings );
    }

    if ( footer_scripts_el.length ) {
        const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
            },
        );
        wp.codeEditor.initialize( footer_scripts_el, editorSettings );
    }

    if ( custom_css_el.length ) {
        const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'css',
            },
        );
        wp.codeEditor.initialize( custom_css_el, editorSettings );
    }

    if ( php_snippets_el.length ) {
        const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
        editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                indentUnit: 2,
                tabSize: 2,
                mode: 'application/x-httpd-php-open',
            },
        );
        const instance = wp.codeEditor.initialize( php_snippets_el, editorSettings );

        // We have to do this since WP doesn't configure application/x-httpd-php-open
        instance.codemirror.on( 'keyup', ( editor, event ) => { // eslint-disable-line complexity
            const token = instance.codemirror.getTokenAt( instance.codemirror.getCursor() );

            if ( token.type === 'string' || token.type === 'comment' ) {
                return;
            }

            const shouldAutocomplete = token.type === 'keyword' || token.type === 'variable';

            if ( shouldAutocomplete ) {
                instance.codemirror.showHint( { completeSingle: false } );
            }
        } );
    }

    // Initialize color pickers
    jQuery( '.color-picker' ).wpColorPicker();

    // Uploading files
    let file_frame;
    const wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
    const set_to_post_id = objectiv_cfw_admin.logo_attachment_id;

    jQuery( '#template_select' ).on( 'change', () => {
        const template_value = jQuery( '#template_select' ).val();

        jQuery( '.template_select_info_table_screen_shot_container' ).each( ( index, el ) => { jQuery( el ).css( 'display', 'none' ); } );

        jQuery( `#template_select_info_table_screen_shot_container_${template_value}` ).css( 'display', 'flex' );
    } ).trigger( 'change' );

    jQuery( '#upload_image_button' ).on( 'click', ( event ) => {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            // Set the post ID to what we want
            file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
            // Open frame
            file_frame.open();
            return;
        }
        // Set the wp.media post id so the uploader grabs the ID we want when initialised
        wp.media.model.settings.post.id = set_to_post_id;

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media( {
            title: 'Select a image to upload',
            button: {
                text: 'Use this image',
            },
            multiple: false,	// Set to true to allow multiple files to be selected
        } );

        // When an image is selected, run a callback.
        file_frame.on( 'select', () => {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get( 'selection' ).first().toJSON();

            // Do something with attachment.id and/or attachment.url here
            $( '#image-preview' ).attr( 'src', attachment.url ).css( 'width', 'auto' );
            $( '#logo_attachment_id' ).val( attachment.id );

            // Restore the main post ID
            wp.media.model.settings.post.id = wp_media_post_id;
        } );

        // Finally, open the modal
        file_frame.open();
    } );

    // Restore the main ID when the add media button is pressed
    jQuery( 'a.add_media' ).on( 'click', () => {
        wp.media.model.settings.post.id = wp_media_post_id;
    } );

    // DELETE IMAGE LINK
    $( '.delete-custom-img' ).on( 'click', ( event ) => {
        event.preventDefault();

        $( '#logo_attachment_id' ).val( '' );
        $( '#image-preview' ).attr( 'src', '' ).css( 'width', 'auto' );
    } );

    const show_hide_cart_redirect_url = function () {
        const cart_editing_redirect_url = jQuery( '#cart_edit_empty_cart_redirect' ).parents( 'tr' );
        if ( jQuery( '#enable_cart_editing' ).is( ':checked' ) && jQuery( '#enable_cart_editing' ).is( ':enabled' ) ) {
            cart_editing_redirect_url.show();
        } else {
            cart_editing_redirect_url.hide();
        }
    };

    jQuery( '#enable_cart_editing' ).on( 'change', show_hide_cart_redirect_url );

    show_hide_cart_redirect_url();

    const show_hide_address_autocomplete_fields = function () {
        const google_places_api_key = jQuery( '#google_places_api_key' ).parents( 'tr' );

        if ( ( jQuery( '#enable_address_autocomplete' ).is( ':enabled' ) && jQuery( '#enable_address_autocomplete' ).is( ':checked' ) ) || ( jQuery( '#enable_thank_you_page' ).is( ':enabled' ) && jQuery( '#enable_thank_you_page' ).is( ':checked' ) && jQuery( '#enable_map_embed' ).is( ':checked' ) ) ) {
            google_places_api_key.show();
        } else {
            google_places_api_key.hide();
        }
    };

    jQuery( '#enable_address_autocomplete, #enable_map_embed' ).on( 'change', show_hide_address_autocomplete_fields );

    show_hide_address_autocomplete_fields();

    const show_hide_thank_you_options = function () {
        const map_embed_option = jQuery( '#enable_map_embed' ).parents( 'tr' );
        const thank_you_order_statuses_option = jQuery( '#thank_you_order_statuses' ).parents( 'tr' );
        const my_account_view_orders_option = jQuery( '#override_view_order_template' ).parents( 'tr' );

        if ( jQuery( '#enable_thank_you_page' ).is( ':enabled' ) && jQuery( '#enable_thank_you_page' ).is( ':checked' ) ) {
            map_embed_option.show();
            thank_you_order_statuses_option.show();
            my_account_view_orders_option.show();
        } else {
            map_embed_option.hide();
            thank_you_order_statuses_option.hide();
            my_account_view_orders_option.hide();
        }

        show_hide_address_autocomplete_fields();
    };

    jQuery( '#enable_thank_you_page' ).on( 'change', show_hide_thank_you_options );
    show_hide_thank_you_options();

    jQuery( document.body ).trigger( 'wc-enhanced-select-init' );

    const cfw_body_font_selector = jQuery( '#cfw-body-font-selector' );

    if ( cfw_body_font_selector.length ) {
        cfw_body_font_selector.change( function () {
            const selected = cfw_body_font_selector.find( 'option:selected' ).text();
            jQuery( this ).css( 'font-family', selected );
        } );

        cfw_body_font_selector.one( 'select2:open', () => {
            const select2_id = cfw_body_font_selector.prop( 'id' );
            const font_results = jQuery( `#select2-${select2_id}-results` );
            let timeout;

            font_results.scroll( ( event ) => {
                clearTimeout( timeout );
                timeout = setTimeout( () => {
                    font_results.find( 'li' ).not( '.font-loaded' ).not( ':first-child' ).each( function ( i, element ) {
                        const li = jQuery( this );
                        const font_name = li.text();
                        const top_of_results = font_results.offset().top;
                        const bottom_of_results = top_of_results + font_results.outerHeight();
                        const top_of_item = li.offset().top;
                        const bottom_of_item = top_of_item + li.outerHeight();

                        if ( bottom_of_results > top_of_item && top_of_results < bottom_of_item ) {
                            WebFont.load( {
                                google: {
                                    families: [ font_name ],
                                    text: font_name,
                                },
                                fontactive( family_name ) {
                                    li.css( 'font-family', family_name );
                                    li.addClass( 'font-loaded' );
                                },
                            } );
                        }
                    } );
                }, 100 );
            } ).trigger( 'scroll' );
        } );
    }

    const cfw_heading_font_selector = jQuery( '#cfw-heading-font-selector' );

    if ( cfw_heading_font_selector.length ) {
        cfw_heading_font_selector.change( function () {
            const selected = cfw_heading_font_selector.find( 'option:selected' ).text();
            jQuery( this ).css( 'font-family', selected );
        } );

        cfw_heading_font_selector.one( 'select2:open', () => {
            const select2_id = cfw_heading_font_selector.prop( 'id' );
            const font_results = jQuery( `#select2-${select2_id}-results` );
            let timeout;

            font_results.scroll( ( event ) => {
                clearTimeout( timeout );
                timeout = setTimeout( () => {
                    font_results.find( 'li' ).not( '.font-loaded' ).not( ':first-child' ).each( function ( i, element ) {
                        const li = jQuery( this );
                        const font_name = li.text();
                        const top_of_results = font_results.offset().top;
                        const bottom_of_results = top_of_results + font_results.outerHeight();
                        const top_of_item = li.offset().top;
                        const bottom_of_item = top_of_item + li.outerHeight();

                        if ( bottom_of_results > top_of_item && top_of_results < bottom_of_item ) {
                            WebFont.load( {
                                google: {
                                    families: [ font_name ],
                                    text: font_name,
                                },
                                fontactive( family_name ) {
                                    li.css( 'font-family', family_name );
                                    li.addClass( 'font-loaded' );
                                },
                            } );
                        }
                    } );
                }, 100 );
            } ).trigger( 'scroll' );
        } );
    }

    const exportButton = jQuery( '#export_settings_button' );

    exportButton.on( 'click', ( e ) => {
        e.preventDefault();
        const nonce = jQuery( '#export_settings_button' ).data( 'nonce' );
        jQuery.ajax( {
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'cfw_generate_settings',
                nonce,
            },
            success( response ) {
                if ( response ) {
                    const data = `data:text/json;charset=utf-8,${encodeURIComponent( response )}`;
                    const element = document.createElement( 'a' );
                    element.setAttribute( 'href', data );
                    element.setAttribute( 'download', 'cfw-settings.json' );
                    document.body.appendChild( element );
                    element.click();
                    element.remove();
                }
            },
        } );
    } );

    const importButton = jQuery( '#import_settings_button' );

    importButton.click( ( e ) => {
        // eslint-disable-next-line no-alert
        if ( !window.confirm( 'Are you sure you want replace your current settings?' ) ) {
            e.preventDefault();
        }
    } );
} );
