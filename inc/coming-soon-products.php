<?php
/**
 * Import and present the supplied HolyOak coming-soon products.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a product is intentionally listed before it is available to buy.
 *
 * @param WC_Product|int|null $product Product object or ID.
 * @return bool
 */
function medzuro_is_coming_soon( $product = null ) {
	$product = is_a( $product, 'WC_Product' ) ? $product : wc_get_product( $product ?: get_the_ID() );

	return $product && 'yes' === $product->get_meta( 'medzuro_coming_soon' );
}

/**
 * Create an attachment from a product image bundled with the theme.
 *
 * @param string $filename   Theme image filename.
 * @param int    $product_id Product post ID.
 * @param string $title      Attachment title and alt text.
 * @return int
 */
function medzuro_import_product_image( $filename, $product_id, $title ) {
	$source = get_theme_file_path( '/assets/img/products/' . $filename );

	if ( ! file_exists( $source ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$temp = wp_tempnam( $filename );
	if ( ! $temp || ! copy( $source, $temp ) ) {
		return 0;
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $temp,
		),
		$product_id,
		$title
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $temp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}

	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	return (int) $attachment_id;
}

/**
 * Seed the six products supplied for the coming-soon catalogue.
 */
function medzuro_seed_coming_soon_products() {
	if ( ! class_exists( 'WC_Product_Simple' ) || '1' === get_option( 'medzuro_coming_soon_products_v1' ) ) {
		return;
	}

	$products = array(
		array( 'holyoak-marine-collagen', 'HolyOak Marine Collagen + Vitamin C', '90 capsules', 'Marine collagen with vitamin C. The packaging states 3000 mg and support for skin health.', 'holyoak-marine-collagen.jpeg' ),
		array( 'holyoak-glyvionix', 'HolyOak Glyvionix + Vitamin D3', '60 capsules', 'A 500 mg dietary supplement with vitamin D3. The packaging describes blood sugar and wellbeing support.', 'holyoak-glyvionix.jpeg' ),
		array( 'holyoak-riseup-men', 'HolyOak RiseUp Men + Vitamin B12', '60 capsules', 'A 500 mg dietary supplement with vitamin B12, presented for men\'s wellness, strength, and vitality.', 'holyoak-riseup-men.jpeg' ),
		array( 'holyoak-l-glutathione', 'HolyOak L-Glutathione + Vitamin C', '90 capsules', 'A 500 mg dietary supplement with vitamin C. The packaging describes antioxidant and natural skin support.', 'holyoak-l-glutathione.jpeg' ),
		array( 'holyoak-garcinia-trimora', 'HolyOak Garcinia Trimora + EGCG', '60 capsules', 'A 500 mg dietary supplement with EGCG, presented for healthy weight support.', 'holyoak-garcinia-trimora.jpeg' ),
		array( 'holyoak-arjun-extract-capsules', 'HolyOak Arjun Extract Capsules', '60 capsules', 'A 500 mg Terminalia arjuna dietary supplement, presented for cardiovascular and heart health support.', 'holyoak-arjun-extract-capsules.jpeg' ),
	);

	$category = term_exists( 'holyoak', 'product_cat' );
	if ( ! $category ) {
		$category = wp_insert_term( 'HolyOak', 'product_cat', array( 'slug' => 'holyoak' ) );
	}
	$category_id = is_wp_error( $category ) ? 0 : (int) ( is_array( $category ) ? $category['term_id'] : $category );

	foreach ( $products as $data ) {
		list( $slug, $name, $size, $description, $image ) = $data;
		$post = get_page_by_path( $slug, OBJECT, 'product' );
		$product = $post ? wc_get_product( $post->ID ) : new WC_Product_Simple();

		if ( ! $product ) {
			continue;
		}

		$product->set_name( $name );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_short_description( $size . ' | Coming soon at Medzuro Retail.' );
		$product->set_description( $description . ' Availability and final product information will be confirmed before sale.' );
		$product->set_manage_stock( false );
		// Keep it catalog-visible even when WooCommerce hides out-of-stock items;
		// the purchasable filter below still prevents cart and checkout access.
		$product->set_stock_status( 'instock' );
		$product->set_regular_price( '' );
		$product->set_price( '' );
		$product->update_meta_data( 'medzuro_brand', 'HolyOak' );
		$product->update_meta_data( 'medzuro_coming_soon', 'yes' );
		if ( $category_id ) {
			$product->set_category_ids( array( $category_id ) );
		}

		$product_id = $product->save();
		wp_update_post( array( 'ID' => $product_id, 'post_name' => $slug ) );

		if ( ! $product->get_image_id() ) {
			$image_id = medzuro_import_product_image( $image, $product_id, $name );
			if ( $image_id ) {
				$product->set_image_id( $image_id );
				$product->save();
			}
		}
	}

	update_option( 'medzuro_coming_soon_products_v1', '1', false );
}
add_action( 'init', 'medzuro_seed_coming_soon_products', 30 );

/**
 * Archive the exact duplicate created by concurrent first-run requests.
 */
function medzuro_archive_duplicate_trimora() {
	if ( '1' === get_option( 'medzuro_trimora_duplicate_cleanup_v1' ) ) {
		return;
	}

	$duplicate = get_page_by_path( 'holyoak-garcinia-trimora-2', OBJECT, 'product' );
	if ( $duplicate && 'yes' === get_post_meta( $duplicate->ID, 'medzuro_coming_soon', true ) ) {
		wp_update_post(
			array(
				'ID'          => $duplicate->ID,
				'post_status' => 'draft',
			)
		);
	}

	update_option( 'medzuro_trimora_duplicate_cleanup_v1', '1', false );
}
add_action( 'init', 'medzuro_archive_duplicate_trimora', 40 );

/**
 * Coming-soon products must never enter the cart.
 *
 * @param bool       $purchasable Existing purchasable state.
 * @param WC_Product $product     Product object.
 * @return bool
 */
function medzuro_coming_soon_is_purchasable( $purchasable, $product ) {
	return medzuro_is_coming_soon( $product ) ? false : $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'medzuro_coming_soon_is_purchasable', 10, 2 );

/**
 * Use a customer-facing availability label instead of "Out of stock".
 *
 * @param string     $text    Existing availability text.
 * @param WC_Product $product Product object.
 * @return string
 */
function medzuro_coming_soon_availability( $text, $product ) {
	return medzuro_is_coming_soon( $product ) ? __( 'Coming Soon', 'medzuro' ) : $text;
}
add_filter( 'woocommerce_get_availability_text', 'medzuro_coming_soon_availability', 10, 2 );
