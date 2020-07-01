<?php
/**
 * Auction Software Auction Start email notification class.
 *
 * @since 1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/emails
 */

if ( class_exists( 'WC_Email' ) ) :
	/**
	 * Auction Software Auction Start email notification class.
	 *
	 * @package    Auction_Software
	 * @subpackage Auction_Software/woocommerce/classes/emails
	 */
	class WC_Auction_Software_Outbid extends WC_Email {

		/**
		 * Product id.
		 *
		 * @access public.
		 * @var int $product_id Product id.
		 */
		public $product_id;

		/**
		 * Product data.
		 *
		 * @access public
		 * @var array $product_data Product data.
		 */
		public $product_data;

		/**
		 * WC_Auction_Software_Outbid constructor.
		 */
		public function __construct() {

			global $woocommerce_auctions;

			$this->id          = 'auction_software_outbid';
			$this->title       = __( 'Auction Outbid', 'auction-software' );
			$this->description = __( 'Auction Outbid', 'auction-software' );

			$this->customer_email = true;

			$this->template_html  = 'emails/auction-software-outbid.php';
			$this->template_plain = 'emails/plain/auction-software-outbid.php';
			$this->template_base  = AUCTION_SOFTWARE_PLUGIN_TEMPLATE_PATH;

			$this->subject = __( '{product name} auction: You\'ve been outbid.', 'auction-software' );
			$this->heading = __( 'You\'ve been outbid.', 'auction-software' );

			add_action( 'woocommerce_auction_software_outbid_notification', array( $this, 'trigger' ) );

			parent::__construct();

			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Trigger notification email for auction outbid.
		 *
		 * @param array $data Data.
		 */
		public function trigger( $data ) {
			global $woocommerce;

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}
			global $wpdb;
			$emails             = array();
			$this->product_id   = $data['product_id'];
			$this->product_data = wc_get_product( $data['product_id'] );
			$results            = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d AND user_id != %d ORDER BY date DESC LIMIT 1', array( $data['product_id'], $data['user_id'] ) ) ); // db call ok; no-cache ok.
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					if ( 0 !== (int) $result->user_id ) {
						$the_user = get_user_by( 'id', $result->user_id );
						$emails[] = $the_user->user_email;
					}
				}
			}
			if ( ! empty( $emails ) ) {
				foreach ( $emails as $send_email_to ) {
					$this->send( $send_email_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				}
			}
		}

		/**
		 * Get email subject.
		 *
		 * @return mixed|string
		 */
		public function get_subject() {
			return str_replace( '{product name}', $this->product_data->get_title(), apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->get_option( 'subject', $this->get_default_subject() ) ), $this->object ) );
		}

		/**
		 * Get the email content in HTML format.
		 *
		 * @return false|string
		 */
		public function get_content_html() {
			ob_start();
			wc_get_template(
				$this->template_html,
				array(
					'email_heading' => $this->get_heading(),
					'product_id'    => $this->product_id,
					'email'         => $this,
				),
				'auction-software/',
				$this->template_base
			);
			return ob_get_clean();
		}

		/**
		 * Get the email content in plain text format.
		 *
		 * @return false|string
		 */
		public function get_content_plain() {
			ob_start();
			wc_get_template(
				$this->template_plain,
				array(
					'email_heading' => $this->get_heading(),
					'product_id'    => $this->product_id,
					'email'         => $this,
				),
				'auction-software/',
				$this->template_base
			);
			return ob_get_clean();
		}
	}
	return new WC_Auction_Software_Outbid();
endif;
