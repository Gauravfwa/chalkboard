<?php

/**
 * SpecialOccasionBanner Class
 *
 * This class is responsible for displaying a special occasion banner in the WordPress admin.
 *
 * @package YourVendor\SpecialOccasionPlugin
 *
 * @since 7.3.18
 */
class Rex_Feed_Special_Occasion_Banner {

	/**
	 * The occasion identifier.
	 *
	 * @var string
	 *
	 * @since 7.3.18
	 */
	private $occasion;

	/**
	 * The start date and time for displaying the banner.
	 *
	 * @var int
	 *
	 * @since 7.3.18
	 */
	private $start_date;

	/**
	 * The end date and time for displaying the banner.
	 *
	 * @var int
	 *
	 * @since 7.3.18
	 */
	private $end_date;

	/**
	 * Constructor method for SpecialOccasionBanner class.
	 *
	 * @param string $occasion The occasion identifier.
	 * @param string $start_date The start date and time for displaying the banner.
	 * @param string $end_date The end date and time for displaying the banner.
	 *
	 * @since 7.3.18
	 */
	public function __construct( $occasion, $start_date, $end_date ) {
		$this->occasion   = "rex_feed_{$occasion}";
		$this->start_date = strtotime( $start_date );
		$this->end_date   = strtotime( $end_date );
	}

	/**
	 * Controls the initialization of certain admin-related functionalities based on conditions.
	 * It checks the current screen, defined allowed screens, product feed version availability,
	 * and date conditions to determine whether to display a banner and enqueue styles.
	 *
	 * @since 7.3.18
	 */
	public function init() {
		$current_date_time = current_time( 'timestamp' );

		if (
			'hidden' !== get_option( $this->occasion, '' )
			&& !defined( 'REX_PRODUCT_FEED_PRO_VERSION' )
			&& ( $current_date_time >= $this->start_date && $current_date_time <= $this->end_date )
		) {
			// Add styles
			add_action( 'admin_head', [ $this, 'enqueue_css' ] );
			// Hook into the admin_notices action to display the banner
			add_action( 'admin_notices', [ $this, 'display_banner' ] );
		}
	}
    

	/**
	 * Displays the special occasion banner if the current date and time are within the specified range.
	 *
	 * @since 7.3.18
	 */
	public function display_banner() {
		$screen          = get_current_screen();
		$allowed_screens = [ 'dashboard', 'plugins', 'product-feed' ];
        $time_remaining  = $this->end_date - current_time( 'timestamp' );

		if ( in_array( $screen->base, $allowed_screens ) || in_array( $screen->parent_base, $allowed_screens ) || in_array( $screen->post_type, $allowed_screens ) || in_array( $screen->parent_file, $allowed_screens ) ) {
        echo '<input type="hidden" id="rexfeed_special_occasion" name="rexfeed_special_occasion" value="'.$this->occasion.'">';
        ?>

            <!-- Name: Christmas Notification Banner -->

            <div class="rex-feed-tb__notification" id="rex_deal_notification">
                <div class="rex-feed-tb__container">
                    <div class="rex-feed-tb__content-area">

                        <div class="rex-feed-tb__image p-8 christmas">
                            <figure>
                                <img src="<?php echo plugin_dir_url( __FILE__ ) . './assets/icon/christmas-logo.webp' ; ?>" alt=" Merry Christmas" />
                            </figure>
                        </div>

                        <div class="rex-feed-tb__image twenty-five-pro-percent-logo ">
                            <figure>
                                <img src="<?php echo plugin_dir_url( __FILE__ ) . './assets/icon/christmas-wpfm.webp' ; ?>" alt="25% off"  />
                            </figure>
                        </div>
                        <!-- .rex-feed-tb__image end -->

                        <div class="rex-feed-tb__btn-area">
                            <a href="<?php echo esc_url('https://rextheme.com/best-woocommerce-product-feed/pricing'); ?>" role="button" class="rex-feed-tb__btn pfm-claim" target="_self">
                                Get <span class="rex-feed-tb__stroke-font">25%</span> OFF
                            </a>
                        </div>
                        <!-- .rex-feed-tb__btn-area end -->
                        
                    </div>
                    <!-- .rex-feed-tb__content-area end -->
                </div>

                <div class="rex-feed-tb__cross-top" id="rex_deal_close">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/cross-top.php'; ?>
                </div>

            </div>
            <!-- .rex-feed-tb-notification end -->

            <script>
                rexfeed_deal_countdown_handler();
                /**
                 * Handles count down on deal notice
                 *
                 * @since 7.3.18
                 */
                function rexfeed_deal_countdown_handler() {
                    // Pass the calculated time remaining to JavaScript
                    let timeRemaining = <?php echo $time_remaining;?>;

                    // Update the countdown every second
                    setInterval(function() {
                        const daysElement = document.getElementById('rex-feed-tb__days');
                        const hoursElement = document.getElementById('rex-feed-tb__hours');
                        const minutesElement = document.getElementById('rex-feed-tb__mins');
                        //const secondsElement = document.getElementById('seconds');

                        timeRemaining--;

                        if ( daysElement && hoursElement && minutesElement ) {
                            // Decrease the remaining time

                            // Calculate new days, hours, minutes, and seconds
                            let days = Math.floor(timeRemaining / (60 * 60 * 24));
                            let hours = Math.floor((timeRemaining % (60 * 60 * 24)) / (60 * 60));
                            let minutes = Math.floor((timeRemaining % (60 * 60)) / 60);
                            //let seconds = timeRemaining % 60;

                            // Format values with leading zeros
                            days = (days < 10) ? '0' + days : days;
                            hours = (hours < 10) ? '0' + hours : hours;
                            minutes = (minutes < 10) ? '0' + minutes : minutes;
                            //seconds = (seconds < 10) ? '0' + seconds : seconds;

                            // Update the HTML
                            daysElement.textContent = days;
                            hoursElement.textContent = hours;
                            minutesElement.textContent = minutes;
                        }
                        // Check if the countdown has ended
                        if (timeRemaining <= 0) {
                            rexfeed_hide_deal_notice();
                        }
                    }, 1000); // Update every second
                }

                document.getElementById( 'rex_deal_close' ).addEventListener( 'click', rexfeed_hide_deal_notice );

                /**
                 * Hide deal notice and save parameter to keep it hidden for future
                 *
                 * @since 7.3.2
                 */
                function rexfeed_hide_deal_notice() {
                    document.getElementById( 'rex_deal_notification' ).style.display = 'none';
                    const payload = { occasion: document.getElementById( 'rexfeed_special_occasion' )?.value }

                    wpAjaxHelperRequest( 'rex-feed-hide-deal-notice', payload );
                }
            </script>

            <?php
		}
	}

