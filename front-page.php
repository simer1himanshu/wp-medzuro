<?php
/**
 * Homepage.
 *
 * Ported from sections/medzuro-reference-home.liquid. The Shopify section was
 * block-driven; copy and blocks come from inc/home-content.php, generated from
 * templates/index.json.
 *
 * The product carousel pulls from the WooCommerce category named by the
 * `collection` setting, falling back to five demo cards while the catalogue is
 * empty — the same fallback the Liquid carried.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();

$s = medzuro_home()['settings'];
?>

<div class="mz-home-ref">

	<section class="mz-home-hero" aria-labelledby="mz-home-title">
		<div class="mz-home-hero__photo" aria-hidden="true"></div>
		<div class="page-width mz-home-hero__wrap">
			<div class="mz-home-hero__copy">
				<p class="mz-home-kicker"><?php echo esc_html( $s['hero_kicker'] ); ?></p>
				<h1 id="mz-home-title" class="mz-home-hero__title">
					<?php echo esc_html( $s['hero_heading'] ); ?> <span><?php echo esc_html( $s['hero_accent'] ); ?></span>
				</h1>
				<p class="mz-home-hero__text"><?php echo esc_html( $s['hero_text'] ); ?></p>
				<div class="mz-home-hero__actions">
					<a class="mz-home-btn mz-home-btn--solid" href="<?php echo esc_url( medzuro_home_url( $s['primary_url'] ) ); ?>">
						<?php echo esc_html( $s['primary_label'] ); ?>
					</a>
					<a class="mz-home-btn mz-home-btn--outline" href="<?php echo esc_url( medzuro_home_url( $s['secondary_url'] ) ); ?>">
						<?php medzuro_ref_icon( 'pin' ); ?>
						<span><?php echo esc_html( $s['secondary_label'] ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</section>

	<?php $trust = medzuro_home_blocks( 'trust' ); ?>
	<?php if ( $trust ) : ?>
		<div class="page-width mz-home-trust">
			<ul class="mz-home-trust__list" role="list">
				<?php foreach ( $trust as $block ) : ?>
					<li>
						<?php medzuro_ref_icon( $block['icon'] ); ?>
						<span><strong><?php echo esc_html( $block['title'] ); ?></strong><small><?php echo esc_html( $block['text'] ); ?></small></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<section class="page-width mz-home-products" aria-labelledby="mz-home-products-title">
		<div class="mz-home-section-title">
			<h2 id="mz-home-products-title"><?php echo esc_html( $s['products_title'] ); ?></h2>
		</div>

		<button class="mz-home-arrow mz-home-arrow--prev" type="button"
			aria-label="<?php esc_attr_e( 'Previous products', 'medzuro' ); ?>" data-mz-home-prev>
			<span aria-hidden="true">&#8249;</span>
		</button>

		<div class="mz-home-products__viewport" data-mz-home-products>
			<ul class="mz-home-products__track" role="list">
				<?php
				$products = medzuro_home_products();

				if ( $products ) :
					foreach ( $products as $i => $product ) :
						$on_sale  = $product->is_on_sale();
						$regular  = (float) $product->get_regular_price();
						$price    = (float) $product->get_price();
						$discount = ( $on_sale && $regular > 0 ) ? (int) round( ( ( $regular - $price ) / $regular ) * 100 ) : 0;
						// Shopify derived a plausible-looking count from the loop
						// index; kept so the design matches until real reviews exist.
						$reviews  = ( ( $i + 1 ) * 16 ) + 48;
						$link     = $product->get_permalink();
						?>
						<li class="mz-home-product">
							<a class="mz-home-product__media" href="<?php echo esc_url( $link ); ?>">
								<?php if ( $discount > 0 ) : ?>
									<span class="mz-home-sale"><?php echo esc_html( $discount ); ?>% off</span>
								<?php endif; ?>

								<?php if ( $product->get_image_id() ) : ?>
									<?php
									echo wp_get_attachment_image(
										$product->get_image_id(),
										'woocommerce_thumbnail',
										false,
										array(
											'class'   => 'mz-home-product__img',
											'loading' => 'lazy',
											'sizes'   => '(max-width: 767px) 46vw, 18vw',
										)
									);
									?>
								<?php else : ?>
									<span class="mz-home-bottle mz-home-bottle--1" aria-hidden="true"><span></span></span>
								<?php endif; ?>
							</a>

							<div class="mz-home-product__body">
								<a class="mz-home-product__title" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
								<span class="mz-home-product__meta">
									<?php echo esc_html( medzuro_field( 'serving', $s['product_meta'], $product->get_id() ) ); ?>
								</span>

								<?php if ( $s['show_ratings'] ) : ?>
									<span class="mz-home-rating" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'medzuro' ); ?>">
										<span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span><small>(<?php echo esc_html( $reviews ); ?>)</small>
									</span>
								<?php endif; ?>

								<span class="mz-home-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>

								<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
									<form method="post" class="mz-home-product__form"
										action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $link ) ); ?>">
										<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
										<input type="hidden" name="quantity" value="1">
										<button class="mz-home-add" type="submit"><?php echo esc_html( $s['add_label'] ); ?></button>
									</form>
								<?php elseif ( ! $product->is_in_stock() ) : ?>
									<button class="mz-home-add" type="button" disabled><?php echo esc_html( $s['soldout_label'] ); ?></button>
								<?php else : ?>
									<a class="mz-home-add" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $s['add_label'] ); ?></a>
								<?php endif; ?>
							</div>
						</li>
						<?php
					endforeach;
				else :
					get_template_part( 'template-parts/home-fallback' );
				endif;
				?>
			</ul>
		</div>

		<button class="mz-home-arrow mz-home-arrow--next" type="button"
			aria-label="<?php esc_attr_e( 'Next products', 'medzuro' ); ?>" data-mz-home-next>
			<span aria-hidden="true">&#8250;</span>
		</button>
	</section>

	<?php
	$stats = medzuro_home_blocks( 'stat' );
	$flags = medzuro_home_blocks( 'flag' );

	if ( $s['show_world'] ) :
		?>
		<section class="page-width mz-home-world" aria-label="<?php echo esc_attr( $s['world_kicker'] ); ?>">
			<div class="mz-home-world__panel">
				<div class="mz-home-world__copy">
					<p class="mz-home-kicker"><?php echo esc_html( $s['world_kicker'] ); ?></p>
					<h2><?php echo esc_html( $s['world_heading'] ); ?><br><?php echo esc_html( $s['world_heading_2'] ); ?> <span><?php echo esc_html( $s['world_accent'] ); ?></span></h2>
				</div>

				<?php if ( $stats ) : ?>
					<div class="mz-home-world__stats" role="list">
						<?php foreach ( $stats as $block ) : ?>
							<div role="listitem">
								<?php medzuro_ref_icon( $block['icon'] ); ?>
								<strong><?php echo esc_html( $block['value'] ); ?></strong><small><?php echo esc_html( $block['label'] ); ?></small>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $flags ) : ?>
					<ul class="mz-home-flags" role="list" aria-label="<?php esc_attr_e( 'International regions', 'medzuro' ); ?>">
						<?php foreach ( $flags as $block ) : ?>
							<li>
								<span class="mz-flag mz-flag--<?php echo esc_attr( $block['flag'] ); ?>"></span><small><?php echo esc_html( $block['label'] ); ?></small>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$checks = medzuro_home_blocks( 'lab_check' );

	if ( $s['show_lab'] ) :
		?>
		<section class="page-width mz-home-lab" aria-labelledby="mz-home-lab-title">
			<div class="mz-home-lab__panel">
				<div class="mz-home-lab__image" aria-hidden="true"></div>
				<div class="mz-home-lab__copy">
					<p class="mz-home-kicker"><?php echo esc_html( $s['lab_kicker'] ); ?></p>
					<h2 id="mz-home-lab-title"><?php echo esc_html( $s['lab_heading'] ); ?> <span><?php echo esc_html( $s['lab_accent'] ); ?></span></h2>
					<p><?php echo esc_html( $s['lab_text'] ); ?></p>

					<?php if ( $s['lab_brand'] ) : ?>
						<div class="mz-home-eurofins" aria-label="<?php echo esc_attr( $s['lab_brand'] ); ?>">
							<span class="mz-home-eurofins__mark" aria-hidden="true"></span>
							<strong><?php echo esc_html( $s['lab_brand'] ); ?></strong>
							<small><?php echo esc_html( $s['lab_brand_tagline'] ); ?></small>
						</div>
					<?php endif; ?>

					<?php if ( $checks ) : ?>
						<ul class="mz-home-lab__checks" role="list">
							<?php foreach ( $checks as $block ) : ?>
								<li>
									<?php medzuro_ref_icon( $block['icon'] ); ?>
									<span><strong><?php echo esc_html( $block['title'] ); ?></strong><small><?php echo esc_html( $block['text'] ); ?></small></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $s['lab_link_label'] ) : ?>
						<a class="mz-home-lab__link" href="<?php echo esc_url( medzuro_home_url( $s['lab_url'] ) ); ?>">
							<?php echo esc_html( $s['lab_link_label'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php $reviews = medzuro_home_blocks( 'review' ); ?>
	<?php if ( $reviews ) : ?>
		<section class="page-width mz-home-reviews" aria-labelledby="mz-home-reviews-title">
			<div class="mz-home-section-title">
				<h2 id="mz-home-reviews-title"><?php echo esc_html( $s['reviews_title'] ); ?></h2>
			</div>

			<button class="mz-home-arrow mz-home-arrow--prev mz-home-arrow--reviews-prev" type="button"
				aria-label="<?php esc_attr_e( 'Previous reviews', 'medzuro' ); ?>" data-mz-reviews-prev>
				<span aria-hidden="true">&#8249;</span>
			</button>

			<div class="mz-home-reviews__viewport" data-mz-reviews>
				<ul class="mz-home-reviews__track" role="list">
					<?php
					foreach ( $reviews as $block ) :
						$stars = (int) ( $block['rating'] ?? 5 );
						?>
						<li class="mz-home-review">
							<span class="mz-home-review__quote" aria-hidden="true">"</span>
							<span class="mz-home-rating"
								aria-label="<?php echo esc_attr( sprintf( '%d out of 5 stars', $stars ) ); ?>">
								<span aria-hidden="true"><?php echo esc_html( str_repeat( "\u{2605}", $stars ) ); ?></span>
							</span>
							<p><?php echo esc_html( $block['text'] ); ?></p>
							<div class="mz-home-review__person">
								<span class="mz-home-avatar mz-home-avatar--<?php echo esc_attr( $block['avatar'] ); ?>"></span>
								<strong><?php echo esc_html( $block['author'] ); ?><small><?php echo esc_html( $block['location'] ); ?></small></strong>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<button class="mz-home-arrow mz-home-arrow--next mz-home-arrow--reviews-next" type="button"
				aria-label="<?php esc_attr_e( 'Next reviews', 'medzuro' ); ?>" data-mz-reviews-next>
				<span aria-hidden="true">&#8250;</span>
			</button>
		</section>
	<?php endif; ?>

	<?php $help = medzuro_home_blocks( 'help' ); ?>
	<?php if ( $help ) : ?>
		<div class="page-width mz-home-help">
			<ul class="mz-home-help__list" role="list">
				<?php foreach ( $help as $block ) : ?>
					<li>
						<?php medzuro_ref_icon( $block['icon'] ); ?>
						<span>
							<strong><?php echo esc_html( $block['title'] ); ?></strong>
							<small><?php echo esc_html( str_replace( '[phone]', $s['phone'], $block['text'] ) ); ?></small>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $s['show_newsletter'] ) : ?>
		<section class="page-width mz-home-newsletter" aria-label="<?php esc_attr_e( 'Subscribe to Medzuro updates', 'medzuro' ); ?>">
			<div class="mz-home-newsletter__panel">
				<div class="mz-home-newsletter__copy">
					<span class="mz-home-newsletter__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
					</span>
					<strong><?php echo esc_html( $s['newsletter_text'] ); ?></strong>
				</div>

				<?php
				/**
				 * Newsletter form.
				 *
				 * Shopify's {% form 'customer' %} posted to its customer
				 * endpoint. Hook a mail plugin's shortcode here; the markup
				 * below is inert until then.
				 */
				if ( has_action( 'medzuro_newsletter_form' ) ) {
					do_action( 'medzuro_newsletter_form' );
				} else {
					?>
					<form class="mz-home-newsletter__form" method="post" action="">
						<label class="v-hidden" for="mz-newsletter-email"><?php esc_html_e( 'Email address', 'medzuro' ); ?></label>
						<input id="mz-newsletter-email" type="email" name="email" autocomplete="email"
							placeholder="<?php echo esc_attr( $s['newsletter_placeholder'] ); ?>" required>
						<button type="submit"><?php echo esc_html( $s['newsletter_button'] ); ?></button>
					</form>
					<?php
				}
				?>
			</div>
		</section>
	<?php endif; ?>

</div>

<?php
get_footer();
