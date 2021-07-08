<?php
/**
 * Class Auction_Software_Public_Test
 *
 * @since 1.1.0
 *
 * @package Auction_Software
 * @subpackage Auction-Software/tests
 */

/**
 * Require Auction_Software_Public class.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-auction-software-public.php';

/**
 * Auction_Software_Public class test cases.
 *
 * @since 1.1.0
 */
class Auction_Software_Public_Test extends WP_UnitTestCase {

	/**
	 * Auction_Software_Public class instance created
	 *
	 * @since 1.1.0
	 * @var class Auction_Software_Public class instance $auction_software_public
	 *
	 * @access public
	 */
	public static $auction_software_public;

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
		self::$auction_software_public = new Auction_Software_Public( 'Auction-software', '1.1.0' );
		self::$product_ids             = $factory->post->create_many( 3, array( 'post_type' => 'product' ) );
		self::$simple_auction_post     = get_post( self::$product_ids[0] );
		wp_set_object_terms( self::$product_ids[0], 'auction_simple', 'product_type' );
		wp_set_object_terms( self::$product_ids[1], 'auction_reverse', 'product_type' );

		update_post_meta( self::$product_ids[0], 'auction_date_from', gmdate( 'Y-m-d H:i:s' ) );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '+5 days' ) ) );
		update_post_meta( self::$product_ids[1], 'auction_date_from', gmdate( 'Y-m-d H:i:s' ) );
		update_post_meta( self::$product_ids[1], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '+5 days' ) ) );

	}

	/**
	 * Test for a constructor function
	 *
	 * @since 1.1.0
	 */
	public function test_construct() {
		$obj = new Auction_Software_public( 'Auction-software', '1.1.0' );
		$this->assertTrue( $obj instanceof Auction_Software_public );
	}

	/**
	 * Test for enqueue_styles function
	 *
	 * @since 1.1.0
	 */
	public function test_enqueue_styles() {
		self::$auction_software_public->enqueue_styles();
		global $wp_styles;
		$this->assertTrue( in_array( 'Auction-software', $wp_styles->queue, true ) );
	}

	/**
	 * Test for enqueue_scripts function
	 *
	 * @since 1.1.0
	 */
	public function test_enqueue_scripts() {
		self::$auction_software_public->enqueue_scripts();
		global $wp_scripts;
		$this->assertTrue( in_array( 'Auction-software', $wp_scripts->queue, true ) );
		$data = $wp_scripts->get_data( 'Auction-software', 'data' );
		$this->assertTrue( strpos( $data, 'adminUrl' ) !== false );
		$this->assertTrue( strpos( $data, 'ajaxurl' ) !== false );
		$this->assertTrue( strpos( $data, 'nonce' ) !== false );
		$this->assertTrue( strpos( $data, 'timezone' ) !== false );
		$this->assertTrue( strpos( $data, 'offset' ) !== false );
		$this->assertTrue( strpos( $data, 'days' ) !== false );
		$this->assertTrue( strpos( $data, 'hours' ) !== false );
		$this->assertTrue( strpos( $data, 'minutes' ) !== false );
		$this->assertTrue( strpos( $data, 'seconds' ) !== false );
		$this->assertTrue( strpos( $data, 'default' ) !== false );
	}

	/**
	 * Test for auction_software_wc_single_product_summary function
	 */
	public function test_auction_software_wc_single_product_summary() {
		$prod = wc_get_product( self::$product_ids[0] );
		global $product;
		$product = $prod;
		ob_start();
		self::$auction_software_public->auction_software_wc_single_product_summary();
		$output = ob_get_clean();
		$this->assertTrue( is_string( $output ) && wp_strip_all_tags( $output ) !== $output );

		$prod    = wc_get_product( self::$product_ids[1] );
		$product = $prod;
		ob_start();
		self::$auction_software_public->auction_software_wc_single_product_summary();
		$output = ob_get_clean();
		$this->assertTrue( is_string( $output ) && wp_strip_all_tags( $output ) !== $output );
	}

	/**
	 * Test for auction_software_wc_remove_prices function
	 */
	public function test_auction_software_wc_remove_prices() {
		$price = self::$auction_software_public->auction_software_wc_remove_prices( '200$', wc_get_product( self::$product_ids[0] ) );
		$this->assertTrue( empty( $price ) );
	}

	/**
	 * Test for auction_software_wc_quantity_input_args function
	 */
	public function test_auction_software_wc_quantity_input_args() {
		$quantity = self::$auction_software_public->auction_software_wc_quantity_input_args( array( 'input_value' => '100' ), wc_get_product( self::$product_ids[0] ) );
		$this->assertEquals( '100', $quantity['min_value'] );
		$this->assertEquals( '100', $quantity['max_value'] );
	}

	/**
	 * Test for auction_software_wc_product_add_to_cart_text function
	 */
	public function test_auction_software_wc_product_add_to_cart_text() {
		$text = self::$auction_software_public->auction_software_wc_product_add_to_cart_text( '', wc_get_product( self::$product_ids[0] ) );
		$this->assertEquals( __( 'Read more', 'auction-software' ), $text );
		wp_set_object_terms( self::$product_ids[2], 'simple', 'post_type' );
		$text = self::$auction_software_public->auction_software_wc_product_add_to_cart_text( '', wc_get_product( self::$product_ids[2] ) );
		$this->assertEquals( __( 'Add to cart', 'auction-software' ), $text );

		update_post_meta( self::$product_ids[0], 'auction_date_from', gmdate( 'Y-m-d H:i:s', strtotime( '-2 days' ) ) );
		update_post_meta( self::$product_ids[0], 'auction_date_to', gmdate( 'Y-m-d H:i:s', strtotime( '+2 days' ) ) );
		update_post_meta( self::$product_ids[0], 'auction_errors', '' );

		$text = self::$auction_software_public->auction_software_wc_product_add_to_cart_text( '', wc_get_product( self::$product_ids[0] ) );
		$this->assertEquals( __( 'Bid Now', 'auction-software' ), $text );
	}

	/**
	 * Test for auction_software_wc_loop_add_to_cart_link function.
	 */
	public function test_auction_software_wc_loop_add_to_cart_link() {
		$url = get_permalink( self::$product_ids[0] );
		$this->go_to( $url );
		update_post_meta( self::$product_ids[0], 'auction_is_ended', '1' );
		$button  = self::$auction_software_public->auction_software_wc_loop_add_to_cart_link( "<input type='button'/>", wc_get_product( self::$product_ids[0] ) );
		$exp_btn = '<a class="button" style="display:none;" href="' . wc_get_product( self::$product_ids[0] )->get_permalink() . '">' . __( 'View product', 'auction-software' ) . '</a>';
		$this->assertEquals( $exp_btn, $button );
	}

	/**
	 * Test for auction_software_wc_check_if_sold function
	 */
	public function test_auction_software_wc_check_if_sold() {
		WC()->cart->add_to_cart( self::$product_ids[0] );
		update_post_meta( self::$product_ids[0], 'auction_is_sold', '1' );
		ob_start();
		self::$auction_software_public->auction_software_wc_check_if_sold();
		$output = ob_get_clean();
		$this->assertTrue(
			is_string( $output ) &&
			( strpos( $output, __( 'Product has already sold.', 'auction-software' ) ) !== false ) &&
			wp_strip_all_tags( $output ) !== $output
		);

		WC()->cart->add_to_cart( self::$product_ids[0] );
		update_post_meta( self::$product_ids[0], 'auction_is_sold', '0' );
		update_post_meta( self::$product_ids[0], 'auction_is_ended', '0' );
		update_post_meta( self::$product_ids[0], 'auction_buy_it_now_price', '10000' );
		update_post_meta( self::$product_ids[0], 'auction_current_bid', '11000' );
		ob_start();
		self::$auction_software_public->auction_software_wc_check_if_sold();
		$output = ob_get_clean();
		$this->assertTrue(
			is_string( $output ) &&
			( strpos( $output, __( 'Product current bid exceeded buy it now price.', 'auction-software' ) ) !== false ) &&
			wp_strip_all_tags( $output ) !== $output
		);

		WC()->cart->add_to_cart( self::$product_ids[0] );
		update_post_meta( self::$product_ids[0], 'auction_is_ended', '1' );
		$user = self::factory()->user->create( array( 'role' => 'editor' ) );
		update_post_meta( self::$product_ids[0], 'auction_highest_bid_user', $user );
		ob_start();
		self::$auction_software_public->auction_software_wc_check_if_sold();
		$output = ob_get_clean();
		$this->assertTrue(
			is_string( $output ) &&
			( strpos( $output, __( 'Auction has ended and you are not a winner.', 'auction-software' ) ) !== false ) &&
			wp_strip_all_tags( $output ) !== $output
		);
	}

}
