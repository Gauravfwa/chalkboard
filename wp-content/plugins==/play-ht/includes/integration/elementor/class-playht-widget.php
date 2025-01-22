<?php

namespace Play_HT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Playht_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'playht';
	}

	public function get_title() {
		return __( 'Play.ht', 'play-ht' );
	}

	public function get_icon() {
		return 'eicon-play';
	}

	public function get_categories() {
		return [ 'general' ];
	}

	public function get_script_depends() {
		return [ 'jquery', 'playht-pageplayer-plugin', 'playht-pageplayer' ];
	}

	public function get_style_depends() {
		return [ 'playht-pageplayer-plugin', 'playht-pageplayer' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'player_section',
			[
				'label' => __( 'Player', 'play-ht' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		if ( ! playht_has_audio( get_the_ID() && \Elementor\Plugin::$instance->preview->is_preview_mode() ) ) {
			$this->add_control(
				'no_audio_message',
				[
					'label'      => __( 'This post has no audio.', 'plugin-name' ),
					'show_label' => false,
					'type'       => \Elementor\Controls_Manager::RAW_HTML,
					'raw'        => $this->no_audio_message(),
					'separator'  => 'after',
				]
			);
		}

		$this->add_control(
			'config',
			[
				'label'   => __( 'Player settings', 'play-ht' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'default' => __( 'Default', 'play-ht' ),
					'custom'  => __( 'Custom', 'play-ht' ),
				],
				'default' => 'default',
			]
		);

		$this->add_control(
			'width',
			[
				'label'      => __( 'Width', 'play-ht' ),
				'type'       => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors'  => [
					'{{WRAPPER}} #playht-iframe-wrapper iframe' => 'width: {{SIZE}}{{UNIT}} !important;',
				],
				'condition'  => [
					'config' => 'custom',

				],
			]
		);

		$this->add_control(
			'title',
			[
				'label'     => __( 'Title', 'play-ht' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Audio', 'play-ht' ),
				'condition' => [
					'config' => 'custom',
				],
			]
		);

		$this->add_control(
			'message',
			[
				'label'     => __( 'Message', 'play-ht' ),
				'type'      => \Elementor\Controls_Manager::TEXT,
				'default'   => __( 'Hello', 'play-ht' ),
				'condition' => [
					'config' => 'custom',
				],
			]
		);

		$this->add_control(
			'alignment',
			[
				'label'     => __( 'Alignment', 'play-ht' ),
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [
						'title' => __( 'Left', 'plugin-domain' ),
						'icon'  => 'fa fa-align-left',
					],
					'center'     => [
						'title' => __( 'Center', 'plugin-domain' ),
						'icon'  => 'fa fa-align-center',
					],
					'flex-end'   => [
						'title' => __( 'Right', 'plugin-domain' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'default'   => 'center',
				'toggle'    => true,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} #playht-iframe-wrapper' => 'justify-content: {{alignment}} !important;',
				],
				'condition' => [
					'config' => 'custom',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$post_id = get_the_ID();
		if ( ! playht_has_audio( $post_id ) && is_admin() ) {
			echo '<p>' . esc_html_e( 'This post has no audio.', 'play-ht' ) . '</p>';
		}

		$settings = $this->get_settings_for_display();

		// Show player.
		$player_config = $this->get_player_config( $settings );
		echo playht_player( $player_config );
	}

	private function no_audio_message() {
		ob_start();
		?>
		<p style="margin-bottom: 12px;">
			<?php esc_html_e( 'This post has no audio. Please add an audio from post list.', 'play-ht' ); ?>
		</p>
		<a href="<?php echo esc_attr( $this->get_add_audio_link() ); ?>" target="_blank">
			<?php esc_html_e( 'Add Audio', 'play-ht' ); ?>
		</a>
		<?php
		return ob_get_clean();
	}

	private function get_add_audio_link() {
		return add_query_arg(
			[
				's'         => get_the_title( get_the_ID() ),
				'action'    => '-1',
				'post_type' => get_post_type( get_the_ID() ),
			],
			admin_url( 'edit.php' )
		);
	}

	private function get_player_config( $settings ) {
		// Get config for the player.
		$user_data            = maybe_unserialize( get_option( 'wppp_play_user_data' ) );
		$download_button_text = $user_data['epconf']['download_button_text'];

		$player_config = [
			'download_button_text' => $download_button_text,
			'alignment'            => $settings['alignment'],
			'width'                => '100%',
			'height'               => '90px', // Iframe size, Player size will not change.
		];

		if ( 'custom' === $settings['config'] ) {
			$player_config['width']   = $settings['width']['size'] . $settings['width']['unit'];
			$player_config['title']   = $settings['title'];
			$player_config['message'] = $settings['message'];
		} else {
			$player_config['title']   = $user_data['epconf']['title'];
			$player_config['message'] = $user_data['epconf']['message'];
		}

		return $player_config;
	}

}
