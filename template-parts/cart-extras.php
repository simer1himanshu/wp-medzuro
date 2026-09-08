<?php
/**
 * Cart reassurance block.
 *
 * Ported from snippets/medzuro-cart-extras.liquid. Copy comes from that
 * snippet's schema defaults, which is what the storefront rendered.
 *
 * As in the original, reward points are deliberately not shown — that figure
 * has to come from a loyalty plugin or it would mislead.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$savings  = medzuro_cart_savings();
$delivery = apply_filters(
	'medzuro_cart_delivery_note',
	'Same-day delivery across most of Suva. 2-3 business days Fiji-wide.'
);
$secure   = apply_filters(
	'medzuro_cart_secure_note',
	'Secure checkout - your payment details are encrypted.'
);
?>
<div class="mz-cart-extras">

	<?php if ( $savings > 0 ) : ?>
		<div class="mz-cart-extras__row mz-cart-extras__savings">
			<?php medzuro_icon( 'tag', 18 ); ?>
			<span>
				<?php esc_html_e( 'You save', 'medzuro' ); ?>
				<strong><?php echo wp_kses_post( wc_price( $savings ) ); ?></strong>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( $delivery ) : ?>
		<div class="mz-cart-extras__row">
			<?php medzuro_icon( 'truck', 18 ); ?>
			<span><?php echo esc_html( $delivery ); ?></span>
		</div>
	<?php endif; ?>

	<?php if ( $secure ) : ?>
		<div class="mz-cart-extras__row">
			<?php medzuro_icon( 'lock', 18 ); ?>
			<span><?php echo esc_html( $secure ); ?></span>
		</div>
	<?php endif; ?>

	<?php medzuro_seller_badge( 'compact' ); ?>
</div>
