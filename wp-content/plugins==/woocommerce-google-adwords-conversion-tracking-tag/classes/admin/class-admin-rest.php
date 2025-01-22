<?php

namespace WCPM\Classes\Admin;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

class Admin_REST
{
    protected  $rest_namespace = 'pmw/v1' ;
    private static  $instance ;
    public static function get_instance()
    {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct()
    {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }
    
    public function register_routes()
    {
        register_rest_route( $this->rest_namespace, '/notifications/', [
            'methods'             => 'POST',
            'callback'            => function ( $request ) {
            $data = $request->get_json_params();
            if ( 'pmw-dismiss-opportunities-message-button' === $data['notification'] ) {
                Opportunities::dismiss_dashboard_notification();
            }
            
            if ( 'dismiss_opportunity' === $data['notification'] ) {
                $opportunity_id = filter_var( $data['opportunityId'], FILTER_SANITIZE_STRING );
                Opportunities::dismiss_opportunity( $opportunity_id );
            }
        
        },
            'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
        ] );
    }

}