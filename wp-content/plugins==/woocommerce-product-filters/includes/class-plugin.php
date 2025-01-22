<?php

namespace WooCommerce_Product_Filter_Plugin;

/**
 * Class Plugin
 *
 * @package WooCommerce_Product_Filter_Plugin
 */
class Plugin {
	protected $component_register;

	protected $object_register;

	protected $entity_register;

	public function __construct() {
		$this->includes();

		$this->attach_hooks();

		$this->load_components();
	}

	protected function includes() {
		include_once __DIR__ . '/class-autoloader.php';

		$autoloader = new Autoloader();

		$autoloader->register();

		$this->component_register = new Register();

		$this->object_register = new Register();

		$this->entity_register = new Entity_Register();
	}

	/**
	 * Attach relevant hooks.
	 */
	protected function attach_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

		add_action( 'init', array( $this, 'on_wp_init' ) );

		add_action( 'widgets_init', array( $this, 'on_widgets_init' ) );

		add_action( 'plugins_loaded', array( $this, 'plugin_support' ) );

		add_action( 'after_setup_theme', array( $this, 'theme_support' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( WC_PRODUCT_FILTER_PLUGIN_FILE ), [ $this, 'plugin_action_links' ] );

	}

	protected function is_active_woocommerce() {
		$woocommerce = 'woocommerce/woocommerce.php';

		$network_plugins = array();

		if ( is_multisite() ) {
			$network_plugins = get_site_option( 'active_sitewide_plugins' );
		}

		$is_active = in_array( $woocommerce, (array) get_option( 'active_plugins', array() ), true );

		$is_active_for_network = is_multisite() && isset( $network_plugins[ $woocommerce ] );

		return $is_active || $is_active_for_network || defined( 'WP_TESTS_DOMAIN' );
	}

	public function theme_support() {
		$component = null;

		if ( defined( 'THE7_VERSION' ) && defined( 'PRESSCORE_THEME_NAME' ) && PRESSCORE_THEME_NAME === 'the7' ) {
			$component = Theme_Support\The7::class;
		}

		if ( $this->is_active_woocommerce() && $component ) {
			$component_builder = $this->get_object_register()->get( 'Component_Builder' );

			$component_builder->build( $component );
		}
	}

	public function plugin_support() {
		$components = array();

		if ( class_exists( 'Polylang' ) ) {
			$components[] = Plugin_Support\Polylang::class;
		}

		if ( defined( 'WPML_PLUGIN_PATH' ) ) {
			$components[] = Plugin_Support\Wpml::class;
		}

		if ( class_exists( 'Elementor\\Plugin' ) ) {
			$components[] = Plugin_Support\Elementor::class;
		}

		if ( class_exists( 'Jetpack' ) ) {
			$components[] = Plugin_Support\Jetpack::class;
		}

		if ( class_exists( 'BeRocket_LMP' ) ) {
			$components[] = Plugin_Support\BeRocket_Load_More_Products::class;
		}

		if ( defined( 'GUAVEN_WOO_SEARCH_PLUGIN_PATH' ) ) {
			$components[] = Plugin_Support\Guaven_Woo_Search::class;
		}

		if ( defined( 'YITH_INFS' ) ) {
			$components[] = Plugin_Support\Yith_Infinite_Scrolling::class;
		}

		if ( $this->is_active_woocommerce() ) {
			$component_builder = $this->get_object_register()->get( 'Component_Builder' );

			foreach ( $components as $component ) {
				$component_builder->build( $component );
			}
		}
	}

	protected function load_components() {
		$component_builder = new Structure\Component_Builder();

		$component_builder->set_plugin( $this );

		$this->get_object_register()->save( 'Component_Builder', $component_builder );

		if ( $this->is_active_woocommerce() ) {
			foreach ( $this->get_components() as $component ) {
				$component_builder->build( $component );
			}
		}
	}

	public function get_component_register() {
		return $this->component_register;
	}

	public function get_object_register() {
		return $this->object_register;
	}

	public function get_entity_register() {
		return $this->entity_register;
	}

	public function load_text_domain() {
		load_plugin_textdomain( WC_PRODUCT_FILTER_INDEX, false, dirname( plugin_basename( WC_PRODUCT_FILTER_PLUGIN_FILE ) ) . '/languages/' );
	}

	public function get_plugin_url() {
		return plugin_dir_url( WC_PRODUCT_FILTER_PLUGIN_FILE );
	}

	public function get_plugin_path() {
		return plugin_dir_path( WC_PRODUCT_FILTER_PLUGIN_FILE );
	}

	public function is_debug_mode() {
		return WP_DEBUG || isset( $_GET[ WC_PRODUCT_FILTER_INDEX . '_debug_mode' ] );
	}

	public function get_resource_url() {
		return $this->get_plugin_url() . 'assets/';
	}

	public function get_assets_url() {
		return $this->get_plugin_url() . 'assets/';
	}

	public function get_assets_path() {
		return $this->get_plugin_path() . 'assets/';
	}

	public function get_plugin_uri() {
		return 'woocommerce-product-filter';
	}

	public function on_widgets_init() {
		if ( defined( 'WC_PLUGIN_FILE' ) || defined( 'WP_TESTS_DOMAIN' ) ) {
			register_widget( Widgets\Filter_Widget::class );

			register_widget( Widgets\Filter_Notes_Widget::class );
		}
	}

	public function on_wp_init() {
		do_action( 'wcpf_register_entities', $this->get_entity_register() );
	}


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 * @since x.x.x
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ): array {
		$settings_url = add_query_arg(
			[
				'page'    => 'wc-settings',
				'tab'     => 'products',
				'section' => 'wcpf',
			],
			admin_url( 'admin.php' )
		);
		$action_links = [
			'settings' => '<a href="' . $settings_url . '">' . esc_html__( 'Settings', 'wcpf' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}

	protected function get_components() {
		$components = array(
			Template_Loader::class,
			Query_Helper::class,
			Activator::class,
			Filters::class,
			Filter\Component_Builder::class,
			Project\Filter_Component_Storage::class,
			Project\Post_Type::class,
			Project\Registration::class,
			Front\Assets::class,
			Front\Product_Loop::class,
			Shortcode::class,
			Field\Common::class,
			Layout\Common::class,
			Woocommerce::class,
		);

		if ( is_admin() ) {
			$components = array_merge(
				array(
					Admin\Page\List_Page::class,
					Admin\Page\Edit_Page::class,
					Admin\Page\New_Page::class,
					Admin\Page\Loader::class,
					Admin\WC_Settings::class,
					Admin\Tools::class,
					Admin\Assets::class,
					Admin\Ajax::class,
					Admin\Editor\Element_Panel\Panel::class,
				),
				$components
			);
		}

		return $components;
	}
}
