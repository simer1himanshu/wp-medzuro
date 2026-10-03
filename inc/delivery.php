<?php
/**
 * Delivery or pickup: shipping method, checkout fields and payment rules.
 *
 * Implements the client's "Initial Flow" (choose DHL Express home delivery or
 * Nakasi store pickup) and the rules that follow from that choice. Home
 * delivery is online payment only; pickup keeps every enabled gateway.
 *
 * After deploying, add the "Medzuro Delivery / Pickup" method to the Fiji
 * shipping zone in WooCommerce > Settings > Shipping.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shipping method that offers DHL home delivery and store pickup rates.
 */
function medzuro_register_delivery_method() {
	if ( ! class_exists( 'WC_Shipping_Method' ) || class_exists( 'Medzuro_Shipping_Delivery' ) ) {
		return;
	}

	class Medzuro_Shipping_Delivery extends WC_Shipping_Method {

		public function __construct( $instance_id = 0 ) {
			$this->id                 = 'medzuro_delivery';
			$this->instance_id        = absint( $instance_id );
			$this->method_title       = __( 'Medzuro Delivery / Pickup', 'medzuro' );
			$this->method_description = __( 'Offers DHL Express home delivery and Nakasi store pickup as two rates.', 'medzuro' );
			$this->supports           = array( 'shipping-zones', 'instance-settings' );

			$this->init_instance_form_fields();
			$this->init_settings();

			$this->title = $this->get_option( 'title', $this->method_title );

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_instance_form_fields() {
			$this->instance_form_fields = array(
				'title'          => array(
					'title'   => __( 'Method title', 'medzuro' ),
					'type'    => 'text',
					'default' => __( 'Delivery / Pickup', 'medzuro' ),
				),
				'dhl_enabled'    => array(
					'title'   => __( 'Home delivery', 'medzuro' ),
					'type'    => 'checkbox',
					'label'   => __( 'Offer DHL Express home delivery', 'medzuro' ),
					'default' => 'yes',
				),
				'dhl_cost'       => array(
					'title'       => __( 'DHL cost', 'medzuro' ),
					'type'        => 'price',
					'description' => __( 'Leave at 0 for free shipping.', 'medzuro' ),
					'default'     => '0',
					'desc_tip'    => true,
				),
				'dhl_eta'        => array(
					'title'   => __( 'DHL delivery time', 'medzuro' ),
					'type'    => 'text',
					'default' => __( '3-7 working days', 'medzuro' ),
				),
				'pickup_enabled' => array(
					'title'   => __( 'Pickup', 'medzuro' ),
					'type'    => 'checkbox',
					'label'   => __( 'Offer store pickup', 'medzuro' ),
					'default' => 'yes',
				),
				'pickup_address' => array(
					'title'   => __( 'Pickup location', 'medzuro' ),
					'type'    => 'text',
					'default' => __( 'Nakasi, Suva', 'medzuro' ),
				),
			);
		}

		public function calculate_shipping( $package = array() ) {
			if ( 'yes' === $this->get_option( 'dhl_enabled', 'yes' ) ) {
				$this->add_rate(
					array(
						'id'        => $this->get_rate_id( 'dhl' ),
						'label'     => __( 'Home Delivery (DHL Express)', 'medzuro' ),
						'cost'      => (float) $this->get_option( 'dhl_cost', 0 ),
						'meta_data' => array(
							'type' => 'delivery',
							'note' => $this->get_option( 'dhl_eta' ),
						),
					)
				);
			}

			if ( 'yes' === $this->get_option( 'pickup_enabled', 'yes' ) ) {
				$this->add_rate(
					array(
						'id'        => $this->get_rate_id( 'pickup' ),
						'label'     => __( 'Store Pickup', 'medzuro' ),
						'cost'      => 0,
						'meta_data' => array(
							'type' => 'pickup',
							'note' => $this->get_option( 'pickup_address' ),
						),
					)
				);
			}
		}
	}
}
add_action( 'woocommerce_shipping_init', 'medzuro_register_delivery_method' );

/**
 * @param array $methods Registered shipping methods.
 * @return array
 */
function medzuro_add_delivery_method( $methods ) {
	$methods['medzuro_delivery'] = 'Medzuro_Shipping_Delivery';
	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'medzuro_add_delivery_method' );

/**
 * Whether the customer has chosen pickup. Null when nothing is chosen yet.
 *
 * @return bool|null
 */
function medzuro_chosen_is_pickup() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return null;
	}

	$chosen = (array) WC()->session->get( 'chosen_shipping_methods', array() );
	$rate   = reset( $chosen );

	if ( ! $rate || 0 !== strpos( $rate, 'medzuro_delivery' ) ) {
		return null;
	}

	return false !== strpos( $rate, 'pickup' );
}

/**
 * Show each rate as a two-line choice: title plus time/location and price.
 *
 * @param string          $label  Default label.
 * @param WC_Shipping_Rate $method Rate.
 * @return string
 */
function medzuro_shipping_rate_label( $label, $method ) {
	if ( 0 !== strpos( $method->get_id(), 'medzuro_delivery' ) ) {
		return $label;
	}

	$meta = $method->get_meta_data();
	$note = isset( $meta['note'] ) ? $meta['note'] : '';
	$cost = (float) $method->get_cost();
	$out  = '<span class="mz-rate"><strong class="mz-rate__title">' . esc_html( $method->get_label() ) . '</strong>';

	if ( $note ) {
		$out .= '<small class="mz-rate__note">' . esc_html( $note ) . '</small>';
	}

	$out .= '<span class="mz-rate__price">'
		. ( $cost > 0 ? wp_kses_post( wc_price( $cost ) ) : esc_html__( 'FREE', 'medzuro' ) )
		. '</span></span>';

	return $out;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'medzuro_shipping_rate_label', 10, 2 );

