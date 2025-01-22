<?php
/**
 * Plugin Name: OpenPath for WooCommerce
 * Plugin URI: http://www.openpath.io/
 * Description: WooCommerce Plugin for accepting payment through OpenPath.
 * Version: 3.7.0
 * Author: OpenPath, Inc.
 * Author URI: http://www.openpath.io
 * Contributors: OpenPath, Inc.
 * Requires at least: 3.5
 * Tested up to: 4.7
 *
 * Text Domain: woo-openpath-plug-in
 * Domain Path: /wp-plug-in/
 *
 * @package OpenPath for WooCommerce
 * @author OpenPath, Inc.
 */
 
add_action('plugins_loaded', 'init_woo_openpath_plug_in', 0);

function init_woo_openpath_plug_in() {

    if (!class_exists('WC_Payment_Gateway_CC')) {
        return;
    }
	require_once dirname(__FILE__) . '/woocommerce_openpath.php';
    load_plugin_textdomain('woo-openpath-plug-in', false, dirname(plugin_basename(__FILE__)) . '/lang');

    add_action('woocommerce_payment_token_deleted', 'woo_openpath_plug_in_payment_token_deleted', 10, 2);

    /**
     * Delete token from OpenPath
     */
    function woo_openpath_plug_in_payment_token_deleted($token_id, $token) {        
        $gateway = new woocommerce_openpathpay();

        if ('openpathpay' === $token->get_gateway_id()) {

            $openpathpay_adr = $gateway->openpath_url . '?';

            $openpathpay_args['username'] = $gateway->username;
            $openpathpay_args['password'] = $gateway->password;
            $openpathpay_args['customer_vault'] = 'delete_customer';
            $openpathpay_args['customer_vault_id'] = $token->get_token();

            $name_value_pairs = array();
            foreach ($openpathpay_args as $key => $value) {
                $name_value_pairs[] = $key . '=' . urlencode($value);
            }
            $gateway_values = implode('&', $name_value_pairs);

            $response = wp_remote_post($openpathpay_adr . $gateway_values, array('sslverify' => false, 'timeout' => 60));

            if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300) {
                parse_str($response['body'], $response);
                if ($response['response'] == '1') {
                    
                } else {
                    if (strpos($response['responsetext'], 'Invalid Customer Vault Id') !== false) {
                        
                    } else {
                        wc_add_notice(sprintf(__('Deleting card failed. %s-%s', 'woo-openpath-plug-in'), $response['response_code'], $response['responsetext']), $notice_type = 'error');
                        return;
                    }
                }
            } else {
                wc_add_notice(__('There was error processing your request.' . print_r($response, TRUE), 'woo-openpath-plug-in'), $notice_type = 'error');
                return;
            }
        }
    }

    /**
     * Add the gateway to WooCommerce
     * */
    function add_woo_openpath_plug_in($methods) {
        $methods[] = 'woocommerce_openpathpay';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_woo_openpath_plug_in');
}
