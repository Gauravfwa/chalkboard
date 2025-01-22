<?php
/**
 * This file contains the class to interact with database.
 *
 * @since 1.0.0
 *
 * @package MonsterInsights
 * @package MonsterInsights_User_Journey
 */

/**
 * Class containing CRUD operations for custom table.
 *
 * @since 1.0.0
 */
final class MonsterInsights_User_Journey_DB {

	/**
	 * Primary key for DB
	 *
	 * @since 1.0.2
	 *
	 * @var string
	 */
	private $primary_key = 'id';

	/**
	 * Get the DB table name.
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	private static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . 'monsterinsights_user_journey';
	}

	/**
	 * Get table columns.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_columns() {

		return [
			'id'         => '%d',
			'entry_id'   => '%d',
			'post_id'    => '%d',
			'url'        => '%s',
			'parameters' => '%s',
			'external'   => '%d',
			'title'      => '%s',
			'duration'   => '%d',
			'step'       => '%d',
			'date'       => '%s',
		];
	}

	/**
	 * Default column values.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function get_column_defaults() {

		return [
			'entry_id'   => '',
			'post_id'    => '',
			'url'        => '',
			'parameters' => '',
			'external'   => '',
			'title'      => '',
			'duration'   => '',
			'step'       => '',
			'date'       => gmdate( 'Y-m-d H:i:s' ),
		];
	}

	/**
	 * Check if the given table exists.
	 *
	 * @param string $table The table name. Defaults to the child class table name.
	 *
	 * @return bool If the table name exists.
	 * @since 1.0.0
	 *
	 */
	public static function table_exists( $table = '' ) {

		global $wpdb;

		if ( ! empty( $table ) ) {
			$table = sanitize_text_field( $table );
		} else {
			$table = self::get_table_name();
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}

	/**
	 * Create custom user journey table. Used on plugin activation.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public static function create_table() {

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate .= "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$table_name = self::get_table_name();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			entry_id bigint(20) NOT NULL,
			post_id bigint(20) NOT NULL,
			url varchar(2083) NOT NULL,
			parameters varchar(2000) NOT NULL,
			external tinyint(1) DEFAULT 0,
			title varchar(256) NOT NULL,
			duration int NOT NULL,
			step tinyint NOT NULL,
			date datetime NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Perform certain actions on plugin activation.
	 *
	 * @param bool $network_wide Whether to enable the plugin for all sites in the network
	 *                           or just the current site. Multisite only. Default is false.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public static function install( $network_wide = false ) {

		// Check if we are on multisite and network activating.
		if ( is_multisite() && $network_wide ) {

			// Multisite - go through each subsite and run the installer.
			$sites = get_sites(
				[
					'fields' => 'ids',
					'number' => 0,
				]
			);

			foreach ( $sites as $blog_id ) {
				switch_to_blog( $blog_id );
				self::run();
				restore_current_blog();
			}
		} else {

			// Normal single site.
			self::run();
		}
	}

	/**
	 * Run the actual installer.
	 *
	 * @since 1.0.0
	 */
	public static function run() {

		// Create the table if it doesn't exist.
		if ( ! self::table_exists() ) {
			self::create_table();
		}

		update_option( 'monsterinsights_user_journey_version', MONSTERINSIGHTS_USER_JOURNEY_VERSION );
	}

	/**
	 * When a new site is created in multisite, see if we are network activated,
	 * and if so run the installer.
	 *
	 * @param int $blog_id Blog ID.
	 * @param int $user_id User ID.
	 * @param string $domain Site domain.
	 * @param string $path Site path.
	 * @param int $site_id Site ID. Only relevant on multi-network installs.
	 * @param array $meta Meta data. Used to set initial site options.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public static function new_multi_site_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		if ( is_plugin_active_for_network( plugin_basename( MONSTERINSIGHTS_USER_JOURNEY_FILE ) ) ) {
			switch_to_blog( $blog_id );
			self::run();
			restore_current_blog();
		}
	}

	/**
	 * Add User Journey entries to custom database table.
	 *
	 * @param array $data Data to store.
	 * @param string $type
	 *
	 * @return int
	 * @since 1.0.0
	 *
	 */
	public function add( $data, $type = '' ) {
		global $wpdb;

		// Set default values.
		$data = (array) wp_parse_args( $data, $this->get_column_defaults() );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );

		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( self::get_table_name(), $data, $column_formats );

		return $wpdb->insert_id;
	}

	/**
	 * Get user journey if available for entry.
	 *
	 * @param int $entry_id Single entry.
	 * @param array $args Query Arguments.
	 *
	 * @param bool $count Wether to return count or not from DB.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 * @since 1.0.2
	 * @since 1.0.0
	 *
	 */
	public function get_user_journey( $entry_id, $args = array(), $count = false ) {
		$records = array();

		if ( 0 === absint( $entry_id ) ) {
			return $records;
		}

		$query_args = array( 'entry_id' => $entry_id );

		if ( ! empty( $args ) ) {
			$query_args = array_merge( $query_args, $args );
		}

		$records = $this->get_rows( $query_args, $count );

		if ( ! empty( $records ) ) {
			return $records;
		}

		return $records;
	}

