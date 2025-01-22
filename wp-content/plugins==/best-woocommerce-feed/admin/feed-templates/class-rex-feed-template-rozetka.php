<?php
/**
 * Class Rex_Feed_Template_Rozetka
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Rozetka
 * @author     RexTheme <info@rextheme.com>
 */

/**
 * Defines the attributes and template for Rozetka feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Rozetka
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Feed_Template_Rozetka extends Rex_Feed_Abstract_Template {

	/**
	 * Define merchant's required and optional/additional attributes
	 *
	 * @return void
	 */
	protected function init_atts() {
		$this->attributes = array(
			'Required Information' => array(
				'stock_quantity' => 'Quantity in Stock [stock_quantity]',
				'price'          => 'Price [price]',
				'currencyId'     => 'Currency ID [currencyId]',
				'categoryId'     => 'Category ID [categoryId]',
				'picture'        => 'Image [picture]',
				'vendor'         => 'Vendor [vendor]',
				'name'           => 'Product Name [name]',
				'description'    => 'Product Description [description]',
				'param'          => 'Parameter [param]',
			),
			'Optional Information' => array(
				'url'       => 'Product URL',
				'price_old' => 'Old Price [price_old]',
				'name_ua'   => 'Ukrainian Product Name [name_ua]',
				'state'     => 'Product Condition [state]',
			),
		);
	}

	/**
	 * Define merchant's default attributes
	 *
	 * @return void
	 */
	protected function init_default_template_mappings() {
		$this->template_mappings = array(
			array(
				'attr'     => 'stock_quantity',
				'type'     => 'meta',
				'meta_key' => 'quantity',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'price',
				'type'     => 'meta',
				'meta_key' => 'price',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'currencyId',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'categoryId',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'picture',
				'type'     => 'meta',
				'meta_key' => 'main_image',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'vendor',
				'type'     => 'static',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'name',
				'type'     => 'meta',
				'meta_key' => 'title',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'description',
				'type'     => 'meta',
				'meta_key' => 'description',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
			array(
				'attr'     => 'param',
				'type'     => 'meta',
				'meta_key' => '',
				'st_value' => '',
				'prefix'   => '',
				'suffix'   => '',
				'escape'   => 'default',
				'limit'    => 0,
			),
		);
	}
}
