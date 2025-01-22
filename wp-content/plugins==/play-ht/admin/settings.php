<?php
@ini_set( 'display_errors', 0 );
error_reporting( 0 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/// by default value for CPT settings

add_action( 'admin_init', 'playht_type_switchsettings_init' );

function playht_type_switchsettings_init() {

	if ( false === get_option( 'playht_type_switch' ) ) {
		update_option(
			'playht_type_switch',
			[
				'post' => 1,
				'page' => 1,
			]
		);
	}

	add_option( 'playHt_articleplayer_switch', '1' );
	add_option( 'playht_button_switch', '0' );
	add_option( 'playht_Listenbutton_switch', '0' );
	add_option( 'playht_listen_button_type', 'pp' );
	add_option( 'playHtDarkMode', '0' );
}

// PlayHt Setting Page
add_action( 'admin_init', 'playHt_SettingPage' );
function playHt_SettingPage() {

	// Sections Floating
	add_settings_section( 'playSettings_area_floatingButton', '', 'playHtfloatingButton', 'play-welcome-page-f' );
	// Sections Floating
	add_settings_section( 'playSettings_area_ListenButton', '', 'playHtListenButton', 'play-welcome-page-l' );
	// Sections iframe player
	add_settings_section( 'playSettings_area_iframePlayer', '', 'playHtIframePlayer', 'play-welcome-page-i' );

	add_settings_section( 'playSettings_area_articleplayer', '', '', 'play-welcome-page-articleplayer' );

	add_settings_section( 'playSettings_area_PostType', '', 'playHtPostType', 'play-post-type' );

	// Settings Field Post Type
	add_settings_field( 'playht_type_switch', '', 'playHtPostTypecheck', 'play-post-type', 'playSettings_area_PostType' );

	// Settings Field Floating
	add_settings_field( 'playht_button_switch', 'Enable button', 'playHtButtonSwitch', 'play-welcome-page-f', 'playSettings_area_floatingButton' );
	add_settings_field( 'playht_button_wlabel', 'White label', 'playHtbuttonWLabel', 'play-welcome-page-f', 'playSettings_area_floatingButton' );
	add_settings_field( 'playHtcolor_backgrund', 'Background color', 'playHtTextcolor_backgrund', 'play-welcome-page-f', 'playSettings_area_floatingButton' );
	add_settings_field( 'playHttextColor', 'Icon color', 'playHttextColor', 'play-welcome-page-f', 'playSettings_area_floatingButton' );
	add_settings_field( 'FielddesktopPositionID', 'Position on Desktop', 'desktopPositionCallBack', 'play-welcome-page-f', 'playSettings_area_floatingButton' );
	add_settings_field( 'FieldmobilePositionID', 'Position on Mobile', 'mobilePositionCallBack', 'play-welcome-page-f', 'playSettings_area_floatingButton' );

	// Settings Field Listen
	add_settings_field( 'playht_Listenbutton_switch', 'Enable button', 'playHtListenbuttonSwitch', 'play-welcome-page-l', 'playSettings_area_ListenButton' );
	add_settings_field( 'playht_listenbutton_type', 'Player type', 'playht_listen_button_type', 'play-welcome-page-l', 'playSettings_area_ListenButton' );
	add_settings_field( 'playHtListenText', 'Text', 'playHtListenTextCallback', 'play-welcome-page-l', 'playSettings_area_ListenButton' );
	add_settings_field( 'playHtListencolor_backgrund', 'Background color', 'playHtListenTextcolor_backgrundCallback', 'play-welcome-page-l', 'playSettings_area_ListenButton' );
	add_settings_field( 'playHtListentextColor', 'Text color', 'playHtListentextColorCallback', 'play-welcome-page-l', 'playSettings_area_ListenButton' );
	add_settings_field( 'playHtListenBorderColor', 'Border color', 'playHtListenBorderColorCallback', 'play-welcome-page-l', 'playSettings_area_ListenButton' );
	add_settings_field( 'playHtListenBorderRadius', 'Border radius', 'playHtListenBorderRadiusCallback', 'play-welcome-page-l', 'playSettings_area_ListenButton' );

	// Settings Field iframe player
	add_settings_field( 'playHtDarkMode', 'Enable Dark mode', 'callPlayHtDarkMode', 'play-welcome-page-i', 'playSettings_area_iframePlayer' );
	add_settings_field( 'playHtPlayerItemsColor', 'Items Color', 'playHtplayerItemsColorCallBack', 'play-welcome-page-i', 'playSettings_area_iframePlayer' );
	add_settings_field( 'playHtPlayerTextColor', 'Text Color', 'playHtPlayerTextColorCallBack', 'play-welcome-page-i', 'playSettings_area_iframePlayer' );
	add_settings_field( 'playHtPlayerBackgroundColor', 'Player BackgroundColor', 'playHtPlayerBackgroundColorCallBack', 'play-welcome-page-i', 'playSettings_area_iframePlayer' );
	add_settings_field( 'fullScreenMobEnabledID', 'Enabled Full screen Player on Mobile', 'fullScreenMobEnabledCallBack', 'play-welcome-page-i', 'playSettings_area_iframePlayer' );

	add_settings_field( 'playHt_articleplayer_switch', '', 'callBackplayHt_articleplayer_switch', 'play-welcome-page-articleplayer', 'playSettings_area_articleplayer', [ 'class' => 'playHt_articleplayer_switch' ] );

	$settingsArrayf = [
		'playht_button_wlabel',
		'playHtcolor_backgrund',
		'playHttextColor',
		'FielddesktopPositionID',
		'FieldmobilePositionID',
	];
	$settingsArrayl = [
		'playHtListenText',
		'playHtListencolor_backgrund',
		'playHtListentextColor',
		'playHtListenBorderColor',
		'playHtListenBorderRadius',

	];
	$settingsArrayi = [
		'playHtDarkMode',
		'playHtPlayerItemsColor',
		'playHtPlayerTextColor',
		'playHtPlayerBackgroundColor',
		'fullScreenMobEnabledID',
	];

	$args = [
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => null,
	];

	register_setting(
		'playSettingsArea-f',
		'playht_button_switch',
		[
			'type'    => 'string',
			'default' => '0',
		]
	);

	foreach ( $settingsArrayf as $setting1 ) {
		register_setting( 'playSettingsArea-f', $setting1, $args );
	}

	register_setting(
		'playPostArea',
		'playht_type_switch',
		[
			'type'    => 'string',
			'default' => '1',
		]
	);

	foreach ( $settingsArrayl as $setting2 ) {
		register_setting( 'playSettingsArea-l', $setting2, $args );
	}

	register_setting(
		'playSettingsArea-l',
		'playht_Listenbutton_switch',
		[
			'type'    => 'string',
			'default' => '0',
		]
	);

	register_setting(
		'playSettingsArea-l',
		'playht_listen_button_type',
		[
			'type'    => 'string',
			'default' => 'pp',
		]
	);

	foreach ( $settingsArrayi as $setting3 ) {
		register_setting( 'playSettingsArea-i', $setting3, $args );
	}

	register_setting(
		'playSettingsArticlePlayerArea',
		'playHt_articleplayer_switch',
		[
			'type'    => 'string',
			'default' => '1',
		]
	);
}


// CallBacks
function playHtfloatingButton() {
	?>
	<h5 class="palyht_title_settings"><?php esc_html_e( 'Page Player Button', 'play-ht' ); ?> <small><?php esc_html_e( 'If enabled, will show a play button at the bottom of the article page and article list pages, when clicked will start the', 'play-ht' ); ?> <strong><?php esc_html_e( 'Page Player', 'play-ht' ); ?></strong> <?php esc_html_e( 'to play the current article.', 'play-ht' ); ?></small></h5>
	<div class="palyhtBodySettings floatingButtonClass">
		<div class="playPrivArea floatingButton">
			<small>Preview</small>
			<div class="inner_playPrivArea">
				<div class="overlayImg"><img src="<?php echo WPPP_URI; ?>assets/images/article_with_btn.png" alt=""></div>
				<div class="conentIco">
					<div class="play_listenfloatingBtn playht-custom-audio__playFloatingBtn" style="color: <?php echo esc_attr( get_option( 'playHttextColor', '#fff' ) ); ?>; background-color: <?php echo esc_attr( get_option( 'playHtcolor_backgrund', '#222' ) ); ?>; float:<?php echo esc_attr( get_option( 'FielddesktopPositionID', 'right' ) ); ?>; <?php
					if ( get_option( 'playht_button_switch' ) ) {
						echo 'opacity: 1';}
					?>;">
						<i class="playht-icon-headphones __play-listen-floating-btn__icon"></i>
					</div>
				</div>
			</div>
		</div>
	<?php
}

function playHtPostType() {
	?>
	<h5 class="palyht_title_settings">Custom Post types <small>Select custom post types to enable audio</small></h5>
	<div class="palyhtBodySettings floatingButtonClass">

	<?php
}

function playHtPostTypecheck() {
	?>
			<?php
			$post_type_a = get_option( 'playht_type_switch' );
			if ( $post_type_a == 1 || empty( $post_type_a ) ) {
				$args            = [
					'public' => true,
				];
				$show_post_types = get_post_types( $args, 'objects' );
				foreach ( $show_post_types  as $key => $posttypes ) {
					if ( $posttypes->name != 'attachment' ) {
						echo '<tr>';
						echo '<th scope="row">' . $posttypes->labels->name . '</th>';
						echo '<td><input id="playht_type_switch[' . $posttypes->name . ']" type="checkbox" class="js-switch" name="playht_type_switch[' . $posttypes->name . ']" value="1"  /></td>';
						echo '</tr>';
					}
				}
			} else {
				$args            = [
					'public' => true,
				];
				$show_post_types = get_post_types( $args, 'objects' );
				foreach ( $show_post_types  as $key => $posttypes ) {
					if ( $posttypes->name != 'attachment' ) {
						$option_to_check = isset( $post_type_a[ $posttypes->name ] ) ? $post_type_a[ $posttypes->name ] : '';
						echo '<tr>';
						echo '<th scope="row">' . $posttypes->labels->name . '</th>';
						echo '<td><input id="playht_type_switch[' . $posttypes->name . ']" type="checkbox" class="js-switch" name="playht_type_switch[' . $posttypes->name . ']" value="1" ' . checked( 1, $option_to_check, false ) . ' /></td>';
						echo '</tr>';
					}
				}
			}
			?>
	<?php
}


function playHtButtonSwitch() {
	echo '<input id="playht_button_switch" type="checkbox" class="js-switch" name="playht_button_switch" value="1" ' . checked( 1, get_option( 'playht_button_switch' ), false ) . ' />';
}

function playHtTextcolor_backgrund() {
	$value = get_option( 'playHtcolor_backgrund' ) ? esc_attr( get_option( 'playHtcolor_backgrund' ) ) : '#222';
	echo '<input type="text" class="color_backgrund_field" name="playHtcolor_backgrund" value="' . $value . '">';
}

function playHttextColor() {
	$value = get_option( 'playHttextColor' ) ? esc_attr( get_option( 'playHttextColor' ) ) : '#fff';
	echo '<input type="text" class="playHttextColor_field" name="playHttextColor" value="' . $value . '">';
}

function desktopPositionCallBack() {
	$selected = get_option( 'FielddesktopPositionID' ) ? esc_attr( get_option( 'FielddesktopPositionID' ) ) : 'right';
	?>
	<select name="FielddesktopPositionID" class="playHtbox_select" id="FielddesktopPositionID">
		<option value="right" <?php selected( $selected, 'right' ); ?>>Right</option>
		<option value="left" <?php selected( $selected, 'left' ); ?>>Left</option>
	</select>
	<?php
}

function mobilePositionCallBack() {
	$selected = get_option( 'FieldmobilePositionID' ) ? esc_attr( get_option( 'FieldmobilePositionID', 'right' ) ) : 'right';
	?>
	<select name="FieldmobilePositionID" class="playHtbox_select">
		<option value="left" <?php selected( $selected, 'left' ); ?>>Left</option>
		<option value="middle" <?php selected( $selected, 'middle' ); ?>>Middle</option>
		<option value="right" <?php selected( $selected, 'right' ); ?>>Right</option>
	</select>

	<?php
}


// CallBacks Listen
function playHtListenButton() {

	?>

	<h5 class="palyht_title_settings"><?php esc_html_e('Title Play Button', 'play-ht'); ?> <small><?php esc_html_e('If enabled, will show a listen button below any article title, only in the article page and articles list pages.', 'play-ht'); ?></small></h5>
	<div class="palyhtBodySettings ListenButtonClass">
		<div class="playPrivArea" id="playListenBtnID-wrapper" style="<?php echo 'pp' === get_option('playht_listen_button_type', 'pp') ? 'display:block;' : 'display:none;' ?>">
			<small><?php esc_html_e('Preview', 'play-ht'); ?></small>
			<div class="inner_playPrivArea">
				<strong><?php esc_html_e('NOVEMBER 1, 2018', 'play-ht'); ?></strong>
				<h3 class="playht_post_titleDemo"><?php esc_html_e('Hello world!', 'play-ht'); ?></h3>
				<div class="playListenBtn" id="playListenBtnID" style="
				color: <?php echo esc_attr( get_option( 'playHtListentextColor', '#fff' ) ); ?>;
				background-color: <?php echo esc_attr( get_option( 'playHtListencolor_backgrund', '#222' ) ); ?>;
				border-color: <?php echo esc_attr( get_option( 'playHtListenBorderColor', '#222' ) ); ?>;
				border-radius: <?php echo esc_attr( get_option( 'playHtListenBorderRadius', '2' ) ); ?>px;
				<?php
				if ( get_option( 'playht_Listenbutton_switch' ) ) {
					echo 'opacity: 1';}
				?>;">
					<i class="playht-icon-headphones __play-listen-floating-btn__icon"></i>
					<span class="__play-listen-btn__text"><?php esc_html_e('Listen', 'play-ht'); ?></span>
				</div>
				<p class="playhtContentDemo"><?php esc_html_e('Welcome to WordPress. This is your first post. Edit or delete it, then start writing!'); ?></p>
			</div>
		</div>

		<div class="playPrivArea"  id="playht-full-button-wrapper" style="<?php echo 'ep' === get_option('playht_listen_button_type', 'pp') ? 'display:block;' : 'display:none;' ?>">
			<small>
				<?php esc_html_e('Preview', 'play-ht'); ?>
			</small>
			<div style="background-color: rgb(221, 221, 221); margin-top: 10px; padding: 10px; border-radius: 4px; width: 450px;">
				<strong><?php esc_html_e('NOVEMBER 1, 2019', 'play-ht'); ?></strong>
				<h2><?php esc_html_e('Hello world!', 'play-ht'); ?></h2>
				<div id="playht-full-button" style="color: <?php echo esc_attr( get_option( 'playHtListentextColor', '#fff' ) ); ?>; background-color: <?php echo esc_attr( get_option( 'playHtListencolor_backgrund', '#222' ) ); ?>; border-radius: <?php echo esc_attr( get_option( 'playHtListenBorderRadius', '2' ) ); ?>px; border: 1px solid <?php echo esc_attr( get_option( 'playHtListenBorderColor', '#222' ) ); ?>; opacity: 1; width: 100%; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; height: 55px; font-size: 18px; padding-left: 14px; margin-bottom: 10px;">
					<i class="playht-icon-play-circled" style="font-size: 30px;"></i>
					<span><?php esc_html_e('Listen . 02:34', 'play-ht'); ?></span>
				</div>
				<p><?php esc_html_e('Welcome to Play. This is your first post. start writing!', 'play-ht'); ?></p>
			</div>
		</div>
	</div>
	<?php
}

function playHtListenbuttonSwitch() {
	echo '<input type="checkbox" id="playht_Listenbutton_switch" class="js-switch" name="playht_Listenbutton_switch" value="1" ' . checked( 1, get_option( 'playht_Listenbutton_switch' ), false ) . ' />';
}

function playht_listen_button_type() {
	ob_start(); ?>

	<select type="checkbox" id="playht_listen_button_type" name="playht_listen_button_type"/>
	<option value="pp" <?php selected( get_option( 'playht_listen_button_type' ), 'pp' ); ?>><?php esc_html_e('Button Only', 'play-ht'); ?></option>
	<option value="ep" <?php selected( get_option( 'playht_listen_button_type' ), 'ep' ); ?>><?php esc_html_e('Full Player', 'play-ht'); ?></option>
	</select>
	<?php
	echo ob_get_clean();
}

function playHtbuttonWLabel() {
	echo '<input type="checkbox" id="playht_button_wlabel" class="js-switch" name="playht_button_wlabel" value="1" ' . checked( 1, get_option( 'playht_button_wlabel' ), false ) . ' />';
}

function playHtListenTextCallback() {
	$value = get_option( 'playHtListenText' ) ? esc_attr( get_option( 'playHtListenText' ) ) : 'Listen';
	echo '<input type="text" class="playHtListenText_field" name="playHtListenText" value="' . $value . '" placeholder="Listen">';
}

function playHtListenTextcolor_backgrundCallback() {
	$value = get_option( 'playHtListencolor_backgrund' ) ? esc_attr( get_option( 'playHtListencolor_backgrund' ) ) : '#222';
	echo '<input type="text" class="Listencolor_backgrund_field" name="playHtListencolor_backgrund" value="' . $value . '">';
}

function playHtListentextColorCallback() {
	$value = get_option( 'playHtListentextColor' ) ? esc_attr( get_option( 'playHtListentextColor' ) ) : '#fff';
	echo '<input type="text" class="playHtListentextColor_field" name="playHtListentextColor" value="' . $value . '">';
}

function playHtListenBorderColorCallback() {
	$value = get_option( 'playHtListenBorderColor' ) ? esc_attr( get_option( 'playHtListenBorderColor' ) ) : '#222';
	echo '<input type="text" class="playHtListenBorderColor_field" name="playHtListenBorderColor" value="' . $value . '">';
}
function playHtListenBorderRadiusCallback() {
	$value = get_option( 'playHtListenBorderRadius' ) ? esc_attr( get_option( 'playHtListenBorderRadius' ) ) : 2;
	echo '<input type="range" class="BorderRadiusSlider" name="playHtListenBorderRadius" id="BorderRadiusRange" min="1" max="20" value="' . $value . '">';
}

// iframe player
function playHtIframePlayer() {
	?>

	<h5 class="palyht_title_settings">Page Player <small>Will become visible and start playing the article when either the <strong>Title Play Button</strong> or <strong>Page Player Button</strong> is clicked.</small></h5>
	<div class="palyhtBodySettings playHtIframePlayer">
	<div class="playPrivArea">
		<div class="isDesc">
			<small>Desktop Preview</small>
			<div class="inner_playPrivArea">
				<div class="playhtforDesktop">
					<div class="overlayImg"><img src="<?php echo WPPP_URI; ?>assets/images/article_with_btn.png" alt=""></div>
					<div class="playht-custom-audio-wrapper" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'background-color: rgb(47, 47, 47);' : 'background-color: #222;'; ?>">
						<audio class="playht-audio-element" src="#"></audio>
						<span class="playht-custom-audio__closeBtn" id="playht-closeBtn">
							<i class="playht-icon-cancel" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>"></i>
						</span>
						<span class="playht-custom-audio__playBtn" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff;' : ''; ?>">
							<i class="playht-icon-play-circled" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>"></i>
						</span>
						<span class="playht-custom-audio__played-time" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerTextColor', '#fff' ) . ''; ?>">00:12</span>
						<span class="playht-custom-audio__playedDiv" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>"></span>
						<span class="playht-custom-audio__total-time" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerTextColor', '#fff' ) . ''; ?>">17:00</span>
						<div class="playhtSpeed_range">
							<span class="line_r" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'background-color: #fff' : 'background-color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>">  </span>
							<span class="line_circl" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'background-color: #fff' : 'background-color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>">  </span>
							<span class="playhtRange__value" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerTextColor', '#fff' ) . ''; ?>">speed: 1.0</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="isMob">
			<small>Mobile Preview</small>
			<div class="inner_playPrivArea">

				<div class="playhtforMobile" style="<?php echo $fullScreenMobEnabledID = ( get_option( 'fullScreenMobEnabledID' ) == true ) ? 'display: block;' : ''; ?>">
					<div class="overlayImg"><img src="<?php echo WPPP_URI; ?>assets/images/mobile_page_player.png" alt=""></div>
					<div class="playht-custom-audio-wrapper--mobile" style="<?php
						echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'background-color: rgb(47, 47, 47);color: #fff' : 'background-color:' . get_option( 'playHtPlayerBackgroundColor', '#222' ) . ';color: ' . get_option( 'playHtPlayerTextColor', '#fff' ) . '';
					?>">
						<i class="topArr playht-icon-down-open" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'color: #fff' : 'color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>"></i>

						<div class="playht-custom-audio-meta" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) != 'dark' ) ? 'color: ' . get_option( 'playHtPlayerTextColor' ) . '' : 'color: #fff'; ?>">
							<span class="imgpl"></span>
							<p>Lorem Ipsum is simply dummy text</p>
							<small class="byJohn">by John Doe</small>
						</div>

						<div class="playht-cutom-audio__time-wrapper--mobile">
							<span class="startCount" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) != 'dark' ) ? 'color: ' . get_option( 'playHtPlayerTextColor' ) . '' : 'color: #fff'; ?>">00:00</span>
							<span class="endCount" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) != 'dark' ) ? 'color: ' . get_option( 'playHtPlayerTextColor' ) . '' : 'color: #fff'; ?>">00:17</span>
						</div>
						<div class="playht-custom-audio__playBtns-wrapper" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) != 'dark' ) ? 'color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . '' : 'color: #fff'; ?>">
							<span><i class="playht-icon-ccw"></i></span>
							<span><i class="playht-icon-play-circled"></i></span>
							<span><i class="playht-icon-cw"></i></span>
						</div>

						<div class="playhtSpeed_range">
							<span class="playhtRange__value" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) != 'dark' ) ? 'color: ' . get_option( 'playHtPlayerTextColor' ) . '' : 'color: #fff'; ?>">Speed: 1.3</span>
							<span class="line_r" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'background-color: #fff' : 'background-color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>">  </span>
							<span class="line_circl" style="<?php echo $playHtDarkMode = ( get_option( 'playHtDarkMode' ) == 'dark' ) ? 'background-color: #fff' : 'background-color: ' . get_option( 'playHtPlayerItemsColor', '#fff' ) . ''; ?>">  </span>
						</div>

						<div class="playhtIconA"><span><i class="playht-icon-flash"></i>by play.ht</span></div>

					</div>
				</div>

			</div>
		</div>
	</div>
	<?php
}

