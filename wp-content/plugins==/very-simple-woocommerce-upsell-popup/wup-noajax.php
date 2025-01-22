<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'thp-wc-upsell-popup-frzn' ) ) {
   die('Undefined constant.');
}

function thp_upsell_popup_trigger($cart_item_key, $product_id) {
	global $thp_addtocart_popup_btn;
	
	if ((! is_ajax()) && ($thp_addtocart_popup_btn != true)) {
		
		global $thp_noajax_popup_html;
		
		ob_start();
		thp_fire_php_include($product_id);
		$thp_noajax_popup_html = ob_get_clean();
	}
}
add_action( 'woocommerce_add_to_cart', 'thp_upsell_popup_trigger', 10, 2 );


function thp_insert_noajax_popup() {
	global $thp_noajax_popup_html;
	echo $thp_noajax_popup_html ? $thp_noajax_popup_html : '';
}
add_action( 'wp_body_open', 'thp_insert_noajax_popup' );

function thp_popup_form_action_single () {
    if ( is_admin() ) {
        return;
    }
	
    if (isset($_POST['thp-popupbutton-yes'])) {
		
		$product_id = filter_var($_POST['thp-upsell-orig-pid'], FILTER_SANITIZE_NUMBER_INT);
		
		$upsell_product_id = filter_var($_POST['thp-upsell-pid'], FILTER_SANITIZE_NUMBER_INT);
		
		$found = false;
           
        if ( sizeof( WC()->cart->get_cart() ) > 0 ) { //double check, just in case
		    foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			    $_product = $values['data'];
			    if ( $_product->get_id() == $upsell_product_id ) {
				    $found = true; //found upsell product in cart
				}
		    }
	    }
		
		if ($found) {
			wc_add_to_cart_message( $product_id );
		} elseif (! $found) { 
			$_product = wc_get_product( $upsell_product_id );
			
			if (( get_post_type( $upsell_product_id ) == 'product_variation' ) || ( $_product->is_type( 'simple' ) ) || ( $_product->is_type( 'course' ) )) {
				
				global $thp_addtocart_popup_btn;
				$thp_addtocart_popup_btn = true;
				
				if(isset($_REQUEST['quality-'.$upsell_product_id.''])){ 
					WC()->cart->add_to_cart( $upsell_product_id,$_REQUEST['quality-'.$upsell_product_id.''] );
				}else{
					WC()->cart->add_to_cart( $upsell_product_id );					
				}
				
				/*WC()->cart->add_to_cart( $upsell_product_id );*/
				
				/*global $woocommerce;$woocommerce->cart->cart_contents[$cart_item_key]['whatever_meta'] = 'testing';
				
				$woocommerce->cart->set_session();   
				
				// when in ajax calls, saves it.global $woocommerce;    
				
				$items = $woocommerce->cart->get_cart();		
				
				echo '<pre>'; print_r($items); die;*/
				
				$redirection = get_post_meta($product_id, 'thp_upsellpopup_redirect', true);
				
				if ( $redirection == '2cart' ) {
					$redirect_url = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
					wp_safe_redirect($redirect_url);
					wc_add_to_cart_message( $upsell_product_id );
					exit;
				}
				elseif ( $redirection == '3checkout' ) {
					$redirect_url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
					wp_safe_redirect($redirect_url);
					wc_add_to_cart_message( $upsell_product_id );
					exit;
				}
				else { //$redirection == '1stay' and everything else
					$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
					$current_page = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					$redirect_url = strtok($current_page, "?");
					//self-note: improve this redirect url for sites that still use plain permalink
					
					wp_safe_redirect($redirect_url);
					
					if ($upsell_product_id)
						wc_add_to_cart_message( $upsell_product_id );
					
					exit;
				}
			}
			else {
				wp_safe_redirect( get_permalink( $upsell_product_id ) );
				exit;
			}				
		}
    }
    elseif ( (isset($_POST['thp-popupbutton-no'])) || (isset($_POST['thp-close-btn-flag'])) ) {
		
		if ( isset($_POST['thp-upsell-orig-pid']) )
			$product_id = filter_var($_POST['thp-upsell-orig-pid'], FILTER_SANITIZE_NUMBER_INT);
		elseif ( isset($_POST['thp-orig-pid-mult']) )
			$product_id = filter_var($_POST['thp-orig-pid-mult'], FILTER_SANITIZE_NUMBER_INT);
		
		$redirection = get_post_meta($product_id, 'thp_upsellpopup_redirect_no', true);
		
		if ( $redirection == '2cart' ) {
			$redirect_url = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
			wp_safe_redirect($redirect_url);
			exit;
		}
		elseif ( $redirection == '3checkout' ) {
			$redirect_url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
			wp_safe_redirect($redirect_url);
			exit;
		}
		else {
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
			$current_page = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$redirect_url = strtok($current_page, "?");
			//self-note: improve this redirect url for sites that still use plain permalink
			
			wp_safe_redirect($redirect_url);
			exit;
		}
    }
}
add_action( 'wp_loaded', 'thp_popup_form_action_single' );


function thp_popup_form_action_mult () {
	if ( is_admin() )
        return;
	
	if (isset($_POST['thp-addcheckedtocart-btn'])) {
	
		$product_id = filter_var($_POST['thp-orig-pid-mult'], FILTER_SANITIZE_NUMBER_INT);
		
		$up_prod_ids = filter_input(INPUT_POST, 'mult_upsell_id', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
		
		$redirection = get_post_meta($product_id, 'thp_upsellpopup_redirect', true);
		
		$showmsg = false;
		
		if ( $redirection == '2cart' ) {
			$redirect_url = get_permalink( get_option( 'woocommerce_cart_page_id' ) );
		}
		elseif ( $redirection == '3checkout' ) {
			$redirect_url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) );
		}
		else {
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
			$current_page = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$current_page = filter_var($current_page, FILTER_VALIDATE_URL); //url validation
			$redirect_url = strtok($current_page, "?");
			$showmsg = true;
		}
		
		global $thp_addtocart_popup_btn;
		$thp_addtocart_popup_btn = true;
		
		WC()->cart->get_cart(); //access cart first to make sure add_to_cart does not skip first item
		
		for ( $n = 0; $n < count($up_prod_ids); $n++ ) {
			if(isset($_REQUEST['quality-'.$up_prod_ids[$n].''])){ 
				WC()->cart->add_to_cart( $up_prod_ids[$n],$_REQUEST['quality-'.$up_prod_ids[$n].''] );
			}else{
				WC()->cart->add_to_cart( $up_prod_ids[$n] );
			}
		}
		
		if (!headers_sent()) {
			wp_safe_redirect($redirect_url);
			if (! $showmsg)
				exit;
		}
		else {
			$redirect_url = filter_var($redirect_url, FILTER_SANITIZE_URL);
			
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$redirect_url.'";';
			echo '</script>';
			if (! $showmsg) {
				exit;
			}
		}
		
		if ($showmsg) {
			for ( $n = 0; $n < count($up_prod_ids); $n++ ) {
				wc_add_to_cart_message( $up_prod_ids[$n] );
			}
		exit;
		}
	}
}
add_action( 'wp_loaded', 'thp_popup_form_action_mult' );