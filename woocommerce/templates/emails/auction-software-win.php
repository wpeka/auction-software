<?php
/**
 * Admin auction win email.
 *
 * This template can be overridden by copying it to yourtheme/auction-software/woocommerce/emails/auction-software-win.php
 *
 * @link       https://club.wpeka.com
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/templates/emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$product_data = wc_get_product( $product_id );

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
echo sprintf( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		/* translators: 1: Auction name, 2: Auction link */
	esc_html__( "You've won the %1\$s auction. %2\$s to buy your product.", 'auction-software' ),
	esc_attr( $product_data->get_title() ),
	sprintf( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		"<a href='%s' target='_blank'>Click here</a>",
		esc_url( get_permalink( $product_id ) )
	)
);
?>
	<br><br>
	<div style="margin-bottom:40px">
		<table cellspacing="0" cellpadding="6" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif">
			<thead>
			<tr>
				<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
					<?php esc_html_e( 'Product', 'auction-software' ); ?>
				</th>
				<th scope="col" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">
					<?php esc_html_e( 'Auction Details', 'auction-software' ); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word;width:50%">
					<?php echo $product_data->get_image( 'woocommerce_gallery_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br>
					<?php echo esc_attr( $product_data->get_title() ); ?><br>
					<?php echo esc_attr( $product_data->get_short_description() ); ?><br>
				</td>
				<td style="color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:'Helvetica Neue',Helvetica,Roboto,Arial,sans-serif;word-wrap:break-word">
					<?php
					esc_html_e( 'Start Price: ', 'auction-software' );
					echo wc_price( $product_data->get_meta( 'auction_start_price' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<br>
					<?php
					esc_html_e( 'Start Date: ', 'auction-software' );
					echo esc_attr( $product_data->get_meta( 'auction_date_from' ) );
					?>
					<br>
					<?php
					esc_html_e( 'End Date: ', 'auction-software' );
					echo esc_attr( $product_data->get_meta( 'auction_date_to' ) );
					?>
					<br>
					<?php
					esc_html_e( 'Bid Increment: ', 'auction-software' );
					echo wc_price( $product_data->get_auction_bid_increment() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<br>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
