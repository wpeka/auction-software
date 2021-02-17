<?php
/**
 * Auction Software WooCommerce Settings.
 *
 * @package Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Settings_Auctions.
 */
class WC_Auction_Software_Settings extends WC_Settings_Page {

	/**
	 * Auction classes.
	 *
	 * @access public
	 * @var array $auction_classes Auction classes.
	 */
	public $auction_classes = array();

	/**
	 * WC_Auction_Software_Settings constructor.
	 */
	public function __construct() {
		$this->id    = 'auctions';
		$this->label = __( 'Auctions', 'auction-software' );
		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			''     => __( 'Global Settings', 'auction-software' ),
			'bids' => __( 'Bid Increment', 'auction-software' ),
		);
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;
		if ( '' === $current_section ) {
			$settings = $this->get_settings();

			WC_Admin_Settings::output_fields( $settings );
		} elseif ( 'bids' === $current_section ) {
			$this->output_bid_range_increment_screen();
		}
		do_action( 'woocommerce_output_sections_' . $this->id, $current_section );
	}

	/**
	 * Output bid ranges settings screen.
	 */
	protected function output_bid_range_increment_screen() {
		global $hide_save_button;
		$hide_save_button = true;
		wp_localize_script(
			'auction-software-wc-settings',
			'auctionClassesLocalizeScript',
			array(
				'classes'                  => $this->get_auction_classes(),
				'default_auction_class'    => array(
					'term_id'     => 0,
					'name'        => '',
					'description' => '',
				),
				'wc_auction_classes_nonce' => wp_create_nonce( 'wc_auction_classes_nonce' ),
				'strings'                  => array(
					'unload_confirmation_msg' => __( 'Your changed data will be lost if you leave this page without saving.', 'auction-software' ),
					'save_failed'             => __( 'Your changes were not saved. Please retry.', 'auction-software' ),
				),
			)
		);
		wp_enqueue_script( 'auction-software-wc-settings' );

		// Extendable columns to show on the bidrange classes screen.
		$auction_class_columns = apply_filters(
			'woocommerce_auction_classes_columns',
			array(
				'wc-auction-class-lower'     => sprintf(
					/* translators: 1: Currency symbol */
					__( 'Lower Limit (%s)', 'auction-software' ),
					esc_attr( get_woocommerce_currency_symbol() )
				),
				'wc-auction-class-upper'     => sprintf(
					/* translators: 1: Currency symbol */
					__( 'Upper Limit (%s)', 'auction-software' ),
					esc_attr( get_woocommerce_currency_symbol() )
				),
				'wc-auction-class-increment' => __( 'Increment', 'auction-software' ),
			)
		);

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'settings/views/auction-software-settings.php';
	}

	/**
	 * Return auction classes.
	 *
	 * @return mixed|void
	 */
	public function get_auction_classes() {
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

		return apply_filters( 'woocommerce_get_auction_classes', $this->auction_classes );
	}

	/**
	 * Returns excluded fields for single product page.
	 *
	 * @return mixed|void
	 */
	public function get_excluded_fields() {
		$excluded_fields = array(
			'add_to_watchlist'   => __( 'Add to Watchlist', 'auction-software' ),
			'auction_history'    => __( 'Auction History', 'auction-software' ),
			'available_bids'     => __( 'Available Bids', 'auction-software' ),
			'bid_increment'      => __( 'Bid Increment', 'auction-software' ),
			'current_bid'        => __( 'Current Bid', 'auction-software' ),
			'ends_in'            => __( 'Auction Ends In', 'auction-software' ),
			'ending_on'          => __( 'Ending On', 'auction-software' ),
			'item_condition'     => __( 'Item Condition', 'auction-software' ),
			'maximum_bid'        => __( 'Your Maximum Bid', 'auction-software' ),
			'reserve_price_text' => __( 'Reserve Price Text', 'auction-software' ),
			'starts_in'          => __( 'Auction Starts In', 'auction-software' ),
			'starting_on'        => __( 'Starting On', 'auction-software' ),
			'start_price'        => __( 'Start Price', 'auction-software' ),
		);
		asort( $excluded_fields );
		return apply_filters( 'woocommerce_auction_excluded_fields', $excluded_fields );
	}

	/**
	 * Returns excluded fields for product loop and widgets.
	 *
	 * @return mixed|void
	 */
	public function get_excluded_loop_fields() {
		$excluded_fields = array(
			'current_bid' => __( 'Current Bid', 'auction-software' ),
			'ends_in'     => __( 'Auction Ends In', 'auction-software' ),
			'starts_in'   => __( 'Auction Starts In', 'auction-software' ),
		);
		asort( $excluded_fields );
		return apply_filters( 'woocommerce_auction_excluded_fields', $excluded_fields );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
		}
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section name.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		$settings = array();
		if ( '' === $current_section ) {
			$settings = apply_filters(
				'woocommerce_auctions_settings',
				array(

					array(
						'title' => __( 'Global Settings', 'auction-software' ),
						'type'  => 'title',
						'desc'  => __( 'This is where you can set the global settings for the auction products', 'woocommerce' ),
						'id'    => 'global_settings',
					),
					array(
						'title'   => __( 'Enable proxy bidding', 'auction-software' ),
						'type'    => 'checkbox',
						'id'      => 'auctions_proxy_bidding_on',
						'default' => 'no',
					),
					array(
						'title'   => __( 'Enable anti sniping', 'auction-software' ),
						'type'    => 'checkbox',
						'id'      => 'auctions_anti_snipping_on',
						'default' => 'no',
					),
					array(
						'title'       => __( 'Anti sniping start time', 'auction-software' ),
						'placeholder' => __( 'In Minutes', 'auction-software' ),
						'desc'        => __( 'Time remaining for auction to end (in minutes)', 'auction-software' ),
						'type'        => 'text',
						'id'          => 'auctions_anti_snipping_trigger_time',
						'default'     => '5',
					),
					array(
						'title'       => __( 'Anti sniping duration', 'auction-software' ),
						'placeholder' => __( 'In Seconds', 'auction-software' ),
						'desc'        => __( 'Extend the auction end time by (in seconds)', 'auction-software' ),
						'type'        => 'text',
						'id'          => 'auctions_anti_snipping_duration',
						'default'     => '60',
					),
					array(
						'title'       => __( 'Bidding information update duration', 'auction-software' ),
						'placeholder' => __( 'In Seconds', 'auction-software' ),
						'desc'        => __( 'Time interval between two ajax requests in seconds (bigger intervals means less load for server)', 'auction-software' ),
						'type'        => 'text',
						'id'          => 'auctions_update_bidding_info_duration',
						'default'     => '60',
					),
					array(
						'id'                => 'auctions_excluded_fields',
						'title'             => __( 'Exclude auction fields on single product page', 'auction-software' ),
						'type'              => 'multiselect',
						'class'             => 'wc-enhanced-select',
						'css'               => 'width: 400px;',
						'default'           => '',
						'options'           => $this->get_excluded_fields(),
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select some fields', 'auction-software' ),
						),
					),
					array(
						'id'                => 'auctions_excluded_fields_product_shop',
						'title'             => __( 'Exclude auction fields on product shop page', 'auction-software' ),
						'type'              => 'multiselect',
						'class'             => 'wc-enhanced-select',
						'css'               => 'width: 400px;',
						'default'           => '',
						'options'           => $this->get_excluded_loop_fields(),
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select some fields', 'auction-software' ),
						),
					),
					array(
						'id'                => 'auctions_excluded_fields_product_widget',
						'title'             => __( 'Exclude auction fields in auction widgets', 'auction-software' ),
						'type'              => 'multiselect',
						'class'             => 'wc-enhanced-select',
						'css'               => 'width: 400px;',
						'default'           => '',
						'options'           => $this->get_excluded_loop_fields(),
						'custom_attributes' => array(
							'data-placeholder' => __( 'Select some fields', 'auction-software' ),
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'global_settings',
					),

				)
			);
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}
}
new WC_Auction_Software_Settings();
