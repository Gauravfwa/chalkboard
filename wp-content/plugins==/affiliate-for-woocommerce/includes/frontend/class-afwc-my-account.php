<?php
/**
 * Main class for Affiliates My Account
 *
 * @package     affiliate-for-woocommerce/includes/frontend/
 * @since       1.0.0
 * @version     1.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_My_Account' ) ) {

	/**
	 * Main class for Affiliates My Account
	 */
	class AFWC_My_Account {

		/**
		 * Variable to hold instance of AFWC_My_Account
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Endpoint
		 *
		 * @var $endpoint
		 */
		public $endpoint;

		/**
		 * Constructor
		 */
		private function __construct() {

			$this->endpoint = get_option( 'woocommerce_myaccount_afwc_dashboard_endpoint', 'afwc-dashboard' );

			add_action( 'init', array( $this, 'endpoint' ) );

			add_action( 'wp_loaded', array( $this, 'afw_myaccount' ) );

			add_action( 'wc_ajax_afwc_reload_dashboard', array( $this, 'ajax_reload_dashboard' ) );
			add_action( 'wc_ajax_afwc_load_more_products', array( $this, 'ajax_load_more_products' ) );
			add_action( 'wc_ajax_afwc_load_more_referrals', array( $this, 'ajax_load_more_referrals' ) );
			add_action( 'wc_ajax_afwc_load_more_payouts', array( $this, 'ajax_load_more_payouts' ) );
			add_action( 'wc_ajax_afwc_save_account_details', array( $this, 'afwc_save_account_details' ) );
			add_action( 'wc_ajax_afwc_save_ref_url_identifier', array( $this, 'afwc_save_ref_url_identifier' ) );

			// To provide admin setting different endpoint for affiliate.
			add_action( 'init', array( $this, 'endpoint_hooks' ) );
		}

		/**
		 * Get single instance of AFWC_My_Account
		 *
		 * @return AFWC_My_Account Singleton object of AFWC_My_Account
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Function to add affiliates endpoint to My Account.
		 *
		 * @see https://developer.woocommerce.com/2016/04/21/tabbed-my-account-pages-in-2-6/
		 */
		public function endpoint() {
			add_rewrite_endpoint( $this->endpoint, EP_ROOT | EP_PAGES );
		}

		/**
		 * Function to add endpoint in My Account if user is an affiliate
		 */
		public function afw_myaccount() {

			if ( ! is_user_logged_in() ) {
				return;
			}

			$user = wp_get_current_user();
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}

			$is_affiliate = afwc_is_user_affiliate( $user );
			if ( 'yes' === $is_affiliate || 'not_registered' === $is_affiliate ) {
				add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'menu_item' ) );
				add_action( 'woocommerce_account_' . $this->endpoint . '_endpoint', array( $this, 'endpoint_content' ) );
				// Change the My Account page title.
				add_filter( 'the_title', array( $this, 'afw_endpoint_title' ) );
				add_filter( 'woocommerce_endpoint_' . $this->endpoint . '_title', array( $this, 'get_endpoint_title' ) );
			}

			if ( 'yes' === $is_affiliate ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ) );
				add_action( 'wp_footer', array( $this, 'footer_styles_scripts' ) );
			}

		}

		/**
		 * Add new query var to WooCommerce.
		 *
		 * @param array $vars The query vars.
		 * @return array
		 */
		public function add_query_vars( $vars = array() ) {
			$vars[ $this->endpoint ] = $this->endpoint;
			return $vars;
		}

		/**
		 * Set endpoint title.
		 *
		 * @param string $title The endpoint page title.
		 *
		 * @return string
		 */
		public function afw_endpoint_title( $title = '' ) {
			global $wp_query;

			if ( ! empty( $wp_query->query_vars[ $this->endpoint ] ) && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				$title = $this->get_endpoint_title( $title );
				remove_filter( 'the_title', array( $this, 'afw_endpoint_title' ) );
			}

			return $title;
		}

		/**
		 * Get the endpoint title.
		 *
		 * @param string $title    The endpoint title.
		 * @param string $endpoint The endpoint name.
		 *
		 * @return string.
		 */
		public function get_endpoint_title( $title = '', $endpoint = '' ) {
			global $wp_query;

			$endpoint = ! empty( $endpoint ) ? $endpoint : ( ! empty( $wp_query->query_vars[ $this->endpoint ] ) ? $wp_query->query_vars[ $this->endpoint ] : '' );

			switch ( $endpoint ) {
				case 'resources':
					return _x( 'Affiliate Resources', 'Affiliate my account page title', 'affiliate-for-woocommerce' );
				case 'campaigns':
					return _x( 'Affiliate Campaigns', 'Affiliate my account page title', 'affiliate-for-woocommerce' );
				default:
					return ( 'not_registered' === afwc_is_user_affiliate( wp_get_current_user() ) ) ? _x( 'Register as an affiliate', 'Affiliate my account page title', 'affiliate-for-woocommerce' ) : _x( 'Affiliate Dashboard', 'Affiliate my account page title', 'affiliate-for-woocommerce' );
			}

			return $title;
		}

		/**
		 * Function to add menu items in My Account.
		 *
		 * @param array $menu_items menu items.
		 * @return array $menu_items menu items.
		 */
		public function menu_item( $menu_items = array() ) {
			$user = wp_get_current_user();
			if ( is_object( $user ) && $user instanceof WP_User && ! empty( $user->ID ) ) {
				$is_affiliate              = afwc_is_user_affiliate( $user );
				$insert_at_index           = array_search( 'edit-account', array_keys( $menu_items ), true );
				$afwc_is_registration_open = apply_filters( 'afwc_is_registration_open', get_option( 'afwc_show_registration_form_in_account', 'yes' ), array( 'source' => $this ) );

				// WooCommerce uses the same on the admin side to get list of WooCommerce Endpoints under Appearance > Menus.
				// So return main endpoint name irrespective of admin's affiliate status.
				if ( is_admin() ) {
					$menu_item = array( $this->endpoint => __( 'Affiliate', 'affiliate-for-woocommerce' ) );
				} else {
					if ( 'yes' === $is_affiliate ) {
						$menu_item = array( $this->endpoint => __( 'Affiliate', 'affiliate-for-woocommerce' ) );
					}
					if ( 'not_registered' === $is_affiliate && 'yes' === $afwc_is_registration_open ) {
						$menu_item = array( $this->endpoint => __( 'Register as an affiliate', 'affiliate-for-woocommerce' ) );
					}
				}

				if ( ! empty( $menu_item ) ) {
					$new_menu_items = array_merge(
						array_slice( $menu_items, 0, $insert_at_index ),
						$menu_item,
						array_slice( $menu_items, $insert_at_index, null )
					);
					return $new_menu_items;
				}
			}
			return $menu_items;
		}

		/**
		 * Function to check if current page has affiliates' endpoint.
		 */
		public function is_afwc_endpoint() {
			global $wp;

			if ( ! empty( $wp->query_vars ) && array_key_exists( $this->endpoint, $wp->query_vars ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Function to add styles.
		 */
		public function enqueue_styles_scripts() {
			if ( $this->is_afwc_endpoint() ) {
				$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();
				$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
					wp_enqueue_script( 'jquery-ui-datepicker' );
				}
				if ( ! wp_script_is( 'wp-i18n' ) ) {
					wp_enqueue_script( 'wp-i18n' );
				}
				if ( ! wp_style_is( 'afwc-admin-dashboard-font', 'registered' ) ) {
					wp_register_style( 'afwc-admin-dashboard-font', AFWC_PLUGIN_URL . '/assets/fontawesome/css/all' . $suffix . '.css', array(), $plugin_data['Version'] );
				}
				wp_enqueue_style( 'afwc-admin-dashboard-font' );
				wp_enqueue_style( 'afwc-my-account', AFWC_PLUGIN_URL . '/assets/css/afwc-my-account.css', array(), $plugin_data['Version'] );
				if ( ! wp_style_is( 'jquery-ui-style', 'registered' ) ) {
					wp_register_style( 'jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui' . $suffix . '.css', array(), WC()->version );
				}
				wp_enqueue_style( 'jquery-ui-style' );
			}
		}

		/**
		 * Function to add scripts in footer.
		 */
		public function footer_styles_scripts() {
			global $wp;
			if ( $this->is_afwc_endpoint() ) {
				if ( ! wp_script_is( 'jquery' ) ) {
					wp_enqueue_script( 'jquery' );
				}
				if ( ! class_exists( 'WC_AJAX' ) ) {
					include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-ajax.php';
				}
				$user = wp_get_current_user();
				if ( ! is_object( $user ) || empty( $user->ID ) ) {
					return;
				}
				$affiliate_id = afwc_get_affiliate_id_based_on_user_id( $user->ID );
				if ( 'campaigns' === $wp->query_vars[ $this->endpoint ] ) {
					$plugin_data = Affiliate_For_WooCommerce::get_plugin_data();
					// Dashboard scripts.
					wp_register_script( 'mithril', AFWC_PLUGIN_URL . '/assets/js/mithril/mithril.min.js', array(), $plugin_data['Version'], true );
					wp_register_script( 'afwc-campaigns-styles', AFWC_PLUGIN_URL . '/assets/js/styles.js', array( 'mithril' ), $plugin_data['Version'], true );
					wp_register_script( 'afwc-campaigns-dashboard', AFWC_PLUGIN_URL . '/assets/js/frontend.js', array( 'afwc-campaigns-styles', 'wp-i18n' ), $plugin_data['Version'], true );
					if ( function_exists( 'wp_set_script_translations' ) ) {
						wp_set_script_translations( 'afwc-campaigns-dashboard', 'affiliate-for-woocommerce', AFWC_PLUGIN_DIR_PATH . 'languages' );
					}
					if ( ! wp_script_is( 'afwc-campaigns-dashboard' ) ) {
						wp_enqueue_script( 'afwc-campaigns-dashboard' );
					}

					$affiliate_id    = afwc_get_affiliate_id_based_on_user_id( $user->ID );
					$afwc_ref_url_id = get_user_meta( $user->ID, 'afwc_ref_url_id', true );
					$affiliate_id    = ( ! empty( $afwc_ref_url_id ) ) ? $afwc_ref_url_id : $affiliate_id;

					wp_localize_script(
						'afwc-campaigns-dashboard',
						'afwcDashboardParams',
						array(
							'security'                => array(
								'campaign' => array(
									'fetchData' => wp_create_nonce( 'afwc-fetch-campaign' ),
								),
							),
							'currencySymbol'          => AFWC_CURRENCY,
							'pname'                   => afwc_get_pname(),
							'afwc_ref_url_id'         => $afwc_ref_url_id,
							'affiliate_id'            => $affiliate_id,
							'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
							'campaign_status'         => 'Active',
							'no_campaign_string'      => __( 'No Campaign yet', 'affiliate-for-woocommerce' ),
							'isPrettyReferralEnabled' => get_option( 'afwc_use_pretty_referral_links', 'no' ),
						)
					);
					$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

					wp_register_style( 'afwc_frontend', AFWC_PLUGIN_URL . '/assets/css/frontend.css', array(), $plugin_data['Version'] );
					if ( ! wp_style_is( 'afwc_frontend' ) ) {
						wp_enqueue_style( 'afwc_frontend' );
					}
				}

				$js = "let afwcDashboardWrapper = jQuery('#afwc_dashboard_wrapper');
						if( window.innerWidth < 760 ) {
							jQuery('.afwc_products, .afwc_referrals, .afwc_payout_history').addClass('woocommerce-table shop_table shop_table_responsive order_details');
						} else {
							jQuery('.afwc_products, .afwc_referrals, .afwc_payout_history').removeClass('woocommerce-table shop_table shop_table_responsive order_details');
						}
						jQuery('body').on('click', '#afwc_load_more_products', function(e){
							e.preventDefault();
							let the_table = jQuery('table.afwc_products');
							let dateRange = afwcGetFormattedDateRange()
							the_table.addClass( 'afwc-loading' );
							jQuery.ajax({
								url: '" . esc_url_raw( WC_AJAX::get_endpoint( 'afwc_load_more_products' ) ) . "',
								type: 'post',
								dataType: 'html',
								data: {
									security: '" . esc_js( wp_create_nonce( 'afwc-load-more-products' ) ) . "',
									from: dateRange.from || '',
									to: dateRange.to || '',
									search: afwcDashboardWrapper.find('#afwc_search').val(),
									offset: the_table.find('tbody tr').length,
									affiliate: '" . esc_attr( $affiliate_id ) . "'
								},
								success: function( response ) {
									if ( response ) {
										the_table.find('tbody').append( response );
										let max_record = jQuery('#afwc_load_more_products').data('max_record');
										if ( the_table.find('tbody tr').length >= max_record ) {
											jQuery('#afwc_load_more_products').addClass('disabled').text('" . esc_html__( 'No more data to load', 'affiliate-for-woocommerce' ) . "');
										}
										the_table.removeClass( 'afwc-loading' );
									}
								}
							});
						});
						jQuery('body').on('click', '#afwc_load_more_referrals', function(){
							let the_table = jQuery('table.afwc_referrals');
							let dateRange = afwcGetFormattedDateRange()
							the_table.addClass( 'afwc-loading' );
							jQuery.ajax({
								url: '" . esc_url_raw( WC_AJAX::get_endpoint( 'afwc_load_more_referrals' ) ) . "',
								type: 'post',
								dataType: 'html',
								data: {
									security: '" . esc_js( wp_create_nonce( 'afwc-load-more-referrals' ) ) . "',
									from: dateRange.from || '',
									to: dateRange.to || '',
									search: afwcDashboardWrapper.find('#afwc_search').val(),
									offset: the_table.find('tbody tr').length,
									affiliate: '" . esc_attr( $affiliate_id ) . "'
								},
								success: function( response ) {
									if ( response ) {
										the_table.find('tbody').append( response );
										let max_record = jQuery('#afwc_load_more_referrals').data('max_record');
										if ( the_table.find('tbody tr').length >= max_record ) {
											jQuery('#afwc_load_more_referrals').addClass('disabled').text('" . esc_html__( 'No more data to load', 'affiliate-for-woocommerce' ) . "');
										}
										the_table.removeClass( 'afwc-loading' );
									}
								}
							});
						});

						jQuery('body').on('click', '#afwc_load_more_payouts', function(){
							let the_table = jQuery('table.afwc_payout_history');
							let dateRange = afwcGetFormattedDateRange();
							the_table.addClass( 'afwc-loading' );
							jQuery.ajax({
								url: '" . esc_url_raw( WC_AJAX::get_endpoint( 'afwc_load_more_payouts' ) ) . "',
								type: 'post',
								dataType: 'html',
								data: {
									security: '" . esc_js( wp_create_nonce( 'afwc-load-more-payouts' ) ) . "',
									from: dateRange.from || '',
									to: dateRange.to || '',
									search: afwcDashboardWrapper.find('#afwc_search').val(),
									offset: the_table.find('tbody tr').length,
									affiliate: '" . esc_attr( $affiliate_id ) . "'
								},
								success: function( response ) {
									if ( response ) {
										the_table.find('tbody').append( response );
										let max_record = jQuery('#afwc_load_more_payouts').data('max_record');
										if ( the_table.find('tbody tr').length >= max_record ) {
											jQuery('#afwc_load_more_payouts').addClass('disabled').text('" . esc_html__( 'No more data to load', 'affiliate-for-woocommerce' ) . "');
										}
										the_table.removeClass( 'afwc-loading' );
									}
								}
							});
						});
						jQuery('body').on('focus', '#afwc_from, #afwc_to', function(){
							load_datepicker( jQuery(this) );
						});
						function load_datepicker( element ) {
							if ( ! element.hasClass('hasDatepicker') ) {
								element.datepicker({
									dateFormat: 'dd-M-yy',
									beforeShowDay: date_range,
									onSelect: dr_on_select
								});
							}
							element.datepicker( 'show' );
						}
						function getDateTime(dateStr){
							var hr = new Date().getHours();
							var min = new Date().getMinutes();
							var sec = new Date().getSeconds();
							dateStr = dateStr + ' ' + hr + ':' + min +':' + sec;
							return dateStr;
						}
						function date_range(date){
							let from        = jQuery.datepicker.parseDate('dd-M-yy', jQuery('#afwc_from').val());
							let to          = jQuery.datepicker.parseDate('dd-M-yy', jQuery('#afwc_to').val());
							let is_highlight = ( from && ( ( date.getTime() == from.getTime() ) || ( to && date >= from && date <= to ) ) );
							return [true, is_highlight ? 'dp-highlight' : ''];
						}
						function dr_on_select(date_text, inst) {
							let from = jQuery.datepicker.parseDate('dd-M-yy', jQuery('#afwc_from').val());
							let to   = jQuery.datepicker.parseDate('dd-M-yy', jQuery('#afwc_to').val());
							if ( ! from && ! to ) {
								jQuery('#afwc_from').val('');
								jQuery('#afwc_to').val('');
								setTimeout(function(){
									load_datepicker( jQuery('#afwc_from') );
								}, 1);
							} else if ( ! from && to ) {
								jQuery('#afwc_from').val(date_text);
								jQuery('#afwc_to').val('');
								setTimeout(function(){
									load_datepicker( jQuery('#afwc_to') );
								}, 1);
							} else if ( from && ! to ) {
								jQuery('#afwc_to').val('');
								setTimeout(function(){
									load_datepicker( jQuery('#afwc_to') );
								}, 1);
							} else if ( from && to ) {
								if ( 'afwc_to' !== inst.id || from >= to ) {
									jQuery('#afwc_from').val(date_text);
									jQuery('#afwc_to').val('');
									setTimeout(function(){
										load_datepicker( jQuery('#afwc_to') );
									}, 1);
								} else {
									jQuery('#afwc_to').trigger('change');
								}
							}
						}
						function afwcGetDatesFromDateRange(){
							return {
								from: afwcDashboardWrapper.find('#afwc_from').val() || '',
								to: afwcDashboardWrapper.find('#afwc_to').val() || ''
							}
						}
						function afwcGetFormattedDateRange(){
							let dateRange = afwcGetDatesFromDateRange();
							let tzoffset = (new Date()).getTimezoneOffset() * 60000;
							return {
								from: dateRange.from ? getDateTime((new Date(jQuery.datepicker.parseDate('dd-M-yy', dateRange.from).getTime() - tzoffset)).toISOString().slice(0,10)) : '',
								to: dateRange.to ? getDateTime((new Date(jQuery.datepicker.parseDate('dd-M-yy', dateRange.to).getTime()- tzoffset)).toISOString().slice(0,10)) : ''
							}
						}
						jQuery('body').on('change', '#afwc_from, #afwc_to, #afwc_search', function(){
							let dateRange = afwcGetFormattedDateRange()
							let search    = afwcDashboardWrapper.find('#afwc_search').val();
							afwcDashboardWrapper.css( 'opacity', 0.5 );
							if ( ( dateRange.from && dateRange.to ) || search ) {
								jQuery.ajax({
									url: '" . esc_url_raw( WC_AJAX::get_endpoint( 'afwc_reload_dashboard' ) ) . "',
									type: 'post',
									dataType: 'html',
									data: {
										security: '" . esc_js( wp_create_nonce( 'afwc-reload-dashboard' ) ) . "',
										afwc_from: afwcGetDatesFromDateRange().from || '',
										afwc_to: afwcGetDatesFromDateRange().to || '',
										afwc_format_from: dateRange.from || '',
										afwc_format_to: dateRange.to || '',
										afwc_search: search || '',
										user_id: '" . esc_attr( $user->ID ) . "'
									},
									success: function( response ) {
										if ( response ) {
											afwcDashboardWrapper.replaceWith( response );
											afwcDashboardWrapper = jQuery('#afwc_dashboard_wrapper');
											afwcDashboardWrapper.css( 'opacity', 1 );
										}
									}
								});
							}
						});";
						wc_enqueue_js( $js );
			}
		}

		/**
		 * Function to retrieve more products.
		 */
		public function ajax_reload_dashboard() {
			check_ajax_referer( 'afwc-reload-dashboard', 'security' );

			$user_id = ( ! empty( $_POST['user_id'] ) ) ? absint( $_POST['user_id'] ) : 0;

			$user = get_user_by( 'id', $user_id );

			$this->dashboard_content( $user );

			die();
		}

		/**
		 * Function to retrieve more products.
		 */
		public function ajax_load_more_products() {
			check_ajax_referer( 'afwc-load-more-products', 'security' );

			$args = apply_filters(
				'afwc_ajax_load_more_products',
				array(
					'from'         => ( ! empty( $_POST['from'] ) ) ? $this->gmt_from_date( wc_clean( wp_unslash( $_POST['from'] ) ) ) : '', // phpcs:ignore
					'to'           => ( ! empty( $_POST['to'] ) ) ? $this->gmt_from_date( wc_clean( wp_unslash( $_POST['to'] ) ) ) : '', // phpcs:ignore
					'search'       => ( ! empty( $_POST['search'] ) ) ? wc_clean( wp_unslash( $_POST['search'] ) ) : '', // phpcs:ignore
					'offset'       => ( ! empty( $_POST['offset'] ) ) ? wc_clean( wp_unslash( $_POST['offset'] ) ) : 0, // phpcs:ignore
					'affiliate_id' => ( ! empty( $_POST['affiliate'] ) ) ? wc_clean( wp_unslash( $_POST['affiliate'] ) ) : 0, // phpcs:ignore
				)
			);

			$products = is_callable( array( 'Affiliate_For_WooCommerce', 'get_my_account_products' ) ) ? Affiliate_For_WooCommerce::get_my_account_products( $args ) : array();

			if ( ! empty( $products ) && ! empty( $products['rows'] ) ) {
				do_action( 'afwc_before_ajax_load_more_products', $products, $args, $this );
				foreach ( $products['rows'] as $product ) {

					$product_name = ( strlen( $product['product'] ) > 20 ) ? substr( $product['product'], 0, 19 ) . '...' : $product['product'];

					?>
						<tr>
							<td data-title="<?php echo esc_html__( 'Product', 'affiliate-for-woocommerce' ); ?>" title="<?php echo ( ! empty( $product['product'] ) ) ? esc_html( $product['product'] ) : ''; ?>"><?php echo esc_html( $product_name ); ?></td>
							<td data-title="<?php echo esc_html__( 'Quantity', 'affiliate-for-woocommerce' ); ?>"><?php echo ( ! empty( $product['qty'] ) ) ? esc_html( $product['qty'] ) : 0; ?></td>
							<td data-title="<?php echo esc_html__( 'Sales', 'affiliate-for-woocommerce' ); ?>"><?php echo wp_kses_post( wc_price( ! empty( $product['sales'] ) ? $product['sales'] : 0 ) ); ?></td>
						</tr>
					<?php
				}
				do_action( 'afwc_after_ajax_load_more_products', $products, $args, $this );
			}
			die();
		}

		/**
		 * Function to retrieve more referrals.
		 */
		public function ajax_load_more_referrals() {
			check_ajax_referer( 'afwc-load-more-referrals', 'security' );

			$date_format = get_option( 'date_format' );

			$args = apply_filters(
				'afwc_ajax_load_more_referrals',
				array(
					'from'         => ( ! empty( $_POST['from'] ) ) ? $this->gmt_from_date( wc_clean( wp_unslash( $_POST['from'] ) ) ) : '', // phpcs:ignore
					'to'           => ( ! empty( $_POST['to'] ) ) ? $this->gmt_from_date( wc_clean( wp_unslash( $_POST['to'] ) ) ) : '', // phpcs:ignore
					'search'       => ( ! empty( $_POST['search'] ) ) ? wc_clean( wp_unslash( $_POST['search'] ) ) : '', // phpcs:ignore
					'offset'       => ( ! empty( $_POST['offset'] ) ) ? wc_clean( wp_unslash( $_POST['offset'] ) ) : 0, // phpcs:ignore
					'affiliate_id' => ( ! empty( $_POST['affiliate'] ) ) ? wc_clean( wp_unslash( $_POST['affiliate'] ) ) : 0, // phpcs:ignore
				)
			);

			$referrals = $this->get_referrals_data( $args );

			if ( ! empty( $referrals['rows'] ) ) {
				do_action( 'afwc_before_ajax_load_more_referrals', $referrals, $args, $this );
				foreach ( $referrals['rows'] as $referral ) {

					$referral_status = ( ! empty( $referral['status'] ) ) ? $referral['status'] : '';

					$customer_name = ( strlen( $referral['display_name'] ) > 20 ) ? substr( $referral['display_name'], 0, 19 ) . '...' : $referral['display_name'];
					$commission    = ( html_entity_decode( get_woocommerce_currency_symbol( $referral['currency_id'] ) ) ) . $referral['amount'];

					$is_show_customer_column = apply_filters( 'afwc_account_show_customer_column', true, array( 'source' => $this ) );
					?>
						<tr>
							<td data-title="<?php echo esc_html__( 'Order ID', 'affiliate-for-woocommerce' ); ?>"> <?php echo ( ! empty( $referral['post_id'] ) ) ? esc_html( $referral['post_id'] ) : 0; ?></td>
							<td data-title="<?php echo esc_html__( 'Date', 'affiliate-for-woocommerce' ); ?>"> <?php echo ( ! empty( $referral['datetime'] ) ) ? esc_html( gmdate( $date_format, strtotime( $referral['datetime'] ) ) ) : ''; ?></td>
							<?php if ( true === $is_show_customer_column ) { ?>
								<td data-title="<?php echo esc_html__( 'Customer', 'affiliate-for-woocommerce' ); ?>" title="<?php echo ( ! empty( $referral['display_name'] ) ) ? esc_html( $referral['display_name'] ) : ''; ?>"><?php echo esc_html( $customer_name ); ?></td>
							<?php } ?>
							<td data-title="<?php echo esc_html__( 'Commission', 'affiliate-for-woocommerce' ); ?>"><?php echo wp_kses_post( $commission ); // phpcs:ignore ?></td>
							<td data-title="<?php echo esc_html__( 'Payout status', 'affiliate-for-woocommerce' ); ?>" title="<?php echo esc_html( ( ! empty( $referral_status ) ) ? afwc_get_commission_statuses( $referral_status ) : '' ); ?>"><div class="afwc_circle <?php echo esc_attr( 'fill_' . ( ! empty( $referral_status ) ? afwc_get_commission_status_colors( $referral_status ) : '' ) ); ?>"></div></td>
						</tr>
					<?php
				}
				do_action( 'afwc_after_ajax_load_more_referrals', $referrals, $args, $this );
			}
			die();
		}

		/**
		 * Function to retrieve more payouts.
		 */
		public function ajax_load_more_payouts() {
			check_ajax_referer( 'afwc-load-more-payouts', 'security' );

			$date_format = get_option( 'date_format' );

			$args = apply_filters(
				'afwc_ajax_load_more_payouts',
				array(
					'from'         => ( ! empty( $_POST['from'] ) ) ? $this->gmt_from_date( wc_clean( wp_unslash( $_POST['from'] ) ) ) : '', // phpcs:ignore
					'to'           => ( ! empty( $_POST['to'] ) ) ? $this->gmt_from_date( wc_clean( wp_unslash( $_POST['to'] ) ) ) : '', // phpcs:ignore
					'search'       => ( ! empty( $_POST['search'] ) ) ? wc_clean( wp_unslash( $_POST['search'] ) ) : '', // phpcs:ignore
					'start_limit'  => ( ! empty( $_POST['offset'] ) ) ? wc_clean( wp_unslash( $_POST['offset'] ) ) : 0, // phpcs:ignore
					'affiliate_id' => ( ! empty( $_POST['affiliate'] ) ) ? wc_clean( wp_unslash( $_POST['affiliate'] ) ) : 0, // phpcs:ignore
				)
			);

			$payout_history = Affiliate_For_WooCommerce::get_affiliates_payout_history( $args );
			if ( ! empty( $payout_history['payouts'] ) ) {
				do_action( 'afwc_before_ajax_load_more_payouts', $payout_history, $args, $this );

				foreach ( $payout_history['payouts'] as $payout ) {
					$payout_method = ! empty( $payout['method'] ) ? afwc_get_payout_methods( $payout['method'] ) : '';
					?>
					<tr>
						<td data-title="<?php echo esc_html__( 'Date', 'affiliate-for-woocommerce' ); ?>" ><?php echo ( ! empty( $payout['datetime'] ) ) ? esc_html( gmdate( $date_format, strtotime( $payout['datetime'] ) ) ) : ''; ?></td>
						<td data-title="<?php echo esc_html__( 'Amount', 'affiliate-for-woocommerce' ); ?>" ><?php echo wp_kses_post( wc_price( ( ! empty( $payout['amount'] ) ? $payout['amount'] : 0 ), array( 'currency' => ! empty( $payout['currency'] ) ? $payout['currency'] : '' ) ) ); ?></td>
						<td data-title="<?php echo esc_html__( 'Method', 'affiliate-for-woocommerce' ); ?>" title="<?php echo esc_html( $payout_method ); ?>"><?php echo esc_html( $payout_method ); ?></td>
						<td data-title="<?php echo esc_html__( 'Notes', 'affiliate-for-woocommerce' ); ?>" ><?php echo ( ! empty( $payout['payout_notes'] ) ) ? wp_kses_post( $payout['payout_notes'] ) : ''; ?></td>
					</tr>
					<?php
				}
				do_action( 'afwc_after_ajax_load_more_payouts', $payout_history, $args, $this );
			}
			die();
		}

		/**
		 * Function to display endpoint content
		 */
		public function endpoint_content() {
			if ( ! is_user_logged_in() ) {
				return;
			}
			if ( ! $this->is_afwc_endpoint() ) {
				return;
			}
			$user = wp_get_current_user();
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}

			$is_affiliate = afwc_is_user_affiliate( $user );
			if ( 'yes' === $is_affiliate ) {
				$this->tabs( $user );
				$this->tab_content( $user );
			}
			if ( 'not_registered' === $is_affiliate ) {
				do_action(
					'afwc_before_registration_form',
					array(
						'user_id' => $user->ID,
						'source'  => $this,
					)
				);
				echo do_shortcode( '[afwc_registration_form]' );
				do_action(
					'afwc_after_registration_form',
					array(
						'user_id' => $user->ID,
						'source'  => $this,
					)
				);
			}
		}

		/**
		 * Function to display tabs headers
		 *
		 * @param WP_User $user The user object.
		 */
		public function tabs( $user = null ) {
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}

			global $wp;
			$tabs = array();
			$tabs = array(
				''          => esc_html__( 'Reports', 'affiliate-for-woocommerce' ),
				'resources' => esc_html__( 'Profile', 'affiliate-for-woocommerce' ),
			);

			// Add campaigns tab only if we find any active campaigns on the store.
			if ( afwc_is_campaign_active() ) {
				$tabs['campaigns'] = esc_html__( 'Campaigns', 'affiliate-for-woocommerce' );
			}

			$tabs = apply_filters( 'afwc_myaccount_tabs', $tabs );
			?>

			<nav class="nav-tab-wrapper">
				<?php
				if ( ! empty( $tabs ) ) {
					foreach ( $tabs as $id => $name ) {
						?>
					<a href="<?php echo esc_url( wc_get_endpoint_url( $this->endpoint, $id ) ); ?>" class="nav-tab <?php echo ( isset( $wp->query_vars[ $this->endpoint ] ) && ( $id === $wp->query_vars[ $this->endpoint ] ) ) ? esc_attr( 'nav-tab-active' ) : ''; ?>"><?php echo esc_attr( $name ); ?></a>
						<?php
					}
				}
				?>
			</nav>
			<?php
		}

		/**
		 * Function to display tabs content on my account.
		 *
		 * @param WP_User $user The user object.
		 */
		public function tab_content( $user = null ) {
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}
			global $wp;

			if ( isset( $wp->query_vars[ $this->endpoint ] ) && empty( $wp->query_vars[ $this->endpoint ] ) ) {
				$this->dashboard_content( $user );
			} elseif ( ! empty( $wp->query_vars[ $this->endpoint ] ) && 'resources' === $wp->query_vars[ $this->endpoint ] ) {
				$this->profile_resources_content( $user );
			} elseif ( ! empty( $wp->query_vars[ $this->endpoint ] ) && 'campaigns' === $wp->query_vars[ $this->endpoint ] && afwc_is_campaign_active() ) {
				$this->campaigns_content( $user );
			}

		}

		/**
		 * Function to display dashboard content on my account.
		 * Default: Reports tab.
		 *
		 * @param WP_User $user The user object.
		 */
		public function dashboard_content( $user = null ) {
			global $wpdb;

			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}

			if ( defined( 'WC_DOING_AJAX' ) && true === WC_DOING_AJAX ) {
				check_ajax_referer( 'afwc-reload-dashboard', 'security' );
			}

			$date_format = get_option( 'date_format' );

			$affiliate_id = afwc_get_affiliate_id_based_on_user_id( $user->ID );

			$from         = ( ! empty( $_POST['afwc_from'] ) ) ? wc_clean( wp_unslash( $_POST['afwc_from'] ) ) : ''; // phpcs:ignore
			$format_from  = ( ! empty( $_POST['afwc_format_from'] ) ) ? wc_clean( wp_unslash( $_POST['afwc_format_from'] ) ) : ''; // phpcs:ignore
			$to           = ( ! empty( $_POST['afwc_to'] ) ) ? wc_clean( wp_unslash( $_POST['afwc_to'] ) ) : ''; // phpcs:ignore
			$format_to    = ( ! empty( $_POST['afwc_format_to'] ) ) ? wc_clean( wp_unslash( $_POST['afwc_format_to'] ) ) : ''; // phpcs:ignore
			$search       = ( ! empty( $_POST['afwc_search'] ) ) ? wc_clean( wp_unslash( $_POST['afwc_search'] ) ) : ''; // phpcs:ignore

			// convert date to GMT for passing in query.
			$args = array(
				'affiliate_id' => $affiliate_id,
				'from'         => ( ! empty( $format_from ) ) ? $this->gmt_from_date( $format_from ) : '',
				'to'           => ( ! empty( $format_to ) ) ? $this->gmt_from_date( $format_to ) : '',
				'search'       => $search,
			);

			$visitors        = $this->get_visitors_data( $args );
			$customers_count = $this->get_customers_data( $args );
			$payouts         = $this->get_payouts_data( $args );
			$kpis            = $this->get_kpis_data( $args );
			$refunds         = $this->get_refunds_data( $args );
			$referrals       = $this->get_referrals_data( $args );
			$products        = is_callable( array( 'Affiliate_For_WooCommerce', 'get_my_account_products' ) ) ? Affiliate_For_WooCommerce::get_my_account_products( $args ) : array();
			$payout_history  = is_callable( array( 'Affiliate_For_WooCommerce', 'get_affiliates_payout_history' ) ) ? Affiliate_For_WooCommerce::get_affiliates_payout_history( $args ) : array();

			$products_total   = ( ! empty( $products ) && ! empty( $products['total_count'] ) ) ? $products['total_count'] : 0;
			$products_rows    = ( ! empty( $products ) && ! empty( $products['rows'] ) ) ? $products['rows'] : array();
			$gross_commission = ( ! empty( $kpis['gross_commission'] ) ) ? floatval( $kpis['gross_commission'] ) : 0;
			$net_commission   = $kpis['paid_commission'] + $kpis['unpaid_commission'];

			$paid_commission_percentage = ( ( ! empty( $kpis['paid_commission'] ) && ! empty( $net_commission ) ) ? ( $kpis['paid_commission'] / $net_commission ) * 100 : 0 );
			$paid_commission_percentage = round( $paid_commission_percentage, 2, PHP_ROUND_HALF_UP );

			$unpaid_commission_percentage = ( ( ! empty( $kpis['unpaid_commission'] ) && ! empty( $net_commission ) ) ? ( $kpis['unpaid_commission'] / $net_commission ) * 100 : 0 );
			$unpaid_commission_percentage = round( $unpaid_commission_percentage, 2, PHP_ROUND_HALF_UP );

			$paid_commission_percentage_style   = ( empty( $paid_commission_percentage ) ) ? 'display:none;' : '';
			$unpaid_commission_percentage_style = ( empty( $unpaid_commission_percentage ) ) ? 'display:none;' : '';

			?>
			<div id="afwc_dashboard_wrapper">
				<div id="afwc_top_row_container">
					<div id="afwc_date_range_container">
						<input type="text" readonly="readonly" id="afwc_from" name="afwc_from" value="<?php echo ( ! empty( $from ) ) ? esc_attr( $from ) : ''; ?>" placeholder="<?php echo esc_attr__( 'From', 'affiliate-for-woocommerce' ); ?>">-<input type="text" readonly="readonly" id="afwc_to" name="afwc_to" value="<?php echo ( ! empty( $to ) ) ? esc_attr( $to ) : ''; ?>" placeholder="<?php echo esc_attr__( 'To', 'affiliate-for-woocommerce' ); ?>">
					</div>
				</div>
				<?php if ( ! empty( $paid_commission_percentage ) || ! empty( $unpaid_commission_percentage ) ) { ?>
					<div id="afwc_commission">
						<div id ="afwc_commission_lbl" class="afwc_kpis_text"><?php echo esc_html__( 'Total Commissions', 'affiliate-for-woocommerce' ); ?>:</div>
						<div id ="afwc_commission_container">
							<div id ="afwc_commission_bar">
								<div id="afwc_paid_commission" class="fill_green" style="<?php echo esc_html( $paid_commission_percentage_style ) . 'width:' . esc_html( $paid_commission_percentage ) . '%'; ?>"></div>
								<div id="afwc_unpaid_commission" class="fill_orange" style="<?php echo esc_html( $unpaid_commission_percentage_style ) . 'width:' . esc_html( $unpaid_commission_percentage ) . '%'; ?>"></div>
							</div>
							<div id ="afwc_commission_stats">
								<?php
									// TODO: can fetch commission statuses from function.
								if ( ! empty( $paid_commission_percentage ) ) {
									?>
									<div id="afwc_commission_stats_paid" class="afwc_kpis_text"><?php echo esc_html__( 'Paid', 'affiliate-for-woocommerce' ) . ': ' . wp_kses_post( wc_price( $kpis['paid_commission'] ) ); //phpcs:ignore ?></div>
								<?php } if ( ! empty( $unpaid_commission_percentage ) ) { ?>
									<div id="afwc_commission_stats_unpaid" class="afwc_kpis_text"><?php echo esc_html__( 'Unpaid', 'affiliate-for-woocommerce' ) . ': ' . wp_kses_post( wc_price( $kpis['unpaid_commission'] ) ); //phpcs:ignore ?></div>
								<?php } ?>
							</div>
						</div>
					</div>
				<?php } ?>
				<div id ="afwc_kpis_container">
					<div class="afwc_kpis_inner_container">
						<div id="afwc_kpi_gross_commission" class="afwc_kpi first">
							<div class="container_parent_left flex_center">
								<div class="afwc_kpis_icon_container">
									<i class="fas fa-dollar-sign afwc_kpis_icon"></i>
								</div>
							</div>
							<div id="afwc_gross_commission" class="afwc_kpis_data flex_center">
								<div class="container_parent_right">
									<span class="afwc_kpis_price">
										<?php echo wp_kses_post( wc_price( $gross_commission ) ); //phpcs:ignore ?> • <span class="afwc_kpis_number"><?php echo esc_html( $kpis['number_of_orders'] ); ?></span>
									</span>
									<p class="afwc_kpis_text"><?php echo esc_html__( 'Gross Commission', 'affiliate-for-woocommerce' ); ?></p>
								</div>
							</div>
						</div>
						<div id="afwc_kpi_refunds" class="afwc_kpi second">
							<div class="container_parent_left flex_center">
								<div class="afwc_kpis_icon_container">
									<i class="fas fa-thumbs-down afwc_kpis_icon"></i>
								</div>
							</div>
							<div id="afwc_refunds" class="afwc_kpis_data flex_center">
								<div class="container_parent_right">
									<span class="afwc_kpis_price">
										<?php echo wp_kses_post( wc_price( $refunds['refund_amount'] ) ); //phpcs:ignore ?> • <span class="afwc_kpis_number"><?php echo esc_html( $kpis['rejected_count'] ); ?></span>
									</span>
									<p class="afwc_kpis_text"><?php echo esc_html__( 'Refunds', 'affiliate-for-woocommerce' ); ?></p>
								</div>
							</div>
						</div>
						<div id="afwc_kpi_net_commission" class="afwc_kpi third">
							<div class="container_parent_left flex_center">
								<div class="afwc_kpis_icon_container">
									<i class="fas fa-hand-holding-usd afwc_kpis_icon"></i>
								</div>
							</div>
							<div id="afwc_net_commission" class="afwc_kpis_data flex_center">
								<div class="container_parent_right">
									<span class="afwc_kpis_price">
										<?php echo wp_kses_post( wc_price( $net_commission ) ); //phpcs:ignore ?> • <span class="afwc_kpis_number"><?php echo esc_html( $kpis['paid_count'] + $kpis['unpaid_count'] ); ?></span>
									</span>
									<p class="afwc_kpis_text"><?php echo esc_html__( 'Net Commission', 'affiliate-for-woocommerce' ); ?></p>
								</div>
							</div>
						</div>
						<div id="afwc_kpi_sales" class="afwc_kpi fourth">
							<div class="container_parent_left flex_center">
								<div class="afwc_kpis_icon_container">
									<i class="fas fa-coins afwc_kpis_icon"></i>
								</div>
							</div>
							<div id="afwc_sales" class="afwc_kpis_data flex_center">
								<div class="container_parent_right">
									<span class="afwc_kpis_price">
										<?php echo wp_kses_post( wc_price( $kpis['sales'] ) ); //phpcs:ignore ?>
									</span>
									<p class="afwc_kpis_text"><?php echo esc_html__( 'Sales', 'affiliate-for-woocommerce' ); ?></p>
								</div>
							</div>
						</div>
						<div id="afwc_kpi_clicks" class="afwc_kpi fifth">
							<div class="container_parent_left flex_center">
								<div class="afwc_kpis_icon_container">
									<i class="fas fa-hand-point-up afwc_kpis_icon"></i>
								</div>
							</div>
							<div id="afwc_clicks" class="afwc_kpis_data flex_center">
								<div class="container_parent_right">
									<span class="afwc_kpis_price">
										<?php echo esc_html( $visitors['visitors'] ); ?>
									</span>
									<p class="afwc_kpis_text"><?php echo esc_html__( 'Visitors', 'affiliate-for-woocommerce' ); ?></p>
								</div>
							</div>
						</div>
						<div id="afwc_kpi_conversion" class="afwc_kpi sixth afwc_kpi_last">
							<div class="container_parent_left flex_center">
								<div class="afwc_kpis_icon_container">
									<i class="fas fa-handshake afwc_kpis_icon"> </i>
								</div>
							</div>
							<div id="afwc_conversion" class="afwc_kpis_data flex_center">
								<div class="container_parent_right">
									<span class="afwc_kpis_price">
										<?php echo esc_html( number_format( ( ( ! empty( $visitors['visitors'] ) ) ? ( $customers_count['customers'] * 100 / $visitors['visitors'] ) : 0 ), 2 ) ) . '%'; ?> • <span class="afwc_kpis_number"><?php echo esc_html( $kpis['number_of_orders'] ); ?></span>
									</span>
									<p class="afwc_kpis_text"><?php echo esc_html__( 'Conversion', 'affiliate-for-woocommerce' ); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="afwc-table-header"><?php echo esc_html__( 'Products', 'affiliate-for-woocommerce' ); ?></div>
				<table class="afwc_products">
					<thead>
						<tr>
							<th class="product-name"><?php echo esc_html__( 'Product', 'affiliate-for-woocommerce' ); ?></th>
							<th class="qty"><?php echo esc_html__( 'Quantity', 'affiliate-for-woocommerce' ); ?></th>
							<th class="sales"><?php echo esc_html__( 'Sales', 'affiliate-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $products_rows ) ) { ?>
							<?php
							foreach ( $products_rows as $product ) {
									$product_name = ( strlen( $product['product'] ) > 30 ) ? substr( $product['product'], 0, 30 ) . '...' : $product['product'];
								?>
							<tr>
								<td class="product-name" data-title="<?php echo esc_html__( 'Product', 'affiliate-for-woocommerce' ); ?>"  title="<?php echo ( ! empty( $product['product'] ) ) ? esc_html( $product['product'] ) : ''; ?>"><?php echo esc_html( $product_name ); ?></td>
								<td class="qty" data-title="<?php echo esc_html__( 'Quantity', 'affiliate-for-woocommerce' ); ?>"><?php echo ( ! empty( $product['qty'] ) ) ? esc_html( $product['qty'] ) : 0; ?></td>
								<td class="sales" data-title="<?php echo esc_html__( 'Sales', 'affiliate-for-woocommerce' ); ?>"><?php echo wp_kses_post( wc_price( ! empty( $product['sales'] ) ? $product['sales'] : 0 ) ); ?></td>
							</tr>
							<?php } ?>
						<?php } else { ?>
							<tr>
								<td colspan="3"><?php echo esc_html__( 'No products to show', 'affiliate-for-woocommerce' ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
					<?php if ( $products_total > count( $products_rows ) ) { ?>
						<tfoot>
							<tr>
								<td colspan="3">
									<a id="afwc_load_more_products" data-max_record="<?php echo esc_attr( $products_total ); ?>"><?php echo esc_html__( 'Load more', 'affiliate-for-woocommerce' ); ?></a>
								</td>
							</tr>
						</tfoot>
					<?php } ?>
				</table>
				<?php
					$is_show_customer_column = apply_filters( 'afwc_account_show_customer_column', true, array( 'source' => $this ) );
					$payout_colspan          = ( true === $is_show_customer_column ) ? 4 : 3;
				?>
				<div class="afwc-table-header"><?php echo esc_html__( 'Referrals', 'affiliate-for-woocommerce' ); ?></div>
				<table class="afwc_referrals">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Order ID', 'affiliate-for-woocommerce' ); ?></th>
							<th><?php echo esc_html__( 'Date', 'affiliate-for-woocommerce' ); ?></th>
							<?php if ( true === $is_show_customer_column ) { ?>
							<th><?php echo esc_html__( 'Customer', 'affiliate-for-woocommerce' ); ?></th>
							<?php } ?>
							<th><?php echo esc_html__( 'Commission', 'affiliate-for-woocommerce' ); ?></th>
							<th><?php echo esc_html__( 'Payout status', 'affiliate-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $referrals['rows'] ) ) { ?>
							<?php
							foreach ( $referrals['rows'] as $referral ) {

								$referral_status = ( ! empty( $referral['status'] ) ) ? $referral['status'] : '';

								$customer_name = ( strlen( $referral['display_name'] ) > 20 ) ? substr( $referral['display_name'], 0, 19 ) . '...' : $referral['display_name'];
								?>
							<tr>
								<td data-title="<?php echo esc_html__( 'Order ID', 'affiliate-for-woocommerce' ); ?>"><?php echo ( ! empty( $referral['post_id'] ) ) ? esc_html( $referral['post_id'] ) : 0; ?></td>
								<td data-title="<?php echo esc_html__( 'Date', 'affiliate-for-woocommerce' ); ?>"><?php echo ( ! empty( $referral['datetime'] ) ) ? esc_html( gmdate( $date_format, strtotime( $referral['datetime'] ) ) ) : ''; ?></td>
								<?php if ( true === $is_show_customer_column ) { ?>
								<td data-title="<?php echo esc_html__( 'Customer', 'affiliate-for-woocommerce' ); ?>" title="<?php echo ( ! empty( $referral['display_name'] ) ) ? esc_html( $referral['display_name'] ) : ''; ?>"><?php echo esc_html( $customer_name ); ?></td>
							<?php } ?>
								<td data-title="<?php echo esc_html__( 'Commission', 'affiliate-for-woocommerce' ); ?>"><?php echo wp_kses_post( wc_price( $referral['amount'], array( 'currency' => ! empty( $referral['currency_id'] ) ? $referral['currency_id'] : '' ) ) ); // phpcs:ignore ?></td>
								<td data-title="<?php echo esc_html__( 'Payout status', 'affiliate-for-woocommerce' ); ?>" title="<?php echo esc_html( ( ! empty( $referral_status ) ) ? afwc_get_commission_statuses( $referral_status ) : '' ); ?>"><div class="afwc_circle <?php echo esc_attr( 'fill_' . ( ! empty( $referral_status ) ? afwc_get_commission_status_colors( $referral_status ) : '' ) ); ?>"></div></td>
							</tr>
							<?php } ?>
						<?php } else { ?>
							<tr>
								<td colspan="<?php echo esc_attr( $payout_colspan ); ?>"><?php echo esc_html__( 'No referrals to show', 'affiliate-for-woocommerce' ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
					<?php if ( $referrals['total_count'] > count( $referrals['rows'] ) ) { ?>
						<tfoot>
							<tr>
								<td colspan="<?php echo esc_attr( $payout_colspan ); ?>">
									<a id="afwc_load_more_referrals" data-max_record="<?php echo esc_attr( $referrals['total_count'] ); ?>"><?php echo esc_html__( 'Load more', 'affiliate-for-woocommerce' ); ?></button>
								</td>
							</tr>
						</tfoot>
					<?php } ?>
				</table>
				<div class="afwc-table-header"><?php echo esc_html__( 'Payout History', 'affiliate-for-woocommerce' ); ?></div>
				<table class="afwc_payout_history">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Date', 'affiliate-for-woocommerce' ); ?></th>
							<th><?php echo esc_html__( 'Amount', 'affiliate-for-woocommerce' ); ?></th>
							<th><?php echo esc_html__( 'Method', 'affiliate-for-woocommerce' ); ?></th>
							<th><?php echo esc_html__( 'Notes', 'affiliate-for-woocommerce' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $payout_history['payouts'] ) ) { ?>
							<?php
							foreach ( $payout_history['payouts'] as $payout ) {
								$payout_method = ! empty( $payout['method'] ) ? afwc_get_payout_methods( $payout['method'] ) : '';
								?>
							<tr>
								<td data-title="<?php echo esc_html__( 'Date', 'affiliate-for-woocommerce' ); ?>" ><?php echo ( ! empty( $payout['datetime'] ) ) ? esc_html( gmdate( $date_format, strtotime( $payout['datetime'] ) ) ) : ''; ?></td>
								<td data-title="<?php echo esc_html__( 'Amount', 'affiliate-for-woocommerce' ); ?>" ><?php echo wp_kses_post( wc_price( ( ! empty( $payout['amount'] ) ? $payout['amount'] : 0 ), array( 'currency' => ! empty( $payout['currency'] ) ? $payout['currency'] : '' ) ) ); ?></td>
								<td data-title="<?php echo esc_html__( 'Method', 'affiliate-for-woocommerce' ); ?>" title="<?php echo esc_html( $payout_method ); ?>"><?php echo esc_html( $payout_method ); ?></td>
								<td data-title="<?php echo esc_html__( 'Notes', 'affiliate-for-woocommerce' ); ?>" ><?php echo ( ! empty( $payout['payout_notes'] ) ) ? wp_kses_post( $payout['payout_notes'] ) : ''; ?></td>
							</tr>
						<?php } ?>
					<?php } else { ?>
						<tr>
							<td colspan="4"><?php echo esc_html__( 'No payouts to show', 'affiliate-for-woocommerce' ); ?></td>
						</tr>
					<?php } ?>
					</tbody>
					<?php if ( $payout_history['total_count'] > count( $payout_history['payouts'] ) ) { ?>
						<tfoot>
							<tr>
								<td colspan="4">
									<a id="afwc_load_more_payouts" data-max_record="<?php echo esc_attr( $payout_history['total_count'] ); ?>"><?php echo esc_html__( 'Load more', 'affiliate-for-woocommerce' ); ?></button>
								</td>
							</tr>
						</tfoot>
					<?php } ?>
				</table>
			</div>
			<?php
		}

		/**
		 * Function to get visitors data
		 *
		 * @param array $args arguments.
		 * @return array visitors data
		 */
		public function get_visitors_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$visitors_result = $wpdb->get_var( // phpcs:ignore
												$wpdb->prepare( // phpcs:ignore
													"SELECT IFNULL(COUNT( DISTINCT CONCAT_WS( ':', ip, user_id ) ), 0)
																FROM {$wpdb->prefix}afwc_hits
																WHERE affiliate_id = %d
																	AND (datetime BETWEEN %s AND %s)",
													$affiliate_id,
													$from,
													$to
												)
				);
			} else {
				$visitors_result = $wpdb->get_var( // phpcs:ignore
												$wpdb->prepare( // phpcs:ignore
													"SELECT IFNULL(COUNT( DISTINCT CONCAT_WS( ':', ip, user_id ) ), 0)
																FROM {$wpdb->prefix}afwc_hits
																WHERE affiliate_id = %d",
													$affiliate_id
												)
				);
			}

			return apply_filters( 'afwc_my_account_clicks_result', array( 'visitors' => $visitors_result ), $args );
		}

		/**
		 * Function to get customers data
		 *
		 * @param array $args arguments.
		 * @return array customers data
		 */
		public function get_customers_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$customers_result = $wpdb->get_var( // phpcs:ignore
												$wpdb->prepare( // phpcs:ignore
													"SELECT IFNULL(COUNT( DISTINCT IF( user_id > 0, user_id, CONCAT_WS( ':', ip, user_id ) ) ), 0) as customers_count
																FROM {$wpdb->prefix}afwc_referrals
																WHERE affiliate_id = %d
																	AND (datetime BETWEEN %s AND %s)",
													$affiliate_id,
													$from,
													$to
												)
				);
			} else {
				$customers_result = $wpdb->get_var( // phpcs:ignore
												$wpdb->prepare( // phpcs:ignore
													"SELECT IFNULL(COUNT( DISTINCT IF( user_id > 0, user_id, CONCAT_WS( ':', ip, user_id ) ) ), 0) as customers_count
																FROM {$wpdb->prefix}afwc_referrals
																WHERE affiliate_id = %d",
													$affiliate_id
												)
				);
			}

			return apply_filters( 'afwc_my_account_customers_result', array( 'customers' => $customers_result ), $args );
		}

		/**
		 * Function to get payouts data
		 *
		 * @param array $args arguments.
		 * @return array $payouts_result payouts data
		 */
		public function get_payouts_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;

			if ( ! empty( $from ) && ! empty( $to ) ) {
				$payouts_result = $wpdb->get_var( // phpcs:ignore
												$wpdb->prepare( // phpcs:ignore
													"SELECT SUM(amount)
															FROM {$wpdb->prefix}afwc_payouts
															WHERE affiliate_id = %d
																AND (datetime BETWEEN %s AND %s)",
													$affiliate_id,
													$from,
													$to
												)
				);
			} else {
				$payouts_result = $wpdb->get_var( // phpcs:ignore
												$wpdb->prepare( // phpcs:ignore
													"SELECT SUM(amount)
															FROM {$wpdb->prefix}afwc_payouts
															WHERE affiliate_id = %d",
													$affiliate_id
												)
				);
			}

			return apply_filters( 'afwc_my_account_payouts_result', array( 'payouts' => $payouts_result ), $args );

		}

		/**
		 * Function to get kpis data
		 *
		 * @param array $args arguments.
		 * @return array $kpis kpis data
		 */
		public function get_kpis_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;

			$prefixed_statuses   = afwc_get_prefixed_order_statuses();
			$option_order_status = 'afwc_order_statuses_' . uniqid();
			update_option( $option_order_status, implode( ',', $prefixed_statuses ), 'no' );

			$temp_option_key     = 'afwc_order_status_' . uniqid();
			$paid_order_statuses = afwc_get_paid_order_status();
			update_option( $temp_option_key, implode( ',', $paid_order_statuses ), 'no' );

			if ( ! empty( $from ) && ! empty( $to ) ) {
				// Need to consider all order_statuses to get correct rejected_commission and hence not passing order_statuses.
				if ( AFWC_IS_HPOS_ENABLED ) {
					$kpis_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(count(DISTINCT wco.id), 0) AS number_of_orders,
																		IFNULL(SUM( afwcr.amount ), 0) AS gross_commissions,
																		IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS paid_commission,
																		IFNULL(SUM(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT( afwcr.order_status USING %s ) COLLATE %s, ( SELECT CONVERT( option_value USING %s ) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  ) THEN afwcr.amount END), 0) AS unpaid_commission,
																		IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS rejected_commission,
																		IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS paid_count,
																		IFNULL(COUNT(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT( afwcr.order_status USING %s ) COLLATE %s, ( SELECT CONVERT( option_value USING %s ) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  )  THEN 1 END), 0) AS unpaid_count,
																		IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS rejected_count
																	FROM {$wpdb->prefix}afwc_referrals AS afwcr
																		JOIN {$wpdb->prefix}wc_orders AS wco
																			ON (afwcr.post_id = wco.id
																				AND wco.type = %s
																				AND afwcr.affiliate_id = %d)
																	WHERE afwcr.status != %s AND (afwcr.datetime BETWEEN %s AND %s)",
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															'shop_order',
															$affiliate_id,
															AFWC_REFERRAL_STATUS_DRAFT,
															$from,
															$to
														),
						'ARRAY_A'
					);

					$order_total = $wpdb->get_results( // phpcs:ignore
										$wpdb->prepare( // phpcs:ignore
											"SELECT IFNULL(SUM(wco.total_amount), 0) AS order_total
													FROM {$wpdb->prefix}afwc_referrals AS afwcr
													JOIN {$wpdb->prefix}wc_orders AS wco
													ON (afwcr.post_id = wco.id
														AND wco.type = %s
														AND afwcr.affiliate_id = %d)
													WHERE afwcr.status != %s
													   	AND FIND_IN_SET ( CONVERT( afwcr.order_status USING %s ) COLLATE %s, ( SELECT CONVERT( option_value using %s ) COLLATE %s
																							FROM {$wpdb->prefix}options
																							WHERE option_name = %s ) )
														AND (afwcr.datetime BETWEEN %s AND %s)",
											'shop_order',
											$affiliate_id,
											AFWC_REFERRAL_STATUS_DRAFT,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											$option_order_status,
											$from,
											$to
										),
						'ARRAY_A'
					);

				} else {
					$kpis_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(count(DISTINCT pm.post_id), 0) AS number_of_orders,
																		IFNULL(SUM( afwcr.amount ), 0) as gross_commissions,
																		IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS paid_commission,
																		IFNULL(SUM(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT(order_status USING %s) COLLATE %s, ( SELECT CONVERT(option_value USING %s) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  ) THEN afwcr.amount END), 0) AS unpaid_commission,
																		IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS rejected_commission,
																		IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS paid_count,
																		IFNULL(COUNT(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT(order_status USING %s) COLLATE %s, ( SELECT CONVERT(option_value USING %s) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  )  THEN 1 END), 0) AS unpaid_count,
																		IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS rejected_count
																	FROM {$wpdb->prefix}afwc_referrals AS afwcr
																		JOIN {$wpdb->postmeta} AS pm
																			ON (afwcr.post_id = pm.post_id
																					AND pm.meta_key = %s
																					AND afwcr.affiliate_id = %d)
																	WHERE afwcr.status != %s AND (afwcr.datetime BETWEEN %s AND %s)",
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															'_order_total',
															$affiliate_id,
															AFWC_REFERRAL_STATUS_DRAFT,
															$from,
															$to
														),
						'ARRAY_A'
					);

					$order_total =  $wpdb->get_results( // phpcs:ignore
										$wpdb->prepare( // phpcs:ignore
											"SELECT IFNULL(SUM(pm.meta_value), 0) AS order_total
													FROM {$wpdb->prefix}afwc_referrals AS afwcr
													JOIN {$wpdb->postmeta} AS pm
													ON (afwcr.post_id = pm.post_id
														AND pm.meta_key = %s
														AND afwcr.affiliate_id = %d)
													JOIN {$wpdb->posts} AS posts
														ON (posts.ID = afwcr.post_id
														AND posts.post_type = %s) 
													WHERE afwcr.status != %s
	                                                    AND FIND_IN_SET ( CONVERT(afwcr.order_status USING %s) COLLATE %s, ( SELECT CONVERT(option_value USING %s) COLLATE %s
																							FROM {$wpdb->prefix}options
																							WHERE option_name = %s ) )
	                                                    AND (afwcr.datetime BETWEEN %s AND %s)",
											'_order_total',
											$affiliate_id,
											'shop_order',
											AFWC_REFERRAL_STATUS_DRAFT,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											$option_order_status,
											$from,
											$to
										),
						'ARRAY_A'
					);

				}
			} else {
				if ( AFWC_IS_HPOS_ENABLED ) {
					$kpis_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(count(DISTINCT wco.id), 0) AS number_of_orders,
																				IFNULL(SUM( afwcr.amount ), 0) as gross_commissions,
																				IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS paid_commission,
																				IFNULL(SUM(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT( order_status USING %s ) COLLATE %s, ( SELECT CONVERT( option_value USING %s ) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  ) THEN afwcr.amount END), 0) AS unpaid_commission,
																				IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS rejected_commission,
																				IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS paid_count,
																				IFNULL(COUNT(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT( order_status USING %s ) COLLATE %s, ( SELECT CONVERT( option_value USING %s ) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  ) THEN 1 END), 0) AS unpaid_count,
																				IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS rejected_count
																		FROM {$wpdb->prefix}afwc_referrals AS afwcr
																			JOIN {$wpdb->prefix}wc_orders AS wco
																				ON (afwcr.post_id = wco.id
																						AND wco.type = %s
																						AND afwcr.affiliate_id = %d)
																						WHERE afwcr.status != %s",
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															'shop_order',
															$affiliate_id,
															AFWC_REFERRAL_STATUS_DRAFT
														),
						'ARRAY_A'
					);

					$order_total = $wpdb->get_results( // phpcs:ignore
										$wpdb->prepare( // phpcs:ignore
											"SELECT IFNULL(SUM(wco.total_amount), 0) AS order_total
													FROM {$wpdb->prefix}afwc_referrals AS afwcr
													JOIN {$wpdb->prefix}wc_orders AS wco
													ON (afwcr.post_id = wco.id
														AND wco.type = %s
														AND afwcr.affiliate_id = %d)
													WHERE afwcr.status != %s
													   	AND FIND_IN_SET ( CONVERT( afwcr.order_status using %s ) COLLATE %s, ( SELECT CONVERT( option_value using %s ) COLLATE %s
																							FROM {$wpdb->prefix}options
																							WHERE option_name = %s ) )",
											'shop_order',
											$affiliate_id,
											AFWC_REFERRAL_STATUS_DRAFT,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											$option_order_status
										),
						'ARRAY_A'
					);
				} else {
					$kpis_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(count(DISTINCT pm.post_id), 0) AS number_of_orders,
																				IFNULL(SUM( afwcr.amount ), 0) as gross_commissions,
																				IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS paid_commission,
																				IFNULL(SUM(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT(order_status USING %s) COLLATE %s, ( SELECT CONVERT(option_value USING %s) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  ) THEN afwcr.amount END), 0) AS unpaid_commission,
																				IFNULL(SUM(CASE WHEN afwcr.status = %s THEN afwcr.amount END), 0) AS rejected_commission,
																				IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS paid_count,
																				IFNULL(COUNT(CASE WHEN afwcr.status = %s AND FIND_IN_SET ( CONVERT(order_status USING %s) COLLATE %s, ( SELECT CONVERT(option_value USING %s) COLLATE %s
																											FROM {$wpdb->prefix}options
																											WHERE option_name = %s )  ) THEN 1 END), 0) AS unpaid_count,
																				IFNULL(COUNT(CASE WHEN afwcr.status = %s THEN 1 END), 0) AS rejected_count
																		FROM {$wpdb->prefix}afwc_referrals AS afwcr
																			JOIN {$wpdb->postmeta} AS pm
																				ON (afwcr.post_id = pm.post_id
																						AND pm.meta_key = %s
																						AND afwcr.affiliate_id = %d)
																						WHERE afwcr.status != %s",
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															AFWC_REFERRAL_STATUS_PAID,
															AFWC_REFERRAL_STATUS_UNPAID,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															AFWC_SQL_CHARSET,
															AFWC_SQL_COLLATION,
															$temp_option_key,
															AFWC_REFERRAL_STATUS_REJECTED,
															'_order_total',
															$affiliate_id,
															AFWC_REFERRAL_STATUS_DRAFT
														),
						'ARRAY_A'
					);

					$order_total =  $wpdb->get_results( // phpcs:ignore
										$wpdb->prepare( // phpcs:ignore
											"SELECT IFNULL(SUM(pm.meta_value), 0) AS order_total
													FROM {$wpdb->prefix}afwc_referrals AS afwcr
													JOIN {$wpdb->postmeta} AS pm
													ON (afwcr.post_id = pm.post_id
														AND pm.meta_key = %s
														AND afwcr.affiliate_id = %d)
													JOIN {$wpdb->posts} AS posts
														ON (posts.ID = afwcr.post_id
														AND posts.post_type = %s) 
													WHERE afwcr.status != %s
	                                                    AND FIND_IN_SET ( CONVERT(afwcr.order_status USING %s) COLLATE %s, ( SELECT CONVERT(option_value USING %s) COLLATE %s
																							FROM {$wpdb->prefix}options
																							WHERE option_name = %s ) )",
											'_order_total',
											$affiliate_id,
											'shop_order',
											AFWC_REFERRAL_STATUS_DRAFT,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											AFWC_SQL_CHARSET,
											AFWC_SQL_COLLATION,
											$option_order_status
										),
						'ARRAY_A'
					);
				}
			}
			delete_option( $option_order_status );
			delete_option( $temp_option_key );

			$kpis_result[0]['order_total'] = ( ! empty( $order_total[0]['order_total'] ) ) ? $order_total[0]['order_total'] : 0;

			return apply_filters(
				'afwc_my_account_kpis_result',
				array(
					'sales'               => ( ! empty( $kpis_result[0]['order_total'] ) ) ? $kpis_result[0]['order_total'] : 0,
					'number_of_orders'    => ( ! empty( $kpis_result[0]['number_of_orders'] ) ) ? $kpis_result[0]['number_of_orders'] : 0,
					'paid_commission'     => ( ! empty( $kpis_result[0]['paid_commission'] ) ) ? $kpis_result[0]['paid_commission'] : 0,
					'unpaid_commission'   => ( ! empty( $kpis_result[0]['unpaid_commission'] ) ) ? $kpis_result[0]['unpaid_commission'] : 0,
					'rejected_commission' => ( ! empty( $kpis_result[0]['rejected_commission'] ) ) ? $kpis_result[0]['rejected_commission'] : 0,
					'paid_count'          => ( ! empty( $kpis_result[0]['paid_count'] ) ) ? $kpis_result[0]['paid_count'] : 0,
					'unpaid_count'        => ( ! empty( $kpis_result[0]['unpaid_count'] ) ) ? $kpis_result[0]['unpaid_count'] : 0,
					'rejected_count'      => ( ! empty( $kpis_result[0]['rejected_count'] ) ) ? $kpis_result[0]['rejected_count'] : 0,
					'gross_commission'    => ( ! empty( $kpis_result[0]['gross_commissions'] ) ) ? $kpis_result[0]['gross_commissions'] : 0,
				),
				array(
					'source'      => $this,
					'kpis_result' => $kpis_result,
				)
			);

		}

		/**
		 * Function to get refunds data
		 *
		 * @param array $args arguments.
		 * @return array $refunds refunds.
		 */
		public function get_refunds_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;

			if ( ! empty( $from ) && ! empty( $to ) ) {

				if ( AFWC_IS_HPOS_ENABLED ) {
					$refunds_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(SUM(ABS(wco.total_amount)), 0) AS refund_amount,
																				IFNULL(COUNT(DISTINCT wco.parent_order_id), 0) AS refund_order_count
																		FROM {$wpdb->prefix}wc_orders AS wco
																			JOIN {$wpdb->prefix}afwc_referrals AS afwcr
																				ON (afwcr.post_id = wco.parent_order_id
																					AND wco.type = %s
																					AND afwcr.affiliate_id = %d)
																			WHERE afwcr.datetime BETWEEN %s AND %s",
															'shop_order_refund',
															$affiliate_id,
															$from,
															$to
														),
						'ARRAY_A'
					);
				} else {
					$refunds_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(SUM(pm.meta_value), 0) AS refund_amount,
																				IFNULL(COUNT(DISTINCT p.post_parent), 0) AS refund_order_count
																		FROM {$wpdb->posts} AS p
																			JOIN {$wpdb->postmeta} AS pm
																				ON (pm.post_id = p.ID
																						AND pm.meta_key = %s
																						AND p.post_type = %s)
																			JOIN {$wpdb->prefix}afwc_referrals AS afwcr
																				ON (afwcr.post_id = p.post_parent)
																		WHERE afwcr.affiliate_id = %d
																			AND (afwcr.datetime BETWEEN %s AND %s) ",
															'_refund_amount',
															'shop_order_refund',
															$affiliate_id,
															$from,
															$to
														),
						'ARRAY_A'
					);
				}
			} else {
				if ( AFWC_IS_HPOS_ENABLED ) {
					$refunds_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(SUM(ABS(wco.total_amount)), 0) AS refund_amount,
																				IFNULL(COUNT(DISTINCT wco.parent_order_id), 0) AS refund_order_count
																		FROM {$wpdb->prefix}wc_orders AS wco
																			JOIN {$wpdb->prefix}afwc_referrals AS afwcr
																				ON (afwcr.post_id = wco.parent_order_id
																					AND wco.type = %s
																					AND afwcr.affiliate_id = %d)",
															'shop_order_refund',
															$affiliate_id
														),
						'ARRAY_A'
					);
				} else {
					$refunds_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT IFNULL(SUM(pm.meta_value), 0) AS refund_amount,
																				IFNULL(COUNT(DISTINCT p.post_parent), 0) AS refund_order_count
																		FROM {$wpdb->posts} AS p
																			JOIN {$wpdb->postmeta} AS pm
																				ON (pm.post_id = p.ID
																						AND pm.meta_key = %s
																						AND p.post_type = %s)
																			JOIN {$wpdb->prefix}afwc_referrals AS afwcr
																				ON (afwcr.post_id = p.post_parent)
																		WHERE afwcr.affiliate_id = %d",
															'_refund_amount',
															'shop_order_refund',
															$affiliate_id
														),
						'ARRAY_A'
					);
				}
			}
			$refunds = array(
				'refund_amount'      => ( isset( $refunds_result[0]['refund_amount'] ) ) ? $refunds_result[0]['refund_amount'] : 0,
				'refund_order_count' => ( isset( $refunds_result[0]['refund_order_count'] ) ) ? $refunds_result[0]['refund_order_count'] : 0,
			);

			return apply_filters( 'afwc_my_account_refunds_result', $refunds, $args );

		}

		/**
		 * Function to get referrals data
		 *
		 * @param array $args arguments.
		 * @return array $referrals referrals data
		 */
		public function get_referrals_data( $args = array() ) {
			global $wpdb;

			$from         = ( ! empty( $args['from'] ) ) ? $args['from'] : '';
			$to           = ( ! empty( $args['to'] ) ) ? $args['to'] : '';
			$affiliate_id = ( ! empty( $args['affiliate_id'] ) ) ? $args['affiliate_id'] : 0;
			$limit        = apply_filters( 'afwc_my_account_referrals_per_page', get_option( 'afwc_my_account_referrals_per_page', AFWC_MY_ACCOUNT_DEFAULT_BATCH_LIMIT ) );
			$offset       = ( ! empty( $args['offset'] ) ) ? $args['offset'] : 0;

			$args['limit']  = $limit;
			$args['offset'] = $offset;

			if ( ! empty( $from ) && ! empty( $to ) ) {

				$referrals_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT CONVERT_TZ( afwcr.datetime, '+00:00', %s ) as datetime,
																			   afwcr.amount,
																			   afwcr.currency_id,
																			   afwcr.status,
																			   afwcr.post_id
																		FROM {$wpdb->prefix}afwc_referrals AS afwcr
																		WHERE afwcr.affiliate_id = %d
																			AND (afwcr.datetime BETWEEN %s AND %s)
																		ORDER BY afwcr.datetime DESC
																		LIMIT %d OFFSET %d",
															AFWC_TIMEZONE_STR,
															$affiliate_id,
															$from,
															$to,
															$limit,
															$offset
														),
					'ARRAY_A'
				);
				$order_ids        = array_map(
					function( $referrals_result ) {
						return $referrals_result['post_id'];
					},
					$referrals_result
				);

				$option_nm = 'afwc_order_ids_' . uniqid();
				update_option( $option_nm, implode( ',', array_unique( $order_ids ) ), 'no' );

				if ( AFWC_IS_HPOS_ENABLED ) {
					$referrals_details = $wpdb->get_results( // phpcs:ignore
													$wpdb->prepare( // phpcs:ignore
														"SELECT order_id,
																CONCAT_WS( ' ', first_name, last_name ) AS display_name
															FROM {$wpdb->prefix}wc_order_addresses
															WHERE address_type = %s
															AND FIND_IN_SET ( order_id, ( SELECT option_value
																									FROM {$wpdb->prefix}options
																									WHERE option_name = %s ) )",
														'billing',
														$option_nm
													),
						'ARRAY_A'
					);
				} else {
					$referrals_details = $wpdb->get_results( // phpcs:ignore
													$wpdb->prepare( // phpcs:ignore
														"SELECT post_id AS order_id,
																	GROUP_CONCAT(CASE WHEN meta_key IN ('_billing_first_name', '_billing_last_name') THEN meta_value END SEPARATOR ' ') AS display_name
																	FROM {$wpdb->postmeta} AS postmeta
																	WHERE meta_key IN ( '_billing_first_name', '_billing_last_name' )
																		AND FIND_IN_SET ( post_id, ( SELECT option_value
																									FROM {$wpdb->prefix}options
																									WHERE option_name = %s ) )
																							GROUP BY order_id",
														$option_nm
													),
						'ARRAY_A'
					);
				}

				delete_option( $option_nm );

				$referrals_total_count = $wpdb->get_var( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT COUNT(*)
																		FROM {$wpdb->prefix}afwc_referrals AS afwcr
																				LEFT JOIN {$wpdb->users} AS u
																					ON (afwcr.user_id = u.ID)
																		WHERE afwcr.affiliate_id = %d
																			AND (afwcr.datetime BETWEEN %s AND %s)",
															$affiliate_id,
															$from,
															$to
														)
				);

			} else {
				$referrals_result = $wpdb->get_results( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT CONVERT_TZ( afwcr.datetime, '+00:00', %s ) as datetime,
																			   afwcr.amount,
																			   afwcr.currency_id,
																			   afwcr.status,
																			   afwcr.post_id
																		FROM {$wpdb->prefix}afwc_referrals AS afwcr
																		WHERE afwcr.affiliate_id = %d
																		ORDER BY afwcr.datetime DESC
																		LIMIT %d OFFSET %d",
															AFWC_TIMEZONE_STR,
															$affiliate_id,
															$limit,
															$offset
														),
					'ARRAY_A'
				);

				$order_ids = array_map(
					function( $referrals_result ) {
						return $referrals_result['post_id'];
					},
					$referrals_result
				);

				$option_nm = 'afwc_order_ids_' . uniqid();
				update_option( $option_nm, implode( ',', array_unique( $order_ids ) ), 'no' );

				if ( AFWC_IS_HPOS_ENABLED ) {
					$referrals_details = $wpdb->get_results( // phpcs:ignore
													$wpdb->prepare( // phpcs:ignore
														"SELECT order_id,
																CONCAT_WS( ' ', first_name, last_name ) AS display_name
															FROM {$wpdb->prefix}wc_order_addresses
															WHERE address_type = %s
															AND FIND_IN_SET ( order_id, ( SELECT option_value
																									FROM {$wpdb->prefix}options
																									WHERE option_name = %s ) )",
														'billing',
														$option_nm
													),
						'ARRAY_A'
					);
				} else {
					$referrals_details = $wpdb->get_results( // phpcs:ignore
													$wpdb->prepare( // phpcs:ignore
														"SELECT post_id AS order_id,
																			GROUP_CONCAT(CASE WHEN meta_key IN ('_billing_first_name', '_billing_last_name') THEN meta_value END SEPARATOR ' ') AS display_name
																	FROM {$wpdb->postmeta} AS postmeta
																	WHERE meta_key IN ( '_billing_first_name', '_billing_last_name' )
																		AND FIND_IN_SET ( post_id, ( SELECT option_value
																									FROM {$wpdb->prefix}options
																									WHERE option_name = %s ) )
																							GROUP BY order_id",
														$option_nm
													),
						'ARRAY_A'
					);
				}

				delete_option( $option_nm );

				$referrals_total_count = $wpdb->get_var( // phpcs:ignore
														$wpdb->prepare( // phpcs:ignore
															"SELECT COUNT(*)
																		FROM {$wpdb->prefix}afwc_referrals AS afwcr
																				LEFT JOIN {$wpdb->users} AS u
																					ON (afwcr.user_id = u.ID)
																		WHERE afwcr.affiliate_id = %d",
															$affiliate_id
														)
				);
			}

			// format referral details.
			foreach ( $referrals_details as $referral ) {
				$referral_id_detail_map[ $referral['order_id'] ] = $referral['display_name'];
			}

			foreach ( $referrals_result as $key => $ref ) {
				$referrals_result[ $key ]['display_name'] = ( ! empty( $referral_id_detail_map[ $ref['post_id'] ] ) ) ? $referral_id_detail_map[ $ref['post_id'] ] : __( 'Guest', 'affiliate-for-woocomerce' );
			}

			$referrals = array(
				'rows'        => $referrals_result,
				'total_count' => $referrals_total_count,
			);

			return apply_filters( 'afwc_my_account_referrals_result', $referrals, $args );

		}

		/**
		 * Function to show content in affiliate profile tab.
		 *
		 * @param WP_User $user The user object.
		 */
		public function profile_resources_content( $user = null ) {

			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}

			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			if ( ! class_exists( 'WC_AJAX' ) ) {
				include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-ajax.php';
			}

			global $affiliate_for_woocommerce;

			// Data.
			$user_id                                = intval( $user->ID );
			$pname                                  = afwc_get_pname();
			$affiliate_id                           = afwc_get_affiliate_id_based_on_user_id( $user_id );
			$afwc_ref_url_id                        = get_user_meta( $user_id, 'afwc_ref_url_id', true );
			$affiliate_identifier                   = ( ! empty( $afwc_ref_url_id ) ) ? $afwc_ref_url_id : $affiliate_id;
			$afwc_allow_custom_affiliate_identifier = get_option( 'afwc_allow_custom_affiliate_identifier', 'yes' );
			$afwc_use_pretty_referral_links         = get_option( 'afwc_use_pretty_referral_links', 'no' );
			$plugin_data                            = $affiliate_for_woocommerce->get_plugin_data();

			if ( ! wp_script_is( 'afwc-profile-js' ) ) {
				wp_register_script( 'afwc-profile-js', AFWC_PLUGIN_URL . '/assets/js/my-account/affiliate-profile.js', array( 'jquery', 'wp-i18n' ), $plugin_data['Version'], true );
				if ( function_exists( 'wp_set_script_translations' ) ) {
					wp_set_script_translations( 'afwc-profile-js', 'affiliate-for-woocommerce', AFWC_PLUGIN_DIR_PATH . 'languages' );
				}
			}
			wp_enqueue_script( 'afwc-profile-js' );

			$localize_params = array(
				'pName'                   => $pname,
				'homeURL'                 => esc_url( trailingslashit( home_url() ) ),
				'saveAccountDetailsURL'   => esc_url_raw( WC_AJAX::get_endpoint( 'afwc_save_account_details' ) ),
				'saveAccountSecurity'     => wp_create_nonce( 'afwc-save-account-details' ),
				'isPrettyReferralEnabled' => $afwc_use_pretty_referral_links,
			);

			if ( 'yes' === $afwc_allow_custom_affiliate_identifier ) {
				$localize_params['identifierRegexPattern']    = afwc_referral_params_regex_pattern();
				$localize_params['saveReferralURLIdentifier'] = esc_url_raw( WC_AJAX::get_endpoint( 'afwc_save_ref_url_identifier' ) );
				$localize_params['saveIdentifierSecurity']    = wp_create_nonce( 'afwc-save-ref-url-identifier' );
			}

			wp_localize_script( 'afwc-profile-js', 'afwcProfileParams', $localize_params );

			wp_register_style( 'afwc-profile-css', AFWC_PLUGIN_URL . '/assets/css/my-account/affiliate-profile.css', array(), $plugin_data['Version'], 'all' );
			if ( ! wp_style_is( 'afwc-profile-css', 'enqueued' ) ) {
				wp_enqueue_style( 'afwc-profile-css' );
			}

			// Template name.
			$template = 'my-account/affiliate-profile.php';
			// Default path of above template.
			$default_path = AFWC_PLUGIN_DIRPATH . '/templates/';
			// Pick from another location if found.
			$template_path = $affiliate_for_woocommerce->get_template_base_dir( $template );

			wc_get_template(
				$template,
				array(
					'user'                            => $user,
					'user_id'                         => $user_id,
					'pname'                           => $pname,
					'affiliate_id'                    => $affiliate_id,
					'affiliate_referral_url_id'       => $afwc_ref_url_id,
					'affiliate_identifier'            => $affiliate_identifier,
					'affiliate_manager_contact_email' => get_option( 'afwc_contact_admin_email_address', '' ),
					'afwc_use_referral_coupons'       => get_option( 'afwc_use_referral_coupons', 'yes' ),
					'afwc_allow_custom_affiliate_identifier' => $afwc_allow_custom_affiliate_identifier,
					'afwc_use_pretty_referral_links'  => $afwc_use_pretty_referral_links,

				),
				$template_path,
				$default_path
			);

		}

		/**
		 * Function to save account details
		 */
		public function afwc_save_account_details() {
			check_ajax_referer( 'afwc-save-account-details', 'security' );

			$user_id = get_current_user_id();
			if ( empty( $user_id ) ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x( 'Invalid user', 'account details updating error message', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$form_data = ( ! empty( $_POST['form_data'] ) ) ? sanitize_text_field( wp_unslash( $_POST['form_data'] ) ) : '';
			if ( empty( $form_data ) ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x(
							'Missing data',
							'account details updating error message',
							'affiliate-for-woocommerce'
						),
					)
				);
			}

			if ( ! empty( $form_data ) ) {
				parse_str( $form_data, $data );
			}

			$paypal_email = ! empty( $data['afwc_affiliate_paypal_email'] ) ? $data['afwc_affiliate_paypal_email'] : '';

			// Send success and delete the user meta if PayPal email is empty.
			if ( empty( $paypal_email ) ) {
				delete_user_meta( $user_id, 'afwc_paypal_email' );
				wp_send_json( array( 'success' => 'yes' ) );
			}

			// Send failure message if the email address is not valid.
			if ( false === is_email( $paypal_email ) ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x( 'The PayPal email address is incorrect.', 'Affiliate My Account page: PayPal email validation', 'affiliate-for-woocommerce' ),
					)
				);
			}

			// Send success and update the PayPal email.
			update_user_meta( $user_id, 'afwc_paypal_email', sanitize_email( $paypal_email ) );
			wp_send_json( array( 'success' => 'yes' ) );
		}

		/**
		 * Function to save referral URL identifier
		 */
		public function afwc_save_ref_url_identifier() {
			check_ajax_referer( 'afwc-save-ref-url-identifier', 'security' );

			$user_id = get_current_user_id();
			if ( empty( $user_id ) ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x( 'Invalid user', 'referral url identifier updating error message', 'affiliate-for-woocommerce' ),
					)
				);
			}

			$ref_url_id = ( ! empty( $_POST['ref_url_id'] ) ) ? wc_clean( wp_unslash( $_POST['ref_url_id'] ) ) : ''; // phpcs:ignore
			if ( empty( $ref_url_id ) ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x(
							'Missing data',
							'referral url identifier updating error message',
							'affiliate-for-woocommerce'
						),
					)
				);
			}

			if ( is_numeric( $ref_url_id ) ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x(
							'Numeric values are not allowed.',
							'referral url identifier updating error message',
							'affiliate-for-woocommerce'
						),
					)
				);
			}

			$user_with_ref_url_id = get_users(
				array(
					'meta_key'   => 'afwc_ref_url_id', // phpcs:ignore
					'meta_value' => $ref_url_id, // phpcs:ignore
					'number'     => 1,
					'fields'     => 'ids',
				)
			);
			$user_with_ref_url_id = reset( $user_with_ref_url_id );

			if ( ! empty( $user_with_ref_url_id ) && $user_id !== $user_with_ref_url_id ) {
				wp_send_json(
					array(
						'success' => 'no',
						'message' => _x(
							'This URL identifier already exists. Please choose a different identifier',
							'referral url identifier updating error message',
							'affiliate-for-woocommerce'
						),
					)
				);
			} else {
				update_user_meta( $user_id, 'afwc_ref_url_id', $ref_url_id );
				wp_send_json(
					array(
						'success' => 'yes',
						'message' => _x(
							'Identifier saved successfully.',
							'referral url identifier updated message',
							'affiliate-for-woocommerce'
						),
					)
				);
			}
		}

		/**
		 * Hooks for endpoint
		 */
		public function endpoint_hooks() {
			$affiliate_for_woocommerce = Affiliate_For_WooCommerce::get_instance();
			if ( $affiliate_for_woocommerce->is_wc_gte_34() ) {
				add_filter( 'woocommerce_get_settings_advanced', array( $this, 'add_endpoint_account_settings' ) );
			} else {
				add_filter( 'woocommerce_account_settings', array( $this, 'add_endpoint_account_settings' ) );
			}
		}

		/**
		 * Add UI option for changing Affiliate endpoints in WC settings
		 *
		 * @param mixed $settings Existing settings.
		 * @return mixed $settings
		 */
		public function add_endpoint_account_settings( $settings ) {
			$affiliate_endpoint_setting = array(
				'title'    => __( 'Affiliate', 'affiliate-for-woocommerce' ),
				'desc'     => __( 'Endpoint for the My Account &rarr; Affiliate page', 'affiliate-for-woocommerce' ),
				'id'       => 'woocommerce_myaccount_afwc_dashboard_endpoint',
				'type'     => 'text',
				'default'  => 'afwc-dashboard',
				'desc_tip' => true,
			);

			$after_key = 'woocommerce_myaccount_view_order_endpoint';

			$after_key = apply_filters(
				'afwc_endpoint_account_settings_after_key',
				$after_key,
				array(
					'settings' => $settings,
					'source'   => $this,
				)
			);

			Affiliate_For_WooCommerce::insert_setting_after( $settings, $after_key, $affiliate_endpoint_setting );

			return $settings;
		}

		/**
		 * Function to show campaigns content resources
		 *
		 * @param WP_User $user The user object.
		 */
		public function campaigns_content( $user = null ) {
			if ( ! is_object( $user ) || empty( $user->ID ) ) {
				return;
			}
			?>
			<div class="afw-campaigns"></div>

			<?php
		}

		/**
		 * Get gmt date.
		 *
		 * @param string $date The date.
		 * @param string $format The date format.
		 *
		 * @return string Return the date with gmt formatted if date is provided otherwise empty string.
		 */
		public function gmt_from_date( $date = '', $format = 'Y-m-d' ) {
			if ( empty( $date ) ) {
				return '';
			}

			return get_gmt_from_date( $date, $format );
		}

	}

}

AFWC_My_Account::get_instance();
