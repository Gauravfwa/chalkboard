<?php

/**
 * Class Opportunities
 *
 * Show opportunities in a PMW tab
 *
 * @package PMW
 * @since   1.27.11
 *
 * Available opportunities
 *          pro
 *  			Meta CAPI
 *  			Google Ads Enhanced Conversions
 *  			Google Ads Conversion Adjustments
 *  			Pinterest Enhanced Match
 *  			Subscription Multiplier
 *
 *          free
 *  			Dynamic Remarketing
 *  			Dynamic Remarketing Variations Output
 *  			Google Ads Conversion Cart Data
 *
 *  TODO: TikTok EAPI
 *  TODO: Newsletter subscription
 *  TODO: Upgrade to Premium version
 *  TODO: Gateway accuracy warning
 *  TODO: Detect WooCommerce GA Integration (rule, only if one, GA3 or GA4 are enabled)
 *  TODO: Detect MonsterInsights
 *  TODO: Detect Tatvic
 *  TODO: Detect WooCommerce Conversion Tracking
 *  TODO: Opportunity to use the SweetCode Google Automated Discounts plugin
 *
 */
namespace WCPM\Classes\Admin;

use  WCPM\Classes\Options ;

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Class Opportunities
 *
 * Manages the opportunities tab.
 * Contains HTML templates.
 *
 * @package WCPM\Classes\Admin
 * @since   1.28.0
 */
