<?php
/**
 * Footer helpers: default link columns and legal links.
 *
 * Used only when the matching menu location has no menu assigned in wp-admin,
 * so the footer is complete out of the box and still editable through Menus.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default footer columns.
 *
 * Page URLs are slug guesses; assign real menus to footer_1..footer_3 to
 * override them.
 *
 * @return array<int,array{title:string,links:array<int,array{0:string,1:string}>}>
 */
function medzuro_footer_default_columns() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

	return array(
		1 => array(
			'title' => __( 'Shop', 'medzuro' ),
			'links' => array(
				array( __( 'Shop All', 'medzuro' ), $shop ),
				array( __( 'Best Sellers', 'medzuro' ), add_query_arg( 'orderby', 'popularity', $shop ) ),
				array( __( 'New Arrivals', 'medzuro' ), add_query_arg( 'orderby', 'date', $shop ) ),
				array( __( 'Wellness Supplements', 'medzuro' ), $shop ),
			),
		),
		2 => array(
			'title' => __( 'Learn', 'medzuro' ),
			'links' => array(
				array( __( 'About Medzuro Retail', 'medzuro' ), home_url( '/about/' ) ),
				array( __( 'HolyOak', 'medzuro' ), home_url( '/holyoak/' ) ),
				array( __( 'Lab Testing & Purity', 'medzuro' ), home_url( '/lab-purity/' ) ),
				array( __( 'Product Information', 'medzuro' ), $shop ),
				array( __( 'FAQs', 'medzuro' ), home_url( '/faqs/' ) ),
			),
		),
		3 => array(
			'title' => __( 'Support', 'medzuro' ),
			'links' => array(
				array( __( 'Contact Us', 'medzuro' ), home_url( '/contact/' ) ),
				array( __( 'Delivery Information', 'medzuro' ), home_url( '/delivery-information/' ) ),
				array( __( 'Track My Order', 'medzuro' ), function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/' ) ),
				array( __( 'Returns & Refunds', 'medzuro' ), home_url( '/refund-policy/' ) ),
				array( __( 'FAQs', 'medzuro' ), home_url( '/faqs/' ) ),
			),
		),
	);
}

/**
 * Fallback for the legal menu: echo <li> links.
 */
function medzuro_footer_legal_fallback() {
	$links = array(
		array( __( 'Privacy Policy', 'medzuro' ), get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ),
		array( __( 'Refund Policy', 'medzuro' ), home_url( '/refund-policy/' ) ),
		array( __( 'Shipping Policy', 'medzuro' ), home_url( '/shipping-policy/' ) ),
		array( __( 'Terms & Conditions', 'medzuro' ), home_url( '/terms-and-conditions/' ) ),
	);

	foreach ( $links as $link ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $link[1] ), esc_html( $link[0] ) );
	}
}
