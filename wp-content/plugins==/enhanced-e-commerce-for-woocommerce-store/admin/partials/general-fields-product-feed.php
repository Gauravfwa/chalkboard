<?php
$TVC_Admin_Helper = new TVC_Admin_Helper();
$customApiObj = new CustomApi();
$TVCProductSyncHelper = new TVCProductSyncHelper();
$wooCommerceAttributes = array_map("unserialize", array_unique(array_map("serialize", $TVCProductSyncHelper->wooCommerceAttributes())));
$ee_mapped_attrs = unserialize(get_option('ee_prod_mapped_attrs'));
$ee_additional_data = $TVC_Admin_Helper->get_ee_additional_data();
$product_id_prefix = '';
if (isset($ee_additional_data['product_id_prefix']) === TRUE && $ee_additional_data['product_id_prefix'] === TRUE) {
    $product_id_prefix = $ee_additional_data['product_id_prefix'];
}
$gmcAttributes = $TVC_Admin_Helper->get_gmcAttributes();
$category_wrapper_obj = new Tatvic_Category_Wrapper();
$country = $TVC_Admin_Helper->get_woo_country();
$currentCustomerId = $TVC_Admin_Helper->get_currentCustomerId();
$class = "";
$message_p = "";
$validate_pixels = [];
$google_detail = $TVC_Admin_Helper->get_ee_options_data();
$plan_id = 1;
$googleDetail = "";
if (isset($google_detail['setting']) === TRUE) {
    $googleDetail = $google_detail['setting'];
    if (isset($googleDetail->plan_id) === TRUE && !in_array($googleDetail->plan_id, ["1"])) {
        $plan_id = $googleDetail->plan_id;
    }
}
$total_products = (new WP_Query(['post_type' => 'product', 'post_status' => 'publish']))->found_posts;
$data = unserialize(get_option('ee_options'));
if (
    isset($_GET['tab']) === FALSE
    && ((isset($data['google_merchant_id']) && $data['google_merchant_id'] !== ''))
) {
    wp_safe_redirect("admin.php?page=conversios-google-shopping-feed&tab=feed_list");
    exit;
}
$conv_selected_events = unserialize(get_option('conv_selected_events'));
$current_customer_id = $TVC_Admin_Helper->get_currentCustomerId();
$subscription_id = $TVC_Admin_Helper->get_subscriptionId();
if ($subscription_id === FALSE) {
    wp_safe_redirect("admin.php?page=conversios_onboarding");
    exit;
}

$is_show_tracking_method_options = true;
?>
<?php
$channel_not_connected = array(
    "gmc_id" => (isset($data['google_merchant_id']) && $data['google_merchant_id'] != '') ? '' : 'conv-pixel-not-connected',
    "tiktok_bussiness_id" => (isset($data['tiktok_setting']['tiktok_business_id']) && $data['tiktok_setting']['tiktok_business_id'] != '') ? '' : 'tik-tok-not-connected',
);

$channel_video_link = ["gmc_id" => "https://www.youtube.com/watch?v=Ku8iW02Os-w"];

$getCountris = @file_get_contents(ENHANCAD_PLUGIN_DIR."includes/setup/json/countries.json");
$contData = json_decode($getCountris);
$site_url = "admin.php?page=conversios-google-shopping-feed&tab=";
$conv_data = $TVC_Admin_Helper->get_store_data();
?>
<style>
    body {
        max-height: 100%;
        background: #f0f0f1;
    }

    #tvc_popup_box {
        width: 500px;
        overflow: hidden;
        background: #eee;
        box-shadow: 0 0 10px black;
        border-radius: 10px;
        position: absolute;
        top: 30%;
        left: 40%;
        display: none;
    }
    
</style>