	/**
	 * Adds internal CSS styles for the special occasion banners.
	 *
	 * @since 7.3.18
	 */
	public function enqueue_css() {
        $plugin_dir_url = plugin_dir_url(__FILE__ );
		?>
		<style type="text/css">
            /* notification var css */

            @font-face {
                font-family: "LexendDeca";
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/LexendDeca-Bold.woff2"; ?>) format("woff2"), url(<?php echo "{$plugin_dir_url}assets/fonts/LexendDeca-Bold.woff"; ?>) format("woff");
                font-weight: 700;
                font-style: normal;
                font-display: swap;
            }

            .rex-feed-tb__notification {
                position: relative;
                width: calc(100% - 20px);
                background-image: url(<?php echo "{$plugin_dir_url}assets/icon/christmas-bg.webp"; ?>);
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                object-fit: cover;
                background-color: #100E1A;
                z-index: 1111;
                margin-top: 50px;
                -webkit-animation-duration: 1s;
                animation-duration: 1s;
                -webkit-animation-fill-mode: both;
                animation-fill-mode: both;
                -webkit-animation-name: goDown;
                animation-name: goDown;
            }

            .rex-feed-tb__container {
                margin: 0 auto;
                padding: 0 15px;
                position: relative;
            }

            .rex-feed-tb__container {
                max-width: 1430px;
            }

            .rex-feed-tb__content-area {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .rex-feed-tb__image h4 {
                font-family: "LexendDeca";
                text-transform: capitalize;
                font-size: 24px;
                font-style: normal;
                font-weight: 700;
                line-height: 1.4;
                color: #E5F7FF;
                margin: 0;
            }

            @media only screen and (max-width: 1199px) {
                .rex-feed-tb__image h4 {
                    font-size: 20px;
                }
            }


            @media only screen and (max-width: 991px) {
                .rex-feed-tb__image h4 {
                    font-size: 14px;
                }
            }

            .rex-feed-tb__image h4 span {
                display: block;
                font-weight: 500;
                color: #AECF9F;
                text-transform: none;
            }

            .rex-feed-tb__image figure {
                margin: 0;
            }

            .rex-feed-tb__image figure img {
                width: auto;
                max-width: 100%;
                height: auto;
                object-fit: cover;
            }

            .rex-feed-tb__image.p-8 {
                padding: 5px 0;
            }

            .rex-feed-tb__image.christmas img {
                width: 100%;
                max-width: 275px;
            }

            .rex-feed-tb__image.twenty-five-pro-percent-logo img {
                width: 100%;
                max-width: 314px;
            }

            .rex-feed-tb__btn-area {
                display: flex;
                align-items: flex-end;
                justify-content: flex-end;
            }


            .rex-feed-tb__btn {
                position: relative;
                font-family: "LexendDeca";
                font-size: 20px;
                font-style: normal;
                font-weight: 700;
                line-height: normal;
                color: #FFF;
                text-align: center;
                border-radius: 15px;
                background: #216DF0;
                padding: 14px 24px;
                display: inline-block;
                cursor: pointer;
                text-transform: uppercase;
                text-decoration: none;
                transition: all 0.3s ease;
            }

            .rex-feed-tb__btn:hover {
                background-color: #201cfe;
                color: #ffffff;
            }

            .rex-feed-tb__btn:active {
                color: #ffffff;
            }

            .rex-feed-tb__btn.pfm-claim {
                background-color: #216DF0;
            }

            .rex-feed-tb__btn.pfm-claim:hover {
                background-color: #00B4FF;
                color: #ffffff;
                box-shadow: none;
            }

            .rex-feed-tb__stroke-font {
                font-size: 26px;
                font-weight: 700;
            }

            .rex-feed-tb__cross-top {
                position: absolute;
                right: -12px;
                top: -12px;
                cursor: pointer;
            }

            @media only screen and (max-width: 1399px) {
                .rex-feed-tb__content h4 {
                    font-size: 23px;
                }
            }
            @media only screen and (max-width: 1199px) {
                .rex-feed-tb__container {
                    max-width: 1010px;
                }
                .rex-feed-tb__image.christmas img {
                    max-width: 190px;
                }
                .rex-feed-tb__image.twenty-five-pro-percent-logo {
                    max-width: 240px;
                }
                .rex-feed-tb__btn.pfm-claim,
                .rex-feed-tb__btn {
                    font-size: 15px;
                    line-height: 20px;
                    padding: 10px 16px;
                    border-radius: 10px;
                    font-weight: 400;
                }
                .rex-feed-tb__content h4 {
                    font-size: 22px;
                }
                .rex-feed-tb__stroke-font {
                    font-size: 20px;
                }
            }

            @media only screen and (max-width: 991px) {
                .rex-feed-tb__container {
                    max-width: 760px;
                }

                .rex-feed-tb__notification {
                    margin-top: 80px;
                }

                .rex-feed-tb__image.christmas img {
                    max-width: 140px;
                }
                .rex-feed-tb__image.twenty-five-pro-percent-logo {
                    max-width: 180px;
                }
                .rex-feed-tb__btn {
                    font-size: 14px;
                    line-height: 18px;
                    padding: 9px 10px;
                    border-radius: 8px;
                    margin-left: 0;
                }
                .rex-feed-tb__stroke-font {
                    font-size: 18px;
                }
            }

            @media only screen and (max-width: 767px) {
                .rex-feed-tb__image.p-8 {
                    padding: 0;
                }
                .rex-feed-tb__notification {
                    margin-top: 60px;
                }

                .rex-feed-tb__image.christmas img {
                    max-width: 170px;
                }
                .rex-feed-tb__image.twenty-five-pro-percent-logo {
                    max-width: 185px;
                }
                .rex-feed-tb__notification {
                    padding: 30px 0;
                    background-position: 14%;
                }
                .rex-feed-tb__stroke-font {
                    font-size: 22px;
                }

                .rex-feed-tb__cross-top {
                    right: -4px;
                }

                .rex-feed-tb__content-area {
                    flex-flow: column;
                    gap: 12px;
                    text-align: center;
                }
                .rex-feed-tb__content h4 {
                    font-size: 24px;
                    line-height: 1.2;
                }
                .rex-feed-tb__btn-area {
                    justify-content: center;
                }
                .rex-feed-tb__btn {
                    font-size: 14px;
                    padding: 11px 16px;
                    border-radius: 8px;
                }
            }

            /* ANIMATION for go down */
            @-webkit-keyframes goDown {
                0% {
                    -webkit-transform: translate3d(0, -100%, 0);
                    transform: translate3d(0, -100%, 0);
                }
                100% {
                    -webkit-transform: none;
                    transform: none;
                }
            }
            
            @keyframes goDown {
                0% {
                    -webkit-transform: translate3d(0, -100%, 0);
                    transform: translate3d(0, -100%, 0);
                }
                100% {
                    -webkit-transform: none;
                    transform: none;
                }
            }

		</style>

		<?php
	}
}