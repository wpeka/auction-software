<?php
/**
 * Class Auction_Software_Admin_Test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */

/**
 * Require Auction_Software_Admin class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-auction-software-admin.php';

/**
 * Auction_Software_Admin class test cases.
 *
 * @since 1.1.0
 */
class Auction_Software_Admin_Test extends WP_UnitTestCase {

	/**
	 * Auction_Software_Admin class instance created
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
	 * Created term group
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int $auctiom_term_group term ids
	 */
	public static $auctiom_term_group;

	/**
	 * Set up function.
	 *
	 * @since 1.1.0
	 * @param WP_UnitTest_Factory $factory helper for unit test functionality.
	 * @access public
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$product_ids         = $factory->post->create_many( 3, array( 'post_type' => 'product' ) );
		self::$auctiom_term_group  = $factory->term->create_many( 2, array( 'taxonomy' => 'product_auction_class' ) );
		self::$simple_auction_post = get_post( self::$product_ids[0] );
		wp_set_object_terms( self::$product_ids[0], 'auction_simple', 'product_type' );
		wp_set_object_terms( self::$product_ids[1], 'auction_reverse', 'product_type' );

		update_post_meta( self::$product_ids[0], 'auction_extend_or_relist_auction', 'extend' );
		update_post_meta( self::$product_ids[0], 'auction_extend_if_fail', 'no' );
		update_post_meta( self::$product_ids[0], 'auction_extend_if_not_paid', 'no' );
		update_post_meta( self::$product_ids[0], 'auction_extend_duration', 10 );
		update_post_meta( self::$product_ids[0], 'auction_date_from', gmdate( 'Y-m-d H:i:s' ) );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '+5 days' ) ) );

		update_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_if_fail', 10 );
		update_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_if_not_paid', 10 );
		update_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_always', 10 );

		update_post_meta( self::$product_ids[0], 'auction_extend_duration_if_fail', 10 );
		update_post_meta( self::$product_ids[0], 'auction_extend_duration_if_not_paid', 10 );
		update_post_meta( self::$product_ids[0], 'auction_extend_duration_always', 10 );

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

	/**
	 * Test for auction_software_wc_settings_tab function
	 */
	public function test_auction_software_wc_settings_tab() {
		$setting_tabs['auctions'] = array( 'Global Settings', 'Bid Increment', 'Bid Packages' );
		$returned_settings        = self::$auction_software_admin->auction_software_wc_settings_tab( $setting_tabs );
		$this->assertEquals( $setting_tabs['auctions'], $returned_settings['auctions'] );
	}

	/**
	 * Test for auction_software_product_auction_tabs function
	 */
	public function test_auction_software_product_auction_tabs() {
		$tabs          = array(
			'auction'         => '',
			'auction_history' => '',
			'auction_relist'  => '',
		);
		$returned_tabs = self::$auction_software_admin->auction_software_product_auction_tabs( $tabs );
		$this->assertTrue(
			is_array( $returned_tabs['auction'] ) && is_array( $returned_tabs['auction_history'] ) && is_array( $returned_tabs['auction_relist'] )
		);
		$this->assertTrue(
			__( 'Auction Settings', 'auction-software' ) === $returned_tabs['auction']['label']
			&& 'auction_options' === $returned_tabs['auction']['target']
		);
		$this->assertTrue(
			__( 'Auction History', 'auction-software' ) === $returned_tabs['auction_history']['label']
			&& 'auction_history' === $returned_tabs['auction_history']['target']
		);
		$this->assertTrue(
			__( 'Auction Relist Settings', 'auction-software' ) === $returned_tabs['auction_relist']['label']
			&& 'auction_relist' === $returned_tabs['auction_relist']['target']
		);
	}

	/**
	 * Test for auction_software_product_auction_types function
	 */
	public function test_auction_software_product_auction_types() {
		$auction_types = self::$auction_software_admin->auction_software_product_auction_types( array() );
		$this->assertTrue(
			is_array( $auction_types ) && ( __( 'Simple Auction', 'auction-software' ) === $auction_types['auction_simple'] &&
			__( 'Reverse Auction', 'auction-software' ) === $auction_types['auction_reverse'] )
		);
	}

