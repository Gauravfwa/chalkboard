<?php

class SyncProductConfiguration
{
protected $TVC_Admin_Helper;
protected $subscriptionId;
protected $TVC_Admin_DB_Helper;
protected $TVCProductSyncHelper;
public function __construct(){
  $this->includes();
	$this->TVC_Admin_Helper = new TVC_Admin_Helper();
  $this->TVC_Admin_DB_Helper = new TVC_Admin_DB_Helper();
  $this->TVCProductSyncHelper = new TVCProductSyncHelper();
  $this->subscriptionId = $this->TVC_Admin_Helper->get_subscriptionId();  
  $this->site_url = "admin.php?page=conversios-google-shopping-feed&tab=";
  $this->TVC_Admin_Helper->need_auto_update_db(); 	
  $this->html_run();
}
public function includes(){
  if (!class_exists('TVCProductSyncHelper')) {
    require_once(__DIR__ . '/class-tvc-product-sync-helper.php');
  }
}
public function html_run(){
	$this->TVC_Admin_Helper->add_spinner_html();
  $this->create_form();
}

public function create_form(){
  if(isset($_GET['welcome_msg']) && sanitize_text_field($_GET['welcome_msg']) == true){
    $this->TVC_Admin_Helper->call_domain_claim();
    $class = 'notice notice-success';
    $message = esc_html__("Everthing is now set up. One more step - Sync your WooCommerce products into your Merchant Center and reach out to millions of shopper across Google.","enhanced-e-commerce-for-woocommerce-store");
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    ?>
    <script>
      jQuery(document).ready(function() {
        var msg="<?php echo esc_attr($message);?>"
        tvc_helper.tvc_alert("success", "<?php esc_html_e("Congratulation..!","enhanced-e-commerce-for-woocommerce-store"); ?>", msg, true);
      });
    </script>
    <?php
  }
	
	$syncProductStat = (object)[];
	$syncProductList = [];
  $last_api_sync_up ="";  
	$google_detail = $this->TVC_Admin_Helper->get_ee_options_data();
	if(isset($google_detail['prod_sync_status'])){
    if ($google_detail['prod_sync_status']) {
      $syncProductStat = $google_detail['prod_sync_status'];
    }
  }
  global $wpdb;  
  $syncProductList = $wpdb->get_results("select * from ".$wpdb->prefix."ee_products_sync_list LIMIT 2000");
	if(isset($google_detail['setting'])){
    if ($google_detail['setting']) {
      $googleDetail = $google_detail['setting'];
    }
  }
  $last_api_sync_up = "";
  if(isset($google_detail['sync_time']) && $google_detail['sync_time']){      
    $date_formate=get_option('date_format')." ".get_option('time_format');
    if($date_formate ==""){
      $date_formate = 'M-d-Y H:i';
    }
    $last_api_sync_up = date( $date_formate, $google_detail['sync_time']);      
  }
  $is_need_to_update = $this->TVC_Admin_Helper->is_need_to_update_api_to_db();  
  $woo_product = wp_count_posts( 'product' )->publish;

  $ee_additional_data = $this->TVC_Admin_Helper->get_ee_additional_data();  
  $tablename = esc_sql( $wpdb->prefix ."ee_prouct_pre_sync_data" );
  $total_que_products = $wpdb->get_var("SELECT COUNT(*) as a FROM $tablename");
  $total_sync_products = $wpdb->get_var("SELECT COUNT(*) as a FROM $tablename where `status` = 1");
  $last_update_date_obj = $wpdb->get_row("SELECT update_date FROM $tablename where `status` = 1  ORDER BY update_date DESC");
  $prod_batch_response = unserialize(get_option('ee_prod_response'));
  $imin = $rhr = $rsec = $rmin = $sec = $total_batch_size = $total_pending_pro = $total_batches = $total_seconds = 0;
  $min = 1;
  if (!empty($last_update_date_obj)) {
    $last_update_date = $last_update_date_obj->update_date;
    //$interval = (new DateTime($last_update_date))->diff(new DateTime());
    $interval = (new DateTime($last_update_date))->diff(new DateTime(date('Y-m-d H:i:s', current_time( 'timestamp' ))));
    $imin = $interval->days * 24 * 60;
    $imin += $interval->h * 60;
    $imin += $interval->i;
  }

  if (isset($prod_batch_response['time_duration'])) {
    $minutes = $prod_batch_response['time_duration']->i;
    $seconds = $prod_batch_response['time_duration']->s;
    if ($minutes > 0) {
      $total_seconds = $minutes*60;
    }
    if ($seconds > 0) {
      $total_seconds = $total_seconds+$seconds;
    }
  }
  if (isset($ee_additional_data['product_sync_batch_size'])) {
    if ($total_que_products > 0) {
      $total_batch_size = $ee_additional_data['product_sync_batch_size'];
      $total_pending_pro = $total_que_products - $total_sync_products;
      $total_batches = ($total_pending_pro/$total_batch_size);
    } 
    if ($total_pending_pro == 0) {
      // add scheduled cron job  
      as_unschedule_all_actions( 'auto_product_sync_process_scheduler' );
    }
  }
  if ($total_batches > 0 && $total_seconds > 0) {
    if ($total_seconds > 60 ) {
      $sec = $total_seconds %60;
      $min = floor(($total_seconds%3600)/60);
    } else {
      //$sec = $total_seconds;
    }

    $total_require_secs = ($total_batches*($total_seconds));
    if ($total_require_secs > 60 ) {
      $rsec = $total_require_secs %60;
      $rmin = floor(($total_require_secs%3600)/60);
      $rhr = floor(($total_require_secs%86400)/3600);
    }
  }
  //$message = "Your WooCommerce products are being synced (".$total_sync_products."/".$total_que_products."). It is taking ".$min." Minutes and ".$sec." Seconds to sync [".$total_batch_size."] products. The remaining ".$total_pending_pro." products will be synced in ".$rhr." Hrs ".$rmin." Minutes and ".$rsec." Seconds.";
  $message = "Your WooCommerce products are being synced (".$total_sync_products."/".$total_que_products."). It is taking ".$min." Minutes to sync [".$total_batch_size."] products. The remaining ".$total_pending_pro." products will be synced in ".$rhr." Hrs ".$rmin." Minutes";
?>

<?php 
// add product sync popup
echo $this->TVCProductSyncHelper->tvc_product_sync_popup_html(); 
$is_need_to_domain_claim = false;
if(isset($googleDetail->google_merchant_center_id) && $googleDetail->google_merchant_center_id && $this->subscriptionId != "" && isset($googleDetail->is_domain_claim) && $googleDetail->is_domain_claim == '0'){
  $is_need_to_domain_claim = true;
}?>
		<?php
  }
}
?>