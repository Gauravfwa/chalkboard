<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'thp-wc-upsell-popup-frzn' ) ) {
   die('Undefined constant.');
}
?>
<!--Template - Dark on Light-->
<?php
	$img_url = get_the_post_thumbnail_url($single_upsell_id,'thumbnail');
	if ($img_url == '')
		$img_url = plugin_dir_url( __DIR__ ).'images/placeholder.png';
?>
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
    background-color: #fff;
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
    max-height: 65%;
	z-index: 9999999;
    overflow: auto;
	background: #333;
	border-radius: 15px;
	color: #fff;
}

.thp-popup-container {
	position: relative;
}

@media only screen and (max-width : 1024px) {
	.thp-content-body {
		top: 5%;
		left: 10%;
		width: 80%;
		height: 90%;
	}
}

@media only screen and (max-width : 768px) {
	.thp-content-body {
		top: 5% !important;
		left: 5% !important;
		width: 90% !important;
		height: 80% !important;
	}	
	.thp-popup-container .thp-popup-content .titleh3 {
		font-size: 18px !important;
	}
}

@media only screen and (max-width : 480px) {
	.thp-popup-content {
		background: none !important;
	}
	.thp-popup-container .thp-popup-content .titleh3 {
		line-height: 30px !important;
	}
	.thp-popup-container .thp-popup-content h2, .thp-popup-container .thp-popup-content .titleh3 {
		text-align: center !important;
		width: 100% !important;
	}
	.thp-popup-button {
		text-align: center !important;
	}
}

.thp-inner-popup {
	padding: 30px;
}

.thp-popup-content {
	display: flex;
	flex-wrap: wrap;
	align-items: flex-start;
}

.thp-popup-container .thp-popup-content h2 {
    color: #51dcde;
    font-size: 70px;
	line-height: 80px;
	font-weight: bold;
	text-align: left;
	display: block;
	width: 60%;
	margin: 0 0 20px 0;
	padding: 0;
}

.thp-popup-container .thp-popup-content .titleh3 {
    color: #FFF;
    font-size: 20px;
	line-height: 36px;
	font-weight: bold;
	text-align: left;
	margin: 0 20px 20px 0;
	display: block;
	width: 60%;
	padding: 0;
}

.thp-popup-button {
	text-align: center;
}

.thp-popup-container .thp-button-yes,
.thp-popup-container .thp-button-no {
    border: 0px;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    -webkit-border-radius: 50px;
    border-radius: 50px;
    text-shadow: 0 2px 2px #000;
    -webkit-box-shadow: 0 5px 10px 0 rgba(0,0,0,0.56);
    box-shadow: 0 5px 10px 0 rgba(0,0,0,0.56);
    background: -moz-linear-gradient(top, #ff6c00 1%, #ff0000 100%);
    background: -webkit-linear-gradient(top, #ff6c00 1%, #ff0000 100%);
    background: linear-gradient(to bottom, #ff6c00 1%, #ff0000 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr="#ff6c00", endColorstr="#ff0000", GradientType=0 );
    cursor: pointer;
	display: inline-block;
	width:auto;
	padding: 15px;
}

@media only screen and (max-width : 768px) {
	.thp-popup-container .thp-button-yes,
	.thp-popup-container .thp-button-no {
		width: auto;
		font-size: 15px;
	}
}

.thp-popup-container .thp-button-yes:hover,
.thp-popup-container .thp-button-no:hover {
	color: #fff;
}

.thp-button-yes {
	margin-right: 15px;
}

.thp-popup-content a {
	color: #29a9a9 !important;
}

.thp-added2cart-notif {
	color: #fff !important;
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
	height: 90%;
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
    border: 1px dashed #fff;
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

echo '.thp-popup-container .thp-button-yes,.thp-popup-container .thp-button-no{ background:'.$wup_options['global_background_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_hover_background_button_color']){ 

echo '.thp-popup-container .thp-button-yes:hover,.thp-popup-container .thp-button-no:hover{ background:'.$wup_options['global_hover_background_button_color'].' !important; }'; 

}else{} 

?>

<?php 

if (@$wup_options['global_text_button_color']){ 

echo '.thp-popup-container .thp-button-yes,.thp-popup-container .thp-button-no{ color:'.$wup_options['global_text_button_color'].' !important; }'; 

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
	
		<div class="thp-popup-content">
			
			<h2><?php _e( "Wait!", 'very-simple-woocommerce-upsell-popup' ); ?></h2>
			
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
			
			<div class="titleh3">
					<?php
					$p_permalink = get_the_permalink( $single_upsell_id );
					$p_title = get_the_title( $single_upsell_id );
					
					$pop_string = sprintf( wp_kses( __( 'The product has been added to cart. Would you be interested in <a href="%1$s"><span>%2$s</span></a> too?', 'very-simple-woocommerce-upsell-popup' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( $p_permalink ), $p_title );
					
					echo $pop_string;
					?>
			</div>
			<img src="<?php echo esc_url( $img_url ); ?>" style="max-width:150px;" />
			
		</div>
			
			<form id="thp-popup-form" method=post>
				<div class="thp-popup-button">					
				
				<?php if(@$wup_options['global_product_quality_on']){ 								
				
				echo '<div class="thp-popup-quality quality-display-'.$single_upsell_id.'"><input type="number" name="quality-'.$single_upsell_id.'" id="quality-'.$single_upsell_id.'" min="1" value="1" class="product_quality" /></div>';					
				} ?>
				
					<input class="thp-button-yes" name="thp-popupbutton-yes" type="submit" value="<?php if(thp_wuppro_active()){ if (@$thp_wuppro_options['cust_txt_atc_btn']){ echo @$thp_wuppro_options['cust_txt_atc_btn']; }else{ _e( "Yes!", 'very-simple-woocommerce-upsell-popup' ); } }else{ _e( "Yes!", 'very-simple-woocommerce-upsell-popup' ); } ?>" />
					
					<input class="thp-button-no" name="thp-popupbutton-no" type="submit" value="<?php if(thp_wuppro_active()){ if (@$thp_wuppro_options['cust_txt_no_btn']){ echo @$thp_wuppro_options['cust_txt_no_btn']; }else{ _e( "No, sorry.", 'very-simple-woocommerce-upsell-popup' ); } }else{ _e( "No, sorry.", 'very-simple-woocommerce-upsell-popup' ); } ?>" />
					
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