function callPlayHtDarkMode() {
	echo '<input type="checkbox" id="playHtDarkModeID" class="js-switch" name="playHtDarkMode" value="dark" ' . checked( 'dark', get_option( 'playHtDarkMode' ), false ) . ' />';
}

function playHtplayerItemsColorCallBack() {
	$value = get_option( 'playHtPlayerItemsColor' ) ? esc_attr( get_option( 'playHtPlayerItemsColor' ) ) : '#fff';
	echo '<input type="text" class="playHtPlayerItemsColor" name="playHtPlayerItemsColor" value="' . $value . '">';
}
function playHtPlayerTextColorCallBack() {
	$value = get_option( 'playHtPlayerTextColor' ) ? esc_attr( get_option( 'playHtPlayerTextColor' ) ) : '#fff';
	echo '<input type="text" class="playHtPlayerTextColor" name="playHtPlayerTextColor" value="' . $value . '">';
}
function playHtPlayerBackgroundColorCallBack() {
	$value = get_option( 'playHtPlayerBackgroundColor' ) ? esc_attr( get_option( 'playHtPlayerBackgroundColor' ) ) : '#222';
	echo '<input type="text" class="playHtPlayerBackgroundColor" name="playHtPlayerBackgroundColor" value="' . $value . '">';
}

