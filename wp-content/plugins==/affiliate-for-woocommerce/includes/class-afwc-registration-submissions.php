<?php
/**
 * Main class for Affiliate registration submissions.
 *
 * @package     affiliate-for-woocommerce/includes/
 * @since       5.2.0
 * @version     1.1.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AFWC_Registration_Submissions' ) ) {

	/**
	 * Affiliate Registration Submission class.
	 */
	class AFWC_Registration_Submissions {

		/**
		 * Hide fields
		 *
		 * @var $hide_fields
		 */
		public $hide_fields = array();

		/**
		 * Readonly fields
		 *
		 * @var $readonly_fields
		 */
		public $readonly_fields = array();

		/**
		 * Variable to hold instance of AFWC_Registration_Submissions.
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of AFWC_Registration_Submissions.
		 *
		 * @return AFWC_Registration_Submissions Singleton object of AFWC_Registration_Submissions.
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			$this->hide_fields     = array( 'afwc_first_name', 'afwc_last_name', 'afwc_password', 'afwc_confirm_password' );
			$this->readonly_fields = array( 'afwc_email' );

			add_filter( 'afwc_registration_form_afwc_parent_id', array( $this, 'validate_parent_id' ), 9, 2 );
			add_filter( 'afwc_registration_form_afwc_email', array( $this, 'validate_email' ), 9, 2 );
			add_filter( 'afwc_registration_form_afwc_password', array( $this, 'validate_password' ), 9, 2 );
			add_filter( 'afwc_registration_form_afwc_paypal_email', array( $this, 'validate_paypal_email' ), 9, 2 );
		}

		/**
		 * Get the default fields.
		 *
		 * @return array Return the default fields.
		 */
		public static function get_default_fields() {
			return array(
				'afwc_email'            => _x( 'Email Address', 'Title for Affiliate registration email address field', 'affiliate-for-woocommerce' ),
				'afwc_first_name'       => _x( 'First Name', 'Title for Affiliate registration first name field', 'affiliate-for-woocommerce' ),
				'afwc_last_name'        => _x( 'Last Name', 'Title for Affiliate registration last name field', 'affiliate-for-woocommerce' ),
				'afwc_password'         => _x( 'Password', 'Title for Affiliate registration password field', 'affiliate-for-woocommerce' ),
				'afwc_confirm_password' => _x( 'Confirm Password', 'Title for Affiliate registration confirmation password field', 'affiliate-for-woocommerce' ),
				'afwc_paypal_email'     => _x( 'PayPal Email Address', 'Title for Affiliate registration PayPal email address field', 'affiliate-for-woocommerce' ),
				'afwc_parent_id'        => _x( 'Referral affiliate user ID', 'Title for Affiliate registration parent user\'s id field', 'affiliate-for-woocommerce' ),
				'afwc_website'          => _x( 'Website URL', 'Title for Affiliate registration website URL field', 'affiliate-for-woocommerce' ),
			);
		}

		/**
		 * Validate the Parent Id field.
		 *
		 * @param array $valid The validation data.
		 * @param int   $parent_id Parent id.
		 *
		 * @return array Return validation data.
		 */
		public function validate_parent_id( $valid = array(), $parent_id = 0 ) {
			if ( ! empty( $parent_id ) && 'yes' !== afwc_is_user_affiliate( intval( $parent_id ) ) ) {
				return array(
					'status'  => 'error',
					'message' => _x( 'The entered parent id not a valid affiliate.', 'Parent id invalid message on affiliate registration form', 'affiliate-for-woocommerce' ),
				);
			}

			return $valid;
		}

		/**
		 * Validate the email field.
		 *
		 * @param array $valid The validation data.
		 * @param int   $email The email id.
		 *
		 * @return array Return validation data.
		 */
		public function validate_email( $valid = array(), $email = '' ) {
			if ( empty( $email ) ) {
				return array(
					'status'  => 'error',
					'message' => _x( 'Email field is required.', 'Email required message on affiliate registration form', 'affiliate-for-woocommerce' ),
				);
			}

			if ( false === is_email( $email ) ) {
				return array(
					'status'  => 'error',
					'message' => _x( 'Please provide a valid email address.', 'Invalid email field error message on affiliate registration form', 'affiliate-for-woocommerce' ),
				);
			}

			return $valid;
		}

		/**
		 * Validate the password field.
		 *
		 * @param array  $valid The validation data.
		 * @param string $password The password field.
		 *
		 * @return array Return validation data.
		 */
		public function validate_password( $valid = array(), $password = '' ) {
			if ( empty( $password ) ) {
				return array(
					'status'  => 'error',
					'message' => _x( 'Password field is required.', 'Password field required message on affiliate registration form', 'affiliate-for-woocommerce' ),
				);
			}

			return $valid;
		}

		/**
		 * Validate the PayPal email field.
		 *
		 * @param array  $valid The validation data.
		 * @param string $email The PayPal Email.
		 *
		 * @return array Return validation data.
		 */
		public function validate_paypal_email( $valid = array(), $email = '' ) {
			// Validate PayPal email if exists.
			if ( ! empty( $email ) && false === is_email( $email ) ) {
				return array(
					'status'  => 'error',
					'message' => _x( 'Please provide a valid PayPal email address.', 'PayPal email invalid error message on affiliate registration form', 'affiliate-for-woocommerce' ),
				);
			}

			return $valid;
		}

		/**
		 * Validate the fields.
		 *
		 * @param array $fields The fields.
		 *
		 * @return array Return validation data.
		 */
		public function check_validation( $fields = array() ) {
			$validation = array(
				'status' => 'success',
			);

			if ( empty( $fields ) ) {
				return $validation;
			}

			$is_user_logged_in = is_user_logged_in();

			foreach ( $fields as $field_key => $field ) {

				// Skip the hidden fields for logged in users.
				if ( ( true === $is_user_logged_in ) && ! empty( $this->hide_fields ) && in_array( $field_key, $this->hide_fields, true ) ) {
					continue;
				}

				$field_validation = apply_filters( 'afwc_registration_form_' . $field_key, $validation, $field, array( 'source' => $this ) );

				if ( ! empty( $field_validation['status'] ) && 'error' === $field_validation['status'] ) {
					return array(
						'status'  => 'error',
						'field'   => $field_key,
						'message' => ! empty( $field_validation['message'] ) ? $field_validation['message'] : _x( 'Something went wrong', 'affiliate registration validation error message', 'affiliate-for-woocommerce' ),
					);
				}
			}

			return apply_filters( 'afwc_registration_form_field_validation', $validation, $fields, array( 'source' => $this ) );
		}

		/**
		 * Register user as an affiliate.
		 *
		 * @param array $fields The user fields.
		 *
		 * @throws Exception The error message.
		 * @return array The response data.
		 */
		public function register_user( $fields = array() ) {
			try {
				$affiliate_status = '';
				$submitted_before = false;

				$user_fields = ! empty( $fields ) ? $this->get_user_meta_fields( $fields ) : array();

				if ( empty( $user_fields ) ) {
					throw new Exception( _x( 'Required fields are missing', 'registration error', 'affiliate-for-woocommerce' ) );
				}

				$validate_fields = $this->check_validation( $user_fields );
				$invalid_field   = '';

				// Throw error for the field validations.
				if ( ! empty( $validate_fields['status'] ) && 'error' === $validate_fields['status'] ) {
					$invalid_field = ! empty( $validate_fields['field'] ) ? $validate_fields['field'] : '';
					throw new Exception( ! empty( $validate_fields['message'] ) ? $validate_fields['message'] : _x( 'One or more fields have an error.', 'affiliate registration field validation message', 'affiliate-for-woocommerce' ) );
				}

				$current_user = wp_get_current_user();
				$user_id      = 0;

				$user = null;

				if ( is_object( $current_user ) && ! empty( $current_user->ID ) ) {
					$user = $current_user;
				} elseif ( ! empty( $user_fields['afwc_email'] ) && email_exists( $user_fields['afwc_email'] ) > 0 ) {
					$user = get_user_by( 'email', $user_fields['afwc_email'] );
				}

				if ( $user instanceof WP_User && ! empty( $user->ID ) ) {

					$affiliate_status = afwc_is_user_affiliate( $user );
					$submitted_before = in_array( $affiliate_status, array( 'pending', 'no', 'yes' ), true );

					if ( 'pending' === $affiliate_status ) {
						throw new Exception( $this->get_message( 'pending' ) );
					} elseif ( 'no' === $affiliate_status ) {
						throw new Exception( $this->get_message( 'rejected' ) );
					} elseif ( 'yes' === $affiliate_status ) {
						throw new Exception( $this->get_message( 'already_registered' ) );
					}
					$user_id = $user->ID;
				} else {
					$user_id = $this->insert_user( $user_fields );
				}

				if ( is_wp_error( $user_id ) ) {
					throw new Exception( is_callable( array( $user_id, 'get_error_message' ) ) ? $user_id->get_error_message() : _x( 'Affiliate registration failed.', 'registration error', 'affiliate-for-woocommerce' ) );
				} elseif ( empty( $user_id ) ) {
					throw new Exception( _x( 'Affiliate registration failed.', 'registration error', 'affiliate-for-woocommerce' ) );
				}

				$user = get_user_by( 'id', $user_id );

				if ( $user instanceof WP_User ) {
					// Update additional data.
					$this->update_additional_fields( $user_id, $user_fields );

					$auto_add_affiliate = get_option( 'afwc_auto_add_affiliate', 'no' );
					$affiliate_status   = ( 'yes' === $auto_add_affiliate ) ? 'yes' : 'pending';

					if ( 'yes' === $affiliate_status ) {
						$this->approve_affiliate( $user_id );
					} else {
						update_user_meta( $user_id, 'afwc_is_affiliate', $affiliate_status );
					}

					$user_fields['user_login'] = ! empty( $user->user_login ) ? $user->user_login : '';

					$msg = '';

					if ( 'yes' === $affiliate_status ) {
						$msg = $this->get_message( 'success_with_auto_approved' );

						// Send welcome email to the affiliate.
						if ( true === AFWC_Emails::is_afwc_mailer_enabled( 'afwc_email_welcome_affiliate' ) ) {
							// Trigger email.
							do_action(
								'afwc_email_welcome_affiliate',
								array(
									'affiliate_id'     => $user_id,
									'is_auto_approved' => $auto_add_affiliate,
								)
							);
						}
					} else {
						$msg = $this->get_message( 'success_without_auto_approved' );
						if ( true === AFWC_Emails::is_afwc_mailer_enabled( 'afwc_email_affiliate_pending_request' ) ) {
							do_action(
								'afwc_email_affiliate_pending_request',
								array(
									'user_id' => $user_id,
								)
							);
						}
					}

					// Notify affiliate manager for new affiliate registration.
					if ( true === AFWC_Emails::is_afwc_mailer_enabled( 'afwc_email_new_registration_received' ) ) {
						// Trigger email.
						do_action(
							'afwc_email_new_registration_received',
							array(
								'user_id'          => $user_id,
								'userdata'         => $user_fields,
								'is_auto_approved' => $auto_add_affiliate,
							)
						);
					}

					return array(
						'status'  => 'success',
						'data'    => array(
							'affiliate_status' => $affiliate_status,
							'submitted_before' => $submitted_before,
						),
						'message' => $msg,
					);
				}
			} catch ( Exception $e ) {
				return array(
					'status'           => 'error',
					'data'             => array(
						'affiliate_status' => $affiliate_status,
						'submitted_before' => $submitted_before,
					),
					'invalid_field_id' => $invalid_field,
					'message'          => is_callable( array( $e, 'getMessage' ) ) ? $e->getMessage() : '',
				);
			}
		}

		/**
		 * Arrange the fields for user meta.
		 *
		 * @param array $fields The fields.
		 *
		 * @return array Return fields for user meta fields.
		 */
		public function get_user_meta_fields( $fields = array() ) {
			if ( empty( $fields ) ) {
				return array();
			}

			$user_fields = array();

			$afwc_fields       = self::get_default_fields();
			$default_field_ids = ! empty( $afwc_fields ) && is_array( $afwc_fields ) ? array_keys( $afwc_fields ) : array();

			foreach ( $fields as $field ) {
				if ( ! empty( $default_field_ids ) && is_array( $default_field_ids ) && in_array( $field['key'], $default_field_ids, true ) ) {
					$user_fields[ $field['key'] ] = $field['value'];
				} else {
					if ( isset( $field['value'] ) && '' !== $field['value'] ) {
						$user_fields['afwc_additional_fields'][] = $field;
					}
				}
			}

			return $user_fields;
		}

		/**
		 * Insert the user to WordPress.
		 *
		 * @param array $user_data The user data.
		 *
		 * @return int|WP_Error Return The newly created user's ID or a WP_Error object if the user could not be created.
		 */
		public function insert_user( $user_data = array() ) {
			if ( empty( $user_data ) || ! is_array( $user_data ) ) {
				return new WP_Error( 'afwc_registration_missing_fields', _x( 'The fields are missing for registration.', 'missing fields error message', 'affiliate-for-woocommerce' ) );
			}

			if ( empty( $user_data['afwc_email'] ) ) {
				return new WP_Error( 'afwc_registration_email_required', _x( 'The Email address is required.', 'email required error message', 'affiliate-for-woocommerce' ) );
			}

			$afwc = Affiliate_For_WooCommerce::get_instance();

			$username = '';

			if ( is_callable( array( $afwc, 'is_wc_gte_36' ) ) && $afwc->is_wc_gte_36() && ! empty( $user_data['afwc_email'] ) ) {
				$username = wc_create_new_customer_username(
					$user_data['afwc_email'],
					array(
						'first_name' => ! empty( $user_data['afwc_first_name'] ) ? $user_data['afwc_first_name'] : '',
						'last_name'  => ! empty( $user_data['afwc_last_name'] ) ? $user_data['afwc_last_name'] : '',
					)
				);
			}

			return wp_insert_user(
				array(
					'user_email' => ! empty( $user_data['afwc_email'] ) ? sanitize_email( $user_data['afwc_email'] ) : '',
					'user_login' => ! empty( $username ) ? $username : $user_data['afwc_email'],
					'user_pass'  => ! empty( $user_data['afwc_password'] ) ? $user_data['afwc_password'] : '',
					'first_name' => ! empty( $user_data['afwc_first_name'] ) ? sanitize_text_field( $user_data['afwc_first_name'] ) : '',
					'last_name'  => ! empty( $user_data['afwc_last_name'] ) ? sanitize_text_field( $user_data['afwc_last_name'] ) : '',
				)
			);
		}

		/**
		 * Update user data for affiliate.
		 *
		 * @param int   $user_id The user id.
		 * @param array $user_data The user data.
		 *
		 * @return void.
		 */
		public function update_additional_fields( $user_id = 0, $user_data = array() ) {
			if ( empty( $user_id ) || empty( $user_data ) || ! is_array( $user_data ) ) {
				return;
			}

			// Update website field if exists.
			if ( ! empty( $user_data['afwc_website'] ) ) {
				wp_update_user(
					array(
						'ID'       => $user_id,
						'user_url' => $user_data['afwc_website'],
					)
				);
			}

			// Save the PayPal email if exists.
			if ( ! empty( $user_data['afwc_paypal_email'] ) ) {
				update_user_meta( $user_id, 'afwc_paypal_email', sanitize_email( $user_data['afwc_paypal_email'] ) );
			}

			// Save the additional fields if exists.
			if ( ! empty( $user_data['afwc_additional_fields'] ) && is_array( $user_data['afwc_additional_fields'] ) ) {
				update_user_meta( $user_id, 'afwc_additional_fields', $user_data['afwc_additional_fields'] );
			}

			// save parent child relation if parent cookie present.
			$parent_affiliate_id = ! empty( $user_data['afwc_parent_id'] ) ? $user_data['afwc_parent_id'] : afwc_get_referrer_id();
			if ( ! empty( $parent_affiliate_id ) && 'yes' === afwc_is_user_affiliate( absint( $parent_affiliate_id ) ) ) {
				$parent_chain = get_user_meta( $parent_affiliate_id, 'afwc_parent_chain', true );

				// Concat parent chain if exists and update to the parent chain meta.
				update_user_meta(
					$user_id,
					'afwc_parent_chain',
					sprintf( '%1$s|%2$s', $parent_affiliate_id, ! empty( $parent_chain ) ? $parent_chain : '' )
				);
			}
		}

		/**
		 * Get the affiliate registration response message.
		 *
		 * @param string $message_name The message name (pending|rejected|already_registered|success_with_auto_approved|success_without_auto_approved).
		 *
		 * @return string|array Return the message if message name is provided otherwise returns all the messages.
		 */
		public function get_message( $message_name = '' ) {
			// Get the admin contact email.
			$afwc_admin_contact_email = get_option( 'afwc_contact_admin_email_address', '' );

			$messages = array(
				'success_without_auto_approved' => _x( 'We have received your request to join our affiliate program. We will review it and will get in touch with you soon!', 'registration success message', 'affiliate-for-woocommerce' ),
				'success_with_auto_approved'    => sprintf(
					/* translators: Link to the my account page */
					_x( 'Congratulations, you are successfully registered as our affiliate. %s to find more details about affiliate program.', 'registration success message', 'affiliate-for-woocommerce' ),
					'<a target="_blank" href="' . esc_url(
						wc_get_endpoint_url(
							get_option( 'woocommerce_myaccount_afwc_dashboard_endpoint', 'afwc-dashboard' ),
							'resources',
							wc_get_page_permalink( 'myaccount' )
						)
					) . '">' . _x( 'Visit here', 'affiliate registration redirect link title', 'affiliate-for-woocommerce' ) . '</a>'
				),
				'pending'                       => _x( 'We have already received your request and will get in touch soon.', 'registration pending message', 'affiliate-for-woocommerce' ),
				'rejected'                      => ! empty( $afwc_admin_contact_email ) ? sprintf(
					/* translators: mailto link to contact affiliate manager */
					_x( 'Your previous request to join our affiliate program has been declined. Please %s for more details.', 'registration rejected message', 'affiliate-for-woocommerce' ),
					'<a target="_blank" href="mailto:' . esc_attr( $afwc_admin_contact_email ) . '">' . _x( 'email affiliate manager', 'email to affiliate manager', 'affiliate-for-woocommerce' ) . '</a>'
				) : _x( 'Your previous request to join our affiliate program has been declined. Please contact the store admin for more details.', 'registration error', 'affiliate-for-woocommerce' ),
				'already_registered'            => _x( 'You are already registered with us as an affiliate.', 'registration already registered message', 'affiliate-for-woocommerce' ),
			);

			if ( empty( $message_name ) ) {
				return $messages;
			}

			return ! empty( $messages[ $message_name ] ) ? $messages[ $message_name ] : '';
		}

		/**
		 * Change affiliate status when affiliate is approved.
		 *
		 * @param int $user_id The affiliate user ID.
		 *
		 * @return bool Return true if affiliate status updated to yes otherwise false.
		 */
		public function approve_affiliate( $user_id = 0 ) {
			if ( empty( $user_id ) ) {
				return false;
			}

			$is_approved = update_user_meta( $user_id, 'afwc_is_affiliate', 'yes' );
			if ( $is_approved ) {
				do_action( 'afwc_affiliate_approved', $user_id );
			}

			return $is_approved;
		}

	}

}
