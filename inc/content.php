<?php
/**
 * Editable copy, in one place.
 *
 * The Shopify build kept these as section.settings.* so they were editable in
 * the theme editor. WooCommerce has no equivalent, so they are hardcoded here
 * rather than scattered through the templates — which keeps the option open of
 * lifting this array into the Customizer or an ACF options page later without
 * touching a single template file.
 *
 * Values seeded from config/settings_data.json of the Shopify theme.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fetch a copy string by dot path, e.g. medzuro_text( 'footer.blurb' ).
 *
 * @param string $path    Dot-delimited key path.
 * @param string $default Returned when the path is absent.
 * @return string
 */
function medzuro_text( $path, $default = '' ) {
	$node = medzuro_content();

	foreach ( explode( '.', $path ) as $key ) {
		if ( ! is_array( $node ) || ! array_key_exists( $key, $node ) ) {
			return $default;
		}
		$node = $node[ $key ];
	}

	return is_string( $node ) ? $node : $default;
}

/**
 * The full copy tree.
 *
 * @return array
 */
function medzuro_content() {
	static $content = null;

	if ( null !== $content ) {
		return $content;
	}

	$content = array(
		'brand' => array(
			'name'    => 'Medzuro Retail',
			'tagline' => 'Wellness, Now Closer to Home',
		),

		'topbar' => array(
			// settings.leftTxt / centerTxt / rightTxt — topbar was enabled.
			'left'   => '',
			'center' => 'Free delivery across Fiji on orders over $100',
			'right'  => '',
		),

		'footer' => array(
			'blurb'             => 'We bring you premium wellness products from trusted brands, delivered to your door across Fiji. Your health, our priority.',
			'badge_text'        => 'Authorized HolyOak Seller',
			'show_seller_badge' => false,
			'show_policies'     => false,
			'show_social'       => false,
			'show_payment'      => true,
			'columns'           => 3,
			'address'           => '',
			'phone'             => '',
			'email'             => '',
			// [year] is substituted at render time.
			'copyright'         => '&copy; [year] Medzuro Retail. All rights reserved.',
		),
	);

	/**
	 * Allows a child theme or a future Customizer/ACF layer to override copy
	 * without editing templates.
	 *
	 * @param array $content Copy tree.
	 */
	return $content = apply_filters( 'medzuro_content', $content );
}
