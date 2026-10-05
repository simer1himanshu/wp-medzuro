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
		'holyoak-resin'    => 'holyoak-resin-banner.jpg',
		'holyoak-gummies'  => 'holyoak-gummies-banner.jpg',
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

/* -------------------------------------------------------------------------
 * Product page design (brand eyebrow, tagline/description, feature strip)
 * ---------------------------------------------------------------------- */

/**
 * Brand shown above the title ("HOLYOAK").
 *
 * Uses WooCommerce Brands when a brand is assigned, otherwise HolyOak, which
 * every product in the store currently is.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function medzuro_pdp_brand( $product ) {
	$brand = 'HolyOak';
	if ( taxonomy_exists( 'product_brand' ) ) {
		$terms = get_the_terms( $product->get_id(), 'product_brand' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$brand = $terms[0]->name;
		}
	}
	return apply_filters( 'medzuro_pdp_brand', $brand, $product );
}

/**
 * Split the short description into the one-line tagline shown beside the
 * rating and the paragraph under it, as in the design.
 *
 * The first sentence becomes the tagline; the rest is the paragraph.
 *
 * @param string $short Short description (HTML).
 * @return array{tagline:string, body:string}
 */
function medzuro_pdp_split_description( $short ) {
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $short ) ) );
	if ( '' === $text ) {
		return array( 'tagline' => '', 'body' => '' );
	}

	if ( preg_match( '/^(.+?[.!?])\s+(.+)$/u', $text, $m ) ) {
		return array( 'tagline' => $m[1], 'body' => $m[2] );
	}

	return array( 'tagline' => $text, 'body' => '' );
}

/**
 * Feature strip under the gallery: icon, title, subtitle.
 *
 * Shilajit products get the client's design copy; other products get no
 * strip until their own copy is written (filter medzuro_pdp_features).
 *
 * @param WC_Product $product Product.
 * @return array<int, array{0:string,1:string,2:string}>
 */
function medzuro_pdp_features( $product ) {
	$features = array();

	if ( false !== stripos( $product->get_name(), 'shilajit' ) ) {
		$features = array(
			array( 'leaf', __( 'Pure & Authentic', 'medzuro' ), __( 'High-quality Shilajit extract', 'medzuro' ) ),
			array( 'lotus', __( 'Daily Wellness', 'medzuro' ), __( 'Supports natural energy', 'medzuro' ) ),
			array( 'arm', __( 'Strength & Vitality', 'medzuro' ), __( 'Helps you stay active', 'medzuro' ) ),
			array( 'heart', __( 'Trusted Supplement', 'medzuro' ), __( 'From the Medzuro range', 'medzuro' ) ),
		);
	}

	return apply_filters( 'medzuro_pdp_features', $features, $product );
}

/**
 * The design's add-to-cart row has no Buy Now button.
 */
function medzuro_pdp_hide_buy_now() {
	remove_action( 'woocommerce_after_add_to_cart_button', 'medzuro_buy_now_button' );
}
add_action( 'wp', 'medzuro_pdp_hide_buy_now' );

/* -------------------------------------------------------------------------
 * "Buy any 2 & get 10% additional discount"
 * ---------------------------------------------------------------------- */

/**
 * Whether the offer is running. Turn it off with
 * add_filter( 'medzuro_bundle_offer_enabled', '__return_false' );
 *
 * @return bool
 */
function medzuro_bundle_offer_enabled() {
	return (bool) apply_filters( 'medzuro_bundle_offer_enabled', true );
}

/**
 * @return float Discount rate, 0.10 = 10%.
 */
function medzuro_bundle_offer_rate() {
	return (float) apply_filters( 'medzuro_bundle_offer_rate', 0.10 );
}

/**
 * Apply the offer: with 2 or more items in the cart, take 10% off the
 * items (after any coupons) as a negative fee, shown as its own line in the
 * cart, checkout and order.
 *
 * @param WC_Cart $cart Cart.
 */
