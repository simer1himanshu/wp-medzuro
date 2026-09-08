<?php
/**
 * Medzuro theme bootstrap.
 *
 * Ported from the Medzuro Shopify theme. Each Shopify section became a
 * template part in template-parts/ or woocommerce/, with its scoped CSS
 * extracted to assets/css/<name>.css and enqueued only where it renders.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

define( 'MEDZURO_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/content.php';
require_once get_template_directory() . '/inc/icons.php';
require_once get_template_directory() . '/inc/product.php';
require_once get_template_directory() . '/inc/collection.php';
require_once get_template_directory() . '/inc/home-content.php';
require_once get_template_directory() . '/inc/ref-icons.php';
require_once get_template_directory() . '/inc/home.php';
require_once get_template_directory() . '/inc/cart.php';

/**
 * Theme supports.
 *
 * The Shopify build shipped its own gallery (photoswipe) and variant picker.
 * We take WooCommerce's instead — it gives us the variation JS for free — and
 * restyle it, rather than porting the Shopify JS.
 */
function medzuro_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'medzuro' ),
			'footer_1' => __( 'Footer column 1', 'medzuro' ),
			'footer_2' => __( 'Footer column 2', 'medzuro' ),
			'footer_3' => __( 'Footer column 3', 'medzuro' ),
			'legal'    => __( 'Footer legal links', 'medzuro' ),
			'product_categories' => __( 'Product categories (shop filter bar)', 'medzuro' ),
		)
	);
}
add_action( 'after_setup_theme', 'medzuro_setup' );

/**
 * Enqueue one CSS file for the current template.
 *
 * Shopify inlined a <style> block per section. We keep that one-file-per-section
 * split but load it from disk so the browser can cache it.
 *
 * @param string $handle Basename of a file in assets/css/.
 */
function medzuro_style( $handle ) {
	$rel = '/assets/css/' . $handle . '.css';
	$abs = get_template_directory() . $rel;

	if ( ! file_exists( $abs ) ) {
		// Silent failure here once hid a missing stylesheet until the page
		// rendered unstyled, so say something while debugging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			trigger_error(
				esc_html( "medzuro_style(): missing assets/css/{$handle}.css" ),
				E_USER_WARNING
			);
		}

		return;
	}

	// Page templates call this before wp_enqueue_scripts fires. Defer so the
	// dependency on medzuro-base is always registered first.
	if ( ! did_action( 'wp_enqueue_scripts' ) ) {
		add_action( 'wp_enqueue_scripts', fn() => medzuro_style( $handle ), 20 );
		return;
	}

	wp_enqueue_style(
		'medzuro-' . $handle,
		get_template_directory_uri() . $rel,
		array( 'medzuro-base' ),
		filemtime( $abs )
	);
}

/**
 * Global assets. Section CSS is enqueued by the template that renders it.
 */
function medzuro_assets() {
	// Bricolage Grotesque 400 — the family configured in the Shopify build
	// (settings_data.json: header_font / body_font = bricolage_grotesque_n4).
	wp_enqueue_style(
		'medzuro-fonts',
		'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'medzuro-base', get_template_directory_uri() . '/assets/css/base.css', array( 'medzuro-fonts' ), MEDZURO_VERSION );
	wp_enqueue_style( 'medzuro-style', get_stylesheet_uri(), array( 'medzuro-base' ), MEDZURO_VERSION );

	medzuro_style( 'components' );
	medzuro_style( 'header' );
	medzuro_style( 'footer' );

	wp_enqueue_script( 'medzuro-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), MEDZURO_VERSION, true );

	if ( function_exists( 'is_product' ) && is_product() ) {
		medzuro_style( 'product-page' );
		wp_enqueue_script( 'medzuro-product', get_template_directory_uri() . '/assets/js/product.js', array(), MEDZURO_VERSION, true );
	}

	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product_taxonomy() ) ) {
		medzuro_style( 'collection-page' );
	}

	if ( is_front_page() ) {
		medzuro_style( 'home' );
		wp_enqueue_script( 'medzuro-home', get_template_directory_uri() . '/assets/js/home.js', array(), MEDZURO_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'medzuro_assets' );

/**
 * WooCommerce wrappers.
 *
 * Woo's default wrappers are replaced so archive-product.php and
 * single-product.php control their own full-width layout, as the Shopify
 * sections did.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Products per page — mirrors section.settings.products_per_page from
 * templates/collection.json.
 */
add_filter( 'loop_shop_per_page', fn() => 12, 20 );

/**
 * Cart count for the header bubble, kept in sync over Woo's AJAX fragments.
 *
 * @param array $fragments Fragments to refresh.
 * @return array
 */
function medzuro_cart_count_fragment( $fragments ) {
	ob_start();
	medzuro_cart_count();
	$fragments['#CartCount'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'medzuro_cart_count_fragment' );

/**
 * Render the cart count badge.
 */
function medzuro_cart_count() {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	printf(
		'<span id="CartCount" class="mz-hd__cart-count">%s</span>',
		esc_html( $count )
	);
}
