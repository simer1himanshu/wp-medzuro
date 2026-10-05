<?php
/**
 * Multi-step checkout: Delivery -> Details -> Payment.
 *
 * The client's flow (cart choice cards, a stepped checkout, Fiji fields,
 * pickup payment options) can't be built on WooCommerce's block checkout,
 * which has no multi-step mode and ignores the classic field hooks. So the
 * cart and checkout pages are rendered with the classic shortcodes through
 * this theme's own page shell, whatever blocks the pages contain in the
 * editor. Removing this file's template_include filter restores the blocks.
 *
 * Templates: woocommerce/mz-page-shell.php, woocommerce/cart/*,
 * woocommerce/checkout/*. Script: assets/js/checkout-steps.js.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Classic cart + checkout
 * ---------------------------------------------------------------------- */

/**
 * Render cart and checkout through the theme shell (classic shortcodes).
 *
 * @param string $template Template path.
 * @return string
 */
function medzuro_classic_cart_checkout( $template ) {
	if ( ! function_exists( 'is_cart' ) ) {
		return $template;
	}

	if ( is_cart() || is_checkout() ) {
		$shell = get_theme_file_path( 'woocommerce/mz-page-shell.php' );
		if ( file_exists( $shell ) ) {
			return $shell;
		}
	}

	return $template;
}
add_filter( 'template_include', 'medzuro_classic_cart_checkout', 50 );

/**
 * The shortcode for the current page.
 *
 * @return string
 */
function medzuro_cart_checkout_shortcode() {
	return is_cart() ? '[woocommerce_cart]' : '[woocommerce_checkout]';
}

/**
 * Checkout scripts: WooCommerce's classic checkout.js plus the step script.
 *
 * The checkout page still contains the Checkout block in the editor, and
 * WooCommerce dequeues the classic "wc-checkout" script whenever that block
 * is processed. Both scripts are therefore (re-)enqueued late, and again in
 * the footer, so the classic form always has its JavaScript.
 */
