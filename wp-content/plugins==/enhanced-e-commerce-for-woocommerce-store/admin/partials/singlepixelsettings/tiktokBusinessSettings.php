<?php
require_once ENHANCAD_PLUGIN_DIR . 'admin/class-tvc-admin-helper.php';
$tvcAdminHelper = new TVC_Admin_DB_Helper();
$catalogData = $tvcAdminHelper->tvc_get_results('ee_tiktok_catalog');
$catalogCountry = array();
$catalog_business_id = array();
if (is_array($catalogData) && !empty($catalogData)) {
    foreach ($catalogData as $key => $value) {
        $catalogCountry[$key] = $value->country;
        $catalog_business_id[$key] = $value->catalog_id;
    }
}

$is_sel_disable = 'disabled';
$tiktok_mail = isset($ee_options['tiktok_setting']['tiktok_mail']) === TRUE ? $ee_options['tiktok_setting']['tiktok_mail'] : '';
$tiktok_user_id = isset($ee_options['tiktok_setting']['tiktok_user_id']) === TRUE ? $ee_options['tiktok_setting']['tiktok_user_id'] : '';
$tiktok_business_id = isset($ee_options['tiktok_setting']['tiktok_business_id']) === TRUE ? $ee_options['tiktok_setting']['tiktok_business_id'] : '';
$tiktok_business_name = isset($ee_options['tiktok_setting']['tiktok_business_name']) === TRUE ? $ee_options['tiktok_setting']['tiktok_business_name'] : '';
if (isset($_GET['tiktok_mail']) == TRUE) {
    $tiktok_mail = $_GET['tiktok_mail'];
}
if (isset($_GET['tiktok_user_id']) == TRUE) {
    $tiktok_user_id = $_GET['tiktok_user_id'];
}

$site_url = "admin.php?page=conversios-google-shopping-feed&tab=";
$TVC_Admin_Helper = new TVC_Admin_Helper();
$plan_id = $TVC_Admin_Helper->get_plan_id();
$conv_data = $TVC_Admin_Helper->get_store_data();
$getCountris = @file_get_contents(ENHANCAD_PLUGIN_DIR . "includes/setup/json/countries.json");
$contData = json_decode($getCountris);
?>
<style>
    .tooltip-inner {
        max-width: 500px !important;
    }

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

