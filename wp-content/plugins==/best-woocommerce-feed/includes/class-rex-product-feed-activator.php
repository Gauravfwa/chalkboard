<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/includes
 * @author     RexTheme <info@rextheme.com>
 */
class Rex_Product_Feed_Activator {

    /**
     * DB updates and callbacks that need to be run per version.
     *
     * @var array
     */
    private static $db_updates = array(
        '3.0' => array(
            'wpfm_update_category_mapping',
        ),
    );

	/**
	 * on Plugin activation
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    self::update_notice();
	}


    /**
     * Does a database update required
     *
     * @since  2.2.5
     * @return boolean
     */
	public static function needs_database_update() {
        $current_db_version         = get_option('rex_wpfm_db_version', null);
        return is_null( $current_db_version );
    }


    /**
     * Get list of DB update callbacks.
     *
     * @since  2.4
     * @return array
     */
    public static function get_db_update_callbacks() {
        return self::$db_updates;
    }


    /**
     * Update DB version to current.
     *
     * @param string|null $version New WoCommerce Product Feed Manager version or null.
     */
    public static function update_db_version( $version = null ) {
        delete_transient('rex-wpfm-database-update');
        delete_option( 'rex_wpfm_db_version' );
        add_option( 'rex_wpfm_db_version', $version );
    }


    /**
     * If we need to update, include a message with the update button.
     */
    public static function update_notice() {
        if ( self::needs_database_update() ) {
            set_transient( 'rex-wpfm-database-update', true, 3153600000 ); /* never expire unless user force it */
        }
    }
}
