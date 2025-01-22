<?php
/**
 * Plugin Name: Woocommerce Upsell Popup
 * Plugin URI: https://woocommerce.upsellpopup.com/
 * Description: An upsell & cross-sell popup plugin for Woocommerce. Show unobtrusive and responsive popup to your visitors when they click add to cart button.
 * Version: 1.8.9
 * Author: Upsell Popup
 * Author URI: https://woocommerce.upsellpopup.com/
 * WC requires at least: 6.0.0
 * WC tested up to: 7.2.2
 * Text Domain: very-simple-woocommerce-upsell-popup
 */

if ( ! defined( 'ABSPATH' ) ) exit;
define('thp-wc-upsell-popup-frzn', TRUE);

include ( 'wup-options.php' );
include ( 'wup-metabox.php' );
include ( 'wup-noajax.php' );

$thp_addtocart_popup_btn = false;

function thp_wuppro_active() {
	if ( ! function_exists('is_plugin_active')) {
	    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	
	if( is_plugin_active( 'woocommerce-upsell-popup-pro/index.php' ) ) {
		return true; //pro version is active
	}
return false;
}

if (thp_wuppro_active()) {
	include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/wuppro-metabox.php' );
}


function thp_woocommerce_inactive() {
	if ( ! function_exists('is_plugin_inactive')) {
	    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	
	if( is_plugin_inactive( 'woocommerce/woocommerce.php' ) ) {
		return true; //woocommerce is inactive
	}
return false;
}


function thp_wc_upsell_dependency_check() {
	if ( thp_woocommerce_inactive() ) {
	   add_action( 'admin_notices', 'thp_wc_upsell_dep_warning' );
	   return;
	}
	
	load_plugin_textdomain( 'very-simple-woocommerce-upsell-popup', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'thp_wc_upsell_dependency_check' );


function thp_wc_upsell_dep_warning() {
	?>
	<div class="notice notice-error">
		<p><?php _e( "Woocommerce Upsell Popup requires Woocommerce in order for it to work properly!", 'very-simple-woocommerce-upsell-popup' ); ?></p>
	</div>
	<?php
}


function thp_wc_upsell_activation_check() {
	
	if ( thp_woocommerce_inactive() ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( "Woocommerce Upsell Popup requires Woocommerce to be active. Please install and activate Woocommerce before trying to activate this plugin again.", 'very-simple-woocommerce-upsell-popup' ) );
	}
}
register_activation_hook( __FILE__, 'thp_wc_upsell_activation_check' );


function thp_wuppro_compatibility_check() {

 if ( (current_user_can('activate_plugins')) && (!wp_doing_ajax()) ) {
	if ( thp_wuppro_active() ) {
		$thp_wuppro_at_least = '1.1.2';
		
		$thp_wuppro_data = get_plugin_data( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/index.php', false, false );
		$thp_wuppro_user_v = $thp_wuppro_data['Version'];
		
		if ( version_compare($thp_wuppro_user_v, $thp_wuppro_at_least, '<') ) {
			
			deactivate_plugins( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/index.php' );
			
			$url = 'https://woocommerce.upsellpopup.com/my-account/';
			
			$notice_string = sprintf( wp_kses( __( 'The latest version of Woocommerce Upsell Popup is only compatible with Woocommerce Upsell Popup PRO version 1.1.2 or later. Please upgrade PRO to latest version!<br /><br /> Login to <a href="%s" target="_blank" rel="noopener noreferrer">your account</a>  to download the latest version of Woocommerce Upsell Popup PRO.', 'very-simple-woocommerce-upsell-popup' ), array(  'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ), 'br' => array() ) ), esc_url( $url ) );
			
			wp_die( $notice_string );
			exit;
		}
	}
 }
}
add_action( 'admin_init', 'thp_wuppro_compatibility_check' );


function thp_wuppro_version_check() {
	if ( thp_wuppro_active() ) {
		$thp_wuppro_latest_v = '1.1.2';
		
		$thp_wuppro_data = get_plugin_data( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/index.php', false, false );
		$thp_wuppro_user_v = $thp_wuppro_data['Version'];
		
		if ( version_compare($thp_wuppro_user_v, $thp_wuppro_latest_v, '<') ) {
			$url = 'https://woocommerce.upsellpopup.com/my-account/';
			$notice_string = sprintf( wp_kses( __( 'There is a new update available for Woocommerce Upsell Popup Pro. Please login to <a href="%s" target="_blank" rel="noopener noreferrer">your account</a>  to download the latest version.', 'very-simple-woocommerce-upsell-popup' ), array(  'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ), esc_url( $url ) );
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<p>'.$notice_string.'</p>';
			echo '</div>';
		}
	}
}
add_action('admin_notices', 'thp_wuppro_version_check');


function thp_wup_settings_link( $links ) {
	$settings_link = '<a href="'.esc_url( get_admin_url(null, 'admin.php?page=thp-wup-main-settings') ).'">' .
	esc_html( __( 'Settings', 'very-simple-woocommerce-upsell-popup' ) ) . '</a>';
	
	return array_merge( array( $settings_link ), $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'thp_wup_settings_link' );


function thp_popup_frontend_scripts () {
	if (!thp_wuppro_active()) {
		
		$ajax_enabled = get_option( 'woocommerce_enable_ajax_add_to_cart' );
		
		if ( is_shop() || is_product_category() ) {
			if ( 'yes' === $ajax_enabled ) {
				wp_enqueue_script( 'thp-popup-js', plugin_dir_url( __FILE__ ).'js/wup-ajax.min.js', array('jquery'), '', false );
				wp_localize_script( 'thp-popup-js', 'thp_popup_vars', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'added_to_cart' => __( 'Added to Cart!', 'very-simple-woocommerce-upsell-popup' ),
					'choose_one' => __( 'Please choose one option!', 'very-simple-woocommerce-upsell-popup' )
					)
				);
				wp_enqueue_script( 'wup-noajax-js', plugin_dir_url( __FILE__ ).'js/wup-noajax.min.js', array('jquery'), '', false );
			}
			else {
				wp_enqueue_script( 'wup-noajax-js', plugin_dir_url( __FILE__ ).'js/wup-noajax.min.js', array('jquery'), '', false );
				wp_localize_script( 'wup-noajax-js', 'wup_noajax_js_vars', array(
					'choose_one' => __( 'Please choose one option!', 'very-simple-woocommerce-upsell-popup' )
					)
				);
			}
		}
		elseif ( is_product() ) {
			
			$wup_options = ( !empty(get_option( 'thp_wup_options' )) ? get_option( 'thp_wup_options' ) : '' );
			$ajax_off = ( !empty($wup_options['ajax_off']) ? $wup_options['ajax_off'] : '' );
			
			if (!$ajax_off) { //ajax enabled
				
				wp_enqueue_script( 'thp-popup-js', plugin_dir_url( __FILE__ ).'js/wup-ajax.min.js', array('jquery'), '', false );
				wp_localize_script( 'thp-popup-js', 'thp_popup_vars', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'added_to_cart' => __( 'Added to Cart!', 'very-simple-woocommerce-upsell-popup' ),
					'choose_one' => __( 'Please choose one option!', 'very-simple-woocommerce-upsell-popup' )
					)
				);
				
				wp_enqueue_script( 'wup-noajax-js', plugin_dir_url( __FILE__ ).'js/wup-noajax.min.js', array('jquery'), '', false );
				
				$use_wc_ajax = ( !empty($wup_options['use_wc_ajax']) ? $wup_options['use_wc_ajax'] : '' );
				
				if ( (!$use_wc_ajax) && (!wp_script_is( 'woo-ajax-add-to-cart', 'enqueued' )) ) //uses quadlayers
					wp_enqueue_script( 'woo-ajax-add-to-cart', plugin_dir_url( __FILE__ ).'js/quadlayers-ajax-add-to-cart.min.js', array('jquery', 'wc-add-to-cart'), '', true );
				elseif ($use_wc_ajax) //uses wc
					wp_enqueue_script( 'wup-woo-ajax-add-to-cart', plugin_dir_url( __FILE__ ).'js/ajaxified.min.js', array('jquery', 'wc-add-to-cart'), '', true );
			}
			elseif ($ajax_off) { //ajax disabled
				wp_enqueue_script( 'wup-noajax-js', plugin_dir_url( __FILE__ ).'js/wup-noajax.min.js', array('jquery'), '', false );
				wp_localize_script( 'wup-noajax-js', 'wup_noajax_js_vars', array(
					'choose_one' => __( 'Please choose one option!', 'very-simple-woocommerce-upsell-popup' )
					)
				);
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'thp_popup_frontend_scripts' );


function thp_wup_ajax_add_to_cart_handler() {
	WC_Form_Handler::add_to_cart_action();
	WC_AJAX::get_refreshed_fragments();
}
add_action( 'wc_ajax_thp_wup_add_to_cart', 'thp_wup_ajax_add_to_cart_handler' );
add_action( 'wc_ajax_nopriv_thp_wup_add_to_cart', 'thp_wup_ajax_add_to_cart_handler' );


function thp_wup_remove_action_for_wc_handler() {
	
	$wup_options = ( !empty(get_option( 'thp_wup_options' )) ? get_option( 'thp_wup_options' ) : '' );
	$ajax_off = ( !empty($wup_options['ajax_off']) ? $wup_options['ajax_off'] : '' );
	
	if ($ajax_off)
		return;
	
	$use_wc_ajax = ( !empty($wup_options['use_wc_ajax']) ? $wup_options['use_wc_ajax'] : '' );
	
	if (! $use_wc_ajax) {
		return;
	}
	else if ( ($use_wc_ajax) && (is_ajax()) && (! is_admin()) ) {
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
	}

}
add_action( 'init', 'thp_wup_remove_action_for_wc_handler' );


function thp_upsell_backend_scripts( $hook ) {
    global $post;

    if ( ($hook == 'post-new.php') || ($hook == 'post.php') || (@$_REQUEST['page'] == 'thp-wup-main-settings')) { 
        if ( ('product' === @$post->post_type) || (@$_REQUEST['page'] == 'thp-wup-main-settings')) {  
			wp_enqueue_style( 'thp-select2-css', plugin_dir_url( __FILE__ ).'css/select2.min.css' );
			wp_enqueue_style( 'thp-upsell-admin-css', plugin_dir_url( __FILE__ ).'css/wup-admin-style.min.css' );						wp_enqueue_style( 'wp-color-picker' );			
			wp_enqueue_script( 'my-script-handle', plugin_dir_url( __FILE__ ).'js/flytonic_color_picker.js', array( 'wp-color-picker' ), false, true);
			wp_enqueue_script( 'thp-select2-js', plugin_dir_url( __FILE__ ).'js/select2.min.js', array( 'jquery' ), '4.0.3', true );
            wp_enqueue_script( 'thp-upsell-admin-js', plugin_dir_url( __FILE__ ).'js/product-search.min.js', array( 'jquery' ), false, true );
			wp_localize_script('thp-upsell-admin-js', 'thp_upsellmetabox_vars', array(
				'select2_searchproduct_placeholder' => __( 'Search for product name..', 'very-simple-woocommerce-upsell-popup' ),
				'select2_template_placeholder' => __( 'Choose template..', 'very-simple-woocommerce-upsell-popup' ),
				'select2_redirect_placeholder' => __( 'Choose behavior..', 'very-simple-woocommerce-upsell-popup' )
				)
			);
        }
    }
}
add_action( 'admin_enqueue_scripts', 'thp_upsell_backend_scripts', 10, 1 );


function thp_search_product_to_upsell() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

		ob_start();

		$thp_search_term = filter_input( INPUT_GET, 'thp_search_term', FILTER_SANITIZE_STRING );
		$thp_current_prod_id    = filter_input( INPUT_GET, 'thp_current_prod_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $thp_search_term ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'posts_per_page' => 50,
			's'              => $thp_search_term,
			'post__not_in'   => array( $thp_current_prod_id )
		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$_product = wc_get_product( get_the_ID() );
				
				$product          = array(
					'id'   => $_product->get_id(),
					'text' => get_the_title() . ' (ID:' . get_the_ID() . ')'
				);
				$found_products[] = $product;
			}
		}
		
		wp_reset_postdata();
		wp_send_json( $found_products );
		die();
}
add_action( 'wp_ajax_thp_search_product_to_upsell', 'thp_search_product_to_upsell' );


function thp_fire_php_include($product_id) {
	
	$wup_options = ( !empty(get_option( 'thp_wup_options' )) ? get_option( 'thp_wup_options' ) : '' );
	/*$global_settings_on = ( !empty($wup_options['global_settings_on']) ? $wup_options['global_settings_on'] : '' );*/
	$individual_settings_on = ( !empty(get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true)) ? get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true) : '' );
	$cat_settings_on = ( !empty($wup_options['cat_settings_on']) ? $wup_options['cat_settings_on'] : '' );
	
	/*if ($global_settings_on) {
		$global_upsell_enabled = ( !empty($wup_options['enable_upsell_global']) ? $wup_options['enable_upsell_global'] : '' );
		
		if (!$global_upsell_enabled)
			return;
	}*/
	if($individual_settings_on){
		if(!empty(get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true))){
			$single_upsell_enabled = get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true);
			
			if (!$single_upsell_enabled)
				return;
		}
	}
	else if ($cat_settings_on) {
		
		$new_cat_ids = array();
		$cat_list_ids = wp_get_post_terms($product_id, 'product_cat', array('fields'=>'ids'));
		
		foreach ($cat_list_ids as $cat_list_id) {
			
			$cat_info = get_term_by('id', $cat_list_id, 'product_cat');
			$cat_name = $cat_info->name;
			
			/*if ($cat_info->parent) {
				$cat_list_id = $cat_info->parent;
				$parent_info = get_term_by('id', $cat_list_id, 'product_cat');
				$cat_name = $parent_info->name;
			}
			echo '<pre>'; print_r($cat_info); die;*/
			$new_cat_ids[$cat_list_id] = $cat_name;
		}
		
		asort($new_cat_ids); //sort by category name in ascending order
		
		reset($new_cat_ids);
		
		$cat_id = key($new_cat_ids); //first category ID 

		$cat_upsell_enabled = ( !empty($wup_options['enable_upsell_cat'.@$cat_id]) ? $wup_options['enable_upsell_cat'.@$cat_id] : '' );
		
		if (!$cat_upsell_enabled) 
			return;
	}else{
		$global_upsell_enabled = ( !empty($wup_options['enable_upsell_global']) ? $wup_options['enable_upsell_global'] : '' );
		
		if (!$global_upsell_enabled)
			return;
	}
	/*else {
		if(!empty(get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true))){
			$single_upsell_enabled = get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true);
			
			if (!$single_upsell_enabled)
				return;
		}
	}*/
	
	$thp_upsell_prod_ids = array();
	$thp_classic_enabled = '';
	$thp_linked_upsells_enabled = '';
	$thp_classic_prodid = get_post_meta($product_id, 'thp_upsell_product_select', true);
	
	if (thp_wuppro_active()) {
		
		if ($individual_settings_on) {
			$thp_linked_upsells_enabled = get_post_meta($product_id, 'thp_wc_linkedprods_upsells', true);
			$thp_classic_enabled = get_post_meta($product_id, 'thp_classic_single_upsell', true);
			
			if ($thp_classic_enabled && $thp_classic_prodid) {
				
				$classic_obj = new WC_Product($thp_classic_prodid);
				
				if ( ( 'publish' == get_post_status ( $thp_classic_prodid ) ) && ( get_post_type( $thp_classic_prodid ) == 'product' ) && ( $classic_obj->is_in_stock() ) ) {
					$thp_upsell_prod_ids[] = $thp_classic_prodid;
				}
			}
		}
		else if ($cat_settings_on) {
			$thp_linked_upsells_enabled = ( !empty($wup_options['linkedprods_upsells_cat'.@$cat_id]) ? $wup_options['linkedprods_upsells_cat'.@$cat_id] : '' );
		}
		else {
			$thp_linked_upsells_enabled = ( !empty($wup_options['linkedprods_upsells_global']) ? $wup_options['linkedprods_upsells_global'] : '' );
			
		}
		
		/*if ($global_settings_on) {
			$thp_linked_upsells_enabled = ( !empty($wup_options['linkedprods_upsells_global']) ? $wup_options['linkedprods_upsells_global'] : '' );
		}
		else if ($cat_settings_on) {
			$thp_linked_upsells_enabled = ( !empty($wup_options['linkedprods_upsells_cat'.@$cat_id]) ? $wup_options['linkedprods_upsells_cat'.@$cat_id] : '' );
		}
		else {
			$thp_linked_upsells_enabled = get_post_meta($product_id, 'thp_wc_linkedprods_upsells', true);
			$thp_classic_enabled = get_post_meta($product_id, 'thp_classic_single_upsell', true);
			
			if ($thp_classic_enabled && $thp_classic_prodid) {
				
				$classic_obj = new WC_Product($thp_classic_prodid);
				
				if ( ( 'publish' == get_post_status ( $thp_classic_prodid ) ) && ( get_post_type( $thp_classic_prodid ) == 'product' ) && ( $classic_obj->is_in_stock() ) ) {
					$thp_upsell_prod_ids[] = $thp_classic_prodid;
				}
			}
		}*/ //end if else
		
		$product_obj = '';
		
		if ($thp_linked_upsells_enabled==1) { 
			$product_obj = new WC_Product($product_id);
			
			$wc_upsell_ids = $product_obj->get_upsell_ids();
			$single_upsell_enabled = get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true);
			
			$global_settings_on = ( !empty($wup_options['global_settings_on']) ? $wup_options['global_settings_on'] : '' );
			$global_upsell_enabled = ( !empty($wup_options['enable_upsell_global']) ? $wup_options['enable_upsell_global'] : '' );
			$list_of_global_upsell_products = ( !empty($wup_options['list_of_global_upsell_products']) ? $wup_options['list_of_global_upsell_products'] : '' );
			
			$cat_settings_on = ( !empty($wup_options['cat_settings_on']) ? $wup_options['cat_settings_on'] : '' );
			$list_of_global_upsell_products_cat = ( !empty($wup_options['list_of_global_upsell_products_cat'.@$cat_id]) ? $wup_options['list_of_global_upsell_products_cat'.@$cat_id] : '' );
			$cat_upsell_enabled = ( !empty($wup_options['enable_upsell_cat'.@$cat_id]) ? $wup_options['enable_upsell_cat'.@$cat_id] : '' );
			
			if ((!empty($wc_upsell_ids)) && (!empty($single_upsell_enabled))) {
				foreach ( $wc_upsell_ids as $upsell_id ) {
					if ( ( get_post_type( $upsell_id ) == 'product' ) && ( 'publish' == get_post_status ( $upsell_id ) ) ) {
						$upsell_obj = new WC_Product($upsell_id);
						if ( $upsell_obj->is_in_stock() )
							$thp_upsell_prod_ids[] = $upsell_id;
					}
					elseif ( get_post_type( $upsell_id ) == 'product_variation' ) {
						$upsell_variation_obj = new WC_Product_Variation($upsell_id);
						
						if ( ($upsell_variation_obj->is_in_stock()) && (wp_get_post_parent_id( $upsell_id ) != false) )
							$thp_upsell_prod_ids[] = $upsell_id;
					}
				}
			}else if((!empty($list_of_global_upsell_products_cat)) && (!empty($cat_upsell_enabled)) && (!empty($cat_settings_on))){
				foreach ( $list_of_global_upsell_products_cat as $upsell_id ) {
					$thp_upsell_prod_ids[] = $upsell_id;
				}
			}else if((!empty($list_of_global_upsell_products)) && (!empty($global_upsell_enabled)) && (!empty($global_settings_on))){ 
				foreach ( $list_of_global_upsell_products as $upsell_id ) {
					$thp_upsell_prod_ids[] = $upsell_id;
				}
			}
		}
		
		if ($thp_linked_upsells_enabled==2) {
			
			$global_upsell_enabled = ( !empty($wup_options['enable_upsell_global']) ? $wup_options['enable_upsell_global'] : '' );
			$global_settings_on = ( !empty($wup_options['global_settings_on']) ? $wup_options['global_settings_on'] : '' );
			$list_of_global_cross_upsell_products = ( !empty($wup_options['list_of_global_cross_upsell_products']) ? $wup_options['list_of_global_cross_upsell_products'] : '' );
			
			$cat_settings_on = ( !empty($wup_options['cat_settings_on']) ? $wup_options['cat_settings_on'] : '' );
			$list_of_global_cross_upsell_products_cat = ( !empty($wup_options['list_of_global_cross_upsell_products_cat'.@$cat_id]) ? $wup_options['list_of_global_cross_upsell_products_cat'.@$cat_id] : '' );
			$cat_upsell_enabled = ( !empty($wup_options['enable_upsell_cat'.@$cat_id]) ? $wup_options['enable_upsell_cat'.@$cat_id] : '' );
			
			if (! is_object($product_obj))
				$product_obj = new WC_Product($product_id);
			
			$wc_crosssell_ids = $product_obj->get_cross_sell_ids();
			$single_upsell_enabled = get_post_meta($product_id, 'thp_wc_enable_upsell_on_single_product', true);
			
			if ((!empty($wc_crosssell_ids)) && (!empty($single_upsell_enabled))) {
				foreach ( $wc_crosssell_ids as $crosssell_id ) {
					if ( ( get_post_type( $crosssell_id ) == 'product' ) && ( 'publish' == get_post_status ( $crosssell_id ) ) ) {
						$crosssell_obj = new WC_Product($crosssell_id);
						if ( $crosssell_obj->is_in_stock() )
							$thp_upsell_prod_ids[] = $crosssell_id;
					}
					elseif ( get_post_type( $crosssell_id ) == 'product_variation' ) {
						$crosssell_variation_obj = new WC_Product_Variation($crosssell_id);
						
						if ( ($crosssell_variation_obj->is_in_stock()) && (wp_get_post_parent_id( $crosssell_id ) != false) )
							$thp_upsell_prod_ids[] = $crosssell_id;
					}
				}
			}else if((!empty($list_of_global_cross_upsell_products_cat)) && (!empty($cat_upsell_enabled)) && (!empty($cat_settings_on))){				
			
				foreach ( $list_of_global_cross_upsell_products_cat as $upsell_id ) {					
				
				$thp_upsell_prod_ids[] = $upsell_id;				
				
				}			
			
			}else if((!empty($list_of_global_cross_upsell_products)) && (!empty($global_upsell_enabled)) && (!empty($global_settings_on))){				
			
				foreach ( $list_of_global_cross_upsell_products as $upsell_id ) {					
				
				$thp_upsell_prod_ids[] = $upsell_id;				
				
				}			
			
			}
		}
		
		$thp_upsell_prod_ids = array_unique($thp_upsell_prod_ids);
	}
	elseif (!thp_wuppro_active()) {
		if ($thp_classic_prodid) {
			
			$classic_obj = new WC_Product($thp_classic_prodid);
			
			if ( ( 'publish' == get_post_status ( $thp_classic_prodid ) ) && ( get_post_type( $thp_classic_prodid ) == 'product' ) && ( $classic_obj->is_in_stock() ) ) {
				$thp_upsell_prod_ids[] = $thp_classic_prodid;
			}
			
			if (! $thp_upsell_prod_ids)
				return;
		}
	}
	
	if ( (! $thp_upsell_prod_ids) && (! $thp_classic_prodid) )
		return;
	
	if (is_ajax()) //reminder: is_ajax is woocommerce function, not wordpress
		$popup = '';
	
	if ( ( sizeof($thp_upsell_prod_ids) == 1 ) || ( $thp_classic_prodid && !$thp_classic_enabled && !$thp_linked_upsells_enabled ) ) { //backward compatibilty
		$found = false;
        
		if ($thp_upsell_prod_ids)
			$single_upsell_id = $thp_upsell_prod_ids[0];
		elseif (!$thp_upsell_prod_ids && $thp_classic_prodid)
			$single_upsell_id = $thp_classic_prodid;
		
        if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
			foreach ( WC()->cart->get_cart() as $cart_item => $values ) {
			    $_product = $values['data'];
			    if ( $_product->get_id() == $single_upsell_id ) {
				    $found = true; //product to upsell found in cart, no need to show popup
				}
			}
		}
		if ( ! $found ) {
			global $woocommerce;
            $woocommerce->session->set_customer_session_cookie(true);
			
			$thp_popup_template = get_post_meta($product_id, 'thp_upsellpopup_template', true);
			
			if (is_ajax())
				ob_start();
			
			if ( ($thp_popup_template) && (thp_wuppro_active()) && (strpos($thp_popup_template, 'protemp') !== false) ) {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-custom.php' );
			}
			elseif ( ($thp_popup_template) && (thp_wuppro_active()) && ($thp_popup_template == 'template5') ) { //will deprecate soon
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template5.php' );
			}
			elseif ( ($thp_popup_template) && ($thp_popup_template != 'template5') && ($thp_popup_template != 'template-default') && (strpos($thp_popup_template, 'protemp') === false) ) {
				include ( 'templates/single/thp-popup-'.$thp_popup_template.'.php' );
			}
			else {
				include ( 'templates/single/thp-popup-template-default.php' );
			}
			
			if (is_ajax())
				$popup = ob_get_clean();
		}
	}
	elseif ( sizeof($thp_upsell_prod_ids) > 1 ) {
		global $woocommerce;
		$woocommerce->session->set_customer_session_cookie(true);
		
		$thp_popup_template = get_post_meta($product_id, 'thp_upsellpopup_template_multiple', true);
		
		if (is_ajax())
			ob_start();
		
		if ($thp_popup_template) {
			
			if ( $thp_popup_template == 'template-default' ) {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-default-mult.php' );
			}
			elseif ($thp_popup_template == 'template-radio') {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-radio-mult.php' );
			}
			elseif ($thp_popup_template == 'template-yesno') {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-yesno-btn-mult.php' );
			}
			elseif ($thp_popup_template == 'template-variable') {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-variable-mult.php' );
			}
			elseif ($thp_popup_template == 'template-fc') {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-fc-mult.php' );
			}
			elseif ($thp_popup_template == 'template-variablefc') {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-variable-fc-mult.php' );
			}
			elseif ( strpos($thp_popup_template, '.php') ) {
				
				$thp_popup_template = filter_var( $thp_popup_template, FILTER_SANITIZE_STRING);
				$thp_popup_template = pathinfo($thp_popup_template, PATHINFO_BASENAME);
				
				if ( file_exists( get_stylesheet_directory() . '/wuppro/multiple/' . $thp_popup_template ) )
					include ( get_stylesheet_directory() . '/wuppro/multiple/' . $thp_popup_template );
				else //if file does not exist
					include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-default-mult.php' );
			}
			else {
				include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-default-mult.php' );
			}
			
		}
		else {
			include ( plugin_dir_path( __DIR__ ).'woocommerce-upsell-popup-pro/templates/thp-popup-template-default-mult.php' );
		}
		
		if (is_ajax())
			$popup = ob_get_clean();
	}

if (is_ajax())
	return $popup;
}


function thp_ajax_popup_trigger() {
	$product_id = filter_input( INPUT_GET, 'prod_id', FILTER_SANITIZE_NUMBER_INT );
	$popup = thp_fire_php_include($product_id);
	
	echo $popup;
	die();
}
add_action('wp_ajax_nopriv_thp_ajax_popup_trigger', 'thp_ajax_popup_trigger');
add_action('wp_ajax_thp_ajax_popup_trigger', 'thp_ajax_popup_trigger');


function thp_popup_form_action_ajax () {
	
	$clicked = filter_input( INPUT_GET, 'clicked', FILTER_SANITIZE_STRING );
	$product_id = filter_input( INPUT_GET, 'prod_id', FILTER_SANITIZE_NUMBER_INT );
	$redirect_url = '';
	
	if ($clicked == 'yes') {
		
		$upsell_product_id = filter_input( INPUT_GET, 'up_pid', FILTER_SANITIZE_NUMBER_INT );
		
		if ( get_post_type( $upsell_product_id ) == 'product' )
			$_product = wc_get_product( $upsell_product_id );
		
		if (( get_post_type( $upsell_product_id ) == 'product_variation' ) || ( $_product->is_type( 'simple' ) ) || ( $_product->is_type( 'course' ) )) {
			//WC()->cart->add_to_cart( $upsell_product_id );
			if(isset($_REQUEST['quality-'.$upsell_product_id.''])){
				WC()->cart->add_to_cart( $upsell_product_id,$_REQUEST['quality-'.$upsell_product_id.''] );
			}else{ 
				WC()->cart->add_to_cart( $upsell_product_id );
			}
			$redirection = get_post_meta($product_id, 'thp_upsellpopup_redirect', true);
			
			if ( $redirection == '2cart' ) {
				$redirect_url = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
			}
			elseif ( $redirection == '3checkout' ) {
				$redirect_url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
			}
		}
		else {
			$redirect_url = get_permalink( $upsell_product_id );
		}
	}
	elseif ($clicked == 'no') {
		
		$redirection = get_post_meta($product_id, 'thp_upsellpopup_redirect_no', true);
		
		if ( $redirection == '2cart' ) {
			$redirect_url = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
		}
		elseif ( $redirection == '3checkout' ) {
			$redirect_url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
		}
	}
	
	echo $redirect_url;
	die();
}
add_action('wp_ajax_nopriv_thp_popup_form_action_ajax', 'thp_popup_form_action_ajax');
add_action('wp_ajax_thp_popup_form_action_ajax', 'thp_popup_form_action_ajax');


function thp_popup_form_action_ajax_mult () {
	
	$product_id = filter_input( INPUT_POST, 'prod_id', FILTER_SANITIZE_NUMBER_INT );
	parse_str( filter_input( INPUT_POST, 'up_pids', FILTER_SANITIZE_STRING ), $up_prod_ids );
	
	foreach($up_prod_ids as $mult_upsell_id => $i){
		foreach($i as $n => $id){
			//WC()->cart->add_to_cart( $id );
			if(isset($_REQUEST['quality-'.$id.''])){ 
				WC()->cart->add_to_cart( $id,$_REQUEST['quality-'.$id.''] );
			}else{ 
				WC()->cart->add_to_cart( $id );
			}
		}
	}
	
	$redirection = get_post_meta($product_id, 'thp_upsellpopup_redirect', true);
	
	if ( $redirection == '2cart' ) {
		$redirect_url = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
	}
	elseif ( $redirection == '3checkout' ) {
		$redirect_url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
	}
	
	echo $redirect_url;
	die();
}
add_action('wp_ajax_nopriv_thp_popup_form_action_ajax_mult', 'thp_popup_form_action_ajax_mult');
add_action('wp_ajax_thp_popup_form_action_ajax_mult', 'thp_popup_form_action_ajax_mult');


function thp_format_wc_price () {
	
	$raw_total = filter_input( INPUT_GET, 'total', FILTER_SANITIZE_STRING );
	
	$formatted_total = wc_price($raw_total);
	
	echo $formatted_total;
	die();
}
add_action('wp_ajax_nopriv_thp_format_wc_price', 'thp_format_wc_price');
add_action('wp_ajax_thp_format_wc_price', 'thp_format_wc_price');


