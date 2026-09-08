<?php
/**
 * Collection page hero.
 *
 * Ported from sections/medzuro-collection-page.liquid. Shopify's
 * collection.title / collection.description become the WooCommerce archive
 * title and the product category term description.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$term        = is_product_category() || is_product_tag() ? get_queried_object() : null;
$title       = $term ? single_term_title( '', false ) : __( 'Products', 'medzuro' );
$description = $term ? term_description( $term ) : '';
?>
<div class="mz-collection-hero">
	<div class="mz-collection-shell mz-collection-hero__inner">
		<div class="mz-collection-hero__copy">
			<p class="mz-collection-eyebrow">Medzuro Retail</p>
			<h1><?php echo esc_html( $title ); ?></h1>

			<?php if ( $description ) : ?>
				<div class="mz-collection-hero__description rte"><?php echo wp_kses_post( $description ); ?></div>
			<?php else : ?>
				<p class="mz-collection-hero__description">
					Genuine wellness essentials, selected for Fiji and backed by friendly local support.
				</p>
			<?php endif; ?>

			<div class="mz-collection-hero__actions">
				<a class="mz-collection-btn mz-collection-btn--primary" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>">
					<span aria-hidden="true">
						<svg viewBox="0 0 24 24" role="img"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
					</span>
					Shop All
				</a>
				<a class="mz-collection-btn mz-collection-btn--secondary" href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>">
					<span aria-hidden="true">
						<svg viewBox="0 0 24 24" role="img"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
					</span>
					Ask Support
				</a>
			</div>
		</div>

		<div class="mz-collection-hero__media" aria-hidden="true">
			<img
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-home-hero.png' ); ?>"
				alt=""
				width="880"
				height="620"
				loading="eager">
		</div>
	</div>
</div>
