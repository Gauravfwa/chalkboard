<?php

/**
 * @since      4.0.2
 * Description: Conversios Onboarding page, It's call while active the plugin
 */
if (class_exists('Conversios_Header') === FALSE) {
	class Conversios_Header extends TVC_Admin_Helper
	{
		// Site Url.
		protected $site_url;

		// Conversios site Url.
		protected $conversios_site_url;

		// Subcription Data.
		protected $subscription_data;

		// Plan id.
		protected $plan_id = 1;

		/** Contruct for Hook */
		public function __construct()
		{
			$this->site_url = "admin.php?page=";
			$this->conversios_site_url = $this->get_conversios_site_url();
			$this->subscription_data = $this->get_user_subscription_data();
			if (isset($this->subscription_data->plan_id) === TRUE && !in_array($this->subscription_data->plan_id, array("1"))) {
				$this->plan_id = $this->subscription_data->plan_id;
			}

			add_action('add_conversios_header', [$this, 'before_start_header']);
			add_action('add_conversios_header', array($this, 'header_notices'));
			add_action('add_conversios_header', [$this, 'header_menu']);
			add_action('add_conversios_header', [$this, 'custom_feedback_form']);
		} //end __construct()


		/**
		 * before start header section
		 *
		 * @since    4.1.4
		 * @return void
		 */
		public function before_start_header()
		{
?>
			<div>
			<?php
		}

		/**
		 * header notices section
		 *
		 * @since    4.1.4
		 */
		public function header_notices()
		{
			?>
				<!--- Promotion box start -->
				<div class="promobandtop">
					<div class="container-fluid">
						<div class="row">
							<div class="promoleft">
								<div class="promobandmsg">
									<?php esc_html_e("Supercharge your E-commerce business with advanced integrations.", "enhanced-e-commerce-for-woocommerce-store"); ?>
									<br>
									<?php esc_html_e("Own your GTM container, GA4 tracking, FB Conversions API, Google Ads Conversion, and more.", "enhanced-e-commerce-for-woocommerce-store"); ?>
								</div>
							</div>
							<div class="promoright">
								<div class="prmoupgrdbtn">
									<a target="_blank" href="<?php echo $this->get_conv_pro_link_adv("top_bar", "all_page", "", "linkonly"); ?>" class="upgradebtn"><?php esc_html_e("Level Up Today!", "enhanced-e-commerce-for-woocommerce-store"); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--- Promotion box end -->
			<?php
			echo esc_attr($this->call_tvc_site_verified_and_domain_claim());
		}

		/**
		 * header section
		 *
		 * @since    4.1.4
		 */
		public function conversios_header()
		{
			$plan_name = esc_html__("Free Plan", "enhanced-e-commerce-for-woocommerce-store");
			if (isset($this->subscription_data->plan_name) && !in_array($this->subscription_data->plan_id, array("1"))) {
				$plan_name = $this->subscription_data->plan_name;
			}
			?>
				<!-- header start -->
				<header class="header">
					<div class="hedertop">
						<div class="row align-items-center">
							<div class="hdrtpleft">
								<div class="brandlogo">
									<a target="_blank" href="<?php echo esc_url_raw($this->conversios_site_url); ?>"><img src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logo.png'); ?>" alt="" /></a>
								</div>
								<div class="hdrcntcbx">
									<?php printf("%s <span><a href=\"mailto:info@conversios.io\">info@conversios.io</a></span>", esc_html_e("For any query, contact us on", "conversios")); ?>
								</div>
							</div>
							<div class="hdrtpright">
								<div class="hustleplanbtn">
									<a href="<?php echo esc_url_raw($this->site_url . 'conversios-account'); ?>"><button class="cvrs-btn greenbtn">
											<?php echo esc_attr($plan_name); ?>
										</button></a>
								</div>
							</div>
							<div class="hdrcntcbx mblhdrcntcbx">
								<?php printf("%s <span><a href=\"tel:+1 (415) 968-6313\">+1 (415) 968-6313</a></span>", esc_html_e("For any query, contact us at", "enhanced-e-commerce-for-woocommerce-store")); ?>
							</div>
						</div>
					</div>
				</header>
				<!-- header end -->
				<?php
			}

			/* add active tab class */
			protected function is_active_menu($page = "")
			{
				if ($page !== "" && isset($_GET['page']) === TRUE && sanitize_text_field($_GET['page']) === $page) {
					return "dark";
				}

				return "secondary";
			}
			public function conversios_menu_list()
			{
				$conversios_menu_arr  = array();
				if (is_plugin_active_for_network('woocommerce/woocommerce.php') || in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
					if (!function_exists('is_plugin_active_for_network')) {
						require_once(ABSPATH . '/wp-admin/includes/woocommerce.php');
					}
					if (CONV_APP_ID == 1) {
						$conversios_menu_arr  = array(
							"conversios" => array(
								"page" => "conversios",
								"title" => "Dashboard"
							),
							"conversios-analytics-reports" => array(
								"page" => "conversios-analytics-reports",
								"title" => "Reports & Insights"
							),
							"conversios-google-analytics" => array(
								"page" => "conversios-google-analytics",
								"title" => "Pixels & Analytics"
							),
							"conversios-google-shopping-feed" => array(
								"page" => "#",
								"title" => "Product Feed",
								"sub_menus" => array(
									"conversios-google-shopping-feed" => array(
										"page" => "conversios-google-shopping-feed&tab=gaa_config_page",
										"title" => "Channel Configuration"
									),
									"feed-list" => array(
										"page" => "conversios-google-shopping-feed&tab=feed_list",
										"title" => "Feed Management"
									),
								)
							),
							"conversios-pmax" => array(
								"page" => "conversios-pmax",
								"title" => "Performance Max"
							),
							"conversios-pricings" => array(
								"page" => "conversios-pricings",
								"title" => "Free Vs Pro"
							),
						);
					} else {
						$conversios_menu_arr  = array(
							"conversios" => array(
								"page" => "conversios",
								"title" => "Dashboard"
							),
							"conversios-google-shopping-feed" => array(
								"page" => "#",
								"title" => "Product Feed",
								"sub_menus" => array(
									"conversios-google-shopping-feed" => array(
										"page" => "conversios-google-shopping-feed&tab=gaa_config_page",
										"title" => "Channel Configuration"
									),
									"feed-list" => array(
										"page" => "conversios-google-shopping-feed&tab=feed_list",
										"title" => "Feed Management"
									),
								)
							),
							"conversios-pricings" => array(
								"page" => "conversios-pricings",
								"title" => "Free Vs Pro"
							),
						);
					}
				} else {
					$conversios_menu_arr  = array(
						"conversios" => array(
							"page" => "conversios",
							"title" => "Dashboard"
						),
						"conversios-google-analytics" => array(
							"page" => "conversios-google-analytics",
							"title" => "Pixels & Analytics"
						),
						"conversios-pricings" => array(
							"page" => "conversios-pricings",
							"title" => "Free Vs Pro"
						),
					);
				}


				return apply_filters('conversios_menu_list', $conversios_menu_arr, $conversios_menu_arr);
			}
			/**
			 * header menu section
			 *
			 * @since    4.1.4
			 */
			public function header_menu()
			{
				$menu_list = $this->conversios_menu_list();
				if (!empty($menu_list)) {
				?>
					<header class="border-bottom bg-white">
						<div class="container-fluid col-12 p-0">
							<nav class="navbar navbar-expand-lg navbar-light bg-light ps-4" style="height:40px;">
								<div class="container-fluid">
									<a class="navbar-brand link-dark fs-16 fw-400">
										<img style="width: 150px;" src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/logo.png'); ?>" />
									</a>
									<div class="collapse navbar-collapse" id="navbarSupportedContent">
										<ul class="navbar-nav me-auto mb-lg-0">
											<?php
											foreach ($menu_list as $key => $value) {
												if (isset($value['title']) && $value['title']) {
													$is_active = $this->is_active_menu($key);
													$active = $is_active != 'secondary' ? 'rich-blue' : '';
													$menu_url = "#";
													if (isset($value['page']) && $value['page'] != "#") {
														$menu_url = $this->site_url . $value['page'];
													}
													$is_parent_menu = "";
													$is_parent_menu_link = "";
													if (isset($value['sub_menus']) && !empty($value['sub_menus'])) {
														$is_parent_menu = "dropdown";
													}
											?>
													<li class="nav-item fs-14 mt-1 fw-400 <?php echo esc_attr($active); ?> <?php echo esc_attr($is_parent_menu); ?>">
														<?php if ($is_parent_menu == "") { ?>
															<?php if ($value['title'] === 'Reports & Insights') { ?>
																<a class="new-badge nav-link text-<?php esc_attr($is_active); ?> " aria-current="page" href="<?php echo esc_url_raw($menu_url); ?>">
																	<?php echo esc_attr($value['title']); ?> <span class='menu-badge badge-bounce'>Ai Powered</span>
																</a>
															<?php } else { ?>
																<a class="nav-link text-<?php esc_attr($is_active); ?> " aria-current="page" href="<?php echo esc_url_raw($menu_url); ?>">
																	<?php echo esc_attr($value['title']); ?>
																</a>
															<?php } ?>
														<?php } else { ?>
															<a class="new-badge nav-link dropdown-toggle text-<?php esc_attr($is_active); ?> " id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
																<?php echo esc_attr($value['title']); ?>
															</a>
															<ul class="dropdown-menu fs-14 fw-400" aria-labelledby="navbarDropdown">
																<?php
																foreach ($value['sub_menus'] as $sub_key => $sub_value) {
																	$sub_menu_url = $this->site_url . $sub_value['page'];
																?>
																	<li>
																		<a class="dropdown-item" href="<?php echo esc_url_raw($sub_menu_url); ?>">
																			<?php echo esc_attr($sub_value['title']); ?>
																		</a>
																	</li>
																<?php }
																?>
															</ul>
														<?php } ?>

													</li>
											<?php
												}
											} ?>
										</ul>
										<div class="d-flex">
											<?php
											$plan_name = esc_html__("Free Plan", "enhanced-e-commerce-for-woocommerce-store");
											$type = 'warning';
											if (isset($this->subscription_data->plan_name) && !in_array($this->subscription_data->plan_id, array("1"))) {
												$plan_name = $this->subscription_data->plan_name;
												$type = 'success';
											} ?>
											<button type="button" class="btn btn-<?php echo esc_attr($type) ?> rounded-pill fs-12 fw-400 me-4 px-2 py-0" data-bs-toggle="modal" data-bs-target="#convLicenceInfoMod"><?php echo esc_attr($plan_name) ?></button>
											<a href="https://wordpress.org/support/plugin/enhanced-e-commerce-for-woocommerce-store/reviews/?rate=5#rate-response" target="_blank"><span class="me-2 fs-12">Rate Us!</span>
												<img style="max-width:153px; height:20px" src="<?php echo esc_url_raw(ENHANCAD_PLUGIN_URL . '/admin/images/rate-us.png'); ?>" />
											</a>
										</div>
									</div>
								</div>
							</nav>
						</div>
					</header>
					<div id="loadingbar_blue_header" class="progress-materializecss d-none ps-2 pe-2" style="width:100%">
						<div class="indeterminate"></div>
					</div>

				<?php
				}
			}
			/**
			 * custom_feedback_form
			 *
			 * @since    4.6.4
			 */
			public function custom_feedback_form()
			{
				if (isset($_GET['page']) && sanitize_text_field($_GET['page']) === "conversios") {
					$this->TVC_Admin_Helper = new TVC_Admin_Helper();
					$customerId = sanitize_text_field($this->TVC_Admin_Helper->get_api_customer_id());
					$subscriptionId = sanitize_text_field($this->TVC_Admin_Helper->get_subscriptionId());
				?>
					<div id="feedback-form-wrapper">
						<div id="feedback_record_btn">
							<button type="button" class="feedback_btn btn-11">
								<?php esc_html_e("Feedback", "enhanced-e-commerce-for-woocommerce-store"); ?>
							</button>
						</div>
						<div id="feedback_form_modal" class="pp-modal whitepopup">
							<div class="sycnprdct-ppcnt">
								<div class="ppwhitebg pp-content upgradsbscrptnpp-cntr" style="max-width: 545px !important;">
									<div class="ppclsbtn absltpsclsbtn clsbtntrgr">
										<span class="material-symbols-outlined closeModal text-white">
											close
										</span>
									</div>
									<div class="upgradsbscrptnpp-hdr">
										<h5>
											<?php esc_html_e("Your feedback is valuable", "enhanced-e-commerce-for-woocommerce-store"); ?>
										</h5>
									</div>
									<div class="ppmodal-body">
										<form id="customer_feedback_form">
											<div class="feedback-form-group">
												<label class="feedback_que_label" for="feedback_question_one">
													<?php esc_html_e("The Conversios plugin helps me to get more insight about business which helps me to take business decisions? *", "enhanced-e-commerce-for-woocommerce-store"); ?>
												</label>
												<div class="rating-input-wrapper">
													<label class="feedback_label"><input name="feedback_question_one" type="radio" value="1" /><span class="feedback_options">
															<?php esc_html_e("Strongly Agree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_one" value="2" /><span class="feedback_options">
															<?php esc_html_e("Agree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_one" value="3" /><span class="feedback_options">
															<?php esc_html_e("Disagree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_one" value="4" /><span class="feedback_options">
															<?php esc_html_e("Strongly Disagree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
												</div>
											</div>
											<div class="feedback-form-group">
												<label class="feedback_que_label" for="feedback_question_two">
													<?php esc_html_e("The Conversios plugin helps me to simplified the Google Ads and Google Merchant Centre? *", "enhanced-e-commerce-for-woocommerce-store"); ?>
												</label>
												<div class="rating-input-wrapper">
													<label class="feedback_label"><input type="radio" name="feedback_question_two" value="1" /><span class="feedback_options">
															<?php esc_html_e("Strongly Agree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_two" value="2" /><span class="feedback_options">
															<?php esc_html_e("Agree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_two" value="3" /><span class="feedback_options">
															<?php esc_html_e("Disagree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_two" value="4" /><span class="feedback_options">
															<?php esc_html_e("Strongly Disagree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
												</div>
											</div>
											<div class="feedback-form-group feedback_txtarea_div">
												<label class="feedback_que_label" for="feedback_question_three">
													<?php esc_html_e("You are satisfied with the Conversion plugin? *", "enhanced-e-commerce-for-woocommerce-store"); ?>
												</label>
												<div class="rating-input-wrapper">
													<label class="feedback_label"><input type="radio" name="feedback_question_three" value="1" /><span class="feedback_options">
															<?php esc_html_e("Strongly Agree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_three" value="2" /><span class="feedback_options">
															<?php esc_html_e("Agree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_three" value="3" /><span class="feedback_options">
															<?php esc_html_e("Disagree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
													<label class="feedback_label"><input type="radio" name="feedback_question_three" value="4" /><span class="feedback_options">
															<?php esc_html_e("Strongly Disagree", "enhanced-e-commerce-for-woocommerce-store"); ?>
														</span></label>
												</div>
											</div>
											<div class="feedback-form-group feedback_txtarea_div">
												<label class="feedback_que_label" for="feedback_description">
													<?php esc_html_e("How could we make the Conversios plugin better for you?", "enhanced-e-commerce-for-woocommerce-store"); ?>
												</label>
												<textarea class="feedback_txtarea" id="feedback_description" onkeyup="feedback_charcountupdate(this.value)" rows="5" maxlength="300"></textarea><span id="charcount"></span>
											</div>
											<button id="submit_wooplugin_feedback" type="submit" class="blueupgrdbtn">
												<?php esc_html_e("Submit", "enhanced-e-commerce-for-woocommerce-store"); ?>
											</button>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
					<script>
						function feedback_charcountupdate(str) {
							let lng = str.length;
							document.getElementById("charcount").innerHTML = '(' + lng + '/300)';
						}
						jQuery(document).ready(function() {
							feedback_charcountupdate(jQuery('#feedback_description').val());

							jQuery("#feedback_record_btn").click(function() {
								setTimeout(() => {
									jQuery("#feedback_form_modal").addClass("showpopup");
								}, 500);
							});
							jQuery(".closeModal").click(function() {
								setTimeout(() => {
									jQuery("#feedback_form_modal").removeClass("showpopup");
								}, 100);
							});
							jQuery("#customer_feedback_form").submit(function(event) {
								event.preventDefault();
								let val_que_one = jQuery('input[name="feedback_question_one"]:checked').val();
								if (val_que_one == "" || val_que_one == undefined) {
									tvc_helper.tvc_alert("error", "", "Please answer the required questions");
									return false;
								}
								let val_que_two = jQuery('input[name="feedback_question_two"]:checked').val();
								if (val_que_two == "" || val_que_two == undefined) {
									return false;
								}
								let val_que_three = jQuery('input[name="feedback_question_three"]:checked').val();
								if (val_que_three == "" || val_que_three == undefined) {
									return false;
								}
								let feedback_description = jQuery('#feedback_description').val();
								let customer_id = "<?php echo $customerId; ?>";
								let subscription_id = "<?php echo $subscriptionId; ?>";
								let formdata = {
									action: "tvc_call_add_customer_feedback",
									que_one: val_que_one,
									que_two: val_que_two,
									que_three: val_que_three,
									subscription_id: subscription_id,
									customer_id: customer_id,
									feedback_description: feedback_description,
									conv_customer_feed_nonce_field: "<?php echo wp_create_nonce('conv_customer_feed_nonce_field_save'); ?>"
								};
								$.ajax({
									type: "POST",
									dataType: "json",
									url: tvc_ajax_url,
									data: formdata,
									beforeSend: function() {
										jQuery("#customer_feedback_form").addClass("is_loading");
									},
									success: function(response) {
										if (response?.error === false) {
											tvc_helper.tvc_alert("success", "", response.message);
										} else {
											tvc_helper.tvc_alert("error", "", response.message);
										}
										jQuery("#customer_feedback_form").removeClass("is_loading");
										jQuery("#feedback_form_modal").removeClass("showpopup");
									}
								});
							});
						});
					</script>
	<?php
				}
			}
		}
	}
	new Conversios_Header();
