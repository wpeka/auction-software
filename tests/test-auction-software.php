<?php
/**
 * Class Auction_Software_Plugin_File_Test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */
class Auction_Software_Plugin_File_Test extends WP_UnitTestCase {

	/**
	 * Setup function for all tests.
	 *
	 * @param WP_UnitTest_Factory $factory helper for unit test functionality.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
	}

	/**
	 * Test for activate_auction_software function
	 *
	 * @since 1.1.0
	 */
	public function test_activate_auction_software() {
		activate_auction_software();
		$this->assertTrue( (bool) get_option( 'auction_software_active' ) );
	}

	/**
	 * Test for deactivate_auction_software function
	 *
	 * @since 1.1.0
	 */
	public function test_deactivate_auction_software() {
		deactivate_auction_software();
		$this->assertFalse( get_option( 'auction_software_active' ) );
		$this->assertFalse( get_option( 'auction_flushed_rewrite_rules' ) );
	}
}
