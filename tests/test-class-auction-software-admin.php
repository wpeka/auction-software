_<?php
/**
 * Class Auction_Software_Admin_Test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */

/**
 * Require Wpadcenter_Admin class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-auction-software-admin.php';

/**
 * Auction_Software_Admin class test cases.
 *
 * @since 1.1.0
 */
class Auction_Software_Admin_Test extends WP_UnitTestCase {

	/**
	 * Auction_Software_Admin class instance craeted
	 *
	 * @since 1.1.0
	 * @var class Auction_Software_Admin class instance $auction_software_admin
	 *
	 * @access public
	 */
	public static $auction_software_admin;

	/**
	 * Product ids array
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int $product_ids product ids
	 */
	public static $product_ids;

	/**
	 * Created a dummy post
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int $simple_auction_post post for simple_auction type post
	 */
	public static $simple_auction_post;

	/**
	 * Set up function.
	 *
	 * @since 1.1.0
	 * @param WP_UnitTest_Factory $factory helper for unit test functionality.
	 * @access public
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$product_ids         = $factory->post->create_many( 3, array( 'post_type' => 'product' ) );
		self::$simple_auction_post = get_post( self::$product_ids[0] );
		wp_set_object_terms( self::$product_ids[0], 'auction_simple', 'product_type' );
	}

	/**
	 * Test for a constructor function
	 *
	 * @since 1.1.0
	 */
	public function test_construct() {
		self::$auction_software_admin = new Auction_Software_Admin( 'Auction-software', '1.1.0' );
		$this->assertTrue( self::$auction_software_admin instanceof Auction_Software_Admin );
	}

	/**
	 * Test for enqueue_styles function
	 *
	 * @since 1.1.0
	 */
	public function test_enqueue_styles() {
		self::$auction_software_admin->enqueue_styles();
		global $wp_styles;
		$this->assertTrue( in_array( 'Auction-software', $wp_styles->queue, true ) );
	}

	/**
	 * Test for enqueue_scripts function
	 *
	 * @since 1.1.0
	 */
	public function test_enqueue_scripts() {
		self::$auction_software_admin->enqueue_scripts();
		global $wp_scripts;
		$this->assertTrue( in_array( 'Auction-software', $wp_scripts->queue, true ) );
		$this->assertTrue( in_array( 'Auction-software-timepicker-addon', $wp_scripts->queue, true ) );
		$this->assertArrayHasKey( 'Auction-software-wc-settings', $wp_scripts->registered, 'Failed to register script Auction-software-wc-settings.' );
	}

	/**
	 * Test for auction_software_widgets_init function
	 */
	public function test_auction_software_widgets_init() {
		self::$auction_software_admin->auction_software_widgets_init();
		$widgets = array_keys( $GLOBALS['wp_widget_factory']->widgets );

		$this->assertTrue( in_array( 'Auction_Software_Widget_Ending_Soon_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_Featured_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_Future_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_My_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_Random_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_Recent_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_Recently_Viewed_Auctions', $widgets, true ) );
		$this->assertTrue( in_array( 'Auction_Software_Widget_Watchlist_Auctions', $widgets, true ) );
	}

	/**
	 * Test for auction_software_plugin_action_links function
	 */
	public function test_auction_software_plugin_action_links() {
		$links = self::$auction_software_admin->auction_software_plugin_action_links( array() );
		$this->assertTrue( is_string( $links[0] ) && wp_strip_all_Tags( $links[0] ) !== $links[0] );
	}

	/**
	 * Test for auction_software_remove_duplicate_link function
	 */
	public function test_auction_software_remove_duplicate_link() {
		$action = self::$auction_software_admin->auction_software_remove_duplicate_link( array( 'duplicate' => '<a href="http://google.com/search?q=' . get_post( self::$product_ids[0] )->post_name . '" class="google_link">Search Google for Page Title</a>' ), self::$simple_auction_post );
		$this->assertTrue( is_array( $action ) && empty( $action ) );
	}

	/**
	 * Test for auction_software_get_wc_classes function
	 */
	public function test_auction_software_get_wc_classes() {
		$inc_classes = self::$auction_software_admin->auction_software_get_wc_classes();
		$this->assertTrue( ! empty( $inc_classes ) );

	}
}