function fullScreenMobEnabledCallBack() {
	echo '<input type="checkbox" id="fullScreenMobEnabledIDJS" class="js-switch" name="fullScreenMobEnabledID" value="1" ' . checked( 1, get_option( 'fullScreenMobEnabledID' ), false ) . ' />';
}

function callBackplayHt_articleplayer_switch() {
	$ep_switch_value = get_option( 'playHt_articleplayer_switch' );
	echo '<div class="_conf"><div class="radio">
            <input type="checkbox" id="playHt_articleplayer_switch" name="playHt_articleplayer_switch" name="enabled" value="1" ' . checked( '1', $ep_switch_value, false ) . '>
            <label class="radio-label">Article Player Enabled</label>
        </div>
    <div class="_desc">Shows or hides the Article Player in all your articles those have audio.</div>
    </div>';
}


// Display playHt Setting page
function display_playHt_SettingArticlePlayer_page() {
	echo '<form class="form_settings_area" method="post" action="options.php">';
	do_settings_sections( 'play-welcome-page-articleplayer' );
	settings_fields( 'playSettingsArticlePlayerArea' );
	echo '<div class="playHtsubmit_button_area">';
	submit_button( 'Save Changes', '_save-btn medium-button medium-button--primary medium-button--filled medium-button--withChrome u-accentColor-- buttonNormal is-touched' );
	echo '</div></form>';
}

