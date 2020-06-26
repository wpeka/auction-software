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
			'' => __( 'Bid Increment', 'auction-software' ),
		);
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;
		if ( '' === $current_section ) {
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
				array()
			);
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}
}
new WC_Auction_Software_Settings();
