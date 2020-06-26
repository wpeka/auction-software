<?php
/**
 * Auction Software Email notification class.
 *
 * @since 1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/emails
 */

/**
 * Auction Software Email notification class.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/emails
 */
class WC_Auction_Software_Email_Manager {

	/**
	 * WC_Auction_Software_Email_Manager constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array( $this, 'auction_software_wc_init_emails' ) );
		add_filter( 'woocommerce_template_directory', array( $this, 'auction_software_wc_template_directory' ), 10, 2 );
	}

	/**
	 * Add custom emails to WooCommerce email classes.
	 *
	 * @since 1.0.0
	 * @param Array $emails WooCommerce emails array.
	 * @return mixed
	 */
	public function auction_software_wc_init_emails( $emails ) {
		$emails['WC_Auction_Software_Start']  = include 'class-wc-auction-software-start.php';
		$emails['WC_Auction_Software_End']    = include 'class-wc-auction-software-end.php';
		$emails['WC_Auction_Software_Win']    = include 'class-wc-auction-software-win.php';
		$emails['WC_Auction_Software_Outbid'] = include 'class-wc-auction-software-outbid.php';
		return $emails;
	}

	/**
	 * Custom directory for templates.
	 *
	 * @since 4.0.0
	 *
	 * @param string $directory Directory.
	 * @param string $template Template.
	 * @return string
	 */
	public function auction_software_wc_template_directory( $directory, $template ) {
		if ( false !== strpos( $template, 'auction-' ) ) {
			return 'auction-software/woocommerce';
		}
		return $directory;
	}

}
new WC_Auction_Software_Email_Manager();