function medzuro_checkout_assets() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) {
		return;
	}

	if ( wp_script_is( 'wc-checkout', 'registered' ) ) {
		wp_enqueue_script( 'wc-checkout' );
	}

	if ( wp_script_is( 'medzuro-checkout-steps', 'enqueued' ) ) {
		return;
	}

	$path = get_theme_file_path( '/assets/js/checkout-steps.js' );

	wp_enqueue_script(
		'medzuro-checkout-steps',
		get_template_directory_uri() . '/assets/js/checkout-steps.js',
		array( 'jquery' ),
		file_exists( $path ) ? filemtime( $path ) : MEDZURO_VERSION,
		true
	);

	wp_localize_script(
		'medzuro-checkout-steps',
		'medzuroCheckout',
		array(
			'required' => __( 'Please fill in this field.', 'medzuro' ),
			'phone'    => __( 'Enter a 7-digit Fiji mobile number.', 'medzuro' ),
			'email'    => __( 'Enter a valid email address.', 'medzuro' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'medzuro_checkout_assets', 999 );
add_action( 'wp_footer', 'medzuro_checkout_assets', 1 );

/**
 * Stop the Checkout/Cart blocks' scripts from dequeuing the classic ones.
 *
 * Runs after WooCommerce's block classes have hooked their dequeue callback
 * (priority 20) and removes it, since those blocks are never rendered here.
 */
function medzuro_keep_classic_scripts() {
	if ( ! function_exists( 'is_checkout' ) || ! ( is_checkout() || is_cart() ) ) {
		return;
	}

	global $wp_filter;
	if ( empty( $wp_filter['wp_enqueue_scripts'] ) ) {
		return;
	}

	foreach ( (array) $wp_filter['wp_enqueue_scripts']->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $cb ) {
			if ( is_array( $cb['function'] ) && is_object( $cb['function'][0] ) && 'dequeue_woocommerce_core_scripts' === $cb['function'][1] ) {
				remove_action( 'wp_enqueue_scripts', $cb['function'], $priority );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'medzuro_keep_classic_scripts', 15 );

/**
 * Shipping goes to the billing (delivery) address; there is no second form.
 */
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

/**
 * Fiji is the only destination.
 */
add_filter( 'default_checkout_billing_country', fn() => 'FJ' );

/**
 * The cart always shows the delivery choice, not an address calculator.
 */
add_filter( 'woocommerce_cart_ready_to_calc_shipping', '__return_true' );
add_filter( 'woocommerce_shipping_show_shipping_calculator', '__return_false' );

/**
 * Tag cart packages so rates cached in customers' sessions before this flow
 * went live are recalculated (and pass through medzuro_only_delivery_rates).
 *
 * @param array $packages Packages.
 * @return array
 */
function medzuro_shipping_package_version( $packages ) {
	foreach ( $packages as $i => $package ) {
		$packages[ $i ]['medzuro_rates'] = 2;
	}
	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', 'medzuro_shipping_package_version' );

/* -------------------------------------------------------------------------
 * Fields
 * ---------------------------------------------------------------------- */

/**
 * Fiji cities/towns for the City field.
 *
 * @return array
 */
function medzuro_fiji_towns() {
	$towns = array( 'Suva', 'Nasinu', 'Nausori', 'Lami', 'Navua', 'Lautoka', 'Nadi', 'Ba', 'Tavua', 'Rakiraki', 'Sigatoka', 'Labasa', 'Savusavu', 'Levuka', 'Korovou', 'Other' );
	return apply_filters( 'medzuro_fiji_towns', array_combine( $towns, $towns ) );
}

/**
 * Checkout fields matching the client's screens.
 *
 * Details step: full name, +679 mobile, email, preferred contact method.
 * Delivery address step: house/unit no., street, area/suburb, city, province,
 * delivery instructions.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function medzuro_checkout_fields( $fields ) {
	$b = $fields['billing'];

	$billing = array(
		'billing_first_name'     => array_merge(
			$b['billing_first_name'],
			array(
				'label'        => __( 'Full name', 'medzuro' ),
				'placeholder'  => __( 'John Doe', 'medzuro' ),
				'class'        => array( 'form-row-wide' ),
				'autocomplete' => 'name',
				'priority'     => 10,
			)
		),
		'billing_phone'          => array(
			'type'         => 'tel',
			'label'        => __( 'Mobile number', 'medzuro' ),
			'placeholder'  => '1234567',
			'required'     => true,
			'class'        => array( 'form-row-wide', 'mz-phone' ),
			'validate'     => array( 'phone' ),
			'autocomplete' => 'tel-national',
			'priority'     => 20,
		),
		'billing_email'          => array_merge(
			$b['billing_email'],
			array(
				'label'       => __( 'Email address', 'medzuro' ),
				'placeholder' => 'john.doe@email.com',
				'class'       => array( 'form-row-wide' ),
				'priority'    => 30,
			)
		),
		'billing_contact_method' => array(
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
			'priority' => 40,
		),
		'billing_country'        => array(
			'type'     => 'hidden',
			'default'  => 'FJ',
			'required' => true,
			'priority' => 45,
		),
		'billing_house_no'       => array(
			'label'       => __( 'House/Unit No.', 'medzuro' ),
			'placeholder' => '12',
			'required'    => true,
			'class'       => array( 'form-row-first', 'address-field' ),
			'priority'    => 50,
		),
		'billing_address_1'      => array(
			'label'        => __( 'Street', 'medzuro' ),
			'placeholder'  => __( 'Main Street', 'medzuro' ),
			'required'     => true,
			'class'        => array( 'form-row-last', 'address-field' ),
			'autocomplete' => 'address-line1',
			'priority'     => 60,
		),
		'billing_address_2'      => array(
			'label'        => __( 'Area/Suburb', 'medzuro' ),
			'placeholder'  => __( 'Nakasi', 'medzuro' ),
			'required'     => true,
			'class'        => array( 'form-row-wide', 'address-field' ),
			'autocomplete' => 'address-line2',
			'priority'     => 70,
		),
		'billing_city'           => array(
			'type'        => 'select',
			'label'       => __( 'City', 'medzuro' ),
			'required'    => true,
			'class'       => array( 'form-row-first', 'address-field', 'update_totals_on_change' ),
			'options'     => array( '' => __( 'Select city', 'medzuro' ) ) + medzuro_fiji_towns(),
			'default'     => 'Suva',
			'priority'    => 80,
		),
		'billing_state'          => array(
			'type'     => 'select',
			'label'    => __( 'Province', 'medzuro' ),
			'required' => true,
			'class'    => array( 'form-row-last', 'address-field', 'update_totals_on_change' ),
			'options'  => array( '' => __( 'Select province', 'medzuro' ) ) + medzuro_fiji_states( array() )['FJ'],
			'default'  => 'C',
			'validate' => array( 'state' ),
			'priority' => 90,
		),
	);

	$fields['billing'] = $billing;

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Delivery instructions', 'medzuro' );
		$fields['order']['order_comments']['placeholder'] = __( 'e.g. Leave with reception, call before delivery', 'medzuro' );
	}

	// Pickup orders need no street address.
	if ( true === medzuro_chosen_is_pickup() ) {
		foreach ( array( 'billing_house_no', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state' ) as $key ) {
			$fields['billing'][ $key ]['required'] = false;
		}
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'medzuro_checkout_fields', 20 );

/**
 * Field keys rendered in each checkout panel.
 *
 * @return array
 */
function medzuro_checkout_panel_fields() {
	return array(
		'details' => array( 'billing_first_name', 'billing_phone', 'billing_email', 'billing_contact_method', 'billing_country' ),
		'address' => array( 'billing_house_no', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state' ),
	);
}

/**
 * Normalise posted data: split the full name, join house no. + street,
 * format the mobile as +679, force Fiji.
 *
 * @param array $data Posted data.
 * @return array
 */
function medzuro_checkout_posted_data( $data ) {
	if ( ! empty( $data['billing_first_name'] ) ) {
		$parts                      = preg_split( '/\s+/', trim( $data['billing_first_name'] ), 2 );
		$data['billing_first_name'] = $parts[0];
		$data['billing_last_name']  = isset( $parts[1] ) ? $parts[1] : '';
	}

	if ( ! empty( $data['billing_house_no'] ) && ! empty( $data['billing_address_1'] ) ) {
		$data['billing_address_1'] = trim( $data['billing_house_no'] ) . ' ' . trim( $data['billing_address_1'] );
	}

	if ( ! empty( $data['billing_phone'] ) ) {
		$digits = preg_replace( '/\D+/', '', $data['billing_phone'] );
		if ( 0 === strpos( $digits, '679' ) && strlen( $digits ) > 7 ) {
			$digits = substr( $digits, 3 );
		}
		$data['billing_phone'] = '+679 ' . $digits;
	}

	$data['billing_country'] = 'FJ';

	// Pickup orders carry the store as their address.
	if ( true === medzuro_chosen_is_pickup() ) {
		$data['billing_city']  = empty( $data['billing_city'] ) ? 'Suva' : $data['billing_city'];
		$data['billing_state'] = empty( $data['billing_state'] ) ? 'C' : $data['billing_state'];
	}

	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'medzuro_checkout_posted_data' );

/**
 * Drop WooCommerce's "Billing" prefix from error messages.
 *
 * @param string $notice Notice.
 * @return string
 */
function medzuro_required_notice( $notice ) {
	return str_replace( '<strong>Billing ', '<strong>', $notice );
}
add_filter( 'woocommerce_checkout_required_field_notice', 'medzuro_required_notice' );

/**
 * Validate the mobile number and the delivery payment rule.
 *
 * @param array    $data   Posted data.
 * @param WP_Error $errors Errors.
 */
function medzuro_validate_checkout( $data, $errors ) {
	$digits = preg_replace( '/\D+/', '', isset( $data['billing_phone'] ) ? $data['billing_phone'] : '' );
	if ( 0 === strpos( $digits, '679' ) ) {
		$digits = substr( $digits, 3 );
	}
	if ( 7 !== strlen( $digits ) ) {
		$errors->add( 'billing_phone_validation', __( 'Please enter a valid Fiji mobile number, e.g. +679 1234567.', 'medzuro' ), array( 'id' => 'billing_phone' ) );
	}

	$method = isset( $data['billing_contact_method'] ) ? $data['billing_contact_method'] : '';
	if ( ! in_array( $method, array( 'viber', 'phone', 'email' ), true ) ) {
		$errors->add( 'billing_contact_method', __( 'Please choose how we should contact you.', 'medzuro' ) );
	}

	$gateway = isset( $data['payment_method'] ) ? $data['payment_method'] : '';
	$option  = medzuro_pickup_payment_choice();

	if ( false === medzuro_chosen_is_pickup() && 'mpaisa' !== $gateway ) {
		$errors->add( 'payment', __( 'Home delivery orders must be paid online with M-PAiSA.', 'medzuro' ) );
	}

	if ( true === medzuro_chosen_is_pickup() ) {
		if ( '' === medzuro_posted_pickup_store() ) {
			$errors->add( 'mz_pickup_store', __( 'Please choose a pickup location.', 'medzuro' ) );
		}
		if ( 'reserve' === $option && 'medzuro_reserve' !== $gateway ) {
			$errors->add( 'payment', __( 'Please choose "Reserve without payment" again.', 'medzuro' ) );
		}
		if ( 'reserve' !== $option && 'medzuro_reserve' === $gateway ) {
			$errors->add( 'payment', __( 'Please choose a payment method.', 'medzuro' ) );
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', 'medzuro_validate_checkout', 10, 2 );

/**
 * Save the contact preference and delivery details on the order.
 *
 * @param WC_Order $order Order.
 * @param array    $data  Posted data.
 */
function medzuro_save_checkout_meta( $order, $data ) {
	$method = isset( $data['billing_contact_method'] ) ? sanitize_key( $data['billing_contact_method'] ) : '';
	if ( in_array( $method, array( 'viber', 'phone', 'email' ), true ) ) {
		$order->update_meta_data( '_billing_contact_method', $method );
	}

	$is_pickup = medzuro_chosen_is_pickup();
	$order->update_meta_data( '_mz_fulfilment', $is_pickup ? 'pickup' : 'delivery' );

	if ( $is_pickup ) {
		$store = medzuro_pickup_store( medzuro_posted_pickup_store() );
		$order->update_meta_data( '_mz_pickup_store', $store['id'] );

		$option = medzuro_pickup_payment_choice();
		$order->update_meta_data( '_mz_pickup_payment', $option );

		if ( 'deposit' === $option ) {
			$order->update_meta_data( '_mz_deposit_amount', medzuro_deposit_amount( (float) $order->get_total() ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verified the checkout nonce.
		$note = isset( $_POST['mz_pickup_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mz_pickup_note'] ) ) : '';
		if ( $note ) {
			$order->set_customer_note( __( 'Pickup instructions:', 'medzuro' ) . ' ' . $note );
		}
	}
}
add_action( 'woocommerce_checkout_create_order', 'medzuro_save_checkout_meta', 10, 2 );

/**
 * Show the contact preference and fulfilment in the admin order screen.
 *
 * @param WC_Order $order Order.
 */
function medzuro_admin_order_details( $order ) {
	$method = $order->get_meta( '_billing_contact_method' );
	if ( $method ) {
		echo '<p><strong>' . esc_html__( 'Preferred contact:', 'medzuro' ) . '</strong> ' . esc_html( ucfirst( $method ) ) . '</p>';
	}

	if ( medzuro_order_is_pickup( $order ) ) {
		$store = medzuro_order_pickup_store( $order );
		echo '<p><strong>' . esc_html__( 'Pickup location:', 'medzuro' ) . '</strong> ' . esc_html( $store['name'] ) . '<br />' . esc_html( $store['street'] ) . '</p>';
	}

	$option = $order->get_meta( '_mz_pickup_payment' );
	if ( $option ) {
		$labels = medzuro_pickup_payment_options();
		echo '<p><strong>' . esc_html__( 'Pickup payment:', 'medzuro' ) . '</strong> ' . esc_html( isset( $labels[ $option ] ) ? $labels[ $option ]['title'] : $option );
		if ( 'deposit' === $option ) {
			echo ' &ndash; ' . wp_kses_post( wc_price( (float) $order->get_meta( '_mz_deposit_amount' ) ) );
		}
		echo '</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'medzuro_admin_order_details' );

/**
 * Hide the rate's internal meta (type/note) from order item displays.
 *
 * @param array $hidden Hidden meta keys.
 * @return array
 */
function medzuro_hide_rate_meta( $hidden ) {
	return array_merge( $hidden, array( 'type', 'note', 'subnote' ) );
}
add_filter( 'woocommerce_hidden_order_itemmeta', 'medzuro_hide_rate_meta' );

/**
 * Same, for the customer-facing order screens and emails.
 *
 * @param array         $meta Formatted meta.
 * @param WC_Order_Item $item Item.
 * @return array
 */
function medzuro_hide_rate_meta_front( $meta, $item ) {
	if ( ! $item instanceof WC_Order_Item_Shipping ) {
		return $meta;
	}
	return array_filter( $meta, fn( $m ) => ! in_array( $m->key, array( 'type', 'note', 'subnote' ), true ) );
}
add_filter( 'woocommerce_order_item_get_formatted_meta_data', 'medzuro_hide_rate_meta_front', 10, 2 );

/* -------------------------------------------------------------------------
 * Pickup payment options
 * ---------------------------------------------------------------------- */

/**
 * Pickup payment options, in display order.
 *
 * @return array
 */
function medzuro_pickup_payment_options() {
	return array(
		'full'    => array(
			'title'   => __( 'Full Payment (Recommended)', 'medzuro' ),
			'short'   => __( 'Full payment', 'medzuro' ),
			'badge'   => __( 'Recommended', 'medzuro' ),
			'icon'    => 'card',
			'desc'    => __( 'Pay the full amount now and confirm your order.', 'medzuro' ),
			'bullets' => array( __( 'Order reserved immediately', 'medzuro' ), __( 'Fast pickup', 'medzuro' ), __( 'No pending balance', 'medzuro' ) ),
		),
		'deposit' => array(
			'title'   => __( 'Pay 10% and Reserve', 'medzuro' ),
			'short'   => __( '10% payment', 'medzuro' ),
			'badge'   => '',
			'icon'    => 'coin',
			'desc'    => __( 'Pay just 10% now to reserve your order.', 'medzuro' ),
			'bullets' => array( __( 'Reserve your order', 'medzuro' ), __( 'Pay remaining later', 'medzuro' ), __( 'Stock will be kept for you', 'medzuro' ) ),
		),
		'reserve' => array(
			'title'   => __( 'Reserve Without Payment', 'medzuro' ),
			'short'   => __( 'Reserve without payment', 'medzuro' ),
			'badge'   => '',
			'icon'    => 'calendar',
			'desc'    => __( 'Place order request without payment.', 'medzuro' ),
			'bullets' => array( __( 'We cannot guarantee reservation', 'medzuro' ), __( 'Subject to stock availability', 'medzuro' ), __( 'Our team will contact you', 'medzuro' ) ),
		),
	);
}

/**
 * Deposit percentage for "10% payment to reserve".
 *
 * @return float
 */
function medzuro_deposit_rate() {
	return (float) apply_filters( 'medzuro_deposit_rate', 0.10 );
}

/**
 * Deposit amount for a total.
 *
 * @param float $total Order/cart total.
 * @return float
 */
function medzuro_deposit_amount( $total ) {
	return round( max( 0.01, $total * medzuro_deposit_rate() ), wc_get_price_decimals() );
}

/**
 * The customer's pickup payment choice (from the request, else session).
 *
 * @return string full|deposit|reserve
 */
function medzuro_pickup_payment_choice() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$posted = isset( $_POST['mz_pickup_payment'] ) ? sanitize_key( wp_unslash( $_POST['mz_pickup_payment'] ) ) : '';
	$choice = $posted ? $posted : ( function_exists( 'WC' ) && WC()->session ? WC()->session->get( 'mz_pickup_payment' ) : '' );

	return array_key_exists( (string) $choice, medzuro_pickup_payment_options() ) ? $choice : 'full';
}

/**
 * Remember the pickup payment choice whenever the order review refreshes.
 *
 * @param string $post_data Serialized checkout form.
 */
function medzuro_store_pickup_choice( $post_data ) {
	parse_str( (string) $post_data, $form );

	if ( isset( $form['mz_pickup_payment'] ) && WC()->session ) {
		$choice = sanitize_key( $form['mz_pickup_payment'] );
		if ( array_key_exists( $choice, medzuro_pickup_payment_options() ) ) {
			WC()->session->set( 'mz_pickup_payment', $choice );
			$_POST['mz_pickup_payment'] = $choice; // So gateway filtering in this request sees it.
		}
	}

	if ( isset( $form['mz_pickup_store'] ) && WC()->session ) {
		$store = sanitize_key( $form['mz_pickup_store'] );
		if ( array_key_exists( $store, medzuro_pickup_stores() ) ) {
			WC()->session->set( 'mz_pickup_store', $store );
		}
	}
}
add_action( 'woocommerce_checkout_update_order_review', 'medzuro_store_pickup_choice' );

/**
 * Same, on the final checkout submit.
 */
function medzuro_store_pickup_choice_on_submit() {
	if ( WC()->session ) {
		WC()->session->set( 'mz_pickup_payment', medzuro_pickup_payment_choice() );
	}
}
add_action( 'woocommerce_checkout_process', 'medzuro_store_pickup_choice_on_submit', 5 );

/**
 * Payment rules.
 *
 * Home delivery: online payment only (M-PAiSA).
 * Pickup, full or 10%: M-PAiSA.
 * Pickup, reserve: "Reserve without payment" only.
 *
 * @param array $gateways Available gateways.
 * @return array
 */
function medzuro_gateways_by_delivery( $gateways ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $gateways;
	}

	// Order-pay page (retrying a failed payment): online only.
	if ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) {
		return array_intersect_key( $gateways, array( 'mpaisa' => true ) );
	}

	$pickup = medzuro_chosen_is_pickup();

	if ( true === $pickup && 'reserve' === medzuro_pickup_payment_choice() ) {
		return array_intersect_key( $gateways, array( 'medzuro_reserve' => true ) );
	}

	unset( $gateways['medzuro_reserve'] );

	if ( null === $pickup ) {
		return $gateways;
	}

	return array_intersect_key( $gateways, array( 'mpaisa' => true ) );
}
add_filter( 'woocommerce_available_payment_gateways', 'medzuro_gateways_by_delivery', 20 );

/**
 * "Pay now" / "Balance at pickup" rows under the order total.
 */
function medzuro_review_deposit_rows() {
	if ( true !== medzuro_chosen_is_pickup() ) {
		return;
	}

	$total  = (float) WC()->cart->get_total( 'edit' );
	$option = medzuro_pickup_payment_choice();

	if ( 'deposit' === $option ) {
		$deposit = medzuro_deposit_amount( $total );
		printf(
			'<tr class="mz-pay-now"><th>%1$s</th><td>%2$s</td></tr><tr class="mz-balance"><th>%3$s</th><td>%4$s</td></tr>',
			esc_html__( 'Pay now (10%)', 'medzuro' ),
			wp_kses_post( wc_price( $deposit ) ),
			esc_html__( 'Balance at pickup', 'medzuro' ),
			wp_kses_post( wc_price( $total - $deposit ) )
		);
	} elseif ( 'reserve' === $option ) {
		printf(
			'<tr class="mz-pay-now"><th>%1$s</th><td>%2$s</td></tr>',
			esc_html__( 'Pay at pickup', 'medzuro' ),
			wp_kses_post( wc_price( $total ) )
		);
	}
}
add_action( 'woocommerce_review_order_after_order_total', 'medzuro_review_deposit_rows' );

/**
 * Plain-text price for button labels ("Pay $49.99 now").
 *
 * @param float $amount Amount.
 * @return string
 */
function medzuro_plain_price( $amount ) {
	return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' );
}

/**
 * What the customer pays now, given delivery/pickup and the pickup option.
 *
 * @return array{option:string, total:float, now:float, later:float}
 */
function medzuro_checkout_amounts() {
	$total  = WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0.0;
	$option = true === medzuro_chosen_is_pickup() ? medzuro_pickup_payment_choice() : 'delivery';
	$now    = $total;

	if ( 'deposit' === $option ) {
		$now = medzuro_deposit_amount( $total );
	} elseif ( 'reserve' === $option ) {
		$now = 0.0;
	}

	return array(
		'option' => $option,
		'total'  => $total,
		'now'    => $now,
		'later'  => max( 0, $total - $now ),
	);
}

/**
 * Place-order button: "Pay $X now", or "Confirm order request" when
 * reserving without payment (designs 7A, 7B, 7C).
 *
 * @return string
 */
function medzuro_order_button_text() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return __( 'Continue to payment', 'medzuro' );
	}

	$a = medzuro_checkout_amounts();
	if ( 'reserve' === $a['option'] ) {
		return __( 'Confirm order request', 'medzuro' );
	}

	/* translators: %s: amount */
	return sprintf( __( 'Pay %s now', 'medzuro' ), medzuro_plain_price( $a['now'] ) );
}
add_filter( 'woocommerce_order_button_text', 'medzuro_order_button_text' );

/**
 * Amount / notice boxes and trust icons above the button. Rendered inside
 * WooCommerce's payment fragment so they refresh with every order update.
 */
function medzuro_payment_summary() {
	if ( ! is_checkout() && ! wp_doing_ajax() ) {
		return;
	}

	$a     = medzuro_checkout_amounts();
	$trust = array(
		array( 'shield', __( 'Secure payment', 'medzuro' ) ),
		array( 'bolt', __( 'Instant confirmation', 'medzuro' ) ),
		array( 'check', __( 'Order reserved immediately', 'medzuro' ) ),
	);

	echo '<div class="mz-pay-summary mz-pay-summary--' . esc_attr( $a['option'] ) . '" data-option="' . esc_attr( $a['option'] ) . '">';

	if ( 'reserve' === $a['option'] ) {
		echo '<div class="mz-notice mz-notice--warn">';
		echo '<span class="mz-notice__icon">';
		medzuro_icon( 'alert', 30 );
		echo '</span><h3>' . esc_html__( 'Important notice', 'medzuro' ) . '</h3>';
		echo '<p class="mz-notice__lead">' . esc_html__( 'Your order has not been reserved.', 'medzuro' ) . '</p>';
		echo '<p>' . esc_html__( 'We have received your order request, but we cannot guarantee stock availability without payment. Our team will contact you to confirm availability and payment.', 'medzuro' ) . '</p>';
		echo '</div>';
		echo '<div class="mz-notice mz-notice--note"><strong>' . esc_html__( 'Please note:', 'medzuro' ) . '</strong><ul>';
		echo '<li>' . esc_html__( 'Stock is subject to availability.', 'medzuro' ) . '</li>';
		echo '<li>' . esc_html__( 'Your order will be confirmed after payment.', 'medzuro' ) . '</li>';
		echo '<li>' . esc_html__( 'Our team will contact you on your mobile or Viber.', 'medzuro' ) . '</li>';
		echo '</ul></div>';
		echo '</div>';
		return;
	}

	if ( 'deposit' === $a['option'] ) {
		echo '<div class="mz-amount"><h3>' . esc_html__( 'Payment details', 'medzuro' ) . '</h3>';
		echo '<div class="mz-amount__row"><span>' . esc_html__( 'Total amount', 'medzuro' ) . '</span><strong>' . wp_kses_post( wc_price( $a['total'] ) ) . '</strong></div>';
		echo '<div class="mz-amount__row mz-amount__row--hl"><span>' . esc_html__( '10% to reserve', 'medzuro' ) . '</span><strong>' . wp_kses_post( wc_price( $a['now'] ) ) . '</strong></div>';
		echo '<div class="mz-amount__row"><span>' . esc_html__( 'Remaining balance', 'medzuro' ) . '</span><strong>' . wp_kses_post( wc_price( $a['later'] ) ) . '</strong></div>';
		echo '<p>' . esc_html__( 'You can pay the remaining amount later, before or at pickup.', 'medzuro' ) . '</p></div>';
		$trust = array(
			array( 'calendar', __( 'Reserve your order', 'medzuro' ) ),
			array( 'box', __( 'Keep stock for you', 'medzuro' ) ),
			array( 'coin', __( 'Flexible payment', 'medzuro' ) ),
		);
	} else {
		echo '<div class="mz-amount"><h3>' . esc_html__( 'Order amount', 'medzuro' ) . '</h3>';
		echo '<div class="mz-amount__row"><span>' . esc_html__( 'Order total', 'medzuro' ) . '</span><strong>' . wp_kses_post( wc_price( $a['total'] ) ) . '</strong></div>';
		if ( 'delivery' === $a['option'] ) {
			echo '<p>' . esc_html__( 'Full payment is required for online delivery.', 'medzuro' ) . '</p>';
			$trust = array(
				array( 'shield', __( 'Secure payment', 'medzuro' ) ),
				array( 'lock', __( 'Encrypted transaction', 'medzuro' ) ),
				array( 'user', __( 'Protects your data', 'medzuro' ) ),
			);
		}
		echo '</div>';
	}

	echo '<ul class="mz-trust">';
	foreach ( $trust as $t ) {
		echo '<li>';
		medzuro_icon( $t[0], 20 );
		echo '<span>' . esc_html( $t[1] ) . '</span></li>';
	}
	echo '</ul></div>';
}
add_action( 'woocommerce_review_order_before_submit', 'medzuro_payment_summary', 5 );

/**
 * "Go back & pay 10%" under the reserve button (design 7C).
 */
function medzuro_payment_after_submit() {
	if ( 'reserve' !== medzuro_checkout_amounts()['option'] ) {
		return;
	}
	echo '<button type="button" class="button mz-switch-option" data-option="deposit">' . esc_html__( 'Go back & pay 10% (recommended)', 'medzuro' ) . '</button>';
}
add_action( 'woocommerce_review_order_after_submit', 'medzuro_payment_after_submit' );

/* -------------------------------------------------------------------------
 * Shared markup
 * ---------------------------------------------------------------------- */

/**
 * The DHL Express carrier mark.
 *
 * Drop the official artwork the client received from DHL at
 * assets/img/dhl-express.png (or .svg) and it is used automatically;
 * otherwise a plain text badge is shown.
 *
 * @param string $size 'sm' or 'lg'.
 */
function medzuro_dhl_badge( $size = 'sm' ) {
	foreach ( array( 'svg', 'png' ) as $ext ) {
		$rel = '/assets/img/dhl-express.' . $ext;
		if ( file_exists( get_theme_file_path( $rel ) ) ) {
			printf(
				'<img class="mz-dhl mz-dhl--img mz-dhl--%1$s" src="%2$s" alt="%3$s" />',
				esc_attr( $size ),
				esc_url( get_theme_file_uri( $rel ) ),
				esc_attr__( 'DHL Express', 'medzuro' )
			);
			return;
		}
	}

	printf( '<span class="mz-dhl mz-dhl--%1$s">%2$s</span>', esc_attr( $size ), esc_html__( 'DHL Express', 'medzuro' ) );
}

/**
 * Delivery choice cards ("Choose how you want to receive").
 *
 * @param WC_Shipping_Rate[] $rates   Available rates.
 * @param string             $chosen  Chosen rate id.
 * @param int                $index   Package index.
 * @param bool               $proxy   True on checkout: cards drive the real
 *                                    radios in the order review via JS
 *                                    instead of being the inputs themselves.
 */
function medzuro_delivery_cards( $rates, $chosen, $index = 0, $proxy = false ) {
	echo '<ul class="mz-ship-cards" role="radiogroup">';

	foreach ( $rates as $rate ) {
		$meta      = $rate->get_meta_data();
		$is_pickup = isset( $meta['type'] ) && 'pickup' === $meta['type'];
		$is_ours   = 0 === strpos( $rate->get_id(), 'medzuro_delivery' );
		$cost      = (float) $rate->get_cost();
		$dom_id    = ( $proxy ? 'mz_choice_' : 'shipping_method_' ) . $index . '_' . sanitize_title( $rate->get_id() );
		$checked   = checked( $rate->get_id(), $chosen, false );

		echo '<li class="mz-ship-card' . ( $is_pickup ? ' mz-ship-card--pickup' : ' mz-ship-card--delivery' ) . '">';

		if ( $proxy ) {
			printf(
				'<input type="radio" name="mz_delivery_choice" id="%1$s" value="%2$s" class="mz-ship-card__input" %3$s />',
				esc_attr( $dom_id ),
				esc_attr( $rate->get_id() ),
				$checked // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		} elseif ( count( $rates ) > 1 ) {
			printf(
				'<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="%2$s" value="%3$s" class="shipping_method mz-ship-card__input" %4$s />',
				(int) $index,
				esc_attr( $dom_id ),
				esc_attr( $rate->get_id() ),
				$checked // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		} else {
			printf(
				'<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="%2$s" value="%3$s" class="shipping_method" />',
				(int) $index,
				esc_attr( $dom_id ),
				esc_attr( $rate->get_id() )
			);
		}

		echo '<label for="' . esc_attr( $dom_id ) . '" class="mz-ship-card__box">';
		echo '<span class="mz-ship-card__radio" aria-hidden="true"></span>';
		echo '<span class="mz-ship-card__icon">';
		medzuro_icon( $is_pickup ? 'store' : 'truck', 34 );
		if ( ! $is_pickup && $is_ours ) {
			medzuro_dhl_badge();
		}
		echo '</span><span class="mz-ship-card__body">';
		echo '<strong class="mz-ship-card__title">' . esc_html( $rate->get_label() ) . '</strong>';

		if ( ! empty( $meta['note'] ) ) {
			echo '<span class="mz-ship-card__note">' . esc_html( $meta['note'] ) . '</span>';
		}
		if ( ! empty( $meta['subnote'] ) ) {
			echo '<span class="mz-ship-card__note">' . esc_html( $meta['subnote'] ) . '</span>';
		}

		if ( ! $is_pickup ) {
			echo '<span class="mz-ship-card__price">'
				. ( $cost > 0 ? wp_kses_post( wc_price( $cost ) ) : esc_html__( 'Free shipping (Fiji wide)', 'medzuro' ) )
				. '</span>';
		}

		echo '</span>';

		if ( ! $is_pickup && $is_ours ) {
			echo '<span class="mz-ship-card__badge">' . esc_html__( 'Recommended', 'medzuro' ) . '</span>';
		}

		echo '</label>';
		do_action( 'woocommerce_after_shipping_rate', $rate, $index );
		echo '</li>';
	}

	echo '</ul>';
}

/**
 * Rates for the first cart package, plus the chosen rate id.
 *
 * @return array{0: WC_Shipping_Rate[], 1: string}
 */
function medzuro_current_rates() {
	if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
		return array( array(), '' );
	}

	$packages = WC()->shipping()->get_packages();
	if ( empty( $packages[0]['rates'] ) ) {
		WC()->cart->calculate_totals();
		$packages = WC()->shipping()->get_packages();
	}
	$rates  = isset( $packages[0]['rates'] ) ? $packages[0]['rates'] : array();
	$chosen = (array) WC()->session->get( 'chosen_shipping_methods', array() );

	return array( $rates, isset( $chosen[0] ) ? $chosen[0] : '' );
}

/* -------------------------------------------------------------------------
 * Payment method cards (design 7A/7B: logo, name, "Pay with ... wallet")
 * ---------------------------------------------------------------------- */

/**
 * Card copy for gateways shown on the payment step.
 *
 * Drop the official logo the client received from Vodafone at
 * assets/img/mpaisa-logo.svg (or .png) and it is used automatically;
 * until then a plain red tile with a phone icon is shown.
 *
 * @return array
 */
function medzuro_gateway_cards() {
	return array(
		'mpaisa'          => array(
			'name' => __( 'M-PAiSA', 'medzuro' ),
			'sub'  => __( 'Pay with M-PAiSA wallet', 'medzuro' ),
			'logo' => 'mpaisa-logo',
			'icon' => 'phone',
			'tone' => 'red',
		),
		'medzuro_reserve' => array(
			'name' => __( 'Reserve without payment', 'medzuro' ),
			'sub'  => __( 'Pay when you collect', 'medzuro' ),
			'logo' => '',
			'icon' => 'calendar',
			'tone' => 'navy',
		),
	);
}

/**
 * True only while WooCommerce prints a payment method row, so the card
 * markup never ends up in the order's saved payment method title.
 *
 * @param bool|null $set New state.
 * @return bool
 */
function medzuro_rendering_payment_list( $set = null ) {
	static $on = false;
	if ( null !== $set ) {
		$on = (bool) $set;
	}
	return $on;
}
// Switched on around each payment-method.php render. (The before/after
// payment actions don't fire during AJAX refreshes, so they can't be used.)
add_action(
	'woocommerce_before_template_part',
	function ( $template_name ) {
		if ( 'checkout/payment-method.php' === $template_name && ! is_admin() ) {
			medzuro_rendering_payment_list( true );
		}
	}
);
add_action(
	'woocommerce_after_template_part',
	function ( $template_name ) {
		if ( 'checkout/payment-method.php' === $template_name ) {
			medzuro_rendering_payment_list( false );
		}
	}
);

/**
 * Gateway title as a card: logo tile, name and one-line subtitle.
 *
 * @param string $title Title.
 * @param string $id    Gateway id.
 * @return string
 */
function medzuro_gateway_card_title( $title, $id = '' ) {
	$cards = medzuro_gateway_cards();
	if ( ! medzuro_rendering_payment_list() || ! isset( $cards[ $id ] ) ) {
		return $title;
	}

	$c    = $cards[ $id ];
	$logo = '';
	if ( $c['logo'] ) {
		foreach ( array( 'svg', 'png', 'jpg' ) as $ext ) {
			$rel = '/assets/img/' . $c['logo'] . '.' . $ext;
			if ( file_exists( get_theme_file_path( $rel ) ) ) {
				$logo = '<img src="' . esc_url( get_theme_file_uri( $rel ) ) . '" alt="" />';
				break;
			}
		}
	}
	if ( ! $logo ) {
		ob_start();
		medzuro_icon( $c['icon'], 22 );
		$logo = ob_get_clean();
	}

	return '<span class="mz-gw mz-gw--' . esc_attr( $c['tone'] ) . '">'
		. '<span class="mz-gw__logo' . ( false !== strpos( $logo, '<img' ) ? ' mz-gw__logo--img' : '' ) . '">' . $logo . '</span>'
		. '<span class="mz-gw__text"><strong>' . esc_html( $c['name'] ) . '</strong><small>' . esc_html( $c['sub'] ) . '</small></span>'
		. '</span>';
}
add_filter( 'woocommerce_gateway_title', 'medzuro_gateway_card_title', 20, 2 );

/**
 * The card already says it all; drop the separate description box.
 *
 * @param string $description Description.
 * @param string $id          Gateway id.
 * @return string
 */
function medzuro_gateway_card_description( $description, $id = '' ) {
	return medzuro_rendering_payment_list() && isset( medzuro_gateway_cards()[ $id ] ) ? '' : $description;
}
add_filter( 'woocommerce_gateway_description', 'medzuro_gateway_card_description', 20, 2 );
