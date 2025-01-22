<?php
/**
 * Save Extra Fee Meta
 *
 * Save the data of all meta boxes i.e. Extra fee, Location, Cart Conditions, users and Shipping
 *
 * @package  addify-abandoned-cart-recovery/includes
 * @version  1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$_nonce = isset( $_POST['afacr_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['afacr_nonce_field'] ) ) : 0;

if ( isset( $_POST['afacr_nonce_field'] ) && ! wp_verify_nonce( $_nonce, 'afacr_nonce_action' ) ) {
	die( 'Failed Security Check' );
}

/**
 * Email Meta Boxes.
 */

// Email Active.
if ( isset( $_POST['afacr_enable'] ) ) {
	update_post_meta( $post_id, 'afacr_enable', sanitize_text_field( wp_unslash( $_POST['afacr_enable'] ) ) );
} else {
	update_post_meta( $post_id, 'afacr_enable', sanitize_text_field( 'no' ) );
}

// Email Subject.
if ( isset( $_POST['afacr_email_subject'] ) ) {
	update_post_meta( $post_id, 'afacr_email_subject', sanitize_text_field( wp_unslash( $_POST['afacr_email_subject'] ) ) );
}

// Email Type.
if ( isset( $_POST['afacr_email_type'] ) ) {
	update_post_meta( $post_id, 'afacr_email_type', sanitize_text_field( wp_unslash( $_POST['afacr_email_type'] ) ) );
}

// Automatic Email.
if ( isset( $_POST['afacr_automatic'] ) ) {
	update_post_meta( $post_id, 'afacr_automatic', sanitize_text_field( wp_unslash( $_POST['afacr_automatic'] ) ) );
} else {
	update_post_meta( $post_id, 'afacr_automatic', sanitize_text_field( 'no' ) );
}

// Automatic Email Time.
if ( isset( $_POST['afacr_time'] ) ) {
	update_post_meta( $post_id, 'afacr_time', wp_json_encode( sanitize_meta( '', wp_unslash( $_POST['afacr_time'] ), '' ) ) );
} else {
	update_post_meta( $post_id, 'afacr_time', wp_json_encode( array() ) );
}

// Automatic Email Time.
if ( isset( $_POST['afacr_customer_roles'] ) ) {
	update_post_meta( $post_id, 'afacr_customer_roles', wp_json_encode( sanitize_meta( '', wp_unslash( $_POST['afacr_customer_roles'] ), '' ) ) );
} else {
	update_post_meta( $post_id, 'afacr_customer_roles', wp_json_encode( array() ) );
}



/**
 * Coupons Meta Box.
 */

if ( isset( $_POST['afacr_enable_coupon'] ) ) {
	update_post_meta( $post_id, 'afacr_enable_coupon', sanitize_text_field( wp_unslash( $_POST['afacr_enable_coupon'] ) ) );
} else {
	update_post_meta( $post_id, 'afacr_enable_coupon', sanitize_text_field( 'no' ) );
}

// Coupons Value.
if ( isset( $_POST['afacr_coupon_value'] ) ) {
	update_post_meta( $post_id, 'afacr_coupon_value', sanitize_text_field( wp_unslash( $_POST['afacr_coupon_value'] ) ) );
}

// Coupons Type.
if ( isset( $_POST['afacr_coupon_type'] ) ) {
	update_post_meta( $post_id, 'afacr_coupon_type', sanitize_text_field( wp_unslash( $_POST['afacr_coupon_type'] ) ) );
}

// Coupons validity.
if ( isset( $_POST['afacr_coupon_validity'] ) ) {
	update_post_meta( $post_id, 'afacr_coupon_validity', sanitize_text_field( wp_unslash( $_POST['afacr_coupon_validity'] ) ) );
}
