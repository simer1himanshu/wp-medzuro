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
$rating    = (float) $product->get_average_rating();
$reviews   = (int) $product->get_review_count();
$coming_soon = medzuro_is_coming_soon( $product );
$regular   = (float) $product->get_regular_price();
$sale      = (float) $product->get_sale_price();
$discount  = ( $on_sale && $regular > 0 && $sale > 0 ) ? (int) round( ( ( $regular - $sale ) / $regular ) * 100 ) : 0;
$descriptor = medzuro_field( 'serving', '', $product->get_id() );

if ( ! $descriptor ) {
	$descriptor = wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 7, '' );
}
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
				<img src="<?php echo esc_url( medzuro_logo_url() ); ?>"
					alt="" loading="lazy" width="220" height="110">
			</span>
		<?php endif; ?>

	</a>

	<div class="mz-product-card__body">
		<h3><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>

		<?php if ( $descriptor ) : ?>
			<p class="mz-product-card__meta"><?php echo esc_html( $descriptor ); ?></p>
		<?php endif; ?>

		<?php if ( ! $coming_soon ) : ?>
			<div class="mz-product-card__price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
				<?php if ( $discount > 0 ) : ?>
					<small class="mz-product-card__discount"><?php echo esc_html( $discount ); ?>% off</small>
				<?php endif; ?>
			</div>

			<?php if ( $rating > 0 ) : ?>
				<div class="mz-product-card__rating" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %s out of 5', 'medzuro' ), $rating ) ); ?>">
					<?php echo wp_kses_post( wc_get_rating_html( $rating, $reviews ) ); ?>
					<small><?php echo esc_html( sprintf( _n( '%d review', '%d reviews', $reviews, 'medzuro' ), $reviews ) ); ?></small>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $coming_soon ) : ?>
			<a class="mz-product-card__button mz-product-card__button--coming-soon" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Coming Soon', 'medzuro' ); ?>
			</a>

		<?php elseif ( ! $product->is_in_stock() ) : ?>
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