/**
 * Always show a customer a shipping choice, never an auto-picked one hidden in totals.
 */
add_filter( 'woocommerce_cart_ready_to_calc_shipping', '__return_true' );

/**
 * Fiji divisions for the Province field.
 *
 * @param array $states Country => states.
 * @return array
 */
function medzuro_fiji_states( $states ) {
	$states['FJ'] = array(
		'C' => __( 'Central', 'medzuro' ),
		'W' => __( 'Western', 'medzuro' ),
		'N' => __( 'Northern', 'medzuro' ),
		'E' => __( 'Eastern', 'medzuro' ),
	);
	return $states;
}
add_filter( 'woocommerce_states', 'medzuro_fiji_states' );

/**
 * Checkout fields: Fiji address labels, +679 mobile and contact preference.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function medzuro_checkout_fields( $fields ) {
	$b = &$fields['billing'];

	$b['billing_phone']['label']       = __( 'Mobile number', 'medzuro' );
	$b['billing_phone']['placeholder'] = '+679 1234567';
	$b['billing_phone']['required']    = true;

	$b['billing_address_1']['label']       = __( 'House/Unit No. and Street', 'medzuro' );
	$b['billing_address_1']['placeholder'] = __( '12 Main Street', 'medzuro' );
	$b['billing_address_2']['label']       = __( 'Area/Suburb', 'medzuro' );
	$b['billing_address_2']['placeholder'] = __( 'Nakasi', 'medzuro' );
	$b['billing_address_2']['required']    = true;
	$b['billing_state']['label']           = __( 'Province', 'medzuro' );

	$b['billing_contact_method'] = array(
		'type'     => 'radio',
		'label'    => __( 'Preferred contact method', 'medzuro' ),
		'required' => true,
		'default'  => 'viber',
		'class'    => array( 'form-row-wide', 'mz-contact-method' ),
		'options'  => array(
			'viber' => __( 'Viber', 'medzuro' ),
			'phone' => __( 'Phone', 'medzuro' ),
			'email' => __( 'Email', 'medzuro' ),
		),
		'priority' => 125,
	);

	$fields['order']['order_comments']['label']       = __( 'Delivery instructions (optional)', 'medzuro' );
	$fields['order']['order_comments']['placeholder'] = __( 'e.g. Leave with reception, call before delivery', 'medzuro' );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'medzuro_checkout_fields' );

/**
 * Pickup orders need no street address; delivery orders do.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function medzuro_relax_address_for_pickup( $fields ) {
	if ( true === medzuro_chosen_is_pickup() ) {
		foreach ( array( 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode' ) as $key ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['required'] = false;
			}
		}
	}
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'medzuro_relax_address_for_pickup', 20 );

/**
 * Validate the Fiji mobile number (7 digits, optional +679).
 */
function medzuro_validate_checkout() {
	$phone  = isset( $_POST['billing_phone'] ) ? wc_clean( wp_unslash( $_POST['billing_phone'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	$digits = preg_replace( '/\D+/', '', $phone );

	if ( '' !== $digits && 0 === strpos( $digits, '679' ) ) {
		$digits = substr( $digits, 3 );
	}

	if ( '' !== $phone && 7 !== strlen( $digits ) ) {
		wc_add_notice( __( 'Please enter a valid Fiji mobile number, e.g. +679 1234567.', 'medzuro' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'medzuro_validate_checkout' );

/**
 * Save the contact preference on the order.
 *
 * @param WC_Order $order Order.
 */
function medzuro_save_contact_method( $order ) {
	$method = isset( $_POST['billing_contact_method'] ) ? sanitize_key( wp_unslash( $_POST['billing_contact_method'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( in_array( $method, array( 'viber', 'phone', 'email' ), true ) ) {
		$order->update_meta_data( '_billing_contact_method', $method );
	}
}
add_action( 'woocommerce_checkout_create_order', 'medzuro_save_contact_method' );

/**
 * Show the contact preference in the admin order screen.
 *
 * @param WC_Order $order Order.
 */
function medzuro_show_contact_method( $order ) {
	$method = $order->get_meta( '_billing_contact_method' );
	if ( $method ) {
		echo '<p><strong>' . esc_html__( 'Preferred contact:', 'medzuro' ) . '</strong> ' . esc_html( ucfirst( $method ) ) . '</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'medzuro_show_contact_method' );

/**
 * Payment rules: home delivery is online payment only.
 *
 * Pickup keeps every enabled gateway so pay-in-store or reserve options can
 * be added later. Offline gateways are removed for delivery orders.
 *
 * @param array $gateways Available gateways.
 * @return array
 */
function medzuro_gateways_by_delivery( $gateways ) {
	if ( is_admin() || false !== medzuro_chosen_is_pickup() ) {
		return $gateways;
	}

	foreach ( array( 'cod', 'cheque', 'bacs' ) as $offline ) {
		unset( $gateways[ $offline ] );
	}

	return $gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'medzuro_gateways_by_delivery' );
