<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Elementor extends Component {

	const MINIMUM_ELEMENTOR_VERSION = '3.4.0';

	public function init() {
		add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
	}

	public function on_plugins_loaded() {
		if ( $this->is_compatible() ) {
			add_action( 'elementor/init', [ $this, 'on_elementor_init' ] );
		}
	}

	public function is_compatible() {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			return false;
		}

		return true;
	}


	public function on_elementor_init() {
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );
	}

	public function init_widgets() {
		// Include Widget files
		require_once __DIR__ . '/class-playht-widget.php';

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Playht_Widget() );
	}
}
