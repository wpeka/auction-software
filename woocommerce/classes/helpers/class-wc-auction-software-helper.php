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
	 * @param string $label Field label.
	 * @param bool   $desc_tip Help tip.
	 * @param string $description Help tip description.
	 * @param bool   $currency Currency.
	 * @param string $options Options.
	 * @param array  $custom_attributes Custom attributes.
	 * @param string $class Class.
	 * @param string $wrapper_class Wrapper class.
	 * @return int|void
	 */
	public static function get_product_tab_fields( $input_type, $id, $label, $desc_tip, $description, $currency = false, $options = '', $custom_attributes = array(), $class = '', $wrapper_class = '' ) {
		switch ( $input_type ) {
			case 'text':
				if ( ( 'date_from' === $id || 'date_to' === $id ) ) {
					return woocommerce_wp_text_input(
						array(
							'id'                => 'auction_' . $id,
							'label'             => self::get_id_title( $id, $label, $currency ),
							'custom_attributes' => $custom_attributes,
							'class'             => $class,
							'desc_tip'          => $desc_tip,
							'description'       => $description,
						)
					);
				} else {
					return woocommerce_wp_text_input(
						array(
							'id'                => 'auction_' . $id,
							'label'             => self::get_id_title( $id, $label, $currency ),
							'custom_attributes' => $custom_attributes,
							'class'             => 'wc_input_price',
							'wrapper_class'     => $wrapper_class,
							'desc_tip'          => $desc_tip,
							'description'       => $description,
						)
					);
				}
			case 'select':
				$options        = explode( ',', $options );
				$select_options = array();
				foreach ( $options as $option_value ) {
					$select_options[ $option_value ] = self::get_id_title( $option_value );
				}
				return woocommerce_wp_select(
					array(
						'id'                => 'auction_' . $id,
						'options'           => $select_options,
						'label'             => self::get_id_title( $id, $label ),
						'custom_attributes' => $custom_attributes,
						'class'             => $class,
						'desc_tip'          => $desc_tip,
						'description'       => $description,
					)
				);
			case 'date':
				return woocommerce_wp_text_input(
					array(
						'id'                => 'auction_' . $id,
						'label'             => self::get_id_title( $id, $label, $currency ),
						'custom_attributes' => $custom_attributes,
						'type'              => 'date',
						'date-type'         => 'years',
						'class'             => $class,
						'desc_tip'          => $desc_tip,
						'description'       => $description,
					)
				);
			case 'checkbox':
				return woocommerce_wp_checkbox(
					array(
						'id'            => 'auction_' . $id,
						'label'         => self::get_id_title( $id, $label, $currency ),
						'class'         => $class,
						'wrapper_class' => $wrapper_class,
						'desc_tip'      => $desc_tip,
						'description'   => $description,
					)
				);
			default:
				return 0;
		}
	}

	/**
	 * Get product field label with currency symbol.
	 *
	 * @param int    $id ID.
	 * @param string $label Field label.
	 * @param bool   $currency Currency.
	 * @return string
	 */
	public static function get_id_title( $id, $label = '', $currency = false ) {
		$id_string = ucwords( str_replace( '_', ' ', $id ) );
		if ( true === $currency ) {
			$id_string .= ' (' . get_woocommerce_currency_symbol() . ')';
		} elseif ( ! empty( $label ) ) {
			$id_string = $label;
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
			$auction_date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
			$timezone_string     = get_option( 'timezone_string' );
			if ( ! $timezone_string ) {
				$timezone_string = 'UTC' . wp_timezone_string();
			}
			$auction_history_string = '
				<table id="auction_history_table">
                <tr id="auction_history_table_heading">
                    <td>' . __( 'User', 'auction-software' ) . '</td>
                    <td>' . __( 'Bid', 'auction-software' ) . '</td>
                    <td>' . __( 'Date', 'auction-software' ) . '</td>
                    <td>' . __( 'Auto', 'auction-software' ) . '</td>
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
		                    <td>' . gmdate( $auction_date_format, strtotime( $auction_history_item['date'] ) ) . '</td>
		                    <td></td>
	                	</tr>
						';
					}
				} elseif ( 'relisted' === $status ) {
					$auction_history_item_string = '
					<tr>
                    <td>' . __( 'Auction is relisted.', 'auction-software' ) . '</td>
                    <td></td>
                    <td>' . gmdate( $auction_date_format, strtotime( $auction_history_item['date'] ) ) . '</td>
                    <td></td>
                </tr>
				';
				} elseif ( 'buyitnow' === $status ) {
					$user_info                   = get_userdata( $auction_history_item['user_id'] );
					$auction_history_item_string = '
					<tr>
					<td>' . __( 'Buy it now used by ', 'auction-software' ) . $user_info->display_name . '.</td>
		            <td>' . wc_price( $auction_history_item['bid'] ) . '</td>
                    <td>' . gmdate( $auction_date_format, strtotime( $auction_history_item['date'] ) ) . '</td>
                    <td></td>
                </tr>
				';
				} else {
					$user_info                   = get_userdata( $auction_history_item['user_id'] );
					$proxy_text                  = ( 1 === (int) $auction_history_item['proxy'] ) ? 'Auto' : '';
					$auction_history_item_string = '
					<tr>
                    <td>' . $user_info->display_name . '</td>
                    <td>' . wc_price( $auction_history_item['bid'] ) . '</td>
                    <td>' . gmdate( $auction_date_format, strtotime( $auction_history_item['date'] ) ) . '</td>
                    <td>' . $proxy_text . '</td>
                </tr>
				';
				}
				$previous_value          = $status;
				$auction_history_string .= $auction_history_item_string;
			}
			$auction_history_string .= '</table>';
			$get_auction_date_from   = get_post_meta( $post_id, 'auction_date_from' );
			$auction_date_from       = '';
			if ( isset( $get_auction_date_from[0] ) ) {
				$auction_date_from = $get_auction_date_from[0];
			}
			$auction_history_string .= '<br><p>' . __( 'Started On ', 'auction-software' ) . gmdate( $auction_date_format, strtotime( $auction_date_from ) ) . ' (' . $timezone_string . ')</p>';
			return $auction_history_string;
		} else {
			$auction_history_string = '
				<table id="auction_history_table">
                <tr id="auction_history_table_heading">
                    <td>' . __( 'User', 'auction-software' ) . '</td>
                    <td>' . __( 'Bid', 'auction-software' ) . '</td>
                    <td>' . __( 'Date', 'auction-software' ) . '</td>
                    <td>' . __( 'Auto', 'auction-software' ) . '</td>
                </tr>
                <tr>
                    <td colspan="4">No bids yet</td>
                </tr>
                </table>
			';
			return $auction_history_string;
		}
	}

	/**
	 * Clear auction logs in case of relisting auction.
	 *
	 * @param int  $post_id Product ID.
	 * @param bool $flag Flag.
	 */
	public static function clear_auction_bid_logs( $post_id, $flag = false ) {
		global $wpdb;
		if ( $flag ) {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d and status IS NOT NULL', array( $post_id ) ) ); // db call ok; no-cache ok.
		} else {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d', array( $post_id ) ) ); // db call ok; no-cache ok.
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
	 * @param int    $proxy Is proxy bid.
	 * @return int
	 */
	public static function set_auction_bid_logs( $user_id, $post_id, $next_bid, $date, $status = null, $proxy = 0 ) {
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
				'proxy'      => $proxy,
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

	/**
	 * Returns my auctions list.
	 *
	 * @param int $user_id User id.
	 * @return array|int
	 */
	public static function get_auctions_list_products( $user_id ) {
		global $woocommerce,$wpdb;

		if ( ! is_user_logged_in() ) {
			return;
		}
		$post_ids      = array();
		$user_auctions = $wpdb->get_results( $wpdb->prepare( 'SELECT  DISTINCT auction_id FROM ' . $wpdb->prefix . 'auction_software_logs WHERE user_id = %d', array( $user_id ) ), ARRAY_N ); // db call ok; no-cache ok.
		if ( isset( $user_auctions ) && ! empty( $user_auctions ) ) {
			foreach ( $user_auctions as $auction ) {
				$post_ids[] = $auction[0];
			}
		} else {
			return;
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$query_args               = array(
			'posts_per_page' => 10,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);
		$query_args['post__in']   = $post_ids;
		$query_args['meta_query'] = $woocommerce->query->get_meta_query(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']  = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);

		$results = new WP_Query( $query_args );

		if ( ! empty( $results ) ) {
			return $results;
		} else {
			return 0;
		}
	}

	/**
	 * Get auction user by Status.
	 *
	 * @param int $post_id Product id.
	 * @return array
	 */
	public static function get_auction_user_by_status( $post_id ) {
		global $wpdb;
		$user = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d AND status = %s', array( $post_id, 'buyitnow' ) ), ARRAY_A ); // db call ok; no-cache ok.

		if ( ! empty( $user ) ) {
			return $user[0]['user_id'];
		} else {
			return '';
		}
	}
	/**
	 * Get Bid Won user by Auction.
	 *
	 * @param int $post_id Product id.
	 * @return array
	 */
	public static function get_won_user_by_auction( $post_id ) {
		global $wpdb;

		$highest_bid = self::get_auction_post_meta( $post_id, 'auction_winning_bid' );
		$user        = $wpdb->get_results( $wpdb->prepare( 'SELECT user_id FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d AND bid = %d', array( $post_id, $highest_bid ) ), ARRAY_A ); // db call ok; no-cache ok.

		if ( ! empty( $user ) ) {
			return $user[0]['user_id'];
		} else {
			return '';
		}
	}
}
