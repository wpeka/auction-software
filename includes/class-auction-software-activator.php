<?php
/**
 * Fired during plugin activation
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Auction_Software
 * @subpackage Auction_Software/includes
 * @author     WPeka Club <support@wpeka.com>
 */
class Auction_Software_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		 add_option( 'auction_software_active', true );
	}

}
