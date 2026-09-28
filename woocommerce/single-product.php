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

	$rating       = (float) $product->get_average_rating();
	$review_count = (int) $product->get_review_count();
	$short_desc   = $product->get_short_description();
	$coming_soon  = medzuro_is_coming_soon( $product );
	$in_stock     = $product->is_in_stock();
	$cards        = $coming_soon ? null : medzuro_pdp_variation_cards( $product );
	$image_ids    = array_values(
		array_unique(
			array_filter(
				array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() )
			)
		)
	);

	// The price block starts on the preselected card, so the page shows the
	// same option the form will submit.
	$price = medzuro_pdp_price( $product );
	if ( $cards ) {
		foreach ( $cards['cards'] as $card ) {
			if ( $card['value'] === $cards['selected'] ) {
				$price    = $card;
				$in_stock = $card['available'];
			}
		}
	}
	?>

<section class="mz-pdp" data-mz-pdp>
	<nav class="mz-pdp-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'medzuro' ); ?>">
		<div class="mz-pdp-shell">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'medzuro' ); ?></a>
			<span class="mz-pdp-breadcrumb__sep" aria-hidden="true"></span>
			<span aria-current="page"><?php the_title(); ?></span>
		</div>
	</nav>

	<div class="mz-pdp-shell">
		<div class="mz-pdp-top">
			<section class="mz-pdp-gallery-wrap" aria-label="<?php esc_attr_e( 'Product gallery', 'medzuro' ); ?>">
				<div class="mz-pdp-gallery<?php echo count( $image_ids ) < 2 ? ' mz-pdp-gallery--single' : ''; ?>">
					<?php if ( count( $image_ids ) > 1 ) : ?>
						<div class="mz-pdp-thumbs-wrap">
							<button class="mz-pdp-thumbs-nav mz-pdp-thumbs-nav--prev" type="button" data-mz-pdp-thumbs-prev
								aria-label="<?php esc_attr_e( 'Scroll thumbnails up', 'medzuro' ); ?>"><?php medzuro_icon( 'chev', 20 ); ?></button>
							<div class="mz-pdp-thumbs" data-mz-pdp-thumbs>
								<?php foreach ( $image_ids as $index => $attachment_id ) : ?>
									<?php
									$full = wp_get_attachment_image_url( $attachment_id, 'woocommerce_single' );
									$alt  = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ?: $product->get_name();
									?>
									<button class="mz-pdp-thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button"
										data-mz-pdp-thumb data-image="<?php echo esc_url( $full ); ?>" data-alt="<?php echo esc_attr( $alt ); ?>"
										aria-label="<?php echo esc_attr( sprintf( __( 'View image %d', 'medzuro' ), $index + 1 ) ); ?>"
										<?php echo 0 === $index ? 'aria-current="true"' : ''; ?>>
										<?php echo wp_get_attachment_image( $attachment_id, 'medium', false, array( 'loading' => 'lazy' ) ); ?>
									</button>
								<?php endforeach; ?>
							</div>
							<button class="mz-pdp-thumbs-nav mz-pdp-thumbs-nav--next" type="button" data-mz-pdp-thumbs-next
								aria-label="<?php esc_attr_e( 'Scroll thumbnails down', 'medzuro' ); ?>"><?php medzuro_icon( 'chev', 20 ); ?></button>
						</div>
					<?php endif; ?>

					<div class="mz-pdp-main-media<?php echo $image_ids ? '' : ' mz-pdp-main-media--empty'; ?>">
						<?php if ( ! $coming_soon && $price['discount'] > 0 ) : ?>
							<span class="mz-pdp-badge" data-mz-pdp-badge>-<?php echo esc_html( $price['discount'] ); ?>%</span>
						<?php endif; ?>

						<?php if ( $image_ids ) : ?>
							<?php
							echo wp_get_attachment_image(
								$image_ids[0],
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

						<?php if ( count( $image_ids ) > 1 ) : ?>
							<button class="mz-pdp-arrow mz-pdp-arrow--prev" type="button" data-mz-pdp-prev
								aria-label="<?php esc_attr_e( 'Previous image', 'medzuro' ); ?>"><?php medzuro_icon( 'chev', 22 ); ?></button>
							<button class="mz-pdp-arrow mz-pdp-arrow--next" type="button" data-mz-pdp-next
								aria-label="<?php esc_attr_e( 'Next image', 'medzuro' ); ?>"><?php medzuro_icon( 'chev', 22 ); ?></button>
						<?php endif; ?>
					</div>
				</div>

				<?php get_template_part( 'template-parts/product-trust-row' ); ?>
			</section>

			<section class="mz-pdp-buybox" aria-labelledby="mz-product-title">
				<div class="mz-pdp-title-row">
					<h1 id="mz-product-title"><?php the_title(); ?></h1>
					<button class="mz-pdp-share" type="button" data-mz-pdp-share
						data-title="<?php echo esc_attr( $product->get_name() ); ?>" data-url="<?php echo esc_url( get_permalink() ); ?>"
						aria-label="<?php esc_attr_e( 'Share this product', 'medzuro' ); ?>">
						<?php medzuro_icon( 'share', 20 ); ?>
						<span class="mz-pdp-share__done" data-mz-pdp-share-done hidden><?php esc_html_e( 'Link copied', 'medzuro' ); ?></span>
					</button>
				</div>

				<div class="mz-pdp-meta-row">
					<?php if ( $review_count > 0 ) : ?>
						<div class="mz-pdp-rating">
							<span class="mz-pdp-stars" style="--mz-rating: <?php echo esc_attr( round( $rating / 5 * 100 ) ); ?>%"
								role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %s out of 5', 'medzuro' ), number_format_i18n( $rating, 1 ) ) ); ?>"></span>
							<span><?php echo esc_html( sprintf( _n( '%d review', '%d reviews', $review_count, 'medzuro' ), $review_count ) ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! $coming_soon ) : ?>
						<p class="mz-pdp-availability">
							<?php esc_html_e( 'Availability:', 'medzuro' ); ?>
							<span class="<?php echo $in_stock ? 'is-in-stock' : 'is-out-of-stock'; ?>" data-mz-pdp-stock
								data-in="<?php esc_attr_e( 'In Stock', 'medzuro' ); ?>" data-out="<?php esc_attr_e( 'Out of Stock', 'medzuro' ); ?>">
								<?php $in_stock ? esc_html_e( 'In Stock', 'medzuro' ) : esc_html_e( 'Out of Stock', 'medzuro' ); ?>
							</span>
						</p>
					<?php endif; ?>
				</div>

				<?php if ( $coming_soon ) : ?>
					<?php if ( $short_desc ) : ?>
						<div class="mz-pdp-subtitle"><?php echo wp_kses_post( wpautop( $short_desc ) ); ?></div>
					<?php endif; ?>

					<div class="mz-pdp-coming-soon">
						<strong><?php esc_html_e( 'Coming Soon', 'medzuro' ); ?></strong>
						<span><?php esc_html_e( 'This product is not available to purchase yet.', 'medzuro' ); ?></span>
					</div>
				<?php else : ?>
					<div class="mz-pdp-price">
						<strong data-mz-pdp-price><?php echo esc_html( $price['price'] ); ?></strong>
						<s data-mz-pdp-compare <?php echo $price['discount'] > 0 ? '' : 'hidden'; ?>><?php echo esc_html( $price['compare'] ); ?></s>
					</div>
					<p class="mz-pdp-save" data-mz-pdp-save-line <?php echo $price['discount'] > 0 ? '' : 'hidden'; ?>>
						<?php esc_html_e( 'Save', 'medzuro' ); ?> <span data-mz-pdp-save><?php echo esc_html( $price['save'] ); ?></span>
						<em>(<span data-mz-pdp-discount><?php echo esc_html( $price['discount'] ); ?></span>% <?php esc_html_e( 'off', 'medzuro' ); ?>)</em>
					</p>
					<p class="mz-pdp-shipping"><?php esc_html_e( 'Shipping calculated at checkout.', 'medzuro' ); ?></p>

					<?php if ( $short_desc ) : ?>
						<div class="mz-pdp-subtitle"><?php echo wp_kses_post( wpautop( $short_desc ) ); ?></div>
					<?php endif; ?>

					<?php if ( $cards ) : ?>
						<fieldset class="mz-pdp-options">
							<legend><?php echo esc_html( $cards['label'] ); ?></legend>
							<div class="mz-pdp-options__grid">
								<?php foreach ( $cards['cards'] as $card ) : ?>
									<label class="mz-pdp-option<?php echo $card['value'] === $cards['selected'] ? ' is-selected' : ''; ?><?php echo $card['available'] ? '' : ' is-unavailable'; ?>">
										<input type="radio" name="mz_pdp_option" value="<?php echo esc_attr( $card['value'] ); ?>"
											data-mz-pdp-option data-attribute="<?php echo esc_attr( $cards['attribute'] ); ?>"
											data-price="<?php echo esc_attr( $card['price'] ); ?>"
											data-compare="<?php echo esc_attr( $card['compare'] ); ?>"
											data-save="<?php echo esc_attr( $card['save'] ); ?>"
											data-discount="<?php echo esc_attr( $card['discount'] ); ?>"
											data-available="<?php echo $card['available'] ? 'true' : 'false'; ?>"
											<?php checked( $card['value'], $cards['selected'] ); ?>>
										<span class="mz-pdp-option__title"><?php echo esc_html( $card['title'] ); ?></span>
										<span class="mz-pdp-option__price"><?php echo esc_html( $card['price'] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</fieldset>
					<?php endif; ?>

					<?php if ( defined( 'YITH_WCWL' ) ) : ?>
						<div class="mz-pdp-wishlist"><?php echo do_shortcode( '[yith_wcwl_add_to_wishlist]' ); ?></div>
					<?php endif; ?>

					<div class="mz-pdp-purchase<?php echo $cards ? ' mz-pdp-purchase--cards' : ''; ?>">
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
