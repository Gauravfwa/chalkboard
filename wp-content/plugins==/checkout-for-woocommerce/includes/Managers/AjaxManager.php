<?php

namespace Objectiv\Plugins\Checkout\Managers;

/**
 * Class AjaxManager
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Managers
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class AjaxManager {

	/**
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $ajax_modules;

	/**
	 * AjaxManager constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $ajax_modules
	 */
	public function __construct( array $ajax_modules ) {
		$this->ajax_modules = $ajax_modules;
	}

	/**
	 * Iterates through each ajax module and loads it (registers it) with the system
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function load_all() {
		foreach ( $this->ajax_modules as $ajax ) {
			$ajax->load();
		}
	}

	/**
	 * @since 1.0.0
	 * @access public
	 * @return mixed
	 */
	public function get_ajax_modules(): array {
		return $this->ajax_modules;
	}
}
