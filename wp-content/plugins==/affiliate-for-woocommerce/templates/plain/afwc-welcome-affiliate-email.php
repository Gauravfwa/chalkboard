<?php
/**
 * Welcome email for affiliate (Affiliate - Welcome Email)
 *
 * @package     affiliate-for-woocommerce/templates/plain/
 * @since       2.4.0
 * @version     1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Affiliate's first name */
echo sprintf( esc_html__( 'Hi %s,', 'affiliate-for-woocommerce' ), esc_html( $user_name ) ) . "\n\n";

echo esc_html__( 'Your affiliate request has been approved.', 'affiliate-for-woocommerce' ) . "\n\n";
echo esc_html__( 'We are excited to have you as our affiliate partner. Here are the details you will need to get started:', 'affiliate-for-woocommerce' ) . "\n\n";

echo esc_html__( 'Your affiliate ID:', 'affiliate-for-woocommerce' ) . "\t" . esc_attr( $affiliate_id ) . "\n\n";

echo esc_html__( 'Your personal affiliated link:', 'affiliate-for-woocommerce' ) . "\t" . esc_attr( $affiliate_link ) . "\n\n";

echo esc_html_x( 'Your affiliate dashboard', 'affiliate dashboard page text', 'affiliate-for-woocommerce' ) . "\n";
/* translators: %s: Affiliate my account link */
echo sprintf( esc_html_x( 'Log in to your affiliate dashboard regularly. You will find our current promotion campaigns, marketing assets, complete record of your referrals and payouts there. You can fully manage your account from the dashboard: %s.', 'Message to view the affiliate dashboard', 'affiliate-for-woocommerce' ), esc_url( $my_account_afwc_url ) ) . "\n\n";

echo esc_html__( 'Our Products', 'affiliate-for-woocommerce' ) . "\n";
echo esc_html__( 'You can refer people using your affiliate link. You can also promote individual products if you like.', 'affiliate-for-woocommerce' ) . "\n";
if ( ! empty( $shop_page ) ) {
	/* translators: %s: Shop page link */
	echo sprintf( esc_html__( 'Here is our complete product catalog: %s.', 'affiliate-for-woocommerce' ), esc_url( $shop_page ) ) . "\n\n";
} else {
	echo "\n";
}

echo esc_html__( 'Partnership and communication are important to us', 'affiliate-for-woocommerce' ) . "\n";
echo esc_html__( 'We value our partners, so we are happy to assist any time. We would also love to discuss any novel promotion ideas you may have. Feel free to reach out to us anytime.', 'affiliate-for-woocommerce' ) . "\n\n";

echo esc_html__( 'Personal note before signing off', 'affiliate-for-woocommerce' ) . "\n";
echo esc_html__( 'The most important thing I have learned working with our partners is that the best way to succeed is quickly to start active promotions. If you postpone, you will not see results. If you take quick action, you may as well become one of our superstar partners! Looking forward to working closely with you.', 'affiliate-for-woocommerce' ) . "\n\n";

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

// Output the email footer.
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