<div class="convcard p-4 mt-0 rounded-3 shadow-sm">
    <?php if (isset($pixel_settings_arr[$subpage]['topnoti']) && $pixel_settings_arr[$subpage]['topnoti'] != "") { ?>
        <div class="alert d-flex p-0" role="alert">
            <span class="p-2  pt-3 material-symbols-outlined text-light conv-success-bg rounded-start">info</span>
            <div class="p-2 w-100 rounded-end border border-start-0 shadow-sm conv-notification-alert lh-lg">
                <?php esc_html_e($pixel_settings_arr[$subpage]['topnoti'], "enhanced-e-commerce-for-woocommerce-store"); ?>
            </div>
        </div>
    <?php } ?>
    <?php
    $connect_url = $TVC_Admin_Helper->get_custom_connect_url_subpage(admin_url() . 'admin.php?page=conversios-google-shopping-feed', "tiktokBusinessSettings");
    /**************Tiktok Auth start ********************************************************/
    $confirm_url = "admin.php?page=conversios-google-shopping-feed&subpage=tiktokBusinessSettings";
    $state = ['confirm_url' => admin_url() . $confirm_url, 'subscription_id' => $subscriptionId];
    $tiktok_auth_url = "https://ads.tiktok.com/marketing_api/auth?app_id=7233778425326993409&redirect_uri=https://connect.tatvic.com/laravelapi/public/auth/tiktok/callback&rid=q6uerfg9osn&state=" . urlencode(json_encode($state));
    
    //$tiktok_auth_url = "https://ads.tiktok.com/marketing_api/auth?app_id=7233778425326993409&redirect_uri=https://laravelapi.tatvic.com/laravelapi/public/auth/tiktok/callback&rid=tee9ehl4mwc&state=" . urlencode(json_encode($state));

    if ($tiktok_mail === '' && $tiktok_user_id === '') { ?>    
        <a onclick='window.open("<?php echo $tiktok_auth_url ?>","MyWindow","width=800,height=700,left=300, top=150"); return false;'
            href="#" class="signIn">
            <button class="btn btn-outline-dark" id=""><img style="width:19px"
                    src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/conv_tiktok_logo.png'); ?>">
                &nbsp;Continue with TikTok</button>
        </a>
        <p class="mt-2" style="font-size: 10px;">Please login to the Email account linked to your TikTok Business account so that we can get you business accounts and catalogs and use Chrome for best experience.</p>
        
    <?php } else { ?>
        <h5 class="fw-normal mb-1">
            <?php esc_html_e("Successfully signed in with account:", "enhanced-e-commerce-for-woocommerce-store"); ?>
        </h5>
        <?php echo ($tiktok_mail . ', <b>User Id: </b>' . $tiktok_user_id . ' '); ?>
        <a onclick='window.open("<?php echo $tiktok_auth_url ?>","MyWindow","width=800,height=700,left=300, top=150"); return false;'
            href="#" class="signIn">Change</a>
    <?php }
    /**************Tiktok Auth end **********************************************************/
    ?>

    <form id="gmcsetings_form" class="convpixsetting-inner-box mt-4">   
        <div id="" class="py-1">
            <label class="text-dark fw-bold-500">
                <?php esc_html_e("Select TikTok Business Account", "enhanced-e-commerce-for-woocommerce-store"); ?>
            </label>
            <div class="row pt-2 conv-gmcsettings">
                <div class="col-8">
                    <select id="tiktok_business_id" name="tiktok_business_id"
                        class="form-select form-select-lg mb-3 selecttwo valtoshow_inpopup_this" style="width: 100%"
                        <?php echo esc_attr($is_sel_disable); ?>>
                        <?php if (!empty($tiktok_business_id)) { ?>
                            <option value="<?php echo esc_attr($tiktok_business_id); ?>" selected
                                data-business_name="<?php echo esc_attr($tiktok_business_name) ?>"><?php echo esc_attr($tiktok_business_id); ?> - <?php echo esc_attr($tiktok_business_name) ?></option>
                        <?php } ?>
                        <option value="">Select TikTok Business Account</option>
                    </select>
                </div>
                <?php $enable = ($tiktok_user_id !== '' ? 'conv-enable-selection': ''); ?>
                <div class="col-2 <?php echo $enable ?> conv-link-blue" >
                    <span class="material-symbols-outlined pt-2">edit</span><label class="mb-2 fs-6 text">Edit</label>
                </div>
            </div>
        </div>
        <div class="py-1">
            <label class="text-dark fw-bold-500">
                <?php esc_html_e("Map Catalog For Target Country", "enhanced-e-commerce-for-woocommerce-store"); ?>
            </label>
            <div class="row pt-2">
                <div class="col-12">
                    <table class="table table-bordered" id="map_catalog_table" style="width:100%">
                        <thead>
                            <tr class="">
                                <th scope="col" class="text-start ">
                                    <?php esc_html_e("Target Country", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </th>
                                <th scope="col" class="text-start">
                                    <?php esc_html_e("Catalog Id", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="table-body">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <input type="hidden" id="valtoshow_inpopup" value="TikTok Business Account:" />
    <input type="hidden" id="tiktok_user_id" value="<?php echo $tiktok_user_id ?>" />
    <input type="hidden" id="tiktok_mail" value="<?php echo $tiktok_mail ?>" />
    <input type="hidden" id="conversios_onboarding_nonce"
        value="<?php echo wp_create_nonce('conversios_onboarding_nonce'); ?>" />
        <input type="hidden" id="feedType" name="feedType" value="<?php echo isset($_GET['feedType']) && $_GET['feedType'] !='' ? esc_attr($_GET['feedType']) : '' ?>" />

</div>
<!-- Error Save Modal -->
<div class="modal fade" id="conv_save_error_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">

            </div>
            <div class="modal-body text-center p-0">
                <img style="width:184px;"
                    src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/error_logo.png'); ?>">
                <h3 class="fw-normal pt-3">Error</h3>
                <span id="conv_save_error_txt" class="mb-1 lh-lg"></span>
            </div>
            <div class="modal-footer border-0 pb-4 mb-1">
                <button class="btn conv-yellow-bg m-auto text-white" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Error Save Modal End -->

<div class="modal fade" id="conv_save_success_modal_" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">

            </div>
            <div class="modal-body text-center p-0">
                <img style="width:184px;"
                    src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/update_success_logo.png'); ?>">
                <h3 class="fw-normal pt-3">
                    <?php esc_html_e("Updated Successfully", "enhanced-e-commerce-for-woocommerce-store"); ?>
                </h3>
                <span id="conv_save_success_txt_" class="mb-1 lh-lg"></span>
            </div>
            <div class="modal-footer border-0 pb-4 mb-1">
                <button class="btn conv-blue-bg m-auto text-white" data-bs-dismiss="modal">Ok, Done</button>
            </div>
        </div>
    </div>
</div>

<!-------------------------CTA POP up Start ---------------------------------->
<div class="modal fade" id="conv_save_success_modal_cta" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div class="connection-box">
                    <div class="items">
                        <img style="width:35px;"
                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/popup_woocommerce _logo.png'); ?>">
                        <span>
                            <?php esc_html_e("Woo Commerce", "enhanced-e-commerce-for-woocommerce-store"); ?>
                        </span>
                    </div>
                    <div class="items">
                        <span class="material-symbols-outlined text-primary">
                            arrow_forward
                        </span>
                    </div>
                    <div class="items">
                        <img style="width:35px;"
                            src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logos/tiktok_channel_logo.png'); ?>">
                        <span>
                            <?php esc_html_e("TikTok Business Account", "enhanced-e-commerce-for-woocommerce-store"); ?>
                        </span>
                    </div>
                </div>

            </div>
            <div class="modal-body text-center p-4">
                <div class="connected-content">
                    <h4>
                        <?php esc_html_e("Successfully Connected", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </h4>
                    <p><span class="fw-bolder">TikTok Business Account -</span> <span
                            class="gmcAccount fw-bolder"></span>
                        Has Been Successfully Connected</p>
                    <p class="my-3">
                        <?php esc_html_e("Success! Your product feed is now linked to TikTok's powerful catalog, unlocking vast global audiences and maximizing your sales potential through our plugin."); ?>
                    </p>
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
                                        <h3>
                                            <?php esc_html_e("Manage Feeds", "enhanced-e-commerce-for-woocommerce-store"); ?>
                                        </h3>
                                        <p>
                                            <?php esc_html_e("A feed management tool centralizes updates, optimizes listings, and boosts data quality, streamlining product feed management for better efficiency and effectiveness.", "enhanced-e-commerce-for-woocommerce-store"); ?>

                                        </p>
                                        <div class="attribute-btn">
                                            <a href="#" class="btn btn-dark common-btn">Create Feed</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a
                                href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&subpage="gmcsettings"'); ?>">Connect
                                to Google Merchant Center</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--------------------------------CTA popup End -------------------------------------->
<?php
$google_merchant_center_id = '';
if (isset($googleDetail->google_merchant_center_id) === TRUE && $googleDetail->google_merchant_center_id !== '') {
    $google_merchant_center_id = $googleDetail->google_merchant_center_id;
}
?>
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
                    <h5 class="modal-title fs-16 fw-500" id="">
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
                            <input type="text" class="form-control-sm fs-14 " <?php echo ($plan_id === 1) ? 'readonly="readonly"' : ''; ?> name="autoSyncIntvl" id="autoSyncIntvl" size="3" min="1"
                                onkeypress="return ( event.charCode === 8 || event.charCode === 0 || event.charCode === 13 || event.charCode === 96) ? null : event.charCode >= 48 && event.charCode <= 57"
                                oninput="removeZero();" value="25">
                            <label for="" class="col-form-label fs-14 fw-400">
                                <?php esc_html_e("Days", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <span>
                                <?php echo ($plan_id === 1) ? '<a target="_blank" href="https://www.conversios.io/wordpress/product-feed-manager-for-woocommerce-pricing/?utm_source=app_wooPFM&utm_medium=BUSINESS&utm_campaign=Pricing"><b> Upgrade To Pro</b></a>' : ''; ?>
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
                            <select class="select2 form-select form-select-sm mb-3" aria-label="form-select-sm example"
                                style="width: 100%" name="target_country" id="target_country">
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
                    <div class="mb-3">
                        <div class="form-check form-check-custom">
                            <input class="form-check-input check-height fs-14 errorChannel" type="checkbox"
                                value="<?php echo $google_merchant_center_id !== '' ? $google_merchant_center_id : '' ?>"
                                id="gmc_id" name="gmc_id" <?php echo $google_merchant_center_id !== '' ? "checked" : 'disabled' ?>>
                            <label for="" class="col-form-label fs-14 pt-0 text-dark fw-500">
                                <?php esc_html_e("Google Merchant Center Account :", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <label class="col-form-label fs-14 pt-0 fw-400 modal_google_merchant_center_id">
                                <?php echo $google_merchant_center_id !== '' ? $google_merchant_center_id : '' ?>
                            </label>
                        </div>
                        <div class="form-check form-check-custom">
                            <input class="form-check-input check-height fs-14 errorChannel" type="checkbox" value=""
                                id="tiktok_id" name="tiktok_id" checked>
                            <label for="" class="col-form-label fs-14 pt-0 text-dark fw-500">
                                <?php esc_html_e("TikTok Catalog Id :", "enhanced-e-commerce-for-woocommerce-store"); ?>
                            </label>
                            <label class="col-form-label fs-14 pt-0 fw-400 tiktok_catalog_id">

                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <input type="hidden" id="edit" name="edit">
                    <input type="hidden" id="is_mapping_update" name="is_mapping_update" value="">
                    <input type="hidden" id="last_sync_date" name="last_sync_date" value="">
                    <button type="button" class="btn btn-light btn-sm border" data-bs-dismiss="modal"
                        onclick="jQuery('#feedForm')[0].reset()">
                        <?php esc_html_e("Cancel", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </button>
                    <button type="button" class="btn btn-soft-primary btn-sm" id="submitFeed">
                        <?php esc_html_e("Create and Next", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-------------------------CTA super_feed_modal Start ---------------------------------->
<div class="modal fade" id="conv_super_feed_modal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true" style="z-index: 99999">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">  
            <div class="modal-header justify-content-end connection-header border-0 pb-0">   
                <button type="button" style="margin: 0px; padding:0px;" class="btn-close close_feed_modal" aria-label="Close"></button>
            </div>                   
            <div class="modal-body text-center p-4">
                <div class="connected-content">
                    <h3>
                        <?php esc_html_e("Congratulations!", "enhanced-e-commerce-for-woocommerce-store"); ?>
                    </h3>                                    
                    <p class="my-3 syncSuccessMessage" style="font-size: 20px;">
                        
                    </p>
                    <p><?php esc_html_e("And that's not all.", "enhanced-e-commerce-for-woocommerce-store"); ?></p>
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
                                            <a href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&tab=feed_list'); ?>" class="btn btn-dark">Manage Feeds</a>
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
                                            <a href="<?php echo esc_url_raw('admin.php?page=conversios-google-shopping-feed&tab=gaa_config_page'); ?>" class="btn btn-dark">Configure Now</a>
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
<script>    
    /**
     * Get TikTok business Account List
     */
    function list_tiktok_business_account() {        
        conv_change_loadingbar("show");
        var conversios_onboarding_nonce = "<?php echo wp_create_nonce('conversios_onboarding_nonce'); ?>";
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: {
                action: "get_tiktok_business_account",
                subscriptionId: "<?php echo $subscriptionId ?>",
                conversios_onboarding_nonce: conversios_onboarding_nonce
            },
            success: function (response) {
                if (response.error === false) {
                    jQuery('#tiktok_business_id').empty();
                    jQuery('#tiktok_business_id').append(jQuery('<option>', {
                        value: "",
                        text: "Select TikTok Business Account"
                    }));
                    if (response.data) {
                        var tiktok_business_id = "<?php echo $tiktok_business_id ?>";
                        jQuery.each(response.data, function (key, value) {
                            jQuery('#tiktok_business_id').append(jQuery('<option>', {
                                value: key,
                                "data-business_name": value,
                                text: key + ' - ' + value,
                                selected: (key === tiktok_business_id)
                            }));
                        });
                        jQuery('#tiktok_business_id').prop('disabled', false);
                        jQuery("#tiktok_business_id").select2({ dropdownCssClass: "fs-12" })
                        jQuery(".conv-btn-connect").removeClass("conv-btn-connect-disabled");
                        jQuery(".conv-btn-connect").removeClass("conv-btn-disconnect");
                        jQuery(".conv-btn-connect").addClass("conv-btn-connect-enabled-gmc");
                    }
                }
                conv_change_loadingbar("hide");
            }
        })
    }
    /*************************function to save tiktok data in ee_option start ******************************************************************************************/
    function saveTiktokUser() {
        var tiktok_user_id = "<?php echo isset($ee_options['tiktok_setting']['tiktok_user_id']) === TRUE ? $ee_options['tiktok_setting']['tiktok_user_id'] : ''; ?>"
        var selected_vals = {};
        var tiktok_data = {};
        tiktok_data["tiktok_mail"] = "<?php echo $tiktok_mail ?>";
        tiktok_data["tiktok_user_id"] = "<?php echo $tiktok_user_id ?>";
        selected_vals["tiktok_setting"] = tiktok_data;
        if (tiktok_user_id == tiktok_data["tiktok_user_id"]) {
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: tvc_ajax_url,
                data: {
                    action: "conv_save_pixel_data",
                    pix_sav_nonce: "<?php echo wp_create_nonce('pix_sav_nonce_val'); ?>",
                    conv_options_data: selected_vals,
                    customer_subscription_id: "<?php echo $subscriptionId ?>",
                    conv_options_type: ["eeoptions"],
                },
                beforeSend: function () {
                    conv_change_loadingbar("show");
                },
                success: function (response) {
                    conv_change_loadingbar("hide");
                }
            });
        }
    }
    /*************************function to save tiktok data in ee_option end ******************************************************************************************/
    /*************************get user catalog id on tiktok business id change start ******************************************************************************************/
    jQuery(document).on("change", "#tiktok_business_id", function () {
        var catalogCountry = <?php echo json_encode($catalogCountry) ?>;
        var catalog_business_id = <?php echo json_encode($catalog_business_id) ?>;
        conv_change_loadingbar("show");
        var conversios_onboarding_nonce = "<?php echo wp_create_nonce('conversios_onboarding_nonce'); ?>";
        var business_id = jQuery('#tiktok_business_id').find(":selected").val();
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: tvc_ajax_url,
            data: {
                action: "get_tiktok_user_catalogs",
                customer_subscription_id: "<?php echo $subscriptionId ?>",
                business_id: business_id,
                conversios_onboarding_nonce: conversios_onboarding_nonce
            },
            success: function (response) {
                if (response.error === false) {
                    if (response.data) {
                        jQuery('#table-body').empty();
                        var tableBody = '';
                        jQuery.each(response.data, function (key, value) {
                            if (jQuery.inArray(key, catalogCountry) === -1 && catalogCountry.length > 0) {
                                jQuery(".conv-btn-connect").removeClass("conv-btn-connect-disabled");
                                jQuery(".conv-btn-connect").removeClass("conv-btn-disconnect");
                                jQuery(".conv-btn-connect").addClass("conv-btn-connect-enabled-gmc");
                            }
                            tableBody += '<tr>';
                            tableBody += '<td class="align-middle text-start">' + key + '</td>';
                            tableBody += '<td class="align-middle text-start"><select id="" name="catalogId[]" class="form-select form-select-lg mb-3 catalogId" style="width: 100%">';
                            jQuery.each(value, function (valKey, ValValue) {
                                var selected = "";
                                if (jQuery.inArray(valKey, catalog_business_id) !== -1 && catalog_business_id.length > 0) {
                                    var selected = 'selected="selected"';
                                }
                                tableBody += '<option value="' + valKey + '"  data-catalog_country="' + key + '" data-catalog_name="' + ValValue + '" ' + selected + '>' + valKey + ' - ' + ValValue + '</option>';
                            })
                            tableBody += '</select></td></tr>';
                        });
                        jQuery('#table-body').html(tableBody);
                        jQuery(".catalogId").select2({ dropdownCssClass: "fs-12" })
                    }
                }
                conv_change_loadingbar("hide");
            }
        })
    });
    /*************************get user catalog id on tiktok business id change end ******************************************************************************************/
    /**************************Enable save button on catalog is change start **********************************************/
    jQuery(document).on("change", ".catalogId", function () {
        jQuery(".conv-btn-connect").removeClass("conv-btn-connect-disabled");
        jQuery(".conv-btn-connect").removeClass("conv-btn-disconnect");
        jQuery(".conv-btn-connect").addClass("conv-btn-connect-enabled-gmc");

    });
    /**************************Enable save button on catalog is change end **********************************************/
    //Onload functions
    jQuery(function () {
        jQuery(".navinfotopnav ul li").removeClass('active');
        jQuery(".navinfotopnav ul li:nth-child(3)").addClass('active');
        jQuery(".navinfotopnav ul li:nth-child(2) img").css('filter', 'grayscale(100%)');

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        jQuery(".catalogId").select2()
        //override back button link to GMC Channel Configuration 
        jQuery('.hreflink').attr('href', 'admin.php?page=conversios-google-shopping-feed&tab=gaa_config_page');

        jQuery(".conv-enable-selection").click(function () {
            conv_change_loadingbar("show");
            jQuery(".conv-enable-selection").addClass('hidden');
            list_tiktok_business_account();
        });

        <?php if ((isset($_GET['subscription_id']) === TRUE && sanitize_text_field($_GET['subscription_id'])) || (isset($_GET['tiktok_mail']) === TRUE && !empty($_GET['tiktok_mail']))) { ?>
            <?php if (isset($ee_options['tiktok_setting']['tiktok_mail']) && ($_GET['tiktok_mail'] !== $ee_options['tiktok_setting']['tiktok_mail'])) { ?>
                saveTiktokUser();
            <?php } ?>
                list_tiktok_business_account();
            jQuery(".conv-enable-selection").addClass("d-none");
        <?php } ?>

        <?php if (!empty($tiktok_business_id)) { ?>
            jQuery('#tiktok_business_id').trigger("change");
        <?php } ?>
            jQuery(document).on('change', '#tiktok_business_id', function () {
                jQuery('.selection').find("[aria-labelledby='select2-tiktok_business_id-container']").removeClass('selectError');
            });
        /********************************Save TikTok id and other data  start ********************************************************/
        jQuery(document).on("click", ".conv-btn-connect-enabled-gmc", function () {
            var feedType = jQuery('#feedType').val();
            var catalogData = {};
            jQuery('.catalogId').each(function () {
                catalogData[jQuery(this).find(":selected").data("catalog_country")] = [jQuery(this).find(":selected").val(), jQuery(this).find(":selected").data("catalog_name"), jQuery(this).find(":selected").data("catalog_country")];
            })
            var selected_vals = {};
            var tiktok_data = {};
            tiktok_data["tiktok_mail"] = jQuery('#tiktok_mail').val();
            tiktok_data["tiktok_user_id"] = jQuery('#tiktok_user_id').val();
            tiktok_data["tiktok_business_id"] = jQuery('#tiktok_business_id').find(":selected").val();
            tiktok_data["tiktok_business_name"] = jQuery('#tiktok_business_id').find(":selected").data("business_name")
            selected_vals["tiktok_setting"] = tiktok_data;
            if (tiktok_data["tiktok_business_id"] === '') {
                jQuery('.selection').find("[aria-labelledby='select2-tiktok_business_id-container']").addClass('selectError');
                return false;
            }

            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: tvc_ajax_url,
                data: {
                    action: "conv_save_pixel_data",
                    pix_sav_nonce: "<?php echo wp_create_nonce('pix_sav_nonce_val'); ?>",
                    conv_options_data: selected_vals,
                    conv_catalogData: catalogData,
                    customer_subscription_id: "<?php echo $subscriptionId ?>",
                    conv_options_type: ["eeoptions", "tiktokmiddleware", "tiktokcatalog"],
                },
                beforeSend: function () {
                    conv_change_loadingbar("show");
                    jQuery(".conv-btn-connect-enabled-gmc").text("Saving...");
                    jQuery(".conv-btn-connect-enabled-gmc").addClass('disabled');
                },
                success: function (response) {
                    if(feedType !== '') {
                        createSuperAIFeed();
                    }else {
                        conv_change_loadingbar("hide");
                        if (response == "0" || response == "1") {
                            jQuery(".conv-btn-connect-enabled-gmc").text("Save");
                            jQuery('.gmcAccount').html(tiktok_data["tiktok_user_id"])
                            jQuery("#conv_save_success_modal_cta").modal("show");
                        }
                    }
                }
            });
        });
        /********************************Save TikTok id and other data  end ********************************************************/

        jQuery(".common-btn").on("click", function () {
            jQuery("#conv_save_success_modal_cta").modal("hide");
            jQuery('#autoSyncIntvl').attr('disabled', false);
            jQuery('#gmc_id').attr('disabled', false);
            jQuery('#target_country').attr('disabled', false);
            jQuery("#feedForm")[0].reset();
            jQuery('#feedType').text('Create New Feed');
            jQuery('#edit').val('');
            jQuery('.modal_google_merchant_center_id').html(jQuery("#google_merchant_center_id").val())
            jQuery('#gmc_id').val(jQuery("#google_merchant_center_id").val());
            jQuery('.tiktok_catalog_id').empty();
            jQuery('.tiktok_catalog_id').removeClass('text-danger');
            jQuery('#convCreateFeedModal').modal('show');
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
            var tiktok_business_account = jQuery('#tiktok_business_id').find(":selected").val();
            if (tiktok_business_account !== '' && jQuery('#tiktok_id').is(":checked")) {
                getCatalogId(jQuery('#target_country').find(":selected").val());
            }
            jQuery('#target_country').select2({ dropdownParent: jQuery("#convCreateFeedModal") });
        });
        /****************Get tiktok catalog id on target country change ***************************************/
        jQuery(document).on('change', '#target_country', function (e) {
            var tiktok_business_account = jQuery('#tiktok_business_id').find(":selected").val();
            jQuery('.select2-selection').css('border', '1px solid #c6c6c6');
            let target_country = jQuery('#target_country').find(":selected").val();
            jQuery('#tiktok_id').empty();
            jQuery('.tiktok_catalog_id').empty()
            if (target_country !== "" && tiktok_business_account !== "" && jQuery('input#tiktok_id').is(':checked')) {
                getCatalogId(target_country);
            }
        });
        /****************Get tiktok catalog id on target country change end ***************************************/
        /****************Get tiktok catalog id on check box change ***************************************/
        jQuery(document).on('change', '#tiktok_id', function () {
            jQuery('.tiktok_catalog_id').empty();
            jQuery('#tiktok_id').val('');
            if (jQuery('#tiktok_id').is(":checked")) {
                getCatalogId(jQuery('#target_country').find(":selected").val())
            }
        });
        /****************Get tiktok catalog id on check box change end ***************************************/
        /****************Feed Name error dismissed start************************/
        jQuery(document).on('input', '#feedName', function (e) {
            e.preventDefault();
            jQuery('#feedName').css('margin-left', '0px');
            jQuery('#feedName').css('margin-right', '0px');
            jQuery('#feedName').removeClass('errorInput');
        });
        /****************Feed Name error dismissed end**************************/
        /********************Modal POP up validation on click remove**********************************/
        jQuery(document).on('click', '#gmc_id', function (e) {
            jQuery('.errorChannel').css('border', '');
        });
        jQuery(document).on('click', '#tiktok_id', function (e) {
            jQuery('.errorChannel').css('border', '');
        });
        /********************Modal POP up validation on click remove end **********************************/
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
                        'margin-left': '+=' + (l = -l) + 'px',
                        'margin-right': '-=' + l + 'px'
                    }, 50);
                }
                return false;
            }

            let autoSyncIntvl = jQuery('#autoSyncIntvl').val();
            if (autoSyncIntvl === '') {
                jQuery('#autoSyncIntvl').css('margin-left', '0px');
                jQuery('#autoSyncIntvl').css('margin-right', '0px');
                jQuery('#autoSyncIntvl').addClass('errorInput');
                var l = 4;
                for (var i = 0; i <= 2; i++) {
                    jQuery('#autoSyncIntvl').animate({
                        'margin-left': '+=' + (l = -l) + 'px',
                        'margin-right': '-=' + l + 'px'
                    }, 50);
                }
                return false;
            }

            let target_country = jQuery('#target_country').find(":selected").val();
            if (target_country === "") {
                jQuery('.select2-selection').css('border', '1px solid #ef1717');
                return false;
            }

            if (!jQuery('#gmc_id').is(":checked") && !jQuery('#tiktok_id').is(":checked")) {
                jQuery('.errorChannel').css('border', '1px solid red');
                return false;
            }

            save_feed_data();
        });

        /****************Submit Feed call end***********************************/
    });
    /*************************************Get saved catalog id by country code start **************************************************/
    function getCatalogId($countryCode) {
        var conv_country_nonce = "<?php echo esc_html(wp_create_nonce('conv_country_nonce')); ?>";
        var data = {
            action: "ee_getCatalogId",
            countryCode: $countryCode,
            conv_country_nonce: conv_country_nonce
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
                //conv_change_loadingbar_modal('hide');
            },
            success: function (response) {
                jQuery('.tiktok_catalog_id').empty()
                jQuery('#tiktok_id').empty();
                jQuery('.tiktok_catalog_id').removeClass('text-danger');

                if (response.error == false) {
                    if (response.data.catalog_id !== '') {
                        jQuery('#tiktok_id').val(response.data.catalog_id);
                        jQuery('.tiktok_catalog_id').text(response.data.catalog_id)
                    } else {
                        jQuery('#tiktok_id').val('Create New');
                        jQuery('.tiktok_catalog_id').text('You do not have a catalog associated with the selected target country. Do not worry we will create a new catalog for you.');
                    }
                }
                conv_change_loadingbar_modal('hide');
            }
        });
    }
    /*************************************Get saved catalog id by country code End ****************************************************/
    function conv_change_loadingbar_modal(state = 'show') {
        if (state === 'show') {
            jQuery("#loadingbar_blue_modal").removeClass('d-none');
            jQuery("#wpbody").css("pointer-events", "none");
            jQuery('#submitFeed').attr('disabled', true);
        } else {
            jQuery("#loadingbar_blue_modal").addClass('d-none');
            jQuery("#wpbody").css("pointer-events", "auto");
            jQuery('#submitFeed').attr('disabled', false);
        }
    }
    /*************************************Save Feed Data Start*************************************************************************/
    function save_feed_data() {
        var conv_onboarding_nonce = "<?php echo esc_html(wp_create_nonce('conv_onboarding_nonce')); ?>"
        let edit = jQuery('#edit').val()
        var planid = "<?php echo esc_attr($plan_id); ?>";
        var data = {
            action: "save_feed_data",
            feedName: jQuery('#feedName').val(),
            google_merchant_center: jQuery('input#gmc_id').is(':checked') ? '1' : '',
            tiktok_id: jQuery('input#tiktok_id').is(':checked') ? '3' : '',
            tiktok_catalog_id: jQuery('input#tiktok_id').is(':checked') ? jQuery('input#tiktok_id').val() : '',
            autoSync: jQuery('input#autoSync').is(':checked') ? '1' : '0',
            autoSyncIntvl: jQuery('#autoSyncIntvl').val(),
            edit: edit,
            last_sync_date: '',
            is_mapping_update: '',
            target_country: jQuery('#target_country').find(":selected").val(),
            customer_subscription_id: "<?php echo $subscriptionId ?>",
            tiktok_business_account: jQuery('#tiktok_business_id').find(":selected").val(),
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
                if (response.id) {
                    jQuery('#convCreateFeedModal').modal('hide');
                    jQuery("#conv_save_success_txt_").html("Great job! Your product feed is ready! The next step is to select the products you want to sync and expand your reach across multiple channels.");
                    jQuery("#conv_save_success_modal_").modal("show");
                    setTimeout(function () {
                        window.location.replace("<?php echo esc_url_raw($site_url . 'product_list&id='); ?>" + response.id);
                    }, 100);
                } else if (response.errorType === 'tiktok') {
                    jQuery('.tiktok_catalog_id').empty();
                    jQuery('.tiktok_catalog_id').html(response.message);
                    jQuery('.tiktok_catalog_id').addClass('text-danger');

                } else {
                    jQuery('#convCreateFeedModal').modal('hide');
                    jQuery("#conv_save_error_txt").html(response.message);
                    jQuery("#conv_save_success_modal_").modal("show");
                }
                conv_change_loadingbar_modal('hide');
            }
        });

    }
        
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
        function createSuperAIFeed() {
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: tvc_ajax_url,
                data: {
                    action: "ee_super_AI_feed",
                    create_superFeed_nonce: "<?php echo wp_create_nonce('create_superFeed_nonce_val'); ?>",
                    type: 'Tiktok',                    
                },
                success: function(response) {
                    conv_change_loadingbar("hide");
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
</script>