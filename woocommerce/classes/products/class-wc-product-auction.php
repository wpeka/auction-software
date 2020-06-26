<?php
/**
 * Auction Product Class.
 *
 * @package Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/products
 */

/**
 * Auction product class.
 */
class WC_Product_Auction extends WC_Product {

	/**
	 * Product data.
	 *
	 * @access public
	 * @var array $attribute_data Product attributes data.
	 */
	public $attribute_data = array(
		array(
			'type'     => 'select',
			'id'       => 'item_condition',
			'currency' => false,
			'options'  => 'new,used',
		),
		array(
			'type'     => 'text',
			'id'       => 'start_price',
			'currency' => true,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'bid_increment',
			'currency' => true,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'date_from',
			'currency' => false,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'date_to',
			'currency' => false,
			'options'  => '',
		),
	);

	/**
	 * Product relist data.
	 *
	 * @access public
	 * @var array $relist_attribute_data Product relist attributes data.
	 */
	public $relist_attribute_data = array(
		array(
			'type'     => 'checkbox',
			'id'       => 'relist_auction',
			'currency' => false,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'relist_if_fail_(hours)',
			'currency' => false,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'relist_if_not_paid_(hours)',
			'currency' => false,
			'options'  => '',
		),
		array(
			'type'     => 'text',
			'id'       => 'relist_duration_(hours)',
			'currency' => false,
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
	 * Current bid value.
	 *
	 * @access private
	 * @var int $current_bid Current bid value.
	 */
	private $current_bid;

	/**
	 * WC_Product_Auction constructor.
	 *
	 * @param int $product Product id.
	 */
	public function __construct( $product = 0 ) {
		$this->product_type = 'auction';
		$this->current_bid  = 0;
		parent::__construct( $product );
	}

	/**
	 * Get product item condition.
	 *
	 * @return string|void
	 */
	public function get_auction_item_condition() {
		return ucwords( WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_item_condition' ) );
	}

	/**
	 * Get product start price.
	 *
	 * @return int
	 */
	public function get_auction_start_price() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_start_price' );
	}

	/**
	 * Get product bid increment value.
	 *
	 * @return float|int|string
	 */
	public function get_auction_bid_increment() {
		$bid_incr = WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_bid_increment' );
		if ( '' === $bid_incr ) {
			$cur_bid = WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_current_bid' );
			if ( 0 !== (int) $cur_bid ) {
				$bid_incr = WC_Auction_Software_Helper::get_auction_bid_increment_by_range( $cur_bid );
			} else {
				$start_price = $this->get_auction_start_price();
				$bid_incr    = WC_Auction_Software_Helper::get_auction_bid_increment_by_range( $start_price );
			}
		}
		return $bid_incr;
	}

	/**
	 * Get single product add to cart text.
	 *
	 * @return string|void
	 */
	public function single_add_to_cart_text() {
		$text = __( 'Bid now', 'auction-software' );
		return $text;
	}

	/**
	 * Check if auction is ended.
	 *
	 * @return bool
	 */
	public function is_ended() {
		$auction_is_ended             = WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_is_ended' );
		$auction_is_started_and_ended = WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_is_started_and_ended' );
		if ( 1 === (int) $auction_is_ended || 1 === (int) $auction_is_started_and_ended ) {
			return true;
		}
		$date1 = new DateTime( $this->get_auction_date_to() );
		$date2 = new DateTime( current_time( 'mysql' ) );

		if ( $date1 < $date2 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get auction end date.
	 *
	 * @return int
	 */
	public function get_auction_date_to() {
		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_date_to' );
	}

	/**
	 * Check if auction is started.
	 *
	 * @return bool
	 */
	public function is_started() {
		$date1 = new DateTime( $this->get_auction_date_from() );
		$date2 = new DateTime( $this->get_auction_date_to() );
		$date3 = new DateTime( current_time( 'mysql' ) );
		if ( $date1 <= $date3 && $date2 >= $date3 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get auction start date.
	 *
	 * @return int
	 */
	public function get_auction_date_from() {

		return WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_date_from' );

	}

	/**
	 * Check if auction is relisted.
	 *
	 * @return string
	 */
	public function get_auction_relist_auction() {
		return ucwords( WC_Auction_Software_Helper::get_auction_post_meta( $this->id, 'auction_relist_auction' ) );
	}

	/**
	 * Get auction end date text.
	 *
	 * @return bool|DateInterval|string
	 */
	public function get_ends_at_text() {
		return $this->get_date_text( $this->get_auction_date_to() );
	}

	/**
	 * Get auction date text.
	 *
	 * @param string $date date.
	 * @return bool|DateInterval|string
	 */
	public function get_date_text( $date ) {
		$date                                = new DateTime( $date );
		$date_text                           = $date->diff( new DateTime( current_time( 'mysql' ) ) );
		0 !== (int) $date_text->y ? $years   = $date_text->y . ' years ' : $years = '';
		0 !== (int) $date_text->m ? $months  = $date_text->m . ' months ' : $months = '';
		0 !== (int) $date_text->d ? $days    = $date_text->d . ' days ' : $days = '';
		0 !== (int) $date_text->h ? $hours   = $date_text->h . ' hours ' : $hours = '';
		0 !== (int) $date_text->i ? $minutes = $date_text->i . ' minutes ' : $minutes = '';
		0 !== (int) $date_text->s ? $seconds = $date_text->s . ' seconds ' : $seconds = '';
		$date_text                           = $years . $months . $days . $hours . $minutes . $seconds;
		if ( ! empty( $date_text ) ) {
			return $date_text;
		}
		return '';
	}

	/**
	 * Get auction start date text.
	 *
	 * @return bool|DateInterval|string
	 */
	public function get_starts_at_text() {
		return $this->get_date_text( $this->get_auction_date_from() );
	}

	/**
	 * Check if auction is in users watchlist.
	 *
	 * @return bool
	 */
	public function is_in_users_watchlist() {
		$user_id   = get_current_user_id();
		$watchlist = get_user_meta( $user_id, 'auction_watchlist' );
		if ( isset( $watchlist[0] ) && ! empty( $watchlist[0] ) ) {
			$watchlist = explode( ',', $watchlist[0] );
			if ( in_array( $this->id, $watchlist, true ) ) {
				return true;
			}
		}
		return false;
	}
}
