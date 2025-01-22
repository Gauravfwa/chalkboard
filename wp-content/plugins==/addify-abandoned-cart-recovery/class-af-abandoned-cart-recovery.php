<?php
/**
 * Plugin Name:       Abandoned Cart Recovery
 * Plugin URI:        https://woocommerce.com/products/abandoned-cart-recovery/
 * Description:       Recover abandoned carts and pending Orders of your store and increase your sales.
 * Version:           1.1.0
 * Author:            Addify
 * Developed By:      Addify
 * Author URI:        http://www.addifypro.com
 * Support:           http://www.addifypro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Text Domain:       addify_acr
 * Woo: 6800056:7fd35c54d88ab6f118c332435fa61865
 * WC requires at least: 3.0.9
 * WC tested up to: 4.*.*
 *
 * @package addify-abandoned-cart-recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check the installation of WooCommerce module if it is not a multi site.
if ( ! is_multisite() ) {

	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

		/**
		 * Constructor of class.
		 */
		function afacr_admin_notice() {

			// Deactivate the plugin.
			deactivate_plugins( __FILE__ );

			$afpvu_woo_check = '<div id="message" class="error">
				<p><strong>' . __( 'WooCommerce Abandoned Cart Recovery plugin is inactive.', 'addify_acr' ) . '</strong> The <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce plugin</a> ' . __( 'must be active for this plugin to work. Please install &amp; activate WooCommerce.', 'addify_acr' ) . ' »</p></div>';
			echo wp_kses_post( $afpvu_woo_check );

		}

		add_action( 'admin_notices', 'afacr_admin_notice' );
	}
}