	/**
	 * Test for auction_software_save_product_auction_options function
	 */
	public function test_auction_software_save_product_auction_options() {
		$_POST['product-type']           = 'auction_simple';
		$_POST['auction_item_condition'] = 'New';
		$_POST['auction_start_price']    = 12000.0123;
		$_POST['auction_bid_increment']  = 120;
		$_POST['auction_date_from']      = gmdate( get_option( 'date_format' ) );
		$_POST['auction_date_to']        = gmdate( get_option( 'date_format' ), strtotime( '+5 days' ) );

		$_POST['auction_extend_or_relist_auction']            = 'extend';
		$_POST['auction_extend_if_fail']                      = true;
		$_POST['auction_extend_if_not_paid']                  = true;
		$_POST['auction_extend_duration_if_fail']             = 10;
		$_POST['auction_wait_time_before_extend_if_fail']     = 10;
		$_POST['auction_wait_time_before_extend_if_not_paid'] = 10;
		$_POST['auction_wait_time_before_extend_always']      = 10;

		$_POST['auction_wait_time_before_relist_always']      = 10;
		$_POST['auction_wait_time_before_relist_if_fail']     = 10;
		$_POST['auction_wait_time_before_relist_if_not_paid'] = 10;
		$_POST['auction_relist_duration_if_fail']             = 10;
		$_POST['auction_relist_duration_if_not_paid']         = 10;
		$_POST['auction_relist_duration_always']              = 10;

		$_POST['auction_reserve_price']    = 16000.210;
		$_POST['auction_buy_it_now_price'] = 17000.00;

		$_POST['auction_reserve_price_reverse']    = 8000.210;
		$_POST['auction_buy_it_now_price_reverse'] = 6000.00;

		self::$auction_software_admin->auction_software_save_product_auction_options( self::$product_ids[0] );

		$this->assertEquals( 'New', get_post_meta( self::$product_ids[0], 'auction_item_condition', true ) );
		$this->assertEquals( '12000.01', get_post_meta( self::$product_ids[0], 'auction_start_price', true ) );
		$this->assertEquals( '120', get_post_meta( self::$product_ids[0], 'auction_bid_increment', true ) );
		$this->assertEquals( gmdate( get_option( 'date_format' ) ), get_post_meta( self::$product_ids[0], 'auction_date_from', true ) );
		$this->assertEquals( gmdate( get_option( 'date_format' ), strtotime( '+5 days' ) ), get_post_meta( self::$product_ids[0], 'auction_date_to', true ) );

		$this->assertEquals( 'extend', get_post_meta( self::$product_ids[0], 'auction_extend_or_relist_auction', true ) );
		$this->assertEquals( true, get_post_meta( self::$product_ids[0], 'auction_extend_if_fail', true ) );
		$this->assertEquals( 10, get_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_if_fail', true ) );
		$this->assertEquals( true, get_post_meta( self::$product_ids[0], 'auction_extend_if_not_paid', true ) );

		$this->assertEquals( 16000.21, get_post_meta( self::$product_ids[0], 'auction_reserve_price', true ) );
		$this->assertEquals( 17000.00, get_post_meta( self::$product_ids[0], 'auction_buy_it_now_price', true ) );

		$_POST['product-type'] = 'auction_reverse';
		self::$auction_software_admin->auction_software_save_product_auction_options( self::$product_ids[1] );
		$this->assertEquals( 8000.21, get_post_meta( self::$product_ids[1], 'auction_reserve_price_reverse', true ) );
		$this->assertEquals( 6000.00, get_post_meta( self::$product_ids[1], 'auction_buy_it_now_price_reverse', true ) );
	}

	/**
	 * Test for auction_software_get_product_auction_errors function
	 */
	public function test_auction_software_get_product_auction_errors() {

		update_post_meta( self::$product_ids[0], 'auction_start_price_error', __( 'Start Price should not be negative or empty.', 'auction-software' ) );
		update_post_meta( self::$product_ids[0], 'auction_bid_increment_error', __( 'Bid Increment should not be negative.', 'auction-software' ) );
		update_post_meta( self::$product_ids[0], 'auction_date_from_error', __( 'Date From should not be empty.', 'auction-software' ) );
		update_post_meta( self::$product_ids[0], 'auction_date_to_error', __( 'Date To should not be empty.', 'auction-software' ) );

		$url = get_permalink( self::$product_ids[0] );
		$this->go_to( $url );
		$errors = self::$auction_software_admin->auction_software_get_product_auction_errors();
		$this->assertEquals( 'Start Price should not be negative or empty.<br>Bid Increment should not be negative.<br>Date From should not be empty.<br>Date To should not be empty.<br>', $errors );
	}

	/**
	 * Test for auction_software_product_imported function
	 */
	public function test_auction_software_product_imported() {
		$simple_auction_post = new WC_Product_Auction_Simple( self::$product_ids[0] );
		$metadata            = array(
			array(
				'key'   => 'auction_item_condition',
				'value' => '',
			),
			array(
				'key'   => 'auction_start_price',
				'value' => '',
			),
			array(
				'key'   => 'auction_bid_increment',
				'value' => -1,
			),
			array(
				'key'   => 'auction_date_from',
				'value' => '',
			),
			array(
				'key'   => 'auction_date_to',
				'value' => '',
			),
			array(
				'key'   => 'auction_reserve_price',
				'value' => -1,
			),
			array(
				'key'   => 'auction_reserve_price_reverse',
				'value' => -1,
			),
			array(
				'key'   => 'auction_buy_it_now_price',
				'value' => -1,
			),
			array(
				'key'   => 'auction_buy_it_now_price_reverse',
				'value' => -1,
			),
			array(
				'key'   => 'auction_wait_time_before_relist_if_fail',
				'value' => -1,
			),
			array(
				'key'   => 'auction_relist_duration_if_fail',
				'value' => -1,
			),
			array(
				'key'   => 'auction_wait_time_before_relist_if_not_paid',
				'value' => -1,
			),
			array(
				'key'   => 'auction_relist_duration_if_not_paid',
				'value' => -1,
			),
			array(
				'key'   => 'auction_wait_time_before_relist_always',
				'value' => -1,
			),
			array(
				'key'   => 'auction_relist_duration_always',
				'value' => -1,
			),
			array(
				'key'   => 'auction_wait_time_before_extend_if_fail',
				'value' => -1,
			),
			array(
				'key'   => 'auction_extend_duration_if_fail',
				'value' => -1,
			),
			array(
				'key'   => 'auction_wait_time_before_extend_if_not_paid',
				'value' => -1,
			),
			array(
				'key'   => 'auction_extend_duration_if_not_paid',
				'value' => -1,
			),
			array(
				'key'   => 'auction_wait_time_before_extend_always',
				'value' => -1,
			),
			array(
				'key'   => 'auction_extend_duration_always',
				'value' => -1,
			),
			array(
				'key'   => 'auction_extend_or_relist_auction',
				'value' => 'extend',
			),
		);
		$data                = array(
			'type'      => 'auction_simple',
			'meta_data' => $metadata,
		);
		self::$auction_software_admin->auction_software_product_imported( $simple_auction_post, $data );
		$this->assertEquals( __( 'Start Price should not be negative or empty.', 'auction-software' ), get_post_meta( self::$product_ids[0], 'auction_start_price_error', true ) );
		$this->assertEquals( __( 'Bid Increment should not be negative.', 'auction-software' ), get_post_meta( self::$product_ids[0], 'auction_bid_increment_error', true ) );
		$this->assertEquals( __( 'Date From should not be empty.', 'auction-software' ), get_post_meta( self::$product_ids[0], 'auction_date_from_error', true ) );
		$this->assertEquals( __( 'Date To should not be empty.', 'auction-software' ), get_post_meta( self::$product_ids[0], 'auction_date_to_error', true ) );
		$this->assertEquals( __( 'Reserve Price should not be negative.', 'auction-software' ), get_post_meta( self::$product_ids[0], 'auction_reserve_price_error', true ) );
	}

	/**
	 * Test for auction_software_product_auction_inventory_section function
	 */
	public function test_auction_software_product_auction_inventory_section() {
		$url = get_permalink( self::$product_ids[0] );
		$this->go_to( $url );
		ob_start();
		self::$auction_software_admin->auction_software_product_auction_inventory_section();
		$script = ob_get_clean();
		$this->assertTrue( is_string( $script ) && wp_strip_all_tags( $script ) !== $script );
	}

	/**
	 * Test for auction_software_product_auction_type_options function
	 */
	public function test_auction_software_product_auction_type_options() {
		$product_type_options = array(
			'virtual'      => array( 'wrapper_class' => '' ),
			'downloadable' => array( 'wrapper_class' => '' ),
		);
		$product_type_options = self::$auction_software_admin->auction_software_product_auction_type_options( $product_type_options );
		$this->assertEquals( ' show_if_auction_simple show_if_auction_reverse', $product_type_options['virtual']['wrapper_class'] );
		$this->assertEquals( ' show_if_auction_simple show_if_auction_reverse', $product_type_options['downloadable']['wrapper_class'] );
	}

	/**
	 * Test for auction_software_query_vars function
	 */
	public function test_auction_software_query_vars() {
		$vars = self::$auction_software_admin->auction_software_query_vars( array() );
		$this->assertTrue( in_array( 'auctions_list', $vars, true ) );
	}

	/**
	 * Test for auction_software_account_menu_items function
	 */
	public function test_auction_software_account_menu_items() {
		$items = self::$auction_software_admin->auction_software_account_menu_items( array() );
		$this->assertEquals( __( 'Auctions', 'auction-software' ), $items['auctions_list'] );
	}

	/**
	 * Test for auction_software_auctions_list_endpoint function
	 */
	public function test_auction_software_auctions_list_endpoint() {
		$user_id = self::factory()->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		ob_start();
		self::$auction_software_admin->auction_software_auctions_list_endpoint();
		$output = ob_get_clean();
		$this->assertTrue( is_string( $output ) && wp_strip_all_tags( $output ) !== $output );
	}

	/**
	 * Test for auction_software_init function
	 */
	public function test_auction_software_init() {
		wp_delete_post( self::$product_ids[1] );
		update_option( 'auction_extend_relist_settings_updated', '0' );

		self::$auction_software_admin->auction_software_init();
		$this->assertEquals( 'yes', get_post_meta( self::$product_ids[0], 'auction_extend_if_fail', true ) );
		$this->assertEquals( 'no', get_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_if_fail', true ) );
		$this->assertEquals( '7200', get_post_meta( self::$product_ids[0], 'auction_extend_duration_if_fail', true ) );
		$this->assertEquals( 'yes', get_post_meta( self::$product_ids[0], 'auction_extend_if_not_paid', true ) );
		$this->assertEquals( 'no', get_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_if_not_paid', true ) );
		$this->assertEquals( '7200', get_post_meta( self::$product_ids[0], 'auction_extend_duration_if_not_paid', true ) );
		$this->assertEquals( 'yes', get_post_meta( self::$product_ids[0], 'auction_extend_always', true ) );
		$this->assertEquals( '10', get_post_meta( self::$product_ids[0], 'auction_wait_time_before_extend_always', true ) );
		$this->assertEquals( '7200', get_post_meta( self::$product_ids[0], 'auction_extend_duration_always', true ) );
	}

	/**
	 * Test for auction_software_every_minute_cron_tasks function
	 */
	public function test_auction_software_every_minute_cron_tasks() {
		wp_delete_post( self::$product_ids[1] );
		update_post_meta( self::$product_ids[0], 'auction_date_from', gmdate( 'Y-m-d H:i:s', strtotime( '-5 days' ) ) );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '-1 days' ) ) );
		update_post_meta( self::$product_ids[0], 'auction_reserve_price_met', 'no' );
		update_post_meta( self::$product_ids[0], 'auction_is_sold', 'no' );
		update_post_meta( self::$product_ids[0], 'auction_extend_if_fail', 'yes' );
		update_post_meta( self::$product_ids[0], 'auction_errors', '' );

		// Test for auction_extend_if_fail is true.
		self::$auction_software_admin->auction_software_every_minute_cron_tasks();
		$this->assertFalse( (bool) get_post_meta( self::$product_ids[0], 'auction_is_ended', true ) );
		$this->assertFalse( (bool) get_post_meta( self::$product_ids[0], 'auction_is_sold', true ) );
		$new_to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
		$new_to_date->add( new DateInterval( ( 'PT10M' ) ) );
		$expected_date = $new_to_date->format( 'Y-m-d' );
		$actual_date   = gmdate( 'Y-m-d', strtotime( get_post_meta( self::$product_ids[0], 'auction_date_to', true ) ) );
		$this->assertEquals( $expected_date, $actual_date );
		$this->assertTrue( (bool) get_post_meta( self::$product_ids[0], self::$product_ids[0] . '_start_mail_sent', true ) );
		update_post_meta( self::$product_ids[0], 'auction_is_ended', 1 );

		// Test for auction_extend_if_fail is false.
		update_post_meta( self::$product_ids[0], 'auction_extend_if_fail', 'no' );
		update_post_meta( self::$product_ids[0], 'auction_extend_if_not_paid', 'yes' );
		update_post_meta( self::$product_ids[0], 'auction_reserve_price_met', 'yes' );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '-1 days' ) ) );

		self::$auction_software_admin->auction_software_every_minute_cron_tasks();
		$this->assertFalse( (bool) get_post_meta( self::$product_ids[0], 'auction_is_ended', true ) );
		$this->assertFalse( (bool) get_post_meta( self::$product_ids[0], 'auction_is_sold', true ) );
		$new_to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
		$new_to_date->add( new DateInterval( ( 'PT10M' ) ) );
		$expected_date = $new_to_date->format( 'Y-m-d' );
		$actual_date   = gmdate( 'Y-m-d', strtotime( get_post_meta( self::$product_ids[0], 'auction_date_to', true ) ) );
		$this->assertEquals( $expected_date, $actual_date );
		$this->assertTrue( (bool) get_post_meta( self::$product_ids[0], self::$product_ids[0] . '_start_mail_sent', true ) );
		update_post_meta( self::$product_ids[0], 'auction_is_ended', 1 );

		// Test for auction_reserve_price_met is no and auction_extend_always is yes.
		update_post_meta( self::$product_ids[0], 'auction_reserve_price_met', 'no' );
		update_post_meta( self::$product_ids[0], 'auction_extend_always', 'yes' );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '-1 days' ) ) );

		self::$auction_software_admin->auction_software_every_minute_cron_tasks();
		$this->assertFalse( (bool) get_post_meta( self::$product_ids[0], 'auction_is_ended', true ) );
		$this->assertFalse( (bool) get_post_meta( self::$product_ids[0], 'auction_is_sold', true ) );
		$new_to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
		$new_to_date->add( new DateInterval( ( 'PT10M' ) ) );
		$expected_date = $new_to_date->format( 'Y-m-d' );
		$actual_date   = gmdate( 'Y-m-d', strtotime( get_post_meta( self::$product_ids[0], 'auction_date_to', true ) ) );
		$this->assertEquals( $expected_date, $actual_date );
		$this->assertTrue( (bool) get_post_meta( self::$product_ids[0], self::$product_ids[0] . '_start_mail_sent', true ) );
	}

	/**
	 * Test for auction_software_product_auction_tab_fields function
	 */
	public function test_auction_software_product_auction_tab_fields() {
		$url = get_permalink( self::$product_ids[0] );
		$this->go_to( $url );
		if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
			include_once WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php';
		}
		ob_start();
		self::$auction_software_admin->auction_software_product_auction_tab_fields();
		$output = ob_get_clean();
		$this->assertTrue( is_string( $output ) && wp_strip_all_Tags( $output ) !== $output );
	}

	/**
	 * Test for auction_software_product_auction_tab_content function
	 */
	public function test_auction_software_product_auction_tab_content() {
		$url = get_permalink( self::$product_ids[0] );
		$this->go_to( $url );
		ob_start();
		self::$auction_software_admin->auction_software_product_auction_tab_content();
		$output = ob_get_clean();
		$this->assertTrue( is_string( $output ) && wp_strip_all_Tags( $output ) !== $output );
	}
}
