<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'thp-wc-upsell-popup-frzn' ) ) {
   die('Undefined constant.');
}


add_action( 'load-post.php', 'thp_wcproduct_upsell_meta_box_setup' );
add_action( 'load-post-new.php', 'thp_wcproduct_upsell_meta_box_setup' );


function thp_wcproduct_upsell_meta_box_setup() {
	add_action( 'add_meta_boxes', 'thp_wcproduct_add_upsell_meta_box' );
	add_action( 'save_post', 'thp_wcproduct_save_upsell_meta', 10, 2 );
}


function thp_wcproduct_add_upsell_meta_box() {
	
	if (thp_wuppro_active()) {
		add_meta_box(
			'thp-wuppro-upsell-conf',      // ID
			esc_html__( 'Upsell Popup Configuration', 'very-simple-woocommerce-upsell-popup' ),    // title
			'thp_wuppro_product_upsell_metabox',   // callback function
			'product',         // screen
			'normal',         // context
			'default'         // priority
		);
	}
	else {
		add_meta_box(
			'thp-wc-single-product-upsell',      // ID
			esc_html__( 'Upsell Popup Configuration', 'very-simple-woocommerce-upsell-popup' ),    // title
			'thp_wc_single_product_upsell_metabox',   // callback function
			'product',         // screen
			'side',         // context
			'default'         // priority
		);
	}
}


function thp_wcproduct_save_upsell_meta( $post_id, $post ) {

	if ( !isset( $_POST['thp_wc_upsell_nonce'] ) || !wp_verify_nonce( $_POST['thp_wc_upsell_nonce'], 'thp-update-metabx-action' ) )
		return $post_id;
	
	$post_type = get_post_type_object( $post->post_type );
	
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
	
	if ( !current_user_can( 'manage_woocommerce' ) )
		return $post_id;
	
	
	$thp_new_enable_upsell_value = ( isset( $_POST['thp_wc_enable_upsell_on_single_product'] ) ? sanitize_html_class( $_POST['thp_wc_enable_upsell_on_single_product'] ) : '' );		
	
	$thp_wc_offer_upsell_on_product = ( isset( $_POST['thp_wc_offer_upsell_on_product'] ) ? sanitize_html_class( $_POST['thp_wc_offer_upsell_on_product'] ) : '' );

	$offer_coupon_code = ( isset( $_POST['offer_coupon_code'] ) ? sanitize_html_class( $_POST['offer_coupon_code'] ) : '' );		
	
	$offer_heading = ( isset( $_POST['offer_heading'] ) ? sanitize_html_class( $_POST['offer_heading'] ) : '' );
	
	$thp_new_linkedprods_upsells = ( isset( $_POST['thp_wc_linkedprods_upsells'] ) ? sanitize_html_class( $_POST['thp_wc_linkedprods_upsells'] ) : '' );
	
	$thp_new_linkedprods_crosssells = ( isset( $_POST['thp_wc_linkedprods_crosssells'] ) ? sanitize_html_class( $_POST['thp_wc_linkedprods_crosssells'] ) : '' );
	
	$thp_new_classic_single_upsell = ( isset( $_POST['thp_classic_single_upsell'] ) ? sanitize_html_class( $_POST['thp_classic_single_upsell'] ) : '' );
	
	$thp_new_upsell_prod_value = ( isset( $_POST['thp_upsell_product_select'] ) ? sanitize_html_class( $_POST['thp_upsell_product_select'] ) : '' );
	
	$thp_new_template = ( isset( $_POST['thp_upsellpopup_template'] ) ? sanitize_html_class( $_POST['thp_upsellpopup_template'] ) : '' );
	
	$thp_new_template_multiple = ( isset( $_POST['thp_upsellpopup_template_multiple'] ) ? filter_input(INPUT_POST, 'thp_upsellpopup_template_multiple', FILTER_SANITIZE_STRING) : '' );
	
	$thp_new_template_multiple = pathinfo($thp_new_template_multiple, PATHINFO_BASENAME);
	
	$thp_new_redirect = ( isset( $_POST['thp_upsellpopup_redirect'] ) ? sanitize_html_class( $_POST['thp_upsellpopup_redirect'] ) : '' );
	
	$thp_new_redirect_no = ( isset( $_POST['thp_upsellpopup_redirect_no'] ) ? sanitize_html_class( $_POST['thp_upsellpopup_redirect_no'] ) : '' );
	
	$thp_new_enable_ctl = ( isset( $_POST['thp_enable_completethelook'] ) ? sanitize_html_class( $_POST['thp_enable_completethelook'] ) : '' );
	
	$thp_new_ctl_img = strip_tags( isset( $_POST['thp_ctl_img'] ) ? filter_var($_POST['thp_ctl_img'], FILTER_SANITIZE_URL) : '' );
	
	$thp_new_ctl_img = str_replace('"', '', $thp_new_ctl_img);
	
	if ($thp_new_ctl_img) {
		
		$filetype = wp_check_filetype($thp_new_ctl_img);
		
		if ( ($filetype['ext'] != 'jpeg') && ($filetype['ext'] != 'jpg') && ($filetype['ext'] != 'png') && ($filetype['ext'] != 'gif') )
			$thp_new_ctl_img = '';
	}
	
	$new_values = array(
		"thp_wc_enable_upsell_on_single_product" => $thp_new_enable_upsell_value,				
		"thp_wc_offer_upsell_on_product" => $thp_wc_offer_upsell_on_product,				
		"offer_coupon_code" => $offer_coupon_code,				
		"offer_heading" => $offer_heading,
		"thp_wc_linkedprods_upsells" => $thp_new_linkedprods_upsells,
		"thp_wc_linkedprods_crosssells" => $thp_new_linkedprods_crosssells,
		"thp_classic_single_upsell" => $thp_new_classic_single_upsell,
		"thp_upsell_product_select" => $thp_new_upsell_prod_value,
		"thp_upsellpopup_template" => $thp_new_template,
		"thp_upsellpopup_template_multiple" => $thp_new_template_multiple,
		"thp_upsellpopup_redirect" => $thp_new_redirect,
		"thp_upsellpopup_redirect_no" => $thp_new_redirect_no,
		"thp_enable_completethelook" => $thp_new_enable_ctl,
		"thp_ctl_img" => $thp_new_ctl_img
	);
	
	foreach ($new_values as $key => $new_value) {
		$old_value = get_post_meta( $post_id, $key, true );
		
		if ( $new_value && '' == $old_value ) {
			add_post_meta( $post_id, $key, $new_value, true );
		}
		elseif ( $new_value && $new_value != $old_value ) {
			update_post_meta( $post_id, $key, $new_value );
		}
		elseif ( '' == $new_value && $old_value ) {
			if ( (!thp_wuppro_active()) && (($key == 'thp_wc_linkedprods_upsells') || ($key == 'thp_wc_linkedprods_crosssells') || ($key == 'thp_classic_single_upsell') || ($key == 'thp_upsellpopup_template_multiple') || ($key == 'thp_enable_completethelook') || ($key == 'thp_ctl_img')) ) {
				//do nothing
			}
			else {
				delete_post_meta( $post_id, $key, $old_value );
			}
		}
	}
}


