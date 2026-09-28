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
 * Build the selling, compare-at, and discount values used by product cards.
 *
 * Real WooCommerce sale pricing always wins. Products without a sale use the
 * store's card presentation of 20% off without changing their checkout price.
 *
 * @param WC_Product $product Product being displayed.
 * @return array{current:string,compare:string,discount:int}
 */
function medzuro_card_pricing( $product ) {
	$numbers = medzuro_price_numbers( $product );

	return array(
		'current'  => wc_price( $numbers['current'] ),
		'compare'  => wc_price( $numbers['compare'] ),
		'discount' => $numbers['discount'],
	);
}

/**
 * Raw selling, compare-at and discount figures behind medzuro_card_pricing().
 *
 * Shared with the product page so the discount a shopper saw on the card is
 * the one they see after clicking through.
 *
 * @param WC_Product $product Product or variation.
 * @return array{current:float,compare:float,discount:int}
 */
function medzuro_price_numbers( $product ) {
	$current = (float) $product->get_price();
	$regular = (float) $product->get_regular_price();

	if ( $product->is_on_sale() && $regular > $current ) {
		$compare  = $regular;
		$discount = (int) round( ( ( $compare - $current ) / $compare ) * 100 );
	} else {
		$discount = 20;
		$compare  = $current / ( 1 - ( $discount / 100 ) );
	}

	return array(
		'current'  => $current,
		'compare'  => $compare,
		'discount' => $discount,
	);
}

/**
 * Format an amount as plain text, for data attributes and textContent.
 *
 * @param float $amount Amount.
 * @return string
 */
function medzuro_price_text( $amount ) {
	return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
}

/**
 * Price block values for the product page: selling, compare-at, saving.
 *
 * @param WC_Product $product Product or variation.
 * @return array{price:string,compare:string,save:string,discount:int}
 */
function medzuro_pdp_price( $product ) {
	$numbers = medzuro_price_numbers( $product );

	return array(
		'price'    => medzuro_price_text( $numbers['current'] ),
		'compare'  => medzuro_price_text( $numbers['compare'] ),
		'save'     => medzuro_price_text( $numbers['compare'] - $numbers['current'] ),
		'discount' => $numbers['discount'],
	);
}

/**
 * Option cards for a variable product with a single attribute (e.g. Weight).
 *
 * The cards drive WooCommerce's own variation form: picking one sets the
 * hidden attribute <select> and Woo's variation script resolves the
 * variation_id, so validation and stock checks stay Woo's. Products with more
 * than one attribute return null and keep the stock dropdowns.
 *
 * @param WC_Product $product Product being displayed.
 * @return array{attribute:string,label:string,selected:string,cards:array}|null
 */
function medzuro_pdp_variation_cards( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return null;
	}

	$attributes = $product->get_variation_attributes();

	if ( 1 !== count( $attributes ) ) {
		return null;
	}

	$taxonomy = (string) array_key_first( $attributes );
	$field    = 'attribute_' . sanitize_title( $taxonomy );
	$defaults = $product->get_default_attributes();
	$selected = (string) ( $defaults[ sanitize_title( $taxonomy ) ] ?? '' );
	$cards    = array();

	foreach ( $product->get_children() as $variation_id ) {
		$variation = wc_get_product( $variation_id );

		if ( ! $variation || ! $variation->variation_is_visible() ) {
			continue;
		}

		$value = (string) ( $variation->get_variation_attributes()[ $field ] ?? '' );

		// An "Any weight" variation cannot be shown as one card.
		if ( '' === $value ) {
			return null;
		}

		$term  = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $value, $taxonomy ) : false;
		$title = $term ? $term->name : $value;

		$cards[] = array_merge(
			medzuro_pdp_price( $variation ),
			array(
				'value'     => $value,
				'title'     => $title,
				'available' => $variation->is_in_stock(),
			)
		);
	}

	if ( ! $cards ) {
		return null;
	}

	$values = wp_list_pluck( $cards, 'value' );

	if ( ! in_array( $selected, $values, true ) ) {
		$in_stock = wp_list_filter( $cards, array( 'available' => true ) );
		$selected = $in_stock ? reset( $in_stock )['value'] : $cards[0]['value'];
	}

	return array(
		'attribute' => $field,
		'label'     => wc_attribute_label( $taxonomy, $product ),
		'selected'  => $selected,
		'cards'     => $cards,
	);
}

