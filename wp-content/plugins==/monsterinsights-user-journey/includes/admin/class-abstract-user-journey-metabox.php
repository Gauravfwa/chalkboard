<?php

/**
 * This class is base abstract class for each provider to extend and use the
 * function defined in the class.
 *
 * @since 1.0.2
 *
 * @package MonsterInsights
 * @subpackage MonsterInsights_User_Journey
 */


abstract class MonsterInsights_User_Journey_Metabox
{

	/**
	 * Contains HTML to display inside the metabox
	 *
	 * @param array  $user_journey User Journey entries.
	 * @param int    $order_id eCommerce Platforms Order ID.
	 * @param string $order_date eCommerce Platforms Order Date.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function metabox_html($user_journey, $order_id = 0, $order_date = '')
	{
?>
		<!-- User Journey metabox -->
		<div id="monsterinsights-user-journey-ajax-result">
			<div id="monsterinsights-entry-user-journey" class="postbox">
				<div class="inside">
					<?php $this->metabox_title(); ?>
					<?php if (empty($user_journey) || !is_array($user_journey)) : ?>
						<h3 style="padding:0 10px 10px;">
							<?php esc_html_e('There\'s no user journey for this entry.', 'monsterinsights-user-journey'); ?></h3>
					<?php else : ?>

						<table width="100%" cellspacing="0" cellpadding="0">

							<?php
							$timestamp_prev = false;
							foreach ($user_journey as $record) {

								$timestamp = strtotime($record->date);
								if (empty($timestamp_prev) || (gmdate('d', $timestamp) !== gmdate('d', $timestamp_prev))) {
							?>
									<tr>
										<td colspan="3" class="date">
											<?php esc_html_e(date_i18n(get_option('date_format'), $timestamp + (get_option('gmt_offset') * 3600))); ?>
										</td>
									</tr>
							<?php
								}

								/**
								 * Check if recorded url is a checkout page of a provider.
								 * If 'true' sent a param for checkout, so that a class can
								 * be added to the table row and highlighted as checkout.
								 *
								 * @since 1.0.2
								 */
								$checkout = $this->is_checkout_url($record->url, $order_id, json_decode($record->parameters, true)) ? 'checkout' : '';
								$this->user_journey_single_record_html(
									date_i18n(get_option('time_format'), $timestamp + (get_option('gmt_offset') * 3600)),
									$record->title,
									$record->url,
									str_replace(home_url(), '', $record->url),
									json_decode($record->parameters, true),
									!empty($record->duration) ? human_time_diff($timestamp - $record->duration, $timestamp) : 0,
									$checkout
								);