if ( ! class_exists( 'AF_Abandoned_Cart_Recovery' ) ) {

	/**
	 * Main class of Plugin
	 */
	class AF_Abandoned_Cart_Recovery {

		/**
		 * Constructor of class.
		 *
		 * @return void
		 */
		public function __construct() {

			$this->afacr_global_constents_vars();

			// Register the custom Post type.
			add_action( 'init', array( $this, 'afacr_register_post_type' ) );

			add_action( 'wp_loaded', array( $this, 'afacr_load_text_domain' ) );

			add_action( 'init', array( $this, 'afacr_schedule_cron_job' ) );

			if ( is_admin() ) {

				include_once AFACR_PLUGIN_DIR . '/includes/class-af-abandoned-cart-admin.php';

			} else {

				include_once AFACR_PLUGIN_DIR . '/includes/class-af-abandoned-cart-front.php';
			}

			include_once AFACR_PLUGIN_DIR . '/includes/class-af-email-content-controller.php';
			include_once AFACR_PLUGIN_DIR . '/includes/class-af-automatic-cron-jobs.php';
		}

		/**
		 * Load Text domain.
		 */
		public function afacr_load_text_domain() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'addify_acr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
			}
		}

		/**
		 * Constructor of class AF_Abandoned_Cart_Admin.
		 */
		public function afacr_schedule_cron_job() {

			if ( ! wp_next_scheduled( 'addify_automatic_emails' ) ) {
				wp_schedule_event( time() + 10, 'afacr_cron_schedule', 'addify_automatic_emails' );
			}

			if ( ! wp_next_scheduled( 'addify_consider_abanoned_cart' ) ) {
				wp_schedule_event( time() + 10, 'afacr_cron_schedule', 'addify_consider_abanoned_cart' );
			}

			if ( ! wp_next_scheduled( 'addify_automatic_delete' ) ) {
				wp_schedule_event( time() + 10, 'afacr_cron_schedule', 'addify_automatic_delete' );
			}
		}

		/**
		 * Constructor of class.
		 *
		 * @return void
		 */
		public function afacr_global_constents_vars() {

			if ( ! defined( 'AFACR_URL' ) ) {
				define( 'AFACR_URL', plugin_dir_url( __FILE__ ) );
			}

			if ( ! defined( 'AFACR_BASENAME' ) ) {
				define( 'AFACR_BASENAME', plugin_basename( __FILE__ ) );
			}

			if ( ! defined( 'AFACR_PLUGIN_DIR' ) ) {
				define( 'AFACR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
		}

		/**
		 * Register custom post type for extra fees.
		 *
		 * @return void
		 */
		public function afacr_register_post_type() {

			$labels1 = array(
				'name'                => esc_html__( 'Email Templates', 'addify_acr' ),
				'singular_name'       => esc_html__( 'Email Template', 'addify_acr' ),
				'add_new'             => esc_html__( 'Add New Template', 'addify_acr' ),
				'add_new_item'        => esc_html__( 'Add New Template', 'addify_acr' ),
				'edit_item'           => esc_html__( 'Edit Template', 'addify_acr' ),
				'new_item'            => esc_html__( 'New Template', 'addify_acr' ),
				'view_item'           => esc_html__( 'View Template', 'addify_acr' ),
				'search_items'        => esc_html__( 'Search Template', 'addify_acr' ),
				'exclude_from_search' => true,
				'not_found'           => esc_html__( 'No Email Template found', 'addify_acr' ),
				'not_found_in_trash'  => esc_html__( 'No Email Template found in trash', 'addify_acr' ),
				'parent_item_colon'   => '',
				'all_items'           => esc_html__( 'All Email Templates', 'addify_acr' ),
				'menu_name'           => esc_html__( 'Email Templates', 'addify_acr' ),
			);

			$args1 = array(
				'labels'             => $labels1,
				'menu_icon'          => plugins_url( 'assets/images/set.png', __FILE__ ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'query_var'          => true,
				'rewrite'            => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 30,
				'rewrite'            => array(
					'slug'       => 'addify_acr_emails',
					'with_front' => false,
				),
				'supports'           => array( 'title', 'editor' ),
			);

			register_post_type( 'addify_acr_emails', $args1 );

			$labels1 = array(
				'name'                => esc_html__( 'Abandoned Carts', 'addify_acr' ),
				'singular_name'       => esc_html__( 'Abandoned Cart', 'addify_acr' ),
				'add_new'             => esc_html__( 'Add New Cart', 'addify_acr' ),
				'add_new_item'        => esc_html__( 'Add New Cart', 'addify_acr' ),
				'edit_item'           => esc_html__( 'Edit Cart', 'addify_acr' ),
				'new_item'            => esc_html__( 'New Cart', 'addify_acr' ),
				'view_item'           => esc_html__( 'View Cart', 'addify_acr' ),
				'search_items'        => esc_html__( 'Search Cart', 'addify_acr' ),
				'exclude_from_search' => true,
				'not_found'           => esc_html__( 'No abandoned cart found', 'addify_acr' ),
				'not_found_in_trash'  => esc_html__( 'No abandoned cart found in trash', 'addify_acr' ),
				'parent_item_colon'   => '',
				'all_items'           => esc_html__( 'All abandoned carts', 'addify_acr' ),
				'menu_name'           => esc_html__( 'Abandoned Carts', 'addify_acr' ),
			);

			$args1 = array(
				'labels'             => $labels1,
				'menu_icon'          => '',
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'query_var'          => true,
				'rewrite'            => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 30,
				'rewrite'            => array(
					'slug'       => 'addify_acr_carts',
					'with_front' => false,
				),
				'supports'           => array( 'title' ),
			);

			register_post_type( 'addify_acr_carts', $args1 );

			$labels1 = array(
				'name'                => esc_html__( 'Abandoned Cart Emails Logs', 'addify_acr' ),
				'singular_name'       => esc_html__( 'Abandoned Cart Emails Log', 'addify_acr' ),
				'add_new'             => '',
				'add_new_item'        => '',
				'edit_item'           => esc_html__( 'Edit Log', 'addify_acr' ),
				'new_item'            => esc_html__( 'New Log', 'addify_acr' ),
				'view_item'           => esc_html__( 'View Log', 'addify_acr' ),
				'search_items'        => esc_html__( 'Search Log', 'addify_acr' ),
				'exclude_from_search' => true,
				'not_found'           => esc_html__( 'No Email log found', 'addify_acr' ),
				'not_found_in_trash'  => esc_html__( 'No Email log found in trash', 'addify_acr' ),
				'parent_item_colon'   => '',
				'all_items'           => esc_html__( 'All Email Logs', 'addify_acr' ),
				'menu_name'           => esc_html__( 'Email Logs', 'addify_acr' ),
			);

			$args1 = array(
				'labels'             => $labels1,
				'menu_icon'          => '',
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'query_var'          => true,
				'rewrite'            => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 30,
				'rewrite'            => array(
					'slug'       => 'addify_acr_logs',
					'with_front' => false,
				),
				'supports'           => array( 'title' ),
			);
			register_post_type( 'addify_acr_logs', $args1 );
		}

	}

	new AF_Abandoned_Cart_Recovery();
}
