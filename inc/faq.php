<?php
/**
 * FAQ content.
 *
 * Answers are draft copy based on the delivery, pickup and payment flow built
 * into the theme; the client should confirm them. Override with the
 * 'medzuro_faq_items' filter.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array<int,array{icon:string,q:string,a:string}>
 */
function medzuro_faq_items() {
	$items = array(
		array(
			'icon' => 'shield',
			'q'    => __( 'Are these genuine HolyOak products?', 'medzuro' ),
			'a'    => __( 'Yes. Medzuro Retail supplies genuine HolyOak products in Fiji. If you have questions about product authenticity, batch details or availability, our team can assist.', 'medzuro' ),
		),
		array(
			'icon' => 'truck',
			'q'    => __( 'How soon will my order arrive?', 'medzuro' ),
			'a'    => __( 'Home delivery is sent with DHL Express and usually takes 3-7 working days across Fiji. Delivery is free within Fiji.', 'medzuro' ),
		),
		array(
			'icon' => 'store',
			'q'    => __( 'Can I pick up my order?', 'medzuro' ),
			'a'    => __( 'Yes. Choose Store Pickup at checkout to collect from Nakasi, Suva. We will let you know when your order is ready.', 'medzuro' ),
		),
		array(
			'icon' => 'pin',
			'q'    => __( 'How can I track my order?', 'medzuro' ),
			'a'    => __( 'Once your order ships with DHL Express we send you a tracking number. You can use it on the DHL website, and your order status is also shown in your account.', 'medzuro' ),
		),
		array(
			'icon' => 'card',
			'q'    => __( 'What payment methods are available?', 'medzuro' ),
			'a'    => __( 'Home delivery orders are paid online with M-PAiSA. Pickup orders can be paid in full, with a 10% deposit, or reserved without payment (reservations are not guaranteed).', 'medzuro' ),
		),
		array(
			'icon' => 'package',
			'q'    => __( 'What if I receive the wrong or damaged product?', 'medzuro' ),
			'a'    => __( 'Contact us as soon as possible with your order number and a photo of the item. Our team will help put it right.', 'medzuro' ),
		),
		array(
			'icon' => 'doc',
			'q'    => __( 'How is product quality verified?', 'medzuro' ),
			'a'    => __( 'HolyOak products are independently tested by Eurofins for purity, potency and safety. See the Lab Testing & Purity page for details; a Certificate of Analysis is available on request.', 'medzuro' ),
		),
		array(
			'icon' => 'snowflake',
			'q'    => __( 'How should I store the product?', 'medzuro' ),
			'a'    => __( 'Keep it tightly closed in a cool, dry place away from direct sunlight and out of reach of children, and follow the storage instructions on the label.', 'medzuro' ),
		),
	);

	return apply_filters( 'medzuro_faq_items', $items );
}
