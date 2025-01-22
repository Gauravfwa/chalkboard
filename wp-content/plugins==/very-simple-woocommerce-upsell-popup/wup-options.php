<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'thp-wc-upsell-popup-frzn' ) ) {
   die('Undefined constant.');
}

function admin_style() {
  wp_enqueue_style('admin-styles', plugins_url('css/admin.css',__FILE__ ));
}
add_action('admin_enqueue_scripts', 'admin_style');

function thp_wup_admin_settings_menu() {
	add_submenu_page( 'woocommerce', __( 'Woocommerce Upsell Popup Settings', 'very-simple-woocommerce-upsell-popup' ), __( 'Upsell Popup', 'very-simple-woocommerce-upsell-popup' ), 'manage_woocommerce', 'thp-wup-main-settings', 'thp_wup_main_settings' );
}
add_action( 'admin_menu', 'thp_wup_admin_settings_menu', 99 );


function thp_wup_main_settings() {
	
	if (isset($_POST['action'])) {
		if ( $_POST['action'] === 'update-wup-options' ) {
			thp_wup_main_settings_handler();
		}
		else if (( $_POST['action'] === 'update-wuppro-options' ) && ( thp_wuppro_active() )) {
			thp_wuppro_handler();
		}
		else {
			_e( "Error. Your changes might not be saved, please try again.", 'very-simple-woocommerce-upsell-popup' );
		}
	}
	
?>

<div class="wrap">
	
	<div class="service_section"><div class="service_box"><h3>Wordpress Customization Service</h3><p>Add more custom features and designs to improve conversion</p><a target="_blank" rel="nofollow noopener" href="https://woocommerce.upsellpopup.com/wordpress-customization-service/" class="learn_more" title="Learn More" data-wpel-link="external">Learn More</a></div><div class="service_box"><h3>SEO Services</h3><p>Increase your ranking and traffic through on page and off page services</p><a target="_blank" rel="nofollow noopener" href="https://woocommerce.upsellpopup.com/seo-services/" class="learn_more" title="Learn More" data-wpel-link="external">Learn More</a></div><div class="service_box"><h3>Content Writing & Virtual Assistant service</h3><p>Consistent content keeps your visitors engaged and drive more traffic</p><a target="_blank" rel="nofollow noopener" href="https://woocommerce.upsellpopup.com/content-writing-virtual-assistant-service/" class="learn_more" title="Learn More" data-wpel-link="external">Learn More</a></div></div>
	
	<h1><?php _e( "Woocommerce Upsell Popup Settings", 'very-simple-woocommerce-upsell-popup' ); ?></h1>
	
	<?php
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
	
	if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = $_GET[ 'tab' ];
	}
	?>
	
	<nav class="nav-tab-wrapper">
		<a href="?page=thp-wup-main-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>"><?php _e( "General", 'very-simple-woocommerce-upsell-popup' ); ?></a>
		<a href="?page=thp-wup-main-settings&tab=pro" class="nav-tab <?php echo $active_tab == 'pro' ? 'nav-tab-active' : ''; ?>"><?php _e( "PRO", 'very-simple-woocommerce-upsell-popup' ); ?></a>
		<a href="?page=thp-wup-main-settings&tab=documentation" class="nav-tab <?php echo $active_tab == 'documentation' ? 'nav-tab-active' : ''; ?>"><?php _e( "Documentation &amp; Support", 'very-simple-woocommerce-upsell-popup' ); ?></a>
	</nav>
	
	<?php
	if ($active_tab == 'general') {
	?>
		
		<form name="thp_wup_main_settings_form" method="post" action="">
		
		<?php wp_nonce_field('update-options', 'wup_main_settings_nonce');
		
		$thp_wup_options = get_option( 'thp_wup_options' );
		
		if (empty($thp_wup_options))
			$thp_wup_options = (array) null;
		
		$keys = array_keys($thp_wup_options);
		$wup_keys = array('ajax_off', 'use_wc_ajax', 'global_settings_on', 'enable_upsell_global', 'linkedprods_upsells_global', 'cat_settings_on','global_offer_on','global_offer_heading','list_of_global_upsell_products','list_of_global_cross_upsell_products','global_product_quality_on','global_coupon_code','global_background_button_color','global_coupon_border_color','global_coupon_text_color','global_coupon_background_color','global_text_button_color','global_hover_background_button_color');
		
		foreach($wup_keys as $wup_key){
			if (in_array($wup_key, $keys)) continue;  //already set
			$thp_wup_options[$wup_key] = '';
		}
		
		?>
		
			<h3><?php _e( "General", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
			
			<p><?php _e( "AJAX is enabled by default for Add To Cart buttons on single product pages. If you're having problems with AJAX or something is not working, you can turn it off below.", 'very-simple-woocommerce-upsell-popup' ); ?></p>
			
			<table class="form-table">
			<tbody>
			
				<tr>
				<th scope="row"><?php _e( "Add To Cart Button Behavior:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
				<td>
					<label for="thp_wup_options[ajax_off]">
						<input type="checkbox" id="thp_wup_options[ajax_off]" name="thp_wup_options[ajax_off]" value="1" <?php checked( '1', $thp_wup_options['ajax_off'] ); ?>>
						
						<?php _e( "Turn off AJAX on single product pages?", 'very-simple-woocommerce-upsell-popup' ); ?>
					</label>
				</td>
				</tr>
				
			</tbody>
			</table>
			
			<p>
			<?php
			$url = 'https://wordpress.org/plugins/woo-ajax-add-to-cart/';
			
			$info_string = sprintf( wp_kses( __( 'By default, we use <a href="%s" target="_blank" rel="noopener noreferrer">QuadLayers</a> AJAX add to cart script  on single product pages. If it causes issues on your site, you can switch to Woocommerce native handler below.', 'very-simple-woocommerce-upsell-popup' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ), 'br' => array() ) ), esc_url( $url ) );
			
			echo $info_string;
			
			?>
			</p>
			
			<table class="form-table">
			<tbody>
				
				<tr>
				<th scope="row"><?php _e( "AJAX Script to Use:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
				<td>
					<label for="thp_wup_options[use_wc_ajax]">
						<input type="checkbox" id="thp_wup_options[use_wc_ajax]" name="thp_wup_options[use_wc_ajax]" value="1" <?php checked( '1', $thp_wup_options['use_wc_ajax'] ); ?>>
						
						<?php _e( "Switch to Woocommerce native handler", 'very-simple-woocommerce-upsell-popup' ); ?>
					</label>
				</td>
				</tr>
				
			</tbody>
			</table>
			
			<style>			
			
			.form_area{width:100%;float:left;}			
			
			.left_area{width:50%;float:left;}			
			
			.right_area{width:50%;float:left;margin-top:15px;}			
			
			</style>	

			<?php if ( thp_wuppro_active() ) { ?>
			
			<h3 style="border-top:1px solid #ccc; padding-top:25px;float:left;width:100%;"><?php _e( "Global Color Settings", 'very-simple-woocommerce-upsell-popup' ); ?></h3>						
			
			<p><?php _e( "Change the color of the popup button and coupon from here.", 'very-simple-woocommerce-upsell-popup' ); ?></p>					
			
			<div class="form_area">			
			
			<div class="left_area">			
			
			<table class="form-table">			
			
			<tbody>							
			
			<tr>				
			
			<th scope="row"><?php _e( "Popup Button Background Color:", 'very-simple-woocommerce-upsell-popup' ); ?></th>				
			
			<td>					
			<label for="thp_wup_options[global_background_button_color]"><input type="text" value="<?php if(!empty($thp_wup_options['global_background_button_color'])){ echo $thp_wup_options['global_background_button_color']; }else{ } ?>" name="thp_wup_options[global_background_button_color]" class="my-color-field" data-default-color="#effeff" /></label>				
			</td>

			</tr>								
			
			<tr>				
			
			<th scope="row"><?php _e( "Popup Button Hover Background Color:", 'very-simple-woocommerce-upsell-popup' ); ?></th>

			<td>					
			
			<label for="thp_wup_options[global_hover_background_button_color]"><input type="text" value="<?php if(!empty($thp_wup_options['global_hover_background_button_color'])){ echo $thp_wup_options['global_hover_background_button_color']; }else{ } ?>" name="thp_wup_options[global_hover_background_button_color]" class="my-color-field" data-default-color="#effeff" /></label>				
			
			</td>				
			
			</tr>								
			
			<tr>				
			
			<th scope="row"><?php _e( "Popup Button Text Color:", 'very-simple-woocommerce-upsell-popup' ); ?></th>

			<td><label for="thp_wup_options[global_text_button_color]"><input type="text" value="<?php if(!empty($thp_wup_options['global_text_button_color'])){ echo $thp_wup_options['global_text_button_color']; }else{ } ?>" name="thp_wup_options[global_text_button_color]" class="my-color-field" data-default-color="#effeff" /></label></td>

			</tr>								
			
			<tr>				
			
			<th scope="row"><?php _e( "Popup Coupon Background Color:", 'very-simple-woocommerce-upsell-popup' ); ?></th>				
			
			<td>
			<label for="thp_wup_options[global_coupon_background_color]">
			<input type="text" value="<?php if(!empty($thp_wup_options['global_coupon_background_color'])){ echo $thp_wup_options['global_coupon_background_color']; }else{ } ?>" name="thp_wup_options[global_coupon_background_color]" class="my-color-field" data-default-color="#effeff" />
			</label>
			</td>				
			
			</tr>								
			
			<tr>
			<th scope="row"><?php _e( "Popup Coupon Text Color:", 'very-simple-woocommerce-upsell-popup' ); ?></th>

			<td><label for="thp_wup_options[global_coupon_text_color]"><input type="text" value="<?php if(!empty($thp_wup_options['global_coupon_text_color'])){ echo $thp_wup_options['global_coupon_text_color']; }else{ } ?>" name="thp_wup_options[global_coupon_text_color]" class="my-color-field" data-default-color="#effeff" /></label></td>				
			
			</tr>								
			
			<tr>
			<th scope="row"><?php _e( "Popup Coupon Border Color:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
			
			<td><label for="thp_wup_options[global_coupon_border_color]"><input type="text" value="<?php if(!empty($thp_wup_options['global_coupon_border_color'])){ echo $thp_wup_options['global_coupon_border_color']; }else{ } ?>" name="thp_wup_options[global_coupon_border_color]" class="my-color-field" data-default-color="#effeff" /></label>
			
			</td>				
			
			</tr>
			
			</tbody>
			
			</table>
			
			</div>
			
			</div>
			
			<?php }else{ ?>
			
			<div class="form_area">
			<div class="left_area">
			<table class="form-table">
			<tbody>
			
				<tr>
				<td>
				<div style="padding: 20px;background: #fff;text-align: center;font-weight: bold;"><?php if(!thp_wuppro_active()){ ?><a href="https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/" target="_blank" rel="noopener"><?php _e( "Upgrade to PRO to unlock more features!", 'very-simple-woocommerce-upsell-popup' ); ?></a><?php } ?>
				</div>
				<img src="<?php echo get_home_url().'/wp-content/plugins/very-simple-woocommerce-upsell-popup/templates/images/color.png' ?>"  /></td>
				</tr>
				
			</tbody>
			</table>
			</div>
			</div>
			
			<?php } ?>
			
			<h3 style="border-top:1px solid #ccc; padding-top:25px;float:left;width:100%;"><?php _e( "Global Settings", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
			<p><?php _e( "If global settings are enabled, all other settings will be overridden. If both global settings and product category settings are enabled, global settings will still be prioritized.", 'very-simple-woocommerce-upsell-popup' ); ?></p>
			<div class="form_area">
			<div class="left_area">
			<table class="form-table">
			<tbody>
			
				<tr>
				<th scope="row">
				<?php _e( "Settings Priority:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
				<td>
					<label for="thp_wup_options[global_settings_on]">
					<input type="checkbox" id="thp_wup_options[global_settings_on]" name="thp_wup_options[global_settings_on]" value="1" <?php checked( '1', $thp_wup_options['global_settings_on'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
					<?php _e( "Use global settings?", 'very-simple-woocommerce-upsell-popup' ); ?>
					</label>
				</td>
				</tr>
			<tr>			
			
			<th scope="row"><?php _e( "Discount/Coupon Offer", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"> <?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?><a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?></th>

			<td>							
			<label for="thp_wup_options[global_offer_on]">					
			<input type="checkbox" name="thp_wup_options[global_offer_on]" value="1" <?php @checked( '1', $thp_wup_options['global_offer_on'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?> /> <?php _e( "Enable global setting for all products", 'very-simple-woocommerce-upsell-popup' ); ?>
			</td>
			</tr>						
			
			<tr>			
			
			<th scope="row"><?php _e( "Offer heading?", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"> <?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?><a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?></th>			
			
			<td>							
			<label for="thp_wup_options[global_offer_heading]">				
			<input type="text" name="thp_wup_options[global_offer_heading]" value="<?php if(!empty($thp_wup_options['global_offer_heading'])){ echo $thp_wup_options['global_offer_heading']; }else{} ?>" <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?> />			
			
			</td>	
			</tr>
			
			<tr>			
			
			<th scope="row"><?php _e( "Offer Coupon Code?", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"> <?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?><a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?></th>			
			
			<td>							
			<label for="thp_wup_options[global_coupon_code]">				
			<input type="text" name="thp_wup_options[global_coupon_code]" value="<?php if(!empty($thp_wup_options['global_coupon_code'])){ echo $thp_wup_options['global_coupon_code']; }else{} ?>" <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?> />			
			
			</td>	
			</tr>
			
			<tr>				
				<th scope="row"><?php _e( "Default product quantity setting:", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"> <?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup'); ?> <a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?></th>			
				<td>					
				<label for="thp_wup_options[global_product_quality_on]"> <input type="checkbox" id="thp_wup_options[global_product_quality_on]" name="thp_wup_options[global_product_quality_on]" value="1" <?php checked( '1', @$thp_wup_options['global_product_quality_on'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>> <?php _e( "Add multiple quantity option to upsell products?", 'very-simple-woocommerce-upsell-popup' ); ?> </label>	</td>				
			</tr> 
				
			<tr>
				<th scope="row">
				<?php _e( "List of global upsell products ? :", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"> <?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?><a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?></th>
			<td>
			<div class="meta-box-sortables ui-sortable postbox " id="thp-wc-single-product-upsell" style="width:200px;">

			<select class="thp-upsell-product-select" multiple="" style="width: 50%;" id="thp_wup_options[list_of_global_upsell_products][]" name="thp_wup_options[list_of_global_upsell_products][]" data-placeholder="Search for a product…" data-action="woocommerce_json_search_products_and_variations" data-exclude="107" tabindex="-1" aria-hidden="true" <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
				
				<?php

				if($thp_wup_options['list_of_global_upsell_products']){
					
					foreach ($thp_wup_options['list_of_global_upsell_products'] as $product) { 
						
							echo '<option selected="selected" value="'.$product.'">'.get_the_title( $product ).' (ID:'.$product.')</option>';
					
					}
					
				}else{
					
					$products = get_posts(array('numberposts' => 1,'post_status' => 'publish','post_type' => 'product'));
					
					if(is_array($products)){
						
						foreach ($products as $product) {
							
							if($product){
								
								echo '<option value="'.$product->ID.'">'.get_the_title( $product->ID ).' (ID:'.$product->ID.')</option>';
							
							}
							
						}
					
					}
				
				}
				?>

				</select>
				</div>
				</td>
				</tr>
				
				
				<tr>
				<th scope="row">
				<?php _e( "List of global cross-sells products ? :", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"> <?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?><a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?></th>
			<td>
			<div class="meta-box-sortables ui-sortable postbox " id="thp-wc-single-product-cross-upsell" style="width:200px;">

			<select class="thp-upsell-product-select" multiple="" style="width: 100%;" id="thp_wup_options[list_of_global_cross_upsell_products][]" name="thp_wup_options[list_of_global_cross_upsell_products][]" data-placeholder="Search for a product…" data-action="woocommerce_json_search_products_and_variations" data-exclude="107" tabindex="-1" aria-hidden="true" <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
				
				<?php

				if($thp_wup_options['list_of_global_cross_upsell_products']){
					
					foreach ($thp_wup_options['list_of_global_cross_upsell_products'] as $product) { 
						
							echo '<option selected="selected" value="'.$product.'">'.get_the_title( $product ).' (ID:'.$product.')</option>';
					
					}
					
				}else{
					
					$products = get_posts(array('numberposts' => 1,'post_status' => 'publish','post_type' => 'product'));
					
					if(is_array($products)){
						
						foreach ($products as $product) {
							
							if($product){
								
								echo '<option value="'.$product->ID.'">'.get_the_title( $product->ID ).' (ID:'.$product->ID.')</option>';
							
							}
							
						}
					
					}
				
				}
				?>

				</select>
				</div>
				</td>
				</tr>
				
				<tr>
				<th scope="row"><?php _e( "Popup activation:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
				<td>
					<label for="thp_wup_options[enable_upsell_global]">
					<input type="checkbox" id="thp_wup_options[enable_upsell_global]" name="thp_wup_options[enable_upsell_global]" value="1" <?php checked( '1', $thp_wup_options['enable_upsell_global'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
					<?php _e( "Enable upsell & cross-sells popup on all products?", 'very-simple-woocommerce-upsell-popup' ); ?>
					</label>
				</td>
				</tr>
				
				<tr>
				<th scope="row">
						<?php _e( "Where to get upsell products from?", 'very-simple-woocommerce-upsell-popup' ); ?><?php if(!thp_wuppro_active()){ ?><span style="font-weight: normal;font-size: small;"><?php _e( "(PRO only)", 'very-simple-woocommerce-upsell-popup' ); ?><a href='https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/' target='_blank' rel='noopener'> <?php _e( 'Upgrade to PRO to unlock this features!', 'very-simple-woocommerce-upsell-popup' ); ?></a></span><?php } ?>
					</th>
				<td>
					<fieldset>
					<label for="thp_wup_options[linkedprods_upsells_global]">
					
						<input type="radio" name="thp_wup_options[linkedprods_upsells_global]" value="1" <?php checked( '1', $thp_wup_options['linkedprods_upsells_global'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
						
						<?php _e( "Woocommerce Linked Products &gt; Upsells", 'very-simple-woocommerce-upsell-popup' ); ?>
					
					</label>
					</fieldset>
					
					<fieldset>
					<label for="thp_wup_options[linkedprods_upsells_global]">
						
						<input type="radio" name="thp_wup_options[linkedprods_upsells_global]" value="2" <?php checked( '2', $thp_wup_options['linkedprods_upsells_global'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
						
						<?php _e( "Woocommerce Linked Products &gt; Cross-sells", 'very-simple-woocommerce-upsell-popup' ); ?>
					
					</label>
					</fieldset>
				</td>
				</tr>
				
			</tbody>
			</table>
			</div>
			<div class="right_area">
			<img src="<?php echo get_home_url().'/wp-content/plugins/very-simple-woocommerce-upsell-popup/templates/images/screenshort.png' ?>" width="650" />
			</div>
			</div>
			<h3 style="border-top:1px solid #ccc; padding-top:25px;float:left;width:100%;"><?php _e( "Product Category Settings", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
			
			<p><?php _e( "If both global settings and product category settings are enabled, global settings will be prioritized. If a product has more than one category, priority will be given to the first category in alphabetical order (from A to Z).", 'very-simple-woocommerce-upsell-popup' ); ?></p>
			
			<table class="form-table" style="margin-bottom:35px;">
			<tbody>
			
				<tr>
				<th scope="row"><?php _e( "Settings Priority:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
				<td>
					<label for="thp_wup_options[cat_settings_on]">
						<input type="checkbox" id="thp_wup_options[cat_settings_on]" name="thp_wup_options[cat_settings_on]" value="1" <?php checked( '1', $thp_wup_options['cat_settings_on'] ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
						<?php _e( "Use category settings?", 'very-simple-woocommerce-upsell-popup' ); ?>
					</label>
				</td>
				</tr>
				
			</tbody>
			</table>
			<style>table.table_show_hide{display: none;width:100%;float:left;}table.table_show_hide tbody{width:100%;float:left;}table.table_show_hide tbody tr{width:100%;float:left;}table.table_show_hide tbody tr td,table.table_show_hide tbody tr th{width:33%;float:left;padding:0px;}#loadMore{background: #2271b1;border-color: #2271b1;color: #fff;padding:10px 20px;text-decoration: none;font-size:15px;border-radius: 5px;}.select2-search__field{width:100% !important;}</style>
			<?php
			
			$args = array(
				'taxonomy'   => 'product_cat',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => true,
				//'parent'     => 0
			);
			
			$product_categories = get_terms($args);
			@$x=0;
			if (!empty($product_categories)) {
				foreach ($product_categories as $cat) {
				@$x=$x+1;	
				?>

				<table class="form-table table_show_hide" <?php if(2>=$x){ ?> style="display:block;" <?php } ?>>
				<tbody>
					<tr><td colspan="3" style="padding:0px;margin:0px;width:100%;float:left;"><h3 style="padding:0px;margin:0px;"><?php echo '<span style="font-weight:bold;">'.$cat->name.'</span> (ID: '.$cat->term_id.')'; ?></h3></td></tr>
					<tr>
					<th scope="row"><?php _e( "Popup activation:", 'very-simple-woocommerce-upsell-popup' ); ?></th>
					<td>
						<label for="thp_wup_options[enable_upsell_cat<?php echo $cat->term_id; ?>]">
						
							<input type="checkbox" id="thp_wup_options[enable_upsell_cat<?php echo $cat->term_id; ?>]" name="thp_wup_options[enable_upsell_cat<?php echo $cat->term_id; ?>]" value="1" <?php checked( '1', ( !empty($thp_wup_options['enable_upsell_cat'.$cat->term_id]) ? $thp_wup_options['enable_upsell_cat'.$cat->term_id] : '' ) ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
							
							<?php _e( "Enable upsell & cross-sells popups in this category?", 'very-simple-woocommerce-upsell-popup' ); ?>
						
						</label>
					</td>
					<td>&nbsp;</td>
					</tr>
					
					<tr>
					<th scope="row">
						<?php _e( "Where to get upsell products from?", 'very-simple-woocommerce-upsell-popup' ); ?>
					</th>
					<td>
						<fieldset>
						<label for="thp_wup_options[linkedprods_upsells_cat<?php echo $cat->term_id; ?>]">
						
							<input type="radio" id="thp_wup_options[linkedprods_upsells_cat<?php echo @$cat->term_id; ?>]" name="thp_wup_options[linkedprods_upsells_cat<?php echo @$cat->term_id; ?>]" value="1" <?php checked( '1', ( !empty($thp_wup_options['linkedprods_upsells_cat'.@$cat->term_id]) ? $thp_wup_options['linkedprods_upsells_cat'.@$cat->term_id] : '' ) ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
						
							<?php _e( "Woocommerce Linked Products &gt; Upsells", 'very-simple-woocommerce-upsell-popup' ); ?>
						
						</label>
						</fieldset>
					<td>
								<div class="meta-box-sortables ui-sortable postbox " id="thp-wc-single-product-upsell" style="width:200px;">

			<select class="thp-upsell-product-select" multiple="" style="width: 50%;" id="thp_wup_options[list_of_global_upsell_products_cat<?php echo @$cat->term_id; ?>][]" name="thp_wup_options[list_of_global_upsell_products_cat<?php echo @$cat->term_id; ?>][]" data-placeholder="Search for a product…" data-action="woocommerce_json_search_products_and_variations" data-exclude="107" tabindex="-1" aria-hidden="true" <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
				
				<?php

				if(@$thp_wup_options['list_of_global_upsell_products_cat'.@$cat->term_id]){
					
					foreach ($thp_wup_options['list_of_global_upsell_products_cat'.@$cat->term_id] as $product) { 
						
							echo '<option selected="selected" value="'.@$product.'">'.get_the_title( @$product ).' (ID:'.@$product.')</option>';
					
					}
					
				}else{
					
					$products = get_posts(array('numberposts' => 1,'post_status' => 'publish','post_type' => 'product'));
					
					if(is_array($products)){
						
						foreach ($products as $product) {
							
							if($product){
								
								echo '<option value="'.@$product->ID.'">'.get_the_title( @$product->ID ).' (ID:'.@$product->ID.')</option>';
							
							}
							
						}
					
					}
				
				}
				?>

				</select>
				</div>
					</td>	
					<tr>
					<td>&nbsp;</td>	
					<td>
						<fieldset>
						<label for="thp_wup_options[linkedprods_upsells_cat<?php echo @$cat->term_id; ?>]">
						
							<input type="radio" id="thp_wup_options[linkedprods_upsells_cat<?php echo @$cat->term_id; ?>]" name="thp_wup_options[linkedprods_upsells_cat<?php echo @$cat->term_id; ?>]" value="2" <?php checked( '2', ( !empty($thp_wup_options['linkedprods_upsells_cat'.@$cat->term_id]) ? $thp_wup_options['linkedprods_upsells_cat'.@$cat->term_id] : '' ) ); ?> <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
						
							<?php _e( "Woocommerce Linked Products &gt; Cross-sells", 'very-simple-woocommerce-upsell-popup' ); ?>
						
						</label>
						</fieldset>
						</td>
						<td>
						<div class="meta-box-sortables ui-sortable postbox " id="thp-wc-single-product-cross-upsell-cat" style="width:200px;">

			<select class="thp-upsell-product-select" multiple="" style="width: 100%;" id="thp_wup_options[list_of_global_cross_upsell_products_cat<?php echo @$cat->term_id; ?>][]" name="thp_wup_options[list_of_global_cross_upsell_products_cat<?php echo @$cat->term_id; ?>][]" data-placeholder="Search for a product…" data-action="woocommerce_json_search_products_and_variations" data-exclude="107" tabindex="-1" aria-hidden="true" <?php echo ( !thp_wuppro_active() ? 'disabled' : '' ) ?>>
				
				<?php

				if(@$thp_wup_options['list_of_global_cross_upsell_products_cat'.@$cat->term_id]){
					
					foreach ($thp_wup_options['list_of_global_cross_upsell_products_cat'.@$cat->term_id] as $product) { 
						
							echo '<option selected="selected" value="'.@$product.'">'.get_the_title( @$product ).' (ID:'.@$product.')</option>';
					
					}
					
				}else{
					
					$products = get_posts(array('numberposts' => 1,'post_status' => 'publish','post_type' => 'product'));
					
					if(is_array($products)){
						
						foreach ($products as $product) {
							
							if($product){
								
								echo '<option value="'.@$product->ID.'">'.get_the_title( @$product->ID ).' (ID:'.@$product->ID.')</option>';
							
							}
							
						}
					
					}
				
				}
				?>

				</select>
				</div>
						
					</td>
					</tr>
					
				</tbody>
				</table>
				
				<?php
				} //end foreach
				?>
				<center><a href="javascript:void(0);" id="loadMore"><?php _e( "Load More", 'very-simple-woocommerce-upsell-popup' ); ?></a></center>
				<script>
					jQuery(function($){ 
					  $("#loadMore").on("click", function(e){ 
						$(".table_show_hide:hidden").slice(0, 2).slideDown();
						if($(".table_show_hide:hidden").length == 0) {
						  $("#loadMore").hide();
						}
					  });
					});
				</script>
				<?php
			} else {
				_e( "No product categories found.", 'very-simple-woocommerce-upsell-popup' );
			}
			?>
			
			<input type="hidden" name="action" value="update-wup-options" />
			
			<p>
				<input class="button-primary" type="submit" name="Submit" value="<?php _e( "Save changes", 'very-simple-woocommerce-upsell-popup' ); ?>" />
			</p>
	
		</form>
	<?php
	}
	
	else if ($active_tab == 'pro') {
		if (thp_wuppro_active()) {
			thp_wuppro_main_settings ();
		}
		else { ?>
			<h3><?php _e( "PRO Settings", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
			
			<div style="padding: 20px;background: #fff;text-align: center;font-weight: bold;">
				<a href="https://woocommerce.upsellpopup.com/woocommerce-upsell-popup-pro-plugin/" target="_blank" rel="noopener"><?php _e( "Upgrade to PRO to unlock more features!", 'very-simple-woocommerce-upsell-popup' ); ?></a>
			</div>
		<?php
		}
	}
	
	else if ($active_tab == 'documentation') { ?>
		
		<h3><?php _e( "Something is not working? Here's what you can do.", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
		
		<h4><?php _e( "These are the tutorials on how to try to fix your issue:", 'very-simple-woocommerce-upsell-popup' ); ?></h4>
		
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/quick-fix-to-most-issues/" target="_blank" rel="noopener" style="font-weight: bold;"><?php _e( "Quick fix to most issues [Read First]", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/troubleshooting/" target="_blank" rel="noopener"><?php _e( "How to troubleshoot problem with popup not showing", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/troubleshooting/" target="_blank" rel="noopener"><?php _e( "How to troubleshoot problem with indefinite loading spinner on popup", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		
		<h3><?php _e( "Plugin Documentation", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
		
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/setting-up-upsell-products/" target="_blank" rel="noopener"><?php _e( "How to set up upsell products", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/editing-popup-template-files/" target="_blank" rel="noopener"><?php _e( "How to edit popup template files for multiple upsell products", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/updating-the-plugin/" target="_blank" rel="noopener"><?php _e( "How to update the plugin", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/action-hooks-and-filters/" target="_blank" rel="noopener"><?php _e( "Action hooks and filters available to modify the plugin", 'very-simple-woocommerce-upsell-popup' ); ?></a><br />
		<a href="https://woocommerce.upsellpopup.com/docs/woocommerce-upsell-popup-pro-docs/" target="_blank" rel="noopener"><?php _e( "See full documentation here", 'very-simple-woocommerce-upsell-popup' ); ?></a>
		
		<h3 style="margin-top: 30px;"><?php _e( "Support", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
		
		<?php _e( "Your issue isn't covered in the documentation and need help?", 'very-simple-woocommerce-upsell-popup' ); ?> 
		<br />
		<a href="https://woocommerce.upsellpopup.com/contact/" target="_blank" rel="noopener"><?php _e( "Contact Support", 'very-simple-woocommerce-upsell-popup' ); ?></a>
		
		<br /><br />
		
		<div style="font-weight: bold;">
			<?php _e( "Your problem is urgent? Need faster response?", 'very-simple-woocommerce-upsell-popup' ); ?> 
			<br />
			<a href="https://woocommerce.upsellpopup.com/priority-support-service/" target="_blank" rel="noopener"><?php _e( "Consider our Priority Support", 'very-simple-woocommerce-upsell-popup' ); ?></a>
		</div>
		
	<?php
	} //end else if
	
	?>

</div><!-- .wrap -->

<?php
}


function thp_wup_main_settings_handler() {
	
	if ( (isset( $_POST['wup_main_settings_nonce'])) && (wp_verify_nonce( $_POST['wup_main_settings_nonce'], 'update-options' )) ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			
			/*$settings = filter_input(INPUT_POST, 'thp_wup_options', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);*/
			$settings = filter_input(INPUT_POST, 'thp_wup_options', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);;
			
			$status = update_option( 'thp_wup_options', $settings );
			
			if ($status) {
				echo '<div class="updated"><p>';
				_e( "Saved successfully!", 'very-simple-woocommerce-upsell-popup' );
				echo '</p></div>';
			}
			else {
				echo '<div class="error"><p>';
				_e( "Error. Settings are not saved!", 'very-simple-woocommerce-upsell-popup' );
				echo '</p></div>';
			}
		}
	}
	else {
		echo '<div class="error"><p>';
		_e( "Sorry, nonce verification failed. Fields are not saved.", 'very-simple-woocommerce-upsell-popup' );
		echo '</p></div>';
		exit;
	}
}