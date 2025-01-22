<?php
/**
 * @package WC_Integration_GMC_Settings
 * @category Integration
 *
 */

class WC_Integration_GMC_Settings extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		global $woocommerce;

		$this->id                 = 'gmc-settings';
		$this->method_title       = __( 'Google Merchant Center Integration', 'wc-google-merchant-center-customer-reviews' );
		$this->method_description = __( 'Make sure you have <a href="https://merchants.google.com/mc/programs" target="_blank">enabled the Customer Reviews program</a> inside your Google Merchant account. If you do not have a Google Merchant account, <a href="https://support.google.com/merchants/answer/7124018" target="_blank">click here</a> for step by step instructions on how to setup one and get your Google Merchant ID. </br></br>For personalized assistance in setting up your Google Merchant Center account and Google Product Feed <a href="https://webperfect.com/product/google-merchant-center-customer-reviews-integration/" target="_blank">click here.</a> <br/> <h2>Merchant Settings</h2>', 'wc-google-merchant-center-customer-reviews' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->api_key = $this->get_option( 'api_key' );
		$this->debug   = $this->get_option( 'debug' );

		// Actions.
		add_action( 'init', array( $this, 'gmcCheck' ) );
		$gmc_settings_data = get_option( 'woocommerce_gmc-settings_settings' );
		if ( $gmc_settings_data['gmc_gtin_enable'] == 'yes' ) {
			add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'gmcDoRenderGTINField' ) );
		}
		add_action( 'woocommerce_process_product_meta', array( $this, 'gmcSaveGTINField' ) );
		add_action( 'wp_footer', array( $this, 'gmcBadge' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'gmcLoadResource' ) );

		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

		// Filters.
		add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );
	}


	/**
	 * Initialize integration settings form fields.
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'gmc_merchantId'           => array(
				'title'       => __( 'Google Merchant ID', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'text',
				'description' => __( 'Your Merchant ID can be found on your Google Merchant center account, on the left below your name.', 'wc-google-merchant-center-customer-reviews' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'gmc_merchant_language'    => array(
				'title'       => __( 'Language', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'select',
				'description' => __( 'We recommend leaving Auto-Detect, unless you need to use a specific language.', 'wc-google-merchant-center-customer-reviews' ),
				'desc_tip'    => true,
				'options'     => $this->getLanguageOptions(),
				'default'     => ''
			),
			'gmc_survey_title'         => array(
				'title' => __( 'Survey Opt-in Popup Settings', 'wc-google-merchant-center-customer-reviews' ),
				'type'  => 'title'
			),
			'gmc_popup_position'       => array(
				'title'       => __( 'Popup Position', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'select',
				'description' => __( 'This determines the position of the Opt-in Survey Popup on the Thank You page.', 'wc-google-merchant-center-customer-reviews' ),
				'desc_tip'    => true,
				'options'     => $this->getPopupPosition(),
				'default'     => ''
			),
			'gmc_estimated_delivery'   => array(
				'title'       => __( 'Estimated Delivery (Days)', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'number',
				'description' => __( 'Indicate the approximate days for delivery so that the review request is properly timed with the delivery of the product to the customer.', 'wc-google-merchant-center-customer-reviews' ),
				'desc_tip'    => true,
				'default'     => ''
			),
			'gmc_gtin_settings_title'  => array(
				'title' => __( 'GTIN Integration', 'wc-google-merchant-center-customer-reviews' ),
				'type'  => 'title'
			),
			'gmc_gtin_enable'          => array(
				'title'       => __( 'Enable GTIN Integration', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'checkbox',
				'label'       => ' ',
				'description' => __( 'Leave the box unchecked for Google to collect <strong>only customer reviews</strong> about the shopping experience on your site (RECOMMENDED).</br></br> GTIN Integration is an <a href="https://support.google.com/merchants/answer/7519329?hl=en&ref_topic=7105160" target="_blank">OPTIONAL feature</a> of the Google Reviews program that modifies the survey opt-in code to gather product reviews.</br></br> If you check this box, this plugin will add a GTIN field to each of your products under the Inventory tab. The GTIN Integration will only work if you have a valid product feed in your Merchant Center account. </br></br>Depending on what you sell, your GTIN can be UPC, EAN, or ISBN numbers. Learn more about GTIN <a href="https://www.gtin.info/" target="_blank">here</a>. The easiest way to create and submit a product feed to Google is by using the <a href="https://woocommerce.com/products/google-product-feed/?aff=7553&cid=629120" target="_blank">WooCommerce Google Product Feed</a>.', 'wc-google-merchant-center-customer-reviews'
				),
				'default'     => 'no'
			),
			'gmc_gtin_options'         => array(
				'title'       => __( 'GTIN Field', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'select',
				'description' => '(Global Trade Item Number) <p>IMPORTANT: This will work only if you have GTIN values for your Products </p>',
				'options'     => array( '_gtin' => 'DEFAULT (_gtin)' ),
				'default'     => ''
			),
			'gmc_badge_settings_title' => array(
				'title' => __( 'Badge Settings', 'wc-google-merchant-center-customer-reviews' ),
				'type'  => 'title'
			),
			'gmc_badge_enable'         => array(
				'title'       => __( 'Enable Rating Badge', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'checkbox',
				'label'       => ' ',
				'description' => __( 'This is the Google Reviews Badge that appears on all your web pages. If you have no reviews yet, we recommend you uncheck this box', 'wc-google-merchant-center-customer-reviews' ),
				'default'     => 'no'
			),
			'gmc_badge_position'       => array(
				'title'       => __( 'Rating Badge Position', 'wc-google-merchant-center-customer-reviews' ),
				'type'        => 'select',
				'description' => __( 'This allows you to select the position of the Google Reviews Badge on your websites pages.', 'wc-google-merchant-center-customer-reviews' ),
				'desc_tip'    => true,
				'options'     => array(
					'BOTTOM_LEFT'  => __( 'Bottom Left', 'wc-google-merchant-center-customer-reviews' ),
					'BOTTOM_RIGHT' => __( 'Bottom Right', 'wc-google-merchant-center-customer-reviews' )
				),
				'default'     => ''
			)
		);
	}

	/**
	 * Get List of Languages
	 *
	 * @return Array languages
	 */
	public function getLanguageOptions() {

		$languages = array(
			''       => __( 'Auto-detect', 'wc-google-merchant-center-customer-reviews' ),
			'af'     => __( 'Afrikaans', 'wc-google-merchant-center-customer-reviews' ),
			'ar-AE'  => __( 'Arabic (United Arab Emirates)', 'wc-google-merchant-center-customer-reviews' ),
			'cs'     => __( 'Czech', 'wc-google-merchant-center-customer-reviews' ),
			'da'     => __( 'Danish', 'wc-google-merchant-center-customer-reviews' ),
			'de'     => __( 'German', 'wc-google-merchant-center-customer-reviews' ),
			'en_AU'  => __( 'English (Australia)', 'wc-google-merchant-center-customer-reviews' ),
			'en_GB'  => __( 'English (United Kingdom)', 'wc-google-merchant-center-customer-reviews' ),
			'en_US'  => __( 'English (United States)', 'wc-google-merchant-center-customer-reviews' ),
			'es'     => __( 'Spanish', 'wc-google-merchant-center-customer-reviews' ),
			'es-419' => __( 'Spanish (Latin America)', 'wc-google-merchant-center-customer-reviews' ),
			'fil'    => __( 'Filipino', 'wc-google-merchant-center-customer-reviews' ),
			'fr'     => __( 'French', 'wc-google-merchant-center-customer-reviews' ),
			'ga'     => __( 'Irish', 'wc-google-merchant-center-customer-reviews' ),
			'id'     => __( 'Indonesian', 'wc-google-merchant-center-customer-reviews' ),
			'it'     => __( 'Italian', 'wc-google-merchant-center-customer-reviews' ),
			'ja'     => __( 'Japanese', 'wc-google-merchant-center-customer-reviews' ),
			'ms'     => __( 'Malay', 'wc-google-merchant-center-customer-reviews' ),
			'nl'     => __( 'Dutch', 'wc-google-merchant-center-customer-reviews' ),
			'no'     => __( 'Norwegian', 'wc-google-merchant-center-customer-reviews' ),
			'pl'     => __( 'Polish', 'wc-google-merchant-center-customer-reviews' ),
			'pt_BR'  => __( 'Portuguese (Brazil)', 'wc-google-merchant-center-customer-reviews' ),
			'pt_PT'  => __( 'Portuguese (Portugal)', 'wc-google-merchant-center-customer-reviews' ),
			'ru'     => __( 'Russian', 'wc-google-merchant-center-customer-reviews' ),
			'sv'     => __( 'Swedish', 'wc-google-merchant-center-customer-reviews' ),
			'tr'     => __( 'Turkish', 'wc-google-merchant-center-customer-reviews' ),
			'zh-CN'  => __( 'Chinese (China)', 'wc-google-merchant-center-customer-reviews' ),
			'zh-TW'  => __( 'Chinese (Taiwan)', 'wc-google-merchant-center-customer-reviews' )
		);

		return $languages;
	}

	/**
	 * Get List of Popup Position
	 *
	 * @return Array position
	 */
	public function getPopupPosition() {
		$position = array(
			'CENTER_DIALOG'       => __( 'Center', 'wc-google-merchant-center-customer-reviews' ),
			'TOP_LEFT_DIALOG'     => __( 'Top Left', 'wc-google-merchant-center-customer-reviews' ),
			'TOP_RIGHT_DIALOG'    => __( 'Top Right', 'wc-google-merchant-center-customer-reviews' ),
			'BOTTOM_LEFT_DIALOG'  => __( 'Bottom Left', 'wc-google-merchant-center-customer-reviews' ),
			'BOTTOM_RIGHT_DIALOG' => __( 'Bottom Right', 'wc-google-merchant-center-customer-reviews' ),
			'BOTTOM_TRAY'         => __( 'Bottom Tray', 'wc-google-merchant-center-customer-reviews' )
		);

		return $position;
	}

	/**
	 * Santize our settings
	 * @see process_admin_options()
	 */
	public function sanitize_settings( $settings ) {
		// We're just going to make the api key all upper case characters since that's how our imaginary API works
		return $settings;
	}

	/**
	 * Validate the API key
	 * @see validate_settings_fields()
	 */
	public function validate_api_key_field( $key ) {
		// get the posted value
		$value = $_POST[ $this->plugin_id . $this->id . '_' . $key ];

		// check if the API key is longer than 20 characters. Our imaginary API doesn't create keys that large so something must be wrong. Throw an error which will prevent the user from saving.
		if ( isset( $value ) &&
		     20 < strlen( $value ) ) {
			$this->errors[] = $key;
		}

		return $value;
	}

	/**
	 * Display errors by overriding the display_errors() method
	 * @see display_errors()
	 */
	public function display_errors() {
		// loop through each error and display it
		foreach ( $this->errors as $key => $value ) {
			?>
			<div class="error">
				<p><?php _e( 'Looks like you made a mistake with the ' . $value . ' field. Make sure it isn&apos;t longer than 20 characters', 'woocommerce-integration-demo' ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Check Woocommerce class exits
	 *
	 * @uses get_option()
	 * @uses add_action()
	 */
	public function gmcCheck() {
		if ( class_exists( 'WooCommerce' ) ) {
			$gmc_settings_data = get_option( 'woocommerce_gmc-settings_settings' );
			if ( $gmc_settings_data['gmc_merchantId'] ) {
				add_action( 'woocommerce_thankyou', array( $this, 'gmcScript' ) );
			}
		}
	}

	/**
	 * Render GTIN field in product attribute
	 */
	public function gmcDoRenderGTINField() {

		$input = array(
			'id'          => '_gtin',
			'label'       => sprintf(
				'<abbr title="%1$s">%2$s</abbr>',
				_x( 'Global Trade Identification Number', 'field label', 'wc-google-merchant-center-customer-reviews' ),
				_x( 'GTIN', 'abbreviated field label', 'wc-google-merchant-center-customer-reviews' )
			),
			'value'       => get_post_meta( get_the_ID(), '_gtin', true ),
			'desc_tip'    => true,
			'description' => __( 'Enter the Global Trade Identification Number (UPC, EAN, ISBN, etc.)', 'wc-google-merchant-center-customer-reviews' ),
		);
		?>
		<div id="gtin_attr" class="options_group"> <?php woocommerce_wp_text_input( $input ); ?> </div> <?php

	}

	/**
	 * Save the product's GTIN number, if provided.
	 *
	 * @param int $product_id The ID of the product being saved.
	 */
	public function gmcSaveGTINField( $product_id ) {

		if ( ! isset( $_POST['_gtin'], $_POST['woocommerce_meta_nonce'] )
		     || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		     || ! current_user_can( 'edit_products' )
		     || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' )
		) {
			return;
		}
		$gtin = sanitize_text_field( $_POST['_gtin'] );
		update_post_meta( $product_id, '_gtin', $gtin );
	}

	/**
	 * Load Admin Resources
	 *
	 * @uses wp_enqueue_script()
	 */
	public function gmcLoadResource( $hook_suffix ) {
		if ( $hook_suffix == 'woocommerce_page_wc-settings' && $_REQUEST['page'] == 'wc-settings' && isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'integration' ) {
			if ( ! wp_script_is( 'gmc-custom', 'registered' ) ) {
				wp_register_script( 'gmc-custom', plugins_url( 'js/merchant-center.js', dirname( __FILE__ ) ), '', '1.0.1', true );
			}
			wp_enqueue_script( 'gmc-custom' );
		}
	}

	/**
	 * Load Google Merchant Review Script
	 */
	public function gmcScript( $order_id ) {
		$order             = new WC_Order( $order_id );
		$items             = $order->get_items();
		$gtins             = [];
		$gtin_field        = $this->gmcGetField();
		$gmc_settings_data = get_option( 'woocommerce_gmc-settings_settings' );

		if ( $gtin_field && $gtin_field != 'NO_GTIN' ) {
			foreach ( $items as $item ) {
				$product_id   = version_compare( WC_VERSION, '3.0', '<' ) ? $item['product_id'] : $item->get_product_id();
				$variation_id = version_compare( WC_VERSION, '3.0', '<' ) ? $item['variation_id'] : $item->get_variation_id();
				// Check if product has variation.
				if ( $variation_id ) {
					$item_id = $item['variation_id'];
				} else {
					$item_id = $item['product_id'];
				}
				$gtin = get_post_meta( $item_id, $gtin_field, true );
				if ( $gtin ) {
					$gtins[] = [ 'gtin' => sanitize_text_field( $gtin ) ];
				}
			}
		}
		?> <!-- BEGIN GCR Opt-in Module Code -->
		<script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer="defer"></script>
		<script>window.renderOptIn = function () {
				window.gapi.load('surveyoptin', function () {
					window.gapi.surveyoptin.render({
						"merchant_id": <?php echo $gmc_settings_data['gmc_merchantId']; ?>,
						"order_id": "<?php echo $order_id; ?>",
						"email": "<?php echo is_callable( array(
							$order,
							'get_billing_email'
						) ) ? $order->get_billing_email() : $order->billing_email; ?>",
						"delivery_country": "<?php echo is_callable( array(
							$order,
							'get_billing_country'
						) ) ? $order->get_billing_country() : $order->billing_country; ?>",
						"estimated_delivery_date": "<?php $order_date = is_callable( array(
							$order,
							'get_date_created'
						) ) ? $order->get_date_created() : $order->order_date;
							echo date( 'Y-m-d', strtotime( $order_date . ' + ' . (int) $gmc_settings_data['gmc_estimated_delivery'] . ' days' ) ); ?>",
						"opt_in_style": "<?php echo $gmc_settings_data['gmc_popup_position']; ?>", <?php if ( $gtins ) {
							echo '"products": ' . json_encode( $gtins );
						} ?> });
				});
			}</script><!-- END GCR Opt-in Module Code --><!-- BEGIN GCR Language Code -->
		<script>window.___gcfg = {
				lang: "<?php echo $gmc_settings_data['gmc_merchant_language']; ?>"
			};</script><!-- END GCR Language Code --> <?php
	}

	/**
	 * Get Options for GTIN Field
	 *
	 * @uses get_option()
	 * @return String gtin_field
	 */
	public function gmcGetField() {
		$gtin_field = get_option( 'gmc_field' );
		if ( ! $gtin_field ) {
			$gtin_field = '_gtin';
		}

		return $gtin_field;
	}

	/**
	 * Load Google Merchant Review Badge Script
	 *
	 * @uses get_option()
	 */
	public function gmcBadge() {
		$gmc_settings_data = get_option( 'woocommerce_gmc-settings_settings' );
		if ( $gmc_settings_data['gmc_badge_enable'] == 'yes' ) {
			if ( $gmc_settings_data['gmc_badge_position'] != 'none' ) {
				?> <!-- BEGIN GCR Badge Code -->
				<script src="https://apis.google.com/js/platform.js?onload=renderBadge" async defer="defer"></script>
				<script>window.renderBadge = function () {
						var ratingBadgeContainer = document.createElement("div");
						document.body.appendChild(ratingBadgeContainer);
						window.gapi.load('ratingbadge', function () {
							window.gapi.ratingbadge.render(ratingBadgeContainer, {
								"merchant_id": <?php echo $gmc_settings_data['gmc_merchantId']; ?>,
								"position": "<?php echo $gmc_settings_data['gmc_badge_position']; ?>"
							});
						});
					}</script><!-- END GCR Badge Code --><!-- BEGIN GCR Language Code -->
				<script>window.___gcfg = {
						lang: "<?php echo $gmc_settings_data['gmc_merchant_language']; ?>"
					};</script><!-- END GCR Language Code -->
				<?php
			}
		}
	}

}