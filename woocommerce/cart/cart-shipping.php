<?php
/**
 * Shipping rows for the cart totals and checkout order review.
 *
 * Overrides woocommerce/templates/cart/cart-shipping.php. Renders the
 * "Choose how you want to receive" cards (Home Delivery with DHL Express /
 * Store Pickup) followed by a one-line "Delivery" summary. On the checkout
 * the cards row is hidden by CSS - checkout step 1 shows its own copy that
 * drives these radios (assets/js/checkout-steps.js).
 *
 * @package Medzuro
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$mz_rates  = ! empty( $available_methods ) && is_array( $available_methods ) ? $available_methods : array();
$mz_chosen = isset( $mz_rates[ $chosen_method ] ) ? $mz_rates[ $chosen_method ] : ( $mz_rates ? reset( $mz_rates ) : null );
?>
<tr class="woocommerce-shipping-totals shipping mz-ship-choose">
	<td colspan="2">
		<h3 class="mz-ship-choose__ttl"><?php esc_html_e( 'Choose how you want to receive', 'medzuro' ); ?></h3>
		<?php
		if ( $mz_rates ) {
			medzuro_delivery_cards( $mz_rates, $chosen_method, $index );
		} else {
			echo '<p class="mz-ship-choose__none">' . wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no delivery options available right now. Please contact us and we will help.', 'medzuro' ) ) ) . '</p>';
		}
		?>
	</td>
</tr>
<?php if ( $mz_chosen ) : ?>
	<tr class="mz-ship-summary">
		<th><?php echo esc_html( 'pickup' === ( $mz_chosen->get_meta_data()['type'] ?? '' ) ? __( 'Pickup', 'medzuro' ) : __( 'Delivery', 'medzuro' ) ); ?></th>
		<td data-title="<?php esc_attr_e( 'Delivery', 'medzuro' ); ?>">
			<span class="mz-ship-summary__name"><?php echo esc_html( $mz_chosen->get_label() ); ?></span>
			<strong class="mz-ship-summary__cost"><?php echo (float) $mz_chosen->get_cost() > 0 ? wp_kses_post( wc_price( $mz_chosen->get_cost() ) ) : esc_html__( 'FREE', 'medzuro' ); ?></strong>
		</td>
	</tr>
<?php endif; ?>
