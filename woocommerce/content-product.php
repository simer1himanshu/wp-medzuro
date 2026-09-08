<?php
/**
 * Product card.
 *
 * Ported from the grid loop in sections/medzuro-collection-page.liquid.
 *
 * Shopify branched on variant count: more than one variant linked through to
 * the product page ("Choose Options"), a single variant posted straight to the
 * cart. WooCommerce draws the same distinction, so the card asks the product
 * whether it can be purchased in one click rather than counting variations —
 * that also covers grouped and external products, which Shopify had no notion
 * of.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! $product->is_visible() ) {
	return;
}

$permalink = $product->get_permalink();
$on_sale   = $product->is_on_sale();
$brand     = medzuro_product_brand( $product );
?>
<article class="mz-product-card">
	<a class="mz-product-card__media" href="<?php echo esc_url( $permalink ); ?>"
		aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php
			echo wp_get_attachment_image(
				get_post_thumbnail_id(),
				'woocommerce_thumbnail',
				false,
				array(
					'loading' => 'lazy',
					'sizes'   => '(min-width: 990px) 25vw, (min-width: 640px) 33vw, 50vw',
				)
			);
			?>
		<?php else : ?>
			<span class="mz-product-card__placeholder">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-logo.png' ); ?>"
					alt="" loading="lazy" width="220" height="110">
			</span>
		<?php endif; ?>

		<?php if ( $on_sale ) : ?>
			<span class="mz-product-card__sale"><?php esc_html_e( 'Sale', 'medzuro' ); ?></span>
		<?php endif; ?>
	</a>

	<div class="mz-product-card__body">
		<?php if ( $brand ) : ?>
			<p class="mz-product-card__vendor"><?php echo esc_html( $brand ); ?></p>
		<?php endif; ?>

		<h3><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>

		<div class="mz-product-card__price">
			<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</div>

		<?php if ( ! $product->is_in_stock() ) : ?>
			<a class="mz-product-card__button mz-product-card__button--disabled" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Sold Out', 'medzuro' ); ?>
			</a>

		<?php elseif ( $product->is_type( 'simple' ) && $product->is_purchasable() ) : ?>
			<form class="mz-product-card__form" method="post"
				action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $permalink ) ); ?>">
				<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
				<input type="hidden" name="quantity" value="1">
				<button class="mz-product-card__button" type="submit">
					<?php esc_html_e( 'Add To Cart', 'medzuro' ); ?>
				</button>
			</form>

		<?php else : ?>
			<a class="mz-product-card__button" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Choose Options', 'medzuro' ); ?>
			</a>
		<?php endif; ?>
	</div>
</article>
