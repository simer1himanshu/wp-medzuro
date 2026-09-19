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
$title       = $term ? single_term_title( '', false ) : __( 'Shop Wellness', 'medzuro' );
$description = $term ? term_description( $term ) : '';
?>
<div class="mz-collection-hero">
	<div class="mz-collection-shell mz-collection-hero__inner">
		<div class="mz-collection-hero__copy">
			<p class="mz-collection-eyebrow">Genuine. Trusted. Local.</p>
			<h1><?php echo esc_html( $title ); ?></h1>

			<?php if ( $description ) : ?>
				<div class="mz-collection-hero__description rte"><?php echo wp_kses_post( $description ); ?></div>
			<?php else : ?>
				<p class="mz-collection-hero__description">
					Genuine wellness essentials, selected for Fiji and backed by friendly local support.
				</p>
			<?php endif; ?>

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
