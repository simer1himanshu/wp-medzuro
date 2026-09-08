<?php
/**
 * Product page helpers.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a Medzuro product field.
 *
 * These were Shopify metafields under the `medzuro` namespace (see
 * MEDZURO-SETUP.md). They are plain post meta here, so ACF can supply them
 * without the templates caring whether ACF is installed.
 *
 * @param string      $key     Field key, e.g. 'subtitle'.
 * @param string      $default Fallback when unset.
 * @param int|null    $post_id Product ID; current post when null.
 * @return string
 */
function medzuro_field( $key, $default = '', $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();

	$value = function_exists( 'get_field' )
		? get_field( 'medzuro_' . $key, $post_id )
		: get_post_meta( $post_id, 'medzuro_' . $key, true );

	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Build the "Select Your Pack" options.
 *
 * The Shopify section had two mutually exclusive branches: real variants when
 * the product had more than one, otherwise four synthetic quantity bundles
 * priced as multiples of the base price. Both are normalised to one shape here
 * so the template renders a single loop.
 *
 * @param WC_Product $product Product being displayed.
 * @return array<int,array<string,mixed>>
 */
function medzuro_pdp_packs( $product ) {
	$supply = array( 1 => '1 Month Supply', 2 => '2 Month Supply', 3 => '3 Month Supply', 4 => '4 Month Supply' );
	$packs  = array();

	if ( $product->is_type( 'variable' ) ) {
		$flags       = array( 1 => 'Starter', 2 => 'Most Popular', 3 => 'Best Value', 4 => 'Family Pack' );
		$variations  = array_slice( $product->get_children(), 0, 4 );
		$default_set = false;

		foreach ( array_values( $variations ) as $i => $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( ! $variation ) {
				continue;
			}

			$index   = $i + 1;
			$price   = (float) $variation->get_price();
			$compare = (float) $variation->get_regular_price();
			$saves   = $compare > $price;

			// First in-stock variation is preselected, matching Shopify's
			// selected_or_first_available_variant.
			$selected = ! $default_set && $variation->is_in_stock();
			$default_set = $default_set || $selected;

			$packs[] = array(
				'value'      => $variation_id,
				'name'       => 'variation_id',
				'quantity'   => 1,
				'flag'       => $flags[ $index ] ?? 'Family Pack',
				'title'      => wc_get_formatted_variation( $variation, true, false ) ?: $variation->get_name(),
				'supply'     => $supply[ $index ] ?? '',
				'price'      => $price,
				'price_html' => wp_strip_all_tags( wc_price( $price ) ),
				'compare'    => $saves ? wp_strip_all_tags( wc_price( $compare ) ) : '',
				'save'       => $saves ? wp_strip_all_tags( wc_price( $compare - $price ) ) : '',
				'available'  => $variation->is_in_stock(),
				'attributes' => $variation->get_variation_attributes(),
				'selected'   => $selected,
			);
		}

		return $packs;
	}

	// Single-variant path: four quantity bundles.
	$flags   = array( 1 => 'Starter', 2 => 'Most Popular', 3 => 'Best Value', 4 => 'Super Saver' );
	$price   = (float) $product->get_price();
	$compare = (float) $product->get_regular_price();
	$saves   = $compare > $price;

	for ( $qty = 1; $qty <= 4; $qty++ ) {
		$packs[] = array(
			'value'      => $qty,
			'name'       => 'medzuro_pack',
			'quantity'   => $qty,
			'flag'       => $flags[ $qty ],
			'title'      => 1 === $qty ? '1 Pack' : $qty . ' Packs',
			'supply'     => $supply[ $qty ],
			'price'      => $price * $qty,
			'price_html' => wp_strip_all_tags( wc_price( $price * $qty ) ),
			'compare'    => $saves ? wp_strip_all_tags( wc_price( $compare * $qty ) ) : '',
			'save'       => $saves ? wp_strip_all_tags( wc_price( ( $compare - $price ) * $qty ) ) : '',
			'available'  => $product->is_in_stock(),
			'attributes' => array(),
			// Shopify preselected the second bundle.
			'selected'   => 2 === $qty,
		);
	}

	return $packs;
}

/**
 * Product brand, standing in for Shopify's product.vendor.
 *
 * WooCommerce has no vendor field. Since 9.4 it ships a native `product_brand`
 * taxonomy, which is what the store should use; the older third-party
 * `pwb-brand` taxonomy and a plain `medzuro_brand` meta value are accepted as
 * fallbacks so the card works whatever is in place.
 *
 * @param WC_Product $product Product to read.
 * @return string Brand name, or an empty string.
 */
function medzuro_product_brand( $product ) {
	foreach ( array( 'product_brand', 'pwb-brand' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = get_the_terms( $product->get_id(), $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
	}

	return (string) medzuro_field( 'brand', '', $product->get_id() );
}
