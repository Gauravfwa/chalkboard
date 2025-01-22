<?php
/**
 * Main class for Affiliates Dashboard
 *
 * @package     affiliate-for-woocommerce/includes/admin/
 * @version     1.10.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_Admin_Dashboard' ) ) {

	/**
	 * Main class for Affiliates Dashboard
	 */
	class AFWC_Admin_Dashboard {

		/**
		 * The Ajax events.
		 *
		 * @var array $ajax_events
		 */
		private $ajax_events = array(
			'update_commission_status',
			'process_payout',
			'dashboard_data',
			'export_affiliates',
			'order_details',
			'payout_details',
			'affiliate_details',
			'update_feedback',
			'update_payout_method',
			'dashboard_kpi_data',
			'affiliate_kpi_details',
			'top_products',
			'profile_data',
			'affiliate_chain_data',
			'link_ltc_customers',
			'unlink_ltc_customers',
			'search_ltc_customers',
		);

		/**
		 * Constructor
		 */
		public function __construct() {
			define( 'AFWC_AFFILIATES_LIMIT', 50 );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_dashboard_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_dashboard_styles' ) );
			add_action( 'wp_ajax_afwc_dashboard_controller', array( $this, 'request_handler' ) );
			add_action( 'admin_print_scripts', array( $this, 'remove_admin_notices' ) );

			add_action( 'wp_ajax_afwc_update_payout_method', array( $this, 'update_payout_method' ) );
		}

		/**
		 * Function to remove admin notices from affiliate dashboard page.
		 */
		public function remove_admin_notices() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( strpos( $screen_id, '_affiliate-for-woocommerce' ) !== false ) {
				remove_all_actions( 'admin_notices' );
			}
		}

		/**
		 * Function to register required scripts for admin dashboard.
		 */
		public function register_admin_dashboard_scripts() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( strpos( $screen_id, '_affiliate-for-woocommerce' ) !== false ) {
				$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();
				$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				// Dashboard scripts.
				wp_register_script( 'mithril', AFWC_PLUGIN_URL . '/assets/js/mithril/mithril.min.js', array(), $plugin_data['Version'], true );
				wp_register_script( 'afwc-admin-dashboard-styles', AFWC_PLUGIN_URL . '/assets/js/styles.js', array( 'mithril' ), $plugin_data['Version'], true );
				if ( ! wp_script_is( 'accounting', 'registered' ) ) {
					wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
				}
				wp_register_script( 'afwc-admin-dashboard', AFWC_PLUGIN_URL . '/assets/js/admin.js', array( 'afwc-admin-dashboard-styles', 'accounting', 'wp-i18n' ), $plugin_data['Version'], true );
				if ( function_exists( 'wp_set_script_translations' ) ) {
					wp_set_script_translations( 'afwc-admin-dashboard', 'affiliate-for-woocommerce', AFWC_PLUGIN_DIR_PATH . 'languages' );
				}
				if ( ! wp_script_is( 'selectWoo', 'registered' ) ) {
					wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo' . $suffix . '.js', array( 'jquery' ), WC_VERSION, true );
				}
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_editor();
				wp_enqueue_media();
			}
		}

		/**
		 * Function to register required styles for admin dashboard.
		 */
		public function register_admin_dashboard_styles() {
			$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();
			$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_style( 'tailwind', AFWC_PLUGIN_URL . '/assets/css/admin.css', array(), $plugin_data['Version'] );
			wp_register_style( 'afwc-admin-dashboard-css', AFWC_PLUGIN_URL . '/assets/css/afwc-admin-dashboard.css', array(), $plugin_data['Version'] );
			wp_enqueue_style( 'selectWoo', WC()->plugin_url() . '/assets/css/select2.css', array(), WC_VERSION );
		}

		/**
		 * Function to show admin dashboard.
		 */
		public static function afwc_dashboard_page() {
			if ( ! wp_script_is( 'afwc-admin-dashboard' ) ) {
				wp_enqueue_script( 'afwc-admin-dashboard' );
			}

			if ( ! wp_style_is( 'tailwind' ) ) {
				wp_enqueue_style( 'tailwind' );
			}

			if ( ! wp_style_is( 'afwc-admin-dashboard-css' ) ) {
				wp_enqueue_style( 'afwc-admin-dashboard-css' );
			}

			if ( ! wp_script_is( 'selectWoo' ) ) {
				wp_enqueue_script( 'selectWoo' );
			}

			$settings_link = add_query_arg(
				array(
					'page' => 'wc-settings',
					'tab'  => 'affiliate-for-woocommerce-settings',
				),
				admin_url( 'admin.php' )
			);

			$afwc_filters                                = array();
			$afwc_filters['affiliate_status']['pending'] = __( 'Awaiting Approval', 'affiliate-for-woocommerce' );
			$afwc_filters['affiliate_status']['yes']     = __( 'Active', 'affiliate-for-woocommerce' );
			$afwc_filters['affiliate_status']['no']      = __( 'Rejected', 'affiliate-for-woocommerce' );
			// TODO: can fetch commission statuses from function.
			$afwc_filters['order_status']['unpaid']   = __( 'Unpaid', 'affiliate-for-woocommerce' );
			$afwc_filters['order_status']['paid']     = __( 'Paid', 'affiliate-for-woocommerce' );
			$afwc_filters['order_status']['rejected'] = __( 'Rejected', 'affiliate-for-woocommerce' );

			$afwc_filters['tags'] = afwc_get_user_tags_id_name_map(); // TODO:: get top 10 tags and pass.
			$afwc_filters['tags'] = ( ! empty( $afwc_filters['tags'] ) ) ? array_slice( $afwc_filters['tags'], 0, 10, true ) : $afwc_filters['tags'];

			$plan_dashboard_data = array();
			$registry            = is_callable( array( 'AFWC_Registry', 'get_registry' ) ) ? AFWC_Registry::get_registry() : array();
			$rule_group_titles   = ( ! empty( $registry ) && ! empty( $registry['meta'] ) && ! empty( $registry['meta']['rule_group_titles'] ) ) ? $registry['meta']['rule_group_titles'] : array();

			$commission_rules = ! empty( $registry['rule'] ) ? $registry['rule'] : array();
			$plan_data        = array();
			foreach ( $commission_rules as $context_key => $class_name ) {
				$props                     = array();
				$class_obj                 = new $class_name( $props );
				$plan                      = array();
				$category                  = is_callable( array( $class_obj, 'get_category' ) ) ? $class_obj->get_category() : '';
				$plan['possibleOperators'] = is_callable( array( $class_obj, 'get_possible_operators' ) ) ? $class_obj->get_possible_operators() : array();
				$plan['title']             = is_callable( array( $class_obj, 'get_title' ) ) ? $class_obj->get_title() : '';
				$plan['placeholder']       = is_callable( array( $class_obj, 'get_placeholder' ) ) ? $class_obj->get_placeholder() : '';
				if ( empty( $plan_data[ $category ] ) ) {
					$plan_data[ $category ] = array();
				}
				$plan_data[ $category ]['_meta']        = array(
					'title' => ( ! empty( $rule_group_titles ) && ! empty( $rule_group_titles[ $category ] ) ) ? $rule_group_titles[ $category ] : $category,
				);
				$plan_data[ $category ][ $context_key ] = $plan;
			}

			$plan_dashboard_data['plan_rule_data']                   = $plan_data;
			$plan_dashboard_data['apply_to']['all']                  = __( 'all matching products in the order', 'affiliate-for-woocommerce' );
			$plan_dashboard_data['apply_to']['first']                = __( 'only the first matching product', 'affiliate-for-woocommerce' );
			$plan_dashboard_data['action_for_remaining']['continue'] = __( 'continue matching commission plans', 'affiliate-for-woocommerce' );
			$plan_dashboard_data['action_for_remaining']['default']  = __( 'use default commission', 'affiliate-for-woocommerce' );
			$plan_dashboard_data['action_for_remaining']['zero']     = __( 'apply zero commission', 'affiliate-for-woocommerce' );

			$can_ask_for_feedback = self::show_feedback();

			// migration notice.
			$migration_of_order_status_done = get_option( 'afwc_migration_for_order_status_done', false );
			$current_db_version             = get_option( '_afwc_current_db_version' );
			$show_admin_notice              = ( '1.2.7' >= $current_db_version && ! $migration_of_order_status_done ) ? true : false;
			$is_process_running             = get_option( 'afwc_is_migration_process_running', false );

			$afwc_dates_migration_done   = get_option( 'afwc_dates_migration_done', false );
			$show_admin_notice_for_dates = ( '1.2.9' >= $current_db_version && 'yes' !== $afwc_dates_migration_done ) ? true : false;
			$default_plan_id             = afwc_get_default_commission_plan_id();
			$is_action_scheduler_exists  = ( function_exists( 'as_schedule_single_action' ) ) ? true : false;
			$review_link                 = 'https://woocommerce.com/products/affiliate-for-woocommerce/?review';
			$afwc_admin_affiliates       = new AFWC_Admin_Affiliates();

			wp_localize_script(
				'afwc-admin-dashboard',
				'afwcDashboardParams',
				array(
					'security'                       => array(
						'dashboard'   => array(
							'updateCommissionStatus' => wp_create_nonce( 'afwc-admin-update-commission-status' ),
							'processPayout'          => wp_create_nonce( 'afwc-admin-process-payout' ),
							'fetchData'              => wp_create_nonce( 'afwc-admin-dashboard-data' ),
							'exportAffiliates'       => wp_create_nonce( 'afwc-admin-export-affiliates' ),
							'orderDetails'           => wp_create_nonce( 'afwc-admin-order-details' ),
							'payoutDetails'          => wp_create_nonce( 'afwc-admin-payout-details' ),
							'affiliateDetails'       => wp_create_nonce( 'afwc-admin-affiliate-details' ),
							'updatePayoutMethod'     => wp_create_nonce( 'afwc-admin-update-payout-method' ),
							'updateFeedback'         => wp_create_nonce( 'afwc-admin-update-feedback' ),
							'kpiData'                => wp_create_nonce( 'afwc-admin-dashboard-kpi-data' ),
							'affiliateKPIData'       => wp_create_nonce( 'afwc-admin-affiliate-kpi-data' ),
							'topProducts'            => wp_create_nonce( 'afwc-admin-top-products' ),
							'profileData'            => wp_create_nonce( 'afwc-admin-profile-data' ),
							'multiTierData'          => wp_create_nonce( 'afwc-admin-multi-tier-data' ),
							'linkLTCCustomers'       => wp_create_nonce( 'afwc-admin-link-ltc-customers' ),
							'unlinkLTCCustomers'     => wp_create_nonce( 'afwc-admin-unlink-ltc-customers' ),
							'searchLTCCustomers'     => wp_create_nonce( 'afwc-admin-search-ltc-customers' ),
						),
						'campaign'    => array(
							'save'      => wp_create_nonce( 'afwc-admin-save-campaign' ),
							'delete'    => wp_create_nonce( 'afwc-admin-delete-campaign' ),
							'fetchData' => wp_create_nonce( 'afwc-admin-campaign-dashboard-data' ),
						),
						'commissions' => array(
							'save'          => wp_create_nonce( 'afwc-admin-save-commissions' ),
							'delete'        => wp_create_nonce( 'afwc-admin-delete-commissions' ),
							'fetchData'     => wp_create_nonce( 'afwc-admin-commissions-dashboard-data' ),
							'savePlanOrder' => wp_create_nonce( 'afwc-admin-save-commission-order' ),
							'extraData'     => wp_create_nonce( 'afwc-admin-extra-data' ),
							'searchPlan'    => wp_create_nonce( 'afwc-admin-search-commission-plans' ),
						),
					),
					'settingsLink'                   => $settings_link,
					'currencySymbol'                 => AFWC_CURRENCY,
					'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
					'home_url'                       => home_url(),
					'afwc_filters'                   => $afwc_filters,
					'plan_dashboard_data'            => $plan_dashboard_data,
					'can_ask_for_feedback'           => $can_ask_for_feedback,
					'review_link'                    => $review_link,
					'show_admin_notice'              => $show_admin_notice,
					'show_admin_notice_for_dates'    => $show_admin_notice_for_dates,
					'is_process_running'             => $is_process_running,
					'is_action_scheduler_exists'     => $is_action_scheduler_exists,
					'affiliate_list_limit'           => AFWC_AFFILIATES_LIMIT,
					'default_plan_id'                => $default_plan_id,
					'precision'                      => afwc_get_price_decimals(),
					'thousandSeperator'              => afwc_get_price_thousand_separator(),
					'decimal'                        => afwc_get_price_decimal_separator(),
					'commissionStatuses'             => afwc_get_commission_statuses(),
					'campaignStatuses'               => is_callable( array( 'AFWC_Campaign_Dashboard', 'get_statuses' ) ) ? AFWC_Campaign_Dashboard::get_statuses() : array(),
					'commissionPlanStatuses'         => is_callable( array( 'AFWC_Commission_Dashboard', 'get_statuses' ) ) ? AFWC_Commission_Dashboard::get_statuses() : array(),
					'show_masspay_deprecated_notice' => 'paypal_masspay' === get_option( 'afwc_commission_payout_method' ),
					'dashboard_data_batch_limit'     => is_callable( array( $afwc_admin_affiliates, 'get_batch_limit' ) ) ? $afwc_admin_affiliates->get_batch_limit() : AFWC_ADMIN_DASHBOARD_DEFAULT_BATCH_LIMIT,
					'payoutMethods'                  => afwc_get_payout_methods(),
					'storeCurrencyCode'              => get_woocommerce_currency(),
					'pname'                          => afwc_get_pname(),
					'isPrettyReferralEnabled'        => get_option( 'afwc_use_pretty_referral_links', 'no' ),
				)
			);

			?>
				<style type="text/css">
					#wpcontent { 
						padding-left: 0 !important;
					}
				</style>
				<div id="afw-admin-dasboard" class="afw-admin-dasboard"></div>
			<?php
		}

		/**
		 * Function to show feedback
		 */
		public static function show_feedback() {
			$feedback_option_review = get_option( 'afwc_feedback_option_review', false );
			if ( ! empty( $feedback_option_review ) ) {
				return false;
			}
			$current_date         = gmdate( 'Y-m-d H:i:s', Affiliate_For_WooCommerce::get_offset_timestamp() );
			$feedback_start_date  = get_option( 'afwc_feedback_start_date', false );
			$feedback_close_date  = get_option( 'afwc_feedback_close_date', false );
			$diff                 = ceil( abs( strtotime( $current_date ) - strtotime( $feedback_start_date ) ) / 86400 );
			$can_ask_for_feedback = ( $diff > 15 ) ? true : false;
			$close_diff           = ! empty( $feedback_close_date ) ? ceil( abs( strtotime( $feedback_close_date ) - strtotime( $current_date ) ) / 86400 ) : 0;
			$can_ask_for_feedback = ( ! empty( $close_diff ) && $close_diff < 15 ) ? false : $can_ask_for_feedback;
			return $can_ask_for_feedback;
		}

		/**
		 * Function to handle all ajax request
		 */
		public function request_handler() {
			if ( ! current_user_can( 'manage_woocommerce' ) || empty( $_REQUEST ) || empty( wc_clean( wp_unslash( $_REQUEST['cmd'] ) ) ) ) { // phpcs:ignore
				return;
			}

			$params = array_map(
				function ( $request_param ) {
					return trim( wc_clean( wp_unslash( $request_param ) ) );
				},
				$_REQUEST // phpcs:ignore
			);

			$func_nm = ! empty( $params['cmd'] ) ? $params['cmd'] : '';
			if ( empty( $func_nm ) || ! in_array( $func_nm, $this->ajax_events, true ) ) {
				wp_die( esc_html_x( 'You are not allowed to use this action', 'authorization failure message', 'affiliate-for-woocommerce' ) );
			}

			$params['from'] = get_gmt_from_date( ( ( ! empty( $params['from_date'] ) ) ? $params['from_date'] : '' ), 'Y-m-d' );
			$params['to']   = get_gmt_from_date( ( ( ! empty( $params['to_date'] ) ) ? $params['to_date'] : '' ), 'Y-m-d' );

			$params['affiliate_id'] = ! empty( $params['affiliate_id'] ) ? $params['affiliate_id'] : 0;
			$params['page']         = ! empty( $params['page'] ) ? $params['page'] : 1;

			if ( is_callable( array( $this, $func_nm ) ) ) {
				$this->$func_nm( $params );
			}
		}

		/**
		 * Function to change commission status of an order for a single affiliate.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function update_commission_status( $params = array() ) {

			check_admin_referer( 'afwc-admin-update-commission-status', 'security' );

			if ( empty( $params['status'] ) ) {
				wp_send_json(
					array(
						'ACK'   => 'Error',
						'error' => _x( 'Status missing', 'error message for requested parameter missing', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$records = false;

			if ( ! empty( $params['referral_ids'] ) ) {

				$referral_ids = json_decode( $params['referral_ids'], true );

				$records = $this->set_commission_status(
					array(
						'status'       => $params['status'],
						'referral_ids' => ( is_array( $referral_ids ) && ! empty( $referral_ids ) ) ? array_map( 'intval', $referral_ids ) : array(),
					)
				);

			} elseif ( ! empty( $params['order_ids'] ) && ! empty( $params['affiliate_id'] ) ) {

				$order_ids = json_decode( $params['order_ids'], true );
				// set commission status.
				$records = $this->set_commission_status(
					array(
						'status'       => $params['status'],
						'order_ids'    => ( is_array( $order_ids ) && ! empty( $order_ids ) ) ? array_map( 'intval', $order_ids ) : array(),
						'affiliate_id' => intval( $params['affiliate_id'] ),
					)
				);

			} else {
				wp_send_json(
					array(
						'ACK'   => 'Error',
						'error' => __( 'Required params missing', 'affiliate-for-woocommerce' ),
					)
				);
			}

			if ( false === $records ) {
				// Return if query execution is failed.
				wp_send_json(
					array(
						'ACK'     => 'Error',
						'message' => __( 'Failed to update commission status, please try after some time.', 'affiliate-for-woocommerce' ),
					)
				);
			}

			wp_send_json(
				array(
					'ACK'     => 'Success',
					'message' => sprintf( // translators: Number of records updated in referrals table.
						_n( 'Commission status updated for %d record', 'Commission status updated for %d records', intval( $records ), 'affiliate-for-woocommerce' ),
						intval( $records )
					),
				)
			);

		}

		/**
		 * Handler for AJAX request for processing affiliate payouts.
		 *
		 * @todo Handle the case for multiple affiliate's payouts.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function process_payout( $params = array() ) {

			check_admin_referer( 'afwc-admin-process-payout', 'security' );

			if ( empty( $params['affiliate'] ) || empty( $params['selected_referrals'] ) || empty( $params['method'] ) ) {
				wp_send_json(
					array(
						'ACK'   => 'Error',
						'error' => __( 'Required params missing', 'affiliate-for-woocommerce' ),
					)
				);
			}

			global $wpdb;

			$affiliate          = ( ! empty( $params['affiliate'] ) ) ? json_decode( $params['affiliate'], true ) : array();
			$affiliate_id       = ( ! empty( $affiliate['id'] ) ) ? intval( $affiliate['id'] ) : '';
			$selected_referrals = ( ! empty( $params['selected_referrals'] ) ) ? json_decode( $params['selected_referrals'], true ) : array();
			$note               = ( ! empty( $params['note'] ) ) ? $params['note'] : '';
			$woo_currencies     = get_woocommerce_currencies();
			$currency           = ( ! empty( $params['currency'] ) && ! empty( $woo_currencies ) && in_array( $params['currency'], array_keys( $woo_currencies ), true ) ) ? $params['currency'] : get_woocommerce_currency();

			$payout_result = array();

			// For now, only checking for 1st Affiliate, Multiple Affiliates Payout is not yet implemented.
			if ( 'paypal' === $params['method'] && ! empty( $affiliate['email'] ) && ! empty( $affiliate['amount'] ) ) {
				$paypal            = AFWC_PayPal_API::get_instance();
				$affiliate['note'] = $note;

				if ( ! in_array( $currency, AFWC_PayPal_API::$paypal_supported_currency, true ) ) {
					/* translators: Currency code */
					Affiliate_For_WooCommerce::get_instance()->log( 'error', sprintf( _x( 'PayPal payout failed as %s currency is not supported.', 'payout failed debug message', 'affiliate-for-woocommerce' ), $currency ) ); // phpcs:ignore
					wp_send_json(
						array(
							'ACK'   => 'Error',
							'error' => _x( 'PayPal payout failed.', 'PayPal Payout error message', 'affiliate-for-woocommerce' ),
						)
					);
				}

				$payout_result = is_callable( array( $paypal, 'process_paypal_mass_payment' ) ) ? $paypal->process_paypal_mass_payment( array( $affiliate ), $currency ) : array( 'ACK' => 'Error' );

				if ( is_wp_error( $payout_result ) || 'Success' !== $payout_result['ACK'] ) {
					/* translators: PayPal response message */
					Affiliate_For_WooCommerce::get_instance()->log( 'error', sprintf( _x( 'PayPal payout failed. Message: %1$s Response: %2$s.', 'payout failed debug message', 'affiliate-for-woocommerce' ), is_wp_error( $payout_result ) && is_callable( array( $payout_result, 'get_error_message' ) ) ? $payout_result->get_error_message() : '', print_r( $payout_result, true ) ) ); // phpcs:ignore

					wp_send_json(
						array(
							'ACK'   => 'Error',
							'error' => _x( 'PayPal payout failed.', 'PayPal Payout error message', 'affiliate-for-woocommerce' ),
						)
					);
				}
			}

			// Get all order ids.
			$referral_ids = array_map(
				function( $obj ) {
					if ( ! empty( $obj['referral_id'] ) ) {
						return intval( $obj['referral_id'] );
					}
				},
				$selected_referrals
			);

			// set commission status.
			$result = $this->set_commission_status(
				array(
					'status'       => AFWC_REFERRAL_STATUS_PAID,
					'referral_ids' => $referral_ids,
				)
			);

			if ( 0 < (int) $result ) {

				$payout_details = array(
					'affiliate_id'    => $affiliate_id,
					'datetime'        => get_gmt_from_date( ( ( ! empty( $params['date'] ) ) ? $params['date'] : '' ), 'Y-m-d H:i:s' ),
					'amount'          => floatval( ! empty( $payout_result['amount'] ) ? $payout_result['amount'] : ( ! empty( $affiliate['amount'] ) ? $affiliate['amount'] : 0.00 ) ),
					'currency'        => $currency,
					'payout_notes'    => $note,
					'payment_gateway' => ( ! empty( $params['method'] ) ) ? $params['method'] : 'other',
					'receiver'        => ( ! empty( $affiliate['email'] ) ) ? $affiliate['email'] : '',
					'type'            => '',
				);

				$records = $this->update_payout_table( $payout_details );

				if ( false === $records ) {
					wp_send_json(
						array(
							'ACK'   => 'Error',
							'error' => __( 'Payout entry failed', 'affiliate-for-woocommerce' ),
						)
					);
				} else {
					$inserted_payout_id = $wpdb->insert_id;

					// Code to update the payout_orders table.
					$values               = array();
					$selected_order_dates = array(
						'from' => '',
						'to'   => '',
					);

					$payout_orders_table = afwc_get_tablename( 'payout_orders' );
					foreach ( $selected_referrals as $order ) {
						if ( empty( $selected_order_dates['from'] ) ) {
							$selected_order_dates['from'] = $order['date'];
						} elseif ( strtotime( $selected_order_dates['from'] ) > strtotime( $order['date'] ) ) {
							$selected_order_dates['from'] = $order['date'];
						}

						if ( empty( $selected_order_dates['to'] ) ) {
							$selected_order_dates['to'] = $order['date'];
						} elseif ( strtotime( $selected_order_dates['to'] ) < strtotime( $order['date'] ) ) {
							$selected_order_dates['to'] = $order['date'];
						}

						$wpdb->insert(
							$payout_orders_table,
							array(
								'payout_id' => $inserted_payout_id,
								'post_id'   => $order['order_id'],
								'amount'    => $order['commission'],
							)
						); // WPCS: db call ok.
					}

					// Send commission paid email to affiliate if enabled.
					if ( true === AFWC_Emails::is_afwc_mailer_enabled( 'afwc_email_commission_paid' ) ) {
						// Trigger email.
						do_action(
							'afwc_email_commission_paid',
							array(
								'affiliate_id'          => $affiliate_id,
								'amount'                => ! empty( $payout_details['amount'] ) ? floatval( $payout_details['amount'] ) : 0.00,
								'currency_id'           => $currency,
								'from_date'             => $selected_order_dates['from'],
								'to_date'               => $selected_order_dates['to'],
								'total_referrals'       => count( array_column( $selected_referrals, 'order_id' ) ),
								'payout_notes'          => $note,
								'payment_gateway'       => ( ! empty( $payout_details['payment_gateway'] ) ) ? $payout_details['payment_gateway'] : 'other',
								'paypal_receiver_email' => ( ! empty( $payout_details['receiver'] ) ) ? $payout_details['receiver'] : '', // For PayPal mass payout else empty.
							)
						);
					}

					wp_send_json(
						array(
							'ACK'                    => 'Success',
							'last_added_payout_id'   => $inserted_payout_id,
							'last_added_payout_data' => array(
								'amount'         => ( ! empty( $payout_details['amount'] ) ) ? floatval( $payout_details['amount'] ) : 0.00,
								'currency'       => ( ! empty( $payout_details['currency'] ) ) ? $payout_details['currency'] : '',
								'datetime'       => gmdate( 'd-M-Y', strtotime( $payout_details['datetime'] ) ),
								'from_date'      => $selected_order_dates['from'],
								'to_date'        => $selected_order_dates['to'],
								'method'         => ( ! empty( $payout_details['payment_gateway'] ) ) ? $payout_details['payment_gateway'] : 'other',
								'referral_count' => count( $selected_referrals ),
								'payout_id'      => $inserted_payout_id,
								'payout_notes'   => ! empty( $payout_details['payout_notes'] ) ? $payout_details['payout_notes'] : '',
							),
						)
					);
				}
			}
		}

		/**
		 * Handler for AJAX request for getting affiliate KPI data.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function dashboard_kpi_data( $params = array() ) {

			check_admin_referer( 'afwc-admin-dashboard-kpi-data', 'security' );

			$affiliates              = $this->affiliates_list( $params );
			$affiliate_ids           = array_map(
				function( $affiliates ) {
					return ! empty( $affiliates['affiliate_id'] ) ? $affiliates['affiliate_id'] : 0;
				},
				$affiliates
			);
			$params['affiliate_ids'] = array_filter( $affiliate_ids );

			wp_send_json(
				array(
					'kpi' => $this->kpi_data( $params ),
				)
			);
		}

		/**
		 * Handler for AJAX request for getting affiliate list.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function dashboard_data( $params = array() ) {

			check_admin_referer( 'afwc-admin-dashboard-data', 'security' );

			wp_send_json(
				array(
					'affiliateList' => $this->affiliates_list( $params ),
				)
			);
		}

		/**
		 * Handler for AJAX request for getting affiliate dashboard KPI data.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function kpi_data( $params = array() ) {
			// current data as per date filters.
			// pass empty affiliate_ids so we can have KPI for all affiliate without limit.
			$search_term  = ! empty( $params['q'] ) ? $params['q'] : '';
			$afwc_filters = ( ! empty( $params['filters'] ) ) ? json_decode( $params['filters'], true ) : array();

			$affiliate_ids                  = ! empty( $params['affiliate_ids'] ) ? $params['affiliate_ids'] : array();
			$affiliate_ids                  = ( empty( $afwc_filters ) && empty( $search_term ) ) ? array() : $affiliate_ids;
			$aa_filtered                    = new AFWC_Admin_Affiliates( $affiliate_ids, $params['from'], $params['to'] );
			$aa_filtered->affiliates_orders = $aa_filtered->get_affiliates_orders();
			$aa_filtered->affiliates_refund = $aa_filtered->get_affiliates_refund();
			$aa_filtered->affiliates_sales  = $aa_filtered->get_affiliates_sales();
			$total_sales                    = $aa_filtered->get_storewide_sales();
			$net_sales                      = $aa_filtered->get_net_affiliates_sales();
			$aggregated                     = $aa_filtered->get_commissions_customers();
			$visitor_count                  = $aa_filtered->get_visitors_count();
			$customers_count                = $aggregated['customers_count'];
			$affiliates_count               = ! empty( $affiliate_ids ) ? count( $affiliate_ids ) : $this->get_affiliate_count( $params );
			$afwc                           = array(
				'net_affiliates_sales' => afwc_format_price( $net_sales ),
				'total_sales'          => $total_sales,
				'paid_commissions'     => afwc_format_price( $aggregated['paid_commissions'] ),
				'unpaid_commissions'   => afwc_format_price( $aggregated['unpaid_commissions'] ),
				'paid_commissions'     => afwc_format_price( $aggregated['paid_commissions'] - $aggregated['unpaid_commissions'] ),
				'unpaid_affiliates'    => afwc_format_price( $aggregated['unpaid_affiliates'], 0 ),
				'all_customers_count'  => afwc_format_price( $customers_count, 0 ),
				'visitors_count'       => afwc_format_price( $visitor_count, 0 ),
				'affiliates_count'     => $affiliates_count,
			);

			$afwc['percent_of_total_sales'] = afwc_format_price( ( ( $total_sales > 0 ) ? ( $net_sales * 100 ) / $total_sales : 0 ) );
			$afwc['conversion_rate']        = afwc_format_price( ( ( $visitor_count > 0 ) ? $afwc['all_customers_count'] * 100 / $visitor_count : 0 ) );
			return $afwc;
		}

		/**
		 * Get affiliate count
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function get_affiliate_count( $params = array() ) {
			$affiliate_count = 0;
			global $wpdb;

			$afwc_filters            = ( ! empty( $params['filters'] ) ) ? json_decode( $params['filters'], true ) : array();
			$params['status']        = '';
			$params['affiliate_ids'] = array();

			$is_affiliate_status_filter = false;
			if ( ! empty( $afwc_filters['affiliate_status'] ) ) {
				$is_affiliate_status_filter = true;
			}

			$default_filters['affiliate_status'] = array( 'yes', 'pending' );
			$default_filters['order_status']     = array();
			$default_filters['tags']             = array();

			$search_term = ! empty( $params['q'] ) ? $params['q'] : '';

			foreach ( $default_filters as $filter => $filter_val ) {
				if ( ! empty( $afwc_filters[ $filter ] ) ) {
					$default_filters[ $filter ] = $afwc_filters[ $filter ];
				}
			}
			$afwc_filters = $default_filters;

			// code for filters.
			$wpdb1 = $wpdb;

			$referrals_join_cond     = " JOIN {$wpdb->prefix}afwc_referrals as ref
										ON(ref.affiliate_id = u.ID) ";
			$filters_join_cond       = ' LEFT ' . $referrals_join_cond;
			$filters_where_cond      = ' 1=1 ';
			$filters_umeta_join_cond = '';
			$filters_user_where_cond = ' 1=1 ';

			// conditions when filtered by affiliate status.
			$user_role_cond       = '';
			$affiliate_user_roles = get_option( 'affiliate_users_roles', array() );
			if ( ! empty( $affiliate_user_roles ) ) {
				$cond = array();
				foreach ( $affiliate_user_roles as $role ) {
					$cond[] = $wpdb1->prepare( // phpcs:ignore
						'um.meta_value LIKE %s',
						'%' . $wpdb->esc_like( $role ) . '%'
					);

				}
				$user_role_cond = implode( ' OR ', $cond );
			}

			$status_filters = implode( ',', $afwc_filters['affiliate_status'] );
			if ( ! empty( $is_affiliate_status_filter ) ) {
				$filters_user_where_cond .= " AND (FIND_IN_SET (um.meta_value, '" . $status_filters . "')) ";
				$filters_umeta_join_cond  = " AND um.meta_key = 'afwc_is_affiliate' ";
			} else {
				$filters_user_where_cond .= " AND (FIND_IN_SET (um.meta_value, '" . $status_filters . "') " . ( ( ! empty( $user_role_cond ) ) ? ' OR ' . $user_role_cond : '' ) . ") 
										AND um.user_id NOT IN (SELECT user_id 
																FROM {$wpdb->usermeta}
																WHERE meta_key = 'afwc_is_affiliate'
																	AND meta_value = 'no') ";
				$filters_umeta_join_cond  = " AND um.meta_key IN ('afwc_is_affiliate'" . ( ( ! empty( $user_role_cond ) ) ? ", '{$wpdb->prefix}capabilities'" : '' ) . ') ';
			}

			// Conditions when filtered by commission status.
			if ( ! empty( $afwc_filters['order_status'] ) ) {
				$filters_join_cond   = $referrals_join_cond;
				$filters_where_cond .= " AND (FIND_IN_SET (ref.status, '" . implode( ',', $afwc_filters['order_status'] ) . "')) ";
			}

			// Conditions when filtered by full text searxh box.
			if ( ! empty( $search_term ) ) {
				$filters_where_cond .= $wpdb1->prepare( // phpcs:ignore
					' AND ( u.user_nicename LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s ) ',
					'%' . $wpdb->esc_like( $search_term ) . '%',
					'%' . $wpdb->esc_like( $search_term ) . '%',
					'%' . $wpdb->esc_like( $search_term ) . '%'
				);
			}

			if ( ! empty( $afwc_filters['tags'] ) ) {
				$filters_join_cond  .= " JOIN {$wpdb->prefix}term_relationships as tr
										ON(tr.object_id = u.ID) ";
				$filters_where_cond .= " AND (FIND_IN_SET(tr.term_taxonomy_id, '" . implode( ',', $afwc_filters['tags'] ) . "')) ";
			}

			$affiliate_count =  $wpdb1->get_var( // phpcs:ignore
				"SELECT COUNT( DISTINCT u.ID ) AS aff_count
												    FROM (SELECT u.ID AS ID,
															u.display_name AS display_name,
															u.user_email AS user_email,
															u.user_nicename AS user_nicename,
															(CASE WHEN um.meta_key = 'afwc_is_affiliate' THEN 1 ELSE 0 END) as priority,
															IFNULL((CASE WHEN um.meta_key = 'afwc_is_affiliate' AND um.meta_value = 'pending' THEN 1 ELSE 0 END), 0) as is_pending
															FROM $wpdb->users as u
																JOIN $wpdb->usermeta as um
																ON(um.user_id = u.ID " . $filters_umeta_join_cond . ')
															WHERE ' . $filters_user_where_cond . "
															GROUP BY ID) as u
															LEFT JOIN (SELECT IFNULL(COUNT( DISTINCT CONCAT_WS( ':', ip, user_id ) ), 0) as total_visitors,
																	affiliate_id
																FROM {$wpdb->prefix}afwc_hits
																) as hits
															ON(hits.affiliate_id = u.ID)
															" . $filters_join_cond . '
															WHERE ' . $filters_where_cond
			);
			return $affiliate_count;

		}

		/**
		 * Handler for AJAX request for getting affiliate's list
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function affiliates_list( $params = array() ) {
			global $wpdb;

			$afwc_filters            = ( ! empty( $params['filters'] ) ) ? json_decode( $params['filters'], true ) : array();
			$affiliate_ids           = array();
			$affiliates              = array();
			$params['status']        = '';
			$params['affiliate_ids'] = array();
			$params['limit']         = AFWC_AFFILIATES_LIMIT;
			$start_limit             = ( ! empty( $params['page'] ) ) ? ( intval( $params['page'] ) - 1 ) * $params['limit'] : 0;

			$params['is_export'] = ! empty( $params['is_export'] ) ? $params['is_export'] : false;

			$limit = ( empty( $params['is_export'] ) ) ? ' LIMIT ' . $start_limit . ', ' . $params['limit'] : '';

			$is_affiliate_status_filter = false;
			if ( ! empty( $afwc_filters['affiliate_status'] ) ) {
				$is_affiliate_status_filter = true;
			}

			$default_filters['affiliate_status'] = array( 'yes', 'pending' );
			$default_filters['order_status']     = array();
			$default_filters['tags']             = array();

			$search_term = ! empty( $params['q'] ) ? $params['q'] : '';

			foreach ( $default_filters as $filter => $filter_val ) {
				if ( ! empty( $afwc_filters[ $filter ] ) ) {
					$default_filters[ $filter ] = $afwc_filters[ $filter ];
				}
			}
			$afwc_filters = $default_filters;

			// code for filters.
			$wpdb1 = $wpdb;

			$referrals_join_cond     = " JOIN {$wpdb->prefix}afwc_referrals as ref
										ON(ref.affiliate_id = u.ID) ";
			$filters_join_cond       = ' LEFT ' . $referrals_join_cond;
			$filters_where_cond      = ' 1=1 ';
			$filters_umeta_join_cond = '';
			$filters_user_where_cond = ' 1=1 ';

			// conditions when filtered by affiliate status.
			$user_role_cond       = '';
			$affiliate_user_roles = get_option( 'affiliate_users_roles', array() );
			if ( ! empty( $affiliate_user_roles ) ) {
				$cond = array();
				foreach ( $affiliate_user_roles as $role ) {
					$cond[] = $wpdb1->prepare( // phpcs:ignore
						'um.meta_value LIKE %s',
						'%' . $wpdb->esc_like( $role ) . '%'
					);

				}
				$user_role_cond = implode( ' OR ', $cond );
			}

			$status_filters = implode( ',', $afwc_filters['affiliate_status'] );
			if ( ! empty( $is_affiliate_status_filter ) ) {
				$filters_user_where_cond .= " AND (FIND_IN_SET (um.meta_value, '" . $status_filters . "')) ";
				$filters_umeta_join_cond  = " AND um.meta_key = 'afwc_is_affiliate' ";
			} else {
				$filters_user_where_cond .= " AND (FIND_IN_SET (um.meta_value, '" . $status_filters . "') " . ( ( ! empty( $user_role_cond ) ) ? ' OR ' . $user_role_cond : '' ) . ") 
										AND um.user_id NOT IN (SELECT user_id 
																FROM {$wpdb->usermeta}
																WHERE meta_key = 'afwc_is_affiliate'
																	AND meta_value = 'no') ";
				$filters_umeta_join_cond  = " AND um.meta_key IN ('afwc_is_affiliate'" . ( ( ! empty( $user_role_cond ) ) ? ", '{$wpdb->prefix}capabilities'" : '' ) . ') ';
			}

			// Conditions when filtered by commission status.
			if ( ! empty( $afwc_filters['order_status'] ) ) {
				$filters_join_cond   = $referrals_join_cond;
				$filters_where_cond .= " AND (FIND_IN_SET (ref.status, '" . implode( ',', $afwc_filters['order_status'] ) . "')) ";
			}

			// Conditions when filtered by full text searxh box.
			if ( ! empty( $search_term ) ) {
				$filters_where_cond .= $wpdb1->prepare( // phpcs:ignore
					' AND ( u.user_nicename LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s ) ',
					'%' . $wpdb->esc_like( $search_term ) . '%',
					'%' . $wpdb->esc_like( $search_term ) . '%',
					'%' . $wpdb->esc_like( $search_term ) . '%'
				);
			}

			if ( ! empty( $afwc_filters['tags'] ) ) {
				$filters_join_cond  .= " JOIN {$wpdb->prefix}term_relationships as tr
										ON(tr.object_id = u.ID) ";
				$filters_where_cond .= " AND (FIND_IN_SET(tr.term_taxonomy_id, '" . implode( ',', $afwc_filters['tags'] ) . "')) ";
			}

			$affiliates                   = array();
			$paid_order_statuses          = afwc_get_paid_order_status();
			$paid_order_statuses_imploded = ( ! empty( $paid_order_statuses ) ) ? implode( ',', $paid_order_statuses ) : '';

			$results =  $wpdb1->get_results( // phpcs:ignore
											$wpdb1->prepare( // phpcs:ignore
												"SELECT DISTINCT u.ID AS affiliate_id,
													u.display_name AS display_name,
													u.user_email AS email,
													u.is_pending as is_pending,
													IFNULL(SUM( CASE WHEN ref.status != 'draft' AND ref.datetime BETWEEN %s AND %s THEN ref.amount ELSE 0 END), 0) as earned_commissions,
													IFNULL(SUM( CASE WHEN ref.status != 'draft' AND ref.datetime BETWEEN %s AND %s AND status = 'unpaid' AND FIND_IN_SET (ref.order_status, '" . $paid_order_statuses_imploded . "') THEN ref.amount ELSE 0 END), 0) as unpaid_commissions,
													IFNULL((CASE WHEN ref.status != 'draft' THEN ref.currency_id ELSE '' END), '') as currency,
													IFNULL(SUM( CASE WHEN ref.status != 'draft' AND ref.datetime BETWEEN %s AND %s THEN 1 ELSE 0 END), 0) as total_order,
													IFNULL(IF( ref.status != 'draft' AND ref.affiliate_id IS NOT NULL, COUNT( DISTINCT IF( ref.user_id > 0, ref.user_id, CONCAT_WS( ':', ref.ip, ref.user_id ) ) ), 0), 0) as customers_count,
													IFNULL(hits.total_visitors, 0) as total_visitors
												FROM (SELECT u.ID AS ID,
															u.display_name AS display_name,
															u.user_email AS user_email,
															u.user_nicename AS user_nicename,
															(CASE WHEN um.meta_key = 'afwc_is_affiliate' THEN 1 ELSE 0 END) as priority,
															IFNULL((CASE WHEN um.meta_key = 'afwc_is_affiliate' AND um.meta_value = 'pending' THEN 1 ELSE 0 END), 0) as is_pending
															FROM $wpdb->users as u
																JOIN $wpdb->usermeta as um
																ON(um.user_id = u.ID " . $filters_umeta_join_cond . ')
															WHERE ' . $filters_user_where_cond . "
															GROUP BY ID) as u
															LEFT JOIN (SELECT IFNULL(COUNT( DISTINCT CONCAT_WS( ':', ip, user_id ) ), 0) as total_visitors,
																	affiliate_id
																FROM {$wpdb->prefix}afwc_hits
																WHERE datetime BETWEEN %s AND %s
																GROUP BY affiliate_id) as hits
															ON(hits.affiliate_id = u.ID)
															" . $filters_join_cond . '
															WHERE ' . $filters_where_cond . '
															GROUP BY affiliate_id
															ORDER BY earned_commissions DESC, customers_count DESC, u.priority DESC, total_visitors DESC
															' . $limit,
												$params['from'] . ' 00:00:00',
												$params['to'] . ' 23:59:59',
												$params['from'] . ' 00:00:00',
												$params['to'] . ' 23:59:59',
												$params['from'] . ' 00:00:00',
												$params['to'] . ' 23:59:59',
												$params['from'] . ' 00:00:00',
												$params['to'] . ' 23:59:59'
											),
				'ARRAY_A'
			);
			if ( count( $results ) > 0 ) {
				foreach ( $results as $affiliate ) {
					$id                = ( ( ! empty( $affiliate['affiliate_id'] ) ) ? $affiliate['affiliate_id'] : 0 );
					$earned_commission = ( ! empty( $affiliate['earned_commissions'] ) ) ? $affiliate['earned_commissions'] : 0;
					$unpaid_commission = ( ! empty( $affiliate['unpaid_commissions'] ) ) ? $affiliate['unpaid_commissions'] : 0;

					if ( empty( $id ) ) {
						continue;
					}

					$affiliate_ids[] = $id;
					$affiliates[]    = array(
						'affiliate_id'       => $id,
						'name'               => ( ( ! empty( $affiliate['display_name'] ) ) ? $affiliate['display_name'] : '' ),
						'email'              => ( ( ! empty( $affiliate['email'] ) ) ? $affiliate['email'] : '' ),
						'earned_commissions' => ( ( empty( $params['is_export'] ) ) ? afwc_format_price( $earned_commission ) : $earned_commission ),
						'unpaid_commissions' => ( ( empty( $params['is_export'] ) ) ? afwc_format_price( $unpaid_commission ) : $unpaid_commission ),
						'currency'           => ( ( ! empty( $affiliate['currency'] ) ) ? $affiliate['currency'] : get_woocommerce_currency() ),
						'customers_count'    => ( ( ! empty( $affiliate['customers_count'] ) ) ? $affiliate['customers_count'] : 0 ),
						'total_order'        => ( ( ! empty( $affiliate['total_order'] ) ) ? $affiliate['total_order'] : 0 ),
						'total_visitors'     => ( ( ! empty( $affiliate['total_visitors'] ) ) ? $affiliate['total_visitors'] : 0 ),
						'pending'            => ( ( ! empty( $affiliate['is_pending'] ) ) ? intval( $affiliate['is_pending'] ) : 0 ),
						'paypal_email'       => get_user_meta( $id, 'afwc_paypal_email', true ),
					);
				}
			}

			return $affiliates;
		}

		/**
		 * Function to generate and export the CSV data
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function export_affiliates( $params = array() ) {
			check_admin_referer( 'afwc-admin-export-affiliates', 'security' );
			global $wpdb;

			$affiliates_data     = array();
			$params['is_export'] = true;
			$affiliates_data     = $this->affiliates_list( $params );
			$type                = ! empty( $params['type'] ) ? $params['type'] : 'standard';

			$wp_upload_path = wp_get_upload_dir();

			if ( empty( $wp_upload_path ) || empty( $wp_upload_path['basedir'] ) ) {
				Affiliate_For_WooCommerce::get_instance()->log( 'error', _x( 'WordPress upload directory is not set.', 'csv export error message', 'affiliate-for-woocommerce' ) );
				return;
			}
			$path     = $wp_upload_path['basedir'] . '/woocommerce_uploads/';
			$filename = sanitize_title( get_bloginfo( 'name' ) ) . '_' . $type . '_affiliates_' . gmdate( 'd-M-Y' ) . '.csv';
			$file     = $path . $filename;

			// open raw memory as file so no temp files needed, you might run out of memory though.
			$f = fopen( $file , 'w+' );// phpcs:ignore
			// loop over the input array.
			if ( 'standard' === $type ) {
				$headers = array( 'Name', 'Email', 'Earned Commissions', 'Unpaid Commissions', 'Total Order', 'Total Visitors' );
			} elseif ( 'mass_payment' === $type ) {
				$headers = array( 'Emails', 'Unpaid Commissions', 'Currency' );
			}
			fwrite( $f, implode( ',', $headers ) . PHP_EOL );// phpcs:ignore

			foreach ( $affiliates_data as $row ) {
				// format array.
				$line = array();
				if ( 'standard' === $type ) {
					$line['name']               = $row['name'];
					$line['email']              = $row['email'];
					$line['earned_commissions'] = $row['earned_commissions'];
					$line['unpaid_commissions'] = $row['unpaid_commissions'];
					$line['total_order']        = $row['total_order'];
					$line['total_visitors']     = $row['total_visitors'];
				} elseif ( 'mass_payment' === $type ) {
					$line['email']              = ! empty( $row['paypal_email'] ) ? $row['paypal_email'] : $row['email'];
					$line['unpaid_commissions'] = $row['unpaid_commissions'];
					$line['currency']           = $row['currency'];
				}
				// generate csv lines from the inner arrays.
				fwrite( $f, implode( ',', $line ) . PHP_EOL ); // phpcs:ignore
			}
			// reset the file pointer to the start of the file.
			fseek( $f, 0 );
			// tell the browser it's going to be a csv file.
			header( 'Content-type: text/x-csv; charset=UTF-8' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// make php send the generated csv lines to the browser.
			function_exists( 'fpassthru' ) ? fpassthru( $f ) : readfile( $file ); // phpcs:ignore

			// Delete file from uploads.
			unlink( $file );
			exit();
		}


		/**
		 * Handler for AJAX request for getting affiliate order details.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function order_details( $params = array() ) {
			check_admin_referer( 'afwc-admin-order-details', 'security' );
			$current_data = new AFWC_Admin_Affiliates(
				! empty( $params['affiliate_id'] ) ? $params['affiliate_id'] : 0,
				! empty( $params['from'] ) ? $params['from'] : '',
				! empty( $params['to'] ) ? $params['to'] : '',
				! empty( $params['page'] ) ? intval( $params['page'] ) : 1
			);
			wp_send_json( is_callable( array( $current_data, 'get_affiliates_order_details' ) ) ? $current_data->get_affiliates_order_details() : array() );
		}

		/**
		 * Handler for AJAX request for getting affiliate payout details.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function payout_details( $params = array() ) {
			check_admin_referer( 'afwc-admin-payout-details', 'security' );
			$current_data = new AFWC_Admin_Affiliates(
				! empty( $params['affiliate_id'] ) ? $params['affiliate_id'] : 0,
				! empty( $params['from'] ) ? $params['from'] : '',
				! empty( $params['to'] ) ? $params['to'] : '',
				! empty( $params['page'] ) ? intval( $params['page'] ) : 1
			);
			wp_send_json( is_callable( array( $current_data, 'get_affiliates_payout_history' ) ) ? $current_data->get_affiliates_payout_history() : array() );
		}

		/**
		 * Handler for AJAX request for getting top products.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function top_products( $params = array() ) {
			check_admin_referer( 'afwc-admin-top-products', 'security' );
			if ( empty( $params['affiliate_id'] ) ) {
				wp_send_json(
					array(
						'ACK'     => 'Error',
						'message' => _x( 'Required parameter missing.', 'error message when fetching top products', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$affiliate_data = new AFWC_Admin_Affiliates(
				$params['affiliate_id'],
				! empty( $params['from'] ) ? $params['from'] : '',
				! empty( $params['to'] ) ? $params['to'] : ''
			);

			wp_send_json(
				array(
					'ACK'  => 'Success',
					'data' => is_callable( array( $affiliate_data, 'get_affiliates_top_products' ) ) ? $affiliate_data->get_affiliates_top_products() : array(),
				)
			);
		}

		/**
		 * Handler for AJAX request for getting profile data.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function profile_data( $params = array() ) {
			check_admin_referer( 'afwc-admin-profile-data', 'security' );
			if ( empty( $params['affiliate_id'] ) ) {
				wp_send_json(
					array(
						'ACK'     => 'Error',
						'message' => _x( 'Required parameter missing.', 'error message when fetching profile details', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$affiliate_data = new AFWC_Admin_Affiliates( $params['affiliate_id'] );
			$is_ltc_enabled = get_option( 'afwc_enable_lifetime_commissions', 'no' );

			$affiliate_obj = new AFWC_Affiliate( $params['affiliate_id'] );

			wp_send_json(
				array(
					'ACK'  => 'Success',
					'data' => array(
						'tags'                         => is_callable( array( $affiliate_data, 'get_affiliates_tags' ) ) ? $affiliate_data->get_affiliates_tags() : array(),
						'coupons'                      => is_callable( array( $affiliate_data, 'get_affiliates_coupons' ) ) ? $affiliate_data->get_affiliates_coupons() : array(),
						'is_referral_coupon_enabled'   => get_option( 'afwc_use_referral_coupons', 'yes' ),
						'is_ltc_enabled'               => $is_ltc_enabled,
						'is_ltc_enabled_for_affiliate' => is_callable( array( $affiliate_obj, 'is_ltc_enabled' ) ) && $affiliate_obj->is_ltc_enabled(),
						'ltc_customers'                => ( 'yes' === $is_ltc_enabled && is_callable( array( $affiliate_data, 'get_ltc_customers' ) ) ) ? $affiliate_data->get_ltc_customers() : array(),
					),
				)
			);

		}

		/**
		 * Function to get affiliate children chain data.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function affiliate_chain_data( $params = array() ) {
			check_admin_referer( 'afwc-admin-multi-tier-data', 'security' );
			if ( empty( $params['affiliate_id'] ) ) {
				wp_send_json(
					array(
						'ACK'   => 'Error',
						'error' => _x( 'Required params missing', 'error message when missing necessary parameters', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$affiliate_multi_tier = new AFWC_Multi_Tier();
			wp_send_json(
				array(
					'ACK'  => 'Success',
					'data' => array(
						'multiTierChain' => ( $affiliate_multi_tier instanceof AFWC_Multi_Tier && is_callable( array( $affiliate_multi_tier, 'get_children_data' ) ) ) ? $affiliate_multi_tier->get_children_data( array( 'affiliate_id' => intval( $params['affiliate_id'] ) ) ) : array(),
					),
				)
			);

		}

		/**
		 * Handler for AJAX request for getting affiliate KPI details.
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function affiliate_kpi_details( $params = array() ) {
			check_admin_referer( 'afwc-admin-affiliate-kpi-data', 'security' );
			if ( empty( $params['affiliate_id'] ) ) {
				wp_send_json(
					array(
						'ACK'     => 'Error',
						'message' => _x( 'Required parameter missing.', 'error message when fetching affiliate KPI details', 'affiliate-for-woocommerce' ),
					)
				);
			}
			$affiliate_id = intval( $params['affiliate_id'] );

			$all_time_data                    = new AFWC_Admin_Affiliates( $affiliate_id );
			$all_time_data->affiliates_orders = $all_time_data->get_affiliates_orders();
			$all_time_data->affiliates_refund = $all_time_data->get_affiliates_refund();
			$all_time_data->affiliates_sales  = $all_time_data->get_affiliates_sales();
			$all_time_commisions_customers    = $all_time_data->get_commissions_customers();
			$all_time_visitor_count           = $all_time_data->get_visitors_count();
			$all_time_paid_commissions        = floatval( ( ! empty( $all_time_commisions_customers['paid_commissions'] ) ) ? $all_time_commisions_customers['paid_commissions'] : 0 );
			$all_time_unpaid_commissions      = floatval( ( ! empty( $all_time_commisions_customers['unpaid_commissions'] ) ) ? $all_time_commisions_customers['unpaid_commissions'] : 0 );

			$current_data = new AFWC_Admin_Affiliates(
				$affiliate_id,
				! empty( $params['from'] ) ? $params['from'] : '',
				! empty( $params['to'] ) ? $params['to'] : ''
			);

			$current_data->get_all_data();

			wp_send_json(
				array(
					'stats' => array(
						'current' => array(
							'net_affiliates_sales' => afwc_format_price( $current_data->net_affiliates_sales ),
							'unpaid_commissions'   => afwc_format_price( $current_data->unpaid_commissions ),
							'paid_commissions'     => afwc_format_price( $current_data->earned_commissions - $current_data->unpaid_commissions ),
							'visitors_count'       => afwc_format_price( $current_data->visitors_count, 0 ),
							'customers_count'      => afwc_format_price( $current_data->customers_count, 0 ),
							'conversion_rate'      => afwc_format_price( ( ( $current_data->visitors_count > 0 ) ? $current_data->customers_count * 100 / $current_data->visitors_count : 0 ) ),
							'affiliates_refund'    => afwc_format_price( $current_data->affiliates_refund ),
							'earned_commissions'   => afwc_format_price( $current_data->earned_commissions ),
							'gross_commissions'    => afwc_format_price( $current_data->gross_commissions ),
						),
						'allTime' => array(
							'net_affiliates_sales' => afwc_format_price( $all_time_data->get_net_affiliates_sales() ),
							'unpaid_commissions'   => afwc_format_price( $all_time_unpaid_commissions ),
							'paid_commissions'     => afwc_format_price( $all_time_paid_commissions ),
							'visitors_count'       => afwc_format_price( $all_time_visitor_count, 0 ),
							'customers_count'      => afwc_format_price( ( ( ! empty( $all_time_commisions_customers['customers_count'] ) ) ? $all_time_commisions_customers['customers_count'] : 0 ), 0 ),
							'conversion_rate'      => afwc_format_price( ( ( $all_time_visitor_count > 0 ) ? $all_time_commisions_customers['customers_count'] * 100 / $all_time_visitor_count : 0 ) ),
							'affiliates_refund'    => afwc_format_price( $all_time_data->affiliates_refund ),
							'earned_commissions'   => afwc_format_price( floatval( $all_time_paid_commissions + $all_time_unpaid_commissions ) ),
						),
					),
				)
			);
		}

		/**
		 * Handler for AJAX request for getting affiliate details
		 *
		 * @param array $params Params from the AJAX request.
		 */
		public function affiliate_details( $params = array() ) {
			check_admin_referer( 'afwc-admin-affiliate-details', 'security' );
			$affiliate_id = ! empty( $params['affiliate_id'] ) ? $params['affiliate_id'] : 0;
			$is_affiliate = '';

			if ( ! empty( $affiliate_id ) ) {
				$is_affiliate = get_user_meta( $affiliate_id, 'afwc_is_affiliate', true );
			}

			if ( 'pending' === $is_affiliate ) {
				$current_data      = new AFWC_Admin_Affiliates( $affiliate_id );
				$details           = $current_data->get_affiliates_details();
				$affiliate_details = array(
					'name'         => ! empty( $details[ $affiliate_id ]['name'] ) ? $details[ $affiliate_id ]['name'] : '',
					'affiliate_id' => $affiliate_id,
					'email'        => ! empty( $details[ $affiliate_id ]['email'] ) ? $details[ $affiliate_id ]['email'] : '',
					'edit_url'     => admin_url( 'user-edit.php?user_id=' . $affiliate_id ) . '#afwc-settings',
					'avatar_url'   => $this->get_avatar_url( get_avatar( $affiliate_id, 32 ) ),
					'pending'      => true,
				);
				wp_send_json( $affiliate_details );
			}

			$pname = afwc_get_pname();

			$paypal     = AFWC_PayPal_API::get_instance();
			$status     = $paypal->get_api_setting_status();
			$is_payable = ( ! empty( $status['value'] ) && 'yes' === $status['value'] ) ? true : false;

			// Get affiliate PayPal email address based on the show PayPal email address setting.
			$afwc_paypal_email = ( 'yes' === get_option( 'afwc_allow_paypal_email', 'no' ) ) ? get_user_meta( $affiliate_id, 'afwc_paypal_email', true ) : '';

			$affiliate_id = afwc_get_affiliate_id_based_on_user_id( $affiliate_id );

			$all_time_data                    = new AFWC_Admin_Affiliates( $affiliate_id );
			$all_time_data->affiliates_orders = $all_time_data->get_affiliates_orders();
			$all_time_data->affiliates_refund = $all_time_data->get_affiliates_refund();
			$all_time_data->affiliates_sales  = $all_time_data->get_affiliates_sales();
			$all_time_commisions_customers    = $all_time_data->get_commissions_customers();
			$all_time_visitor_count           = $all_time_data->get_visitors_count();
			$all_time_paid_commissions        = floatval( ( ! empty( $all_time_commisions_customers['paid_commissions'] ) ) ? $all_time_commisions_customers['paid_commissions'] : 0 );
			$all_time_unpaid_commissions      = floatval( ( ! empty( $all_time_commisions_customers['unpaid_commissions'] ) ) ? $all_time_commisions_customers['unpaid_commissions'] : 0 );

			$current_data = new AFWC_Admin_Affiliates(
				$affiliate_id,
				! empty( $params['from'] ) ? $params['from'] : '',
				! empty( $params['to'] ) ? $params['to'] : ''
			);
			$current_data->get_all_data();

			$afwc_allow_custom_affiliate_identifier = get_option( 'afwc_allow_custom_affiliate_identifier', 'yes' );

			$afwc_ref_url_id = get_user_meta( $affiliate_id, 'afwc_ref_url_id', true );
			$afwc_ref_url_id = ( 'yes' === $afwc_allow_custom_affiliate_identifier && ! empty( $afwc_ref_url_id ) ) ? $afwc_ref_url_id : $affiliate_id;

			wp_send_json(
				array(
					'name'                    => ! empty( $current_data->affiliates_details[ $affiliate_id ]['name'] ) ? $current_data->affiliates_details[ $affiliate_id ]['name'] : '',
					'affiliate_id'            => $affiliate_id,
					'email'                   => ! empty( $current_data->affiliates_details[ $affiliate_id ]['email'] ) ? $current_data->affiliates_details[ $affiliate_id ]['email'] : '',
					'edit_url'                => admin_url( 'user-edit.php?user_id=' . $affiliate_id ) . '#afwc-settings',
					'referral_url'            => afwc_get_affiliate_url( trailingslashit( home_url() ), $pname, $afwc_ref_url_id ),
					'paypal_email'            => ( true === $is_payable && ! empty( $afwc_paypal_email ) ) ? $afwc_paypal_email : '',
					'avatar_url'              => $this->get_avatar_url( get_avatar( $affiliate_id, 32 ) ),
					'formatted_join_duration' => $current_data->get_formatted_join_duration(),
				)
			);
		}

		/**
		 * Function to get avatar url
		 *
		 * @param string $get_avatar URL string containing avatar URL.
		 * @return string $matches matched string
		 */
		public function get_avatar_url( $get_avatar = '' ) {
			preg_match( "/src='(.*?)'/i", $get_avatar, $matches );
			if ( ! empty( $matches ) ) {
				return $matches[1];
			}
		}

		/**
		 * Update feedback option
		 *
		 * @param array $params The Params.
		 */
		public function update_feedback( $params = array() ) {
			check_admin_referer( 'afwc-admin-update-feedback', 'security' );
			$update_action = ! empty( $params['update_action'] ) ? $params['update_action'] : '';
			if ( ! empty( $update_action ) ) {
				if ( 'close' === $update_action ) {
					$current_date = gmdate( 'Y-m-d', Affiliate_For_WooCommerce::get_offset_timestamp() );
					update_option( 'afwc_feedback_close_date', $current_date, 'no' );
				} else {
					update_option( 'afwc_feedback_option_' . $update_action, true, 'no' );
				}
				wp_send_json(
					array(
						'ACK' => 'Success',
						'msg' => __( 'Feedback option updated', 'affiliate-for-woocommerce' ),
					)
				);
			}
		}

		/**
		 * Set/Update Commission Status.
		 *
		 * @param array $args The arguments.
		 *
		 * @return int|bool Number of rows affected or boolean false on error.
		 */
		public function set_commission_status( $args = array() ) {

			// Return if status is not provided.
			if ( empty( $args['status'] ) ) {
				return false;
			}

			$current_user_id = get_current_user_id();

			// Return if the current user is null.
			if ( empty( $current_user_id ) ) {
				return false;
			}

			global $wpdb;

			if ( ! empty( $args['referral_ids'] ) ) {
				// Create temporary key to save referral ids.
				// TODO: check why current_user_id is used instead of uniqid.
				$temp_referral_ids_db_key = 'afwc_change_commission_status_referrals_' . $current_user_id;

				// Store referrals ids temporarily in option.
				update_option( $temp_referral_ids_db_key, implode( ',', $args['referral_ids'] ), 'no' );

				$result = $wpdb->query( // phpcs:ignore
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}afwc_referrals
							SET status = %s
							WHERE FIND_IN_SET ( referral_id, ( SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s ) )", // phpcs:ignore
						$args['status'],
						$temp_referral_ids_db_key
					)
				);

				// Delete the temporary option.
				delete_option( $temp_referral_ids_db_key );

				return $result;
			} elseif ( ! empty( $args['order_ids'] ) && ! empty( $args['affiliate_id'] ) ) {
				// Create temporary key to save order ids.
				// TODO: check why current_user_id is used instead of uniqid.
				$temp_order_ids_db_key = 'afwc_change_commission_status_order_ids_' . $current_user_id;

				// Store order ids temporarily in option.
				update_option( $temp_order_ids_db_key, implode( ',', $args['order_ids'] ), 'no' );

				$result = $wpdb->query( // phpcs:ignore
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}afwc_referrals
							SET status = %s
							WHERE affiliate_id = %d
							AND FIND_IN_SET ( post_id, ( SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = %s ) )", // phpcs:ignore
						$args['status'],
						intval( $args['affiliate_id'] ),
						$temp_order_ids_db_key
					)
				);

				// Delete the temporary option.
				delete_option( $temp_order_ids_db_key );

				return $result;

			}

			return false;

		}

		/**
		 * Insert payout details.
		 *
		 * @param array $payout_details Payout details.
		 *
		 * @return int|bool Number of rows affected or boolean false on error.
		 */
		public function update_payout_table( $payout_details = array() ) {

			global $wpdb;

			$values = wp_parse_args(
				$payout_details,
				array(
					'affiliate_id'    => 0,
					'datetime'        => '',
					'amount'          => 0,
					'currency'        => '',
					'payout_notes'    => '',
					'payment_gateway' => 'other',
					'receiver'        => '',
					'type'            => '',
				)
			);

			$records = $wpdb->query( // phpcs:ignore
				$wpdb->prepare( // phpcs:ignore
					"INSERT INTO {$wpdb->prefix}afwc_payouts(`affiliate_id`, `datetime`, `amount`,  `currency`, `payout_notes`, `payment_gateway`, `receiver`, `type`)
								VALUES(%d, %s, %f, %s, %s, %s, %s, %s)",
					$values
				)
			);

			return is_wp_error( $records ) ? false : $records;
		}

		/**
		 * Update the payout method with ajax.
		 *
		 * @return void
		 */
		public function update_payout_method() {

			check_admin_referer( 'afwc-admin-update-payout-method', 'security' );

			$afwc_paypal = is_callable( array( 'AFWC_PayPal_API', 'get_instance' ) ) ? AFWC_PayPal_API::get_instance() : null;

			if ( ! empty( $afwc_paypal ) && is_callable( array( $afwc_paypal, 'check_for_paypal_payout' ) ) ) {
				$afwc_paypal->check_for_paypal_payout();
			}

			wp_send_json(
				array(
					'ACK' => true,
				)
			);

		}

		/**
		 * Link the Lifetime commission customer.
		 *
		 * @param array $params Params from the AJAX request.
		 *
		 * @return void
		 */
		public function link_ltc_customers( $params = array() ) {

			check_admin_referer( 'afwc-admin-link-ltc-customers', 'security' );

			if ( empty( $params['customer'] ) || empty( $params['affiliate_id'] ) ) {
				wp_send_json(
					array(
						'ACK' => 'Error',
						'msg' => _x( 'Customer or affiliate ID missing', 'error message for missing of required parameter for linking the lifetime customer', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$linked_affiliate = afwc_get_ltc_affiliate_by_customer( $params['customer'] );

			if ( ! empty( $linked_affiliate ) ) {
				wp_send_json(
					array(
						'ACK' => 'Error',
						'msg' => intval( $linked_affiliate ) === intval( $params['affiliate_id'] )
								? _x( 'Customer is already linked to this affiliate', 'error message on linking the customer due to the customer is already linked with the same affiliate', 'affiliate-for-woocommerce' )
								: _x( 'Customer is already linked with another affiliate', 'error message on linking the customer due to the customer is already linked with another affiliate', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$affiliate = new AFWC_Admin_Affiliates( $params['affiliate_id'] );

			wp_send_json(
				array(
					'ACK' => is_callable( array( $affiliate, 'add_ltc_customers' ) ) && $affiliate->add_ltc_customers( $params['customer'] ) ? 'Success' : 'Error',
				)
			);

		}

		/**
		 * Unlink the Lifetime commission customer.
		 *
		 * @param array $params Params from the AJAX request.
		 *
		 * @return void
		 */
		public function unlink_ltc_customers( $params = array() ) {

			check_admin_referer( 'afwc-admin-unlink-ltc-customers', 'security' );

			if ( empty( $params['customer'] ) || empty( $params['affiliate_id'] ) ) {
				wp_send_json(
					array(
						'ACK' => 'Error',
						'msg' => _x( 'Customer or affiliate ID missing', 'error message for missing of required parameter for unlinking the lifetime customer', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$affiliate = new AFWC_Admin_Affiliates( $params['affiliate_id'] );

			wp_send_json(
				array(
					'ACK' => ( is_callable( array( $affiliate, 'remove_ltc_customers' ) ) && $affiliate->remove_ltc_customers( $params['customer'] ) ) ? 'Success' : 'Error',
				)
			);

		}


		/**
		 * Search the customers.
		 *
		 * @param array $params Params from the AJAX request.
		 *
		 * @return void
		 */
		public function search_ltc_customers( $params = array() ) {

			check_admin_referer( 'afwc-admin-search-ltc-customers', 'security' );

			if ( empty( $params['term'] ) ) {
				wp_send_json(
					array(
						'ACK' => 'Error',
						'msg' => _x( 'Search term is missing', 'error message for missing of search term of linking lifetime customer', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$affiliate   = new AFWC_Admin_Affiliates();
			$search_list = is_callable( array( $affiliate, 'search_ltc_customers' ) ) ? $affiliate->search_ltc_customers( $params['term'] ) : array();

			wp_send_json(
				array(
					'ACK'  => 'Success',
					'data' => $search_list,
				)
			);

		}

	}

}

return new AFWC_Admin_Dashboard();
