<?php
/**
 * Homepage carousel placeholders, shown while the catalogue is empty.
 *
 * Ported from the `else` branch of the product loop in
 * sections/medzuro-reference-home.liquid. Same five demo products as the
 * collection fallback, in the carousel's card markup.
 *
 * Delete along with template-parts/collection-fallback.php once real products
 * are published.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$fallbacks = array(
	array( 'HolyOak Ashwagandha',       '60 Capsules', 'FJD $39.20', 'FJD $49.00', 128 ),
	array( 'HolyOak Shilajit',          '60 Capsules', 'FJD $45.00', '',           96 ),
	array( 'HolyOak Turmeric Curcumin', '60 Capsules', 'FJD $38.25', 'FJD $45.00', 78 ),
	array( 'HolyOak Omega 3',           '60 Softgels', 'FJD $42.00', '',           84 ),
	array( 'HolyOak Multivitamin',      '60 Tablets',  'FJD $35.00', '',           112 ),
);

$shop         = get_permalink( wc_get_page_id( 'shop' ) );
$shop         = $shop ? $shop : home_url( '/' );
$show_ratings = medzuro_home_setting( 'show_ratings', true );
$add_label    = medzuro_home_setting( 'add_label', 'Add to cart' );

foreach ( $fallbacks as $i => $fallback ) :
	list( $title, $type, $price, $compare, $reviews ) = $fallback;
	$index = $i + 1;
	?>
	<li class="mz-home-product">
		<a class="mz-home-product__media" href="<?php echo esc_url( $shop ); ?>">
			<?php if ( 3 === $index ) : ?>
				<span class="mz-home-sale">15% off</span>
			<?php endif; ?>
			<span class="mz-home-bottle mz-home-bottle--<?php echo esc_attr( $index ); ?>" aria-hidden="true"><span></span></span>
		</a>

		<div class="mz-home-product__body">
			<a class="mz-home-product__title" href="<?php echo esc_url( $shop ); ?>"><?php echo esc_html( $title ); ?></a>
			<span class="mz-home-product__meta"><?php echo esc_html( $type ); ?></span>

			<?php if ( $show_ratings ) : ?>
				<span class="mz-home-rating" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'medzuro' ); ?>">
					<span aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span><small>(<?php echo esc_html( $reviews ); ?>)</small>
				</span>
			<?php endif; ?>

			<span class="mz-home-price">
				<strong><?php echo esc_html( $price ); ?></strong>
				<?php if ( $compare ) : ?>
					<s><?php echo esc_html( $compare ); ?></s>
				<?php endif; ?>
			</span>

			<a class="mz-home-add" href="<?php echo esc_url( $shop ); ?>"><?php echo esc_html( $add_label ); ?></a>
		</div>
	</li>
	<?php
endforeach;
