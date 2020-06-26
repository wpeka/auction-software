<?php
/**
 * Auction Software Helper class.
 *
 * @since 1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/helpers
 */

/**
 * Auction Software Helper class.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/helpers
 */
class WC_Auction_Software_Helper {

	/**
	 * Output product tab fields.
	 *
	 * @param string $input_type Input type.
	 * @param string $id Field id.
	 * @param bool   $currency Currency.
	 * @param string $options Options.
	 * @param array  $custom_attributes Custom attributes.
	 * @param string $class Class.
	 * @return int|void
	 */
	public static function get_product_tab_fields( $input_type, $id, $currency = false, $options = '', $custom_attributes = array(), $class = '' ) {
		switch ( $input_type ) {
			case 'text':
				if ( ( 'date_from' === $id || 'date_to' === $id ) ) {
					return woocommerce_wp_text_input(
						array(
							'id'                => 'auction_' . $id,
							'label'             => self::get_id_title( $id, $currency ),
							'custom_attributes' => $custom_attributes,
							'class'             => $class,
						)
					);
				} else {
					return woocommerce_wp_text_input(
						array(
							'id'                => 'auction_' . $id,
							'label'             => self::get_id_title( $id, $currency ),
							'custom_attributes' => $custom_attributes,
							'class'             => 'wc_input_price',
						)
					);
				}
			case 'select':
				$options        = explode( ',', $options );
				$select_options = [];
				foreach ( $options as $option_value ) {
					$select_options[ $option_value ] = self::get_id_title( $option_value );
				}
				return woocommerce_wp_select(
					array(
						'id'                => 'auction_' . $id,
						'options'           => $select_options,
						'label'             => self::get_id_title( $id ),
						'custom_attributes' => $custom_attributes,
						'class'             => $class,
					)
				);
			case 'date':
				return woocommerce_wp_text_input(
					array(
						'id'                => 'auction_' . $id,
						'label'             => self::get_id_title( $id, $currency ),
						'custom_attributes' => $custom_attributes,
						'type'              => 'date',
						'date-type'         => 'years',
						'class'             => $class,
					)
				);
			case 'checkbox':
				return woocommerce_wp_checkbox(
					array(
						'id'    => 'auction_' . $id,
						'label' => self::get_id_title( $id, $currency ),
						'class' => $class,
					)
				);
			default:
				return 0;
		}
	}

	/**
	 * Get product field label with currency symbol.
	 *
	 * @param int  $id ID.
	 * @param bool $currency Currency.
	 * @return string|void
	 */
	public static function get_id_title( $id, $currency = false ) {
		$id_string = ucwords( str_replace( '_', ' ', $id ) );
		if ( true === $currency ) {
			$id_string .= ' (' . get_woocommerce_currency_symbol() . ')';
		}
		return $id_string;
	}

	/**
	 * Get auction history.
	 *
	 * @param int $post_id Product id.
	 * @return string
	 */
	public static function get_auction_history( $post_id ) {
		global $wpdb;
		$auction_history = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d ORDER BY id DESC', array( $post_id ) ), ARRAY_A ); // db call ok; no-cache ok.
		if ( ! empty( $auction_history ) ) {
			$auction_history_string = '
				<table id="auction_history_table">
                <tr id="auction_history_table_heading">
                    <td>' . __( 'User', 'auction-software' ) . '</td>
                    <td>' . __( 'Bid', 'auction-software' ) . '</td>
                    <td>' . __( 'Date', 'auction-software' ) . '</td>
                </tr>
			';
			$previous_value         = null;
			foreach ( $auction_history as $auction_history_item ) {
				$status = $auction_history_item['status'];
				if ( 'ended' === $status ) {
					if ( $previous_value && $previous_value === $status ) {
						$auction_history_item_string = '';
					} else {
						$auction_history_item_string = '
						<tr>
		                    <td>' . __( 'Auction has ended.', 'auction-software' ) . '</td>
		                    <td></td>
		                    <td>' . $auction_history_item['date'] . '</td>
	                	</tr>
						';
					}
				} elseif ( 'relisted' === $status ) {
					$auction_history_item_string = '
					<tr>
                    <td>' . __( 'Auction is relisted.', 'auction-software' ) . '</td>
                    <td></td>
                    <td>' . $auction_history_item['date'] . '</td>
                </tr>
				';
				} elseif ( 'buyitnow' === $status ) {
					$user_info                   = get_userdata( $auction_history_item['user_id'] );
					$auction_history_item_string = '
					<tr>
					<td>' . __( 'Buy it now used by ', 'auction-software' ) . $user_info->display_name . '.</td>
		            <td>' . wc_price( $auction_history_item['bid'] ) . '</td>
                    <td>' . $auction_history_item['date'] . '</td>
                </tr>
				';
				} else {
					$user_info                   = get_userdata( $auction_history_item['user_id'] );
					$auction_history_item_string = '
					<tr>
                    <td>' . $user_info->display_name . '</td>
                    <td>' . wc_price( $auction_history_item['bid'] ) . '</td>
                    <td>' . $auction_history_item['date'] . '</td>
                </tr>
				';
				}
				$previous_value          = $status;
				$auction_history_string .= $auction_history_item_string;
			}
			$auction_history_string   .= '</table>';
			$get_auction_date_from = get_post_meta( $post_id, 'auction_date_from' );
			$auction_date_from     = '';
			if ( isset( $get_auction_date_from[0] ) ) {
				$auction_date_from = $get_auction_date_from[0];
			}
			$auction_history_string .= '<br><p>' . __( 'Auction Started at ', 'auction-software' ) . $auction_date_from . '</p>';
			return $auction_history_string;
		} else {
			$auction_history_string = '
				<table id="auction_history_table">
                <tr id="auction_history_table_heading">
                    <td>' . __( 'User', 'auction-software' ) . '</td>
                    <td>' . __( 'Bid', 'auction-software' ) . '</td>
                    <td>' . __( 'Date', 'auction-software' ) . '</td>
                </tr>
                </table>
			';
			return $auction_history_string;
		}
	}