								$timestamp_prev = $timestamp;
							}

							$count_user_journey = monsterinsights_user_journey()->db->get_user_journey($order_id, array(), true);
							$full_user_journey  = monsterinsights_user_journey()->db->get_user_journey($order_id);

							$summary = sprintf(
								/* translators: %1$s - number of steps; %2$s - total time spent. */
								__('User took %1$s over %2$s', 'monsterinsights-user-journey'),
								sprintf(
									/* translators: Total number of steps taken. */
									_n('%s step', '%s steps', $count_user_journey, 'monsterinsights-user-journey'),
									$count_user_journey
								),
								human_time_diff(strtotime($full_user_journey[0]->date), strtotime($order_date))
							);

							if ($this->pagination_last_page($order_id)) {
								$order_ID = $this->get_order_id_to_display($order_id);

								$this->user_journey_single_record_html(
									date_i18n(get_option('time_format'), $timestamp + (get_option('gmt_offset') * 3600)),
									sprintf(esc_html__('Order #%s Placed', 'monsterinsights-user-journey'), $order_ID),
									'',
									$summary,
									array(),
									'',
									'ordered'
								);
							}
							?>

							<tr>
								<td colspan="3"><?php $this->pagination_nav($order_id); ?></td>
							</tr>
						</table>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php
	}

	/**
	 * Part of metabox HTML. Contains HTML for single entry for loop.
	 *
	 * @param string $time Date of the entry.
	 * @param string $title Title of the page.
	 * @param string $url Page URL.
	 * @param string $path Page path.
	 * @param array  $params Additional Parameters
	 * @param int    $duration Duration on a page.
	 * @param string $status Status of the journey.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function user_journey_single_record_html($time, $title, $url, $path, $params, $duration, $status = '')
	{
		$order_row_css_class    = '' !== $status && 'ordered' === $status ? 'monsterinsights-uj-order-completed-row' : '';
		$checkout_row_css_class = '' !== $status && 'checkout' === $status ? 'monsterinsights-uj-checkout-completed-row' : '';
	?>
		<tr>

			<td class="time">
				<?php esc_html_e($time); ?>
			</td>

			<td class="title-area <?php esc_attr_e($order_row_css_class); ?> <?php esc_attr_e($checkout_row_css_class); ?>">
				<span class="monsterinsights-title-area-highlight">
					<?php if ('' !== $status && 'ordered' === $status) { ?>
						<span class="success-icon-svg">
							<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
								<g clip-path="url(#clip0_44_8)">
									<path d="M10.6283 19.9998H9.37878C9.31579 19.988 9.25319 19.9736 9.19021 19.9646C8.77238 19.9035 8.34908 19.8691 7.93791 19.7799C5.53738 19.2596 3.56875 18.0292 2.0825 16.0777C0.236723 13.6544 -0.385709 10.9194 0.229681 7.93898C0.772305 5.30914 2.19948 3.23132 4.41418 1.72431C6.60228 0.235685 9.03137 -0.277607 11.642 0.139443C13.5304 0.44108 15.1954 1.24662 16.6222 2.52046C18.355 4.06738 19.4386 5.98636 19.8467 8.27817C19.9112 8.64202 19.9496 9.01016 20 9.37635V10.6263C19.9891 10.6764 19.9715 10.7261 19.9676 10.777C19.8494 12.3333 19.4003 13.7898 18.5834 15.1172C16.9951 17.6974 14.7099 19.2862 11.7249 19.8468C11.3627 19.9149 10.9941 19.9497 10.6287 19.9998H10.6283Z" fill="#55CE99" />
									<path d="M8.36024 11.8875C8.43614 11.7995 8.48269 11.7329 8.5492 11.666C10.4321 9.77915 12.3182 7.89499 14.2012 6.00771C14.4731 5.73541 14.7806 5.58596 15.171 5.65677C15.9597 5.80036 16.2887 6.71583 15.7719 7.32967C15.7152 7.39696 15.651 7.4576 15.5888 7.5202C13.4551 9.65474 11.3214 11.7889 9.18728 13.923C8.60593 14.5044 8.06096 14.506 7.48352 13.9289C6.42487 12.8702 5.36349 11.8147 4.30915 10.7517C3.72975 10.1676 3.9414 9.24356 4.70506 9.01821C5.11819 8.89615 5.47303 9.01547 5.77623 9.32102C6.56571 10.1164 7.36028 10.9071 8.15289 11.6993C8.20845 11.7549 8.25031 11.785 8.35946 11.8875H8.36024Z" fill="white" />
								</g>
								<defs>
									<clipPath id="clip0_44_8">
										<rect width="20" height="20" fill="white" />
									</clipPath>
								</defs>
							</svg>
						</span>
					<?php } ?>
					<span class="title"><?php esc_html_e($title); ?></span>
					<span class="path">
						<?php esc_html_e($path); ?>
						<?php if ($path === '/') : ?>
							<?php if (!empty($params['s'])) : ?>
								<em>(<?php esc_html_e('Search Results', 'monsterinsights-user-journey'); ?>)</em>
							<?php else : ?>
								<em>(<?php esc_html_e('Homepage', 'monsterinsights-user-journey'); ?>)</em>
							<?php endif; ?>
						<?php endif; ?>
					</span>
				</span>

				<?php if (!empty($url) && strpos($url, home_url()) !== false) : ?>
					<a href="<?php echo esc_url($url); ?>" class="go" target="blank" rel="noopener noreferrer" title="<?php esc_attr_e('Go to URL', 'monsterinsights-user-journey'); ?>">
						<svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8.4375 6.45801H8.125C7.94922 6.45801 7.8125 6.61426 7.8125 6.77051V9.77832C7.8125 9.85645 7.75391 9.89551 7.69531 9.89551H1.05469C0.976562 9.89551 0.9375 9.85645 0.9375 9.77832V3.1377C0.9375 3.0791 0.976562 3.02051 1.05469 3.02051H4.0625C4.21875 3.02051 4.375 2.88379 4.375 2.70801V2.39551C4.375 2.23926 4.21875 2.08301 4.0625 2.08301H0.9375C0.410156 2.08301 0 2.5127 0 3.02051V9.89551C0 10.4229 0.410156 10.833 0.9375 10.833H7.8125C8.32031 10.833 8.75 10.4229 8.75 9.89551V6.77051C8.75 6.61426 8.59375 6.45801 8.4375 6.45801ZM9.76562 0.833008H7.10938C6.97266 0.852539 6.875 0.950195 6.875 1.06738C6.875 1.12598 6.89453 1.2041 6.93359 1.24316L7.87109 2.18066L2.55859 7.49316C2.51953 7.53223 2.48047 7.61035 2.48047 7.66895C2.48047 7.72754 2.51953 7.78613 2.55859 7.8252L3.00781 8.27441C3.04688 8.31348 3.10547 8.35254 3.16406 8.35254C3.22266 8.35254 3.30078 8.31348 3.33984 8.27441L8.65234 2.96191L9.58984 3.89941C9.62891 3.93848 9.70703 3.95801 9.76562 3.95801C9.88281 3.95801 9.98047 3.86035 10 3.72363V1.06738C10 0.950195 9.88281 0.833008 9.76562 0.833008Z" fill="#A6A6A6" />
						</svg>
					</a>
				<?php endif; ?>

				<?php if (!empty($params)) : ?>
					<button class="parameter-toggle" title="<?php esc_attr_e('Toggle URL parameter display', 'monsterinsights-user-journey'); ?>">
						<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M8 0.25C9.375 0.25 10.6875 0.625 11.875 1.3125C13.0625 2 14 2.9375 14.6875 4.125C15.375 5.3125 15.75 6.625 15.75 8C15.75 9.40625 15.375 10.6875 14.6875 11.875C14 13.0625 13.0625 14.0312 11.875 14.7188C10.6875 15.4062 9.375 15.75 8 15.75C6.59375 15.75 5.3125 15.4062 4.125 14.7188C2.9375 14.0312 1.96875 13.0625 1.28125 11.875C0.59375 10.6875 0.25 9.40625 0.25 8C0.25 6.625 0.59375 5.3125 1.28125 4.125C1.96875 2.9375 2.9375 2 4.125 1.3125C5.3125 0.625 6.59375 0.25 8 0.25ZM8 3.6875C7.625 3.6875 7.3125 3.84375 7.0625 4.09375C6.8125 4.34375 6.6875 4.65625 6.6875 5C6.6875 5.375 6.8125 5.6875 7.0625 5.9375C7.3125 6.1875 7.625 6.3125 8 6.3125C8.34375 6.3125 8.65625 6.1875 8.90625 5.9375C9.15625 5.6875 9.3125 5.375 9.3125 5C9.3125 4.65625 9.15625 4.34375 8.90625 4.09375C8.65625 3.84375 8.34375 3.6875 8 3.6875ZM9.75 11.625V10.875C9.75 10.7812 9.6875 10.6875 9.625 10.625C9.5625 10.5625 9.46875 10.5 9.375 10.5H9V7.375C9 7.28125 8.9375 7.1875 8.875 7.125C8.8125 7.0625 8.71875 7 8.625 7H6.625C6.5 7 6.40625 7.0625 6.34375 7.125C6.28125 7.1875 6.25 7.28125 6.25 7.375V8.125C6.25 8.25 6.28125 8.34375 6.34375 8.40625C6.40625 8.46875 6.5 8.5 6.625 8.5H7V10.5H6.625C6.5 10.5 6.40625 10.5625 6.34375 10.625C6.28125 10.6875 6.25 10.7812 6.25 10.875V11.625C6.25 11.75 6.28125 11.8438 6.34375 11.9062C6.40625 11.9688 6.5 12 6.625 12H9.375C9.46875 12 9.5625 11.9688 9.625 11.9062C9.6875 11.8438 9.75 11.75 9.75 11.625Z" fill="#464646" />
						</svg>
					</button>
				<?php endif; ?>

				<?php if (!empty($params)) : ?>
					<ul class="parameters">
						<?php foreach ($params as $key => $param) : ?>
							<li>
								<?php esc_html_e($key); ?>
								<svg width="14" height="12" viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M0.600098 6.63291V5.03291H10.2001L7.0001 1.83291L7.8001 0.23291L13.4001 5.83291L7.8001 11.4329L7.0001 9.83291L10.2001 6.63291H0.600098Z" fill="#A6A6A6" />
								</svg>
								<?php esc_html_e(is_array($param) ? print_r($param, true) : $param); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</td>

			<td class="duration">
				<?php echo !empty($duration) ? esc_html($duration) : ''; ?>
			</td>

		</tr>

	<?php
	}

	/**
	 * Display metabox HTML
	 *
	 * @param object|array $order_info Order Information.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	abstract public function display_meta_box($order_info);

	/**
	 * Get the current provider name.
	 *
	 * @return void
	 * @since 1.0.2
	 */
	abstract protected function get_provider();

	/**
	 * Metabox Title
	 *
	 * Some providers does not use default implementation
	 * of WP Metabox like WooCommerce, so we need to add
	 * title additionally for the metabox
	 *
	 * @return string
	 * @since 1.0.2
	 */
	protected function metabox_title()
	{
		return '';
	}

	/**
	 * Database offset/start row form.
	 *
	 * @return int
	 * @since 1.0.2
	 */
	public function db_offset()
	{
		return 0;
	}

	/**
	 * Database limit row.
	 *
	 * @return int
	 * @since 1.0.2
	 */
	public function db_limit()
	{
		return 10;
	}

	/**
	 * Generate pagination for User Journey Metabox.
	 *
	 * @param int $id Order ID
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public function pagination_nav($id)
	{
		$page              = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;
		$cur_page          = $page;
		$page             -= 1;
		$per_page          = $this->db_limit();
		$previous_btn      = true;
		$next_btn          = true;
		$start             = $page * $per_page;
		$count             = monsterinsights_user_journey()->db->get_user_journey($id, array(), true);
		$no_of_paginations = ceil($count / $per_page);

		if ($cur_page >= 7) {
			$start_loop = $cur_page - 3;
			if ($no_of_paginations > $cur_page + 3) {
				$end_loop = $cur_page + 3;
			} elseif ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
				$start_loop = $no_of_paginations - 6;
				$end_loop   = $no_of_paginations;
			} else {
				$end_loop = $no_of_paginations;
			}
		} else {
			$start_loop = 1;
			if ($no_of_paginations > 7) {
				$end_loop = 7;
			} else {
				$end_loop = $no_of_paginations;
			}
		}
	?>
		<div class='monsterinsights-user-journey-pagination-links'>
			<?php
			if ($previous_btn && $cur_page > 1) {
				$pre = $cur_page - 1;
			?>
				<a class="monsterinsights-user-journey-prev-link monsterinsights-user-journey-page" data-provider="<?php esc_attr_e($this->get_provider()); ?>" data-id="<?php esc_attr_e($id); ?>" data-page="<?php esc_attr_e($pre); ?>" class="active" href="#" title="">&#8592;
					<?php esc_html_e('Previous', 'monsterinsights-user-journey'); ?>
				</a>
			<?php
			} elseif ($previous_btn) {
			?>
				<span class='monsterinsights-user-journey-prev-link inactive'>&#8592;
					<?php esc_html_e('Previous', 'monsterinsights-user-journey'); ?></span>
			<?php
			}
			?>
			<ul>
				<?php
				for ($i = $start_loop; $i <= $end_loop; $i++) {
					if ($cur_page == $i) {
				?>
						<li class="selected"><?php esc_html_e($i); ?></li>
					<?php
					} else {
					?>
						<li>
							<a href="#" data-provider="<?php esc_attr_e($this->get_provider()); ?>" data-id="<?php esc_attr_e($id); ?>" data-page="<?php esc_attr_e($i); ?>" class="active monsterinsights-user-journey-page" title=""><?php esc_html_e($i); ?></a>
						</li>
				<?php
					}
				}
				?>
			</ul>
			<?php
			if ($next_btn && $cur_page < $no_of_paginations) {
				$next_page = $cur_page + 1;
			?>
				<a class="monsterinsights-user-journey-next-link monsterinsights-user-journey-page" data-provider="<?php esc_attr_e($this->get_provider()); ?>" data-id="<?php esc_attr_e($id); ?>" data-page="<?php esc_attr_e($next_page); ?>" class="active" href="#" title=""><?php esc_html_e('Next', 'monsterinsights-user-journey'); ?> &#8594;</a>
			<?php
			} elseif ($next_btn) {
			?>
				<span class='inactive monsterinsights-user-journey-next-link'><?php esc_html_e('Next', 'monsterinsights-user-journey'); ?>
					&#8594;</span>
			<?php
			}
			?>
		</div>
<?php
	}

	/**
	 * Check if we are on the last page of pagination.
	 *
	 * @param int $id Order ID.
	 *
	 * @return bool
	 * @since 1.0.2
	 */
	private function pagination_last_page($id)
	{
		$page     = isset($_POST['page']) ? sanitize_text_field($_POST['page']) : 1;
		$per_page = $this->db_limit();
		$count    = monsterinsights_user_journey()->db->get_user_journey($id, array(), true);

		$pagination_pages = ceil($count / $per_page);

		if ((int) $pagination_pages === (int) $page) {
			return true;
		}

		return false;
	}

	/**
	 * Get Order data based on the provider being loaded.
	 *
	 * @param string $provider Provider Name.
	 * @param int    $id Order ID.
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function get_provider_order_data($id)
	{

		if (!$this->get_provider()) {
			return;
		}

		$provider   = $this->get_provider();
		$order_data = array();

		switch ($provider) {
			case 'woocommerce':
				if (MonsterInsights_User_Journey_Helper::is_woocommerce_active()) {
					$order = wc_get_order($id);
					$post  = get_post($id);

					if (!$order) {
						return $order_data;
					}

					$order_data['id']   = $post->ID;
					$order_data['date'] = $post->post_date_gmt;
				}
				break;
			case 'rcp':
				if (MonsterInsights_User_Journey_Helper::is_rcp_active()) {
					$rcp_payments = new RCP_Payments();
					$payment      = $rcp_payments->get_payment($id);

					if (!$payment) {
						return $order_data;
					}

					$order_data['id']   = $payment->id;
					$order_data['date'] = $payment->date;
				}
				break;
			case 'memberpress':
				if (MonsterInsights_User_Journey_Helper::is_memberpress_active()) {
					$txn     = new MeprTransaction();
					$payment = $txn->get_one($id);

					if (!$payment) {
						return $order_data;
					}

					$order_data['id']   = $payment->id;
					$order_data['date'] = $payment->created_at;
				}
				break;
			case 'lifterlms':
				if (MonsterInsights_User_Journey_Helper::is_lifter_lms_active()) {
					$order = llms_get_post($id);

					if (!$order || !is_a($order, 'LLMS_Order')) {
						return $order_data;
					}

					$order_data['id']   = $order->get('id');
					$order_data['date'] = $order->get('date_gmt');
				}
				break;
			case 'givewp':
				if (MonsterInsights_User_Journey_Helper::is_givewp_active()) {
					$payment_id = absint($id);
					$payment    = new Give_Payment($payment_id);

					$payment_exists = $payment->ID;

					if (!$payment_exists) {
						return $order_data;
					}

					$order_data['id']   = $payment->ID;
					$order_data['date'] = get_gmt_from_date($payment->date);
				}
				break;
			case 'edd':
				if (MonsterInsights_User_Journey_Helper::is_edd_active()) {
					$payment = function_exists('edd_get_order') ? edd_get_order($id) : edd_get_payment($id);

					if (!is_object($payment) && empty($payment)) {
						return $order_data;
					}

					$order_data['id'] = $payment->ID;

					if (isset($payment->date_completed)) {
						$order_data['date'] = $payment->date_completed;
					} else if (isset($payment->completed_date)) {
						$order_data['date'] = $payment->completed_date;
					}
				}
				break;

			case 'charitable':
				$donation = charitable_get_donation( $id );

				if ( ! $donation ) {
					return $order_data;
				}

				$order_data['id']   = $donation->ID;
				$order_data['date'] = $donation->get_date( 'Y-m-d H:i:s' );
				break;

			default:
				$order_data = array();
		}

		return $order_data;
	}

	/**
	 * Get checkout page url for all providers.
	 *
	 * @param string  $user_journey_provider_url The recorded URL.
	 * @param integer $id Order ID (Optional)
	 * @param array   $query_params URL Query parameters (Optional)
	 *
	 * @return boolean
	 * @since 1.0.2
	 */
	private function is_checkout_url($user_journey_provider_url, $id = 0, $query_params = array())
	{
		if (!$this->get_provider()) {
			return;
		}

		$provider          = $this->get_provider();
		$checkout_page_url = '';

		switch ($provider) {
			case 'woocommerce':
				$checkout_page_id  = wc_get_page_id('checkout');
				$checkout_page_url = $checkout_page_id ? get_permalink($checkout_page_id) : '';
				break;
			case 'givewp':
				$post = get_post(url_to_postid($user_journey_provider_url));
				if ('give_forms' === get_post_type($post)) {
					return get_permalink($post->ID);
				}
				break;
			case 'edd':
				$checkout_page_url = function_exists('edd_get_checkout_uri') ? edd_get_checkout_uri() : '';
				break;
			case 'lifterlms':
				$checkout_page_url = llms_get_page_url('checkout');
				break;
			case 'memberpress':
				$transaction = new MeprTransaction($id);
				$url         = remove_query_arg(array('action', 'txn'), $transaction->checkout_url());
				if (!empty($query_params)) {
					if (array_key_exists('action', $query_params)) {
						if ('checkout' === $query_params['action']) {
							$checkout_page_url = $url;
						}
					}
				}
				break;
			case 'rcp':
				$rcp_options          = get_option('rcp_settings');
				$rcp_register_page_id = absint($rcp_options['registration_page']);
				$checkout_page_url    = get_permalink($rcp_register_page_id);
				break;
			default:
				$checkout_page_url = '';
		}

		if ($user_journey_provider_url === $checkout_page_url) {
			return true;
		}

		return false;
	}

	/**
	 * Get Order ID to display, this can be a Order Number as well
	 * with prefixes.
	 *
	 * @since 1.0.4
	 *
	 * @param int $order_id Original Order ID.
	 *
	 * @return mixed
	 */
	protected function get_order_id_to_display($order_id)
	{
		$order_ID = '';

		if ('givewp' === $this->get_provider() && 0 !== absint($order_id)) {
			$order_ID = MonsterInsights_User_Journey_Helper::givewp_donation_id($order_id);
		} elseif ('woocommerce' === $this->get_provider() && 0 !== absint($order_id)) {
			$order_ID = MonsterInsights_User_Journey_Helper::get_woo_order_id($order_id);
		} elseif ('edd' === $this->get_provider() && 0 !== absint($order_id)) {
			$order_ID = MonsterInsights_User_Journey_Helper::get_edd_order_id($order_id);
		} else {
			$order_ID = $order_id;
		}

		return $order_ID;
	}
}
