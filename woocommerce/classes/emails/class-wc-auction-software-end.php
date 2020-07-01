<?php
/**
 * Auction Software Auction End email notification class.
 *
 * @since 1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/emails
 */

if ( class_exists( 'WC_Email' ) ) :
	/**
	 * Auction Software Auction End email notification class.
	 *
	 * @package    Auction_Software
	 * @subpackage Auction_Software/woocommerce/classes/emails
	 */
	class WC_Auction_Software_End extends WC_Email {

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
		 * WC_Auction_Software_End constructor.
		 */
		public function __construct() {

			global $woocommerce_auctions;

			$this->id          = 'auction_software_end';
			$this->title       = __( 'Auction End', 'auction-software' );
			$this->description = __( 'Auction End', 'auction-software' );

			$this->template_html  = 'emails/auction-software-end.php';
			$this->template_plain = 'emails/plain/auction-softwareend.php';
			$this->template_base  = AUCTION_SOFTWARE_PLUGIN_TEMPLATE_PATH;

			$this->subject = __( '{product name} auction has ended.', 'auction-software' );
			$this->heading = __( 'Auction has ended.', 'auction-software' );

			add_action( 'woocommerce_auction_software_end_notification', array( $this, 'trigger' ) );

			parent::__construct();

			$this->recipient = $this->get_option( 'recipient' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}
		}

		/**
		 * Trigger notification email for auction start.
		 *
		 * @param int $product_id Product id.
		 */
		public function trigger( $product_id ) {
			global $woocommerce, $wpdb;
			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}
			$emails             = array();
			$this->product_id   = $product_id;
			$this->product_data = wc_get_product( $product_id );
			$is_ended           = WC_Auction_Software_Helper::get_auction_post_meta( $product_id, 'auction_is_ended' );
			if ( 1 === (int) $is_ended ) {
				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT DISTINCT user_id FROM ' . $wpdb->prefix . 'auction_software_logs WHERE auction_id = %d', array( $product_id ) ) ); // db call ok; no-cache ok.
				if ( ! empty( $results ) ) {
					foreach ( $results as $result ) {
						$is_notified = WC_Auction_Software_Helper::check_if_user_notified_auction_end( $result->user_id, $product_id );
						if ( 0 === (int) $is_notified ) {
							if ( 0 !== (int) $result->user_id ) {
								$the_user = get_user_by( 'id', $result->user_id );
								$emails[] = $the_user->user_email;
							}
							update_post_meta( $product_id, 'auction_notify_' . $result->user_id . '_' . $product_id . '_is_ended', 1 );
						}
					}
				}
				if ( ! empty( $emails ) ) {
					$receipients    = explode( ', ', $this->get_recipient() );
					$send_email_tos = array_unique( array_merge( $receipients, $emails ) );
					foreach ( $send_email_tos as $send_email_to ) {
						$this->send( $send_email_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
					}
				}
			} elseif ( 0 === (int) $is_ended ) {
				$users = get_users();
				foreach ( $users as $user ) {
					update_post_meta( $product_id, 'auction_notify_' . $user->ID . '_' . $product_id . '_is_ended', 0 );
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
		 * Get email heading.
		 *
		 * @return mixed|string
		 */
		public function get_heading() {
			return str_replace( '{product name}', $this->product_data->get_title(), apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->get_option( 'heading', $this->get_default_heading() ) ), $this->object ) );
		}

		/**
		 * Get the email content in HTML format.
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

		/**
		 * Initialise Settings Form Fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'auction-software' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'auction-software' ),
					'default' => 'yes',
				),
				'recipient'  => array(
					'title'       => __( 'Recipient(s)', 'auction-software' ),
					'type'        => 'text',
					'description' => __( 'Enter recipients (comma separated) for this email.', 'auction-software' ),
					'placeholder' => esc_attr( get_option( 'admin_email' ) ),
					'default'     => '',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'auction-software' ),
					'type'        => 'text',
					'description' => '',
					'placeholder' => $this->subject,
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email Heading', 'auction-software' ),
					'type'        => 'text',
					'description' => '',
					'placeholder' => $this->heading,
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'auction-software' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'auction-software' ),
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => array(
						'plain'     => __( 'Plain text', 'auction-software' ),
						'html'      => __( 'HTML', 'auction-software' ),
						'multipart' => __( 'Multipart', 'auction-software' ),
					),
				),
			);
		}
	}
	return new WC_Auction_Software_End();
endif;
