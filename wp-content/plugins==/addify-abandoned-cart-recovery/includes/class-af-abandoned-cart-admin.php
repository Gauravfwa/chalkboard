<?php
/**
 * Admin file of Module
 *
 * Manage all settings and actions of admin
 *
 * @package  addify-abandoned-cart-recovery/includes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AF_Abandoned_Cart_Admin' ) ) {
	/**
	 * Admin class of module.
	 */
	class AF_Abandoned_Cart_Admin {

		/**
		 * Store All Abandoned Carts
		 *
		 * @var array
		 */
		private $abandoned_carts;

		/**
		 * Store All Abandoned Carts
		 *
		 * @var array
		 */
		private $recovered_carts;

		/**
		 * Constructor of class AF_Abandoned_Cart_Admin.
		 */
		public function __construct() {

			add_action( 'admin_menu', array( $this, 'afacr_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'afacr_add_setting_files' ), 10 );
			add_action( 'admin_init', array( $this, 'afacr_add_tinymce_plugin' ), 10 );
			add_action( 'add_meta_boxes', array( $this, 'afacr_add_metaboxes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'afacr_admin_assests' ), 10 );
			add_action( 'save_post', array( $this, 'addify_adacr_save_metadata' ), 10, 2 );

			// Count Pending Order.
			add_action( 'woocommerce_order_status_changed', array( $this, 'afacr_count_orders' ), 10, 4 );

			// Admin all templates Table columns.
			add_filter( 'manage_addify_acr_emails_posts_columns', array( $this, 'addify_a_c_r_custom_columns' ) );
			add_action( 'manage_addify_acr_emails_posts_custom_column', array( $this, 'addify_a_c_r_custom_columns_value' ), 10, 2 );

		}

		/**
		 * Order status change call back.
		 *
		 * @param int    $order_id Order id.
		 * @param string $old_status old status of array.
		 * @param string $new_status New status of array.
		 * @param object $order Order object.
		 *
		 * @return void
		 */
		public function afacr_count_orders( $order_id = 0, $old_status = '', $new_status = '', $order = '' ) {

			if ( ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( ! is_admin() ) {
				return;
			}

			if ( 'pending' === $old_status && in_array( (string) $new_status, array( 'on-hold', 'processing', 'completed' ), true ) ) {

				$total_orders = intval( get_option( 'afacr_total_recoverd_orders' ) ) + 1;

				update_option( 'afacr_total_recoverd_orders', $total_orders );

				$order_total = 0 !== $order->get_total() ? $order->get_total() : $order->get_subtotal();

				$total_orders = floatval( get_option( 'afacr_total_recovered_orders_amount' ) ) + $order_total;

				update_option( 'afacr_total_recovered_orders_amount', $total_orders );

			}
		}

		/**
		 * Add hooks for MCE Plugin.
		 *
		 * @return void
		 */
		public function afacr_add_tinymce_plugin() {
			// Register MCE Button.
			add_filter( 'mce_buttons', array( $this, 'afacr_tinymce_plugin_register_buttons' ) );
			// Load the TinyMCE plugin.
			add_filter( 'mce_external_plugins', array( $this, 'afacr_plugin_register_tinymce_javascript' ) );
		}

		/**
		 * Enqueue admin assets.
		 *
		 * @param array $plugin_array Timymce plugins array.
		 *
		 * @return array
		 */
		public function afacr_plugin_register_tinymce_javascript( $plugin_array ) {

			$screen = get_current_screen();

			if ( 'addify_acr_emails' !== $screen->post_type ) {
				return $plugin_array;
			}

			$plugin_array['acr_cart'] = AFACR_URL . 'assets/js/tiny-mice-acr-cart-plugin/tiny-mice-button.js';
			return $plugin_array;
		}

		/**
		 * Enqueue admin assets.
		 *
		 * @param array $buttons Buttons.
		 *
		 * @return array
		 */
		public function afacr_tinymce_plugin_register_buttons( $buttons ) {

			$screen = get_current_screen();

			if ( 'addify_acr_emails' !== $screen->post_type ) {
				return $buttons;
			}

			array_push( $buttons, 'acr_cart' );
			return $buttons;
		}



		/**
		 * Enqueue admin assets.
		 *
		 * @return void
		 */
		public function afacr_admin_assests() {

			// Enqueue Styles.
			wp_enqueue_style( 'adcod-admin', AFACR_URL . 'assets/css/afacr-admin.css', false, '1.0' );

			// // Enqueue Scripts.
			wp_enqueue_script( 'jquery' );

			wp_enqueue_script( 'afacr-admin', AFACR_URL . 'assets/js/admin.js', array( 'jquery' ), '1.0', false );

			wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css', false, '4.0.3' );
			wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery' ), '4.0.3', false );

			if ( isset( $_REQUEST['_afacr_wpnonce'] ) && ! wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['_afacr_wpnonce'] ) ) ), '_afacr_wpnonce' ) ) {
				die( 'Nonce not verified.' );
			}

			if ( isset( $_REQUEST['page'] ) && 'addify_acr_menu' === $_REQUEST['page'] && ( ! isset( $_REQUEST['tab'] ) || 'dashboard' === $_REQUEST['tab'] ) ) {
				wp_enqueue_script( 'afacr-charts', AFACR_URL . 'assets/js/google-charts.js', array( 'jquery' ), '1.0', false );
				wp_enqueue_script( 'google-charts', 'https://www.gstatic.com/charts/loader.js', array( 'jquery' ), '1.0', false );

				$afacr_monthly_report = $this->get_monthly_report();

				$afacr_var = array(
					'total_abandoned_carts'  => get_option( 'afacr_total_abandoned_carts' ),
					'total_recovered_carts'  => get_option( 'afacr_total_recovered_carts' ),
					'total_abandoned_amount' => get_option( 'afacr_total_abandoned_cart_amount' ),
					'total_recovered_amount' => get_option( 'afacr_total_recovered_cart_amount' ),
					'monthly_report'         => $afacr_monthly_report,
				);
				wp_localize_script( 'afacr-charts', 'afacr_var', $afacr_var );
			}
		}

		/**
		 * Get monthly report of abandoned and recovered carts.
		 *
		 * @return string
		 */
		public function get_monthly_report() {

			$this->afacr_load_carts();

			$montly_report[] = array( 'Days', 'Recovered', 'Abandoned' );

			for ( $i = 30; $i >= 0; $i-- ) {

				$report_date = gmdate( 'Y-m-d', strtotime( '-' . $i . ' days' ) );

				$abandoned_count = 0;

				foreach ( $this->abandoned_carts as $cart_id ) {

					$post = get_post( $cart_id );

					$post_date = date_format( date_create( $post->post_date ), 'Y-m-d' );

					if ( $report_date === $post_date ) {
						$abandoned_count++;
					}
				}

				$recovered_count = 0;

				foreach ( $this->recovered_carts as $cart_id ) {

					$post = get_post( $cart_id );

					$post_date = date_format( date_create( $post->post_date ), 'Y-m-d' );

					if ( $report_date === $post_date ) {
						$recovered_count++;
					}
				}

				if ( 0 !== $abandoned_count || 0 !== $recovered_count ) {

					$montly_report[] = array( date_format( date_create( $report_date ), 'M d,Y' ), $recovered_count, $abandoned_count );
				}
			}

			return wp_json_encode( $montly_report );
		}

		/**
		 * Load all abandoned carts.
		 */
		public function afacr_load_carts() {

			// Load all abandoned carts.
			$args = array(
				'post_type'      => 'addify_acr_carts',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cart_status',
						'value'   => 'abandoned',
						'compare' => '=',
					),
				),
				'date_query'     => array(
					array(
						'after'   => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
						'include' => true,
					),
				),
				'order_by'       => 'post_date',
				'fields'         => 'ids',
			);

			$the_query             = new WP_Query( $args );
			$this->abandoned_carts = $the_query->get_posts();

			// Load all recovered carts.
			$args = array(
				'post_type'      => 'addify_acr_carts',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cart_status',
						'value'   => 'recovered',
						'compare' => '=',
					),
				),
				'date_query'     => array(
					array(
						'after' => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
					),
				),
				'order_by'       => 'post_date',
				'fields'         => 'ids',
			);

			$the_query             = new WP_Query( $args );
			$this->recovered_carts = $the_query->get_posts();
		}

		/**
		 * Save Post meta for extra fees.
		 *
		 * @param int     $post_id post id of current post.
		 * @param WP_Post $post    post object.
		 *
		 * @return void
		 */
		public function addify_adacr_save_metadata( $post_id, $post = false ) {

			// Return if not relevant post type.
			if ( 'addify_acr_emails' !== $post->post_type ) {
				return;
			}

			// Return if we're doing an auto save.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( 'auto-draft' === get_post_status( $post_id ) ) {
				return;
			}

			include_once AFACR_PLUGIN_DIR . '/includes/meta-boxes/save-metaboxes.php';
		}

		/**
		 * Add columns in all fees table.
		 *
		 * @param array $columns Array of columns of  table.
		 *
		 * @return array       Array of columns.
		 */
		public function addify_a_c_r_custom_columns( $columns ) {
			unset( $columns['date'] );
			$columns['active']        = esc_html__( 'Active', 'addify_acr' );
			$columns['email-type']    = esc_html__( 'Email Type', 'addify_acr' );
			$columns['email-subject'] = esc_html__( 'Email Subject', 'addify_acr' );
			$columns['automatic']     = esc_html__( 'Automatic Time', 'addify_acr' );
			$columns['coupon-value']  = esc_html__( 'Coupons Value', 'addify_acr' );
			$columns['coupon-type']   = esc_html__( 'Coupons Type', 'addify_acr' );
			$columns['date']          = esc_html__( 'Date', 'addify_acr' );
			return $columns;
		}

		/**
		 * Add columns in all fees table.
		 *
		 * @param string $column  Column name of table.
		 * @param int    $post_id Post id of current row.
		 *
		 * @return void
		 */
		public function addify_a_c_r_custom_columns_value( $column, $post_id ) {

			switch ( $column ) {

				case 'active':
					echo esc_attr( get_post_meta( $post_id, 'afacr_enable', true ) );
					break;

				case 'email-type':
					echo esc_attr( get_post_meta( $post_id, 'afacr_email_type', true ) );
					break;

				case 'email-subject':
					echo esc_attr( get_post_meta( $post_id, 'afacr_email_subject', true ) );
					break;

				case 'automatic':
					$time_and_type = json_decode( get_post_meta( $post_id, 'afacr_time', true ) );
					$automatic     = get_post_meta( $post_id, 'afacr_automatic', true );

					if ( 'yes' !== $automatic ) {

						echo esc_html__( 'No', 'addify_acr' );
						break;

					} else {

						$time      = isset( $time_and_type[0] ) ? $time_and_type[0] : 0;
						$time_type = isset( $time_and_type[1] ) ? $time_and_type[1] : 'minutes';

						echo esc_html__( 'Yes, after ', 'addify_acr' ) . intval( $time ) . ' ' . esc_attr( $time_type );
						break;

					}

				case 'coupon-value':
					if ( 'cart' === get_post_meta( $post_id, 'afacr_email_type', true ) ) {
						echo esc_attr( get_post_meta( $post_id, 'afacr_coupon_value', true ) );
					} else {
						echo '';
					}
					break;

				case 'coupon-type':
					if ( 'cart' === get_post_meta( $post_id, 'afacr_email_type', true ) ) {
						echo esc_attr( get_post_meta( $post_id, 'afacr_coupon_type', true ) );
					} else {
						echo '';
					}

					break;
				default:
					break;
			}

		}

		/**
		 * Add menu of module.
		 */
		public function afacr_add_metaboxes() {
			add_meta_box(
				'email-settings',
				__( 'Email Settings', 'addify-acr' ),
				array( $this, 'afacr_email_metabox_callback' ),
				'addify_acr_emails'
			);

			add_meta_box(
				'coupons-settings',
				__( 'Coupons Settings', 'addify-acr' ),
				array( $this, 'afacr_coupons_metabox_callback' ),
				'addify_acr_emails'
			);
		}

		/**
		 * Add menu of module.
		 */
		public function afacr_email_metabox_callback() {
			include_once AFACR_PLUGIN_DIR . '/includes/meta-boxes/email-settings.php';
		}

		/**
		 * Add menu of module.
		 */
		public function afacr_coupons_metabox_callback() {
			include_once AFACR_PLUGIN_DIR . '/includes/meta-boxes/coupons-settings.php';
		}


		/**
		 * Add menu of module.
		 */
		public function afacr_add_setting_files() {

			include_once AFACR_PLUGIN_DIR . '/includes/settings/general.php';
			include_once AFACR_PLUGIN_DIR . '/includes/settings/pending-order.php';
			include_once AFACR_PLUGIN_DIR . '/includes/settings/user-settings.php';
			include_once AFACR_PLUGIN_DIR . '/includes/settings/cron-settings.php';
			include_once AFACR_PLUGIN_DIR . '/includes/settings/coupons.php';
		}

		/**
		 * Add menu of module.
		 */
		public function afacr_admin_menu() {
			add_menu_page(
				esc_html__( 'Abandoned Cart Recovery Statistics', 'addify_acr' ), // Page title.
				esc_html__( 'Cart Recovery', 'addify_acr' ), // Menu title.
				'manage_options', // Capability.
				'addify_acr_menu',  // Menu-slug.
				array( $this, 'afacr_menu_callback' ),   // Function that will render its output.
				plugins_url( '../assets/img/grey.png', __FILE__ ),   // Link to the icon that will be displayed in the sidebar.
				25    // Position of the menu option.
			);

			// Add sub menu page in Cart Recovery menu.
			add_submenu_page(
				'addify_acr_menu',
				__( 'Email Templates', 'addify_acr' ),
				__( 'Automatic Emails', 'addify_acr' ),
				'manage_options',
				'edit.php?post_type=addify_acr_emails',
				'',
				5
			);

			// Add sub menu page in Cart Recovery menu.
			add_submenu_page(
				'addify_acr_menu',
				__( 'Abandoned Cart Settings', 'addify_acr' ),
				__( 'Settings', 'addify_acr' ),
				'manage_options',
				'addify_acr_settings',
				array( $this, 'addify_acr_settings_callback' ),
				5
			);
		}

		/**
		 * Addify Cart Recovery statistics Page.
		 *
		 * @return void
		 */
		public function addify_acr_settings_callback() {

			$_nonce = isset( $_POST['afacr_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['afacr_nonce_field'] ) ) : 0;

			if ( isset( $_POST['afacr_nonce_field'] ) && ! wp_verify_nonce( $_nonce, 'afacr_nonce_action' ) ) {
				die( 'Failed Security Check' );
			}

			if ( isset( $_GET['tab'] ) ) {
				$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			} else {
				$active_tab = 'general';
			}

			?>
			<div class="addify-cod-settings">
				<div class="wrap woocommerce">
					<h2><?php echo esc_html__( 'Abandoned Cart Recovery Settings', 'addify_acr' ); ?></h2>
					<?php settings_errors(); ?> 
					<h2 class="nav-tab-wrapper">
						<a href="?page=addify_acr_settings&tab=general" class="nav-tab <?php echo esc_attr( $active_tab ) === 'general' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'General', 'addify_acr' ); ?>
						</a>
						<a href="?page=addify_acr_settings&tab=pending_order" class="nav-tab <?php echo esc_attr( $active_tab ) === 'pending_order' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Pending Orders', 'addify_acr' ); ?>
						</a>
						<a href="?page=addify_acr_settings&tab=user-setting" class="nav-tab <?php echo esc_attr( $active_tab ) === 'user-setting' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Users Settings', 'addify_acr' ); ?>
						</a>
						<a href="?page=addify_acr_settings&tab=coupons" class="nav-tab <?php echo esc_attr( $active_tab ) === 'coupons' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Coupons Settings', 'addify_acr' ); ?>
						</a>
						<a href="?page=addify_acr_settings&tab=cron" class="nav-tab <?php echo esc_attr( $active_tab ) === 'cron' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Cron Job Settings', 'addify_acr' ); ?>
						</a>
					</h2>
				</div>
				<form method="post" action="options.php" class="afacr_options_form"> 	
				<?php

				if ( 'general' === $active_tab ) {

					settings_fields( 'afacr_general_setting_fields' );
					do_settings_sections( 'afacr_general_setting_section' );

				} elseif ( 'pending_order' === $active_tab ) {

					settings_fields( 'afacr_pending_order_fields' );
					do_settings_sections( 'afacr_pending_order_section' );

				} elseif ( 'user-setting' === $active_tab ) {

					settings_fields( 'afacr_user_setting_fields' );
					do_settings_sections( 'afacr_user_setting_section' );

				} elseif ( 'admin-email' === $active_tab ) {

					settings_fields( 'afacr_admin_email_fields' );
					do_settings_sections( 'afacr_admin_email_section' );

				} elseif ( 'coupons' === $active_tab ) {

					settings_fields( 'afacr_coupon_fields' );
					do_settings_sections( 'afacr_coupon_section' );

				} elseif ( 'cron' === $active_tab ) {

					settings_fields( 'afacr_cron_fields' );
					do_settings_sections( 'afacr_cron_section' );

				}
					submit_button();
				?>
				</form>	
			</div>
			<?php
		}

		/**
		 * Addify Cart Recovery setting Page.
		 *
		 * @return void
		 */
		public function afacr_menu_callback() {

			$_nonce = isset( $_POST['afacr_nonce_field'] ) ? sanitize_text_field( wp_unslash( $_POST['afacr_nonce_field'] ) ) : 0;

			if ( isset( $_POST['afacr_nonce_field'] ) && ! wp_verify_nonce( $_nonce, 'afacr_nonce_action' ) ) {
				die( 'Failed Security Check' );
			}

			if ( isset( $_GET['tab'] ) ) {
				$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
			} else {
				$active_tab = 'dashboard';
			}

			?>
			<div class="addify-cod-settings">
				<div class="wrap woocommerce">
					<h2><?php echo esc_html__( 'Abandoned Cart Recovery Statistics', 'addify_acr' ); ?></h2>
					<?php settings_errors(); ?> 
					<h2 class="nav-tab-wrapper">
						<a href="?page=addify_acr_menu&tab=dashboard" class="nav-tab <?php echo esc_attr( $active_tab ) === 'dashboard' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Dashboard', 'addify_acr' ); ?>
						</a>
						<a href="?page=addify_acr_menu&tab=carts" class="nav-tab <?php echo esc_attr( $active_tab ) === 'carts' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Abandoned Carts', 'addify_acr' ); ?>
						</a>
						<?php if ( 'yes' === get_option( 'afacr_enable_pending_order' ) ) { ?>
							<a href="?page=addify_acr_menu&tab=orders" class="nav-tab <?php echo esc_attr( $active_tab ) === 'orders' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Pending Orders', 'addify_acr' ); ?>
							</a>
						<?php } ?>
						<a href="?page=addify_acr_menu&tab=recovered-carts" class="nav-tab <?php echo esc_attr( $active_tab ) === 'recovered-carts' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Recovered Carts', 'addify_acr' ); ?>
						</a>
						<a href="?page=addify_acr_menu&tab=email-logs" class="nav-tab <?php echo esc_attr( $active_tab ) === 'email-logs' ? 'nav-tab-active' : ''; ?>"><?php echo esc_html__( 'Email Logs', 'addify_acr' ); ?>
						</a>
					</h2>
				</div>
				<?php

				if ( 'dashboard' === $active_tab ) {

					include_once AFACR_PLUGIN_DIR . '/includes/settings/reports/dashboard.php';

				} elseif ( 'carts' === $active_tab ) {

					if ( isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) && ! empty( $_GET['id'] ) ) {
						$cart_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
						include_once AFACR_PLUGIN_DIR . '/templates/admin/carts.php';
					} else {

						if ( isset( $_GET['send_email'] ) && isset( $_GET['cart_id'] ) ) {

							$email_controller = new AF_Email_Content_Controller();

							$template_id = sanitize_text_field( wp_unslash( $_GET['send_email'] ) );
							$cart_id     = sanitize_text_field( wp_unslash( $_GET['cart_id'] ) );

							if ( $email_controller->send_email_for_cart( $template_id, $cart_id ) ) {
								echo wp_kses_post( '<div id="message" class="updated notice notice-success is-dismissible"><p>Email sent successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
							}
						}

						include_once AFACR_PLUGIN_DIR . '/includes/settings/reports/abandoned-cart.php';
					}
				} elseif ( 'recovered-carts' === $active_tab ) {
					if ( isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) && ! empty( $_GET['id'] ) ) {
						$cart_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
						include_once AFACR_PLUGIN_DIR . '/templates/admin/carts.php';
					} else {
						include_once AFACR_PLUGIN_DIR . '/includes/settings/reports/recovered-cart.php';
					}
				} elseif ( 'orders' === $active_tab ) {

					if ( isset( $_GET['action'] ) && 'edit' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) && ! empty( $_GET['id'] ) ) {
						$cart_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
						include_once AFACR_PLUGIN_DIR . '/templates/admin/carts.php';
					} else {

						if ( isset( $_GET['send_email'] ) && isset( $_GET['cart_id'] ) ) {

							$email_controller = new AF_Email_Content_Controller();

							$template_id = sanitize_text_field( wp_unslash( $_GET['send_email'] ) );
							$cart_id     = sanitize_text_field( wp_unslash( $_GET['cart_id'] ) );

							if ( $email_controller->send_email_for_order( $template_id, $cart_id ) ) {
								echo wp_kses_post( '<div id="message" class="updated notice notice-success is-dismissible"><p>Email sent successfully.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>' );
							}
						}

						include_once AFACR_PLUGIN_DIR . '/includes/settings/reports/pending-orders.php';
					}
				} elseif ( 'email-logs' === $active_tab ) {

					include_once AFACR_PLUGIN_DIR . '/includes/settings/reports/emails-logs.php';

				}
				?>
			</div>
			<?php
		}
	}

	new AF_Abandoned_Cart_Admin();
}
