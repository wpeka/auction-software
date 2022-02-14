<?php
/**
 * Class Auction_Software_Widget_Ending_Soon_Auctions_Test
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */

require_once \Elementor\Widget_Base;
/**
 * Require Auction_Software Elementor Ending Soon class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor/class-auction-software-widget-coming-soon-auctions.php';

/**
 * Auction_Software_Elementor Widget Endinf Soom Auctions class test cases.
 *
 * @since 1.1.0
 */
class Elementor_Widget_Ending_Soon_Auctions_Test extends WP_UnitTestCase {

	/**
	 * Product ids array
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int $product_ids class instance.
	 */
	public static $widget_coming_soon;

	/**
	 * Test for a get_name function
	 *
	 * @since 1.1.0
	 */
	public function test_get_name() {
		self::$widget_coming_soon = new Widget_Coming_Soon();
		$name                     = self::$widget_coming_soon->get_name();
		$this->assertEqual( $name, 'Auction Software Coming Soon Auctions' );
	}

	/**
	 * Test for a get_title function
	 *
	 * @since 1.1.0
	 */
	public function test_get_title() {
		$title = self::$widget_coming_soon->get_title();
		$this->assertTrue( $title, 'Auction Software Coming Soon Auctions' );
	}

	/**
	 * Test for a get_icon function
	 *
	 * @since 1.1.0
	 */
	public function test_get_icon() {
		$icon = self::$widget_coming_soon->get_icon();
		$this->assertTrue( $icon, 'eicon-wordpress' );
	}

	/**
	 * Test for a get_categories function
	 *
	 * @since 1.1.0
	 */
	public function test_get_categories() {
		$cat = self::$widget_coming_soon->get_categories();
		$this->assertTrue( in_array( 'wp-auction', $cat, true ) );
	}
}
