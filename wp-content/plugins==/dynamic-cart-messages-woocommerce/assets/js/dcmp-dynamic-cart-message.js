jQuery(document).ready(function () {
    const { __ } = wp.i18n;

    // To Enable wp color picker for all color field on settings API page.
    jQuery('.dcmp-color-field').wpColorPicker();
    jQuery('#dcmp-custom-message-background-colors-gradient, #dcmp-custom-message-background-colors, #dcmp-custom-message-text-colors, #dcmp-custom-button-background-colors, #dcmp-custom-button-text-colors, #dcmp-custom-message-box-border-colors, #dcmp-custom-button-text-colors-on-hover, #dcmp-custom-icon-colors').wpColorPicker();


    // To set initial values for radio button i.e 'Message based on' and 'Message Condition Type'.

    if (false == jQuery("input[name='dcmp_taxonomy_type']").is(':checked')) {
        jQuery("input[name='dcmp_taxonomy_type']:first-child").prop("checked", true);  // Product Category.
    }
    if (false == jQuery("input[name='dcmp_message_type']").is(':checked')) {
        jQuery("input[name='dcmp_message_type']:first-child").prop("checked", true); // Simple text.
    }
    if (false == jQuery("input[name='dcmp_icon']").is(':checked')) {
        jQuery("input[name='dcmp_icon']:first-child").prop("checked", true); // Icon.
    }

    // To show/hide 'Initial Message for Product page' for product message.
    jQuery('input[name="dcmp_show_in_product_page"]').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#main_dcmp_product_page_message").show().find('#dcmp_product_page_message').prop('required', true);
        } else {
            jQuery("#main_dcmp_product_page_message").hide().find('#dcmp_product_page_message').prop('required', false);
        }
    }).trigger('change');

    // To show/hide 'Initial Message for Product page' for product message.
    jQuery('input[name="dcmp_show_message_button"]').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#main_dcmp_message_button_label").show().find('#dcmp_message_button_label').prop('required', true);
            jQuery("#main_dcmp_message_button_url").show().find('#dcmp_message_button_url').prop('required', true);
            jQuery("#main_dcmp_message_open_new_tab").show().find('#dcmp_message_open_new_tab');
        } else {
            jQuery("#main_dcmp_message_button_label").hide().find('#dcmp_message_button_label').prop('required', false);
            jQuery("#main_dcmp_message_button_url").hide().find('#dcmp_message_button_url').prop('required', false);
            jQuery("#main_dcmp_message_open_new_tab").hide().find('#dcmp_message_open_new_tab');
        }
    }).trigger('change');

    // Show/Hide the drowpdown list for taxonomy type. i.e Product Name or Product category.

    jQuery('input[name="dcmp_taxonomy_type"]').change(function () {

        var selected_value = jQuery("input[name='dcmp_taxonomy_type']:checked").val();
        if (selected_value == 'product_category') {
            jQuery("#main_dcmp_selected_category").show();
            jQuery("#main_dcmp_selected_product").hide();
        }
        else if (selected_value == 'product_name') {
            jQuery("#main_dcmp_selected_product").show();
            jQuery("#main_dcmp_selected_category").hide();
        }
        else {
            jQuery("#main_dcmp_selected_product").hide();
            jQuery("#main_dcmp_selected_category").hide();
        } // Not need this else part, because radio button values is set above initailly --SFT. 
    }).trigger('change');


    //Show/Hide the text field and change the description below the text field based on Condition selected .

    jQuery('input[name="dcmp_message_type"]').change(function () {

        var selected_value = null;
        selected_value = jQuery("input[name='dcmp_message_type']:checked").val();

        if (selected_value == 'simple_text') {
            jQuery("#main_dcmp_threshold_value").hide().find("#dcmp_threshold_value").prop("required", false);
            // jQuery("#main_dcmp_threshold_message").hide().find("#dcmp_threshold_message").prop("required", false);
            jQuery("#main_dcmp_threshold_message").hide();
            jQuery("#dcmp_after_initial_message").prop('required', true);
            jQuery("#dcmp_after_initial_message_field_desc").html(__('Enter a Simple Text', 'dynamic-cart-messages-woocommerce'));
        }

        else if (selected_value == 'dcmp_price') {
            jQuery("#main_dcmp_threshold_value").show().find("#dcmp_threshold_value").prop("required", true);
            // jQuery("#main_dcmp_threshold_message").show().find("#dcmp_threshold_message").prop("required", true);
            jQuery("#main_dcmp_threshold_message").show();
            jQuery("#dcmp_after_initial_message").prop('required', true);
            jQuery("#dcmp_threshold_value_field_desc").html(__('Enter the threshold value for price', 'dynamic-cart-messages-woocommerce'));
            jQuery("#dcmp_after_initial_message_field_desc").html(__('Use placeholder <strong> {cs} </strong> to show default currency symbol and <strong> {price} </strong> to show price.<br>For E.g Buy for <strong>{cs}{price}</strong> more to avail Free delivery in your cart! will show up as "Buy for <strong>$10</strong> more to avail Free delivery in your cart!"', 'dynamic-cart-messages-woocommerce'));
        }
        else {
            jQuery("#main_dcmp_threshold_value").show().find("#dcmp_threshold_value").prop("required", true);
            // jQuery("#main_dcmp_threshold_message").show().find("#dcmp_threshold_message").prop("required", true);
            jQuery("#main_dcmp_threshold_message").show();
            jQuery("#dcmp_after_initial_message").prop('required', true);
            jQuery("#dcmp_threshold_value_field_desc").html(__('Enter the threshold value for quantity', 'dynamic-cart-messages-woocommerce'));
            jQuery("#dcmp_after_initial_message_field_desc").html(__('Use placeholder <strong> {cs} </strong> to show default currency symbol and <strong> {qty} </strong> to show quantity.<br>For E.g Buy <strong> {qty} </strong> pcs more to avail discount of <strong> {cs} </strong> 20 in your cart! will show up as "Buy <strong>5</strong> pcs more to avail discount of <strong>$</strong>20 in your cart!"', 'dynamic-cart-messages-woocommerce'));
        } // Not need this else part, because radio button values is set above initailly --SFT. 
    }).trigger('change');

    // To show selected message icon. 
    jQuery("select#dcmp_message_icon").change(function () {
        jQuery('.message-icon i').removeClass().addClass('fa fa-2x ' + jQuery(this).val());
    });

     // To show Custom Color option for the Cart Message Setting Page.
     jQuery('input[name="dcmp_custom_color"]').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#main_dcmp-custom-message-background-colors").show().find('#dcmp-custom-message-background-colors');
            jQuery("#main_dcmp-custom-message-text-colors").show().find('#dcmp-custom-message-text-colors');
            jQuery("#main_dcmp-custom-button-background-colors").show().find('#dcmp-custom-button-background-colors');
            jQuery("#main_dcmp-custom-button-text-colors").show().find('#dcmp-custom-button-text-colors');
            jQuery("#main_dcmp-custom-button-text-colors-on-hover").show().find('#dcmp-custom-button-text-colors-on-hover');
            jQuery("#main_dcmp-custom-icon-colors").show().find('#dcmp-custom-icon-colors');
            jQuery("#main_dcmp-custom-message-box-border-style").show().find('#dcmp-custom-message-box-border-style');
            jQuery("#main_dcmp_custom_message_radius").show().find('#dcmp_custom_message_radius');
            jQuery("#main_dcmp_custom_button_radius").show().find('#dcmp_custom_button_radius');
            jQuery("#main_dcmp_grad_msg_bg_color").show().find('#dcmp_grad_msg_bg_color');

            if ( jQuery("#dcmp_grad_msg_bg_color").is(":checked") ) {
                jQuery("#main_dcmp_grad_effect").show().find('#dcmp_grad_effect');
                jQuery("#dcmp-custom-message-background-colors-gradient").parent().parent().parent().show();
            } else {
                jQuery("#main_dcmp_grad_effect").hide().find('#dcmp_grad_effect');
                jQuery("#dcmp-custom-message-background-colors-gradient").parent().parent().parent().hide();
            }

        } else {
            jQuery("#main_dcmp_grad_msg_bg_color").hide().find('#dcmp_grad_msg_bg_color');
            jQuery("#main_dcmp_grad_effect").hide().find('#dcmp_grad_effect');
            jQuery("#main_dcmp-custom-message-background-colors").hide().find('#dcmp-custom-message-background-colors');
            jQuery("#main_dcmp-custom-message-text-colors").hide().find('#dcmp-custom-message-text-colors');
            jQuery("#main_dcmp-custom-button-background-colors").hide().find('#dcmp-custom-button-background-colors');
            jQuery("#main_dcmp-custom-button-text-colors").hide().find('#dcmp-custom-button-text-colors');
            jQuery("#main_dcmp-custom-button-text-colors-on-hover").hide().find('#dcmp-custom-button-text-colors-on-hover');
            jQuery("#main_dcmp-custom-icon-colors").hide().find('#dcmp-custom-icon-colors');
            jQuery("#main_dcmp-custom-message-box-border-style").hide().find('#dcmp-custom-message-box-border-style');
            jQuery("#main_dcmp_custom_message_radius").hide().find('#dcmp_custom_message_radius');
            jQuery("#main_dcmp_custom_button_radius").hide().find('#dcmp_custom_button_radius');

        }
    }).trigger('change');

    // Hide / Show for Gradient Color Fields.
    if (jQuery('#dcmp_grad_msg_bg_color').is(':checked')) {
        jQuery("#main_dcmp_grad_effect").show().find('#dcmp_grad_effect');
        jQuery("#dcmp-custom-message-background-colors-gradient").parent().parent().parent().show();
    } else {
        jQuery("#main_dcmp_grad_effect").hide().find('#dcmp_grad_effect');
        jQuery("#dcmp-custom-message-background-colors-gradient").parent().parent().parent().hide();
    }
    jQuery('input[name="dcmp_grad_msg_bg_color"]').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#main_dcmp_grad_effect").show().find('#dcmp_grad_effect');
            jQuery("#dcmp-custom-message-background-colors-gradient").parent().parent().parent().show();
        } else {
            jQuery("#main_dcmp_grad_effect").hide().find('#dcmp_grad_effect');
            jQuery("#dcmp-custom-message-background-colors-gradient").parent().parent().parent().hide();
        }
    }).trigger('change');

    if ('none' === jQuery("#main_dcmp-custom-message-box-border-style option:selected").val()) {
        // To Initially Hide the Border Color Field.
        jQuery("#main_dcmp-custom-message-box-border-colors").hide().find('#dcmp-custom-message-box-border-colors');
    }

    // Show/Hide Icon Color or Custom Icon Option.
    jQuery('input[name="dcmp_icon"]').change(function () {

        let selectedIconOption = '';
        selectedIconOption = jQuery("input[name='dcmp_icon']:checked").val();
        if ('custom_icon_color' === selectedIconOption) {
            jQuery("#main_dcmp_message_icon").show();
            jQuery("#main_dcmp_fa_icon").hide();
            jQuery("#main_dcmp-custom-icon").hide();
        } else if ('custom_icon_color_pro' === selectedIconOption) {
            jQuery("#main_dcmp_message_icon").hide();
            jQuery("#main_dcmp_fa_icon").show();
            jQuery("#main_dcmp-custom-icon").hide();
        } else if ('custom_icon' === selectedIconOption) {
            jQuery("#main_dcmp_message_icon").hide();
            jQuery("#main_dcmp_fa_icon").hide();
            jQuery("#main_dcmp-custom-icon").show();
        } else {
            jQuery("#main_dcmp_message_icon").hide();
            jQuery("#main_dcmp_fa_icon").hide();
            jQuery("#main_dcmp-custom-icon").hide();
        } // Not need this else part, because radio button values is set above initailly --SFT. 
    }).trigger('change');

    jQuery('input[name="dcmp_custom_countdown"]').change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery("#main_dcmp_countdown_type").show().find('#dcmp_countdown_type');
            jQuery("#main_dcmp_countdown_timer_style").show().find('#dcmp_countdown_timer_style');
        } else {
            jQuery("#main_dcmp_countdown_time").hide().find('#dcmp_countdown_time');
            jQuery("#main_dcmp_countdown_expired").hide().find('#dcmp_countdown_expired');
            jQuery("#main_dcmp_countdown_type").hide().find('#dcmp_countdown_type');
            jQuery("#main_dcmp_countdown_timer_style").hide().find('#dcmp_countdown_timer_style');
        }
    }).trigger('change');

    jQuery('#dcmp-custom-message-box-border-style').change(function () {
        if ('none' === this.value) {
            jQuery("#main_dcmp-custom-message-box-border-colors").hide().find('#dcmp-custom-message-box-border-colors');
        } else {
            jQuery("#main_dcmp-custom-message-box-border-colors").show().find('#dcmp-custom-message-box-border-colors');
        }
    });

    // To Initially Hide the Fake Counter Input Field.
    jQuery("#main_dcmp_countdown_time").hide().find('#dcmp_countdown_time');

    // Show/Hide the drowpdown list for Countdown Timer i.e. Schedule Timer or Fake Timer.
    jQuery('input[name="dcmp_countdown_type"]').change(function () {
        let selectedCounterType = '';
        selectedCounterType = jQuery("input[name='dcmp_countdown_type']:checked").val();
        if ('fake_counter' === selectedCounterType) {
            jQuery("#main_dcmp_countdown_time").show().find('#dcmp_countdown_time');
            jQuery("#main_dcmp_countdown_expired").show().find('#dcmp_countdown_expired');
        }
        else {
            jQuery("#main_dcmp_countdown_time").hide().find('#dcmp_countdown_time');
            jQuery("#main_dcmp_countdown_expired").hide().find('#dcmp_countdown_expired');
        }
    }).trigger('change');

    jQuery("#dcmp_control_cart_msg, #dcmp_start_date, #days, #hours, #minutes, #seconds, #dcmp_countdown_expired, #dcmp_custom_message_radius, #dcmp_custom_button_radius, #dcmp_request_files, #update, #dcmp-cart-message-preview").attr('disabled', 'disabled');
    
    jQuery('#static_counter, #custom_icon_color_pro, #custom_icon, #no_icon, #dcmp-custom-message-box-border-style option, #dcmp_grad_effect option, #dcmp_countdown_timer_style option').prop('disabled', true)

    var colorPickerIds = [
        '#dcmp-custom-message-background-colors',
        '#dcmp-custom-message-text-colors',
        '#dcmp-custom-message-background-colors-gradient',
        '#dcmp-custom-icon-colors',
        '#dcmp-custom-message-box-border-colors',
        '#dcmp-custom-button-background-colors',
        '#dcmp-custom-button-text-colors',
        '#dcmp-custom-button-text-colors-on-hover'
    ];
    
    colorPickerIds.forEach(function(colorPickerId) {
        var colorPicker = jQuery(colorPickerId);
        colorPicker.wpColorPicker();
        colorPicker.closest('.wp-picker-container').find('.wp-color-result').unbind('click');
    });

    var dcmpAlertTitle = sft_dcmp_cart.dcmp_free_to_pro_alert_title;
    var dcmpAlertMessage = sft_dcmp_cart.dcmp_free_to_pro_alert_message;
    var dcmpUpgradeNow = sft_dcmp_cart.dcmp_free_to_pro_upgrade;

    jQuery('.dcm-pro-alert, #main_dcmp_control_cart_msg > td > .switch').click(function () {

        Swal.fire({
          icon: 'info',
          title: dcmpAlertTitle,
          showCloseButton: true,
          html: '<h5><b>'+dcmpAlertMessage+'</b></h5><button class="sft-upgrade-now" style="border: none"><a href="https://www.saffiretech.com/dynamic-cart-messages-pro-woocommerce/" target="_blank" class="purchase-pro-link">'+dcmpUpgradeNow+'</a></button>',
          showConfirmButton: false,
        });
    });

});
