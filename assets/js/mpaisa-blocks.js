/**
 * Registers the M-PAiSA gateway with the WooCommerce Blocks checkout.
 *
 * The classic WC_Payment_Gateway API (inc/mpaisa-gateway.php) is enough for
 * the legacy shortcode checkout and for WooCommerce's own Store API cart
 * data (which already lists "mpaisa" in payment_methods once the gateway is
 * enabled). It is NOT enough for the block-based Checkout block this theme
 * uses, though: that renders payment methods from a separate client-side
 * registry (wc.wcBlocksRegistry) that only knows about gateways explicitly
 * registered here. Without this file the gateway is "available" by every
 * server-side check yet invisible at checkout — exactly what happened
 * before this file existed: get_page_text on /checkout/ showed "There are
 * no payment methods available" even though wc/store/v1/cart reported
 * payment_methods: ["mpaisa"] and window.wc.wcBlocksRegistry.getPaymentMethods()
 * was empty. Built-in gateways (bacs, cheque, cod) ship this registration
 * from WooCommerce core itself, which is why bacs worked without it.
 *
 * @package Medzuro
 */

( function () {
	var settings = window.wc.wcSettings.getSetting( 'mpaisa_data', {} );
	var el = window.wp.element.createElement;
	var label = settings.title || 'M-PAiSA (Vodafone Fiji Mobile Money)';

	var Content = function () {
		return el( 'div', null, settings.description || '' );
	};

	var Label = function () {
		return el( 'span', null, label );
	};

	window.wc.wcBlocksRegistry.registerPaymentMethod( {
		name: 'mpaisa',
		label: el( Label, null ),
		content: el( Content, null ),
		edit: el( Content, null ),
		canMakePayment: function () {
			return true;
		},
		ariaLabel: label,
		supports: {
			features: settings.supports || [ 'products' ],
		},
	} );
} )();
