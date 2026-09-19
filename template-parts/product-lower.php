<?php
/**
 * Product information below the purchase area.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
	return;
}

$description = $product->get_description();
?>

<section class="mz-pdp-details" aria-label="<?php esc_attr_e( 'Product information', 'medzuro' ); ?>">
	<div class="mz-pdp-shell">
		<?php if ( $description ) : ?>
			<div class="mz-pdp-description">
				<p class="mz-pdp-kicker"><?php esc_html_e( 'Product details', 'medzuro' ); ?></p>
				<h2><?php esc_html_e( 'About this product', 'medzuro' ); ?></h2>
				<div class="rte"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			</div>
		<?php endif; ?>

		<div class="mz-pdp-info-grid">
			<article>
				<span class="mz-pdp-info-icon"><?php medzuro_icon( 'shield', 26 ); ?></span>
				<h3><?php esc_html_e( 'Quality first', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'We source genuine wellness products and provide local support before and after your order.', 'medzuro' ); ?></p>
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'lab-test-and-purity' ) ) ); ?>"><?php esc_html_e( 'Quality and purity', 'medzuro' ); ?></a>
			</article>
			<article>
				<span class="mz-pdp-info-icon"><?php medzuro_icon( 'truck', 26 ); ?></span>
				<h3><?php esc_html_e( 'Fiji delivery', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Delivery is available across Fiji, with local pickup available by arrangement in Suva.', 'medzuro' ); ?></p>
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'delivery' ) ) ); ?>"><?php esc_html_e( 'Delivery information', 'medzuro' ); ?></a>
			</article>
			<article>
				<span class="mz-pdp-info-icon"><?php medzuro_icon( 'headset', 26 ); ?></span>
				<h3><?php esc_html_e( 'Need help?', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Our Fiji team can help with availability, pickup, delivery, and product questions.', 'medzuro' ); ?></p>
				<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>"><?php esc_html_e( 'Contact our team', 'medzuro' ); ?></a>
			</article>
		</div>

		<?php if ( $product->has_attributes() || $product->has_weight() || $product->has_dimensions() ) : ?>
			<div class="mz-pdp-specifications">
				<h2><?php esc_html_e( 'Product information', 'medzuro' ); ?></h2>
				<?php wc_display_product_attributes( $product ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>
