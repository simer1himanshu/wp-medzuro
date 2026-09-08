<?php
/**
 * Homepage helpers.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Translate a Shopify storefront path to a WordPress URL.
 *
 * The homepage settings store links the way Shopify wrote them
 * (/collections/all, /pages/contact). Rather than rewrite them in the
 * generated content file — which would be lost on the next regeneration —
 * they are resolved here.
 *
 * Unknown targets fall back to the site root rather than emitting an empty
 * href, so a link to a page that was never created still goes somewhere sane.
 *
 * @param string $path Shopify-style path, or any absolute URL.
 * @return string
 */
function medzuro_home_url( $path ) {
	$path = (string) $path;

	if ( '' === $path ) {
		return home_url( '/' );
	}

	// Already absolute — leave it alone.
	if ( preg_match( '#^(https?:)?//#', $path ) ) {
		return $path;
	}

	if ( '/collections/all' === $path ) {
		return get_permalink( wc_get_page_id( 'shop' ) ) ?: home_url( '/' );
	}

	if ( preg_match( '#^/collections/([^/?]+)#', $path, $m ) ) {
		$link = get_term_link( $m[1], 'product_cat' );

		return is_wp_error( $link ) ? home_url( '/' ) : $link;
	}

	if ( preg_match( '#^/products/([^/?]+)#', $path, $m ) ) {
		$page = get_page_by_path( $m[1], OBJECT, 'product' );

		return $page ? get_permalink( $page ) : home_url( '/' );
	}

	if ( preg_match( '#^/pages/([^/?]+)#', $path, $m ) ) {
		$page = get_page_by_path( $m[1] );

		return $page ? get_permalink( $page ) : home_url( '/' );
	}

	return home_url( ltrim( $path, '/' ) );
}

/**
 * Products for the homepage carousel.
 *
 * Mirrors `collections[s.collection].products limit: s.product_limit`. The
 * `collection` setting holds a Shopify collection handle; 'all' means the whole
 * catalogue, anything else is matched against a product category slug.
 *
 * @return WC_Product[] Empty when the catalogue has nothing to show.
 */
function medzuro_home_products() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$handle = (string) medzuro_home_setting( 'collection', 'all' );
	$limit  = (int) medzuro_home_setting( 'product_limit', 5 );

	$args = array(
		'status'  => 'publish',
		'limit'   => $limit > 0 ? $limit : 5,
		'orderby' => 'menu_order',
		'order'   => 'ASC',
	);

	if ( '' !== $handle && 'all' !== $handle ) {
		$args['category'] = array( $handle );
	}

	$products = wc_get_products( $args );

	// A renamed or missing category should show the catalogue rather than an
	// empty rail.
	if ( ! $products && isset( $args['category'] ) ) {
		unset( $args['category'] );
		$products = wc_get_products( $args );
	}

	return is_array( $products ) ? $products : array();
}
