<?php

namespace WCPM\Classes\Admin;

use WC_Order_Query;
use WCPM\Classes\Helpers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// https://stackoverflow.com/a/36453587/4688612
class Order_Columns {

	private static $instance;

	public static function get_instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {

		// pre HPOS
		add_action('pre_get_posts', [$this, 'view_pixels_not_fired'], 1000, 1);

		// pre HPOS and HPOS
		add_action('manage_shop_order_posts_custom_column', [$this, 'custom_orders_list_column_content'], 20, 2);
		add_filter('manage_edit-shop_order_columns', [$this, 'custom_shop_order_column'], 20);
		add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'custom_shop_order_column'], 20);

		// HPOS only
		add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'custom_orders_list_column_content_order'], 20, 2);
		add_action('woocommerce_order_list_table_prepare_items_query_args', [$this, 'hpos_view_query_adjustment'], 20);
		add_filter('views_woocommerce_page_wc-orders', [$this, 'hpos_menu_view'], 20);
	}

	public function hpos_menu_view( $views ) {

		$count_missing_pixel_fires = count(wc_get_orders([
															 'return'         => 'ids',
															 'status'         => ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending'],
															 'posts_per_page' => -1,
															 'date_created'   => '>' . ( time() - MONTH_IN_SECONDS ),
															 'meta_query'     => [
																 [
																	 'key'     => '_wpm_process_through_wpm',
																	 'compare' => 'EXISTS',
																 ],
																 [
																	 'key'     => '_wpm_conversion_pixel_fired',
																	 'compare' => 'NOT EXISTS'
																 ],
															 ],
															 'field_query'    => [
																 [
																	 'field'   => 'created_via',
																	 'value'   => 'checkout',
																	 'compare' => '='
																 ]
															 ]
														 ]));


		return $this->get_updated_views_hpos($views, $count_missing_pixel_fires, $this->is_pmw_pixels_not_fired_view());
	}

	public function get_updated_views_hpos( $views, $count_missing_pixel_fires, $pmw_pixels_not_fired_query ) {

		$query_string = admin_url('admin.php?page=wc-orders');
		$query_string = add_query_arg('pmw-pixels-not-fired', '', $query_string);

		$class = $pmw_pixels_not_fired_query ? 'current' : '';

		$views['pmw-pixels-not-fired'] = $this->get_pmw_no_pixels_fired_view_html($query_string, $class, $count_missing_pixel_fires);

		return $views;
	}

	private function get_pmw_no_pixels_fired_view_html( $query_string, $class, $count_missing_pixel_fires ) {
		return sprintf('<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', $query_string, $class, esc_html__('PMW pixels not fired - 30d', 'woocommerce-google-adwords-conversion-tracking-tag'), $count_missing_pixel_fires);
	}

	public function hpos_view_query_adjustment( $query_args ) {

		if (!$this->is_pmw_pixels_not_fired_view()) {
			return $query_args;
		}

		// checkout, admin, subscription, Vipps Express Checkout
		$query_args['created_via'] = 'checkout';

		$query_args['status']       = ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending'];
		$query_args['date_created'] = '>' . ( time() - MONTH_IN_SECONDS );

		$query_args['meta_query'][] = [
			'key'     => '_wpm_process_through_wpm',
			'compare' => 'EXISTS',
		];

		$query_args['meta_query'][] = [
			'key'     => '_wpm_conversion_pixel_fired',
			'compare' => 'NOT EXISTS',
		];

		return $query_args;
	}


	private function is_pmw_pixels_not_fired_view() {
		$_get = Helpers::get_input_vars_sanitized(INPUT_GET);
		return isset($_get['pmw-pixels-not-fired']);
	}

	public function view_pixels_not_fired( $query ) {

		if (!Helpers::is_orders_page()) {
			return;
		}

		if (Helpers::is_wc_hpos_enabled()) {
			return;
		}

		if (!$query->is_main_query()) {
			return;
		}

		$is_pmw_pixels_not_fired_view = $this->is_pmw_pixels_not_fired_view();

		// Set additional filters for the view
		if ($is_pmw_pixels_not_fired_view) {

			$query->set('post_status', ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending']);

			// Only search order not older than 30 days
			$query->set('date_query', [
				'column' => 'post_date',
				'after'  => '30 days ago',
			]);

			$query->query_vars['meta_query'][] = [
				'key'     => '_wpm_process_through_wpm',
				'compare' => 'EXISTS',
			];

			$query->query_vars['meta_query'][] = [
				'key'     => '_wpm_conversion_pixel_fired',
				'compare' => 'NOT EXISTS',
			];

			// checkout, admin, subscription, Vipps Express Checkout
			$query->query_vars['meta_query'][] = [
				'key'     => '_created_via',
				'value'   => 'checkout',
				'compare' => '=',
			];
		}

		// Count all post_meta where _wpm_process_through_wpm exists and _wpm_conversion_pixel_fired is missing and not older than 30d
		$count_missing_pixel_fires = count(get_posts([
														 'fields'         => 'ids',
														 'post_type'      => 'shop_order',
														 'post_status'    => ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending'],
														 'posts_per_page' => -1,
														 'date_query'     => [
															 'column' => 'post_date',
															 'after'  => '30 days ago',
														 ],
														 'meta_query'     => [
															 [
																 'key'     => '_wpm_process_through_wpm',
																 'compare' => 'EXISTS',
															 ],
															 [
																 'key'     => '_wpm_conversion_pixel_fired',
																 'compare' => 'NOT EXISTS'
															 ],
															 [
																 'key'     => '_created_via',
																 'value'   => 'checkout',
																 'compare' => '=',
															 ],
														 ]
													 ]));

		// Add new view with all orders where the conversion pixels have not been fired
		add_filter('views_edit-shop_order', function ( $views ) use ( $count_missing_pixel_fires, $is_pmw_pixels_not_fired_view ) {
			return $this->get_updated_views($views, $count_missing_pixel_fires, $is_pmw_pixels_not_fired_view);
		});
	}

	public function get_updated_views( $views, $count_missing_pixel_fires, $pmw_pixels_not_fired_query ) {
		$query_string = admin_url('edit.php?post_type=shop_order');
		$query_string = add_query_arg('pmw-pixels-not-fired', '', $query_string);

		$class = $pmw_pixels_not_fired_query ? 'current' : '';

		$views['pmw-pixels-not-fired'] = $this->get_pmw_no_pixels_fired_view_html($query_string, $class, $count_missing_pixel_fires);

		return $views;
	}


	public function pmw_order_table_styles() {
		wp_add_inline_style(
			'woocommerce_admin_styles',
			'table.wp-list-table .column-pmw-monitored{ width: 9%; } table.wp-list-table .column-pmw-pixels-fired{ width: 9px; }'
		);
	}

	public function custom_shop_order_column( $columns ) {

//		error_log('columns: ' . print_r($columns, true));

		function get_list_order_parameter( $list_order ) {
			if (is_null($list_order)) {
				return '';
			}

			return '&amp;order=' . $list_order;
		}

		$_get = Helpers::get_input_vars_sanitized(INPUT_GET);

		if (!isset($_get['order'])) {
			$list_order = 'dsc';
		} elseif ('dsc' === $_get['order']) {
			$list_order = 'asc';
		} else {
			$list_order = null;
		}

		$reordered_columns = [];

		// Inserting columns to a specific location
		foreach ($columns as $key => $column) {
			$reordered_columns[$key] = $column;
			if ('wc_actions' === $key) {
//				$reordered_columns['pmw-monitored']    = '<a href="' . get_site_url() . '/wp-admin/edit.php?post_type=shop_order&amp;orderby=pmw-monitored' . get_list_order_parameter($list_order) . '"><span class="widefat column-order_date column-pmw-monitored { width: 9%; } pmw-monitored-head tips"  data-tip="PMW monitored">PMW monitored</span></a>';
				$pixels_fired_text                     = esc_html__('PMW pixels fired', 'woocommerce-google-adwords-conversion-tracking-tag');
				$reordered_columns['pmw-pixels-fired'] = '<span class="pmw-monitored-head tips" data-tip="' . $pixels_fired_text . '">' . $pixels_fired_text . '</span>';
			}
		}

		return $reordered_columns;
	}

	public function custom_orders_list_column_content( $column_name, $post_id ) {

		$order = wc_get_order($post_id);

		$this->custom_orders_list_column_content_order($column_name, $order);
	}

	public function custom_orders_list_column_content_order( $column_name, $order ) {


		switch ($column_name) {
			case 'pmw-monitored':
				// Get custom post meta data
				if ($order->get_meta('_wpm_process_through_wpm', true)) {
					$text = __('PMW monitored order', 'woocommerce-google-adwords-conversion-tracking-tag');
					?>
					<div aria-hidden="true" title="<?php esc_html_e($text); ?>" class="pmw-monitored-icon good">
						<span class="screen-reader-text"><?php esc_html_e($text); ?></span>
					</div>
					<?php
				} else {
					$text = __('Not monitored by PMW', 'woocommerce-google-adwords-conversion-tracking-tag');
					?>
					<div aria-hidden="true" title="<?php esc_html_e($text); ?>" class="pmw-monitored-icon bad">
						<span class="screen-reader-text"><?php esc_html_e($text); ?></span>
					</div>
					<?php
				}

				break;

			case 'pmw-pixels-fired':
				// Get custom post meta data
				if (!$order->get_meta('_wpm_process_through_wpm', true)) {
					$text = __('Order not tracked by PMW', 'woocommerce-google-adwords-conversion-tracking-tag');
					?>
					<div aria-hidden="true" title="<?php esc_html_e($text); ?>" class="pmw-monitored-icon none">
						<span class="screen-reader-text"><?php esc_html_e($text); ?></span>
					</div>
					<?php

				} elseif ($order->get_created_via() !== 'checkout') {
					$text = __('This order was either created by a shop manager, or automatically added by an extension like a subscription plugin. Only orders created by customers are analysed.', 'woocommerce-google-adwords-conversion-tracking-tag');
					?>
					<div aria-hidden="true" title="<?php esc_html_e($text); ?>" class="pmw-monitored-icon none">
						<span class="screen-reader-text"><?php esc_html_e($text); ?></span>
					</div>
					<?php

				} elseif ($order->get_meta('_wpm_conversion_pixel_fired', true)) {
					$text = __('Conversion pixels fired', 'woocommerce-google-adwords-conversion-tracking-tag');
					?>
					<div aria-hidden="true" title="<?php esc_html_e($text); ?>" class="pmw-monitored-icon good">
						<span class="screen-reader-text"><?php esc_html_e($text); ?></span>
					</div>
					<?php

				} else {
					$text = __('Conversion pixels not fired yet', 'woocommerce-google-adwords-conversion-tracking-tag');
					?>
					<div aria-hidden="true" title="<?php esc_html_e($text); ?>" class="pmw-monitored-icon bad">
						<span class="screen-reader-text"><?php esc_html_e($text); ?></span>
					</div>
					<?php
				}

				break;
		}
	}
}
