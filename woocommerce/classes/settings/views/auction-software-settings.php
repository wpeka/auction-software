<?php
/**
 * WooCommerce Admin Settings
 *
 * @package Auction_Software
 * @subpackage Auction_Software/woocommerce/classes/settings/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h2>
	<?php esc_html_e( 'Bid Increment Ranges', 'auction-software' ); ?>
</h2>
<span class="description">
<?php
echo sprintf(
	/* translators: 1: Currency symbol, 2: Currency symbol, 3: Currency symbol, 4: Currency symbol, 5: Currency symbol, 6: Currency symbol*/
	esc_html__( 'Next bid price will be determined by this. Here you can set a different increment for each range. For example, you can say that if the auction price is between %1$s1 and %2$s50, then bid increments need to be at least %3$s2. You can then add another range say between %4$s51 to %5$s100, the bid increment needs to be %6$s5.', 'auction-software' ),
	esc_attr( get_woocommerce_currency_symbol() ),
	esc_attr( get_woocommerce_currency_symbol() ),
	esc_attr( get_woocommerce_currency_symbol() ),
	esc_attr( get_woocommerce_currency_symbol() ),
	esc_attr( get_woocommerce_currency_symbol() ),
	esc_attr( get_woocommerce_currency_symbol() )
);
?>
		</span>
<table class="wc-auction-classes widefat">
	<thead>
	<tr>
		<?php foreach ( $auction_class_columns as $class => $heading ) : ?>
			<th class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $heading ); ?></th>
		<?php endforeach; ?>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="<?php echo absint( count( $auction_class_columns ) ); ?>">
			<button type="submit" name="save" class="button button-primary wc-auction-class-save"
					value="<?php esc_attr_e( 'Save Ranges', 'auction-software' ); ?>"
					disabled><?php esc_html_e( 'Save Ranges', 'auction-software' ); ?></button>
			<a class="button button-secondary wc-auction-class-add" href="#"><?php esc_html_e( 'Add New Range', 'auction-software' ); ?></a>
		</td>
	</tr>
	</tfoot>
	<tbody class="wc-auction-class-rows"></tbody>
</table>

<script type="text/html" id="tmpl-wc-auction-class-row-blank">
	<tr>
		<td class="wc-auction-classes-blank-state" colspan="<?php echo absint( count( $auction_class_columns ) ); ?>">
			<p><?php esc_html_e( 'No ranges have been created.', 'auction-software' ); ?></p></td>
	</tr>
</script>

<script type="text/html" id="tmpl-wc-auction-class-row">
	<tr data-id="{{ data.term_id }}">
		<?php
		foreach ( $auction_class_columns as $class => $heading ) {
			echo '<td class="' . esc_attr( $class ) . '">';
			switch ( $class ) {
				case 'wc-auction-class-lower':
					?>
					<div class="view">
						{{ data.name }}
						<div class="row-actions">
							<a class="wc-auction-class-edit" href="#"><?php esc_html_e( 'Edit', 'auction-software' ); ?></a>
					|       <a href="#" class="wc-auction-class-delete"><?php esc_html_e( 'Remove', 'auction-software' ); ?></a>
						</div>
					</div>
					<div class="edit">
						<input type="number" name="name[{{ data.term_id }}]" data-attribute="name"
							value="{{ data.name }}"
							placeholder="<?php esc_attr_e( 'Lower Limit', 'auction-software' ); ?>"/>
						<div class="row-actions">
							<a class="wc-auction-class-cancel-edit"
								href="#"><?php esc_html_e( 'Cancel changes', 'auction-software' ); ?></a>
						</div>
					</div>
					<?php
					break;
				case 'wc-auction-class-upper':
					?>
					<div class="view">{{ data.slug }}</div>
					<div class="edit"><input type="number" name="slug[{{ data.term_id }}]" data-attribute="slug"
											value="{{ data.slug }}"
											placeholder="<?php esc_attr_e( 'Upper Limit', 'auction-software' ); ?>"/></div>
					<?php
					break;
				case 'wc-auction-class-increment':
					?>
					<div class="view">{{ data.description }}</div>
					<div class="edit"><input type="number" name="description[{{ data.term_id }}]"
											data-attribute="description" value="{{ data.description }}"
											placeholder="<?php esc_attr_e( 'Increment', 'auction-software' ); ?>"/></div>
					<?php
					break;
				default:
					do_action( 'woocommerce_auction_classes_column_' . $class );
					break;
			}
			echo '</td>';
		}
		?>
	</tr>
</script>
