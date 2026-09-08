<?php
/**
 * Collection (product archive) helpers.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sort options for the archive toolbar.
 *
 * Shopify supplied collection.sort_options. WooCommerce keeps the equivalent
 * list behind woocommerce_catalog_orderby, which is what the Woo sorting
 * dropdown itself uses, so the values stay in step with core.
 *
 * @return array<string,string>
 */
function medzuro_catalog_orderby_options() {
	$options = apply_filters(
		'woocommerce_catalog_orderby',
		array(
			'menu_order' => __( 'Default sorting', 'woocommerce' ),
			'popularity' => __( 'Sort by popularity', 'woocommerce' ),
			'rating'     => __( 'Sort by average rating', 'woocommerce' ),
			'date'       => __( 'Sort by latest', 'woocommerce' ),
			'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
			'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
		)
	);

	// Woo hides the relevance option unless a search is running.
	if ( ! is_search() ) {
		unset( $options['relevance'] );
	}

	return $options;
}

/**
 * Archive pagination.
 *
 * Rebuilt with paginate_links() rather than woocommerce_pagination() so the
 * markup matches the Liquid: bare anchors and spans inside a nav, with chevron
 * SVGs for previous and next and `is-current` on the active page.
 */
function medzuro_pagination() {
	global $wp_query;

	if ( $wp_query->max_num_pages < 2 ) {
		return;
	}

	$links = paginate_links(
		array(
			'total'     => $wp_query->max_num_pages,
			'current'   => max( 1, (int) $wp_query->get( 'paged' ) ),
			'type'      => 'array',
			'end_size'  => 1,
			'mid_size'  => 1,
			'prev_text' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>',
			'next_text' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>',
		)
	);

	if ( ! $links ) {
		return;
	}

	$allowed = array_merge(
		wp_kses_allowed_html( 'post' ),
		array(
			'svg'  => array(
				'viewbox'     => true,
				'aria-hidden' => true,
			),
			'path' => array( 'd' => true ),
		)
	);

	echo '<nav class="mz-collection-pagination" role="navigation" aria-label="' . esc_attr__( 'Pagination', 'medzuro' ) . '">';

	foreach ( $links as $link ) {
		// paginate_links() marks the active page with `current`; the ported CSS
		// expects `is-current`, and dots are plain spans in both.
		$link = str_replace( 'page-numbers current', 'page-numbers is-current', $link );
		echo wp_kses( $link, $allowed );
	}

	echo '</nav>';
}

/**
 * Menu walker that emits bare anchors.
 *
 * The category nav in the Liquid was a flat list of <a> elements, not a <ul>,
 * so the default walker's list markup would not match the ported CSS.
 */
class Medzuro_Flat_Link_Walker extends Walker_Nav_Menu {

	/**
	 * No list wrapper.
	 *
	 * @param string $output Concatenated output.
	 * @param int    $depth  Depth of menu item.
	 * @param array  $args   Menu arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * No list wrapper.
	 *
	 * @param string $output Concatenated output.
	 * @param int    $depth  Depth of menu item.
	 * @param array  $args   Menu arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Render one anchor.
	 *
	 * @param string   $output Concatenated output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Menu item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$active = in_array( 'current-menu-item', (array) $item->classes, true )
			|| in_array( 'current-menu-parent', (array) $item->classes, true );

		$output .= sprintf(
			'<a class="%1$s" href="%2$s">%3$s</a>',
			$active ? 'is-active' : '',
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}

	/**
	 * Anchors need no closing wrapper.
	 *
	 * @param string   $output Concatenated output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   Menu arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}
