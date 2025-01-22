<?php
namespace Wordpress\Play;

class Assets_Handler extends Component {
	public $suffix = '.min';

	protected function init() {
		parent::init();
		add_action( 'wp_enqueue_scripts', [ $this, 'register_public_assets' ], 5 );
		add_action( 'wp_enqueue_scripts', [ $this, 'localize_public_scripts' ], 10 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_assets' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'localize_admin_scripts' ], 10 );
		add_action( 'wp_head', [ $this, 'prefetch_dns' ] );

		if ( $this->is_script_debug() ) {
			$this->suffix = '';
		}
	}

	public static function is_script_debug() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	public function prefetch_dns() {
		?>
		<link rel="preconnect" href="//www.googletagmanager.com/">
		<link rel="preconnect" href="//s3.amazonaws.com/">
		<link rel="preconnect" href="//play.ht/">
		<link rel="preconnect" href="//static.play.ht/">
		<link rel="preconnect" href="//a.play.ht/">
		<link rel="preconnect" href="//media.play.ht/">
		<link rel="dns-prefetch" href="//www.googletagmanager.com/">
		<link rel="dns-prefetch" href="//s3.amazonaws.com/">
		<link rel="dns-prefetch" href="//play.ht/">
		<link rel="dns-prefetch" href="//static.play.ht/">
		<link rel="dns-prefetch" href="//a.play.ht/">
		<link rel="dns-prefetch" href="//media.play.ht/">
		<?php
	}

	public function register_public_assets() {
		$this->register_public_scripts();
		$this->register_public_styles();
	}

	public function localize_public_scripts() {

		$podcast_data = maybe_unserialize( get_post_meta( get_the_ID(), 'play_podcast_data', true ) );

		$current_post = get_post( get_the_ID() );

		if ( ! isset( $current_post->post_content ) ) {
			return;
		}

		unset( $current_post->post_content );

		$image_data = '';

		if ( has_post_thumbnail() ) {
			$image_data = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
		}

		wp_localize_script(
			'playht-pageplayer',
			'image_page_player',
			[
				'image_params' => $image_data,
			]
		);

		wp_localize_script(
			'playht-pageplayer',
			'wppp_page_player',
			[
				'current_article_play_data' => $podcast_data,
				'current_article_data'      => $current_post,
			]
		);

		wp_localize_script(
			'playht-pageplayer',
			'wppp_user_data',
			[
				'user_id'                     => get_option( 'wppp_blog_userId' ),
				'app_id'                      => get_option( 'wppp_blog_appId' ),
				'ajax_url'                    => admin_url( 'admin-ajax.php' ),

				'playhtButtonSwitch'          => isset( $podcast_data['audio_status'] ) && $podcast_data['audio_status'] == 2 ? get_option( 'playht_button_switch', '1' ) : false,
				'playhtListenbuttonSwitch'    => playht_has_listen_button(),
				'playHtbuttonWLabel'          => get_option( 'playht_button_wlabel', '' ),
				'playHtcolor_backgrund'       => get_option( 'playHtcolor_backgrund', '#222' ),
				'playHttextColor'             => get_option( 'playHttextColor', '#fff' ),
				'FielddesktopPositionID'      => get_option( 'FielddesktopPositionID', 'right' ),
				'FieldmobilePositionID'       => get_option( 'FieldmobilePositionID', 'right' ),
				'playHtDarkMode'              => get_option( 'playHtDarkMode', '' ),
				'playHtPlayerItemsColor'      => get_option( 'playHtPlayerItemsColor', '#fff' ),
				'playHtPlayerTextColor'       => get_option( 'playHtPlayerTextColor', '#fff' ),
				'playHtPlayerBackgroundColor' => get_option( 'playHtPlayerBackgroundColor', '#222' ),
				'fullScreenMobEnabledID'      => get_option( 'fullScreenMobEnabledID', '1' ),
				'playHtListencolor_backgrund' => get_option( 'playHtListencolor_backgrund', '#222' ),
				'playHtListenBorderColor'     => get_option( 'playHtListenBorderColor', '#222' ),
				'playHtListenBorderRadius'    => get_option( 'playHtListenBorderRadius', '2' ),
				'playHtListenText'            => get_option( 'playHtListenText', 'Listen' ),
				'playHtListentextColor'       => get_option( 'playHtListentextColor', '#fff' ),
			]
		);

		wp_localize_script(
			'playht-pageplayer',
			'wppp_player_images',
			[
				'close'                  => WPPP_IMAGES_PATH . 'close.png',
				'loader'                 => WPPP_IMAGES_PATH . 'loader.gif',
				'play_btn'               => WPPP_IMAGES_PATH . 'play-btn.png',
				'pause_btn'              => WPPP_IMAGES_PATH . 'pause-btn.png',
				'pageplayer_placeholder' => WPPP_IMAGES_PATH . 'pageplayer_placeholder.png',
			]
		);
	}

	public function register_public_scripts() {
		wp_register_script( 'playht-pageplayer-plugin', 'https://static.play.ht/playht-pageplayer-plugin.js', [], wppp_version(), true );

		wp_register_script( 'playht-pageplayer', WPPP_JS_PATH . 'pageplayer.js', [ 'playht-pageplayer-plugin' ], wppp_version(), true );
	}

	public function register_public_styles() {
		wp_register_style( 'playht-pageplayer-plugin', 'https://static.play.ht/playht-pageplayer-plugin.css', [], wppp_version() );

		wp_register_style( 'playht-pageplayer', WPPP_CSS_PATH . 'public.min.css', [], wppp_version() );
	}

	public function register_admin_assets() {
		$this->register_admin_scripts();
		$this->register_admin_styles();
	}

	public function localize_admin_scripts() {

		$podcast_data = false;

		if ( isset( $_GET['post'] ) ) {
			$podcast_data = maybe_unserialize( get_post_meta( $_GET['post'], 'play_podcast_data', true ) );
		}

		wp_localize_script(
			'save_conversion_data',
			'wppp_images',
			[
				'settings'           => WPPP_IMAGES_PATH . 'settings.png',
				'checked'            => WPPP_IMAGES_PATH . 'wp-checked.png',
				'cancel'             => WPPP_IMAGES_PATH . 'wp-cancel.png',
				'warning'            => WPPP_IMAGES_PATH . 'wp-warning.png',
				'audio'              => WPPP_IMAGES_PATH . 'audio-grey.png',
				'no_audio'           => WPPP_IMAGES_PATH . 'no-audio-grey.png',
				'audio_error'        => WPPP_IMAGES_PATH . 'audio_error.png',
				'shortcodeExplainer' => WPPP_IMAGES_PATH . 'shortcode-explainer.gif',
				'no_credits'         => WPPP_IMAGES_PATH . 'no_credits.png',
				'close'              => WPPP_IMAGES_PATH . 'close.png',
			]
		);

		if ( get_post_type() == 'post' ) {
			wp_localize_script(
				'tinymce_ep_button',
				'tinymce_ep_obj',
				[
					'post_data'  => $podcast_data,
					'audio_icon' => WPPP_IMAGES_PATH . 'audio-grey.png',
				]
			);
		}

		if ( 1 == get_option( 'wppp_status_flag' ) ) {
			wp_localize_script(
				'retrieve_user_data',
				'wppp_retrieve',
				[
					'user_id'  => get_option( 'wppp_blog_userId' ),
					'app_id'   => get_option( 'wppp_blog_appId' ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				]
			);
		}
	}

	public function register_admin_scripts() {

		wp_register_script( 'firebase_script', 'https://www.gstatic.com/firebasejs/4.2.0/firebase.js', [], wppp_version(), true );

		wp_register_script( 'helpers', WPPP_JS_PATH . 'helpers.js', [], wppp_version(), true );

		wp_register_script( 'promise_polyfill', WPPP_JS_PATH . 'promise.polyfill.js', [], wppp_version(), true );

		wp_register_script( 'dashboard_wppp_chart', 'https://code.highcharts.com/highcharts.js', [], wppp_version(), true );

		wp_register_script( 'dashboard_wppp_exporting', 'https://code.highcharts.com/modules/exporting.js', [], wppp_version(), true );

		wp_register_script( 'dashboard_wppp_export_data', 'https://code.highcharts.com/modules/export-data.js', [], wppp_version(), true );

		wp_register_script( 'dashboard_wppp_access', 'https://code.highcharts.com/modules/accessibility.js', [], wppp_version(), true );

		wp_register_script( 'jquery_dropdown_wpplay', WPPP_JS_PATH . 'jquery-dropdown.js', [], wppp_version(), true );

		wp_register_script( 'jscolor_wpplay', WPPP_JS_PATH . 'jscolor.js', [], wppp_version(), true );

		wp_register_script( 'regenerator_runtime', WPPP_JS_PATH . 'regenerator_runtime.js', [], wppp_version(), true );

		wp_register_script( 'editor_script', 'https://d1553uxug7aswy.cloudfront.net/js/editor.min.2.9.9.js', [], wppp_version(), true );

		wp_register_script( 'sweetalert', WPPP_JS_PATH . 'sweetalert.min.js', [], wppp_version(), true );

		wp_register_script( 'switchery', WPPP_JS_PATH . 'switchery.js', [], wppp_version(), true );

		wp_register_script(
			'analytics',
			WPPP_JS_PATH . 'analytics.js',
			[
				'jquery',
			],
			wppp_version(),
			true
		);


		wp_register_script(
			'welcome_page_voices',
			WPPP_JS_PATH . 'welcome_page_voices.js',
			[
				'jquery',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'add_blog_domain',
			WPPP_JS_PATH . 'add_blog_domain.js',
			[
				'jquery',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'referral_theme',
			WPPP_JS_PATH . 'referral_theme.js',
			[
				'jquery',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'play_ht_settings',
			WPPP_JS_PATH . 'playhtsettings.js',
			[
				'wp-color-picker',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'initialize_firebase',
			WPPP_JS_PATH . 'initialize_firebase.js',
			[
				'jquery',
				'firebase_script',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'tinymce_ep_button',
			WPPP_JS_PATH . 'tinymce_ep_button.js',
			[
				'jquery',
				'sweetalert',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'retrieve_user_data',
			WPPP_JS_PATH . 'retrieve_user_data.js',
			[
				'jquery',
				'firebase_script',
				'helpers',
			],
			wppp_version(),
			true
		);

		wp_register_script(
			'dashboard_wppp',
			WPPP_JS_PATH . 'dashboard-wpp.js',
			[
				'jquery-ui-datepicker',
				'dashboard_wppp_chart',
				'dashboard_wppp_exporting',
				'dashboard_wppp_export_data',
				'dashboard_wppp_access',
				'jquery_dropdown_wpplay',
				'jscolor_wpplay',
				'analytics',
				'retrieve_user_data',
			],
			wppp_version(),
			true
		);

		global $wp_version;

		if ( version_compare( $wp_version, '5.0' ) < 0 ) {
			wp_register_script(
				'react',
				'https://unpkg.com/react@16.8.6/umd/react.production.min.js',
				[],
				wppp_version(),
				true
			);

			wp_register_script(
				'react-dom',
				'https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js',
				[],
				wppp_version(),
				true
			);
		}

		wp_register_script(
			'save_conversion_data',
			WPPP_JS_PATH . 'save_conversion_data.js',
			[
				'jquery',
				'react',
				'react-dom',
				'sweetalert',
				'regenerator_runtime',
				'firebase_script',
				'analytics',
				'editor_script',
			],
			wppp_version(),
			true
		);

	}

	public function register_admin_styles() {
		wp_register_style( 'playht-backend', WPPP_CSS_PATH . 'admin.min.css', [], wppp_version() );
		wp_register_style( 'sweetalert', WPPP_CSS_PATH . 'sweetalert.min.css', [], wppp_version() );
	}
}