<!-- Main container -->
<div class="container-old conv-container conv-setting-container pt-0">    
    <!-- Main row -->
    <div class="row justify-content-center">
        <!-- Main col8 center -->
        <div class="convfixedcontainermid col-8 col-xs-12 m-0 p-0">
            <div class="pt-4 pb-4 conv-heading-box">
                <h3>CHANNEL CONFIGURATION</h3>
                <span>You can configure your add channels for your product feeds</span>
            </div>
            <!-- Google Merchant card Start -->
            <div class="convcard d-flex flex-row p-2 mt-0 rounded-3 shadow-sm">
                <div class="convcard-left conv-pixel-logo">
                    <div class="convcard-logo text-center p-2 pe-3 border-end">
                        <img src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/conv_gmc_logo.png'); ?>" />
                    </div>
                </div>
                <div class="convcard-center p-2 ps-3 col-10">
                    <div class="convcard-title">
                        <h3>Google Merchant Center
                            <span
                                class="badge rounded-pill conv-badge <?php echo !empty($channel_not_connected['gmc_id']) ? "conv-badge-yellow" : "conv-badge-green"; ?>">
                                <?php echo !empty($channel_not_connected['gmc_id']) ? "Not Connected" : "Connected"; ?>
                            </span>
                        </h3>
                        <?php if (isset($data['google_merchant_id']) === TRUE && $data['google_merchant_id'] !== '') { ?>
                            <span>
                                Google Merchant Center Account -
                                <?php echo esc_html($data['google_merchant_id']) ?>
                            </span>
                        <?php } ?>

                        <hr>
                        <div class="d-flex">
                            <span>
                                How to connect your Google Merchant Center Account
                                <a class="conv-link-blue conv-watch-video" href="https://www.youtube.com/watch?v=Ku8iW02Os-w"
                                    target="_blank">
                                    Watch here
                                    <span class="material-symbols-outlined align-middle">play_circle_outline</span>
                                </a>
                            </span>
                        </div>

                        <div class="d-flex mt-3">
                            <span>
                                Benefits of integrating Google Merchant Center Account
                                <a class="conv-link-blue conv-watch-video"
                                    href="https://www.conversios.io/docs/benefits-of-product-sync-to-google-merchant-center/?utm_source=gmc_inapp&utm_medium=resource_center_list&utm_campaign=resource_center"
                                    target="_blank">
                                    Click here
                                    <span class="material-symbols-outlined align-middle">open_in_new_outline</span>
                                </a>
                            </span>
                        </div>

                    </div>
                </div>
                <div class="convcard-right ms-auto">
                    <a href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&subpage="gmcsettings"'); ?>"
                        class="h-100 rounded-end d-flex justify-content-center convcard-right-arrow link-dark firstStep">
                        <span class="material-symbols-outlined align-self-center">chevron_right</span>
                    </a>
                </div>
            </div>
            <!-- TikTok Business Account Start -->            
            <div class="convcard d-flex flex-row p-2 mt-4 rounded-3 shadow-sm">
                <div class="convcard-left conv-pixel-logo">
                    <div class="convcard-logo text-center p-2 pe-3 border-end">
                        <img
                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/conv_tiktok_logo.png'); ?>" />
                    </div>
                </div>
                <div class="convcard-center p-2 ps-3 col-10">
                    <div class="convcard-title">
                        <h3>TikTok Business Account
                            <span
                                class="badge rounded-pill conv-badge <?php echo !empty($channel_not_connected['tiktok_bussiness_id']) ? "conv-badge-yellow" : "conv-badge-green"; ?>">
                                <?php echo !empty($channel_not_connected['tiktok_bussiness_id']) ? "Not Connected" : "Connected"; ?>
                            </span>
                        </h3>                      
                        <?php if (isset($data['tiktok_setting']['tiktok_business_id'] ) && $data['tiktok_setting']['tiktok_business_id']  != '') { ?>
                            <span>
                                TikTok Business Account -
                                <?php echo $data['tiktok_setting']['tiktok_business_id'] ?>
                            </span>
                        <?php } ?>
                        <hr>
                        <!-- <div class="d-flex">
                            <span>
                                How to connect your Tiktok business account
                                <a class="conv-link-blue conv-watch-video" href="#">
                                    Watch here
                                    <span class="material-symbols-outlined align-middle">play_circle_outline</span>
                                </a>
                            </span>
                        </div> -->

                        <div class="d-flex mt-3">
                            <span>
                            <?php esc_html_e("Benefits and how to integrate Tiktok Business Account","enhanced-e-commerce-for-woocommerce-store") ?>
                                <a class="conv-link-blue conv-watch-video" href="https://www.conversios.io/docs/how-to-create-product-feed-to-your-tik-tok-catalog/" target="_blank">
                                    Click here
                                    <span class="material-symbols-outlined align-middle">open_in_new_outline</span>
                                </a>
                            </span>
                        </div>

                    </div>
                </div>
                <div class="convcard-right ms-auto">                    
                    <a href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&subpage="tiktokBusinessSettings"'); ?>"
                        class="h-100 rounded-end d-flex justify-content-center convcard-right-arrow link-dark secondStep">
                        <span class="material-symbols-outlined align-self-center">chevron_right</span>
                    </a>
                </div>
            </div>
            <!-- TikTok Business Account End -->
            <!--Super Feed Start --->
            <?php
                $tvc_admin_db_helper = new TVC_Admin_DB_Helper();
                $where_super = "is_super_feed = '" . esc_sql(1) . "'";
                $row_count_super = $tvc_admin_db_helper->tvc_check_row('ee_product_feed', $where_super);
                if($row_count_super == 0) {
            ?>
            <div id="loadingbar_blue_modal" class="progress-materializecss ps-2 pe-2 d-none" style="width:100%; top:20px;">
                    <div class="indeterminate"></div>
                </div>
            <div class="convcard d-flex flex-row mt-4 rounded-3 shadow-sm">  
                
                <div class="convcard-center p-2 ps-3 col-10">
                
                    <div class="convcard-title">
                        <p class="mt-2 ms-5">Your
                            <?php echo $total_products ?> products are just a click away to sync by our <b>Super AI Feed</b> fetaure
                        </p>
                    </div>
                </div>
                <div class="convcard-right d-flex" style="margin-top:13px;">
                <?php $desc = !empty($channel_not_connected['gmc_id']) === TRUE ? '<span class="text-danger">Google Merchant Center Required</span>' : ''; ?>
                <span class="float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                    title="<?php echo esc_attr($desc) ?>">
                    <button class="btn btn-primary fs-12 <?php echo !empty($channel_not_connected['gmc_id']) === TRUE ? "" : "createSuperAIFeed"; ?>" 
                    <?php echo !empty($channel_not_connected['gmc_id']) === TRUE ? "disabled" : ""; ?>>Sync Now</button>
                </span>
                </div>
            </div>
            <!--Super Feed End --->
            <?php
                }
            ?>
        </div>
    </div> 
    <div class="container-old conv-container conv-setting-container pt-0" id="attributeMapping">
        <div class="row justify-content-center" >
            <!-- Main col8 center -->
            <div class="convfixedcontainermid col-8 col-xs-12 m-0 p-0">
                <!-- Google Merchant card End -->
                <div class="pt-4 pb-4 conv-heading-box">
                    <h3>ATTRIBUTE MAPPING</h3>
                    <span>At Conversios, we provide an automatic mapping feature that enables you to align the
                        categories
                        and attributes of your WooCommerce products with Conversios categories and attributes. This
                        mapping
                        ensures that your product categories and attributes seamlessly correspond to the categories
                        and
                        attributes of the selected channels.</span>
                </div>
                <!-- Product Mapping card Start -->
                <div id="loadingbar_blue" class="progress-materializecss d-none ps-2 pe-2">
                    <div class="indeterminate"></div>
                </div>
                <div class="convcard flex-row p-2 mt-0 rounded-3 shadow-sm container-fluid font-style" style="height:700px">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link-conv active" id="pills-home-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                aria-selected="true"><?php esc_html_e("Attribute Mapping", "enhanced-e-commerce-for-woocommerce-store") ?></button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link-conv" id="pills-profile-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                                aria-selected="false"><?php esc_html_e("Category Mapping", "enhanced-e-commerce-for-woocommerce-store") ?></button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                            aria-labelledby="pills-home-tab">
                            <div class="col-12 row conv-light-grey-bg m-0 p-0" style="height:48px;">
                                <div class="col-6 pt-2">
                                    <span class="ps-2">
                                        <img
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/conversios_logo.png'); ?>" />
                                        <?php esc_html_e("Conversios Product Attribute", "enhanced-e-commerce-for-woocommerce-store") ?></span>
                                </div>
                                <div class="col-6 pt-2 ps-0">
                                    <span class="ps-0">
                                        <img
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/woocommerce_logo.png'); ?>" />
                                        <?php esc_html_e("WooCommerce Product Attribute", "enhanced-e-commerce-for-woocommerce-store") ?></span>
                                </div>
                            </div>
                            <div class="col-12 row bg-white m-0 p-0 mb-3">
                                <div class="col-12  attributeDiv" style="overflow-y: scroll; max-height:550px;">
                                    <form id="attribute_mapping" class="row">
                                        <?php foreach ($gmcAttributes as $key => $attribute) {
                                            $sel_val = ""; ?>
                                            <div class="col-6 mt-2">
                                                <span class="ps-3 fw-400 text-color fs-12">
                                                    <?php echo esc_attr($attribute["field"]) . " " . (isset($attribute["required"]) === TRUE && esc_attr($attribute["required"]) === '1' ? '<span class="text-color fs-6"> *</span>' : ""); ?>
                                                    <span class="material-symbols-outlined fs-6" data-bs-toggle="tooltip"
                                                        data-bs-placement="right"
                                                        title="<?php echo (isset($attribute['desc']) === TRUE ? esc_attr($attribute['desc']) : ''); ?>">
                                                        info
                                                    </span>
                                                </span>
                                            </div>
                                            <div class="col-6 mt-2">
                                                <?php
                                                $ee_select_option = $TVC_Admin_Helper->add_additional_option_in_tvc_select($wooCommerceAttributes, $attribute["field"]);
                                                $require = FALSE;
                                                if (isset($attribute['required']) === TRUE) {
                                                    $require = TRUE;
                                                }
                                                $sel_val_def = "";
                                                if (isset($attribute['wAttribute']) === TRUE) {
                                                    $sel_val_def = $attribute['wAttribute'];
                                                }
                                                if ($attribute["field"] === 'link') {
                                                    "product link";
                                                } else if ($attribute["field"] === 'shipping') {
                                                    $sel_val = esc_attr($sel_val_def);
                                                    if (isset($ee_mapped_attrs[$attribute["field"]]) === TRUE) {
                                                        $sel_val = esc_attr($ee_mapped_attrs[$attribute["field"]]);
                                                    }

                                                    $TVC_Admin_Helper->tvc_text($attribute["field"], 'number', '', esc_html__('Add shipping flat rate', 'product-feed-manager-for-woocommerce'), $sel_val, $require);
                                                } else if ($attribute["field"] === 'tax') {
                                                    $sel_val = esc_attr($sel_val_def);
                                                    if (isset($ee_mapped_attrs[$attribute["field"]]) === TRUE) {
                                                        $sel_val = esc_attr($ee_mapped_attrs[$attribute["field"]]);
                                                    }

                                                    $TVC_Admin_Helper->tvc_text($attribute["field"], 'number', '', 'Add TAX flat (%)', $sel_val, $require);
                                                } else if ($attribute["field"] === 'content_language') {
                                                    $TVC_Admin_Helper->tvc_language_select($attribute["field"], 'content_language', esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), 'en', $require);
                                                } else if ($attribute["field"] === 'target_country') {
                                                    $TVC_Admin_Helper->tvc_countries_select($attribute["field"], 'target_country', esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), $require);
                                                } else {
                                                    if (isset($attribute['fixed_options']) === TRUE && $attribute['fixed_options'] !== "") {
                                                        $ee_select_option_t = explode(",", $attribute['fixed_options']);
                                                        $ee_select_option = [];
                                                        foreach ($ee_select_option_t as $o_val) {
                                                            $ee_select_option[]['field'] = esc_attr($o_val);
                                                        }

                                                        $sel_val = $sel_val_def;
                                                        $TVC_Admin_Helper->tvc_select($attribute["field"], $attribute["field"], esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), $sel_val, $require, $ee_select_option);
                                                    } else {
                                                        $sel_val = esc_attr($sel_val_def);
                                                        if (isset($ee_mapped_attrs[$attribute["field"]]) === TRUE) {
                                                            $sel_val = esc_attr($ee_mapped_attrs[$attribute["field"]]);
                                                        }
                                                        $TVC_Admin_Helper->tvc_select($attribute["field"], $attribute["field"], esc_html__('Please Select Attribute', 'product-feed-manager-for-woocommerce'), $sel_val, $require, $ee_select_option);
                                                    }
                                                } //end attribute if
                                                ?>
                                            </div>

                                        <?php } //end gmcAttributes foreach
                                        ?>
                                        <div class="row add_additional_attr_div m-0 p-0" >
                                            <div class="add_additional_attr_div mt-2" style="display: flex; justify-content: start">
                                                <button type="button" class="fs-12 btn btn-soft-primary add_additional_attr" title="Add Attribute" data-bs-toggle="modal" data-bs-target="#convUptoProModal"> Add Attributes
                                                </button>                                                    
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="button" id="attr_mapping_save"
                                                class="btn btn-soft-primary float-end mt-2 ps-4 pe-4">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                            <div class="col-12 row conv-light-grey-bg m-0 p-0" style="height:48px;">
                                <div class="col-6 pt-2">
                                    <span class="ps-2 fw-normal">
                                        <img
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/woocommerce_logo.png'); ?>" />
                                        <?php esc_html_e("WooCommerce Product Category", "enhanced-e-commerce-for-woocommerce-store") ?></span>
                                </div>
                                <div class="col-6 pt-2 ps-0">
                                    <span class="ps-1 fw-normal">
                                        <img
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/conversios_logo.png'); ?>" />
                                        <?php esc_html_e("Conversios Product Category", "enhanced-e-commerce-for-woocommerce-store") ?></span>
                                </div>
                            </div>
                            <div class="row bg-white m-0 p-0 mb-3">
                                <div class="col-12 categoryDiv" style="overflow-y: scroll; max-height:550px;">
                                    <form id="category_mapping">
                                        <?php echo $category_wrapper_obj->category_table_content(0, 0, 'mapping'); ?>
                                        <div class="col-12">
                                            <button type="button" id="cat_mapping_save"
                                                class="btn btn-soft-primary float-end mt-2 ps-4 pe-4">Save</button>
                                        </div>
                                        <input type="hidden" name="selectedCategory" id="selectedCategory">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div class="col-xl-4 col-lg-4 col-md-12 col-12">
                <div style="margin-right: auto; width: 90%">
                    <div class="convcard d-flex flex-row p-2 rounded-3 shadow-sm"
                        style="max-width: 545px; margin-top: 149px; margin-left:2px;">
                        <fieldset class="border p-3 mt-2" style="border-radius:8px;">
                            <legend class="float-none w-auto px-3 fs-7">
                                <?php //esc_html_e("Benefits Of Category Mapping", "enhanced-e-commerce-for-woocommerce-store") ?>
                            </legend>
                            <div class="control-group fw-400 text-color">
                                <p>
                                    <?php //esc_html_e("Benefits of product attributes and category mapping for a product feed in Google Merchant Center:", "enhanced-e-commerce-for-woocommerce-store") ?>
                                    <br />
                                    <?php //esc_html_e("1. Accurate and Structured Data: Mapping attributes and categories ensures organized and consistent product data, leading to better categorization and relevance.", "enhanced-e-commerce-for-woocommerce-store") ?>
                                    <br />
                                    <?php //esc_html_e("2. Improved Visibility: Mapping helps your products appear in relevant search results, increasing visibility and driving targeted traffic.", "enhanced-e-commerce-for-woocommerce-store") ?>
                                    <br />
                                    <?php //esc_html_e("3. Enhanced User Experience: Clear and detailed product information improves the overall shopping experience for users.", "enhanced-e-commerce-for-woocommerce-store") ?>
                                    <br />
                                    <?php //esc_html_e("4. Better Targeting and Ad Performance: Accurate mapping enables precise targeting, resulting in improved ad performance and higher conversion rates.", "enhanced-e-commerce-for-woocommerce-store") ?>
                                    <br />
                                    <?php //esc_html_e("5. Simplified Updates and Maintenance: Mapping facilitates easier updates and maintenance of your product feed.", "enhanced-e-commerce-for-woocommerce-store") ?>
                                    <br />
                                    <?php //esc_html_e("6. Reduced Errors and Disapprovals: Proper mapping minimizes the risk of errors or disapprovals in product listings, ensuring compliance with Google's guidelines.", "enhanced-e-commerce-for-woocommerce-store") ?>
                                </p>
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
    <!-- Product Mapping card End -->
    <div class="container-old conv-container conv-setting-container pt-0">
        <div class="row justify-content-center" >
        <!-- Main col8 center -->
            <div class="convfixedcontainermid col-8 col-xs-12 m-0 p-0">
                <!-- Blue upgrade to pro -->
                <div class="convcard conv-green-grad-bg rounded-3 d-flex flex-row p-3 mt-4 shadow-sm">
                    <div class="convcard-blue-left align-self-center p-2 bd-highlight">
                        <h3 class="text-light mb-3">
                            <?php esc_html_e("Upgrade your Plan to get pro benefits", "enhanced-e-commerce-for-woocommerce-store"); ?>
                        </h3>
                        <span class="text-light">
                            <ul class="conv-green-banner-list ps-4">
                                <li>
                                    <?php esc_html_e("Take control, boost speed. Automate your Google Tag Manager.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </li>
                                <li>
                                    <?php esc_html_e("Maximize campaigns with Google Ads Conversion integration.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </li>
                                <li>
                                    <?php esc_html_e("Quick and Easy install of Facebook Conversions API to drive sales via Facebook Ads.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </li>
                                <li>
                                    <?php esc_html_e("Sync unlimited product feeds with Content API and more.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </li>
                                <li>
                                    <?php esc_html_e("Make data-driven decisions. Scale your ecommerce business with our reporting dashboard.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </li>
                                <li>
                                    <?php esc_html_e("Free website audit, dedicated success manager, priority slack support.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </li>
                            </ul>
                        </span>
                        <span class="d-flex">
                            <a style="padding:8px 24px 8px 24px;" class="btn conv-yellow-bg mt-4 btn-lg"
                                href="<?php echo esc_attr($TVC_Admin_Helper->get_conv_pro_link_adv("banner", "channel_config", "", "linkonly")) ?>"
                                target="_blank">Upgrade Now</a>
                        </span>
                    </div>
                    <div class="convcard-blue-right align-self-center p-2 bd-highlight mx-auto">
                        <img
                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/freetopaid_banner_img.png'); ?>" />
                    </div>
                </div>
                <!-- Blue upgrade to pro End -->
            </div>
        </div>
    </div>
</div>
<!-- Main col8 center -->
<!-- Main container End -->
<!-- Upgrade to PRO modal -->
<div class="modal fade" id="convUptoProModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img style="width:184px;"
                    src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/conv_modal_img_getpro.png'); ?>">;
                <h3 class="fw-normal pt-3">Upgrade To Pro</h3>
                <span class="mb-1">Checkout our Premium Plans to unlock all the features to <br> scale your
                    business</span>
            </div>
            <div class="modal-footer border-0 pb-4 mb-1">
                <a class="btn conv-yellow-bg m-auto" href="https://www.conversios.io/wordpress/all-in-one-google-analytics-pixels-and-product-feed-manager-for-woocommerce-pricing/?utm_source=in_app&utm_medium=tiktok_banner&utm_campaign=pricing" target="_blank">Upgrade Now</a>
            </div>
        </div>
    </div>
</div>
<!-- Upgrade to PRO modal End -->
<!-------------------------CTA super_feed_modal Start ---------------------------------->
<div class="modal fade" id="conv_super_feed_modal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">  
            <div class="modal-header justify-content-end connection-header border-0 pb-0">   
                <button type="button" style="margin: 0px; padding:0px;" class="btn-close close_feed_modal" aria-label="Close"></button>
            </div>                   
            <div class="modal-body text-center p-4">
                <div class="connected-content">
                    <h2>
                        <?php esc_html_e("Congratulations!", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </h2>                                    
                    <p class="my-3 syncSuccessMessage" style="font-size: 20px;">
                        
                    </p>
                    <p style="font-size: 20px;"><?php esc_html_e("And that's not all.", "enhanced-e-commerce-for-woocommerce-store"); ?></p>
                    <h4 class="mb-3">
                        <?php esc_html_e("Embrace these amazing features:", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </h4>
                </div>
                <div>
                    <div class="attributemapping-box">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-6">
                                <div class="attribute-box mb-3">
                                    <div class="attribute-icon">
                                        <img style="width:35px;filter: drop-shadow(3px 3px 3px #ccc);"
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/Campaign-Management.svg'); ?>">
                                    </div>
                                    <div class="attribute-content para">
                                        <h3>
                                            <?php esc_html_e("Effortless Feed Management:", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                        </h3>
                                        <p>
                                            <?php esc_html_e("Generate custom feeds, apply filters, and ensure up-to-date product data with auto-sync for targeted regions and successful promotions.", "enhanced-e-commerce-for-woocommerce-store"); ?>

                                        </p>
                                        <div class="attribute-btn">
                                            <a href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&tab=feed_list'); ?>" class="btn btn-dark common-btn">Manage Feeds</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-6">
                                <div class="attribute-box mb-3">
                                    <div class="attribute-icon">
                                        <img style="width:35px;filter: drop-shadow(3px 3px 3px #ccc);"
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/Integrations.svg'); ?>">
                                    </div>
                                    <div class="attribute-content para">
                                        <h3>
                                            <?php esc_html_e("Seamless Integration:", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                        </h3>
                                        <p>
                                            <?php esc_html_e("Connect more channels to sync your product data. Map your WooCommerce product attributes and categories for an optimized product feed process.", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                        </p>
                                        <div class="attribute-btn">
                                            <a href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&tab=gaa_config_page'); ?>" class="btn btn-dark common-btn">Configure Now</a>
                                        </div>
                                    </div>
                                </div>
                            </div>                                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--------------------------------super_feed_modal End -------------------------------------->
<!-- Create Feed Modal -->
<div class="modal fade" id="convCreateFeedModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ">
            <form id="feedForm" onfocus="this.className='focused'">
                <div id="loadingbar_blue_modal" class="progress-materializecss d-none ps-2 pe-2" style="width:98%">
                    <div class="indeterminate"></div>
                </div>
                <div class="modal-header bg-light p-2 ps-4">
                    <h5 class="modal-title fs-16 fw-500" id="feedType">
                        <?php esc_html_e("Create New Feed", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        onclick="jQuery('#feedForm')[0].reset()"></button>
                </div>
                <div class="modal-body ps-4 pt-0">
                    <div class="mb-4 feed_name">
                        <label for="feed_name" class="col-form-label text-dark fs-14 fw-500">
                            <?php esc_html_e("Feed Name", "enhanced-e-commerce-for-woocommerce-store"); ?>
                        </label>
                        <span class="material-symbols-outlined fs-6" data-bs-toggle="tooltip" data-bs-placement="right"
                            title="Add a name to your feed for your reference, for example, 'April end-of-season sales' or 'Black Friday sales for the USA'.">
                            info
                        </span>
                        <input type="text" class="form-control fs-14" name="feedName" id="feedName"
                            placeholder="e.g. New Summer Collection">
                    </div>
                    <div class="mb-2 row">
                        <div class="col-5">
                            <label for="auto_sync" class="col-form-label text-dark fs-14 fw-500">
                                <?php esc_html_e("Auto Sync", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <span class="material-symbols-outlined fs-6" data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="Turn on this feature to schedule an automated product feed to keep your products up to date with the changes made in the products. You can come and change this any time.">
                                info
                            </span>
                        </div>
                        <div class="form-check form-switch col-7 mt-0 fs-5">
                            <input class="form-check-input" type="checkbox" name="autoSync" id="autoSync" checked>
                        </div>
                    </div>                    
                    <div class="mb-3 row">
                        <div class="col-5">
                            <label for="auto_sync_interval" class="col-form-label text-dark fs-14 fw-500">
                                <?php esc_html_e("Auto Sync Interval", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <span class="material-symbols-outlined fs-6" data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="Set the number of days to schedule the next auto-sync for the products in this feed. You can come and change this any time.">
                                info
                            </span>
                        </div>
                        <div class="col-7">
                            <input type="text" class="form-control-sm fs-14 " readonly="readonly" name="autoSyncIntvl" id="autoSyncIntvl" size="3" min="1"
                                onkeypress="return ( event.charCode === 8 || event.charCode === 0 || event.charCode === 13 || event.charCode === 96) ? null : event.charCode >= 48 && event.charCode <= 57"
                                oninput="removeZero();"
                                value="25">
                            <label for="" class="col-form-label fs-14 fw-400">
                                <?php esc_html_e("Days", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <span>
                               <a target="_blank" href="https://www.conversios.io/wordpress/all-in-one-google-analytics-pixels-and-product-feed-manager-for-woocommerce-pricing/?utm_source=in_app&utm_medium=tiktok_banner&utm_campaign=pricing"><b> Upgrade To Pro</b></a>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <div class="col-5">
                            <label for="target_country" class="col-form-label text-dark fs-14 fw-500" name="">
                                <?php esc_html_e("Target Country", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <span class="material-symbols-outlined fs-6" data-bs-toggle="tooltip"
                                data-bs-placement="right"
                                title="Specify the target country for your product feed. Select the country where you intend to promote and sell your products.">
                                info
                            </span>
                        </div>
                        <div class="col-7">
                            <select class="select2 form-select form-select-sm mb-3" aria-label="form-select-sm example" style="width: 100%" name="target_country" id="target_country">
                            <option value="">Select Country</option>
                        <?php
                        $selecetdCountry = $conv_data['user_country'];
                        foreach ($contData as $key => $value) {
                            ?>
                            <option value="<?php echo esc_attr($value->code) ?>" <?php echo $selecetdCountry === $value->code ? 'selected = "selecetd"' : '' ?>><?php echo esc_html($value->name) ?></option>"
                            <?php
                        }

                        ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="auto_sync_interval" class="col-form-label text-dark fs-14 fw-500">
                            <?php esc_html_e("Select Channel", "enhanced-e-commerce-for-woocommerce-store"); ?>
                        </label>
                        <span class="material-symbols-outlined fs-6" data-bs-toggle="tooltip" data-bs-placement="right"
                            title="Below is the list of channels that you have linked for product feed. Please note you will not be able to make any changes in the selected channels once product feed process is done.">
                            info
                        </span>
                    </div>
                    <?php if(isset($data['google_merchant_id']) === TRUE && $data['google_merchant_id'] !== '') { ?>
                    <div class="mb-3">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input check-height fs-14 errorChannel" type="checkbox"
                                value="" id="gmc_id"
                                name="gmc_id"  <?php echo isset($data['google_merchant_id']) === TRUE ? 'checked' : '' ?>>
                            <label for="" class="col-form-label fs-14 pt-0 text-dark fw-500">
                                <?php esc_html_e("Google Merchant Center Account :", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <label class="col-form-label fs-14 pt-0 fw-400 modal_google_merchant_center_id">
                            <?php echo isset($data['google_merchant_id']) === TRUE ? $data['google_merchant_id'] : '' ?>
                            </label>
                        </div>
                    </div>
                    <?php }else { ?>
                    <div class="mb-3">
                        <div class="form-check form-check-custom">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                        onclick="jQuery('#feedForm')[0].reset()">Connect Google Merchant Centre</button>                        
                        </div>
                    </div>
                    <?php   }?>
                    
                </div>
                <div class="modal-footer p-2">
                    <input type="hidden" id="edit" name="edit"> 
                    <input type="hidden" id="is_mapping_update" name="is_mapping_update" value="">
                    <input type="hidden" id="last_sync_date" name="last_sync_date" value="">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal"
                        onclick="jQuery('#feedForm')[0].reset()">
                        <?php esc_html_e("Cancel", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </button>
                    <button type="button" class="btn btn-soft-primary btn-sm" <?php echo isset($data['google_merchant_id']) === TRUE && $data['google_merchant_id'] !== ''? '' : 'disabled' ?> <?php echo isset($data['google_merchant_id']) === TRUE && $data['google_merchant_id'] !== ''? 'id="submitFeed"' : 'id=""' ?>>
                        <?php esc_html_e("Create and Next", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Suucess Modal---->
<div class="modal fade" id="conv_save_success_modal_cta" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header connection-header border-0 pb-0">
                <div class="connection-box">
                    <div class="items">
                        <img style="width:35px;"
                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/popup_woocommerce _logo.png'); ?>">
                        <span>Woo Commerce</span>
                    </div>
                    <div class="items">
                        <span class="material-symbols-outlined text-primary">
                            arrow_forward
                        </span>
                    </div>
                    <div class="items">
                        <img style="width:35px;"
                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/popup_mapping_logo.png'); ?>">
                        <span>Conversios Product Attributes</span>
                    </div>
                    
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="connected-content">
                    <h4>Successfully Connected</h4>
                    <p class="my-3"><?php esc_html_e("Congratulations on successfully mapping your product categories and attributes! By
                        ensuring accurate classification and detailed product information, you've enhanced the
                        discoverability and relevance of your products, providing a better shopping experience for
                        customers.", "enhanced-e-commerce-for-woocommerce-store") ?> </p>
                </div>
                <div>
                    <div class="attributemapping-box">
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-12">
                                <div class="attribute-box mb-3">
                                    <div class="attribute-icon">
                                        <img style="width:35px;"
                                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/Manage_feed.png'); ?>">
                                    </div>
                                    <div class="attribute-content para">
                                        <h3><?php esc_html_e("Manage Feeds", "enhanced-e-commerce-for-woocommerce-store") ?></h3>
                                        <p>
                                        <?php esc_html_e("A feed management tool offers benefits such as centralized product updates,
                                            optimized product listings, and improved data quality, ultimately enhancing
                                            the efficiency and effectiveness of your product feed management process.", "enhanced-e-commerce-for-woocommerce-store") ?>

                                        </p>
                                        <div class="attribute-btn">
                                        <a href="#"
                                                class="btn btn-dark common-btn createFeed">Create Feed</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$path = ENHANCAD_PLUGIN_DIR . 'includes/setup/json/category.json';
$str = file_get_contents($path);
?>
<script>
    var cat_json = <?php echo $str ?>;
    jQuery(document).on('click', '.con_edit_text', function (event) {
        event.preventDefault();
        jQuery("#con_conversion_label").removeAttr('disabled');
    });

    jQuery(document).on('click', '.con_conversion_label', function (event) {
        event.preventDefault();
        var data = {
            action: "con_get_conversion_list",
            TVCNonce: "<?php echo esc_html(wp_create_nonce('con_get_conversion_list-nonce')); ?>"
        };
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: data,
            beforeSend: function () {
                tvc_helper.loaderSection(true);
            },
            success: function (response) {
                if (response == 0) {
                    jQuery('.google_conversion_label_message').show();
                    jQuery('.google_ads_conversion_sec_static').show();
                    jQuery('#con_conversion_label').hide();
                    tvc_helper.loaderSection(false);
                    return;
                } else {
                    var response;
                    var con_select = '<option value="">Please select conversion label</option>';
                    for (var key in response) {
                        con_select += '<option value="' + response[key] + '">' + response[key] + '</option>';
                    }
                    document.getElementById('con_conversion_label').innerHTML = con_select;
                    jQuery("#con_conversion_label").removeClass("tvc-hide");
                    jQuery('.con_conversion_label').hide();
                    tvc_helper.loaderSection(false);
                }
            }

        });
    });


    jQuery(document).ready(function () {        
        jQuery('.select2').select2();
        jQuery(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
        jQuery('.target_country').select2();
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        jQuery(document).on('submit', '#ee_plugin_form', function (e) {
            if (jQuery('.google_ads_conversion_sec_static').is(":visible") && jQuery("#ee_conversio_send_to_static").val() != "") {
                var inpval = jQuery("#ee_conversio_send_to_static").val();
                var regex = /^AW-+[0-9{5,}]+[\/]+[a-zA-Z0-9{5,}]/;
                if (regex.test(inpval)) {
                } else {
                    e.preventDefault();
                    jQuery("#ee_conversio_send_to_static_msg").show();
                }
            }
        });

        jQuery(document).on('click', '.con_faq_title', function (event) {
            let faq_id = jQuery(this).attr("data-id");
            jQuery(this).toggleClass('active');
            jQuery('#' + faq_id).toggleClass('active');
        });

        jQuery(document).on('change', '.css-selector', function (event) {
            if (jQuery(this).val() == "custom") {
                jQuery(this).next().addClass("tvc-hide");
                jQuery(this).next().next().removeClass("tvc-hide");
            } else {
                jQuery(this).next().next().addClass("tvc-hide");
                jQuery(this).next().removeClass("tvc-hide");
            }
        });

        jQuery('input[type=radio][name=conv_show_badge]').change(function () {
            if (jQuery(this).val() == 'yes') {
                jQuery(".only-for-badgeshow").show();
            } else if (jQuery(this).val() == 'no') {
                jQuery(".only-for-badgeshow").hide();
            }
        });

        //pixel validation
        jQuery(document).ready(function () {
            jQuery("#fb_pixel_id,#microsoft_ads_pixel_id,#twitter_ads_pixel_id,#pinterest_ads_pixel_id,#snapchat_ads_pixel_id,#tiKtok_ads_pixel_id").blur(function () {
                var ele_id = this.id;
                var ele_val = jQuery(this).val();
                var regex_arr = {
                    fb_pixel_id: new RegExp(/^\d{14,16}$/m),
                    microsoft_ads_pixel_id: new RegExp(/^\d{7,9}$/m),
                    twitter_ads_pixel_id: new RegExp(/^[a-z0-9]{5,7}$/m),
                    pinterest_ads_pixel_id: new RegExp(/^\d{13}$/m),
                    snapchat_ads_pixel_id: new RegExp(/^[a-z0-9\-]*$/m),
                    tiKtok_ads_pixel_id: new RegExp(/^[A-Z0-9]{20,20}$/m)
                };
                if (ele_val.match(regex_arr[ele_id]) || ele_val === "") {
                    jQuery(this).removeClass("redinvalid");
                } else {
                    jQuery(this).addClass("redinvalid");
                }
                if (jQuery("#fb_pixel_id,#microsoft_ads_pixel_id,#twitter_ads_pixel_id,#pinterest_ads_pixel_id,#snapchat_ads_pixel_id,#tiKtok_ads_pixel_id").hasClass("redinvalid")) {
                    jQuery('#ee_submit_plugin').attr('disabled', true);
                    jQuery('#ee_submit_plugin').addClass('convdisabled');
                } else {
                    jQuery("#ee_submit_plugin").prop("disabled", false);
                    jQuery('#ee_submit_plugin').removeClass('convdisabled');
                }
            });
        });

        /*facebook*/
        jQuery("#fb_pixel_id").keypress(function (evt) {
            var theEvent = evt || window.event;
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
            var regex = /[-\d\.]/; // dowolna liczba (+- ,.) :)
            var objRegex = /^-?\d*[\.]?\d*$/;
            var val = jQuery(evt.target).val();
            if (!regex.test(key) || !objRegex.test(val + key) ||
                !theEvent.keyCode == 46 || !theEvent.keyCode == 8) {
                theEvent.returnValue = false;
                if (theEvent.preventDefault) theEvent.preventDefault();
            };
        });

        jQuery(document).on('click', '#tvc_google_connect_active_licence_close', function (event) {
            jQuery('#tvc_google_connect_active_licence').modal('hide');
        });
        jQuery(document).on('click', '.tvc_licence_key_change', function (event) {
            jQuery(".tvc_licence_key_change_wapper").slideUp(500);
            jQuery(".tvc_licence_key_wapper").slideDown(700);
        });

    });

    jQuery(function () {
        jQuery("#google_ads_conversion_tracking").click(function () {
            if (jQuery("#google_ads_conversion_tracking").is(":checked")) {
                jQuery('#google_ads_conversion_sec :input').removeAttr('disabled');
            } else {
                //To disable all input elements within div use the following code:  
                jQuery('#google_ads_conversion_sec :input').attr('disabled', 'disabled');
            }
        });
    });

    jQuery('#google_ads_conversion_tracking').click(function () {
        if (!this.checked) {
            jQuery("#ga_EC").prop("checked", false);
        } else {
            jQuery("#ga_EC").prop("checked", true);
        }
    });

    /****************************** Mapping value is Numeric Start ************************************************************************************/
    jQuery(document).on('keydown', 'input[name="shipping"]', function (event) {
        if (event.shiftKey == true) {
            event.preventDefault();
        }
        if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if (jQuery(this).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault();
    })
    jQuery(document).on('keydown', 'input[name="tax"]', function () {
        if (event.shiftKey == true) {
            event.preventDefault();
        }
        if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {

        } else {
            event.preventDefault();
        }

        if (jQuery(this).val().indexOf('.') !== -1 && event.keyCode == 190)
            event.preventDefault();
    })
    /****************************** Mapping value is Numeric End ************************************************************************************/
    /**************************Append All conversios categories to select tag *********************************************************/
    jQuery(document).on('click', '.select2-selection.select2-selection--single', function (e) {
        var iscatMapped = jQuery(this).parent().parent().prev().attr('iscategory')
        var selectId = jQuery(this).parent().parent().prev().attr('id')
        var toAppend = '';
        if (iscatMapped == 'false') {
            jQuery(this).parent().parent().prev().attr('iscategory', 'true')
            jQuery.each(cat_json, function (i, o) {
                toAppend += '<option value="' + o.id + '">' + o.name + '</option>';
            });
            jQuery('#' + selectId).append(toAppend);
            jQuery('#' + selectId).select2();
            jQuery('#' + selectId).select2('open');
        }
    });
    /**************************Append All conversios categories to select tag end*********************************************************/
    jQuery(document).on("click", ".change_prodct_feed_cat", function () {
        jQuery(this).hide();
        var feed_select_cat_id = jQuery(this).attr("data-id");
        var woo_cat_id = jQuery(this).attr("data-cat-id");
        jQuery("#category-" + woo_cat_id).val("0");
        jQuery("#category-name-" + woo_cat_id).val("");
        jQuery("#label-" + feed_select_cat_id).hide();
        jQuery("#" + feed_select_cat_id).css('width', '100%');
        jQuery("#" + feed_select_cat_id).addClass('select2');
        jQuery("#" + feed_select_cat_id).slideDown();
        jQuery('.select2').select2();
    });
    /***********************Save Attribute Mapping Start **************************************************************************/
    jQuery(document).on("click", "#attr_mapping_save", function () {
        let ee_data = jQuery("#attribute_mapping").find("input[value!=''], select:not(:empty), input[type='number']").serialize();
        var data = {
            action: "save_attribute_mapping",
            ee_data: ee_data,
            auto_product_sync_setting: "<?php echo esc_html_e(wp_create_nonce('auto_product_sync_setting-nonce')); ?>"
        };
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: data,
            beforeSend: function () {                
                jQuery('html, body').animate({
                    scrollTop: jQuery("#attributeMapping").offset().top
                }, 0);
                conv_change_loadingbar('show');
            },
            success: function (response) {
                conv_change_loadingbar('hide');
                if (response.error === false) {
                    jQuery("#conv_save_success_modal_cta").modal("show");
                } else {
                    jQuery("#conv_save_error_txt").html(response.message);
                    jQuery("#conv_save_error_modal").modal("show");
                }

            }
        });
    });
    /***********************Save Attribute Mapping End **************************************************************************/
    /***********************Save Category Mapping Start **************************************************************************/
    jQuery(document).on("click", "#cat_mapping_save", function () {
        let ee_data = jQuery("#category_mapping").find("input[value!=''], select:not(:empty), input[type='number']").serialize();
        var data = {
            action: "save_category_mapping",
            ee_data: ee_data,
            auto_product_sync_setting: "<?php echo esc_html_e(wp_create_nonce('auto_product_sync_setting-nonce')); ?>"
        };
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: data,
            beforeSend: function () {
               
                jQuery('html, body').animate({
                    scrollTop: jQuery("#attributeMapping").offset().top
                },0);
                conv_change_loadingbar('show');
            },
            success: function (response) {
                conv_change_loadingbar('hide');
                if (response.error === false) {
                    jQuery("#conv_save_success_modal_cta").modal("show");
                } else {
                    jQuery("#conv_save_error_txt").html(response.message);
                    jQuery("#conv_save_error_modal").modal("show");
                }
            }
        });
    });
    /***********************Save Category Mapping End **************************************************************************/
    /***********************Enable edited category Start **************************************************************************/
    function selectSubCategory(thisObj) {
        selectId = thisObj.id;
        wooCategoryId = jQuery(thisObj).attr("catid");
        var selvalue = jQuery('#' + selectId).find(":selected").val();
        var seltext = jQuery('#' + selectId).find(":selected").text();
        jQuery("#category-" + wooCategoryId).val(selvalue);
        jQuery("#category-name-" + wooCategoryId).val(seltext);
        setTimeout(function () {
            jQuery(".select2").select2();
        }, 100);
    }
    /***********************Enable edited category End **************************************************************************/
    /***********************Show Loading Bar Start **************************************************************************/
    function conv_change_loadingbar(state = 'show') {
        if (state === 'show') {
            jQuery("#loadingbar_blue").removeClass('d-none');
            jQuery("#wpbody").css("pointer-events", "none");            
        } else {
            jQuery("#loadingbar_blue").addClass('d-none');
            jQuery("#wpbody").css("pointer-events", "auto");
        }
    }
    /***********************Show Loading Bar End **************************************************************************/
    /*************************************Save Feed Data End***************************************************************************/
    function conv_change_loadingbar_modal(state = 'show') {
        if (state === 'show') {
            jQuery("#loadingbar_blue_modal").removeClass('d-none');
            jQuery("#wpbody").css("pointer-events", "none");
        } else {
            jQuery("#loadingbar_blue_modal").addClass('d-none');
            jQuery("#wpbody").css("pointer-events", "auto");
        }
    }
    /*************************Create Super AI Feed Start ************************************************************************/
    /*************************Create Super AI Feed Start ************************************************************************/
    jQuery(document).on('click', '.createSuperAIFeed', function() {
        createSuperAIFeed();
    });
    function createSuperAIFeed() {
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: {
                action: "ee_super_AI_feed",
                create_superFeed_nonce: "<?php echo wp_create_nonce('create_superFeed_nonce_val'); ?>",                           
            },
            beforeSend: function () {
                conv_change_loadingbar_modal('show');
            },
            success: function(response) {
                conv_change_loadingbar_modal('hide');
                if(response.status == 'success'){
                    jQuery('.syncSuccessMessage').html('Your latest '+ response.total_product +' products are synced to your Google Merchant Center account.')
                    jQuery("#conv_super_feed_modal").modal("show");
                }
            },
            error: function(error) {
                
            }
        });
    }
    /*************************Create Super AI Feed End ***************************************************************************/
    jQuery(document).on('click', '.close_feed_modal', function () {
        jQuery("#conv_super_feed_modal").modal("hide");
        location.reload();
    })
    /***************************Call create feed modal ****************************************************************************/
    jQuery(".createFeed").on("click", function () { 
        jQuery("#conv_save_success_modal_cta").modal("hide");         
        jQuery('#autoSyncIntvl').attr('disabled', false);
        jQuery('#gmc_id').attr('disabled', false);
        jQuery('#target_country').attr('disabled', false);
        jQuery("#feedForm")[0].reset();
        jQuery('#feedType').text('Create New Feed');
        jQuery('#edit').val('');
        jQuery('#convCreateFeedModal').modal('show');
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        jQuery('.select2').select2({ dropdownParent: jQuery("#convCreateFeedModal") });
    });
     /***************************Call create feed modal end****************************************************************************/
    /****************Submit Feed call start*********************************/
    jQuery(document).on('click', '#submitFeed', function (e) {
            e.preventDefault();
            let feedName = jQuery('#feedName').val();
            if (feedName === '') {
                jQuery('#feedName').css('margin-left', '0px');
                jQuery('#feedName').css('margin-right', '0px');
                jQuery('#feedName').addClass('errorInput');
                var l = 4;
                for (var i = 0; i <= 2; i++) {
                    jQuery('#feedName').animate({
                        'margin-left': '+='+(l = -l)+'px',
                        'margin-right': '-='+l+'px'
                    }, 50);
                }
                return false;
            }

            let autoSyncIntvl = jQuery('#autoSyncIntvl').val();
            if(autoSyncIntvl === ''){
                jQuery('#autoSyncIntvl').css('margin-left', '0px');
                jQuery('#autoSyncIntvl').css('margin-right', '0px');
                jQuery('#autoSyncIntvl').addClass('errorInput');
                var l = 4;
                for (var i = 0; i <= 2; i++) {
                    jQuery('#autoSyncIntvl').animate({
                        'margin-left': '+='+(l = -l)+'px',
                        'margin-right': '-='+l+'px'
                    }, 50);
                }
                return false;
            }

            let target_country = jQuery('#target_country').find(":selected").val();
            if(target_country === ""){
                jQuery('.select2-selection').css('border', '1px solid #ef1717');                
                return false;
            }            

            if (!jQuery('#gmc_id').is(":checked")) {
                jQuery('.errorChannel').css('border', '1px solid red');
                return false;
            }

            save_feed_data();
        });

        /****************Submit Feed call end***********************************/

    /*************************************Save Feed Data Start*************************************************************************/
    function save_feed_data(google_merchant_center_id, catalog_id) {
        var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>"
        let edit = jQuery('#edit').val();
        var data = {
            action: "save_feed_data",
            feedName: jQuery('#feedName').val(),
            google_merchant_center: jQuery('input#gmc_id').is(':checked') ? '1' : '',
            autoSync: jQuery('input#autoSync').is(':checked') ? '1' : '0',
            autoSyncIntvl: '25',
            edit: edit,
            last_sync_date: jQuery('#last_sync_date').val(),
            is_mapping_update: jQuery('#is_mapping_update').val(),
            target_country: jQuery('#target_country').find(":selected").val(),
            conv_onboarding_nonce: conv_onboarding_nonce
        }
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: data,
            beforeSend: function () {
                conv_change_loadingbar_modal('show');
            },
            error: function (err, status) {
                conv_change_loadingbar_modal('hide');
                jQuery('#convCreateFeedModal').modal('hide');
                jQuery("#conv_save_error_txt").html('Error occured.');
                jQuery("#conv_save_error_modal").modal("show");
            },
            success: function (response) {
                conv_change_loadingbar_modal('hide');
                jQuery('#convCreateFeedModal').modal('hide');
                if (response.id) {
                    jQuery(".created_success").html('Feed Created Successfully');
                    jQuery("#conv_save_success_txt_").html('Redirecting To Product List');
                     jQuery("#conv_save_success_modal_").modal("show");
                    setTimeout(function () {
                        if (edit !== '') {
                            location.reload(true);
                        } else {
                            window.location.replace("<?php echo esc_url_raw($site_url.'product_list&id='); ?>"+response.id);
                        }

                    }, 100);
                } else {
                    jQuery("#conv_save_error_txt").html(response.message);
                    jQuery("#conv_save_error_modal").modal("show");
                }
            }
        });

    }
    /*************************************Save Feed Data End***************************************************************************/
    
    function conv_change_loadingbar_header(state = 'show') {
        if (state === 'show') {
            jQuery("#loadingbar_blue_header").removeClass('d-none');
            jQuery("#wpbody").css("pointer-events", "none");
        } else {
            jQuery("#loadingbar_blue_header").addClass('d-none');
            jQuery("#wpbody").css("pointer-events", "auto");
        }
    }
    /*************************Create Super AI Feed Start ************************************************************************/
    /*************************Slider animation start ************************************************************************/
    jQuery(document).on('click', '.toggleOpen', function() {
        jQuery('.toggleSpan').show(300);
    })
    jQuery(document).on('click', '.toggleClose', function() {
        jQuery('.toggleSpan').hide(300);
    })
    /*************************Slider animation end ************************************************************************/
</script>