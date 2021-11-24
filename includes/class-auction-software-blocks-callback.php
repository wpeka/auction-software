<?php
/**
 * The callback-blocks functionality of the plugin.
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/admin
 */
class Auction_Software_Blocks_Callback {

	/**
	 * Ending soon callback.
	 */
	public function auction_software_ending_soon_callback( $instance ) {
		global $woocommerce;

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Ending Soon Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['num_of_auctions'] ) ) {
			$number = intval( $instance['num_of_auctions'] );
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args                 = array(
			'posts_per_page' => $number,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);
		$query_args['meta_query']   = array(); // phpcs:ignore slow query
		$query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query'][] = array(
			'key'     => 'auction_date_from',
			'value'   => current_time( 'mysql' ),
			'compare' => '<=',
			'type'    => 'DATETIME',
		);
		$query_args['meta_query'][] = array(
			'key'     => 'auction_date_to',
			'value'   => current_time( 'mysql' ),
			'compare' => '>=',
			'type'    => 'DATETIME',
		);
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore slow query
		$query_args['tax_query']    = array( // phpcs:ignore slow query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);
		$query_args['orderby']      = 'meta_value';
		$query_args['order']        = 'ASC';

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;

			if ( $title ) {
				$content .= $title;
			}

			$content .= '<ul class="product_list_widget">';

			while ( $r->have_posts() ) {
				$r->the_post();

				global $product;

				$content .= '<li>
					<a href="' . get_permalink() . '">
						' . ( has_post_thumbnail() ? get_the_post_thumbnail( $r->post->ID, 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					</a> ';

				if ( ! empty( $product->get_auction_errors() ) ) {
					$content .= '<span class="auction_error">' . __( 'Please resolve the errors from Product admin.', 'auction-software' ) . '</span>';
				} else {
					if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
						if ( true === $product->is_started() ) {
							if ( $product->is_ended() ) {
								$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $product->get_auction_winning_bid() ) . '</span>';
							} else {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Current Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
							}
						} else {
							$content .= '<span class="auction-no-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
						}
					endif;

					$date_to_or_from = '';
					if ( false === $product->is_started() ) {
						if ( ! in_array( 'starts_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_starts_in startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Starts In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_from();
						endif;
					} elseif ( 1 !== (int) $hide_time && ! $product->is_ended() ) {
						if ( ! in_array( 'ends_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_time_left startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Ends In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_to();
						endif;
					}

					if ( $product->is_ended() ) {
						$content .= '<span class="has-finished">' . __( 'Auction finished', 'auction-software' ) . '</span>';
					}

					$content .= "<input type='hidden' class='timeLeftId' name='timeLeftId' value='" . $product->get_id() . "' />";

					$content .= "<input type='hidden' class='timeLeftValue" . $product->get_id() . "' value='" . esc_attr( $date_to_or_from ) . "' />";
				}

				$content .= '</li>';
			}

			$content .= '</ul>';

		}

		wp_reset_postdata();

		return $content;
	}

	/**
	 * Coming Soon Callback.
	 */
	public function auction_software_coming_soon_callback( $instance ) {

		global $woocommerce;
		
		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Coming Soon Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['num_of_auctions'] ) ) {
			$number = intval( $instance['num_of_auctions'] );
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args                    = array(
			'posts_per_page' => $number,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);
		$query_args['meta_query']      = array(); // phpcs:ignore slow query
		$query_args['meta_query'][]    = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query'][]    = array(
			'key'     => 'auction_date_from',
			'value'   => current_time( 'mysql' ),
			'compare' => '>',
			'type'    => 'DATETIME',
		);
		$query_args['meta_query']      = array_filter( $query_args['meta_query'] ); // phpcs:ignore slow query
		$query_args['tax_query']       = array( // phpcs:ignore slow query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);
		$query_args['auction_archive'] = true;
		$query_args['meta_key']        = 'auction_date_to'; // phpcs:ignore slow query
		$query_args['orderby']         = 'meta_value';
		$query_args['order']           = 'ASC';

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;

			if ( $title ) {
				$content .= $title;
			}

			$content .= '<ul class="product_list_widget">';

			while ( $r->have_posts() ) {
				$r->the_post();

				global $product;

				$content .= '<li>
					<a href="' . get_permalink() . '">
						' . ( has_post_thumbnail() ? get_the_post_thumbnail( $r->post->ID, 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					</a> ';
				if ( ! empty( $product->get_auction_errors() ) ) {
					$content .= '<span class="auction_error">' . __( 'Please resolve the errors from Product admin.', 'auction-software' ) . '</span>';
				} else {
					if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
						if ( true === $product->is_started() ) {
							if ( $product->is_ended() ) {
								$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $product->get_auction_winning_bid() ) . '</span>';
							} else {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Current Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
							}
						} else {
							$content .= '<span class="auction-no-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
						}
					endif;

					$date_to_or_from = '';
					if ( false === $product->is_started() ) {
						if ( ! in_array( 'starts_in', $excluded_fields, true ) && 1 !== (int) $hide_time ) :
							$content        .= '<p class="auction_starts_in startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Starts In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_from();
						endif;
					} elseif ( 1 !== (int) $hide_time && ! $product->is_ended() ) {
						if ( ! in_array( 'ends_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_time_left startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Ends In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_to();
						endif;
					}

					if ( $product->is_ended() ) {
						$content .= '<span class="has-finished">' . __( 'Auction finished', 'auction-software' ) . '</span>';
					}

					$content .= "<input type='hidden' class='timeLeftId' name='timeLeftId' value='" . esc_attr( $product->get_id() ) . "' />";

					$content .= "<input type='hidden' class='timeLeftValue" . esc_attr( $product->get_id() ) . "' value='" . esc_attr( $date_to_or_from ) . "' />";

				}
				$content .= '</li>';
			}

			$content .= '</ul>';

		}

		wp_reset_postdata();

		return $content;
	}

	/**
	 * Random Auction Callback.
	 */
	public function auction_software_random_auction_callback( $instance ) {
		global $woocommerce;

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Random Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['num_of_auctions'] ) ) {
			$number = intval( $instance['num_of_auctions'] );
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $number,
			'orderby'        => 'rand',
			'no_found_rows'  => 1,
		);

		$query_args['meta_query']   = array(); // phpcs:ignore slow query
		$query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore slow query
		$query_args['tax_query']    = array( // phpcs:ignore slow query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);

		$query = new WP_Query( $query_args );

		$content = '';

		if ( $query->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;

			if ( $title ) {
				$content .= $title;
			}

			$content .= '<ul class="product_list_widget">';

			while ( $query->have_posts() ) {
				$query->the_post();

				global $product;

				$content .= '<li>
					<a href="' . get_permalink() . '">
						' . ( has_post_thumbnail() ? get_the_post_thumbnail( $query->post->ID, 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					</a> ';

				if ( ! empty( $product->get_auction_errors() ) ) {
					$content .= '<span class="auction_error">' . __( 'Please resolve the errors from Product admin.', 'auction-software' ) . '</span>';
				} else {
					if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
						if ( true === $product->is_started() ) {
							if ( $product->is_ended() ) {
								$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $product->get_auction_winning_bid() ) . '</span>';
							} else {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Current Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
							}
						} else {
							$content .= '<span class="auction-no-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
						}
					endif;

					$date_to_or_from = '';
					error_log(print_r( $hide_time, true ));
					if ( false === $product->is_started() && 1 !== (int) $hide_time ) {
						if ( ! in_array( 'starts_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_starts_in startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Starts In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_from();
						endif;
					} elseif ( 1 !== (int) $hide_time && ! $product->is_ended() ) {
						if ( ! in_array( 'ends_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_time_left startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Ends In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_to();
						endif;
					}
					if ( $product->is_ended() ) {
						$content .= '<span class="has-finished">' . __( 'Auction finished', 'auction-software' ) . '</span>';
					}

					$content .= "<input type='hidden' class='timeLeftId' name='timeLeftId' value='" . $product->get_id() . "' />";

					$content .= "<input type='hidden' class='timeLeftValue" . $product->get_id() . "' value='" . $date_to_or_from . "' />";

				}
				$content .= '</li>';
			}

			$content .= '</ul>';
		}

		wp_reset_postdata();

		return $content;
	}

	/**
	 * Recent Auction Callback.
	 */
	public function auction_software_recent_auction_callback( $instance ) {
		global $woocommerce;

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recent Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['num_of_auctions'] ) ) {
			$number = intval( $instance['num_of_auctions'] );
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args = array(
			'posts_per_page' => $number,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);

		$query_args['meta_query']   = array(); // phpcs:ignore slow query
		$query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore slow query
		$query_args['tax_query']    = array( // phpcs:ignore slow query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;

			if ( $title ) {
				$content .= $title;
			}

			$content .= '<ul class="product_list_widget">';

			while ( $r->have_posts() ) {
				$r->the_post();

				global $product;

				$content .= '<li>
					<a href="' . get_permalink() . '">
						' . ( has_post_thumbnail() ? get_the_post_thumbnail( $r->post->ID, 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					</a> ';

				if ( ! empty( $product->get_auction_errors() ) ) {
					$content .= '<span class="auction_error">' . __( 'Please resolve the errors from Product admin.', 'auction-software' ) . '</span>';
				} else {
					if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
						if ( true === $product->is_started() ) {
							if ( $product->is_ended() ) {
								$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $product->get_auction_winning_bid() ) . '</span>';
							} else {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Current Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
							}
						} else {
							$content .= '<span class="auction-no-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
						}
					endif;

					$date_to_or_from = '';
					if ( false === $product->is_started() && 1 !== (int) $hide_time ) {
						if ( ! in_array( 'starts_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_starts_in startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Starts In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_from();
						endif;
					} elseif ( 1 !== (int) $hide_time && ! $product->is_ended() ) {
						if ( ! in_array( 'ends_in', $excluded_fields, true ) ) :
							$content        .= '<p class="auction_time_left startEndText' . $product->get_id() . '">' . esc_html__( 'Auction Ends In:', 'auction-software' ) . '</p>';
							$content        .= '<p class="timeLeft timeLeft' . $product->get_id() . '" id="timeLeft' . $product->get_id() . '"></p>';
							$date_to_or_from = $product->get_auction_date_to();
						endif;
					}
					if ( $product->is_ended() ) {
						$content .= '<span class="has-finished">' . __( 'Auction finished', 'auction-software' ) . '</span>';
					}

					$content .= "<input type='hidden' class='timeLeftId' name='timeLeftId' value='" . $product->get_id() . "' />";

					$content .= "<input type='hidden' class='timeLeftValue" . $product->get_id() . "' value='" . $date_to_or_from . "' />";
				}
				$content .= '</li>';
			}

			$content .= '</ul>';
		}

		wp_reset_postdata();

		return $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}