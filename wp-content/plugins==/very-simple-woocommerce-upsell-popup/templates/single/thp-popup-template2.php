<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'thp-wc-upsell-popup-frzn' ) ) {
   die('Undefined constant.');
}
?>
<!--Template - Colorful-->
<style type="text/css">
.thp-popup-quality input[type=number]{width:150px;margin-bottom:10px;}
.thp-dark-overlay {
    display: block;
    position: fixed;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 100%;
    background-color: #000;
    z-index: 9999998;
    -moz-opacity: 0.8;
    opacity: .80;
    filter: alpha(opacity=80);
}

.thp-content-body {
    display: block;
    position: fixed;
    top: 5%;
    left: 25%;
	width: 50%;
	max-height: 95%;
    z-index: 9999999;
    overflow: auto;
    font-size: 18px;
    box-shadow: 10px 10px 5px #000;
    border-radius: 6px;
    background: #ffffff;
    background: -moz-linear-gradient(top, #ffffff 0%, #ffffff 50%, #e6e6e6 75%, #aaaaaa 100%);
    background: -webkit-linear-gradient(top, #ffffff 0%,#ffffff 50%,#e6e6e6 75%,#aaaaaa 100%);
    background: linear-gradient(to bottom, #ffffff 0%,#ffffff 50%,#e6e6e6 75%,#aaaaaa 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ffffff", endColorstr="#aaaaaa",GradientType=0 );
	color: #333;
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

.thp-popup-title {
    text-align: center;
    padding: 0 3% 3% 3%;
    margin: 0;
    font-size: 30px;
}

.thp-popup-container {
    text-align: center;
	position: relative;
}

.thp-popup-container table td {
    height: 10px;
	padding: 0;
}

.thp-inner-popup {
	padding: 10px 20px;
}

.thp-popup-content {
	font-size: 15px;
}

.thp-popup-content span {
	font-size: 15px;
    text-decoration: underline;
    font-weight: bold;
}

.thp-top-border {
	width: 100%;
	border: none;
	margin: 0;
}

.thp-inner-popup .thp-inner-img {
	border: 2px solid #ccc;
	margin: 20px;
	border-radius: 3px;
	max-height: 190px;
}

.thp-inner-popup .thp-placeholder-img {
	max-height: 190px;
}

.thp-popup-button {
    text-align: center !important; 
    width: 80%;
    margin:0 auto !important;
}

.thp-popup-button input[type=submit] {
    border: none !important;
    color: #fff !important;
	padding: 0.75em 2em !important;
    font-size: 15px;
	line-height: 15px;
	display: inline-block;
	cursor: pointer;
}

.thp-popup-button input[name=thp-popupbutton-yes] {
    background: #4F86B5 !important;
	display: inline-block;
}

.thp-popup-button input[name=thp-popupbutton-no] {
    background: #FF575B !important;
	display: inline-block;
}

.thp-popup-button input[name=thp-popupbutton-yes]:hover {
    background: #6cace2 !important;
}

.thp-popup-button input[name=thp-popupbutton-no]:hover {
    background: #ff9193 !important;
}

.thp-added2cart-notif {
	color: #333 !important;
}

#thp-spinner-container {
	display: none;
}

#thp-spinner-overlay {
	position: absolute;
	top: 0;
	bottom: 0;
	left: 0;
	right: 0;
	z-index: 10000000;
	width: 100%;
	height:100%;
	background: rgba(0,0,0,0.3);
}
.thp-cv-spinner {
	height: 100%;
	display: flex;
	justify-content: center;
	align-items: center;  
}
.thp-spinner {
	width: 40px;
	height: 40px;
	border: 4px #fff solid;
	border-top: 4px #000 solid;
	border-radius: 50%;
	animation: sp-anime 0.8s infinite linear;
}
@keyframes sp-anime {
	0% { 
		transform: rotate(0deg); 
	}
	100% { 
		transform: rotate(359deg); 
	}
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

echo '.thp-popup-button input[name=thp-popupbutton-yes],.thp-popup-button input[name=thp-popupbutton-no]{ background-color:'.$wup_options['global_background_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_hover_background_button_color']){ 

echo '.thp-popup-button input[name=thp-popupbutton-yes]:hover,.thp-popup-button input[name=thp-popupbutton-no]:hover{ background-color:'.$wup_options['global_hover_background_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_text_button_color']){ 

echo '.thp-popup-button input[name=thp-popupbutton-yes],.thp-popup-button input[name=thp-popupbutton-no]{ color:'.$wup_options['global_text_button_color'].' !important; }'; 

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
		
		    <div class="thp-colorful-strip">
                <table class="thp-top-border">
                    <tbody>
                    <tr>
                        <td style="background-color: #ffb861;">&nbsp;</td>
                        <td style="background-color: #32d0d9;">&nbsp;</td>
                        <td style="background-color: #ff6961;">&nbsp;</td>
                        <td style="background-color: #32d93b;">&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
            </div>
			
			<div class="thp-inner-popup">
			
			<h3 class="thp-popup-title"><?php _e( "Oh, Before That...", 'very-simple-woocommerce-upsell-popup' ); ?></h3>
			
			<div class="thp-popup-content">
					<?php
					$p_permalink = get_the_permalink( $single_upsell_id );
					$p_title = get_the_title( $single_upsell_id );
					
					$pop_string = sprintf( wp_kses( __( 'The product has been added to cart. Would you also be interested in <a href="%1$s"><span>%2$s</span></a>?', 'very-simple-woocommerce-upsell-popup' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $p_permalink ), $p_title );
					
					echo $pop_string;
					?>
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
				
				<?php if(@$wup_options['global_product_quality_on']){ 								
				
				echo '<div class="thp-popup-quality quality-display-'.$single_upsell_id.'"><input type="number" name="quality-'.$single_upsell_id.'" id="quality-'.$single_upsell_id.'" min="1" value="1" class="product_quality" /></div>';					
				} 
				
				?>
					<input class="thp-button-yes" name="thp-popupbutton-yes" type="submit" value="<?php if(thp_wuppro_active()){ if (@$thp_wuppro_options['cust_txt_atc_btn']){ echo @$thp_wuppro_options['cust_txt_atc_btn']; }else{ _e( "Yes", 'very-simple-woocommerce-upsell-popup' ); } }else{ _e( "Yes", 'very-simple-woocommerce-upsell-popup' ); } ?>" />
					<input class="thp-button-no" name="thp-popupbutton-no" type="submit" value="<?php if(thp_wuppro_active()){ if (@$thp_wuppro_options['cust_txt_no_btn']){ echo @$thp_wuppro_options['cust_txt_no_btn']; }else{ _e( "No", 'very-simple-woocommerce-upsell-popup' ); } }else{ _e( "No", 'very-simple-woocommerce-upsell-popup' ); } ?>" />
					<input id="thp-hidden-prod-id" name="thp-upsell-pid" type="hidden" value="<?php echo esc_html($single_upsell_id); ?>">
					<input id="thp-hidden-orig-prod-id" name="thp-upsell-orig-pid" type="hidden" value="<?php echo esc_html($product_id); ?>">
				</div>
			</form>
			</div>
			
			<div id="thp-spinner-container">
				<div id="thp-spinner-overlay">
					<div class="thp-cv-spinner">
						<span class="thp-spinner"></span>
					</div>
				</div>
			</div>

		</div>
		
</div>