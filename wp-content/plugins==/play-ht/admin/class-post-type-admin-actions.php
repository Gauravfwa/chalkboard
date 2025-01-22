<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backend logic
 *
 * @package Play_HT
 */
class Post_Type_Admin_Actions extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// avoid injecting our content on learnPress plugin pages
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'lp_course' ) {
			return false;
		}

		if ( ! playht_is_post_type_supported() ) {
			return;
		}

		// add custom columns to posts
		add_filter( 'manage_posts_columns', [ &$this, 'play_columns_head' ] );
		add_filter( 'manage_pages_columns', [ &$this, 'play_columns_head' ] );
		add_filter( 'manage_pages_custom_column', [ &$this, 'play_columns_head' ] );

		// custom column content
		add_action( 'manage_posts_custom_column', [ &$this, 'play_columns_content' ], 10, 2 );
		add_action( 'manage_pages_custom_column', [ &$this, 'play_columns_content' ], 10, 2 );
	}

	/**
	 *  Add custom columns posts table
	 *
	 * @param $defaults
	 *
	 * @return array
	 */
	public function play_columns_head( $defaults ) {
		$new_order = [];

		if ( is_array( $defaults ) ) {
			foreach ( $defaults as $key => $title ) {
				$new_order[ $key ] = $title;
				if ( $key == 'title' ) {
					$new_order['has_audio'] = 'Audio';
					// TODO: Add number of listens back when we replace keen with ES and read the number of listens from there. Need a new column and showinf its data.
				}
			}
		}
			return $new_order;
	}

	/**
	 *  Play columns content
	 *
	 * @param $column_name
	 * @param $post_ID
	 */
	public function play_columns_content( $column_name, $post_ID ) {
		if ( $column_name == 'has_audio' ) {
			echo PHP_EOL . playht_action_rows( $post_ID ) . PHP_EOL;
		}
	}

}
