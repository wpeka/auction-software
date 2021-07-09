<?php
/**
 * Class Auction_Software_Admin class Test
 *
 * @package Auction_Software
 * @subpackage Auction_Software/tests
 */

/**
 * Required file.
 */
require_once ABSPATH . 'wp-admin/includes/ajax-actions.php';

/**
 * Require Wpadcenter_Admin class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-auction-software-admin.php';
/**
 * Unit test cases for ajax request from admin class.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/tests
 * @author     WPEka <hello@wpeka.com>
 */
class AjaxTest extends WP_Ajax_UnitTestCase {

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
	 * @param class WP_UnitTest_Factory $factory class instance.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$auction_software_admin = new Auction_Software_Admin( 'Auction-software', '1.1.0' );
		self::$product_ids            = $factory->post->create_many( 3, array( 'post_type' => 'product' ) );
		self::$auctiom_term_group     = $factory->term->create_many( 2, array( 'taxonomy' => 'product_auction_class' ) );
		wp_set_object_terms( self::$product_ids[0], 'auction_simple', 'product_type' );
		update_post_meta( self::$product_ids[0], 'auction_date_from', gmdate( 'Y-m-d H:i:s', strtotime( '-1 days' ) ) );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '+5 days' ) ) );

		update_post_meta( self::$product_ids[0], 'auction_current_bid', '12000' );

		update_post_meta( self::$product_ids[1], 'auction_date_from', gmdate( 'Y-m-d H:i:s' ) );
		update_post_meta( self::$product_ids[1], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '+5 days' ) ) );
	}

	/**
	 * Test for auction_software_save_wc_classes function.
	 *
	 * @since 1.1.0
	 */
	public function test_auction_software_save_wc_classes() {

		// become administrator.
		$this->_setRole( 'administrator' );

		// setup a default request.
		$_POST['wc_auction_classes_nonce'] = wp_create_nonce( 'wc_auction_classes_nonce' );
		$_POST['action']                   = 'auction_software_save_wc_classes';
		$changes                           = array(
			self::$auctiom_term_group[0] => array(
				'name'        => '200',
				'slug'        => '600',
				'description' => 'Auction Bid 200',
			),
			self::$auctiom_term_group[1] => array(
				'name'        => '1200',
				'slug'        => '1600',
				'description' => 'Auction Bid 1200',
			),
		);
		$_POST['changes']                  = $changes;
		try {
			$this->_handleAjax( 'auction_software_save_wc_classes' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}
		$response    = json_decode( $this->_last_response );
		$class_array = array();
		foreach ( $response->data->auction_classes as $class ) {
			array_push( $class_array, $class->term_id );
		}
		$this->assertTrue( in_array( self::$auctiom_term_group[0], $class_array, true ) );
		$this->assertTrue( in_array( self::$auctiom_term_group[1], $class_array, true ) );
	}

	/**
	 * Test for auction_software_wc_ajax_add_to_auctionwatchlist function
	 *
	 * @since 1.1.0
	 */
	public function test_auction_software_wc_ajax_add_to_auctionwatchlist() {

		// setup a default request.
		$_POST['product_id'] = self::$product_ids[0];
		$_POST['action']     = 'woocommerce_ajax_add_to_auctionwatchlist';

		// become administrator.
		$user_id = self::factory()->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		update_user_meta( $user_id, 'auction_watchlist', self::$product_ids[1] );
		try {
			$this->_handleAjax( 'woocommerce_ajax_add_to_auctionwatchlist' );

		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}
		// get response.
		$response = json_decode( $this->_last_response );
		$this->assertEquals( 'success', $response );
		$watchlist_products = get_user_meta( $user_id, 'auction_watchlist', true );
		$this->assertTrue( in_array( self::$product_ids[0], explode( ',', $watchlist_products ) ) ); //phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	}

	/**
	 * Test for auction_software_wc_ajax_add_to_cart_simple function
	 *
	 * @since 1.1.0
	 */
	public function test_auction_software_wc_ajax_add_to_cart_simple() {

		// set a default request.
		$_POST['product_id']  = self::$product_ids[0];
		$_POST['auction_bid'] = '13000';
		try {
			$this->_handleAjax( 'woocommerce_ajax_add_to_cart_simple' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}
		$response = json_decode( $this->_last_response );
		$this->assertEquals( 'notice', $response->status );
		$this->assertEquals( '<div class="woocommerce-message error auction-error" role="alert">You must be logged in to place bid. <a class="button" href="">Log in</a></div>', $response->notice_message );
		$this->assertEquals( 0, $response->change_bid );
		$this->assertEquals( get_post_meta( self::$product_ids[0], 'auction_date_to', true ), $response->seconds );
	}

	/**
	 * Test for auction_software_wc_ajax_remove_from_auctionwatchlist function
	 *
	 * @since 1.1.0
	 */
	public function test_auction_software_wc_ajax_remove_from_auctionwatchlist() {

		// setup a default request.
		$_POST['product_id'] = self::$product_ids[0];
		$_POST['action']     = 'woocommerce_ajax_remove_from_auctionwatchlist';

		// become administrator.
		$user_id = self::factory()->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		update_user_meta( $user_id, 'auction_watchlist', array( self::$product_ids[0] ) );
		$this->assertEquals( self::$product_ids[0], get_user_meta( $user_id, 'auction_watchlist', true )[0] );
		try {
			$this->_handleAjax( 'woocommerce_ajax_remove_from_auctionwatchlist' );

		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}
		// get response.
		$response = json_decode( $this->_last_response );
		$this->assertEquals( 'success', $response );
		$watchlist_products = get_user_meta( $user_id, 'auction_watchlist', true );
		$this->assertTrue( empty( $watchlist_products ) );
	}
}

