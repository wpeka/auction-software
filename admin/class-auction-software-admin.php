<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://club.wpeka.com/
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/admin
 * @author     WPeka Club <support@wpeka.com>
 */
class Auction_Software_Admin {


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Auction increment classes.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $auction_classes Auction increment classes.
	 */
	private $auction_classes;

	/**
	 * Instance of callback functions of all block-based widgets.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $auction_classes Auction increment classes.
	 */
	private $block_callbacks;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name     = $plugin_name;
		$this->version         = $version;
		$this->block_callbacks = new Auction_Software_Blocks_Callback();

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/auction-software-admin' . AUCTION_SOFTWARE_SUFFIX . '.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script(
			$this->plugin_name . '-timepicker-addon',
			plugin_dir_url( __FILE__ ) . 'js/timepicker-addon.js',
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-datepicker',
			),
			$this->version,
			true
		);
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/auction-software-admin' . AUCTION_SOFTWARE_SUFFIX . '.js',
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-datepicker',
				$this->plugin_name . '-timepicker-addon',
			),
			$this->version,
			false
		);
		wp_register_script(
			$this->plugin_name . '-wc-settings',
			plugin_dir_url( __FILE__ ) . 'js/auction-software-settings' . AUCTION_SOFTWARE_SUFFIX . '.js',
			array(
				'jquery',
				'wp-util',
				'underscore',
				'backbone',
			),
			$this->version,
			true
		);

	}

	/**
	 * Plugin init.
	 *
	 * @since 1.0.0
	 */
	public function auction_software_init() {
		add_rewrite_endpoint( 'auctions_list', EP_ROOT | EP_PAGES );
		if ( ! get_option( 'auction_flushed_rewrite_rules' ) ) {
			add_option( 'auction_flushed_rewrite_rules', true );
			flush_rewrite_rules();
		}

		add_filter( 'post_row_actions', array( $this, 'auction_software_remove_duplicate_link' ), 15, 2 );
		add_filter( 'page_row_actions', array( $this, 'auction_software_remove_duplicate_link' ), 15, 2 );

		if ( ! taxonomy_exists( 'product_auction_class' ) ) {
			register_taxonomy(
				'product_auction_class',
				'products',
				array(
					'label'        => __( 'Product Auction Classes', 'auction-software' ),
					'rewrite'      => array( 'slug' => 'product_auction_class' ),
					'hierarchical' => true,
				)
			);
		}
		if ( ! term_exists( '50', 'product_auction_class' ) ) {
			wp_insert_term(
				'0',
				'product_auction_class',
				array(
					'slug'        => '50',
					'description' => '1',
				)
			);
		}
		if ( ! term_exists( '100', 'product_auction_class' ) ) {
			wp_insert_term(
				'50',
				'product_auction_class',
				array(
					'slug'        => '100',
					'description' => '5',
				)
			);
		}
		if ( ! term_exists( '500', 'product_auction_class' ) ) {
			wp_insert_term(
				'100',
				'product_auction_class',
				array(
					'slug'        => '500',
					'description' => '10',
				)
			);
		}
		if ( ! term_exists( '1000', 'product_auction_class' ) ) {
			wp_insert_term(
				'500',
				'product_auction_class',
				array(
					'slug'        => '1000',
					'description' => '15',
				)
			);
		}
		if ( ! term_exists( '5000', 'product_auction_class' ) ) {
			wp_insert_term(
				'1000',
				'product_auction_class',
				array(
					'slug'        => '5000',
					'description' => '25',
				)
			);
		}

		if ( ! wp_next_scheduled( 'auction_software_every_minute_cron' ) ) {
			wp_schedule_event( time(), 'every_minute', 'auction_software_every_minute_cron' );
		}

		// Email Actions.
		$email_actions = apply_filters(
			'woocommerce_auction_software_email_actions',
			array(
				'woocommerce_auction_software_start',
				'woocommerce_auction_software_end',
				'woocommerce_auction_software_win',
				'woocommerce_auction_software_outbid',
			)
		);
		if ( class_exists( 'WC_Emails' ) ) {
			foreach ( $email_actions as $action ) {
				add_action( $action, array( 'WC_Emails', 'send_transactional_email' ), 10, 10 );
			}
		}
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce/classes/emails/class-wc-auction-software-email-manager.php';

		// Update settings for existing products.
		$auction_extend_relist_settings_updated = get_option( 'auction_extend_relist_settings_updated' );
		if ( '1' !== $auction_extend_relist_settings_updated ) {
			$query = new WP_Query(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => - 1,
				)
			);
			while ( $query->have_posts() ) {
				$query->the_post();
				$postid  = get_the_ID();
				$product = wc_get_product( $postid );
				if ( 'auction_simple' === $product->get_type() || 'auction_reverse' === $product->get_type() ) {
					$extend_relist_auction = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_extend_or_relist_auction' );
					if ( 'none' !== $extend_relist_auction ) {
						$date_from       = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_date_from' );
						$date_time_from  = datetime::createfromformat( 'Y-m-d H:i:s', $date_from );
						$date_to         = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_date_to' );
						$date_time_to    = datetime::createfromformat( 'Y-m-d H:i:s', $date_to );
						$diff            = intval( ( $date_time_to->getTimestamp() - $date_time_from->getTimestamp() ) / 60 );
						$if_fail_hrs     = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_if_fail' );
						$if_not_paid_hrs = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_if_not_paid' );
						$if_duration_hrs = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration' );
						$always          = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_always' );
						if ( 'yes' !== $if_fail_hrs && '' !== $if_fail_hrs ) {
							update_post_meta( $postid, 'auction_' . $extend_relist_auction . '_if_fail', 'yes' );
							update_post_meta( $postid, 'auction_wait_time_before_' . $extend_relist_auction . '_if_fail', $if_fail_hrs );
							update_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration_if_fail', $diff );
						}
						if ( 'yes' !== $if_not_paid_hrs && '' !== $if_not_paid_hrs ) {
							update_post_meta( $postid, 'auction_' . $extend_relist_auction . '_if_not_paid', 'yes' );
							update_post_meta( $postid, 'auction_wait_time_before_' . $extend_relist_auction . '_if_not_paid', $if_not_paid_hrs );
							update_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration_if_not_paid', $diff );
						}

						if ( 'yes' !== $always && '' !== $if_duration_hrs ) {
							update_post_meta( $postid, 'auction_' . $extend_relist_auction . '_always', 'yes' );
							update_post_meta( $postid, 'auction_wait_time_before_' . $extend_relist_auction . '_always', $if_duration_hrs );
							update_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration_always', $diff );
						}
					}
				}
			}
			wp_reset_postdata();
			add_option( 'auction_extend_relist_settings_updated', true );
		}

	}

	/**
	 * Register widgets.
	 */
	public function auction_software_widgets_init() {

		// No widgets will be register for WordPress version >= 5.8.
		global $wp_version;

		if ( version_compare( $wp_version, '5.8' ) >= 0 ) {
			return;
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-ending-soon-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-featured-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-future-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-my-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-random-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-recent-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-recently-viewed-auctions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-auction-software-widget-watchlist-auctions.php';
		register_widget( 'Auction_Software_Widget_Ending_Soon_Auctions' );
		register_widget( 'Auction_Software_Widget_Featured_Auctions' );
		register_widget( 'Auction_Software_Widget_Future_Auctions' );
		register_widget( 'Auction_Software_Widget_My_Auctions' );
		register_widget( 'Auction_Software_Widget_Random_Auctions' );
		register_widget( 'Auction_Software_Widget_Recent_Auctions' );
		register_widget( 'Auction_Software_Widget_Recently_Viewed_Auctions' );
		register_widget( 'Auction_Software_Widget_Watchlist_Auctions' );
	}

	/**
	 * Remove duplicate action link for auction products.
	 *
	 * @param Array  $actions Page/post actions.
	 * @param Object $post Post object.
	 * @return mixed
	 */
	public function auction_software_remove_duplicate_link( $actions, $post ) {
		$auction_types = apply_filters(
			'auction_software_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);

		if ( 'product' !== $post->post_type ) {
			return $actions;
		}

		$product = wc_get_product( $post->ID );

		if ( ! in_array( $product->get_type(), $auction_types, true ) ) {
			return $actions;
		}

		unset( $actions['duplicate'] );
		return $actions;
	}

	/**
	 * Returns plugin action links.
	 *
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public function auction_software_plugin_action_links( $links ) {
		if ( ! get_option( 'auction_software_pro_installed' ) ) {
			$links = array_merge(
				array(
					'<a href="' . esc_url( 'https://club.wpeka.com/product/woo-auction-software/?utm_source=plugins&utm_campaign=auctionsoftware&utm_content=upgrade-to-pro' ) . '" target="_blank" rel="noopener noreferrer"><strong style="color: #11967A; display: inline;">' . __( 'Upgrade to Pro', 'auction-software' ) . '</strong></a>',
				),
				$links
			);
		}
		return $links;
	}

	/**
	 * Every minute cron tasks.
	 */
	public function auction_software_every_minute_cron_tasks() {
		// Get all posts of type auction.
		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
			)
		);

		while ( $query->have_posts() ) {
			$query->the_post();
			$postid  = get_the_ID();
			$product = wc_get_product( $postid );
			if ( 'auction_simple' === $product->get_type() || 'auction_reverse' === $product->get_type() ) {
				if ( $product->is_ended() ) {
					$is_ended = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_is_ended' );
					if ( 1 !== (int) $is_ended ) {
						update_post_meta( $postid, 'auction_is_ended', 1 );
						WC_Auction_Software_Helper::set_auction_bid_logs( '', $postid, $product->get_auction_current_bid(), current_time( 'mysql' ), 'ended' );
						do_action( 'woocommerce_auction_software_end', $postid );
					}
				}
				$extend_relist_auction = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_extend_or_relist_auction' );
				if ( 'none' !== $extend_relist_auction ) {
					$is_ended               = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_is_ended' );
					$is_reserve_price_met   = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_reserve_price_met' );
					$is_sold                = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_is_sold' );
					$date_to                = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_date_to' );
					$date_time_to           = datetime::createfromformat( 'Y-m-d H:i:s', $date_to );
					$curdate                = current_time( 'mysql' );
					$date_time_current_date = datetime::createfromformat( 'Y-m-d H:i:s', $curdate );
					$if_fail                = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_if_fail' );
					$if_not_paid            = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_if_not_paid' );
					$always                 = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_always' );

					$wait_time_before_if_fail     = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_wait_time_before_' . $extend_relist_auction . '_if_fail' );
					$wait_time_before_if_not_paid = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_wait_time_before_' . $extend_relist_auction . '_if_not_paid' );
					$wait_time_before_always      = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_wait_time_before_' . $extend_relist_auction . '_always' );

					$duration_if_fail     = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration_if_fail' );
					$duration_if_not_paid = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration_if_not_paid' );
					$duration_always      = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_' . $extend_relist_auction . '_duration_always' );

					if ( 1 === (int) $is_ended ) {
						if ( 'yes' !== $is_reserve_price_met && 1 !== (int) $is_sold && 'yes' === $if_fail ) {
							$date = $date_time_to;
							$date->add( new DateInterval( 'PT' . $wait_time_before_if_fail . 'M' ) );
							if ( $date_time_current_date >= $date ) {
								update_post_meta( $postid, 'auction_is_ended', 0 );
								update_post_meta( $postid, 'auction_is_sold', 0 );
								WC_Auction_Software_Helper::clear_auction_bid_logs( $postid, true );
								$to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
								$to_date->add( new DateInterval( ( 'PT' . $duration_if_fail . 'M' ) ) );
								update_post_meta( $postid, 'auction_date_to', $to_date->format( 'Y-m-d H:i:s' ) );
								if ( 'relist' === $extend_relist_auction ) {
									WC_Auction_Software_Helper::clear_auction_bid_logs( $postid );
									WC_Auction_Software_Helper::set_auction_bid_logs( '', $postid, 0, current_time( 'mysql' ), 'relisted' );
									$from_date = current_time( 'mysql' );
									update_post_meta( $postid, 'auction_date_from', $from_date );
									update_post_meta( $postid, 'auction_current_bid', 0 );
									update_post_meta( $postid, 'auction_is_started_and_ended', 0 );
									update_post_meta( $postid, $postid . '_start_mail_sent', 0 );
									update_post_meta( $postid, 'auction_initial_bid_placed', 0 );
									do_action( 'woocommerce_auction_software_start', $postid );
								}
							}
						} elseif ( 'yes' === $is_reserve_price_met && 1 !== (int) $is_sold && 'yes' === $if_not_paid ) {
							$date = $date_time_to;
							$date->add( new DateInterval( 'PT' . $wait_time_before_if_not_paid . 'M' ) );
							if ( $date_time_current_date >= $date ) {
								update_post_meta( $postid, 'auction_is_ended', 0 );
								update_post_meta( $postid, 'auction_is_sold', 0 );
								WC_Auction_Software_Helper::clear_auction_bid_logs( $postid, true );
								$to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
								$to_date->add( new DateInterval( ( 'PT' . $duration_if_not_paid . 'M' ) ) );
								update_post_meta( $postid, 'auction_date_to', $to_date->format( 'Y-m-d H:i:s' ) );
								if ( 'relist' === $extend_relist_auction ) {
									WC_Auction_Software_Helper::clear_auction_bid_logs( $postid );
									WC_Auction_Software_Helper::set_auction_bid_logs( '', $postid, 0, current_time( 'mysql' ), 'relisted' );
									$from_date = current_time( 'mysql' );
									update_post_meta( $postid, 'auction_date_from', $from_date );
									update_post_meta( $postid, 'auction_current_bid', 0 );
									update_post_meta( $postid, 'auction_is_started_and_ended', 0 );
									update_post_meta( $postid, $postid . '_start_mail_sent', 0 );
									update_post_meta( $postid, 'auction_initial_bid_placed', 0 );
									do_action( 'woocommerce_auction_software_start', $postid );
								}
							}
						} elseif ( 'yes' === $always ) {
							$date = $date_time_to;
							$date->add( new DateInterval( 'PT' . $wait_time_before_always . 'M' ) );
							if ( $date_time_current_date >= $date ) {
								update_post_meta( $postid, 'auction_is_ended', 0 );
								update_post_meta( $postid, 'auction_is_sold', 0 );
								WC_Auction_Software_Helper::clear_auction_bid_logs( $postid, true );
								$to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
								$to_date->add( new DateInterval( ( 'PT' . $duration_always . 'M' ) ) );
								update_post_meta( $postid, 'auction_date_to', $to_date->format( 'Y-m-d H:i:s' ) );
								if ( 'relist' === $extend_relist_auction ) {
									WC_Auction_Software_Helper::clear_auction_bid_logs( $postid );
									WC_Auction_Software_Helper::set_auction_bid_logs( '', $postid, 0, current_time( 'mysql' ), 'relisted' );
									$from_date = current_time( 'mysql' );
									update_post_meta( $postid, 'auction_date_from', $from_date );
									update_post_meta( $postid, 'auction_current_bid', 0 );
									update_post_meta( $postid, 'auction_is_started_and_ended', 0 );
									update_post_meta( $postid, $postid . '_start_mail_sent', 0 );
									update_post_meta( $postid, 'auction_initial_bid_placed', 0 );
									do_action( 'woocommerce_auction_software_start', $postid );
								}
							}
						}
					} else {
						if ( 'extend' === $extend_relist_auction ) {
							if ( 'yes' !== $is_reserve_price_met && 1 !== (int) $is_sold && 'yes' === $if_fail ) {
								$date = $date_time_to;
								$date->add( new DateInterval( 'PT' . $wait_time_before_if_fail . 'M' ) );
								if ( $date_time_current_date >= $date ) {
									update_post_meta( $postid, 'auction_is_ended', 0 );
									update_post_meta( $postid, 'auction_is_sold', 0 );
									WC_Auction_Software_Helper::clear_auction_bid_logs( $postid, true );
									$to_date = datetime::createfromformat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
									$to_date->add( new DateInterval( ( 'PT' . $duration_if_fail . 'M' ) ) );
									update_post_meta( $postid, 'auction_date_to', $to_date->format( 'Y-m-d H:i:s' ) );
								}
							}
						}
					}
				}
			}

			if ( 'auction_simple' === $product->get_type() || 'auction_reverse' === $product->get_type() ) {
				do_action( 'woocommerce_auction_software_end', $postid );
				do_action( 'woocommerce_auction_software_win', $postid );
				// Logic for start mail.
				$mail_sent              = get_post_meta( $postid, $postid . '_start_mail_sent' );
				$date_from              = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_date_from' );
				$date_time_from         = datetime::createfromformat( 'Y-m-d H:i:s', $date_from );
				$curdate                = current_time( 'mysql' );
				$date_time_current_date = datetime::createfromformat( 'Y-m-d H:i:s', $curdate );
				$status                 = get_post_status( $postid );
				if ( $date_time_current_date >= $date_time_from && empty( $mail_sent ) && 'publish' === $status ) {
					$error = get_post_meta( $postid, 'auction_errors' );
					if ( '' === $error[0] ) {
						do_action( 'woocommerce_auction_software_start', $postid );
						update_post_meta( $postid, $postid . '_start_mail_sent', 1 );
					}
				}
			}

			do_action( 'auction_software_every_minute_cron_tasks', $postid, $product );
		}
		wp_reset_postdata();
	}

	/**
	 * Ajax call to save auction increment classes.
	 *
	 * @since 1.0.0
	 */
	public function auction_software_save_wc_classes() {
		if ( ! isset( $_POST['wc_auction_classes_nonce'], $_POST['changes'] ) ) {
			wp_send_json_error( 'missing_fields' );
			wp_die();
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc_auction_classes_nonce'] ) ), 'wc_auction_classes_nonce' ) ) {
			wp_send_json_error( 'bad_nonce' );
			wp_die();
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'missing_capabilities' );
			wp_die();
		}
		// The below phpcs ignore comment has been added after referring WooCommerce plugin.
		$changes = wp_unslash( $_POST['changes'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( $changes as $term_id => $data ) {
			$term_id = absint( $term_id );

			if ( isset( $data['deleted'] ) ) {
				if ( isset( $data['newRow'] ) ) {
					// So the user added and deleted a new row.
					// That's fine, it's not in the database anyways. NEXT!
					continue;
				}
				wp_delete_term( $term_id, 'product_auction_class' );
				continue;
			}

			$update_args = array();

			if ( isset( $data['name'] ) ) {
				$update_args['name'] = wc_clean( $data['name'] );
			}

			if ( isset( $data['slug'] ) ) {
				$update_args['slug'] = wc_clean( $data['slug'] );
			}

			if ( isset( $data['description'] ) ) {
				$update_args['description'] = wc_clean( $data['description'] );
			}

			if ( isset( $data['newRow'] ) ) {
				$update_args = array_filter( $update_args );
				if ( empty( $update_args['name'] ) ) {
					continue;
				} else {
					$name = $update_args['name'];
					unset( $update_args['name'] );
				}
				$inserted_term = wp_insert_term( $name, 'product_auction_class', $update_args );
				$term_id       = is_wp_error( $inserted_term ) ? 0 : $inserted_term['term_id'];
			} else {
				wp_update_term( $term_id, 'product_auction_class', $update_args );
			}

			do_action( 'auction_software_save_wc_classes', $term_id, $data );
		}
		wp_send_json_success(
			array(
				'auction_classes' => $this->auction_software_get_wc_classes(),
			)
		);

	}

	/**
	 * Get auction increment classes.
	 *
	 * @since 1.0.0
	 * @return mixed|void
	 */
	public function auction_software_get_wc_classes() {
		if ( empty( $this->auction_classes ) ) {
			$classes               = get_terms(
				'product_auction_class',
				array(
					'hide_empty' => '0',
					'orderby'    => 'term_id',
				)
			);
			$this->auction_classes = ! is_wp_error( $classes ) ? $classes : array();
		}

		return apply_filters( 'auction_software_get_wc_classes', $this->auction_classes );
	}

	/**
	 * Re-arrange registered auction settings tab.
	 *
	 * @since 1.0.0
	 * @param array $settings_tabs WooCommerce settings tab.
	 * @return mixed
	 */
	public function auction_software_wc_settings_tab( $settings_tabs ) {
		$auction = $settings_tabs['auctions'];
		unset( $settings_tabs['auctions'] );
		$settings_tabs['auctions'] = $auction;

		return $settings_tabs;
	}

	/**
	 * Auction product-type tabs.
	 *
	 * @since 1.0.0
	 * @param array $tabs WooCommerce product-type tabs.
	 * @return mixed
	 */
	public function auction_software_product_auction_tabs( $tabs ) {
		$classes = 'show_if_auction_simple show_if_auction_reverse';
		$classes = apply_filters( 'auction_software_auction_tabs_classes', $classes );

		$tabs['general']['priority'] = 1;

		$tabs['auction'] = array(
			'label'    => __( 'Auction Settings', 'auction-software' ),
			'target'   => 'auction_options',
			'class'    => $classes,
			'priority' => 2,
		);

		$tabs['auction_history'] = array(
			'label'    => __( 'Auction History', 'auction-software' ),
			'target'   => 'auction_history',
			'class'    => $classes,
			'priority' => 3,
		);

		$tabs['auction_relist'] = array(
			'label'    => __( 'Auction Relist Settings', 'auction-software' ),
			'target'   => 'auction_relist',
			'class'    => $classes,
			'priority' => 4,
		);
		return $tabs;
	}

	/**
	 * Auction product-type tabs content.
	 *
	 * @since 1.0.0
	 */
	public function auction_software_product_auction_tab_content() {
		$auction_errors = $this->auction_software_get_product_auction_errors();
		update_post_meta( get_the_ID(), 'auction_errors', $auction_errors );
		?>
		<div id='auction_options' class='panel woocommerce_options_panel'>		<div class='options_group'>
		<?php
		if ( ! empty( $auction_errors ) ) {
			echo '<p class="auction_error">' . wp_kses_post( $auction_errors ) . '</p>';
		}
		?>
		<?php
		$auction        = new WC_Product_Auction();
		$attribute_data = $auction->attribute_data;
		$custom_attr    = array();
		foreach ( $attribute_data as $attribute ) {
			WC_Auction_Software_Helper::get_product_tab_fields( $attribute['type'], $attribute['id'], $attribute['label'], $attribute['desc_tip'], $attribute['description'], $attribute['currency'], $attribute['options'], $custom_attr );
			if ( 'date_to' === $attribute['id'] ) {
				?>
						<p class="auctiontimezone_notice">
					<?php
					echo sprintf(
					/* translators: 1: Current time 2: Timezone 3: Link */
						wp_kses_post( __( "Your website's current time is <strong>%1\$1s</strong> Timezone: <strong>%2\$2s</strong> %3\$3s" ) ),
						esc_attr( wp_date( 'Y-m-d H:i:s', time(), wp_timezone() ) ),
						esc_attr( wp_timezone_string() ),
						sprintf(
						/* translators: 1: Link URL 2: Link text */
							'<a href="%1s" target="_blank">%2s</a>',
							esc_url( admin_url( 'options-general.php?#timezone_string' ) ),
							esc_html__( 'Click here to change', 'auction-software' )
						)
					);
					?>
						</p>
					<?php
			}
		}
		?>
			</div> <?php do_action( 'woocommerce_product_options_auction_product_data' ); ?>
		</div>		<div id='auction_history' class='panel woocommerce_options_panel'>			<div class='options_group'>
			<?php
			echo WC_Auction_Software_Helper::get_auction_history( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			</div>
		</div>

		<div id='auction_relist' class='panel woocommerce_options_panel'>		<div class='options_group'>
			<?php
			if ( ! empty( $auction_errors ) ) {
				echo '<p class="auction_error">' . wp_kses_post( $auction_errors, $arr ) . '</p>';
			}
			?>
			<?php
			$relist_attribute_data = $auction->extend_relist_attribute_data;
			$custom_attr           = array();
			foreach ( $relist_attribute_data as $relist_attribute ) {
				$wrapper_class = '';
				if ( 'extend_or_relist_auction' !== $relist_attribute['id'] ) {
					if ( false !== strpos( $relist_attribute['id'], 'extend' ) ) {
						$wrapper_class .= 'auction_extend ';
						if ( 'checkbox' !== $relist_attribute['type'] ) {
							if ( false !== strpos( $relist_attribute['id'], 'if_fail' ) ) {
								$wrapper_class .= 'auction_extend_if_fail ';
							}
							if ( false !== strpos( $relist_attribute['id'], 'if_not_paid' ) ) {
								$wrapper_class .= 'auction_extend_if_not_paid ';
							}
							if ( false !== strpos( $relist_attribute['id'], 'always' ) ) {
								$wrapper_class .= 'auction_extend_always ';
							}
						}
					} elseif ( false !== strpos( $relist_attribute['id'], 'relist' ) ) {
						$wrapper_class .= 'auction_relist ';
						if ( 'checkbox' !== $relist_attribute['type'] ) {
							if ( false !== strpos( $relist_attribute['id'], 'if_fail' ) ) {
								$wrapper_class .= 'auction_relist_if_fail ';
							}
							if ( false !== strpos( $relist_attribute['id'], 'if_not_paid' ) ) {
								$wrapper_class .= 'auction_relist_if_not_paid ';
							}
							if ( false !== strpos( $relist_attribute['id'], 'always' ) ) {
								$wrapper_class .= 'auction_relist_always ';
							}
						}
					}
				}
				WC_Auction_Software_Helper::get_product_tab_fields( $relist_attribute['type'], $relist_attribute['id'], $relist_attribute['label'], $relist_attribute['desc_tip'], $relist_attribute['description'], $relist_attribute['currency'], $relist_attribute['options'], $custom_attr, '', $wrapper_class );
			}
			?>
			</div>
		</div>
			<?php
	}

	/**
	 * Get Auction product tabs save errors.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function auction_software_get_product_auction_errors() {
		$auction_errors        = '';
		$get_start_price_error = get_post_meta( get_the_ID(), 'auction_start_price_error' );
		isset( $get_start_price_error[0] ) && ! empty( $get_start_price_error[0] ) ? $auction_errors .= $get_start_price_error[0] . '<br>' : '';

		$get_bid_increment_error = get_post_meta( get_the_ID(), 'auction_bid_increment_error' );
		isset( $get_bid_increment_error[0] ) && ! empty( $get_bid_increment_error[0] ) ? $auction_errors .= $get_bid_increment_error[0] . '<br>' : '';

		$get_date_from_error = get_post_meta( get_the_ID(), 'auction_date_from_error' );
		isset( $get_date_from_error[0] ) && ! empty( $get_date_from_error[0] ) ? $auction_errors .= $get_date_from_error[0] . '<br>' : '';

		$get_date_to_error = get_post_meta( get_the_ID(), 'auction_date_to_error' );
		isset( $get_date_to_error[0] ) && ! empty( $get_date_to_error[0] ) ? $auction_errors .= $get_date_to_error[0] . '<br>' : '';

		$get_reserve_price_error = get_post_meta( get_the_ID(), 'auction_reserve_price_error' );
		isset( $get_reserve_price_error[0] ) && ! empty( $get_reserve_price_error[0] ) ? $auction_errors .= $get_reserve_price_error[0] . '<br>' : '';

		$get_reserve_price_reverse_error = get_post_meta( get_the_ID(), 'auction_reserve_price_reverse_error' );
		isset( $get_reserve_price_reverse_error[0] ) && ! empty( $get_reserve_price_reverse_error[0] ) ? $auction_errors .= $get_reserve_price_reverse_error[0] . '<br>' : '';

		$get_buy_it_now_price_error = get_post_meta( get_the_ID(), 'auction_buy_it_now_price_error' );
		isset( $get_buy_it_now_price_error[0] ) && ! empty( $get_buy_it_now_price_error[0] ) ? $auction_errors .= $get_buy_it_now_price_error[0] . '<br>' : '';

		$get_buy_it_now_price_reverse_error = get_post_meta( get_the_ID(), 'auction_buy_it_now_price_reverse_error' );
		isset( $get_buy_it_now_price_reverse_error[0] ) && ! empty( $get_buy_it_now_price_reverse_error[0] ) ? $auction_errors .= $get_buy_it_now_price_reverse_error[0] . '<br>' : '';

		$get_wait_time_before_relist_if_fail_error = get_post_meta( get_the_ID(), 'auction_wait_time_before_relist_if_fail_error' );
		isset( $get_wait_time_before_relist_if_fail_error[0] ) && ! empty( $get_wait_time_before_relist_if_fail_error[0] ) ? $auction_errors .= $get_wait_time_before_relist_if_fail_error[0] . '<br>' : '';

		$get_wait_time_before_relist_if_not_paid_error = get_post_meta( get_the_ID(), 'auction_wait_time_before_relist_if_not_paid_error' );
		isset( $get_wait_time_before_relist_if_not_paid_error[0] ) && ! empty( $get_wait_time_before_relist_if_not_paid_error[0] ) ? $auction_errors .= $get_wait_time_before_relist_if_not_paid_error[0] . '<br>' : '';

		$get_wait_time_before_relist_always_error = get_post_meta( get_the_ID(), 'auction_wait_time_before_relist_always_error' );
		isset( $get_wait_time_before_relist_always_error[0] ) && ! empty( $get_wait_time_before_relist_always_error[0] ) ? $auction_errors .= $get_wait_time_before_relist_always_error[0] . '<br>' : '';

		$get_relist_duration_if_fail_error = get_post_meta( get_the_ID(), 'auction_relist_duration_if_fail_error' );
		isset( $get_relist_duration_if_fail_error[0] ) && ! empty( $get_relist_duration_if_fail_error[0] ) ? $auction_errors .= $get_relist_duration_if_fail_error[0] . '<br>' : '';

		$get_relist_duration_if_not_paid_error = get_post_meta( get_the_ID(), 'auction_relist_duration_if_not_paid_error' );
		isset( $get_relist_duration_if_not_paid_error[0] ) && ! empty( $get_relist_duration_if_not_paid_error[0] ) ? $auction_errors .= $get_relist_duration_if_not_paid_error[0] . '<br>' : '';

		$get_relist_duration_always_error = get_post_meta( get_the_ID(), 'auction_relist_duration_always_error' );
		isset( $get_relist_duration_always_error[0] ) && ! empty( $get_relist_duration_always_error[0] ) ? $auction_errors .= $get_relist_duration_always_error[0] . '<br>' : '';

		$get_wait_time_before_extend_if_fail_error = get_post_meta( get_the_ID(), 'auction_wait_time_before_extend_if_fail_error' );
		isset( $get_wait_time_before_extend_if_fail_error[0] ) && ! empty( $get_wait_time_before_extend_if_fail_error[0] ) ? $auction_errors .= $get_wait_time_before_extend_if_fail_error[0] . '<br>' : '';

		$get_wait_time_before_extend_if_not_paid_error = get_post_meta( get_the_ID(), 'auction_wait_time_before_extend_if_not_paid_error' );
		isset( $get_wait_time_before_extend_if_not_paid_error[0] ) && ! empty( $get_wait_time_before_extend_if_not_paid_error[0] ) ? $auction_errors .= $get_wait_time_before_extend_if_not_paid_error[0] . '<br>' : '';

		$get_wait_time_before_extend_always_error = get_post_meta( get_the_ID(), 'auction_wait_time_before_extend_always_error' );
		isset( $get_wait_time_before_extend_always_error[0] ) && ! empty( $get_wait_time_before_extend_always_error[0] ) ? $auction_errors .= $get_wait_time_before_extend_always_error[0] . '<br>' : '';

		$get_extend_duration_if_fail_error = get_post_meta( get_the_ID(), 'auction_extend_duration_if_fail_error' );
		isset( $get_extend_duration_if_fail_error[0] ) && ! empty( $get_extend_duration_if_fail_error[0] ) ? $auction_errors .= $get_extend_duration_if_fail_error[0] . '<br>' : '';

		$get_extend_duration_if_not_paid_error = get_post_meta( get_the_ID(), 'auction_extend_duration_if_not_paid_error' );
		isset( $get_extend_duration_if_not_paid_error[0] ) && ! empty( $get_extend_duration_if_not_paid_error[0] ) ? $auction_errors .= $get_extend_duration_if_not_paid_error[0] . '<br>' : '';

		$get_extend_duration_always_error = get_post_meta( get_the_ID(), 'auction_extend_duration_always_error' );
		isset( $get_extend_duration_always_error[0] ) && ! empty( $get_extend_duration_always_error[0] ) ? $auction_errors .= $get_extend_duration_always_error[0] . '<br>' : '';

		$get_bid_price_error = get_post_meta( get_the_ID(), 'auction_bid_price_error' );
		isset( $get_bid_price_error[0] ) && ! empty( $get_bid_price_error[0] ) ? $auction_errors .= $get_bid_price_error[0] . '<br>' : '';

		return apply_filters( 'auction_software_product_auction_errors', $auction_errors );

	}

	/**
	 * Register Auction product types.
	 *
	 * @since 1.0.0
	 * @param array $type Product-type.
	 * @return mixed
	 */
	public function auction_software_product_auction_types( $type ) {
		$type['auction_simple']  = __( 'Simple Auction', 'auction-software' );
		$type['auction_reverse'] = __( 'Reverse Auction', 'auction-software' );
		return apply_filters( 'auction_software_product_auction_types', $type );
	}

	/**
	 * Returns Auction product tab fields.
	 *
	 * @since 1.0.0
	 */
	public function auction_software_product_auction_tab_fields() {
		?>
		<div class='options_group show_if_auction_simple'>
		<?php
		$auction_simple = new WC_Product_Auction_Simple();
		$attribute_data = $auction_simple->attribute_data;
		foreach ( $attribute_data as $attribute ) {
			$custom_attr = array();
			WC_Auction_Software_Helper::get_product_tab_fields( $attribute['type'], $attribute['id'], $attribute['label'], $attribute['desc_tip'], $attribute['description'], $attribute['currency'], $attribute['options'], $custom_attr );
		}
		?>
		</div>
		<div class='options_group show_if_auction_reverse'>
		<?php
		$auction_reverse = new WC_Product_Auction_Reverse();
		$attribute_data  = $auction_reverse->attribute_data;
		foreach ( $attribute_data as $attribute ) {
			$custom_attr = array();
			WC_Auction_Software_Helper::get_product_tab_fields( $attribute['type'], $attribute['id'], $attribute['label'], $attribute['desc_tip'], $attribute['description'], $attribute['currency'], $attribute['options'], $custom_attr );
		}
		?>
		</div>
		<?php
		do_action( 'auction_software_product_auction_tab_fields' );
	}

	/**
	 * Save Auction product meta data.
	 *
	 * @since 1.0.0
	 * @param int $post_id Product post id.
	 */
	public function auction_software_save_product_auction_options( $post_id ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$error_flag     = false;
		$product_type   = isset( $_POST['product-type'] ) ? sanitize_text_field( wp_unslash( $_POST['product-type'] ) ) : '';
		$auction        = new WC_Product_Auction();
		$attribute_data = $auction->attribute_data;
		foreach ( $attribute_data as $attribute ) {
			$attribute_id = isset( $_POST[ 'auction_' . $attribute['id'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'auction_' . $attribute['id'] ] ) ) : '';
			if ( ( 'start_price' === $attribute['id'] ) || ( ( 'bid_increment' === $attribute['id'] ) && '' !== $attribute_id ) ) {
				if(is_numeric($attribute_id)){
					$attribute_id = round( $attribute_id, 2 );
				}
			}
			$error_flag = $this->auction_software_check_validations( $attribute['id'], $attribute_id, $post_id, $error_flag );
			update_post_meta( $post_id, 'auction_' . $attribute['id'], $attribute_id );
		}

		$relist_attribute_data = $auction->extend_relist_attribute_data;
		foreach ( $relist_attribute_data as $relist_attribute ) {
			$relist_attribute_id = isset( $_POST[ 'auction_' . $relist_attribute['id'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'auction_' . $relist_attribute['id'] ] ) ) : '';
			$error_flag          = $this->auction_software_check_validations( $relist_attribute['id'], $relist_attribute_id, $post_id, $error_flag );
			update_post_meta( $post_id, 'auction_' . $relist_attribute['id'], $relist_attribute_id );
		}

		if ( 'auction_simple' === $product_type ) {
			$auction_simple        = new WC_Product_Auction_Simple();
			$attribute_data_simple = $auction_simple->attribute_data;
			foreach ( $attribute_data_simple as $attribute_simple ) {
				$simple_attribute_id = isset( $_POST[ 'auction_' . $attribute_simple['id'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'auction_' . $attribute_simple['id'] ] ) ) : '';
				if ( ! empty( $simple_attribute_id ) && ( 'reserve_price' === $attribute_simple['id'] || 'buy_it_now_price' === $attribute_simple['id'] ) ) {
					$simple_attribute_id = round( $simple_attribute_id, 2 );
				}
				$error_flag = $this->auction_software_check_validations( $attribute_simple['id'], $simple_attribute_id, $post_id, $error_flag );
				update_post_meta( $post_id, 'auction_' . $attribute_simple['id'], $simple_attribute_id );
			}
		} elseif ( 'auction_reverse' === $product_type ) {
			$auction_reverse        = new WC_Product_Auction_Reverse();
			$attribute_data_reverse = $auction_reverse->attribute_data;
			foreach ( $attribute_data_reverse as $attribute_reverse ) {
				$reverse_attribute_id = isset( $_POST[ 'auction_' . $attribute_reverse['id'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'auction_' . $attribute_reverse['id'] ] ) ) : '';
				if ( ! empty( $reverse_attribute_id ) && ( 'reserve_price_reverse' === $attribute_reverse['id'] || 'buy_it_now_price_reverse' === $attribute_reverse['id'] ) ) {
					$reverse_attribute_id = round( $reverse_attribute_id, 2 );
				}
				$error_flag = $this->auction_software_check_validations( $attribute_reverse['id'], $reverse_attribute_id, $post_id, $error_flag );
				update_post_meta( $post_id, 'auction_' . $attribute_reverse['id'], $reverse_attribute_id );
			}
		}
		do_action( 'auction_software_save_product_auction_options', $post_id );
		if ( ! $error_flag ) {
			$is_ended = get_post_meta( $post_id, 'auction_is_ended' );
			if ( ! empty( $is_ended ) && 1 === (int) $is_ended[0] ) {
				update_post_meta( $post_id, 'auction_is_started_and_ended', 1 );
			}
			update_post_meta( $post_id, 'auction_is_started', 1 );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Process import for auction products.
	 *
	 * @param Object $object Product object.
	 * @param Array  $data Product data.
	 */
	public function auction_software_product_imported( $object, $data ) {
		$post_id       = $object->get_id();
		$auction_types = apply_filters(
			'auction_software_import_auction_types',
			array(
				'auction_simple',
				'auction_reverse',
			)
		);
		if ( ! in_array( $data['type'], $auction_types, true ) ) {
			return;
		}
		if ( isset( $data['meta_data'] ) ) {
			$auction_data      = array_reduce(
				$data['meta_data'],
				function( $reduced, $current ) {
					$reduced[ $current['key'] ] = $current['value'];
					return $reduced;
				}
			);
			$auction_data_keys = apply_filters(
				'auction_software_import_auction_data_keys',
				array(
					'auction_item_condition',
					'auction_start_price',
					'auction_bid_increment',
					'auction_date_from',
					'auction_date_to',
					'auction_reserve_price',
					'auction_reserve_price_reverse',
					'auction_buy_it_now_price',
					'auction_buy_it_now_price_reverse',
					'auction_wait_time_before_relist_if_fail',
					'auction_relist_duration_if_fail',
					'auction_wait_time_before_relist_if_not_paid',
					'auction_relist_duration_if_not_paid',
					'auction_wait_time_before_relist_always',
					'auction_relist_duration_always',
					'auction_wait_time_before_extend_if_fail',
					'auction_extend_duration_if_fail',
					'auction_wait_time_before_extend_if_not_paid',
					'auction_extend_duration_if_not_paid',
					'auction_wait_time_before_extend_always',
					'auction_extend_duration_always',
				)
			);
			$date_from         = isset( $auction_data['auction_date_from'] ) ? $auction_data['auction_date_from'] : '';
			$date_to           = isset( $auction_data['auction_date_to'] ) ? $auction_data['auction_date_to'] : '';
			$flag              = false;
			$auction_errors    = '';
			foreach ( $auction_data as $key => $value ) {
				if ( in_array( $key, $auction_data_keys, true ) ) {
					switch ( $key ) {
						case 'auction_start_price':
							if ( '' === $value || $value < 0 ) {
								$flag            = true;
								$auction_errors .= __( 'Start Price should not be negative or empty.', 'auction-software' );
								update_post_meta( $post_id, $key . '_error', __( 'Start Price should not be negative or empty.', 'auction-software' ) );
							} else {
								update_post_meta( $post_id, $key . '_error', '' );
							}
							break;
						case 'auction_bid_increment':
							if ( $value < 0 ) {
								$flag            = true;
								$auction_errors .= __( 'Bid Increment should not be negative.', 'auction-software' );
								update_post_meta( $post_id, $key . '_error', __( 'Bid Increment should not be negative.', 'auction-software' ) );
							} else {
								update_post_meta( $post_id, $key . '_error', '' );
							}
							break;
						case 'auction_date_from':
							if ( '' === $value ) {
								$flag            = true;
								$auction_errors .= __( 'Date From should not be empty.', 'auction-software' );
								update_post_meta( $post_id, $key . '_error', __( 'Date From should not be empty.', 'auction-software' ) );
							} elseif ( $value > $date_to ) {
								$flag            = true;
								$auction_errors .= __( 'Date From should not be greater than Date To.', 'auction-software' );
								update_post_meta( $post_id, $key . '_error', __( 'Date From should not be greater than Date To.', 'auction-software' ) );
							} else {
								update_post_meta( $post_id, $key . '_error', '' );
							}
							break;
						case 'auction_date_to':
							if ( '' === $value ) {
								$flag            = true;
								$auction_errors .= __( 'Date To should not be empty.', 'auction-software' );
								update_post_meta( $post_id, $key . '_error', __( 'Date To should not be empty.', 'auction-software' ) );
							} elseif ( $value < $date_from ) {
								$flag            = true;
								$auction_errors .= __( 'Date To should not be smaller than Date From.', 'auction-software' );
								update_post_meta( $post_id, $key . '_error', __( 'Date To should not be smaller than Date From.', 'auction-software' ) );
							} else {
								update_post_meta( $post_id, $key . '_error', '' );
							}
							break;
						case 'auction_reserve_price':
							if ( 'auction_simple' === $data['type'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Reserve Price should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Reserve Price should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_reserve_price_reverse':
							if ( 'auction_reverse' === $data['type'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Reserve Price should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Reserve Price should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_reserve_price_penny':
							if ( 'auction_penny' === $data['type'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Reserve Price should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Reserve Price should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_buy_it_now_price':
							if ( 'auction_simple' === $data['type'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Buy It Now Price should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Buy It Now Price should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_buy_it_now_price_reverse':
							if ( 'auction_reverse' === $data['type'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Buy It Now Price should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Buy It Now Price should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_buy_it_now_price_penny':
							if ( 'auction_penny' === $data['type'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Buy It Now Price should not be negative.', 'auction-software' );
									update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Buy It Now Price should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
								}
							}
							break;
						case 'auction_wait_time_before_relist_if_fail':
							if ( 'relist' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Wait time before relist should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Wait time before relist should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_relist_duration_if_fail':
							if ( 'relist' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Relist duration should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Relist duration should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_wait_time_before_relist_if_not_paid':
							if ( 'relist' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Wait time before relist should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Wait time before relist should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_relist_duration_if_not_paid':
							if ( 'relist' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Relist duration should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Relist duration should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_wait_time_before_relist_always':
							if ( 'relist' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Wait time before relist should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Wait time before relist should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_relist_duration_always':
							if ( 'relist' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Relist duration should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Relist duration should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_wait_time_before_extend_if_fail':
							if ( 'extend' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Wait time before extend should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Wait time before extend should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_extend_duration_if_fail':
							if ( 'extend' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Extend duration should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Extend duration should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_wait_time_before_extend_if_not_paid':
							if ( 'extend' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Wait time before extend should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Wait time before extend should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_extend_duration_if_not_paid':
							if ( 'extend' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Extend duration should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Extend duration should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_wait_time_before_extend_always':
							if ( 'extend' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Wait time before extend should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Wait time before extend should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
						case 'auction_extend_duration_always':
							if ( 'extend' === $auction_data['auction_extend_or_relist_auction'] ) {
								if ( $value < 0 ) {
									$flag            = true;
									$auction_errors .= __( 'Extend duration should not be negative.', 'auction-software' );
									update_post_meta( $post_id, $key . '_error', __( 'Extend duration should not be negative.', 'auction-software' ) );
								} else {
									update_post_meta( $post_id, $key . '_error', '' );
								}
							}
							break;
					}
				}
			}
			if ( $flag ) {
				update_post_meta( $post_id, 'auction_errors', $auction_errors );
			}
		}
	}

	/**
	 * Validations for Auction product meta key-value pairs.
	 *
	 * @since 1.0.0
	 * @param string $key Meta key.
	 * @param string $value Meta value.
	 * @param int    $post_id Product post id.
	 * @param bool   $error_flag Error flag.
	 * @return int
	 */
	public function auction_software_check_validations( $key, $value, $post_id, $error_flag = false ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$product_type = isset( $_POST['product-type'] ) ? sanitize_text_field( wp_unslash( $_POST['product-type'] ) ) : '';
		$date_to      = isset( $_POST['auction_date_to'] ) ? sanitize_text_field( wp_unslash( $_POST['auction_date_to'] ) ) : '';
		$date_from    = isset( $_POST['auction_date_from'] ) ? sanitize_text_field( wp_unslash( $_POST['auction_date_from'] ) ) : '';
		switch ( $key ) {
			case 'start_price':
				if ( '' === $value || $value < 0 ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Start Price should not be negative or empty.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'bid_increment':
				if ( '' === $value || $value < 0 ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Bid Increment should not be negative or empty.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'date_from':
				if ( '' === $value ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Date From should not be empty.', 'auction-software' ) );
				} elseif ( $value > $date_to ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Date From should not be greater than Date To.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'date_to':
				if ( '' === $value ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Date To should not be empty.', 'auction-software' ) );
				} elseif ( $value < $date_from ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Date To should not be smaller than Date From.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'reserve_price':
				// Seller can lower but can not raise the reserve price.
				if ( 'auction_simple' === $product_type ) {
					if ( '' !== $value && $value < 0 ) {
						$error_flag = true;
						update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Reserve Price should not be negative.', 'auction-software' ) );
					} else {
						update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
					}
				}
				break;
			case 'reserve_price_reverse':
				// Seller can lower but can not raise the reserve price.
				if ( 'auction_reverse' === $product_type ) {
					if ( '' !== $value && $value < 0 ) {
						$error_flag = true;
						update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Reserve Price should not be negative.', 'auction-software' ) );
					} else {
						update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
					}
				}
				break;
			case 'buy_it_now_price':
				if ( 'auction_simple' === $product_type ) {
					if ( '' !== $value && $value < 0 ) {
						$error_flag = true;
						update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Buy It Now Price should not be negative.', 'auction-software' ) );
					} else {
						update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
					}
				}
				break;
			case 'buy_it_now_price_reverse':
				if ( 'auction_reverse' === $product_type ) {
					if ( '' !== $value && $value < 0 ) {
						$error_flag = true;
						update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Buy It Now Price should not be negative.', 'auction-software' ) );
					} else {
						update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
					}
				}
				break;
			case 'wait_time_before_relist_if_fail':
			case 'wait_time_before_relist_if_not_paid':
			case 'wait_time_before_relist_always':
				if ( '' !== $value && $value < 0 ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Wait time before relist should not be negative.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'relist_duration_if_fail':
			case 'relist_duration_if_not_paid':
			case 'relist_duration_always':
				if ( '' !== $value && $value < 0 ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Relist Duration should not be negative.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'wait_time_before_extend_if_fail':
			case 'wait_time_before_extend_if_not_paid':
			case 'wait_time_before_extend_always':
				if ( '' !== $value && $value < 0 ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Wait time before extend should not be negative.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'extend_duration_if_fail':
			case 'extend_duration_if_not_paid':
			case 'extend_duration_always':
				if ( '' !== $value && $value < 0 ) {
					$error_flag = true;
					update_post_meta( $post_id, 'auction_' . $key . '_error', __( 'Extend Duration should not be negative.', 'auction-software' ) );
				} else {
					update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				}
				break;
			case 'bid_price':
				update_post_meta( $post_id, 'auction_' . $key . '_error', '' );
				break;
			default:
				break;
		}
		return $error_flag;
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Auction product-type inventory section script.
	 *
	 * @since 1.0.0
	 */
	public function auction_software_product_auction_inventory_section() {
		if ( 'product' !== get_post_type() ) :
			return;
			endif;

		?>
		<script type='text/javascript'>
			jQuery(document).ready(function ($) {

				// General tab for auction products.
				jQuery('.general_options').addClass('show_if_simple show_if_external show_if_affiliate show_if_variable show_if_auction_simple show_if_auction_reverse show_if_auction_penny').show();
				jQuery('#general_product_data ._tax_status_field').parent().addClass('show_if_auction_simple show_if_auction_reverse show_if_auction_penny').show();

				// For Inventory tab.
				$('.inventory_options').addClass('show_if_auction_simple show_if_auction_reverse').show();
				$('#inventory_product_data ._manage_stock_field').addClass('show_if_auction_simple show_if_auction_reverse').show();
				$('#inventory_product_data ._sold_individually_field').parent().addClass('show_if_auction_simple show_if_auction_reverse').show();
				$('#inventory_product_data ._sold_individually_field').addClass('show_if_auction_simple show_if_auction_reverse').show();
			});
		</script>
		<?php
	}

	/**
	 * Auction product-type options.
	 *
	 * @since 1.0.0
	 * @param array $product_type_options Product type options.
	 * @return mixed
	 */
	public function auction_software_product_auction_type_options( $product_type_options ) {
		$classes = 'show_if_auction_simple show_if_auction_reverse';
		$classes = apply_filters( 'auction_software_auction_tabs_classes', $classes );
		if ( isset( $product_type_options['virtual']['wrapper_class'] ) ) {
			$product_type_options['virtual']['wrapper_class'] .= ' ' . $classes;
		} else {
			$product_type_options['virtual']['wrapper_class'] = $classes;
		}
		if ( isset( $product_type_options['downloadable']['wrapper_class'] ) ) {
			$product_type_options['downloadable']['wrapper_class'] .= ' ' . $classes;
		} else {
			$product_type_options['downloadable']['wrapper_class'] = $classes;
		}
		return $product_type_options;
	}

	/**
	 * Add auctions list to query vars.
	 *
	 * @param array $vars Vars array.
	 * @return array
	 */
	public function auction_software_query_vars( $vars ) {
		$vars[] = 'auctions_list';
		return $vars;
	}

	/**
	 * Add auction list to my account.
	 *
	 * @param array $items My account menu items.
	 * @return mixed
	 */
	public function auction_software_account_menu_items( $items ) {
		$items['auctions_list'] = __( 'Auctions', 'auction-software' );
		return $items;
	}

	/**
	 * My Auctions List endpoint
	 */
	public function auction_software_auctions_list_endpoint() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user_id  = get_current_user_id();
		$content  = '';
		$r        = WC_Auction_Software_Helper::get_auctions_list_products( $user_id );
		$content .= '<div id="auction_buy_bids">
                                <h3>' . esc_html__( 'My Auctions', 'auction-software' ) . '</h3>
                                <form id="auctions_list_form" type="post" enctype="multipart/form-data" action="#">
                                    <table>
                                        <tr>
                                            <td>' . esc_html__( 'Auctions', 'auction-software' ) . '</td>
                                            <td>' . esc_html__( 'Current Bid', 'auction-software' ) . '</td>
                                            <td>' . esc_html__( 'Item Condition', 'auction-software' ) . '</td>
                                            <td>' . esc_html__( 'Status', 'auction-software' ) . '</td>
                                            <td>' . esc_html__( 'Action', 'auction-software' ) . '</td>
                                        </tr>';
		if ( ! empty( $r ) ) {
			if ( $r->have_posts() ) {
				while ( $r->have_posts() ) {
					$r->the_post();

					global $product;
					$item_condition = WC_Auction_Software_Helper::get_auction_post_meta( $product->get_id(), 'auction_item_condition' );

					$content .= '<tr>
                                  <td>' . get_the_title() . '</td>';
					if ( true === $product->is_started( $product->get_id() ) ) {
						if ( $product->is_ended( $product->get_id() ) ) {
							$content .= '<td>' . wc_price( $product->get_auction_winning_bid() ) . '</td>';
						} else {
							$current_bid_value = $product->get_auction_current_bid();
							if ( 0.00 === (float) $current_bid_value ) {
								$content .= '<td>' . esc_html__( 'No bids yet', 'auction-software' ) . '</td>';
							} else {
								$content .= '<td>' . wc_price( $current_bid_value ) . '</td>';
							}
						}
					} else {
						$content .= '<td >' . esc_html__( 'No bids yet', 'auction-software' ) . '</td>';
					}
					$content .= '<td>' . ucfirst( $item_condition ) . '</td>';
					if ( $product->is_ended( $product->get_id() ) ) {

						$reserve_price_met = $product->check_if_reserve_price_met( $product->get_id() );
						$content          .= '<td>' . esc_html__( 'Auction finished', 'auction-software' ) . '</td>';

						$winner = $product->check_if_user_has_winning_bid( $product->get_auction_current_bid(), $user_id, $product->get_id() );
						if ( $winner && $reserve_price_met ) {
							if ( 1 !== (int) $product->get_auction_is_sold() ) {

								$content .= '<td><a class="button" 
                                                href ="' . get_site_url() . '?add-to-cart=' . $product->get_id() . '" 
                                                >' . $product->get_buy_it_now_cart_text() . '</a>
                                            </td>';
							} else {
								$content .= '<td>' . esc_html__( 'Won', 'auction-software' ) . '</td>';
							}
						} elseif ( 1 === (int) $product->get_auction_is_sold() ) {
							$user          = WC_Auction_Software_Helper::get_auction_user_by_status( $product->get_id() );
							$user_info     = get_userdata( $user );
							$won_user      = WC_Auction_Software_Helper::get_won_user_by_auction( $product->get_id() );
							$won_user_info = get_userdata( $won_user );
							if ( $user_id === $user ) {
								$content .= '<td>' . esc_html__( 'Won', 'auction-software' ) . '</td>';
							} else {
								if ( $reserve_price_met ) {
									$content .= '<td>' . esc_html__( 'Won by ', 'auction-software' ) . $won_user_info->display_name . '</td>';
								} else {
									$content .= '<td>' . esc_html__( 'Buy it Now Used by ', 'auction-software' ) . $user_info->display_name . '</td>';
								}
							}
						} else {
							$content .= '<td>--</td>';

						}
					} elseif ( false === $product->is_started( $product->get_id() ) ) {
						$content .= '<td >--</td><td>--</td>';
					} elseif ( ! $product->is_ended( $product->get_id() ) ) {
						$content .= '<td>' . esc_html__( 'Auction in progress', 'auction-software' ) . '</td>
                                        <td><a href="' . get_permalink() . '" data-quantity="1"
                                        class="button" data-product_id="' . $product->get_id() . '"
                                        data-product_sku="" aria-label="Read more about "' . get_the_title() . '" rel="nofollow">Bid Now</a></td>';
					}
				}
				$content .= '</tr>';

			}
		} else {
			$content .= '<tr>
                            <td colspan="5">' . esc_html__( 'You didn\'t participated in any Auctions.', 'auction-software' ) . '</td>
                         </tr>';
		}

		$content .= '</table></form></div>';

		echo wp_kses_post( $content );
	}

	/**
	 * Register gutenberg blocks / block-based widgets.
	 */
	public function auction_software_register_gutenberg_blocks() {

		// Get json data for all 8 blocks and decode it.
		$response = wp_remote_get( AUCTION_SOFTWARE_PLUGIN_URL . 'src/gutenberg-blocks/data.json' );
		$data;
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$data = wp_remote_retrieve_body( $response );
		}
		if ( $data ) {
			$data = json_decode( $data );
		} else {
			$data = array();
		}
		// Register a single script file which loops through all 8 blocks and regs them one by one.
		wp_register_script(
			'auction-software-auction-widgets',
			plugin_dir_url( __DIR__ ) . 'admin/js/gutenberg-blocks/auction-software-auction-widgets.js',
			array( 'wp-blocks', 'wp-components', 'wp-i18n' ),
			$this->version,
			false
		);

		// If function exists for WordPress 5 and above, loop through all 8 blocks and register them.
		if ( function_exists( 'register_block_type' ) ) {
			foreach ( $data as $chunk ) {
				register_block_type(
					'auction-software/' . $chunk->register_block_type,
					array(
						'editor_script'   => 'auction-software-auction-widgets',
						'attributes'      => array(
							'title'           => array(
								'type'    => 'string',
								'default' => $chunk->attributes_title_default,
							),
							'num_of_auctions' => array(
								'type'    => 'string',
								'default' => 5,
							),
							'hide_time_left'  => array(
								'type'    => 'boolean',
								'default' => false,
							),
						),
						'render_callback' => array( $this->block_callbacks, $chunk->render_callback ),
					)
				);
			}
		}
	}


}
