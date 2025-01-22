<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'thp-wc-upsell-popup-frzn' ) ) {
   die('Undefined constant.');
}
?>
<!--Template - No CSS-->
<!-- Basic CSS below, comment it if you don't need it -->
<style type="text/css">
form#thp-popup-form input[type=submit]{margin:10px 5px;}
.thp-popup-quality input[type=number]{width:150px;margin:10px 0px;}
.thp-dark-overlay {
    display: block;
    position: fixed;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 100%;
    background-color: #000;
    z-index: 9999998;
    -moz-opacity: 0.7;
    opacity: .70;
    filter: alpha(opacity=70);
}

.thp-content-body {
    display: block;
    position: fixed;
    top: 10%;
    left: 25%;
	width: 50%;
	max-height: 75%;
    z-index: 9999999;
    overflow: auto;
    font-size: 18px;
    background: #fff;
	color: #333;
	border-radius: 5px;
}

@media only screen and (max-width : 768px) {
	.thp-content-body {
		top: 5%;
		left: 10%;
		width: 80%;
		height: 90%;
		border-radius: 3px;
	}
}

@media only screen and (max-width : 480px) {
	.thp-content-body {
		top: 0%;
		left: 0%;
		width: 100%;
		height: 100%;
		border-radius: 0;
	}
}

.thp-inner-popup {
	padding: 20px;
}

.thp-placeholder-img {
	margin-bottom: 10px;
	max-width: 100%;
	max-height: 190px;
	height: 100%;
}

.thp-inner-img {
	max-height: 190px;
	margin-bottom: 10px;
	padding: 2px;
}

.thp-popup-button {
    text-align: center; 
    margin:0 auto;
}

.thp-popup-button input[type=submit] {
	display: inline-block;
	line-height: 20px;
}

.thp-added2cart-notif {
	color: #333;
}
.coupon_code {
    border: 1px dashed #000;
    margin: 0px 10px;
    padding: 3px;
    float: left;
}
.inner_coupon_code {
    background: #41ad49;
    color: #fff;
    font-weight: bold;
    padding: 10px;
    float: left;
    font-size: 10px;
}
.thp-popup-header.thp-popup-line {float: left;}

<?php 

if (@$wup_options['global_background_button_color']){ 

echo '.thp-button-yes,.thp-button-no{ background-color:'.$wup_options['global_background_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_hover_background_button_color']){ 

echo '.thp-button-yes:hover,.thp-button-no:hover{ background-color:'.$wup_options['global_hover_background_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_text_button_color']){ 

echo '.thp-button-yes,.thp-button-no{ color:'.$wup_options['global_text_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_coupon_text_color']){ 

echo '.inner_coupon_code{ color:'.$wup_options['global_coupon_text_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_coupon_background_color']){ 

echo '.inner_coupon_code{ background-color:'.$wup_options['global_coupon_background_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_coupon_border_color']){ 

echo '.coupon_code{ border:1px dashed '.$wup_options['global_coupon_border_color'].' !important; }'; 

}else{} 

?>

</style>

<?php $thp_wuppro_options = get_option( 'thp_wuppro_options' ); ?>

<div class="thp-dark-overlay"></div>

<div class="thp-content-body">

		<div class="thp-popup-container">
			
			<div class="thp-inner-popup">
			
			<h3 class="thp-popup-title"><?php _e( "Thank you! The product has been added to cart.", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
			
			<div class="thp-popup-content">
				<p>
					<?php
					$p_permalink = get_the_permalink( $single_upsell_id );
					$p_title = get_the_title( $single_upsell_id );
					
					$pop_string = sprintf( wp_kses( __( 'Would you also be interested in <a href="%1$s"><span>%2$s</span></a>?', 'very-simple-woocommerce-upsell-popup' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $p_permalink ), $p_title );
					
					echo $pop_string;
					?>
				</p>
			</div>
			
				<?php if((!empty(get_post_meta($single_upsell_id, 'thp_wc_offer_upsell_on_product',true))) && (get_post_meta($single_upsell_id, 'thp_wc_offer_upsell_on_product',true)==1)){ ?> 
			<div class="thp-popup-header thp-popup-line">
				<?php if(!empty(get_post_meta($single_upsell_id, 'offer_coupon_code',true))){ echo '<span class="coupon_code"><span class="inner_coupon_code">'.get_post_meta($single_upsell_id, 'offer_coupon_code',true).'</span></span>'; } ?> <?php if(!empty(get_post_meta($single_upsell_id, 'offer_heading',true))){ echo get_post_meta($single_upsell_id, 'offer_heading',true); } ?> 
			</div>
			<?php }else{ ?>
			
			<?php if(!empty($wup_options['global_offer_on'])){  ?> 
			<div class="thp-popup-header thp-popup-line">
				<?php if(!empty($wup_options['global_coupon_code'])){ echo '<span class="coupon_code"><span class="inner_coupon_code">'.$wup_options['global_coupon_code'].'</span></span>'; } ?> <?php if(!empty($wup_options['global_offer_heading'])){ echo $wup_options['global_offer_heading']; } ?> 
			</div>
			<?php } ?>
			
			<?php } ?>
			
			<?php 
			$img_url = get_the_post_thumbnail_url($single_upsell_id,'medium');
			if ($img_url != '') {
			?>
				<a href="<?php echo get_the_permalink( $single_upsell_id ); ?>">
					<img class="thp-inner-img" src="<?php echo esc_url( $img_url ); ?>" alt="product image" />
				</a>
			<?php } else { ?>
				<img class="thp-placeholder-img" src="<?php echo plugin_dir_url( __DIR__ ).'images/placeholder.png' ?>" alt="placeholder" />
			<?php } ?>
		
			<form id="thp-popup-form" method=post>
				<div class="thp-popup-button">					
				<?php 
				
				if(@$wup_options['global_product_quality_on']){
					
				echo '<div class="thp-popup-quality quality-display-'.$single_upsell_id.'"><input type="number" name="quality-'.$single_upsell_id.'" id="quality-'.$single_upsell_id.'" min="1" value="1" class="product_quality" /></div>';					
				} ?>
				
					<input class="thp-button-yes" name="thp-popupbutton-yes" type="submit" value="<?php if(thp_wuppro_active()){ if (@$thp_wuppro_options['cust_txt_atc_btn']){ echo @$thp_wuppro_options['cust_txt_atc_btn']; }else{ _e( "Yes", 'very-simple-woocommerce-upsell-popup' ); } }else{ _e( "Yes", 'very-simple-woocommerce-upsell-popup' ); } ?>" />
					
					<input class="thp-button-no" name="thp-popupbutton-no" type="submit" value="<?php if(thp_wuppro_active()){ if (@$thp_wuppro_options['cust_txt_no_btn']){ echo @$thp_wuppro_options['cust_txt_no_btn']; }else{ _e( "No", 'very-simple-woocommerce-upsell-popup' ); } }else{ _e( "No", 'very-simple-woocommerce-upsell-popup' ); } ?>" />
					
					<input id="thp-hidden-prod-id" name="thp-upsell-pid" type="hidden" value="<?php echo esc_html($single_upsell_id); ?>">
					<input id="thp-hidden-orig-prod-id" name="thp-upsell-orig-pid" type="hidden" value="<?php echo esc_html($product_id); ?>">
				</div>
			</form>
			</div>

		</div>
		
</div>