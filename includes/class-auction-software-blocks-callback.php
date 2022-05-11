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

/**
 * The callback-blocks functionality class of the plugin.
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
	 *
	 * @param array $instance Instance array.
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

		$query_args = array(
			'posts_per_page' => $number,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);

		$query_args['meta_query']   = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
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
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']    = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
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
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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

					if ( 1 !== (int) $hide_time && false === $product->is_started() && ! $product->is_ended() ) {
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
	 *
	 * @param array $instance Instance array.
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
		$query_args['meta_query']      = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['meta_query'][]    = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query'][]    = array(
			'key'     => 'auction_date_from',
			'value'   => current_time( 'mysql' ),
			'compare' => '>',
			'type'    => 'DATETIME',
		);
		$query_args['meta_query']      = array_filter( $query_args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']       = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
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
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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
					if ( false === $product->is_started() && ! $product->is_ended() ) {
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
	 *
	 * @param array $instance Instance array.
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

		$query_args['meta_query']   = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']    = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
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
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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
					if ( false === $product->is_started() && 1 !== (int) $hide_time && ! $product->is_ended() ) {
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
	 *
	 * @param array $instance Instance array.
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

		$query_args['meta_query']   = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['meta_query'][] = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']    = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
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
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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
					if ( false === $product->is_started() && 1 !== (int) $hide_time && ! $product->is_ended() ) {
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
	 * Featured Auction Callback.
	 *
	 * @param array $instance Instance array.
	 */
	public function auction_software_featured_auction_callback( $instance ) {
		global $woocommerce;

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Featured Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['number'] ) ) {
			$number = intval( $instance['number'] );
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args               = array(
			'posts_per_page' => $number,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);
		$query_args['meta_query'] = $woocommerce->query->get_meta_query(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']  = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);

		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			$query_args['meta_query'][] = array(
				'key'   => '_featured',
				'value' => 'yes',
			);
		} else {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			);
		}

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;
			if ( $title ) {
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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
					if ( false === $product->is_started() && 1 !== (int) $hide_time && ! $product->is_ended() ) {
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
	 * Watchlist Callback
	 *
	 * @param array $instance Instance array.
	 */
	public function auction_software_watchlist_auction_callback( $instance ) {
		global $woocommerce;

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Watchlist Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['num_of_auctions'] ) ) {
			$number = intval( $instance['num_of_auctions'] );
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id   = get_current_user_id();
		$watchlist = get_user_meta( $user_id, 'auction_watchlist' );
		if ( ! isset( $watchlist[0] ) || empty( $watchlist[0] ) ) {
			$widget_content  = '';
			$widget_title    = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recently viewed auctions', 'auction-software' ) : $instance['title'], $instance );
			$widget_content .= $widget_title;
			$widget_content .= '<p>You don\'t have any product in your watchlist.</p>';
			return $widget_content;
		}
		if ( isset( $watchlist[0] ) && ! empty( $watchlist[0] ) ) {
			$watchlist = explode( ',', $watchlist[0] );
		}

		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args               = array(
			'posts_per_page' => $number,
			'no_found_rows'  => 1,
			'post_status'    => 'publish',
			'post_type'      => 'product',
		);
		$query_args['post__in']   = $watchlist;
		$query_args['meta_query'] = $woocommerce->query->get_meta_query(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']  = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
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
				$content .= '<p>' . $title . '</p>';
			}

			$content .= '<ul class="product_list_widget">';

			while ( $r->have_posts() ) {
				$r->the_post();

				global $product;

				if ( false === $product->is_ended() ) {
					$content .= '<li>
					<a href="' . get_permalink() . '">
						' . ( has_post_thumbnail() ? get_the_post_thumbnail( $r->post->ID, 'shop_thumbnail' ) : wc_placeholder_img( 'shop_thumbnail' ) ) . ' ' . get_the_title() . '
					</a> ';
					if ( ! empty( $product->get_auction_errors() ) ) {
						$content .= '<span class="auction_error">' . __( 'Please resolve the errors from Product admin.', 'auction-software' ) . '</span>';
					} else {
						if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
							if ( true === $product->is_started() || $product->is_ended() ) {
								if ( $product->is_ended() ) {
									$current_bid_value = $product->get_auction_current_bid();
									if ( 0.00 === (float) $current_bid_value ) {
										$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
									} else {
										$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
									}
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
						if ( 1 !== (int) $hide_time && false === $product->is_started() && ! $product->is_ended() ) {
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
			}

			$content .= '</ul>';

		}

		wp_reset_postdata();

		return $content;

	}

	/**
	 * Recently Viewed Auctions Callback
	 *
	 * @param array $instance Instance array.
	 */
	public function auction_software_recently_viewed_auction_callback( $instance ) {
		global $woocommerce;

		$viewed_products = isset( $_COOKIE['woocommerce_recently_viewed_auctions'] ) ? (array) explode( '|', sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_recently_viewed_auctions'] ) ) ) : array();
		$viewed_products = array_filter( array_map( 'absint', $viewed_products ) );

		if ( empty( $viewed_products ) ) {
			$widget_content  = '';
			$widget_title    = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recently viewed auctions', 'auction-software' ) : $instance['title'], $instance );
			$widget_content .= $widget_title;
			$widget_content .= '<p>You haven\'t viewed any product yet</p>';
			return $widget_content;
		}

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Recently viewed auctions', 'auction-software' ) : $instance['title'], $instance );
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
			'post__in'       => $viewed_products,
			'orderby'        => 'rand',
		);

		$query_args['meta_query']      = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['meta_query'][]    = $woocommerce->query->stock_status_meta_query();
		$query_args['meta_query']      = array_filter( $query_args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query_args['tax_query']       = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);
		$query_args['auction_archive'] = true;

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;

			if ( $title ) {
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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
					if ( 1 !== (int) $hide_time && false === $product->is_started() && ! $product->is_ended() ) {
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
	 * My auctions callback
	 *
	 * @param array $instance Instance array.
	 */
	public function auction_software_my_auction_callback( $instance ) {
		global $woocommerce, $wpdb;

		$title  = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'My Auctions', 'auction-software' ) : $instance['title'], $instance );
		$number = 5;
		if ( isset( $instance['num_of_auctions'] ) ) {
			$number = intval( $instance['num_of_auctions'] );
		}

		if ( ! is_user_logged_in() ) {
			return;
		}
		$user_id       = get_current_user_id();
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

		$excluded_fields = get_option( 'auctions_excluded_fields_product_widget', array() );

		$query_args               = array(
			'posts_per_page' => $number,
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

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = $instance['hide_time_left'] ? 1 : 0;

			if ( $title ) {
				$content .= '<p>' . $title . '</p>';
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
						if ( true === $product->is_started() || $product->is_ended() ) {
							if ( $product->is_ended() ) {
								$current_bid_value = $product->get_auction_current_bid();
								if ( 0.00 === (float) $current_bid_value ) {
									$content .= '<span class="auction-current-bid">' . __( 'No bids yet', 'auction-software' ) . '</span>';
								} else {
									$content .= '<span class="auction-current-bid">' . __( 'Winning Bid: ', 'auction-software' ) . wc_price( $current_bid_value ) . '</span>';
								}
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
					if ( false === $product->is_started() && ! $product->is_ended() ) {
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

}
