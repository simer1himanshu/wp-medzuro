<?php
/**
 * Single product template.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	global $product;
	$product = wc_get_product( get_the_ID() );

	if ( ! $product ) {
		continue;
	}

	$brand        = medzuro_product_brand( $product );
	$short_desc   = $product->get_short_description();
	$rating       = (float) $product->get_average_rating();
	$review_count = (int) $product->get_review_count();
	$coming_soon = medzuro_is_coming_soon( $product );
	$image_ids    = array_values(
		array_filter(
			array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() )
		)
	);
	$main_image = $image_ids[0] ?? 0;
	?>

<section class="mz-pdp" data-mz-pdp>
	<div class="mz-pdp-shell">
		<nav class="mz-pdp-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'medzuro' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'medzuro' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Shop', 'medzuro' ); ?></a>
			<span aria-hidden="true">/</span>
			<span aria-current="page"><?php the_title(); ?></span>
		</nav>

		<div class="mz-pdp-top">
			<section class="mz-pdp-gallery-wrap" aria-label="<?php esc_attr_e( 'Product gallery', 'medzuro' ); ?>">
				<div class="mz-pdp-gallery<?php echo count( $image_ids ) < 2 ? ' mz-pdp-gallery--single' : ''; ?>">
					<?php if ( count( $image_ids ) > 1 ) : ?>
						<div class="mz-pdp-thumbs" aria-label="<?php esc_attr_e( 'Product images', 'medzuro' ); ?>">
							<?php foreach ( array_slice( $image_ids, 0, 8 ) as $index => $attachment_id ) : ?>
								<?php
								$full = wp_get_attachment_image_url( $attachment_id, 'woocommerce_single' );
								$alt  = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ?: $product->get_name();
								?>
								<button class="mz-pdp-thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button"
									data-mz-pdp-thumb data-image="<?php echo esc_url( $full ); ?>" data-alt="<?php echo esc_attr( $alt ); ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'View image %d', 'medzuro' ), $index + 1 ) ); ?>">
									<?php echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="mz-pdp-main-media<?php echo $main_image ? '' : ' mz-pdp-main-media--empty'; ?>">
						<?php if ( $main_image ) : ?>
							<?php
							echo wp_get_attachment_image(
								$main_image,
								'woocommerce_single',
								false,
								array(
									'class'   => 'mz-pdp-main-image',
									'loading' => 'eager',
									'alt'     => $product->get_name(),
								)
							);
							?>
						<?php else : ?>
							<img class="mz-pdp-placeholder" src="<?php echo esc_url( medzuro_logo_url() ); ?>"
								alt="<?php echo esc_attr( $product->get_name() ); ?>" width="420" height="136">
							<p><?php esc_html_e( 'Product image coming soon', 'medzuro' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<?php get_template_part( 'template-parts/product-trust-row' ); ?>
			</section>

			<section class="mz-pdp-buybox" aria-labelledby="mz-product-title">
				<div class="mz-pdp-status-row">
					<?php if ( $brand ) : ?><span class="mz-pdp-brand"><?php echo esc_html( $brand ); ?></span><?php endif; ?>
					<?php echo wp_kses_post( wc_get_stock_html( $product ) ); ?>
				</div>

				<h1 id="mz-product-title"><?php the_title(); ?></h1>

				<?php if ( $rating > 0 ) : ?>
					<div class="mz-pdp-rating">
						<?php echo wp_kses_post( wc_get_rating_html( $rating, $review_count ) ); ?>
						<span><?php echo esc_html( sprintf( _n( '%d review', '%d reviews', $review_count, 'medzuro' ), $review_count ) ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $short_desc ) : ?>
					<div class="mz-pdp-subtitle"><?php echo wp_kses_post( wpautop( $short_desc ) ); ?></div>
				<?php endif; ?>

				<?php if ( $coming_soon ) : ?>
					<div class="mz-pdp-coming-soon">
						<strong><?php esc_html_e( 'Coming Soon', 'medzuro' ); ?></strong>
						<span><?php esc_html_e( 'This product is not available to purchase yet.', 'medzuro' ); ?></span>
					</div>
				<?php else : ?>
					<div class="mz-pdp-price"><?php woocommerce_template_single_price(); ?></div>

					<div class="mz-pdp-purchase">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>
				<?php endif; ?>

				<ul class="mz-pdp-assurances" role="list">
					<li><?php medzuro_icon( 'shield', 20 ); ?><span><strong><?php esc_html_e( 'Secure payment', 'medzuro' ); ?></strong><?php esc_html_e( 'Protected checkout with trusted payment methods.', 'medzuro' ); ?></span></li>
					<li><?php medzuro_icon( 'truck', 20 ); ?><span><strong><?php esc_html_e( 'Delivery across Fiji', 'medzuro' ); ?></strong><?php esc_html_e( 'Delivery timing is confirmed during fulfilment.', 'medzuro' ); ?></span></li>
					<li><?php medzuro_icon( 'pin', 20 ); ?><span><strong><?php esc_html_e( 'Local pickup', 'medzuro' ); ?></strong><?php esc_html_e( 'Pickup can be arranged from our Suva location.', 'medzuro' ); ?></span></li>
				</ul>

				<div class="mz-pdp-meta"><?php woocommerce_template_single_meta(); ?></div>
			</section>
		</div>
	</div>

	<?php get_template_part( 'template-parts/product-lower' ); ?>
</section>

	<?php
endwhile;

get_footer();