/**
 * Preselect the carded option in Woo's hidden dropdown.
 *
 * Without this the form loads with no variation chosen and the add-to-cart
 * button disabled, while the cards already show one as selected.
 *
 * @param array $args wc_dropdown_variation_attribute_options() arguments.
 * @return array
 */
function medzuro_pdp_preselect_variation( $args ) {
	if ( ! empty( $args['selected'] ) || ! is_product() || empty( $args['product'] ) ) {
		return $args;
	}

	$cards = medzuro_pdp_variation_cards( $args['product'] );

	if ( $cards ) {
		$args['selected'] = $cards['selected'];
	}

	return $args;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'medzuro_pdp_preselect_variation' );

/**
 * Wrap the product page quantity field in − / + buttons.
 */
function medzuro_pdp_qty_minus() {
	if ( is_product() ) {
		echo '<button type="button" class="mz-pdp-qty-btn" data-mz-pdp-minus aria-label="' . esc_attr__( 'Decrease quantity', 'medzuro' ) . '">&minus;</button>';
	}
}
add_action( 'woocommerce_before_quantity_input_field', 'medzuro_pdp_qty_minus' );

function medzuro_pdp_qty_plus() {
	if ( is_product() ) {
		echo '<button type="button" class="mz-pdp-qty-btn" data-mz-pdp-plus aria-label="' . esc_attr__( 'Increase quantity', 'medzuro' ) . '">+</button>';
	}
}
add_action( 'woocommerce_after_quantity_input_field', 'medzuro_pdp_qty_plus' );

/**
 * Return the coordinated square catalog image for a product when available.
 *
 * @param WC_Product $product Product being displayed.
 * @return string Catalog image URL, or an empty string for the Woo fallback.
 */
function medzuro_card_image_url( $product ) {
	$images = array(
		'holyoak-capsules'                 => 'holyoak-capsules.png',
		'holyoak-gummies'                  => 'holyoak-gummies.png',
		'holyoak-resin'                    => 'holyoak-resin.png',
		'holyoak-arjun-extract-capsules'   => 'holyoak-arjun-extract-capsules.png',
		'holyoak-garcinia-trimora'         => 'holyoak-garcinia-trimora.png',
		'holyoak-glyvionix'                => 'holyoak-glyvionix.png',
		'holyoak-l-glutathione'            => 'holyoak-l-glutathione.png',
		'holyoak-marine-collagen'          => 'holyoak-marine-collagen.png',
		'holyoak-riseup-men'               => 'holyoak-riseup-men.png',
	);
	$filename = $images[ $product->get_slug() ] ?? '';

	return $filename ? get_template_directory_uri() . '/assets/img/catalog/' . $filename : '';
}

/**
 * A wide marketing banner for the product page, for products supplied with
 * one, keyed by slug like medzuro_card_image_url() above. Falls back to the
 * medzuro_pdp_hero_banner post meta set through a custom field, so either
 * path works.
 *
 * @param WC_Product $product Product being displayed.
 * @return string Banner image URL, or an empty string.
 */
function medzuro_pdp_banner_url( $product ) {
	$images = array(
		'holyoak-capsules' => 'holyoak-capsules-banner.jpg',
	);
	$filename = $images[ $product->get_slug() ] ?? '';

	if ( $filename ) {
		return get_template_directory_uri() . '/assets/img/' . $filename;
	}

	$meta_id = absint( get_post_meta( $product->get_id(), 'medzuro_pdp_hero_banner', true ) );

	return $meta_id ? (string) wp_get_attachment_image_url( $meta_id, 'large' ) : '';
}

/**
 * Keep the three live HolyOak product names concise across WooCommerce.
 *
 * @param string     $name    Existing product name.
 * @param WC_Product $product Product being displayed.
 * @return string
 */
function medzuro_live_product_name( $name, $product ) {
	$names = array(
		'holyoak-gummies' => 'Shilajit Gummies',
		'holyoak-resin'   => 'Shilajit Resin',
		'holyoak-capsules' => 'Shilajit Capsules',
	);

	return $names[ $product->get_slug() ] ?? $name;
}
add_filter( 'woocommerce_product_get_name', 'medzuro_live_product_name', 10, 2 );

/**
 * Use the same display name in the browser tab on product pages.
 *
 * The filter above only reaches code that asks WooCommerce for the name; the
 * document title reads the raw post title.
 *
 * @param array $parts Document title parts.
 * @return array
 */
function medzuro_live_product_document_title( $parts ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );

		if ( $product ) {
			$parts['title'] = $product->get_name();
		}
	}

	return $parts;
}
add_filter( 'document_title_parts', 'medzuro_live_product_document_title' );

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
