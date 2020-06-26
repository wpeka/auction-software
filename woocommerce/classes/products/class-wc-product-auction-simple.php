<?php
/**
 * Auction Simple Product Class.
 *
 * @package Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/products
 */

/**
 * Auction simple product class.
 */
class WC_Product_Auction_Simple extends WC_Product_Auction {

	/**
	 * Product data.
	 *
	 * @access public
	 * @var array $attribute_data Product attributes data.
	 */
	public $attribute_data = array(
		array(
			'type'     => 'text',
			'id'       => 'reserve_price',
			'currency' => true,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'buy_it_now_price',
			'currency' => true,
			'options'  => '',
		),
	);

	/**
	 * Product type.
	 *
	 * @access private
	 * @var string $product_type Product type.
	 */
	private $product_type;

	/**
	 * WC_Product_Auction_Simple constructor.
	 *
	 * @param int $product Product id.
	 */
	public function __construct( $product = 0 ) {
		$this->product_type = 'auction_simple';
		parent::__construct( $product );
	}

	/**
	 * Get product type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'auction_simple';
	}

	/**
	 * Check if product is sold.
	 *
	 * @return int
	 */
	public function get_auction_is_sold() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_is_sold' );
	}

	/**
	 * Get product price.
	 *
	 * @param string $context View context.
	 * @return int|string
	 */
	public function get_price( $context = 'view' ) {
		$result = '';
		if ( $this->is_started() ) {
			$result = $this->get_auction_buy_it_now_price();
		} elseif ( $this->is_ended() ) {
			$result = WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_highest_bid' );
		}
		return $result;
	}

	/**
	 * Get product buy it now price.
	 *
	 * @return int
	 */
	public function get_auction_buy_it_now_price() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_buy_it_now_price' );
	}

	/**
	 * Get buy it now add to cart text.
	 *
	 * @return string|void
	 */
	public function get_buy_it_now_cart_text() {
		if ( $this->is_ended() ) {
			update_post_meta( get_the_ID(), 'auction_winning_bid', $this->get_auction_current_bid() );
			$text = sprintf(
				/* translators: 1: Price */
				__( 'Add to cart for %s', 'auction-software' ),
				wc_price( WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_highest_bid' ) )
			);
		} elseif ( $this->is_started() ) {
			if ( $this->get_auction_buy_it_now_price() < $this->get_auction_current_bid() ) {
				$text = __( 'NA', 'auction-software' );
			} else {
				$text = sprintf(
					/* translators: 1: Price */
					__( 'Buy it now for %s', 'auction-software' ),
					wc_price( $this->get_auction_buy_it_now_price() )
				);
			}
		} elseif ( ! $this->is_ended() && ! $this->is_started() ) {
			$text = __( 'Auction not started', 'auction-software' );
		}
		return $text;
	}

	/**
	 * Get product current bid.
	 *
	 * @return int
	 */
	public function get_auction_current_bid() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_current_bid' );
	}

	/**
	 * Set product current bid.
	 *
	 * @param double $current_bid Current bid.
	 * @param double $next_bid Next bid.
	 * @param int    $user_id User id.
	 * @param int    $post_id Product id.
	 * @return int
	 */
	public function set_auction_current_bid( $current_bid, $next_bid, $user_id, $post_id ) {
		$initial_bid_placed = WC_Auction_Software_Helper::get_auction_post_meta( $post_id, 'auction_initial_bid_placed' );
		$start_price        = WC_Auction_Software_Helper::get_auction_post_meta( $post_id, 'auction_start_price' );
		if ( $next_bid < $start_price ) {
			return 5;
		}
		if ( ( $next_bid === $this->get_auction_start_price() && 0 === (int) $current_bid && 1 !== (int) $initial_bid_placed ) ) {
			// Initial bid.
			// Update current bid post meta and save user bid info in auction software logs.
			update_post_meta( $post_id, 'auction_initial_bid_placed', 1 );
			update_post_meta( $post_id, 'auction_current_bid', $next_bid );
			update_post_meta( $post_id, 'auction_highest_bid', $next_bid );
			update_post_meta( $post_id, 'auction_highest_bid_user', $user_id );
			$result = WC_Auction_Software_Helper::set_auction_bid_logs( $user_id, $post_id, $next_bid, current_time( 'mysql' ) );
			$data   = array(
				'product_id' => $post_id,
				'user_id'    => $user_id,
			);
			// Send outbid email to users.
			do_action( 'woocommerce_auction_software_outbid', $data );
			return $result;
		} elseif ( ( $next_bid === $this->get_auction_start_price() && 0 !== (int) $current_bid ) || ( $next_bid <= $current_bid ) ) {
			// If bid is same or lower than current bid.
			return 3;
		} elseif ( $next_bid < $current_bid + $this->get_auction_bid_increment() ) {
			// If bid is lower than bid increment.
			return 4;
		} elseif ( ( $next_bid > $current_bid ) ) {
			$is_user_winning = $this->check_if_user_has_winning_bid( $next_bid, $user_id, $post_id );
			if ( $is_user_winning ) {
				return 2;
			}
			update_post_meta( $post_id, 'auction_current_bid', $next_bid );
			update_post_meta( $post_id, 'auction_highest_bid', $next_bid );
			update_post_meta( $post_id, 'auction_highest_bid_user', $user_id );
			$result = WC_Auction_Software_Helper::set_auction_bid_logs( $user_id, $post_id, $next_bid, current_time( 'mysql' ) );
			$data   = array(
				'product_id' => $post_id,
				'user_id'    => $user_id,
			);
			// Send outbid email to users.
			do_action( 'woocommerce_auction_software_outbid', $data );
			return $result;
		} else {
			return 0;
		}
	}

	/**
	 * Check if user has winning bid.
	 *
	 * @param double $next_bid Next bid.
	 * @param int    $user_id User id.
	 * @param int    $post_id Product id.
	 * @return int
	 */
	public function check_if_user_has_winning_bid( $next_bid, $user_id, $post_id ) {
		$highest_bid      = WC_Auction_Software_Helper::get_auction_post_meta( $post_id, 'auction_highest_bid' );
		$highest_bid_user = WC_Auction_Software_Helper::get_auction_post_meta( $post_id, 'auction_highest_bid_user' );
		if ( ! ( $this->is_ended() ) && $next_bid >= $highest_bid && $user_id === (int) $highest_bid_user ) {
			return 1;
		} elseif ( $this->is_ended() && (int) $user_id === (int) $highest_bid_user ) {
			return 1;
		}
		return 0;
	}

	/**
	 * Get product price html.
	 *
	 * @param string $price Price.
	 * @return mixed|string|void
	 */
	public function get_price_html( $price = '' ) {
		if ( $this->is_ended() ) {
			$price = '<span class="auction-current-bid">' . __( 'Winning Bid:', 'auction-software' ) . '</span>' . wc_price( $this->get_auction_winning_bid() ) . '</span>';
		} else {
			$price = '<span class="auction-current-bid">' . __( 'Current Bid:', 'auction-software' ) . '</span>' . wc_price( $this->get_auction_current_bid() ) . '</span>';
		}
		return apply_filters( 'woocommerce_get_price_html', $price, $this );
	}

	/**
	 * Get auction winning bid.
	 *
	 * @return int
	 */
	public function get_auction_winning_bid() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_winning_bid' );
	}

	/**
	 * Check if auction reserve price is met.
	 *
	 * @param int $post_id Product id.
	 * @return int
	 */
	public function check_if_reserve_price_met( $post_id ) {
		if ( ! ( $this->is_ended() ) && $this->get_auction_reserve_price() <= $this->get_auction_current_bid() ) {
			update_post_meta( $post_id, 'auction_reserve_price_met', 'yes' );
			return 1;
		} elseif ( $this->is_ended() && $this->get_auction_reserve_price() <= WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_highest_bid' ) ) {
			return 1;
		}
		update_post_meta( $post_id, 'auction_reserve_price_met', 'no' );
		return 0;
	}

	/**
	 * Get auction reserve price.
	 *
	 * @return int
	 */
	public function get_auction_reserve_price() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_reserve_price' );
	}

	/**
	 * Get auction errors.
	 *
	 * @return int
	 */
	public function get_auction_errors() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_errors' );
	}
}
