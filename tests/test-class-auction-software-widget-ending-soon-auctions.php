<?php
/**
 * Elementor_Widget_Ending_Soon_Auctions_test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */
require_once \Elementor\Widget_Base;
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/elementor/class-auction-software-widget-coming-soon-auctions.php';
class Elementor_Widget_Ending_Soon_Auctions_test extends WP_UnitTestCase {

	public static $widget_coming_soon;

	public function test_get_name() {
		self::$widget_coming_soon = new Widget_Coming_Soon();
		$name                     = self::$widget_coming_soon->get_name();
		$this->assertEqual( $name, 'Auction Software Coming Soon Auctions' );
	}
	public function test_get_title() {
		$title = self::$widget_coming_soon->get_title();
		$this->assertTrue( $title, 'Auction Software Coming Soon Auctions' );
	}
	public function test_get_icon() {
		$icon = self::$widget_coming_soon->get_icon();
		$this->assertTrue( $icon, 'eicon-wordpress' );
	}
	public function test_get_categories() {
		$cat = self::$widget_coming_soon->get_categories();
		$this->assertTrue( in_array( 'wp-auction', $cat ) );
	}
}