<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/public
 * @author     WPeka Club <support@wpeka.com>
 */
class Auction_Software_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Auction_Software_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Auction_Software_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/auction-software-public' . AUCTION_SOFTWARE_SUFFIX . '.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'dashicons' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Auction_Software_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Auction_Software_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/auction-software-public' . AUCTION_SOFTWARE_SUFFIX . '.js', array( 'jquery' ), $this->version, false );
		$data_to_be_passed = array(
			'adminUrl' => get_admin_url(),
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ajax_nonce' ),
			'timezone' => wc_timezone_string(),
			'offset'   => wc_timezone_offset(),
			'days'     => __( ' days ', 'auction-software' ),
			'hours'    => __( ' hours ', 'auction-software' ),
			'minutes'  => __( ' minutes ', 'auction-software' ),
			'seconds'  => __( ' seconds ', 'auction-software' ),
			'Days'     => __( ' Days ', 'auction-software' ),
			'Hours'    => __( ' Hours ', 'auction-software' ),
			'Minutes'  => __( ' Minutes ', 'auction-software' ),
			'Seconds'  => __( ' Seconds ', 'auction-software' ),
			'default'  => false,
		);

		wp_enqueue_script( 'wc-cart-fragments' );

		$timezone_string = wc_timezone_string();
		if ( strpos( $timezone_string, ':' ) !== false ) {
			$data_to_be_passed['default'] = true;
			if ( strpos( $timezone_string, ':3' ) !== false ) {
				$timezone_string = str_replace( ':3', '.5', $timezone_string );
			} elseif ( strpos( $timezone_string, ':4' ) !== false ) {
				$timezone_string = str_replace( ':4', '.7', $timezone_string );
			} else {
				$timezone_string = str_replace( ':', '.', $timezone_string );
			}
			$data_to_be_passed['timezone'] = $timezone_string;
		}
		$time_interval                     = get_option( 'auctions_update_bidding_info_duration', 60 );
		$data_to_be_passed['timeinterval'] = $time_interval;

		wp_localize_script( $this->plugin_name, 'php_vars', $data_to_be_passed );

	}

	/**
	 * Auction single product template.
	 */
	public function auction_software_wc_single_product_summary() {
		global $product;
		if ( 'auction_simple' === $product->get_type() ) {
			$template_path = plugin_dir_path( __FILE__ ) . 'partials/';
			wc_get_template(
				'single-product/auction-software-simple.php',
				'',
				'',
				trailingslashit( $template_path )
			);
		} elseif ( 'auction_reverse' === $product->get_type() ) {
			$template_path = plugin_dir_path( __FILE__ ) . 'partials/';
			wc_get_template(
				'single-product/auction-software-reverse.php',
				'',
				'',
				trailingslashit( $template_path )
			);
		}
		do_action( 'auction_software_wc_single_product_summary', $product );
	}

	/**
	 * Track view of viewed auctions.
	 */
	public function auction_software_track_view() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}

		global $post;

		$viewed_products = isset( $_COOKIE['woocommerce_recently_viewed_auctions'] ) ? (array) explode( '|', sanitize_text_field( wp_unslash( $_COOKIE['woocommerce_recently_viewed_auctions'] ) ) ) : array();

		if ( ! in_array( $post->ID, $viewed_products, true ) ) {
			$viewed_products[] = $post->ID;
		}

		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}

		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed_auctions', implode( '|', $viewed_products ) );
	}

	/**
	 * Remove auction product price display.
	 *
	 * @param string $price Product price.
	 * @param object $product Product.
	 * @return string
	 */
	public function auction_software_wc_remove_prices( $price, $product ) {
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		if ( in_array( $product->get_type(), $auction_types, true ) ) {
			if ( ! is_admin() ) {
				$price = '';
			}
		}
		return $price;
	}

	/**
	 * Remove auction product quantity.
	 *
	 * @param array  $args Arguments.
	 * @param object $product Product.
	 * @return mixed
	 */
	public function auction_software_wc_quantity_input_args( $args, $product ) {
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		if ( in_array( $product->get_type(), $auction_types, true ) ) {
			$input_value       = $args['input_value'];
			$args['min_value'] = $input_value;
			$args['max_value'] = $input_value;
		}
		return $args;
	}

	/**
	 * Auction product loop display.
	 */
	public function auction_software_wc_after_shop_loop_item() {
		$product_id    = get_the_ID();
		$product       = wc_get_product( $product_id );
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		if ( in_array( $product->get_type(), $auction_types, true ) ) {
			$excluded_fields = get_option( 'auctions_excluded_fields_product_shop', array() );
			$return_arr      = WC_Auction_Software_Helper::get_time_left_for_auction( $product_id );
			$cur_bid         = WC_Auction_Software_Helper::get_auction_post_meta( $product_id, 'auction_current_bid' );
			if ( ! empty( $return_arr ) && false === $product->is_ended() && '' === $product->get_auction_errors() ) {
				if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
					echo "<p class='description'>" . esc_html__( 'Current Bid: ', 'auction-software' ) . esc_attr( get_woocommerce_currency_symbol() ) . esc_attr( $cur_bid ) . '</p>';
				endif;
				if ( isset( $return_arr[1] ) ) {
					if ( ! in_array( 'starts_in', $excluded_fields, true ) ) :
						echo '<p class="auction_starts_in startEndText' . esc_attr( $product_id ) . '">' . esc_html_e( 'Auction Starts In:', 'auction-software' ) . '</p>';
						echo '<p class="timeLeft timeLeft' . esc_attr( $product_id ) . '" id="timeLeft' . esc_attr( $product_id ) . '"></p>';
					endif;
				} else {
					if ( ! in_array( 'ends_in', $excluded_fields, true ) ) :
						echo '<p class="auction_time_left startEndText' . esc_attr( $product_id ) . '">' . esc_html_e( 'Auction Ends In:', 'auction-software' ) . '</p>';
						echo '<p class="timeLeft timeLeft' . esc_attr( $product_id ) . '" id="timeLeft' . esc_attr( $product_id ) . '"></p>';
					endif;
				}
				if ( isset( $return_arr[0] ) ) {
					echo "<input type='hidden' class='timeLeftValue" . esc_attr( $product_id ) . "' value='" . esc_attr( $return_arr[0] ) . "' />";
				}
				echo "<input type='hidden' class='timeLeftId' name='timeLeftId' value='" . esc_attr( $product_id ) . "' />";
			} elseif ( $product->is_ended() ) {
				echo "<p class='description'>" . esc_html__( 'Auction has ended.', 'auction-software' ) . '</p>';
			} else {
				if ( ! empty( $product->get_auction_errors() ) ) { ?>
					<p class="auction_error">
					<?php esc_html_e( 'Please resolve the errors from Product admin.', 'auction-software' ); ?> <br>
					</p>
					<?php
				}
			}
		}
	}

	/**
	 * Auction add to cart text.
	 *
	 * @param string $text Text.
	 * @param object $product Product.
	 * @return string|void
	 */
	public function auction_software_wc_product_add_to_cart_text( $text, $product ) {
		if ( $product ) {
			$auction_types = apply_filters(
				'auction_software_auction_types',
				array(
					'auction_simple',
					'auction_reverse',
				)
			);
			if ( in_array( $product->get_type(), $auction_types, true ) ) {
				$time_left = WC_Auction_Software_Helper::get_time_left_for_auction( $product->get_id() );
				if ( $time_left && '' === $product->get_auction_errors() ) {
					$text = __( 'Bid Now', 'auction-software' );
				} else {
					$text = __( 'Read more', 'auction-software' );
				}
			} else {
				$text = __( 'Add to cart', 'auction-software' );
			}
		}
		return $text;
	}

	/**
	 * Auction add to cart link for loop display.
	 *
	 * @param string $button Button.
	 * @param object $product Product.
	 * @return string
	 */
	public function auction_software_wc_loop_add_to_cart_link( $button, $product ) {
		$product_id    = get_the_ID();
		$product       = wc_get_product( $product_id );
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		if ( in_array( $product->get_type(), $auction_types, true ) ) {
			$is_ended = WC_Auction_Software_Helper::get_auction_post_meta( $product_id, 'auction_is_ended' );
			if ( 1 !== (int) $is_ended ) {
				return $button;
			} else {
				$button_text = __( 'View product', 'auction-software' );
				$button      = '<a class="button" style="display:none;" href="' . $product->get_permalink() . '">' . $button_text . '</a>';

				return $button;
			}
		} else {
			return $button;
		}
	}

	/**
	 * Auction add to cart validations.
	 *
	 * @param bool $passed Passed.
	 * @param int  $product_id Product id.
	 * @param int  $quantity Quantity.
	 * @return mixed
	 */
	public function auction_software_wc_add_to_cart_validation( $passed, $product_id, $quantity ) {
		if ( WC()->cart->is_empty() ) {
			return $passed;
		}
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = wc_get_product( $cart_item['product_id'] );
			if ( in_array( $product->get_type(), $auction_types, true ) && $product_id === $cart_item['product_id'] ) {
				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}
		return $passed;
	}

	/**
	 * WooCommerce order complete status from Admin dashboard.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $order_status Order status.
	 */
	public function auction_software_wc_order_edit_status( $order_id, $order_status ) {
		$this->auction_software_wc_payment_complete( $order_status, $order_id );
	}

	/**
	 * WooCommerce order complete status.
	 *
	 * @param string $order_status Order status.
	 * @param int    $order_id Order id.
	 * @return mixed
	 */
	public function auction_software_wc_payment_complete( $order_status, $order_id ) {
		if ( 'completed' === $order_status ) {
			global $wpdb;
			$order = wc_get_order( $order_id );
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product_id = $item['product_id'];
				$product    = wc_get_product( $product_id );
				if ( $product ) {
					if ( $product->is_type( 'auction_simple' ) || $product->is_type( 'auction_reverse' ) ) {
						if ( $product->is_type( 'auction_simple' ) ) {
							$get_auction_buy_it_now_price = get_post_meta( $product_id, 'auction_buy_it_now_price' );
						} elseif ( $product->is_type( 'auction_reverse' ) ) {
							$get_auction_buy_it_now_price = get_post_meta( $product_id, 'auction_buy_it_now_price_reverse' );
						}
						$auction_buy_it_now_price = '';
						if ( isset( $get_auction_buy_it_now_price[0] ) ) {
							$auction_buy_it_now_price = $get_auction_buy_it_now_price[0];
						}
						$is_ended = get_post_meta( $product_id, 'auction_is_ended' );
						if ( 1 === (int) $is_ended[0] ) {
							update_post_meta( $product_id, 'auction_is_sold', 1 );
							update_post_meta( $product_id, 'auction_winning_bid', $product->get_price() );
						} else {
							update_post_meta( $product_id, 'auction_is_sold', 1 );
							update_post_meta( $product_id, 'auction_is_ended', 1 );
							update_post_meta( $product_id, 'auction_winning_bid', $auction_buy_it_now_price );
							WC_Auction_Software_Helper::set_auction_bid_logs( $order->get_user_id(), $product_id, $auction_buy_it_now_price, current_time( 'mysql' ), 'buyitnow' );
							WC_Auction_Software_Helper::set_auction_bid_logs( $order->get_user_id(), $product_id, $auction_buy_it_now_price, current_time( 'mysql' ), 'ended' );
						}
					}
					do_action( 'auction_software_wc_payment_complete', $product_id, $product, $order_id, $order, $item );
				}
			}
		}
		return $order_status;
	}

	/**
	 * Check auction product if sold already.
	 */
	public function auction_software_wc_check_if_sold() {
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = wc_get_product( $cart_item['product_id'] );
			if ( in_array( $product->get_type(), $auction_types, true ) ) {
				if ( 1 === (int) $product->get_auction_is_sold() ) {
					WC()->cart->remove_cart_item( $cart_item_key );
					wc_print_notice(
						sprintf(
							/* translators: 1: href link */
							esc_html__( 'Product has already sold. %s', 'auction-software' ),
							sprintf(
								/* translators: %s href link */
								'<a href="%s" class="wc-backward">Return to shop</a>',
								esc_url( wc_get_page_permalink( 'shop' ) )
							)
						),
						'error'
					);
				} elseif ( ( ( $product->get_auction_buy_it_now_price() < $product->get_auction_current_bid() && 'auction_reverse' !== $product->get_type() ) || ( 'auction_reverse' === $product->get_type() && $product->get_auction_buy_it_now_price() > $product->get_auction_current_bid() ) ) && ! $product->is_ended() ) {
					WC()->cart->remove_cart_item( $cart_item_key );
					wc_print_notice(
						sprintf(
							/* translators: 1: href link */
							esc_html__( 'Product current bid exceeded buy it now price.. %s', 'auction-software' ),
							sprintf(
								/* translators: %s href link */
								'<a href="%s" class="wc-backward">Return to shop</a>',
								esc_url( wc_get_page_permalink( 'shop' ) )
							)
						),
						'error'
					);
				} elseif ( $product->is_ended() ) {
					$winner = WC_Auction_Software_Helper::get_auction_post_meta( $cart_item['product_id'], 'auction_highest_bid_user' );
					if ( get_current_user_id() !== (int) $winner ) {
						WC()->cart->remove_cart_item( $cart_item_key );
						wc_print_notice(
							sprintf(
								/* translators: 1: href link */
								esc_html__( 'Auction has ended and you are not a winner. %s', 'auction-software' ),
								sprintf(
									/* translators: %s href link */
									'<a href="%s" class="wc-backward">Return to shop</a>',
									esc_url( wc_get_page_permalink( 'shop' ) )
								)
							),
							'error'
						);
					}
				}
			}
		}
	}

	/**
	 * Product add to cart.
	 */
	public function auction_software_wc_ajax_add_to_cart() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( is_user_logged_in() ) {
			$product_id        = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
			$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
			$quantity          = isset( $_POST['quantity'] ) ? wc_stock_amount( sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) ) : 1;
			$variation_id      = isset( $_POST['variation_id'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) ) : '';
			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
			$product_status    = get_post_status( $product_id );
			try {
				if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id ) && 'publish' === $product_status ) {
					do_action( 'woocommerce_ajax_added_to_cart', $product_id );
					if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
						wc_add_to_cart_message( array( $product_id => $quantity ), true );
					}
					WC_AJAX::get_refreshed_fragments();
				} else {
					$data = array(
						'error'       => true,
						'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
					);
					echo wp_send_json( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			} catch ( Exception $e ) {
				$data = array(
					'error'       => true,
					'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
				);
				echo wp_send_json( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			$notice_message                  = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'You must be logged in.', 'auction-software' ) . '</div>';
			$json_response['status']         = 'login_error';
			$json_response['notice_message'] = $notice_message;
			echo wp_send_json( $json_response ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		wp_die();
        // phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Simple auction product add to cart.
	 */
	public function auction_software_wc_ajax_add_to_cart_simple() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		global $product;
		$json_response  = array();
		$product_id     = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		$product_id     = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$product        = wc_get_product( $product_id );
		$notice_message = '';
		$current_bid    = $product->get_auction_current_bid();
		$increment_bid  = $product->get_auction_bid_increment();
		$date_to        = $product->get_auction_date_to();
		$date_time_to   = datetime::createfromformat( 'Y-m-d H:i:s', $date_to );
		$user_id        = get_current_user_id();
		$flag           = 0;
		$max_flag       = 0;
		if ( isset( $_POST['auction_bid'] ) ) {
			if ( true === $product->is_started() ) {
				if ( is_user_logged_in() ) {
					if ( get_the_author_meta( 'user_email', $product->post->post_author ) !== wp_get_current_user()->user_email ) {
						$next_bid                = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : 0;
						$set_auction_current_bid = $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $product_id );
						if ( 5 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Please enter a higher amount.', 'auction-software' ) . '</div>';
						} elseif ( 4 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Bid should be greater than or equal to bid increment.', 'auction-software' ) . '</div>';
						} elseif ( 1 === (int) $set_auction_current_bid ) {
							$notice_message       = '<div class="woocommerce-message success auction-success" role="alert">' . __( 'Bid placed successfully.', 'auction-software' ) . '</div>';
							$is_reserve_price_met = $product->check_if_reserve_price_met( $product_id );
							if ( 'yes' === $product->is_proxy_bidding() && $is_reserve_price_met ) {
								$max_bid_user = $product->get_auction_max_bid_user();
								if ( ! empty( $max_bid_user ) ) {
									$max_bid = $product->get_auction_max_bid();
									if ( $user_id !== (int) $max_bid_user ) {
										$auto_current_bid   = $product->get_auction_current_bid();
										$auto_increment_bid = $increment_bid;
										$auto_next_bid      = $auto_current_bid + $auto_increment_bid;
										if ( $auto_next_bid > $max_bid ) {
											$auto_next_bid = $max_bid;
										}
										$set_auction_auto_bid = $product->set_auction_current_bid( $auto_current_bid, $auto_next_bid, $max_bid_user, $product_id, 1 );
										if ( 1 === (int) $set_auction_auto_bid ) {
											$notice_message = '<div class="woocommerce-message success auction-success" role="alert">' . __( 'Bid placed successfully, but you have been outbid!', 'auction-software' ) . '</div>';
										}
									}
									if ( $user_id === (int) $max_bid_user ) {
										$max_flag       = 1;
										$change_max_bid = (float) $max_bid;
									}
								}
							}
							$flag                 = 1;
							$change_current_bid   = (float) $product->get_auction_current_bid();
							$change_price_box_bid = (float) $product->get_auction_current_bid() + (float) $increment_bid;
						} elseif ( 2 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message success auction-success" role="alert">' . __( 'Your bid is winning, no need to place again.', 'auction-software' ) . '</div>';
						} elseif ( 3 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Please enter a higher amount.', 'auction-software' ) . '</div>';
						} else {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Bid not placed successfully.', 'auction-software' ) . '</div>';
						}
					} else {
						$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'You can not place bid on your products.', 'auction-software' ) . '</div>';
					}
				} else {
					$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'You must be logged in to place bid. ', 'auction-software' ) . '<a class="button" href="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '">' . __( 'Log in', 'auction-software' ) . '</a></div>';
				}
			} else {
				$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Auction has expired. Please reload the page.', 'auction-software' ) . '</div>';
			}
		}
		! empty( $notice_message ) ? $json_response['status'] = 'notice' : $json_response['status'] = '';
		$json_response['notice_message']                      = $notice_message;
		if ( 1 === $flag ) {
			if ( 'yes' === $product->is_anti_snipping() ) {
				$seconds      = get_option( 'auctions_anti_snipping_duration', 0 );
				$trigger_time = get_option( 'auctions_anti_snipping_trigger_time', 5 );
				$time         = current_time( 'timestamp' ); // phpcs:ignore
				$timeplus     = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $trigger_time . ' minutes', $time ) );
				if ( $timeplus > $date_to ) {
					$date_time_to->add( new DateInterval( 'PT' . $seconds . 'S' ) );
					update_post_meta( $product_id, 'auction_date_to', $date_time_to->format( 'Y-m-d H:i:s' ) );
				}
			}
			$json_response['change_bid']          = 1;
			$json_response['change_current_bid']  = wc_price( $change_current_bid );
			$json_response['change_pricebox_bid'] = round( $change_price_box_bid, 2 );
			if ( __( 'NA', 'auction-software' ) !== $product->get_buy_it_now_cart_text() ) {
				$json_response['remove_buy_it_now_cart_text'] = 0;
			} else {
				$json_response['remove_buy_it_now_cart_text'] = 1;
			}
			if ( 1 === $max_flag ) {
				$json_response['change_max_bid']       = 1;
				$json_response['change_max_bid_value'] = wc_price( $change_max_bid );
			} else {
				$json_response['change_max_bid'] = 0;
			}
		} else {
			$json_response['change_bid'] = 0;
		}
		$json_response['seconds'] = $date_time_to->format( 'Y-m-d H:i:s' );
		echo wp_send_json( $json_response ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
        // phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Reverse auction product add to cart.
	 */
	public function auction_software_wc_ajax_add_to_cart_reverse() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		global $product;
		$json_response  = array();
		$product_id     = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		$product_id     = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$product        = wc_get_product( $product_id );
		$notice_message = '';
		$current_bid    = $product->get_auction_current_bid();
		$increment_bid  = $product->get_auction_bid_increment();
		$date_to        = $product->get_auction_date_to();
		$date_time_to   = datetime::createfromformat( 'Y-m-d H:i:s', $date_to );
		$user_id        = get_current_user_id();
		$flag           = 0;
		$max_flag       = 0;
		$next_bid       = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : 0;
		if ( $next_bid <= 0 ) {
			$is_negative = 1;
		} else {
			$is_negative = 0;
		}
		if ( isset( $_POST['auction_bid'] ) && 0 === (int) $is_negative ) {
			if ( true === $product->is_started() ) {
				if ( is_user_logged_in() ) {
					if ( get_the_author_meta( 'user_email', $product->post->post_author ) !== wp_get_current_user()->user_email ) {
						$set_auction_current_bid = $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $product_id );
						if ( 5 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Please enter a lower amount.', 'auction-software' ) . '</div>';
						} elseif ( 4 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Bid should be less than or equal to bid increment.', 'auction-software' ) . '</div>';
						} elseif ( 1 === (int) $set_auction_current_bid ) {
							$notice_message       = '<div class="woocommerce-message success auction-success" role="alert">' . __( 'Bid placed successfully.', 'auction-software' ) . '</div>';
							$is_reserve_price_met = $product->check_if_reserve_price_met( $product_id );
							if ( 'yes' === $product->is_proxy_bidding() && $is_reserve_price_met ) {
								$max_bid_user = $product->get_auction_max_bid_user();
								if ( ! empty( $max_bid_user ) ) {
									$max_bid = $product->get_auction_max_bid();
									if ( $user_id !== (int) $max_bid_user ) {
										$auto_current_bid   = $product->get_auction_current_bid();
										$auto_increment_bid = $product->get_auction_bid_increment();
										$auto_next_bid      = $auto_current_bid - $auto_increment_bid;
										if ( $auto_next_bid < $max_bid ) {
											$auto_next_bid = $max_bid;
										}
										$set_auction_auto_bid = $product->set_auction_current_bid( $auto_current_bid, $auto_next_bid, $max_bid_user, $product_id, 1 );
										if ( 1 === (int) $set_auction_auto_bid ) {
											$notice_message = '<div class="woocommerce-message success auction-success" role="alert">' . __( 'Bid placed successfully, but you have been outbid!', 'auction-software' ) . '</div>';
										}
									}
									if ( $user_id === (int) $max_bid_user ) {
										$max_flag       = 1;
										$change_max_bid = (float) $max_bid;
									}
								}
							}
							$flag                 = 1;
							$change_current_bid   = (float) $product->get_auction_current_bid();
							$change_price_box_bid = (float) $product->get_auction_current_bid() - (float) $increment_bid;
						} elseif ( 2 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message success auction-success" role="alert">' . __( 'Your bid is winning, no need to place again.', 'auction-software' ) . '</div>';
						} elseif ( 3 === (int) $set_auction_current_bid ) {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Please enter a lower amount.', 'auction-software' ) . '</div>';
						} else {
							$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Bid not placed successfully.', 'auction-software' ) . '</div>';
						}
					} else {
						$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'You can not place bid on your products.', 'auction-software' ) . '</div>';
					}
				} else {
					$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'You must be logged in to place bid. ', 'auction-software' ) . '<a class="button" href="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '">' . __( 'Log in', 'auction-software' ) . '</a></div>';
				}
			} else {
				$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Auction has expired. Please reload the page.', 'auction-software' ) . '</div>';
			}
		} else {
			$notice_message = '<div class="woocommerce-message error auction-error" role="alert">' . __( 'Bid should not be negative.', 'auction-software' ) . '</div>';
		}
		! empty( $notice_message ) ? $json_response['status'] = 'notice' : $json_response['status'] = '';
		$json_response['notice_message']                      = $notice_message;
		if ( 1 === (int) $flag ) {
			if ( 'yes' === $product->is_anti_snipping() ) {
				$seconds      = get_option( 'auctions_anti_snipping_duration', 0 );
				$trigger_time = get_option( 'auctions_anti_snipping_trigger_time', 5 );
				$time         = current_time( 'timestamp' ); // phpcs:ignore
				$timeplus     = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $trigger_time . ' minutes', $time ) );
				if ( $timeplus > $date_to ) {
					$date_time_to->add( new DateInterval( 'PT' . $seconds . 'S' ) );
					update_post_meta( $product_id, 'auction_date_to', $date_time_to->format( 'Y-m-d H:i:s' ) );
				}
			}
			$json_response['change_bid']          = 1;
			$json_response['change_current_bid']  = wc_price( $change_current_bid );
			$json_response['change_pricebox_bid'] = round( $change_price_box_bid, 2 );
			if ( __( 'NA', 'auction-software' ) !== $product->get_buy_it_now_cart_text() ) {
				$json_response['remove_buy_it_now_cart_text'] = 0;
			} else {
				$json_response['remove_buy_it_now_cart_text'] = 1;
			}
			if ( 1 === $max_flag ) {
				$json_response['change_max_bid']       = 1;
				$json_response['change_max_bid_value'] = wc_price( $change_max_bid );
			} else {
				$json_response['change_max_bid'] = 0;
			}
		} else {
			$json_response['change_bid'] = 0;
		}
		$json_response['seconds'] = $date_time_to->format( 'Y-m-d H:i:s' );
		echo wp_send_json( $json_response ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
        // phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Auction product add to watchlist.
	 */
	public function auction_software_wc_ajax_add_to_auctionwatchlist() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$user_id    = get_current_user_id();
		if ( 0 === (int) $user_id ) {
			echo wp_send_json( 'notlogin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die();
		}
		$watchlist = get_user_meta( $user_id, 'auction_watchlist' );
		if ( isset( $watchlist[0] ) && ! empty( $watchlist[0] ) ) {
			$watchlist = explode( ',', $watchlist[0] );
			if ( ! in_array( $product_id, $watchlist, true ) ) {
				array_push( $watchlist, $product_id );
			}
			$watchlist = implode( ',', $watchlist );
			update_user_meta( $user_id, 'auction_watchlist', $watchlist );
		} else {
			update_user_meta( $user_id, 'auction_watchlist', ',' . $product_id );
		}
		echo wp_send_json( 'success' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
        // phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Auction product remove from watchlist.
	 */
	public function auction_software_wc_ajax_remove_from_auctionwatchlist() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
		$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
		$user_id    = get_current_user_id();
		if ( 0 === (int) $user_id ) {
			echo wp_send_json( 'notlogin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die();
		}
		$watchlist = get_user_meta( $user_id, 'auction_watchlist' );
		if ( isset( $watchlist[0] ) && ! empty( $watchlist[0] ) ) {
			$watchlist = explode( ',', $watchlist[0] );
			array_shift( $watchlist );
			$key = array_search( $product_id, $watchlist ); //phpcs:ignore
			if ( false !== $key ) {
				unset( $watchlist[ $key ] );
			}
			$watchlist = implode( ',', $watchlist );
			update_user_meta( $user_id, 'auction_watchlist', $watchlist );
		} else {
			update_user_meta( $user_id, 'auction_watchlist', ',' . $product_id );
		}
		echo wp_send_json( 'success' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die();
        // phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Reorder my account menu items.
	 *
	 * @param array $items Menu items.
	 * @return array
	 */
	public function auction_software_my_account_menu_items( $items ) {
		$ordered_items = array();
		foreach ( $items as $key => $value ) {
			if ( 'dashboard' === $key ) {
				$ordered_items[ $key ] = $value;
			}
			if ( 'auctions_list' === $key ) {
				$ordered_items[ $key ] = $value;
			}
		}
		$ordered_items = array_merge( $ordered_items, $items );
		return $ordered_items;
	}
}
