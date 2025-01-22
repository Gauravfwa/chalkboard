<section class="playhtwp">

	<nav class="welcome-page-nav">
		<a class="logo" href="#">
			<img src="<?php echo WPPP_URI; ?>assets/images/playht_logo.png">
			<span>Play.ht <strong class="dev_dashboard">Dashboard</strong> <small>(v<?php echo PLAYHT_VERSION; ?>)</small></span>
		</a>
		<?php settings_errors(); ?>
	</nav>
	<section class="content-2">
		<div class="dashboard-container">
			<div class="_nav">
				<ul>
					<li class="active" data-tab="2" data-tabname="account" data-onclick="tab"><?php _e( 'Account', 'play-ht' ); ?></li>
					<li data-tab="3" data-tabname="stats" data-onclick="tab"><?php _e( 'Audio Analytics', 'play-ht' ); ?></li>
					<li style="display: none;" data-tabname="credits" data-tab="1" data-onclick="tab"><?php _e( 'Words', 'play-ht' ); ?></li>
					<li data-tab="6" data-tabname="customization" data-onclick="tab"><?php _e( 'Audio Players', 'play-ht' ); ?></li>
					<li data-tab="4" data-tabname="subscribers" data-onclick="tab"><?php _e( 'Audio Subscribers', 'play-ht' ); ?></li>
					<li data-tab="8" data-tabname="post_types" data-onclick="tab"><?php _e( 'Settings', 'play-ht' ); ?></li>
					<li data-tab="7" data-tabname="chat" data-onclick="tab"><?php _e( 'Help', 'play-ht' ); ?></li>
					<li><span id="toUsePlayht" style="color: #388eb9;font-weight: 600;font-size: 13px;"><?php _e( 'See How to add audio to your articles', 'play-ht' ); ?></span></li>
				</ul>

			</div>

			<div class="play-dashboard-tab" id="play-dashboard-tab-1" style="display: none;">
				<div class="_credits">
					<div class="_title"><?php _e( 'Words remaining', 'play-ht' ); ?></div>
					<div class="_meta" id="play-dashbaord-credits">6000</div>
					<div class="_act">
						<a data-onclick="dashboardBuyCredits" target="_blank" class="medium-button medium-button--primary medium-button--withChrome u-accentColor--buttonNormal" style="margin-left: 15px;vertical-align: middle;" href="https://play.ht/wordpress/upgrade/"><?php _e( 'Buy Words', 'play-ht' ); ?></a>
					</div>
				</div>
				<div class="_credits_usage">
					<div class="_title"><?php _e( 'Words usage', 'play-ht' ); ?></div>
					<div class="_head"><ul><li><span class="_t"><?php _e( 'Article Title', 'play-ht' ); ?></span><span class="_v"><?php _e( 'Voice', 'play-ht' ); ?></span><li></ul></div>
					<div id="credits_usage"><?php _e( 'Loading...', 'play-ht' ); ?></div>
				</div>
			</div>

			<div class="play-dashboard-tab" id="play-dashboard-tab-2"><?php _e( 'Loading...', 'play-ht' ); ?></div>
			<div class="play-dashboard-tab" id="play-dashboard-tab-3" style="display: none;">
				<div id="date-selectors-wrapper" style="display: none;">
					<div class="_article">
						<p class="_p"><?php _e( 'Article:', 'play-ht' ); ?></p>
						<a class="_article-selector" href="#" data-jq-dropdown="#jq-dropdown-stats-article"><?php _e( 'Showing analytics for all posts', 'play-ht' ); ?></a>
						<i class="playht-icons playht-icon-angle-circled-down align-right"></i>
						<div id="jq-dropdown-stats-article" class="jq-dropdown jq-dropdown-tip jq-dropdown-relative">
						</div>
					</div>
					<div class="_period">
						<p class="_p"><?php _e( 'Period:', 'play-ht' ); ?></p>
						<label class="back-calendar"><input type="text" id="play-dashboard-stats-date-picker-from" placeholder="<?php _e( 'Select starting date', 'play-ht' ); ?>" /><i class="playht-icon-calendar"></i></label>
						<label class="back-calendar"><input type="text" id="play-dashboard-stats-date-picker-to" placeholder="<?php _e( 'Select end date', 'play-ht' ); ?>" /><i class="playht-icon-calendar"></i></label>
					</div>
				</div>

				<svg id="dashboard-stats-loader" width="130px"  height="130px"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-ripple" style="background: none;"><circle cx="50" cy="50" r="16.3131" fill="none" ng-attr-stroke="{{config.c1}}" ng-attr-stroke-width="{{config.width}}" stroke="#158c19" stroke-width="2"><animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="1" keySplines="0 0.2 0.8 1" begin="-0.5s" repeatCount="indefinite"></animate><animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="1" keySplines="0.2 0 0.8 1" begin="-0.5s" repeatCount="indefinite"></animate></circle><circle cx="50" cy="50" r="35.092" fill="none" ng-attr-stroke="{{config.c2}}" ng-attr-stroke-width="{{config.width}}" stroke="#00d400" stroke-width="2"><animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="1" keySplines="0 0.2 0.8 1" begin="0s" repeatCount="indefinite"></animate><animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="1" keySplines="0.2 0 0.8 1" begin="0s" repeatCount="indefinite"></animate></circle></svg>
				<div id="dashboard-stats"></div>
			</div>
			<div class="play-dashboard-tab" id="play-dashboard-tab-4" style="display: none;">
				<div id="zero-sub-modal" style="display:none;">
					<span>You don't have subscribers for now.</span>
				</div>
				<div id="dashboard-subscribers-stats-wrapper">
					<div class="sub-stats-head" style="display:none;">
						<span class="_sub"><span class="_count" id="play-dash-sub-count">-</span>Subscribers</span>
					</div>
					<div class="sub-stats-filter" style="display:none;">
						<p class="_p">Period:</p>
						<input type="text" id="play-dashboard-sub-stats-date-picker-from" placeholder="Select starting date" />
						<input type="text" id="play-dashboard-sub-stats-date-picker-to" placeholder="Select end date" />
					</div>
					<svg id="dashboard-subscribers-stats-loader" width="130px"  height="130px"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-ripple" style="background: none;"><circle cx="50" cy="50" r="16.3131" fill="none" ng-attr-stroke="{{config.c1}}" ng-attr-stroke-width="{{config.width}}" stroke="#158c19" stroke-width="2"><animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="1" keySplines="0 0.2 0.8 1" begin="-0.5s" repeatCount="indefinite"></animate><animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="1" keySplines="0.2 0 0.8 1" begin="-0.5s" repeatCount="indefinite"></animate></circle><circle cx="50" cy="50" r="35.092" fill="none" ng-attr-stroke="{{config.c2}}" ng-attr-stroke-width="{{config.width}}" stroke="#00d400" stroke-width="2"><animate attributeName="r" calcMode="spline" values="0;40" keyTimes="0;1" dur="1" keySplines="0 0.2 0.8 1" begin="0s" repeatCount="indefinite"></animate><animate attributeName="opacity" calcMode="spline" values="1;0" keyTimes="0;1" dur="1" keySplines="0.2 0 0.8 1" begin="0s" repeatCount="indefinite"></animate></circle></svg>
					<div id="dashboard-subscribers-stats" width="840" height="280"></div>
				</div>
				<div id="dashboard-subscribers-info-wrapper">

				</div>
			</div>
			<div class="play-dashboard-tab palyht_settings_area" id="play-dashboard-tab-8" style="display: none;">
				<?php display_playHt_SettingPostType_page(); ?>
			</div>

			<div class="play-dashboard-tab" id="play-dashboard-tab-7" style="display: none;">
				<div class="face-issue"><?php _e( 'Facing an issue? Please let us know in the chat.', 'play-ht' ); ?></div>
				<?php require_once 'crisp_chat.php'; ?>
			</div>

			<div class="play-dashboard-tab" id="play-dashboard-tab-6" style="display: none;">
				<script> enable_ap = <?php echo ( ! empty( get_option( 'playHt_articleplayer_switch' ) ) ? get_option( 'playHt_articleplayer_switch' ) : 0 ); ?>;</script>
				<div style="margin-top: 40px;padding: 20px;border: 1px solid #eee;">
					<h3 style="color: #000;font-size: 20px;font-weight: 700;">Article Audio Player:</h3>
					<p style="color: #000;margin: 0;font-size: 14px;">Embed a full width audio player in your posts. The player is fully customizable and can be positioned anywhere in the post using shortcodes.</p>
					<hr style="margin-top: 10px;">
					<p style="color: #000;font-size: 14px;">This is a preview. You can change the settings below to customize it the look and feel of it.</p>
					<div style="display: none;"><?php display_playHt_SettingArticlePlayer_page(); ?></div>
					<div class="js-play-dashboard-tab-6"><?php _e( 'Loading...', 'play-ht' ); ?></div>
				</div>
				<div class="palyht_settings_area">
					<?php display_playHt_SettingPage_pagef(); ?>

				</div>
				<div class="palyht_settings_area">

					<?php display_playHt_SettingPage_pagel(); ?>

				</div>
				<div class="palyht_settings_area">

					<?php display_playHt_SettingPage_pagei(); ?>
				</div>
			</div>
		</div>
	</section>


	<div class="howToUse">
		<div class="inner_howToUse">
			<script src="https://fast.wistia.com/embed/medias/dvaw1yq2hf.jsonp" async></script>
			<script src="https://fast.wistia.com/assets/external/E-v1.js" async></script>
			<div class="wistia_responsive_padding" style="padding:62.5% 0 0 0;position:relative;">
				<div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;">
				<span class="wistia_embed wistia_async_dvaw1yq2hf popoverAnimateThumbnail=true videoFoam=true" style="display:inline-block;height:100%;width:100%">&nbsp;</span>
				</div>
			</div>
		</div>
		<div id="closeV"></button>
	</div>

</section>
