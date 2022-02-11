<?php
/**
 * The widget-specific functionality for Watchlist auctions.
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/widgets
 */

/**
 * Elementor Widget_Watchlist_Auctions Widget.
 *
 * @since 1.0.0
 */
class Auction_Software_Watchlist_Auctions extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve Widget_Watchlist_Auctions widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'Auction-Software-Watchlist-Auctions';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Widget_Watchlist_Auctions widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Auction Software Watchlist Auctions', 'auction-software' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Widget_Watchlist_Auctions widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-wordpress';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Widget_Watchlist_Auctions widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wp-auction' );
	}

	/**
	 * Register Widget_Watchlist_Auctions widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'auction-software' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'widget_title_Watchlist',
			array(
				'label'       => __( 'Title', 'auction-software' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'input_type'  => 'text',
				'default'     => __( 'Watchlist Auctions', 'auction-software' ),
				'placeholder' => __( 'Type your title here', 'auction-software' ),
			)
		);
		$this->add_control(
			'widget_post_no_Watchlist',
			array(
				'label'       => __( 'Number of Auctions to Show', 'auction-software' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'input_type'  => 'number',
				'default'     => __( '5', 'auction-software' ),
				'placeholder' => __( 'Enter the No. of Auctions to show', 'auction-software' ),
			)
		);
		$this->add_control(
			'show_time_Watchlist',
			array(
				'label'     => esc_html__( 'Hide Time Left', 'auction-software' ),
				'type'      => \Elementor\Controls_Manager::SWITCHER,
				'label_on'  => esc_html__( 'Show', 'auction-software' ),
				'label_off' => esc_html__( 'Hide', 'auction-software' ),
				'default'   => 'no',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render Widget_Watchlist_Auctions widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		global $woocommerce;

		$cache = wp_cache_get( 'widget_watchlist_auctions', 'widget' );
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}
		$settings   = $this->get_settings_for_display();
		$title      = __( $settings['widget_title_Watchlist'], 'auction-software' ); //phpcs:ignore
			$number = 5;
		if ( $settings['widget_post_no_Watchlist'] ) {
			if ( ! is_numeric( $settings['widget_post_no_Watchlist'] ) ) {
				$number = 10;
			} elseif ( $number < 1 ) {
				$number = 1;
			} elseif ( $number > 15 ) {
				$number = 15;
			} else {
				$number = $settings['widget_post_no_Watchlist'];
			}
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id   = get_current_user_id();
		$watchlist = get_user_meta( $user_id, 'auction_watchlist' );
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
		$query_args['meta_query'] = $woocommerce->query->get_meta_query(); // phpcs:ignore slow query
		$query_args['tax_query']  = array( // phpcs:ignore slow query
			array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => $auction_types,
			),
		);

		$r = new WP_Query( $query_args );

		$content = '';

		if ( $r->have_posts() ) {
			$hide_time = empty( $settings['show_time_Watchlist'] ) ? 0 : 1;

			if ( $title ) {
				$content .= $title;
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

						$content .= "<input type='hidden' class='timeLeftValue" . $product->get_id() . "' value='" . $date_to_or_from . "' />";
					}
					$content .= '</li>';
				}
			}

			$content .= '</ul>';

		}

		wp_reset_postdata();

		if ( isset( $args['widget_id'] ) ) {
			$cache[ $args['widget_id'] ] = $content;
		}

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_cache_set( 'widget_watchlist_auctions', $cache, 'widget' );
	}

}