function display_playHt_SettingPage_pagef() {
	echo '<form class="form_settings_area f" method="post" action="options.php">';
	do_settings_sections( 'play-welcome-page-f' );
	settings_fields( 'playSettingsArea-f' );
	echo '<div class="playHtsubmit_button_area">';
	submit_button( 'Save Changes', '_save-btn medium-button medium-button--primary medium-button--filled medium-button--withChrome u-accentColor-- buttonNormal is-touched' );
	echo '</div></form></div>';
}
function display_playHt_SettingPage_pagel() {

	echo '<form class="form_settings_area l" method="post" action="options.php">';
	do_settings_sections( 'play-welcome-page-l' );
	settings_fields( 'playSettingsArea-l' );
	echo '<div class="playHtsubmit_button_area">';
	submit_button( 'Save Changes', '_save-btn medium-button medium-button--primary medium-button--filled medium-button--withChrome u-accentColor-- buttonNormal is-touched' );
	echo '</div></form>';
}
function display_playHt_SettingPage_pagei() {

	echo '<form class="form_settings_area i" method="post" action="options.php">';
	do_settings_sections( 'play-welcome-page-i' );
	settings_fields( 'playSettingsArea-i' );
	echo '<div class="playHtsubmit_button_area">';
	submit_button( 'Save Changes', '_save-btn medium-button medium-button--primary medium-button--filled medium-button--withChrome u-accentColor-- buttonNormal is-touched' );
	echo '</div></form>';
}


function display_playHt_SettingPostType_page() {
	echo '<form class="form_settings_area" method="post" action="options.php">';
	do_settings_sections( 'play-post-type' );
	settings_fields( 'playPostArea' );
	echo '</div><div class="playHtsubmit_button_area1">';
	submit_button();
	echo '</div></form>';
}
