<?php
/**
 * Elementor newplugin Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Widget_Random_Auctions extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve newplugin widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'Auction Software Random Auctions';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve newplugin widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Auction Software Random Auctions', 'auction-software' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve newplugin widget icon.
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
	 * Retrieve the list of categories the newplugin widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'wp-auction' ];
	}

	/**
	 * Register newplugin widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'auction-software' ),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'widget_title_random',
			[
				'label' => __( 'Title', 'auction-software' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'input_type' => 'text',
				'default'=>__('Random Auctions','auction-software'),
				'placeholder' => __( 'Type your title here', 'auction-software' ),
			]
		);
		$this->add_control(
			'widget_post_no_random',
			[
				'label' => __( 'Number of Auctions to Show', 'auction-software' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'input_type' => 'number',
				'default'=>__('5','auction-software'),
				'placeholder' => __( 'Enter the No. of Auctions to show', 'auction-software' ),
			]
		);
		$this->add_control(
			'show_time_random',
			[
				'label' => esc_html__( 'Hide Time Left', 'auction-software' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'auction-software' ),
				'label_off' => esc_html__( 'Hide', 'auction-software' ),
				'default' => 'no',
			]
		);
		
		$this->end_controls_section();

	}

	/**
	 * Render newplugin widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
    protected function render() {
		global $woocommerce, $wpdb;
        $settings=$this->get_settings_for_display();
		$cache = wp_cache_get( 'widget_random_auctions', 'widget' );
		if ( ! is_array( $cache ) ) {
			$cache = array();
		}
		$title  = __($settings['widget_title_random'],'auction-software');
			$number = 5;
			if ($settings['widget_post_no_random'] ) {
				if ( ! is_numeric( $settings['widget_post_no_random'] ) ) {
					$number = 10;
				} elseif ( $number < 1 ) {
					$number = 1;
				} elseif ( $number > 15 ) {
					$number = 15;
				} else {
					$number = $settings['widget_post_no_random'];
				}
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
			$hide_time = empty( $instance['show_time_random'] ) ? 0 : 1;

			// $content .= $before_widget;

			if ( $title ) {
				$content .=$title;
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

			$content .= '</ul>';
			// $content .= $after_widget;
		}

		wp_reset_postdata();

		if ( isset( $args['widget_id'] ) ) {
			$cache[ $args['widget_id'] ] = $content;
		}

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		wp_cache_set( 'widget_random_auctions', $cache, 'widget' );
	}

}