	/**
	 * Get rows from the database.
	 *
	 * @param array $args Optional args.
	 * @param bool $count Flag to return count instead of results.
	 *
	 * @return array|int
	 * @since 1.0.0
	 *
	 */
	public function get_rows( $args = [], $count = false ) {

		global $wpdb;

		$defaults = [
			'number'   => 999,
			'offset'   => 0,
			'id'       => 0,
			'entry_id' => 0,
			'post_id'  => 0,
			'orderby'  => 'id',
			'order'    => 'ASC',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = PHP_INT_MAX;
		}

		$entry_id = esc_attr( absint( $args['entry_id'] ) );

		// Orderby.
		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

		// Offset.
		$args['offset'] = absint( $args['offset'] );

		// Number.
		$args['number'] = absint( $args['number'] );

		// Order.
		if ( 'ASC' === strtoupper( $args['order'] ) ) {
			$args['order'] = 'ASC';
		} else {
			$args['order'] = 'DESC';
		}

		$table_name = self::get_table_name();

		if ( true === $count ) {

			$results = absint( $wpdb->get_var( "SELECT COUNT({$this->primary_key}) FROM {$table_name} WHERE entry_id = {$entry_id};" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		} else {

			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				"SELECT * FROM {$table_name} WHERE entry_id = {$entry_id} ORDER BY {$args['orderby']} {$args['order']} LIMIT {$args['offset']}, {$args['number']};" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
		}

		return $results;
	}

	/**
	 * Delete rows from User Journey table.
	 *
	 * @param integer $entry_id Entry ID or Order ID.
	 *
	 * @return bool
	 * @since 1.0.1
	 *
	 */
	public function delete( $entry_id = 0 ) {
		global $wpdb;

		$entry_id = absint( $entry_id );

		if ( 0 === $entry_id ) {
			return;
		}

		$user_journey = $this->get_user_journey( $entry_id );

		if ( empty( $user_journey ) ) {
			return;
		}

		$deleted = $wpdb->delete( self::get_table_name(), array( 'entry_id' => esc_attr( $entry_id ) ) );

		if ( false !== $deleted && $deleted > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Ger user journey report.
	 *
	 * @param array $params Post data by users.
	 *
	 * @return array
	 * @since 1.0.7
	 */
	public function get_paginated_report( $params ) {
		global $wpdb;

		$table_name   = $this->get_table_name();
		$prepare_args = array();

		$query = "SELECT SQL_CALC_FOUND_ROWS `entry_id` AS transaction_id, COUNT(`step`) AS steps FROM {$table_name} WHERE 1=1 ";

		if ( ! empty( $params['search'] ) ) {
			$query .= " AND `entry_id` = %d";
			$prepare_args[] = intval( $params['search'] );
		}

		if ( ! empty( $params['start_date'] ) && ! empty( $params['end_date'] ) ) {
			$query .= " AND (`date` BETWEEN %s AND %s)";
			$prepare_args[] = trim( $params['start_date'] ) . ' 00:00:00';
			$prepare_args[] = trim( $params['end_date'] ) . ' 23:59:59';
		}

		if ( ! empty( $params['campaigns'] ) ) {
			$query .= " AND `parameters` LIKE %s";
			$prepare_args[] = '%"utm_campaign":"' . $params['campaigns'] . '"%';
		}

		if ( ! empty( $params['mediums'] ) ) {
			$query .= " AND `parameters` LIKE %s";
			$prepare_args[] = '%"utm_medium":"' . $params['mediums'] . '"%';
		}

		if ( ! empty( $params['sources'] ) ) {
			$query .= " AND `parameters` LIKE %s";
			$prepare_args[] = '%"utm_source":"' . $params['sources'] . '"%';
		}

		$prepare_args[] = $per_page = 10;
		$prepare_args[] = ( $params['page'] - 1 ) * $per_page; // offset

		$query .= " GROUP BY `entry_id` ORDER BY `entry_id` DESC LIMIT %d OFFSET %d";

		$result = $wpdb->get_results( $wpdb->prepare( $query, $prepare_args ) );
		$total_count = (int) $wpdb->get_var( "SELECT FOUND_ROWS();" );

		$report = array(
			'items'      => array(),
			'pagination' => array(
				'page'     => $params['page'],
				'pages'    => ceil( $total_count / $per_page ),
				'per_page' => $per_page,
				'total'    => $total_count,
			),
			'success'    => true,
		);

		if ( $wpdb->last_error || ! is_array( $result ) ) {
			$report['success'] = false;

			return $report;
		}

		if ( empty( $result ) ) {
			return $report;
		}

		foreach ( $result as $item ) {
			$report['items'][] = $this->prepare_report_item( $item );
		}

		return $report;
	}

	/**
	 * Process data to show in frontend.
	 *
	 * @param stdClass $item Data from DB.
	 *
	 * @since 1.0.7
	 * @return array
	 */
	private function prepare_report_item( $item ) {
		$data = array(
			'transaction_id' => $item->transaction_id,
			'steps'          => $item->steps,
		);

		$parameters = array();

		if ( $string_parameters = $this->get_item_utm_parameters( $item->transaction_id ) ) {
			$parameters = json_decode( $string_parameters, true );
		}

		$data['utm_source']   = empty( $parameters['utm_source'] ) ? '--' : $parameters['utm_source'];
		$data['utm_medium']   = empty( $parameters['utm_medium'] ) ? '--' : $parameters['utm_medium'];
		$data['utm_campaign'] = empty( $parameters['utm_campaign'] ) ? '--' : $parameters['utm_campaign'];

		$order_data = monsterinsights_user_journey()->helper->get_provider_order_data( $item->transaction_id );

		if ( isset( $order_data['date'] ) ) {
			$date_time_format = get_option( 'date_format', 'Y-m-d' ) . ' ' . get_option( 'time_format', 'H:i' );

			if ( function_exists( 'wp_date' ) ) { // wp_date introduced on v5.3.0
				$data['purchase_date'] = wp_date( $date_time_format, strtotime( $order_data['date'] ) );
			} else {
				$data['purchase_date'] = gmdate( $date_time_format, strtotime( $order_data['date'] ) );
			}
		} else {
			$data['purchase_date'] = '--';
		}

		$data['order_total']    = isset( $order_data['total_with_currency'] ) ? $order_data['total_with_currency'] : '--';
		$data['edit_order_url'] = isset( $order_data['edit_order_url'] ) ? $order_data['edit_order_url'] : '#';

		return $data;
	}

	/**
	 * Get all parameters.
	 *
	 * @since 1.0.7
	 * @return array
	 */
	public function get_all_parameters() {
		global $wpdb;

		$table_name = $this->get_table_name();
		$query      = "SELECT `parameters` FROM `{$table_name}` WHERE `parameters` IS NOT NULL AND `parameters` != ''";
		$result     = $wpdb->get_col( $query );

		$source   = array();
		$medium   = array();
		$campaign = array();

		if ( $wpdb->last_error || empty( $result ) || ! is_array( $result ) ) {
			return array(
				'sources'   => $source,
				'mediums'   => $medium,
				'campaigns' => $campaign,
			);
		}

		foreach( $result as $item ) {
			$parameters = json_decode( $item, true );

			if ( ! empty( $parameters['utm_source'] ) ) {
				$utm_source = $parameters['utm_source'];
				$source[ $utm_source ] = $utm_source;
			}

			if ( ! empty( $parameters['utm_medium'] ) ) {
				$utm_medium = $parameters['utm_medium'];
				$medium[ $utm_medium ] = $utm_medium;
			}

			if ( ! empty( $parameters['utm_campaign'] ) ) {
				$utm_campaign = $parameters['utm_campaign'];
				$campaign[ $utm_campaign ] = $utm_campaign;
			}
		}

		return array(
			'sources'   => array_values( $source ),
			'mediums'   => array_values( $medium ),
			'campaigns' => array_values( $campaign ),
		);
	}

	/**
	 * Determine we need to show demo report or actual.
	 *
	 * @return false|array
	 */
	public function show_demo_report() {
		global $wpdb;

		$table_name = $this->get_table_name();

		$data_exists = $wpdb->get_var( "SELECT `id` FROM {$table_name} LIMIT 1" );

		if ( $data_exists ) {
			return false;
		}

		$report = array();

		$sources   = array( 'google', 'newsletter', 'billboard' );
		$mediums   = array( 'cpc', 'banner', 'email' );
		$campaigns = array( 'campaign-name', 'slogan', 'promo-code' );

		for ( $i = 1; $i <= 10; $i ++ ) {
			$rand_key = array_rand( $sources );

			$report[] = array(
				'transaction_id' => wp_rand( 12, 30 ),
				'steps'          => wp_rand( 3, 8 ),
				'order_total'    => wp_rand( 10, 50 ),
				'utm_source'     => $sources[ $rand_key ],
				'utm_medium'     => $mediums[ $rand_key ],
				'utm_campaign'   => $campaigns[ $rand_key ],
				'purchase_date'  => '--',
			);
		}

		return $report;
	}

	/**
	 * Get UTM parameters.
	 *
	 * @param int $transaction_id Entry id from user journey.
	 *
	 * @return bool|string
	 */
	private function get_item_utm_parameters( $transaction_id ) {
		global $wpdb;

		$table_name = $this->get_table_name();

		$parameters = $wpdb->get_var( $wpdb->prepare(
			"SELECT `parameters` FROM {$table_name} WHERE
			 `parameters` LIKE %s AND
			 `entry_id` = %d ORDER BY `date` DESC LIMIT 1",
			'%"utm_%',
			$transaction_id
		) );

		if ( $parameters ) {
			return $parameters;
		}

		return false;
	}
}