	/**
	 * Get auction logs.
	 *
	 * @param int    $user_id User id.
	 * @param int    $post_id Product id.
	 * @param double $next_bid Next bid.
	 * @param string $date Date.
	 * @param null   $status Status.
	 * @return int
	 */
	public static function set_auction_bid_logs( $user_id, $post_id, $next_bid, $date, $status = null ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'auction_software_logs';
		$success    = $wpdb->insert(
			$table_name,
			array(
				'user_id'    => $user_id,
				'auction_id' => $post_id,
				'bid'        => $next_bid,
				'date'       => $date,
				'status'     => $status,
			)
		); // db call ok; no-cache ok.

		if ( $success ) {
			update_post_meta( $post_id, 'auction_current_bid', sanitize_text_field( $next_bid ) );
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * Get product meta data by key.
	 *
	 * @param int $post_id Product id.
	 * @param int $key Meta key.
	 * @return int
	 */
	public static function get_auction_post_meta( $post_id, $key ) {
		$get_auction_post_meta = get_post_meta( $post_id, $key );
		$auction_post_meta     = 0;
		if ( isset( $get_auction_post_meta[0] ) ) {
			$auction_post_meta = $get_auction_post_meta[0];
		}
		return $auction_post_meta;
	}

	/**
	 * Check if user notified for auction start.
	 *
	 * @param int $user_id User id.
	 * @param int $post_id Product id.
	 * @return int
	 */
	public static function check_if_user_notified_auction_start( $user_id, $post_id ) {
		return self::get_auction_post_meta( $post_id, 'auction_notify_' . $user_id . '_' . $post_id . '_is_started' );
	}

	/**
	 * Check if user notified for auction ended.
	 *
	 * @param int $user_id User id.
	 * @param int $post_id Product id.
	 * @return int
	 */
	public static function check_if_user_notified_auction_end( $user_id, $post_id ) {
		return self::get_auction_post_meta( $post_id, 'auction_notify_' . $user_id . '_' . $post_id . '_is_ended' );
	}

	/**
	 * Check if user notified for auction won.
	 *
	 * @param int $user_id User id.
	 * @param int $post_id Product id.
	 * @return int
	 */
	public static function check_if_user_notified_auction_win( $user_id, $post_id ) {
		return self::get_auction_post_meta( $post_id, 'auction_notify_' . $user_id . '_' . $post_id . '_is_won' );
	}

	/**
	 * Get bid increment by range.
	 *
	 * @param double $cur_bid Current bid.
	 * @return float|string
	 */
	public static function get_auction_bid_increment_by_range( $cur_bid ) {
		$bid_incr = '';
		$ranges   = get_terms(
			'product_auction_class',
			array(
				'hide_empty' => '0',
			)
		);
		foreach ( $ranges as $range ) {
			if ( $cur_bid >= $range->name && $cur_bid <= $range->slug ) {
				$bid_incr = $range->description;
			}
		}
		$highest_bid_range_increment_value = 0.01;
		foreach ( $ranges as $range ) {
			if ( $highest_bid_range_increment_value < $range->description ) {
				$highest_bid_range_increment_value = $range->description;
			}
		}
		if ( '' === $bid_incr ) {
			$bid_incr = $highest_bid_range_increment_value;
		}
		return $bid_incr;
	}

	/**
	 * Get time left for auction.
	 *
	 * @param int $product_id Product id.
	 * @return array
	 */
	public static function get_time_left_for_auction( $product_id ) {
		$time_to    = self::get_auction_post_meta( $product_id, 'auction_date_to' );
		$time_from  = self::get_auction_post_meta( $product_id, 'auction_date_from' );
		$return_arr = array();
		try {
			$date_to = new DateTime( $time_to );
		} catch ( Exception $e ) {
			return $return_arr;
		}
		try {
			$date_from = new DateTime( $time_from );
		} catch ( Exception $e ) {
			return $return_arr;
		}
		try {
			$date_cur = new DateTime( current_time( 'mysql' ) );
		} catch ( Exception $e ) {
			return $return_arr;
		}
		if ( $date_from > $date_cur ) { // Future auction.
			$is_future = true;
			array_push( $return_arr, $time_from );
			array_push( $return_arr, $is_future );
			return $return_arr;
		} elseif ( $date_to > $date_cur ) {
			array_push( $return_arr, $time_to );
			return $return_arr;
		} else {
			array_push( $return_arr, 0 );
			return $return_arr;
		}
	}
}
