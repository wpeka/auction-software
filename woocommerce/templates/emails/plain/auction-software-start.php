<?php
/**
 * Admin auction start email (plain text).
 *
 * This template can be overridden by copying it to yourtheme/auction-software/woocommerce/emails/plain/auction-software-start.php
 *
 * @link       https://club.wpeka.com
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/templates/emails/plain
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$product_data = wc_get_product( $product_id );

echo '= ' . esc_html( $email_heading ) . " =\n\n";

echo sprintf(
	/* translators: 1: Auction name, 2: Auction link */
	esc_html__( 'The %1$s auction has started. %2$s to place your bid.', 'auction-software' ),
	esc_attr( $product_data->get_title() ),
	sprintf(
		'<a href="%s" target="_blank">Click here</a>',
		esc_url( get_permalink( $product_id ) )
	)
);

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
// The below phpcs ignore comment has been added after referring WooCommerce Plugin.
echo esc_html__( 'Start Price: ', 'auction-software' ) . wc_price( $product_data->get_meta( 'auction_start_price' ) ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo esc_html__( 'Start Date: ', 'auction-software' ) . esc_attr( $product_data->get_meta( 'auction_date_from' ) ) . "\n";
echo esc_html__( 'End Date: ', 'auction-software' ) . esc_attr( $product_data->get_meta( 'auction_date_to' ) ) . "\n";
// The below phpcs ignore comment has been added after referring WooCommerce Plugin.
echo esc_html__( 'Bid Increment: ', 'auction-software' ) . wc_price( $product_data->get_auction_bid_increment() ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
