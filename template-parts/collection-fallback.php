<?php
/**
 * Placeholder product grid, shown when a category has no products.
 *
 * Ported from the `else` branch of sections/medzuro-collection-page.liquid,
 * which built five demo cards from parallel Liquid arrays. Kept because the
 * store has no catalogue entered yet, so an empty shop page would otherwise
 * render blank. Delete this part once real products are published.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$fallbacks = array(
	array( 'HolyOak Ashwagandha',        '60 Capsules', 'FJD $39.20', 'FJD $49.00', 128 ),
	array( 'HolyOak Shilajit',           '60 Capsules', 'FJD $45.00', '',           96 ),
	array( 'HolyOak Turmeric Curcumin',  '60 Capsules', 'FJD $38.25', 'FJD $45.00', 78 ),
	array( 'HolyOak Omega 3',            '60 Softgels', 'FJD $42.00', '',           84 ),
	array( 'HolyOak Multivitamin',       '60 Tablets',  'FJD $35.00', '',           112 ),
);

$contact = get_permalink( get_page_by_path( 'contact' ) );
?>
<div class="mz-product-grid mz-product-grid--dummy">
	<?php
	foreach ( $fallbacks as $i => $fallback ) :
		list( $title, $type, $price, $compare, $reviews ) = $fallback;
		$index = $i + 1;
		?>
		<article class="mz-product-card mz-product-card--dummy">
			<a class="mz-product-card__media" href="<?php echo esc_url( $contact ); ?>"
				aria-label="<?php echo esc_attr( $title ); ?>">
				<?php if ( 3 === $index ) : ?>
					<span class="mz-product-card__sale">15% off</span>
				<?php endif; ?>
				<span class="mz-fallback-bottle mz-fallback-bottle--<?php echo esc_attr( $index ); ?>" aria-hidden="true"><span></span></span>
			</a>

			<div class="mz-product-card__body">
				<p class="mz-product-card__vendor">HolyOak</p>
				<h3><a href="<?php echo esc_url( $contact ); ?>"><?php echo esc_html( $title ); ?></a></h3>
				<p class="mz-product-card__meta"><?php echo esc_html( $type ); ?></p>

				<div class="mz-product-card__rating" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'medzuro' ); ?>">
					<span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
					<small>(<?php echo esc_html( $reviews ); ?>)</small>
				</div>

				<div class="mz-product-card__price">
					<span><?php echo esc_html( $price ); ?></span>
					<?php if ( $compare ) : ?>
						<s><?php echo esc_html( $compare ); ?></s>
					<?php endif; ?>
				</div>

				<a class="mz-product-card__button" href="<?php echo esc_url( $contact ); ?>">Add To Cart</a>
			</div>
		</article>
	<?php endforeach; ?>
</div>
