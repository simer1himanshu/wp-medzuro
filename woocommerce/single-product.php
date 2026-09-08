<?php
/**
 * Single product template.
 *
 * Ported from sections/medzuro-product-page.liquid. The gallery, buybox and
 * pack selector are hand-written against WooCommerce; everything below the
 * buybox is generated (see template-parts/product-lower.php).
 *
 * Shopify's {% form 'product' %} became Woo's add-to-cart contract:
 * add-to-cart=<id>, quantity, and for variable products variation_id plus the
 * attribute_* fields, which the pack selector fills in via JS.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$product = wc_get_product( get_the_ID() );

	if ( ! $product ) {
		continue;
	}

	$packs     = medzuro_pdp_packs( $product );
	$matches   = array_filter( $packs, fn( $p ) => $p['selected'] );
	$selected  = $matches ? reset( $matches ) : ( $packs[0] ?? array() );
	$is_var    = $product->is_type( 'variable' );
	$subtitle  = medzuro_field( 'subtitle', 'Authentic wellness support for daily energy, strength, and performance.' );
	$serving   = medzuro_field( 'serving', 'Choose the supply that fits your routine' );
	$start_qty = $selected['quantity'] ?? 1;
	$in_stock  = $product->is_in_stock();

	$thumbs = array_slice(
		array_filter(
			array_merge(
				array( get_post_thumbnail_id() ),
				$product->get_gallery_image_ids()
			)
		),
		0,
		8
	);
	?>

<section class="mz-pdp" data-mz-pdp>
	<div class="mz-pdp-shell">

		<nav class="mz-pdp-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'medzuro' ); ?>">
			<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'Shop', 'medzuro' ); ?></a>
			<span><?php the_title(); ?></span>
		</nav>

		<div class="mz-pdp-top">
			<div class="mz-pdp-gallery-wrap">
				<div class="mz-pdp-gallery">
					<div class="mz-pdp-main-media">
						<?php
						if ( has_post_thumbnail() ) {
							echo wp_get_attachment_image(
								get_post_thumbnail_id(),
								'woocommerce_single',
								false,
								array(
									'class'   => 'mz-pdp-main-image',
									'loading' => 'eager',
								)
							);
						} else {
							echo wc_placeholder_img( 'woocommerce_single', array( 'class' => 'mz-pdp-main-image' ) );
						}
						?>
					</div>

					<div class="mz-pdp-thumbs" aria-label="<?php esc_attr_e( 'Product media', 'medzuro' ); ?>">
						<?php
						foreach ( $thumbs as $i => $attachment_id ) :
							$full = wp_get_attachment_image_url( $attachment_id, 'woocommerce_single' );
							$alt  = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
							$alt  = $alt ? $alt : get_the_title();
							?>
							<button class="mz-pdp-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" type="button"
								data-mz-pdp-thumb
								data-image="<?php echo esc_url( $full ); ?>"
								data-alt="<?php echo esc_attr( $alt ); ?>">
								<?php echo wp_get_attachment_image( $attachment_id, 'woocommerce_gallery_thumbnail', false, array( 'loading' => 'lazy' ) ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				</div>

				<?php get_template_part( 'template-parts/product-trust-row' ); ?>
			</div>

			<div class="mz-pdp-buybox">
				<p class="mz-pdp-kicker">Best Seller | Wellness Support</p>
				<h1><?php the_title(); ?></h1>
				<p class="mz-pdp-subtitle"><?php echo esc_html( $subtitle ); ?></p>

				<div class="mz-pdp-rating">
					<span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
					<strong>4.8/5.0</strong>
					<small>Trusted by Fiji customers</small>
				</div>

				<div class="mz-pdp-loved">Loved by local wellness customers</div>

				<div class="mz-pdp-benefits">
					<span>Energy and stamina support</span>
					<span>Batch tested quality</span>
					<span>Local pickup in Fiji</span>
					<span>Friendly support team</span>
				</div>

				<form class="mz-pdp-form" method="post" enctype="multipart/form-data"
					action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>">

					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">

					<?php if ( $is_var ) : ?>
						<input type="hidden" name="variation_id" data-mz-pdp-variation
							value="<?php echo esc_attr( $selected['value'] ?? '' ); ?>">

						<?php
						foreach ( array_keys( $product->get_variation_attributes() ) as $attribute ) :
							$field = 'attribute_' . sanitize_title( $attribute );
							?>
							<input type="hidden"
								name="<?php echo esc_attr( $field ); ?>"
								data-mz-pdp-attribute="<?php echo esc_attr( $field ); ?>"
								value="<?php echo esc_attr( $selected['attributes'][ $field ] ?? '' ); ?>">
						<?php endforeach; ?>
					<?php endif; ?>

					<div class="mz-pdp-pack-head">
						<h2><?php esc_html_e( 'Select Your Pack', 'medzuro' ); ?></h2>
						<span><?php echo esc_html( $serving ); ?></span>
					</div>

					<div class="mz-pdp-pack-grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Select pack', 'medzuro' ); ?>">
						<?php foreach ( $packs as $pack ) : ?>
							<label class="mz-pdp-pack<?php echo $pack['selected'] ? ' is-selected' : ''; ?><?php echo $pack['available'] ? '' : ' is-disabled'; ?>">
								<input type="radio" name="medzuro_pack" data-mz-pdp-pack
									value="<?php echo esc_attr( $pack['value'] ); ?>"
									<?php checked( $pack['selected'] ); ?>
									<?php disabled( ! $pack['available'] ); ?>
									data-quantity="<?php echo esc_attr( $pack['quantity'] ); ?>"
									data-variation="<?php echo esc_attr( $is_var ? $pack['value'] : '' ); ?>"
									data-attributes="<?php echo esc_attr( wp_json_encode( $pack['attributes'] ) ); ?>"
									data-price="<?php echo esc_attr( $pack['price_html'] ); ?>"
									data-compare="<?php echo esc_attr( $pack['compare'] ); ?>"
									data-save="<?php echo esc_attr( $pack['save'] ); ?>"
									data-available="<?php echo $pack['available'] ? 'true' : 'false'; ?>">

								<span class="mz-pdp-pack__flag"><?php echo esc_html( $pack['flag'] ); ?></span>
								<strong><?php echo esc_html( $pack['title'] ); ?></strong>
								<small><?php echo esc_html( $pack['supply'] ); ?></small>
								<b><?php echo esc_html( $pack['price_html'] ); ?></b>
								<?php if ( $pack['compare'] ) : ?>
									<s><?php echo esc_html( $pack['compare'] ); ?></s>
								<?php endif; ?>
							</label>
						<?php endforeach; ?>
					</div>

					<div class="mz-pdp-freebie">
						<?php medzuro_icon( 'gift', 24 ); ?>
						Ask about bundle offers and local pickup when you order.
					</div>

					<div class="mz-pdp-qty-row">
						<div>
							<label for="mz-quantity"><?php esc_html_e( 'Choose Quantity', 'medzuro' ); ?></label>
							<div class="mz-pdp-qty">
								<button type="button" data-mz-pdp-minus aria-label="<?php esc_attr_e( 'Decrease quantity', 'medzuro' ); ?>">-</button>
								<input id="mz-quantity" type="number" name="quantity" min="1"
									value="<?php echo esc_attr( $start_qty ); ?>">
								<button type="button" data-mz-pdp-plus aria-label="<?php esc_attr_e( 'Increase quantity', 'medzuro' ); ?>">+</button>
							</div>
						</div>

						<div class="mz-pdp-price-box">
							<strong data-mz-pdp-price><?php echo esc_html( $selected['price_html'] ?? '' ); ?></strong>

							<s data-mz-pdp-compare <?php echo empty( $selected['compare'] ) ? 'hidden' : ''; ?>>
								<?php echo esc_html( $selected['compare'] ?? '' ); ?>
							</s>

							<span data-mz-pdp-save <?php echo empty( $selected['save'] ) ? 'hidden' : ''; ?>>
								SAVE <?php echo esc_html( $selected['save'] ?? '' ); ?>
							</span>

							<small><?php esc_html_e( 'MRP inclusive of all taxes', 'medzuro' ); ?></small>
						</div>
					</div>

					<div class="mz-pdp-offers">
						<h2><?php esc_html_e( 'Limited Period Offers', 'medzuro' ); ?></h2>
						<ul>
							<li>Get local support before and after your order.</li>
							<li>Prepaid and pickup options available in Fiji.</li>
							<li data-mz-pdp-saving-line>
								<?php
								echo empty( $selected['save'] )
									? esc_html__( 'Ask us about current bundle savings.', 'medzuro' )
									/* translators: %s: formatted saving amount. */
									: esc_html( sprintf( __( 'You are saving %s on this order.', 'medzuro' ), $selected['save'] ) );
								?>
							</li>
						</ul>
					</div>

					<div class="mz-pdp-actions">
						<button class="mz-pdp-add" type="submit" <?php disabled( ! $in_stock ); ?>>
							<span>
								<?php
								echo $in_stock
									? esc_html__( 'Add To Cart', 'medzuro' )
									: esc_html__( 'Sold Out', 'medzuro' );
								?>
							</span>
						</button>
					</div>
				</form>

				<div class="mz-pdp-delivery">
					<label for="mz-delivery"><?php esc_html_e( 'Check Delivery', 'medzuro' ); ?></label>
					<div>
						<input id="mz-delivery" type="text" placeholder="<?php esc_attr_e( 'Enter suburb or area', 'medzuro' ); ?>">
						<button type="button"><?php esc_html_e( 'Check', 'medzuro' ); ?></button>
					</div>
				</div>

				<div class="mz-pdp-service">
					<span>Fast Delivery</span>
					<span>Local Pickup</span>
					<span>24/7 Support</span>
				</div>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/product-lower' ); ?>
</section>

	<?php
endwhile;

get_footer();
