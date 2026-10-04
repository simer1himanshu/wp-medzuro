<?php
/**
 * Product information below the purchase area.
 *
 * Extended past the description/info-grid/specifications core with a set of
 * marketing sections styled after the richer PDP layout the theme shipped
 * before the WooCommerce-native rebuild (usage steps, a purity/trust panel,
 * a benefits grid, and an FAQ accordion). The CSS for all of these already
 * exists in assets/css/product-page.css from that earlier build, so nothing
 * here is new styling — only the honest, generic copy is new.
 *
 * The "HolyOak around the world" banner reads its copy and stats/flags from
 * medzuro_home() — the same source the front page uses — so there is one
 * place to update that content rather than two.
 *
 * Deliberately no reviews or testimonial section here: WooCommerce's own
 * review system (shown in the buybox and via woocommerce_template_single_meta)
 * is the only reviews surface this theme uses, so nothing here is ever
 * fabricated to look like a genuine customer said it.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
	return;
}

$description     = $product->get_description();
$hero_banner_url = medzuro_pdp_banner_url( $product );
$brand           = medzuro_product_brand( $product );

// get_name() runs the medzuro_live_product_name filter, which shortens the
// three live HolyOak products to "Shilajit …" — so it no longer contains
// "holyoak" and can't be used to detect them. get_the_title() is the raw,
// unfiltered post title instead. The slug catches the rest (every HolyOak
// product, live or coming soon, is slugged holyoak-*).
$is_holyoak = false !== stripos( $brand, 'holyoak' )
	|| false !== stripos( $product->get_slug(), 'holyoak' )
	|| false !== stripos( get_the_title( $product->get_id() ), 'holyoak' )
	|| has_term( 'holyoak', 'product_cat', $product->get_id() );
?>

<?php
$mz_title  = ( false !== stripos( $product->get_name(), $brand ) || '' === $brand ) ? $product->get_name() : trim( $brand . ' ' . $product->get_name() );
$mz_image  = medzuro_card_image_url( $product );
$mz_image  = $mz_image ?: wp_get_attachment_image_url( $product->get_image_id(), 'large' );
$mz_chips  = medzuro_pdp_spec_chips( $product, $is_holyoak );
$mz_has_specs = $product->has_attributes() || $product->has_weight() || $product->has_dimensions();
$mz_cards  = array(
	array(
		'icon'  => 'shield',
		'title' => __( 'Quality You Can Trust', 'medzuro' ),
		'text'  => __( 'Made with carefully selected ingredients and quality-focused manufacturing standards. Batch testing documentation can be provided where applicable.', 'medzuro' ),
		'label' => __( 'Quality & Testing', 'medzuro' ),
		'url'   => medzuro_page_url( array( 'lab-test-and-purity', 'lab-purity', 'lab-testing' ), home_url( '/lab-purity/' ) ),
	),
	array(
		'icon'  => 'truck',
		'title' => __( 'Delivered Across Fiji', 'medzuro' ),
		'text'  => __( 'Convenient delivery to customers across Fiji through our reliable shipping partner DHL, with local pickup options available in Suva where offered.', 'medzuro' ),
		'label' => __( 'Delivery Information', 'medzuro' ),
		'url'   => medzuro_page_url( array( 'delivery', 'delivery-information' ), home_url( '/delivery-information/' ) ),
	),
	array(
		'icon'  => 'headset',
		'title' => __( "We're Here to Help", 'medzuro' ),
		'text'  => __( 'Need help with your order, delivery, product information or availability? Our Fiji support team is ready to assist you.', 'medzuro' ),
		'label' => __( 'Contact Our Team', 'medzuro' ),
		'url'   => medzuro_page_url( array( 'contact' ), home_url( '/contact/' ) ),
	),
	array(
		'icon'  => 'doc',
		'title' => __( "What's Inside", 'medzuro' ),
		'text'  => __( 'View ingredients, serving size, suggested use, storage information and important product guidance to help you make an informed choice.', 'medzuro' ),
		'label' => __( 'Product Information', 'medzuro' ),
		'url'   => $mz_has_specs ? '#mz-pdp-specifications' : '#mz-faq-title',
	),
);
?>
<section class="mz-pdp-details" aria-label="<?php esc_attr_e( 'Product information', 'medzuro' ); ?>">
	<div class="mz-pdp-shell">
		<div class="mz-pdp-intro">
			<div class="mz-pdp-intro__copy">
				<p class="mz-pdp-kicker"><?php esc_html_e( 'Product details', 'medzuro' ); ?></p>
				<h2><?php
					/* translators: %s: product name */
					echo esc_html( sprintf( __( 'Why Choose %s?', 'medzuro' ), $mz_title ) );
				?></h2>
				<p class="mz-pdp-intro__tag"><?php esc_html_e( 'Pure. Convenient. Made for your daily wellness routine.', 'medzuro' ); ?></p>
				<?php if ( $description ) : ?>
					<div class="mz-pdp-intro__body rte"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
				<?php endif; ?>

				<ul class="mz-pdp-chips" role="list">
					<?php foreach ( $mz_chips as $chip ) : ?>
						<li>
							<span class="mz-pdp-chips__icon"><?php medzuro_ref_icon( $chip['icon'] ); ?></span>
							<strong><?php echo esc_html( $chip['value'] ); ?></strong>
							<small><?php echo esc_html( $chip['label'] ); ?></small>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( $mz_image ) : ?>
				<div class="mz-pdp-intro__media">
					<img src="<?php echo esc_url( $mz_image ); ?>" alt="<?php echo esc_attr( $mz_title ); ?>" loading="lazy">
				</div>
			<?php endif; ?>
		</div>

		<div class="mz-pdp-info-grid">
			<?php foreach ( $mz_cards as $card ) : ?>
				<article>
					<span class="mz-pdp-info-icon"><?php medzuro_ref_icon( $card['icon'] ); ?></span>
					<h3><?php echo esc_html( $card['title'] ); ?></h3>
					<p><?php echo esc_html( $card['text'] ); ?></p>
					<a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['label'] ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( $product->has_attributes() || $product->has_weight() || $product->has_dimensions() ) : ?>
			<div class="mz-pdp-specifications" id="mz-pdp-specifications">
				<h2><?php esc_html_e( 'Product information', 'medzuro' ); ?></h2>
				<?php wc_display_product_attributes( $product ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( $is_holyoak ) : ?>
<div class="mz-pdp-section mz-pdp-usage">
	<div class="mz-pdp-shell">
		<div class="mz-pdp-section-head">
			<p><?php esc_html_e( 'Getting Started', 'medzuro' ); ?></p>
			<h2><?php esc_html_e( 'Make it part of your daily routine', 'medzuro' ); ?></h2>
		</div>
		<div class="mz-pdp-usage-grid">
			<div>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v18"/><path d="M6 9h12"/><path d="M8 21h8"/><path d="M9 3h6"/></svg>
				<h3><?php esc_html_e( 'Daily Serving', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( "Follow the product label or your practitioner's advice for the right daily serving.", 'medzuro' ); ?></p>
			</div>
			<div>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10"/><path d="M9 2v6l-5 9a3 3 0 0 0 2.6 4.5h10.8A3 3 0 0 0 20 17L15 8V2"/><path d="M8 14h8"/></svg>
				<h3><?php esc_html_e( 'Simple Routine', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Take consistently with water or as recommended for this product.', 'medzuro' ); ?></p>
			</div>
			<div>
				<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 6v6l4 2"/></svg>
				<h3><?php esc_html_e( 'Consistency', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Best results come from a steady routine, balanced meals, rest, and hydration.', 'medzuro' ); ?></p>
			</div>
		</div>
	</div>
</div>

<?php // Sits between the usage steps and the world banner — not a top-of-page hero — and, being inside this $is_holyoak block, only for HolyOak products. Every product with a banner today is HolyOak; a non-HolyOak product using the medzuro_pdp_hero_banner fallback would need its own slot if that comes up. ?>
<?php if ( $hero_banner_url ) : ?>
<div class="mz-pdp-section mz-pdp-hero-banner">
	<div class="mz-pdp-shell">
		<img src="<?php echo esc_url( $hero_banner_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
	</div>
</div>
<?php endif; ?>

<?php
$home  = medzuro_home()['settings'];
$stats = medzuro_home_blocks( 'stat' );
$flags = medzuro_home_blocks( 'flag' );
?>
<?php if ( ! empty( $home['show_world'] ) && ( $stats || $flags ) ) : ?>
<div class="mz-pdp-section mz-pdp-world">
	<div class="mz-pdp-shell">
		<div class="mz-pdp-world__panel">
			<div class="mz-pdp-world__copy">
				<p class="mz-pdp-kicker"><?php echo esc_html( $home['world_kicker'] ); ?></p>
				<h2><?php echo esc_html( $home['world_heading'] ); ?><br><?php echo esc_html( $home['world_heading_2'] ); ?> <span><?php echo esc_html( $home['world_accent'] ); ?></span></h2>
			</div>

			<?php if ( $stats ) : ?>
				<div class="mz-pdp-world__stats" role="list">
					<?php foreach ( $stats as $block ) : ?>
						<div role="listitem">
							<?php medzuro_ref_icon( $block['icon'] ); ?>
							<strong><?php echo esc_html( $block['value'] ); ?></strong><small><?php echo esc_html( $block['label'] ); ?></small>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $flags ) : ?>
				<ul class="mz-pdp-flags" role="list" aria-label="<?php esc_attr_e( 'International regions', 'medzuro' ); ?>">
					<?php foreach ( $flags as $block ) : ?>
						<li>
							<span class="mz-flag mz-flag--<?php echo esc_attr( $block['flag'] ); ?>"></span><small><?php echo esc_html( $block['label'] ); ?></small>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="mz-pdp-section mz-pdp-purity">
	<div class="mz-pdp-shell mz-pdp-purity__inner">
		<div class="mz-pdp-purity__copy">
			<p><?php esc_html_e( 'Lab Tested. Genuine. Trusted.', 'medzuro' ); ?></p>
			<h2><?php esc_html_e( 'Quality you can verify', 'medzuro' ); ?></h2>
			<span><?php esc_html_e( 'HolyOak products sold through Medzuro Retail are independently tested by Eurofins, a leading third-party laboratory, so you can check what you\'re taking rather than take our word for it.', 'medzuro' ); ?></span>
			<div class="mz-pdp-purity-list">
				<div><?php esc_html_e( 'Third-party lab tested', 'medzuro' ); ?></div>
				<div><?php esc_html_e( 'Genuine, authorised stock', 'medzuro' ); ?></div>
				<div><?php esc_html_e( 'Certificate available on request', 'medzuro' ); ?></div>
				<div><?php esc_html_e( 'Checked before it reaches you', 'medzuro' ); ?></div>
			</div>
		</div>
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-eurofins-building-v2.png' ); ?>" alt="<?php esc_attr_e( 'Eurofins independent testing laboratory', 'medzuro' ); ?>" loading="lazy" width="1419" height="1108">
	</div>
</div>

<div class="mz-pdp-section mz-pdp-difference">
	<div class="mz-pdp-shell">
		<div class="mz-pdp-section-head">
			<p><?php esc_html_e( 'Why Buy From Medzuro', 'medzuro' ); ?></p>
			<h2><?php esc_html_e( 'What You Get With Every Order', 'medzuro' ); ?></h2>
		</div>
		<div class="mz-pdp-difference-grid">
			<article>
				<span>01</span>
				<h3><?php esc_html_e( 'Genuine Stock', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Sourced and sold as authentic HolyOak product, not a third-party repack.', 'medzuro' ); ?></p>
			</article>
			<article>
				<span>02</span>
				<h3><?php esc_html_e( 'Independently Tested', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Checked by Eurofins so quality is verified, not just claimed.', 'medzuro' ); ?></p>
			</article>
			<article>
				<span>03</span>
				<h3><?php esc_html_e( 'Local to Fiji', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'Delivered locally, with pickup available in Suva by arrangement.', 'medzuro' ); ?></p>
			</article>
			<article>
				<span>04</span>
				<h3><?php esc_html_e( 'Real Support', 'medzuro' ); ?></h3>
				<p><?php esc_html_e( 'A local team you can actually reach with questions before or after you order.', 'medzuro' ); ?></p>
			</article>
		</div>
	</div>
</div>

<div class="mz-pdp-section mz-pdp-faq">
	<div class="mz-pdp-shell">
		<?php get_template_part( 'template-parts/faq' ); ?>
	</div>
</div>
<?php endif; ?>
