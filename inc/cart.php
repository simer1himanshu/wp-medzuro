<?php
/**
 * Cart additions.
 *
 * The Shopify cart (sections/cart-template.liquid, 736 lines) was stock Avone
 * with exactly one Medzuro addition: the reassurance block rendered by
 * snippets/medzuro-cart-extras.liquid. WooCommerce's own cart template is
 * well-tested and already handles coupons, shipping calculation, quantity
 * updates and cross-sells, so it is left in place and the Medzuro block is
 * hooked into it rather than reimplementing 736 lines of Avone markup.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * The authorised-seller badge.
 *
 * Ported from snippets/medzuro-seller-badge.liquid. Settings come from that
 * snippet's schema defaults, which is what the storefront rendered — none were
 * overridden in settings_data.json.
 *
 * @param string $style 'full' (icon, title, subtitle) or 'compact'.
 * @param string $align 'left' or 'center'.
 */
function medzuro_seller_badge( $style = 'full', $align = 'left' ) {
	$title = apply_filters( 'medzuro_seller_badge_title', 'Authorized HolyOak Seller' );

	if ( ! $title ) {
		return;
	}

	$subtitle = apply_filters( 'medzuro_seller_badge_subtitle', 'Official distributor in Fiji' );
	?>
	<div class="mz-seller-badge mz-seller-badge--<?php echo esc_attr( $style ); ?> mz-seller-badge--<?php echo esc_attr( $align ); ?>">
		<span class="mz-seller-badge__icon"><?php medzuro_icon( 'shield', 28 ); ?></span>
		<div class="mz-seller-badge__text">
			<span class="mz-seller-badge__title"><?php echo esc_html( $title ); ?></span>
			<?php if ( 'full' === $style && $subtitle ) : ?>
				<span class="mz-seller-badge__subtitle"><?php echo esc_html( $subtitle ); ?></span>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Total saving across the cart, in store currency.
 *
 * Mirrors the Liquid loop over cart.items comparing compare_at_price to price.
 * WooCommerce's equivalent is regular price against the price actually charged.
 *
 * @return float
 */
function medzuro_cart_savings() {
	if ( ! WC()->cart ) {
		return 0.0;
	}

	$savings = 0.0;

	foreach ( WC()->cart->get_cart() as $item ) {
		if ( empty( $item['data'] ) ) {
			continue;
		}

		$product = $item['data'];
		$regular = (float) $product->get_regular_price();
		$price   = (float) $product->get_price();

		if ( $regular > $price ) {
			$savings += ( $regular - $price ) * (int) $item['quantity'];
		}
	}

	return $savings;
}

/**
 * Render the cart reassurance block.
 */
function medzuro_cart_extras() {
	get_template_part( 'template-parts/cart-extras' );
}
add_action( 'woocommerce_cart_collaterals', 'medzuro_cart_extras', 5 );

/**
 * Seller badge on checkout.
 *
 * The badge snippet documented five placements: homepage, product page,
 * cart/checkout, footer and about. Checkout is Woo's own template, so the
 * badge is hooked above the order review rather than templated in.
 */
function medzuro_checkout_badge() {
	medzuro_seller_badge( 'compact' );
}
add_action( 'woocommerce_checkout_before_order_review', 'medzuro_checkout_badge', 5 );
