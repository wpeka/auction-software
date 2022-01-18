<?php
/**
 * Class Auction_Software_Widget_Ending_Soon_Auctions_Test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests/widgets
 */

/**
 * Require Auction_Software_Admin class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-ending-soon-auctions.php';

/**
 * Auction_Software_Admin class test cases.
 *
 * @since 1.1.0
 */
class Auction_Software_Admin_Test extends WP_UnitTestCase {
    /**
	 * Testing Auction_Software_Widget_Ending_Soon_Auctions constructor.
	 *
	 * @since 1.0.0
	 */
    public function test__construct(){
    $obj = new Auction_Software_Widget_Ending_Soon_Auctions();
    $this->assertTrue( $obj instanceof Auction_Software_Widget_Ending_Soon_Auctions );
    }
}
