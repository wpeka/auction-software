<?php
/**
 * Reverse auction product.
 *
 * @link       https://club.wpeka.com
 * @since      1.0.0
 *
 * @package    Auction_Software
 * @subpackage Auction_Software/public/partials/single-product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;
$current_bid         = $product->get_auction_current_bid();
$postid              = get_the_ID();
$user_id             = get_current_user_id();
$excluded_fields     = get_option( 'auctions_excluded_fields', array() );
$auction_date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
$timezone_string     = get_option( 'timezone_string' );
if ( ! $timezone_string ) {
	$timezone_string = 'UTC' . wp_timezone_string();
}
// phpcs:disable WordPress.Security.NonceVerification.Missing
if ( isset( $_POST['auction-bid'] ) ) {
	if ( true === $product->is_started() ) {
		if ( is_user_logged_in() ) {
			$next_bid = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : 0;
			if ( 4 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Bid should be less than or equal to bid increment.', 'auction-software' ), 'error' );
			} elseif ( 1 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Bid placed successfully.', 'auction-software' ), 'success' );
			} elseif ( 2 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Your bid is winning, no need to place again.', 'auction-software' ), 'success' );
			} elseif ( 3 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Please enter a lower amount.', 'auction-software' ), 'error' );
			} else {
				wc_print_notice( __( 'Bid not placed successfully.', 'auction-software' ), 'error' );
			}
		} else {
			wc_print_notice( __( 'You must be logged in to place bid. ', 'auction-software' ) . '<a class="button" href="' . get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '">' . __( 'Log in', 'auction-software' ) . '</a>', 'error' );
			return;
		}
	} else {
		wc_print_notice( __( 'Auction has expired.', 'auction-software' ), 'error' );
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Missing
do_action( 'auction_reverse_before_add_to_cart_form' );
?>
<form action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
		class="auction-software-form auction_reverse_cart" method="post" enctype='multipart/form-data'>
	<?php if ( $product->is_started() ) { ?>
		<input id="is_started" type="hidden" value="1" />
	<?php } else { ?>
		<input id="is_started" type="hidden" value="0" />
	<?php } ?>
	<input id="product_type" type="hidden" value="auction">
	<input id="product_auction_type" type="hidden" value="<?php echo esc_attr( $product->get_type() ); ?>">
	<input id="end_date" type="hidden" value="<?php echo esc_attr( $product->get_auction_date_to() ); ?>"/>
	<input id="start_date" type="hidden" value="<?php echo esc_attr( $product->get_auction_date_from() ); ?>"/>
	<input id="auction_start_price" type="hidden" value="<?php echo esc_attr( $product->get_auction_start_price() ); ?>"/>
	<input id="auction_current_bid" type="hidden" value="<?php echo esc_attr( $product->get_auction_current_bid() ); ?>"/>
	<input id="auction_initial_bid_placed" type="hidden"
		value="<?php echo esc_attr( WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_initial_bid_placed' ) ); ?>"/>
	<input id="auction_bid_increment" type="hidden"
		value="<?php echo esc_attr( $product->get_auction_bid_increment() ); ?>"/>
	<?php if ( '' === $product->get_auction_errors() || 0 === $product->get_auction_errors() ) { ?>
		<?php if ( true === $product->is_started() && 1 !== (int) $product->get_auction_is_sold() && false === $product->is_ended() ) { ?>
		<table cellspacing="0">
			<tbody>
			<?php if ( ! in_array( 'start_price', $excluded_fields, true ) ) : ?>
			<tr>
				<td>
					<label for="auction_start_price"><?php esc_html_e( 'Start Price: ', 'auction-software' ); ?></label>
				</td>
				<td class="title">
					<?php // The below phpcs ignore comment has been added after referring WooCommerce Plugin. ?>
					<?php echo wc_price( $product->get_auction_start_price() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			</tr>
				<?php
			endif;
			if ( ! in_array( 'current_bid', $excluded_fields, true ) ) :
				?>
			<tr>
				<td>
					<label for="auction_current_bid"><?php esc_html_e( 'Current Bid: ', 'auction-software' ); ?></label>
				</td>
				<td class="title auction_current_bid_reverse">
					<?php
					$current_bid_value = $product->get_auction_current_bid();
					if ( 0.00 === (float) $current_bid_value ) {
						esc_html_e( 'No bids yet ', 'auction-software' );
					} else {
						// The below phpcs ignore comment has been added after referring WooCommerce Plugin.
						echo wc_price( $product->get_auction_current_bid() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</td>
			</tr>
				<?php
			endif;
			if ( ! in_array( 'bid_increment', $excluded_fields, true ) ) :
				?>
			<tr>
				<td>
					<label for="auction_bid_increment"><?php esc_html_e( 'Bid Increment: ', 'auction-software' ); ?></label>
				</td>
				<td class="title auction_bid_increment">
					<?php // The below phpcs ignore comment has been added after referring WooCommerce Plugin. ?>
					<?php echo wc_price( $product->get_auction_bid_increment() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			</tr>
				<?php
			endif;
			if ( ! in_array( 'item_condition', $excluded_fields, true ) ) :
				?>
			<tr>
				<td>
					<label for="auction_item_condition"><?php esc_html_e( 'Item Condition: ', 'auction-software' ); ?></label>
				</td>
				<td class="title">
					<?php echo esc_attr( $product->get_auction_item_condition() ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php
			$max_bid_user = $product->get_auction_max_bid_user();
			$max_bid      = $product->get_auction_max_bid();
			if ( ! empty( $max_bid_user ) && $user_id === (int) $max_bid_user && ! in_array( 'maximum_bid', $excluded_fields, true ) ) {
				$style = 'display:table-row';
			} else {
				$style = 'display:none';
			}
			?>
			<tr id="auction_max_bid" style="<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
				<td>
					<label for="auction_max_bid"><?php esc_html_e( 'Your Maximum Bid: ', 'auction-software' ); ?></label>
				</td>
				<td class="title auction_max_bid_reverse">
					<?php // The below phpcs ignore comment has been added after referring WooCommerce Plugin. ?>
					<?php echo wc_price( $max_bid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			</tr>
			</tbody>
		</table>
			<?php if ( ! in_array( 'ends_in', $excluded_fields, true ) ) : ?>
			<p for="auction_time_left" class="auction-time	"><?php esc_html_e( 'Auction Ends In:', 'auction-software' ); ?></p>
			<p class="time-left" id="time_left">Auction Ends In</p>
			<?php endif; ?>
			<?php if ( ! in_array( 'ending_on', $excluded_fields, true ) ) : ?>
				<p for="auction_ending_time" class="auction-ending-time"><?php esc_html_e( 'Ending On: ', 'auction-software' ); ?><?php echo esc_attr( gmdate( $auction_date_format, strtotime( $product->get_auction_date_to() ) ) . ' (' . $timezone_string . ')' ); ?></p>
			<?php endif; ?>
			<?php
			if ( ! in_array( 'reserve_price_text', $excluded_fields, true ) ) :
				$reserve_price_met  = $product->check_if_reserve_price_met( $postid );
				$reserve_text_style = '';
				if ( ! $reserve_price_met ) {
					$reserve_text_style = 'color:#e2401c;';
					$reserve_price_text = esc_html__( 'Reserve price not met.', 'auction-software' );
				} else {
					$reserve_text_style = 'color:#0f834d';
					$reserve_price_text = esc_html__( 'Reserve price met.', 'auction-software' );
				}
				?>
			<p class="auction_reserve_price" style="<?php echo $reserve_text_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php echo esc_attr( trim( $reserve_price_text ) ); ?></p>
			<?php endif; ?>
		<div class="container">
			<div class="button-container">
				<div class="auction-price">
					<label for="price" class="auction-price-label"><?php echo get_woocommerce_currency_symbol(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
					<input type="number" name="price" class="auction-price-incr-decr price" value="0" class="input-text price" id="auction-price-incr-decr"></input>
				</div>
				<button type="submit" name="auction-bid" value="<?php echo esc_attr( $product->get_id() ); ?>"
					class="auction-bid-reverse button alt" id="auction-bid-reverse"><?php echo esc_attr( $product->single_add_to_cart_text() ); ?></button>
					<?php // The below phpcs ignore comment has been added after referring WooCommerce Plugin. ?>
				<p class="auction-start-text">(Enter less than or equal to: <?php echo wc_price( $product->get_auction_start_price() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>)</p> 
			</div>
			<br />
			<div class="button-container">
				<?php if ( ( $product->get_auction_current_bid() > $product->get_auction_buy_it_now_price() ) || 0 === (int) $product->get_auction_current_bid() ) { ?>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo $product->get_buy_it_now_cart_text(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<?php } ?>
			</div>
		</div>
			<?php
		} elseif ( true === $product->is_ended() ) {
			$is_ended = WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_is_ended' );
			if ( 1 !== (int) $is_ended ) {
				update_post_meta( $postid, 'auction_is_ended', 1 );
				WC_Auction_Software_Helper::set_auction_bid_logs( '', $postid, $current_bid, current_time( 'mysql' ), 'ended' );
			}
			?>
			<?php $reserve_price_met = $product->check_if_reserve_price_met( $postid ); ?>
	<div id="auction-expired">
			<?php esc_html_e( 'Auction has ended.', 'auction-software' ); ?>
			<?php
			if ( ! $reserve_price_met ) {
				esc_html_e( 'Reserve price not met.', 'auction-software' );
			}
			?>
	</div>
			<?php
			$winner = $product->check_if_user_has_winning_bid( $current_bid, $user_id, $postid );
			if ( $winner && $reserve_price_met ) {
				?>
				<?php if ( 1 !== (int) $product->get_auction_is_sold() ) { ?>
				<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
						class="single_add_to_cart_button button alt"><?php echo $product->get_buy_it_now_cart_text(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<?php
				}
			}
			?>
			<?php } else { ?>
			<table cellspacing="0">
				<tbody>
			<?php if ( ! in_array( 'start_price', $excluded_fields, true ) ) : ?>
				<tr>
					<td>
						<label for="auction_start_price"><?php esc_html_e( 'Start Price: ', 'auction-software' ); ?></label>
					</td>
					<td class="title">
						<?php // The below phpcs ignore comment has been added after referring WooCommerce Plugin. ?>
						<?php echo wc_price( $product->get_auction_start_price() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<?php
			endif;
			if ( ! in_array( 'bid_increment', $excluded_fields, true ) ) :
				?>
				<tr>
					<td>
						<label for="auction_bid_increment"><?php esc_html_e( 'Bid Increment: ', 'auction-software' ); ?></label>
					</td>
					<td class="title auction_bid_increment">
						<?php // The below phpcs ignore comment has been added after referring WooCommerce Plugin. ?>
						<?php echo wc_price( $product->get_auction_bid_increment() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<?php
			endif;
			if ( ! in_array( 'item_condition', $excluded_fields, true ) ) :
				?>
				<tr>
					<td>
						<label for="auction_item_condition"><?php esc_html_e( 'Item Condition: ', 'auction-software' ); ?></label>
					</td>
					<td class="title">
						<?php echo esc_attr( $product->get_auction_item_condition() ); ?>
					</td>
				</tr>
				<?php
			endif;
			?>
				</tbody>
			</table>
			<?php if ( ! in_array( 'starts_in', $excluded_fields, true ) ) : ?>
				<p for="auction_starts_in" class="auction-time"><?php esc_html_e( 'Auction starts in:', 'auction-software' ); ?></p>
				<p class="time-left" id="time_start">Auction Starts In</p>
			<?php endif; ?>
			<?php
			if ( ! in_array( 'starting_on', $excluded_fields, true ) ) :
				?>
				<p for="auction_starting_time" class="auction-starting-time"><?php esc_html_e( 'Starting On: ', 'auction-software' ); ?><?php echo esc_attr( gmdate( $auction_date_format, strtotime( $product->get_auction_date_from() ) ) . ' (' . $timezone_string . ')' ); ?></p>
				<?php
			endif;
			}
			/**
			 * Display history tabs.
			 *
			 * @param array $tabs Tabs.
			 * @return mixed
			 */
			function auction_history_tab( $tabs ) {
				$tabs['auction_history_tab'] = array(
					'title'    => __( 'Auction History', 'auction-software' ),
					'priority' => 1,
					'callback' => 'auction_history_tab_content',
				);
				return $tabs;
			}

			/**
			 * History tab content.
			 */
			function auction_history_tab_content() {
				echo '<h2>' . esc_html__( 'Auction History', 'auction-software' ) . '</h2>';
				echo WC_Auction_Software_Helper::get_auction_history( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			if ( ! in_array( 'auction_history', $excluded_fields, true ) ) :
				add_filter( 'woocommerce_product_tabs', 'auction_history_tab' );
		endif;

	} else {
		?>
			<p class="auction_error">
				<?php if ( ! empty( $product->get_auction_errors() ) ) { ?>
					<?php esc_html_e( 'Please resolve the errors from Product admin.', 'auction-software' ); ?> <br>
				<?php } ?>
			</p>
		<?php } ?>

</form>
<?php if ( false === $product->is_ended() && ! in_array( 'add_to_watchlist', $excluded_fields, true ) ) { ?>
	<div class="auctionwatchlist">
		<?php
		if ( is_user_logged_in() ) {
			$logged_in = 'none';
			if ( $product->is_in_users_watchlist() ) {
				$add_watchlist    = 'none';
				$remove_watchlist = 'block';
			} else {
				$add_watchlist    = 'block';
				$remove_watchlist = 'none';
			}
			?>
			<a id="auctionremove-watchlist" href="#" class="auctionremove-watchlist"
				data-value="<?php echo esc_attr( $product->get_id() ); ?>" style="display: <?php echo esc_attr( $remove_watchlist ); ?>">
				<span class="dashicons dashicons-hidden"></span>&nbsp;
				<span class="remove-watchlist-text"><?php esc_html_e( 'Remove from watchlist', 'auction-software' ); ?></span>
			</a>
			<a id="auctionadd-watchlist" href="#" class="auctionadd-watchlist"
				data-value="<?php echo esc_attr( $product->get_id() ); ?>" style="display: <?php echo esc_attr( $add_watchlist ); ?>">
				<span class="dashicons dashicons-visibility"></span>&nbsp;
				<span class="add-watchlist-text"><?php esc_html_e( 'Add to watchlist', 'auction-software' ); ?></span>
			</a>
			<?php
		} else {
			$logged_in = 'block';
		}
		?>
		<?php
		echo '<p id="auctionlogin_message" style="display:' . esc_attr( $logged_in ) . '">';
		echo sprintf(
			/* translators: %s: Login link */
			esc_html__( 'Sorry, you must be logged in to add auction to watchlist. %s', 'auction-software' ),
			sprintf(
				/* translators: %s: Myaccount link */
				'<a href="%s" class="button">Login &rarr;</a>',
				esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) )
			)
		);
		echo '</p>';
		?>
	</div>
<?php } ?>
<?php do_action( 'auction_reverse_after_add_to_cart_form' ); ?>
