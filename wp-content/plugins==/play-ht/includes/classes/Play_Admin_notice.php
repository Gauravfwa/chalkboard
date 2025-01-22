<?php namespace Wordpress\Play;

class Play_Admin_notice extends Component {

	/**
	 * Play notices.
	 * @var array
	 */
	private static $notices = [];

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		self::$notices = get_option( 'play_admin_notices', [] );

		add_action( 'plugins_loaded', [ &$this, 'print_styles' ] );
	}

	public function single_article_admin_notice() {
		WPPP_view(
			'back-end/notice',
			[
				'notice_text'  => __( 'The Article is sent successfully, it\'s being converted.', 'play-ht' ),
				'notice_class' => 'notice-info',
				'dismiss'      => true,
			]
		);
	}

	public function print_styles() {
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_print_styles', [ &$this, 'add_notices' ] );
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {
		update_option( 'play_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = [];
	}

	/**
	 * Add notices + styles if needed.
	 */
	public function add_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				add_action( 'admin_notices', [ $this, 'print_play_notices' ] );
			}
		}
	}

	/**
	 * Output any stored custom notices.
	 */
	public function print_play_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				WPPP_view(
					'back-end/notice',
					[
						'notice' => $notice,
					]
				);
			}
		}
	}

}
