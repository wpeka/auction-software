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
	 * @param class WP_UnitTest_Factory $factory class instance.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$auction_software_admin = new Auction_Software_Admin( 'Auction-software', '1.1.0' );
		self::$product_ids            = $factory->post->create_many( 3, array( 'post_type' => 'product' ) );
		self::$auctiom_term_group     = $factory->term->create_many( 2, array( 'taxonomy' => 'product_auction_class' ) );
	}

	/**
	 * Test for auction_software_save_wc_classes function.
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
}
