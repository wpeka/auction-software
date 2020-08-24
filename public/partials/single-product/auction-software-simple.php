<?php
/**
 * Simple auction product.
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
$current_bid = $product->get_auction_current_bid();
$postid      = get_the_ID();
$user_id     = get_current_user_id();
// phpcs:disable WordPress.Security.NonceVerification.Missing
if ( isset( $_POST['auction-bid'] ) ) {
	if ( true === $product->is_started() ) {
		if ( is_user_logged_in() ) {
			$next_bid = isset( $_POST['price'] ) ? sanitize_text_field( wp_unslash( $_POST['price'] ) ) : 0;
			if ( 4 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Bid should be greater than or equal to bid increment.', 'auction-software' ), 'error' );
			} elseif ( 1 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Bid placed successfully.', 'auction-software' ), 'success' );
			} elseif ( 2 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Your bid is winning, no need to place again.', 'auction-software' ), 'success' );
			} elseif ( 3 === (int) $product->set_auction_current_bid( $current_bid, $next_bid, $user_id, $postid ) ) {
				wc_print_notice( __( 'Please enter a higher amount.', 'auction-software' ), 'error' );
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
do_action( 'auction_simple_before_add_to_cart_form' );
?>
<form action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
		class="auction_simple_cart" method="post" enctype='multipart/form-data'>
	<?php if ( $product->is_started() ) { ?>
		<input id="is_started" type="hidden" value="1" />
	<?php } else { ?>
		<input id="is_started" type="hidden" value="0" />
	<?php } ?>
	<input id="product_type" type="hidden" value="auction">
	<input id="end_date" type="hidden" value="<?php echo esc_attr( $product->get_auction_date_to() ); ?>"/>
	<input id="start_date" type="hidden" value="<?php echo esc_attr( $product->get_auction_date_from() ); ?>"/>
	<input id="auction_start_price" type="hidden" value="<?php echo esc_attr( $product->get_auction_start_price() ); ?>"/>
	<input id="auction_current_bid" type="hidden" value="<?php echo esc_attr( $product->get_auction_current_bid() ); ?>"/>
	<input id="auction_initial_bid_placed" type="hidden" value="<?php echo esc_attr( WC_Auction_Software_Helper::get_auction_post_meta( $postid, 'auction_initial_bid_placed' ) ); ?>"/>
	<input id="auction_bid_increment" type="hidden" value="<?php echo esc_attr( $product->get_auction_bid_increment() ); ?>"/>
	<?php if ( '' === $product->get_auction_errors() ) { ?>
		<?php if ( true === $product->is_started() && 1 !== (int) $product->get_auction_is_sold() && false === $product->is_ended() ) { ?>
			<table cellspacing="0">
				<tbody>
				<tr>
					<td>
						<label for="auction_start_price"><?php esc_html_e( 'Start Price: ', 'auction-software' ); ?></label>
					</td>
					<td class="title">
						<?php echo wc_price( $product->get_auction_start_price() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_current_bid"><?php esc_html_e( 'Current Bid:', 'auction-software' ); ?></label>
					</td>
					<td class="title auction_current_bid_simple">
					<?php
					$current_bid_value = $product->get_auction_current_bid();
					if ( 0 === (int) $current_bid_value ) {
						esc_html_e( 'No bids yet.', 'auction-software' );
					} else {
						echo wc_price( $product->get_auction_current_bid() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_bid_increment"><?php esc_html_e( 'Bid Increment: ', 'auction-software' ); ?></label>
					</td>
					<td class="title auction_bid_increment">
						<?php echo wc_price( $product->get_auction_bid_increment() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_item_condition"><?php esc_html_e( 'Item Condition: ', 'auction-software' ); ?></label>
					</td>
					<td class="title">
						<?php echo esc_attr( $product->get_auction_item_condition() ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_time_left"><?php esc_html_e( 'Time Left:', 'auction-software' ); ?></label>
					</td>
					<td class="timer">
						<p id="time_left"></p>
					</td>
				</tr>
				<?php
				$max_bid_user = $product->get_auction_max_bid_user();
				$max_bid      = $product->get_auction_max_bid();
				if ( ! empty( $max_bid_user ) && $user_id === (int) $max_bid_user ) {
					$style = 'display:table-row';
				} else {
					$style = 'display:none';
				}
				?>
				<tr id="auction_max_bid" style="<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
					<td>
						<label for="auction_max_bid"><?php esc_html_e( 'Your Maximum Bid: ', 'auction-software' ); ?></label>
					</td>
					<td class="title auction_max_bid_simple">
						<?php echo wc_price( $max_bid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				</tbody>
			</table>
			<?php
			$reserve_price_met = $product->check_if_reserve_price_met( $postid );
			if ( ! $reserve_price_met ) {
				$reserve_price_text = esc_html__( 'Reserve price not met.', 'auction-software' );
			} else {
				$reserve_price_text = esc_html__( 'Reserve price met.', 'auction-software' );
			}
			?>
			<p class="auction_reserve_price"><?php echo esc_attr( trim( $reserve_price_text ) ); ?></p>
			<div class="container">
				<div class="button-container">
					<button class="cart-price-plus" type="button" value="+">+</button>
					<input type="text" name="price" class="price" maxlength="12" value="0" class="input-text price" id="auction-price-incr-decr"/>
					<button class="cart-price-minus" type="button" value="-">-</button>
				</div>
				<br />
				<div class="button-container">
					<button type="submit" name="auction-bid" value="<?php echo esc_attr( $product->get_id() ); ?>"
							class="auction-bid-simple button alt"><?php echo esc_attr( $product->single_add_to_cart_text() ); ?></button>
					<?php if ( $product->get_auction_current_bid() < $product->get_auction_buy_it_now_price() ) { ?>
						<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
								class="single_add_to_cart_button button alt"><?php echo $product->get_buy_it_now_cart_text(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
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
				if ( 1 !== (int) $product->get_auction_is_sold() ) {
					?>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
							class="single_add_to_cart_button button alt"><?php echo $product->get_buy_it_now_cart_text(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<?php
				}
			}
			?>
			<?php } else { ?>
			<table cellspacing="0">
				<tbody>
				<tr>
					<td>
						<label for="auction_start_price"><?php esc_html_e( 'Start Price: ', 'auction-software' ); ?></label>
					</td>
					<td class="title">
						<?php echo wc_price( $product->get_auction_start_price() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_bid_increment"><?php esc_html_e( 'Bid Increment: ', 'auction-software' ); ?></label>
					</td>
					<td class="title auction_bid_increment">
						<?php echo wc_price( $product->get_auction_bid_increment() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_item_condition"><?php esc_html_e( 'Item Condition: ', 'auction-software' ); ?></label>
					</td>
					<td class="title">
						<?php echo esc_attr( $product->get_auction_item_condition() ); ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="auction_starts_in"><?php esc_html_e( 'Auction starts in:', 'auction-software' ); ?></label>
					</td>
					<td class="timer">
						<p id="time_start"></p>
					</td>
				</tr>
				</tbody>
			</table>
				<?php
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

			add_filter( 'woocommerce_product_tabs', 'auction_history_tab' );

	} else {
		?>
		<p class="auction_error">
			<?php if ( ! empty( $product->get_auction_errors() ) ) { ?>
				<?php esc_html_e( 'Please resolve the errors from Product admin.', 'auction-software' ); ?> <br>
			<?php } ?>
		</p>
	<?php } ?>

</form>
<?php if ( false === $product->is_ended() ) { ?>
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

<?php do_action( 'auction_simple_after_add_to_cart_form' ); ?>
