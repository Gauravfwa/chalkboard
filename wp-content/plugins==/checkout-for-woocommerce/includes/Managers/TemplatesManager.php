<?php

namespace Objectiv\Plugins\Checkout\Managers;

use Objectiv\Plugins\Checkout\Core\Template;
use Symfony\Component\Finder\Finder;

/**
 * The templates manager loads the active template
 * as well as provides information on all available templates
 *
 * @link checkoutwc.com
 * @since 2.31.0
 * @package Objectiv\Plugins\Checkout\Managers
 * @author Clifton Griffin <clif@checkoutwc.com>
 */

class TemplatesManager {
	private $_active_template;
	public $path_manager;

	/**
	 * TemplatesManager constructor.
	 *
	 * @param ExtendedPathManager $path_manager
	 * @param string $active_template_slug
	 */
	public function __construct( ExtendedPathManager $path_manager, string $active_template_slug ) {
		$this->_active_template = new Template( $active_template_slug, $path_manager );
		$this->path_manager     = $path_manager;

		add_action( 'cfw_load_template_assets', array( $this, 'enqueue_assets' ) );
		$this->get_active_template()->load_functions();
	}

	function enqueue_assets() {
		$min = ( ! CFW_DEV_MODE ) ? '.min' : '';

		wp_enqueue_style( 'cfw_front_template_css', $this->get_active_template()->get_template_uri() . "/style{$min}.css", array(), $this->get_active_template()->get_version() );
		wp_enqueue_script( 'wc-checkout', $this->get_active_template()->get_template_uri() . "/theme{$min}.js", array( 'jquery' ), $this->get_active_template()->get_version(), true );
	}

	function get_user_template_path(): string {
		return get_stylesheet_directory() . '/checkout-wc';
	}

	function get_plugin_template_path(): string {
		return $this->path_manager->get_base() . '/templates';
	}

	/**
	 * @return Template[]
	 */
	function get_available_templates(): array {
		$templates = array();
		$finder    = new Finder();

		$finder->directories()->depth( 0 )->in( $this->get_plugin_template_path() );

		foreach ( $finder as $template ) {
			$templates[ $template->getBasename() ] = new Template( $template->getBasename(), $this->path_manager );
		}

		if ( is_dir( $this->get_user_template_path() ) ) {
			$finder = new Finder();
			$finder->directories()->depth( 0 )->in( $this->get_user_template_path() );

			foreach ( $finder as $template ) {
				$templates[ $template->getBasename() ] = new Template( $template->getBasename(), $this->path_manager );
			}
		}

		return $templates;
	}

	/**
	 * @return Template
	 */
	public function get_active_template(): Template {
		return $this->_active_template;
	}

	/**
	 * @param Template $activate_template
	 */
	public function set_active_template( Template $activate_template ) {
		$this->_active_template = $activate_template;
	}
}
