<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Auction_Software
 * @subpackage Auction_Software/includes
 * @author     WPeka Club <support@wpeka.com>
 */
class Auction_Software_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		delete_option( 'auction_software_active' );
		delete_option( 'auction_flushed_rewrite_rules' );
	}

}