function thp_wc_single_product_upsell_metabox( $object, $box ) {

	wp_nonce_field( 'thp-update-metabx-action', 'thp_wc_upsell_nonce' );
	
	_e( "Enable upsell on this product? :", 'very-simple-woocommerce-upsell-popup' ); ?>
	
	<input type="checkbox" name="thp_wc_enable_upsell_on_single_product" value="1" <?php checked( '1', get_post_meta( $object->ID, 'thp_wc_enable_upsell_on_single_product', true ) ); ?>><?php _e( "Yes", 'very-simple-woocommerce-upsell-popup' ); ?>
	
	<?php
	echo '<br /><br />';
	_e( "Which product to upsell? :", 'very-simple-woocommerce-upsell-popup' );
	echo '<br />'; ?>
	
	<select class="thp-upsell-product-select" name="thp_upsell_product_select">
	<?php 
	$value = get_post_meta($object->ID, 'thp_upsell_product_select', true); 
	if ($value)
		echo '<option selected="selected" value="'.$value.'">'.get_the_title( $value ).' (ID:'.$value.')</option>';
	?>
	</select>
	
	<div style="padding: 18px 5px 3px; font-style: italic;">
		<?php
		$pro_url = 'https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/';
		$upgrade_link1 = sprintf( wp_kses( __( 'Upselling multiple products? <a href="%s">Upgrade to PRO</a>!', 'very-simple-woocommerce-upsell-popup' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $pro_url ) );
		echo $upgrade_link1;
		?>
	</div>
	
	<?php
	echo '<br />';
	_e( "Redirection behavior after upsell product is added to cart:", 'very-simple-woocommerce-upsell-popup' );
	echo '<br />'; ?>
	
	<select class="thp-upsellpopup-redirect" style="width: 100%;" name="thp_upsellpopup_redirect">
	<?php 
	$redirect = get_post_meta($object->ID, 'thp_upsellpopup_redirect', true); 
	if ($redirect)
		echo '<option selected="selected" value="'.$redirect.'">';
			if ($redirect == "1stay")
				_e( "Stay on page", 'very-simple-woocommerce-upsell-popup' );
			elseif ($redirect == "2cart")
				_e( "Redirect to cart", 'very-simple-woocommerce-upsell-popup' );
			elseif ($redirect == "3checkout")
				_e( "Redirect to checkout", 'very-simple-woocommerce-upsell-popup' );
		echo '</option>';
	?>
		<option></option>
		<option value="1stay"><?php _e( "Stay on page", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="2cart"><?php _e( "Redirect to cart", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="3checkout"><?php _e( "Redirect to checkout", 'very-simple-woocommerce-upsell-popup' ); ?></option>
	</select>
	
	<?php
	echo '<br /><br />';
	_e( "Redirection behavior if the NO button is clicked:", 'very-simple-woocommerce-upsell-popup' );
	echo '<br />'; ?>
	
	<select class="thp-upsellpopup-redirect-no" style="width: 100%;" name="thp_upsellpopup_redirect_no">
	<?php 
	$redirect_nobtn = get_post_meta($object->ID, 'thp_upsellpopup_redirect_no', true); 
	if ($redirect_nobtn)
		echo '<option selected="selected" value="'.$redirect_nobtn.'">';
			if ($redirect_nobtn == "1stay")
				_e( "Stay on page", 'very-simple-woocommerce-upsell-popup' );
			elseif ($redirect_nobtn == "2cart")
				_e( "Redirect to cart", 'very-simple-woocommerce-upsell-popup' );
			elseif ($redirect_nobtn == "3checkout")
				_e( "Redirect to checkout", 'very-simple-woocommerce-upsell-popup' );
		echo '</option>';
	?>
		<option></option>
		<option value="1stay"><?php _e( "Stay on page", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="2cart"><?php _e( "Redirect to cart", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="3checkout"><?php _e( "Redirect to checkout", 'very-simple-woocommerce-upsell-popup' ); ?></option>
	</select>
	
	<?php
	echo '<br /><br />';
	_e( "Popup template :", 'very-simple-woocommerce-upsell-popup' );
	echo '<br />'; ?>
	
	<select class="thp-upsellpopup-template" style="width: 100%;" name="thp_upsellpopup_template">
	<?php 
	$template = get_post_meta($object->ID, 'thp_upsellpopup_template', true); 
	if ($template)
		echo '<option selected="selected" value="'.$template.'">';
			if ($template == "template-default")
				_e( "Template - Default", 'very-simple-woocommerce-upsell-popup' );
			elseif ($template == "template1")
				_e( "Template - Basic", 'very-simple-woocommerce-upsell-popup' );
			elseif ($template == "template2")
				_e( "Template - Colorful", 'very-simple-woocommerce-upsell-popup' );
			elseif ($template == "template3")
				_e( "Template - Dark on Light", 'very-simple-woocommerce-upsell-popup' );
			elseif ($template == "template4")
				_e( "Template - No CSS", 'very-simple-woocommerce-upsell-popup' );
			elseif ($template == "template6")
				_e( "Template - Variable Upsell", 'very-simple-woocommerce-upsell-popup' );
			else
				_e( "Please upgrade to PRO", 'very-simple-woocommerce-upsell-popup' );
		echo '</option>';
	?>
		<option value="template-default"><?php _e( "Template - Default", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="template1"><?php _e( "Template - Basic", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="template2"><?php _e( "Template - Colorful", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="template3"><?php _e( "Template - Dark on Light", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="template4"><?php _e( "Template - No CSS", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="template6"><?php _e( "Template - Variable Upsell", 'very-simple-woocommerce-upsell-popup' ); ?></option>
		<option value="template5" disabled>
			<?php _e( "Custom Template (PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?>
		</option>
	</select>
	
	<div style="padding: 18px 5px 3px; font-style: italic;">
		<?php
		$upgrade_link2 = sprintf( wp_kses( __( 'Create your own popup templates, <a href="%s">upgrade to PRO</a>!', 'very-simple-woocommerce-upsell-popup' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $pro_url ) );
		echo $upgrade_link2;
		?>
	</div>
	
	<div class="thp-target-product-id" style="display:none;"><?php echo $object->ID; ?></div>
	
<?php
}