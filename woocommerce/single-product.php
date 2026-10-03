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
	$mz_desc      = medzuro_pdp_split_description( $short_desc );
	$mz_cur       = apply_filters( 'medzuro_pdp_currency_prefix', 'FJD ' );
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
			<span aria-current="page"><?php echo esc_html( $product->get_name() ); ?></span>
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
						<?php /* The design shows the discount beside the price, not on the image. */ ?>

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
			</section>

			<section class="mz-pdp-buybox" aria-labelledby="mz-product-title">
				<p class="mz-pdp-brand"><?php echo esc_html( medzuro_pdp_brand( $product ) ); ?></p>
				<h1 id="mz-product-title"><?php echo esc_html( $product->get_name() ); ?></h1>

				<div class="mz-pdp-meta-row">
					<?php if ( $review_count > 0 ) : ?>
						<div class="mz-pdp-rating">
							<span class="mz-pdp-stars" style="--mz-rating: <?php echo esc_attr( round( $rating / 5 * 100 ) ); ?>%"
								role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %1$s out of 5 from %2$d reviews', 'medzuro' ), number_format_i18n( $rating, 1 ), $review_count ) ); ?>"></span>
							<strong><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></strong>
						</div>
					<?php endif; ?>
					<?php if ( $mz_desc['tagline'] ) : ?>
						<p class="mz-pdp-tagline"><?php echo esc_html( $mz_desc['tagline'] ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $mz_desc['body'] ) : ?>
					<div class="mz-pdp-desc"><?php echo wp_kses_post( wpautop( $mz_desc['body'] ) ); ?></div>
				<?php endif; ?>

				<?php if ( ! $coming_soon && ! $in_stock ) : ?>
					<p class="mz-pdp-availability"><span class="is-out-of-stock" data-mz-pdp-stock data-in="<?php esc_attr_e( 'In Stock', 'medzuro' ); ?>" data-out="<?php esc_attr_e( 'Out of Stock', 'medzuro' ); ?>"><?php esc_html_e( 'Out of Stock', 'medzuro' ); ?></span></p>
				<?php endif; ?>

				<?php if ( $coming_soon ) : ?>

					<div class="mz-pdp-coming-soon">
						<strong><?php esc_html_e( 'Coming Soon', 'medzuro' ); ?></strong>
						<span><?php esc_html_e( 'This product is not available to purchase yet.', 'medzuro' ); ?></span>
					</div>
				<?php else : ?>
					<div class="mz-pdp-price">
						<strong><span class="mz-pdp-cur"><?php echo esc_html( $mz_cur ); ?></span><span data-mz-pdp-price><?php echo esc_html( $price['price'] ); ?></span></strong>
						<s <?php echo $price['discount'] > 0 ? '' : 'hidden'; ?> data-mz-pdp-compare-wrap><span class="mz-pdp-cur"><?php echo esc_html( $mz_cur ); ?></span><span data-mz-pdp-compare><?php echo esc_html( $price['compare'] ); ?></span></s>
						<span class="mz-pdp-off" data-mz-pdp-save-line <?php echo $price['discount'] > 0 ? '' : 'hidden'; ?>><span data-mz-pdp-discount><?php echo esc_html( $price['discount'] ); ?></span>% <?php esc_html_e( 'OFF', 'medzuro' ); ?></span>
					</div>
					<p class="mz-pdp-save" data-mz-pdp-save-pill <?php echo $price['discount'] > 0 ? '' : 'hidden'; ?>>
						<?php esc_html_e( 'You save', 'medzuro' ); ?> <?php echo esc_html( $mz_cur ); ?><span data-mz-pdp-save><?php echo esc_html( $price['save'] ); ?></span>
					</p>

					<ul class="mz-pdp-trust">
						<li><span class="mz-pdp-trust__icon"><?php medzuro_icon( 'flask', 22 ); ?></span><span><strong><?php esc_html_e( 'Lab-tested', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Purity you can trust', 'medzuro' ); ?></small></span></li>
						<li><span class="mz-pdp-trust__icon"><?php medzuro_icon( 'shield', 22 ); ?></span><span><strong><?php esc_html_e( 'Quality checked', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Premium standards', 'medzuro' ); ?></small></span></li>
						<li><span class="mz-pdp-trust__icon"><?php medzuro_icon( 'lock', 22 ); ?></span><span><strong><?php esc_html_e( 'Secure checkout', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Your information is safe', 'medzuro' ); ?></small></span></li>
					</ul>

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

					<?php if ( medzuro_bundle_offer_enabled() ) : ?>
						<div class="mz-pdp-promo">
							<span class="mz-pdp-promo__icon"><?php medzuro_icon( 'gift', 56 ); ?></span>
							<span class="mz-pdp-promo__text">
								<strong><?php esc_html_e( 'Buy any 2', 'medzuro' ); ?></strong>
								<em>
									<?php
									/* translators: %d: percent */
									printf( esc_html__( '& GET %d%%', 'medzuro' ), (int) round( medzuro_bundle_offer_rate() * 100 ) );
									?>
								</em>
								<span><?php esc_html_e( 'Additional discount', 'medzuro' ); ?></span>
							</span>
						</div>
					<?php endif; ?>

					<div class="mz-pdp-delivery">
						<span class="mz-pdp-delivery__icon"><?php medzuro_icon( 'truck', 34 ); ?></span>
						<span><strong><?php esc_html_e( 'Fast delivery across Fiji', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Reliable shipping with DHL', 'medzuro' ); ?></small></span>
						<?php medzuro_dhl_badge( 'lg' ); ?>
					</div>
				<?php endif; ?>
			</section>
		</div>

		<?php $mz_features = medzuro_pdp_features( $product ); ?>
		<?php if ( $mz_features ) : ?>
			<ul class="mz-pdp-features">
				<?php foreach ( $mz_features as $mz_f ) : ?>
					<li>
						<span class="mz-pdp-features__icon"><?php medzuro_icon( $mz_f[0], 30 ); ?></span>
						<span><strong><?php echo esc_html( $mz_f[1] ); ?></strong><small><?php echo esc_html( $mz_f[2] ); ?></small></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php get_template_part( 'template-parts/product-lower' ); ?>
</section>

	<?php
endwhile;

get_footer();
