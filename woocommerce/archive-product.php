<?php
/**
 * Product archive — shop, category and tag listings.
 *
 * Ported from sections/medzuro-collection-page.liquid.
 *
 * Shopify's {% paginate %} maps onto the main WP_Query, whose per-page count
 * comes from the loop_shop_per_page filter in functions.php. The sort form
 * replaces collection.sort_options with Woo's catalog ordering values, and the
 * pagination is rebuilt with paginate_links() to keep the original markup
 * rather than accepting woocommerce_pagination()'s.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;

$total    = (int) $wp_query->found_posts;
$per_page = (int) $wp_query->get( 'posts_per_page' );
$paged    = max( 1, (int) $wp_query->get( 'paged' ) );
$first    = $total ? ( ( $paged - 1 ) * $per_page ) + 1 : 0;
$last     = min( $paged * $per_page, $total );
?>

<section class="mz-collection-page">

	<?php get_template_part( 'template-parts/collection-hero' ); ?>

	<div class="mz-collection-shell mz-collection-body">

		<?php get_template_part( 'template-parts/collection-highlights' ); ?>

		<?php
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
			)
		);

		if ( ! is_wp_error( $categories ) && $categories ) :
			?>
			<nav class="mz-collection-categories" aria-label="<?php esc_attr_e( 'Product categories', 'medzuro' ); ?>">
				<a class="<?php echo is_shop() ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php esc_html_e( 'All products', 'medzuro' ); ?></a>
				<?php foreach ( $categories as $category ) : ?>
					<a class="<?php echo is_product_category( $category->slug ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
						<?php echo esc_html( $category->name ); ?> <small><?php echo esc_html( $category->count ); ?></small>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<div class="mz-collection-toolbar">
			<div class="mz-collection-toolbar__title">
				<p class="mz-collection-eyebrow"><?php esc_html_e( 'Our catalogue', 'medzuro' ); ?></p>
				<h2>
					<?php
					if ( $total > 0 ) {
						printf(
							/* translators: 1: first product number, 2: last product number, 3: total products. */
							esc_html__( 'Showing %1$d-%2$d of %3$d', 'medzuro' ),
							(int) $first,
							(int) $last,
							(int) $total
						);
					} else {
						esc_html_e( 'Products coming soon', 'medzuro' );
					}
					?>
				</h2>
			</div>

			<?php if ( $total > 0 ) : ?>
				<form class="mz-collection-sort" method="get">
					<label for="mz-sort"><?php esc_html_e( 'Sort', 'medzuro' ); ?></label>
					<select id="mz-sort" name="orderby" onchange="this.form.submit()">
						<?php
						$current = isset( $_GET['orderby'] )
							? wc_clean( wp_unslash( $_GET['orderby'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							: get_option( 'woocommerce_default_catalog_orderby', 'menu_order' );

						foreach ( medzuro_catalog_orderby_options() as $value => $label ) :
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>

					<?php
					// Preserve the rest of the query string, so sorting does not
					// drop an active search term or filter.
					foreach ( $_GET as $key => $value ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						if ( 'orderby' === $key || 'submit' === $key || is_array( $value ) ) {
							continue;
						}
						?>
						<input type="hidden" name="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( wc_clean( wp_unslash( $value ) ) ); ?>">
					<?php endforeach; ?>
				</form>
			<?php endif; ?>
		</div>

		<?php if ( have_posts() ) : ?>

			<div class="mz-product-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					wc_get_template_part( 'content', 'product' );
				endwhile;
				?>
			</div>

			<?php medzuro_pagination(); ?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/collection-fallback' ); ?>
		<?php endif; ?>

	</div>
</section>

<?php
get_footer();
