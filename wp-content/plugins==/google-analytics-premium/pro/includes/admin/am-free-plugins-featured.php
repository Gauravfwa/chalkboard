<?php
/**
 * Show featured plugins `add new plugin` screen.
 *
 * @since 8.15
 *
 * @package MonsterInsights
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AM_Free_Plugins_Featured' ) ) {
	/**
	 * Class AM_Free_Plugins_Featured
	 */
	class AM_Free_Plugins_Featured {

		/**
		 * @var array
		 */
		protected $plugins = array();

		/**
		 * Construct of the class.
		 */
		public function __construct() {
			add_filter( 'plugins_api_result', array( $this, 'add' ), 11, 3 );

			$this->plugins = apply_filters( 'am_free_plugins_featured', array(
				'wpforms-lite'               => 'wpforms.php',
				'all-in-one-seo-pack'        => 'all_in_one_seo_pack.php',
				'wp-mail-smtp'               => 'wp_mail_smtp.php',
				'optinmonster'               => 'optin-monster-wp-api.php',
				'insert-headers-and-footers' => 'ihaf.php',
				'coming-soon'                => 'coming-soon.php',
				'instagram-feed'             => 'instagram-feed.php',
				'custom-facebook-feed'       => 'custom-facebook-feed.php',
				'easy-digital-downloads'     => 'easy-digital-downloads.php',
				'stripe'                     => 'stripe-checkout.php',
				'userfeedback-lite'          => 'userfeedback.php',
			) );
		}

		/**
		 * Add our own information to filter.
		 *
		 * @param $result
		 * @param $action
		 * @param $args
		 *
		 * @return array
		 */
		public function add( $result, $action, $args ) {

			if (
				empty( $args->browse ) ||
				! in_array( $args->browse, [ 'featured', 'recommended', 'popular' ] ) ||
				! isset( $result->info['page'] ) ||
				1 < $result->info['page']
			) {
				return $result;
			}

			$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

			foreach ( $this->plugins as $slug => $file_name ) {
				if (
					in_array( $slug, $result_slugs, true ) ||
					is_plugin_active( "{$slug}/{$file_name}" ) ||
					is_plugin_active_for_network( "{$slug}/{$file_name}" )
				) {
					continue;
				}

				$plugin_data = plugins_api( 'plugin_information', array(
					'slug'   => $slug,
					'fields' => [
						'icons'             => true,
						'active_installs'   => true,
						'short_description' => true,
						'group'             => true,
					],
				) );

				if ( is_wp_error( $plugin_data ) ) {
					continue;
				}

				array_unshift( $result->plugins, $plugin_data );
			}

			return $result;
		}
	}

	new AM_Free_Plugins_Featured();
}