function medzuro_apply_bundle_offer( $cart ) {
	if ( ! medzuro_bundle_offer_enabled() || ( is_admin() && ! wp_doing_ajax() ) ) {
		return;
	}

	if ( $cart->get_cart_contents_count() < 2 ) {
		return;
	}

	$base = (float) $cart->get_subtotal() - (float) $cart->get_discount_total();
	if ( $base <= 0 ) {
		return;
	}

	$rate     = medzuro_bundle_offer_rate();
	$discount = round( $base * $rate, wc_get_price_decimals() );

	/* translators: %d: percent */
	$cart->add_fee( sprintf( __( 'Buy 2+ offer (%d%% off)', 'medzuro' ), (int) round( $rate * 100 ) ), -$discount, false );
}
add_action( 'woocommerce_cart_calculate_fees', 'medzuro_apply_bundle_offer' );


/**
 * Spec chips for the product details intro (count, strength, testing, type).
 *
 * Built only from data the product already has, so a chip is omitted rather
 * than guessed when the information is missing.
 *
 * @param WC_Product $product    Product.
 * @param bool       $is_holyoak Whether the Eurofins testing claim applies.
 * @return array<int,array{icon:string,value:string,label:string}>
 */
function medzuro_pdp_spec_chips( $product, $is_holyoak ) {
	$chips   = array();
	$serving = medzuro_product_serving( $product ) ?: (string) ( medzuro_home()['settings']['product_meta'] ?? '' );
	$unit    = '';

	if ( preg_match( '/(\d+)\s*([A-Za-z]+)/', $serving, $m ) ) {
		$unit    = strtolower( $m[2] );
		$weight  = in_array( $unit, array( 'g', 'kg', 'ml' ), true );
		$chips[] = array(
			'icon'  => 'pill',
			'value' => $weight ? $m[1] . ' ' . $m[2] : $m[1],
			'label' => $weight ? __( 'Net weight', 'medzuro' ) : ucfirst( $m[2] ),
		);
	}

	$text = $product->get_name() . ' ' . wp_strip_all_tags( $product->get_description() . ' ' . $product->get_short_description() );

	if ( 'capsules' === $unit && preg_match( '/(\d+(?:\.\d+)?)\s?mg\b/i', $text, $m ) ) {
		$chips[] = array(
			'icon'  => 'leaf',
			'value' => $m[1] . ' mg',
			'label' => __( 'per capsule', 'medzuro' ),
		);
	}

	if ( $is_holyoak ) {
		$chips[] = array(
			'icon'  => 'lab',
			'value' => __( 'Third-Party', 'medzuro' ),
			'label' => __( 'Lab Tested', 'medzuro' ),
		);
	}

	$chips[] = array(
		'icon'  => 'leaf2',
		'value' => __( 'Dietary', 'medzuro' ),
		'label' => __( 'Supplement', 'medzuro' ),
	);

	return $chips;
}

/**
 * First published page matching one of several slugs, else a fallback URL.
 *
 * @param string[] $slugs    Candidate page slugs.
 * @param string   $fallback Fallback URL.
 * @return string
 */
function medzuro_page_url( $slugs, $fallback ) {
	foreach ( $slugs as $slug ) {
		$page = get_page_by_path( $slug );

		if ( $page && 'publish' === $page->post_status ) {
			return get_permalink( $page );
		}
	}

	return $fallback;
}


/**
 * Pack size and servings line, e.g. "60 Capsules • 30 Servings".
 *
 * The medzuro_serving custom field wins. When it is empty the three live
 * HolyOak products fall back to these values, so a product never shows another
 * product's pack size.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function medzuro_product_serving( $product ) {
	$value = (string) medzuro_field( 'serving', '', $product->get_id() );

	if ( '' !== $value ) {
		return $value;
	}

	$defaults = array(
		'holyoak-resin'    => '30 g • 45 Servings',
		'holyoak-capsules' => '60 Capsules • 30 Servings',
		'holyoak-gummies'  => '60 Gummies • 30 Servings',
	);

	return (string) apply_filters( 'medzuro_product_serving', $defaults[ $product->get_slug() ] ?? '', $product );
}