class Opportunities
{
    public static  $pmw_opportunities_option = 'pmw_opportunities' ;
    public static function html()
    {
        ?>
		<div>
			<div>
				<p>
					<?php 
        esc_html_e( 'Opportunities show how you could tweak the plugin settings to get more out of the Pixel Manager.', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
				</p>
			</div>
			<div>
				<h2>
					<?php 
        esc_html_e( 'Available Opportunities', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>
			</div>

			<!-- Opportunities -->

			<?php 
        self::opportunities_not_dismissed();
        ?>

			<div>
				<h2>
					<?php 
        esc_html_e( 'Dismissed Opportunities', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?></h2>
			</div>
			<div id="pmw-dismissed-opportunities">
				<?php 
        self::opportunities_dismissed();
        ?>
			</div>
		</div>
		<?php 
    }
    
    private static function opportunities_not_dismissed()
    {
        foreach ( self::get_opportunities() as $opportunity ) {
            if ( $opportunity::is_not_dismissed() ) {
                $opportunity::output_card();
            }
        }
    }
    
    private static function opportunities_dismissed()
    {
        foreach ( self::get_opportunities() as $opportunity ) {
            if ( $opportunity::is_dismissed() ) {
                $opportunity::output_card();
            }
        }
    }
    
    public static function card_html( $card_data, $custom_middle_html = null )
    {
        $main_card_classes = [ 'pmw', 'opportunity-card' ];
        if ( $card_data['dismissed'] ) {
            $main_card_classes[] = 'dismissed';
        }
        ?>
		<div id="pmw-opportunity-<?php 
        esc_html_e( $card_data['id'] );
        ?>"
			 class="<?php 
        esc_html_e( implode( ' ', $main_card_classes ) );
        ?>"
		>
			<!-- top -->
			<div class="pmw opportunity-card-top">
				<div><b><?php 
        esc_html_e( $card_data['title'] );
        ?></b></div>
				<div class="pmw opportunity-card-top-right">
					<div class="pmw opportunity-card-top-impact">
						<?php 
        esc_html_e( 'Impact', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>:
					</div>
					<div class="pmw opportunity-card-top-impact-level">
						<?php 
        esc_html_e( $card_data['impact'] );
        ?>
					</div>
				</div>
			</div>

			<hr class="pmw opportunity-card-hr">

			<!-- middle -->
			<div class="pmw opportunity-card-middle">

				<?php 
        
        if ( !empty($custom_middle_html) ) {
            ?>
					<?php 
            esc_html_e( $custom_middle_html );
            ?>
				<?php 
        } else {
            ?>
					<?php 
            foreach ( $card_data['description'] as $description ) {
                ?>
						<p class="pmw opportunity-card-description">
							<?php 
                esc_html_e( $description );
                ?>
						</p>
					<?php 
            }
            ?>
				<?php 
        }
        
        ?>

			</div>

			<hr class="pmw opportunity-card-hr">

			<!-- bottom -->
			<div class="pmw opportunity-card-bottom">
				<a class="pmw opportunity-card-button-link"
				   href="<?php 
        esc_html_e( $card_data['setup_link'] );
        ?>"
				   target="_blank"
				>
					<div class="pmw opportunity-card-bottom-button">
						<?php 
        esc_html_e( 'Setup', 'woocommerce-google-adwords-conversion-tracking-tag' );
        ?>
					</div>
				</a>

				<?php 
        
        if ( array_key_exists( 'learn_more_link', $card_data ) ) {
            ?>
					<a class="pmw opportunity-card-button-link"
					   href="<?php 
            esc_html_e( $card_data['learn_more_link'] );
            ?>"
					   target="_blank"
					>
						<div class="pmw opportunity-card-bottom-button">
							<?php 
            esc_html_e( 'Learn more', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
						</div>
					</a>
				<?php 
        }
        
        ?>

				<?php 
        
        if ( empty($card_data['dismissed']) ) {
            ?>
					<a class="pmw opportunity-card-button-link"
					   href="#"
					>
						<div class="pmw opportunity-dismiss opportunity-card-bottom-button"
							 data-opportunity-id="<?php 
            esc_html_e( $card_data['id'] );
            ?>">
							<?php 
            esc_html_e( 'Dismiss', 'woocommerce-google-adwords-conversion-tracking-tag' );
            ?>
						</div>
					</a>
				<?php 
        }
        
        ?>
			</div>
		</div>
		<?php 
    }
    
    private static function get_opportunities()
    {
        $classes = get_declared_classes();
        $opportunities = [];
        foreach ( $classes as $class ) {
            if ( is_subclass_of( $class, 'WCPM\\Classes\\Admin\\Opportunity' ) ) {
                $opportunities[] = $class;
            }
        }
        return $opportunities;
    }
    
    public static function active_opportunities_available()
    {
        // get pmw_opportunities option
        $option = get_option( self::$pmw_opportunities_option );
        foreach ( self::get_opportunities() as $opportunity ) {
            if ( class_exists( $opportunity ) ) {
                if ( $opportunity::available() && $opportunity::is_not_dismissed() && $opportunity::is_newer_than_dismissed_dashboard_time( $option ) ) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Dismisses the dashboard notification.
     *
     * @return void
     * @since 1.28.0
     */
    public static function dismiss_dashboard_notification()
    {
        $option = get_option( self::$pmw_opportunities_option );
        if ( empty($option) ) {
            $option = [];
        }
        $option['dashboard_notification_dismissed'] = time();
        update_option( self::$pmw_opportunities_option, $option );
        wp_send_json_success();
    }
    
    public static function dismiss_opportunity( $opportunity_id )
    {
        $option = get_option( self::$pmw_opportunities_option );
        if ( empty($option) ) {
            $option = [];
        }
        $option[$opportunity_id]['dismissed'] = time();
        update_option( self::$pmw_opportunities_option, $option );
        wp_send_json_success();
    }

}
/**
 * Abstract class Opportunity
 *
 * @since 1.28.0
 */
abstract class Opportunity
{
    /**
     * Check if the opportunity is available.
     *
     * @return bool
     * @since 1.28.0
     */
    public static abstract function available();
    
    public static function not_available()
    {
        return !static::available();
    }
    
    public static abstract function card_data();
    
    public static function custom_middle_cart_html()
    {
        return null;
    }
    
    public static function output_card()
    {
        if ( static::not_available() ) {
            return;
        }
        $card_data = static::card_data();
        $card_data['dismissed'] = static::is_dismissed();
        Opportunities::card_html( $card_data, static::custom_middle_cart_html() );
    }
    
    public static function is_dismissed()
    {
        $option = get_option( Opportunities::$pmw_opportunities_option );
        if ( empty($option) ) {
            return false;
        }
        if ( isset( $option[static::card_data()['id']]['dismissed'] ) ) {
            return true;
        }
        return false;
    }
    
    public static function is_not_dismissed()
    {
        return !static::is_dismissed();
    }
    
    public static function is_newer_than_dismissed_dashboard_time( $option )
    {
        if ( empty($option) ) {
            return true;
        }
        if ( !isset( $option['dashboard_notification_dismissed'] ) ) {
            return true;
        }
        if ( static::card_data()['since'] > $option['dashboard_notification_dismissed'] ) {
            return true;
        }
        return false;
    }

}
// end if (wpm_fs()->can_use_premium_code__premium_only())
/**
 * Opportunity: Google Ads Conversion Cart Data
 *
 * @since 1.28.0
 */
class Google_Ads_Conversion_Cart_Data extends Opportunity
{
    public static function available()
    {
        // Google Ads purchase conversion must be enabled
        if ( !Options::is_google_ads_purchase_conversion_active() ) {
            return false;
        }
        // Conversion Cart Data must be disabled
        if ( Options::is_google_ads_conversion_cart_data_active() ) {
            return false;
        }
        return true;
    }
    
    public static function card_data()
    {
        return [
            'id'          => 'google-ads-conversion-cart-data',
            'title'       => esc_html__( 'Google Ads Conversion Cart Data', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'description' => [ esc_html__( 'The Pixel Manager detected that Google Ads purchase conversion is enabled, but Google Ads Conversion Cart Data has yet to be enabled.', 'woocommerce-google-adwords-conversion-tracking-tag' ), esc_html__( 'Enabling Google Ads Conversion Cart Data will improve reporting by including cart item data in your Google Ads conversion reports.', 'woocommerce-google-adwords-conversion-tracking-tag' ) ],
            'impact'      => esc_html__( 'medium', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'setup_link'  => Documentation::get_link( 'aw_merchant_id' ),
            'since'       => 1672895375,
        ];
    }

}
/**
 * Opportunity: Dynamic Remarketing
 *
 * @since 1.28.0
 */
class Dynamic_Remarketing extends Opportunity
{
    public static function available()
    {
        // At least one paid ads pixel must be enabled
        if ( !Options::is_at_least_one_paid_ads_pixel_active() ) {
            return false;
        }
        // Dynamic Remarketing must be disabled
        if ( Options::is_dynamic_remarketing_enabled() ) {
            return false;
        }
        return true;
    }
    
    public static function card_data()
    {
        return [
            'id'          => 'dynamic-remarketing',
            'title'       => esc_html__( 'Dynamic Remarketing', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'description' => [ esc_html__( 'The Pixel Manager detected that at least one paid ads pixel is enabled, but Dynamic Remarketing has yet to be enabled.', 'woocommerce-google-adwords-conversion-tracking-tag' ), esc_html__( 'Enabling Dynamic Remarketing output will allow you to collect dynamic audiences (such as general visitors, product viewers, cart abandoners, and buyers) and create dynamic remarketing campaigns.', 'woocommerce-google-adwords-conversion-tracking-tag' ) ],
            'impact'      => esc_html__( 'medium', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'setup_link'  => Documentation::get_link( 'google_ads_dynamic_remarketing' ),
            'since'       => 1672895375,
        ];
    }

}
/**
 * Opportunity: Dynamic Remarketing Variations Output
 *
 * @since 1.28.0
 */
class Dynamic_Remarketing_Variations_Output extends Opportunity
{
    public static function available()
    {
        // At least one paid ads pixel must be enabled
        if ( !Options::is_at_least_one_paid_ads_pixel_active() ) {
            return false;
        }
        // Dynamic Remarketing must be disabled
        if ( !Options::is_dynamic_remarketing_enabled() ) {
            return false;
        }
        // Dynamic Remarketing Variations Output must be disabled
        if ( Options::is_dynamic_remarketing_variations_output_enabled() ) {
            return false;
        }
        return true;
    }
    
    public static function card_data()
    {
        return [
            'id'          => 'dynamic-remarketing-variations-output',
            'title'       => esc_html__( 'Dynamic Remarketing Variations Output', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'description' => [ esc_html__( 'The Pixel Manager detected that at least one paid ads pixel is enabled, Dynamic Remarketing is enabled, but Variations Output has yet to be enabled.', 'woocommerce-google-adwords-conversion-tracking-tag' ), esc_html__( 'Enabling Dynamic Remarketing Variations Output will allow you to collect more fine-grained, dynamic audiences down to the product variation level.', 'woocommerce-google-adwords-conversion-tracking-tag' ), esc_html__( 'When enabling this setting, you also need to upload product variations to your catalogs.', 'woocommerce-google-adwords-conversion-tracking-tag' ) ],
            'impact'      => esc_html__( 'low', 'woocommerce-google-adwords-conversion-tracking-tag' ),
            'setup_link'  => Documentation::get_link( 'variations_output' ),
            'since'       => 1672895375,
        ];
    }

}