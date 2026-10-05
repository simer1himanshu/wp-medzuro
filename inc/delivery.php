<?php
/**
 * Delivery or pickup: the shipping method and its helpers.
 *
 * Implements the client's "Initial Flow": the customer chooses DHL Express
 * home delivery or store pickup in Suva (Nakasi or Princes Road). Every other shipping rate is
 * removed so the cart and checkout always show exactly those two choices.
 *
 * The method can optionally be added to the Fiji shipping zone
 * (WooCommerce > Settings > Shipping) to change its cost, delivery time or
 * pickup address. If it isn't added, the same two rates are created with the
 * defaults below, so the flow works without any admin setup.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default settings for the two rates.
 *
 * @return array
 */
function medzuro_delivery_defaults() {
	return array(
		'dhl_enabled'    => 'yes',
		'dhl_cost'       => '0',
		'dhl_eta'        => __( '3-7 working days', 'medzuro' ),
		'pickup_enabled' => 'yes',
		'pickup_address' => __( 'Multiple Pickup Locations Available Across Suva', 'medzuro' ),
		'pickup_note'    => __( 'Choose Nakasi or Princes Road at checkout', 'medzuro' ),
		'pickup_hours'   => __( 'Mon - Sat, 9:00 AM - 5:00 PM', 'medzuro' ),
	);
}

/**
 * Saved settings of the Medzuro method in the Fiji zone, when it was added
 * there. Empty values are left out so the defaults apply.
 *
 * @return array
 */
function medzuro_delivery_saved_settings() {
	static $settings = null;
	if ( null !== $settings ) {
		return $settings;
	}

	$settings = array();
	if ( class_exists( 'WC_Shipping_Zones' ) ) {
		foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
			foreach ( $zone['shipping_methods'] as $method ) {
				if ( 'medzuro_delivery' === $method->id ) {
					foreach ( array_keys( medzuro_delivery_defaults() ) as $key ) {
						$value = $method->get_option( $key );
						if ( '' !== $value && null !== $value ) {
							$settings[ $key ] = $value;
						}
					}
					break 2;
				}
			}
		}
	}

	return $settings;
}

/**
 * The pickup stores the customer can choose from, keyed by a stable id that
 * is posted as "mz_pickup_store" and saved on the order as _mz_pickup_store.
 * The first store is the default.
 *
 * Drop a photo at assets/img/pickup-store-{id}.jpg (e.g.
 * pickup-store-princes-road.jpg) to show it on the "Select pickup location"
 * step. assets/img/pickup-store.jpg is used for Nakasi as before.
 *
 * @return array[] id => array{id:string, name:string, street:string, hours:string, map:string, area:string, image:string}
 */
function medzuro_pickup_stores() {
	static $stores = null;
	if ( null !== $stores ) {
		return $stores;
	}

	$s     = wp_parse_args( medzuro_delivery_saved_settings(), medzuro_delivery_defaults() );
	$hours = $s['pickup_hours'];

	$stores = array(
		'nakasi'       => array(
			'name'   => __( 'Medzuro Retail - Nakasi', 'medzuro' ),
			'street' => __( '18, Valili Street, Vishnu Deo Road, Nakasi, Suva', 'medzuro' ),
			'area'   => __( 'Nakasi, Suva', 'medzuro' ),
			'hours'  => $hours,
			'map'    => 'https://maps.app.goo.gl/68wbiqEhmbqoQcXH6',
			'photos' => array( 'pickup-store-nakasi', 'pickup-store' ),
		),
		'princes-road' => array(
			'name'   => __( 'Medzuro Retail - Princes Road', 'medzuro' ),
			'street' => __( '204, Princes Road, Suva, Fiji', 'medzuro' ),
			'area'   => __( 'Princes Road, Suva', 'medzuro' ),
			'hours'  => $hours,
			'map'    => 'https://maps.app.goo.gl/4Jqa1znBGrMrSnRYA',
			'photos' => array( 'pickup-store-princes-road' ),
		),
	);

	foreach ( $stores as $id => $store ) {
		$image = '';
		foreach ( $store['photos'] as $base ) {
			foreach ( array( 'jpg', 'jpeg', 'png', 'webp' ) as $ext ) {
				if ( file_exists( get_theme_file_path( '/assets/img/' . $base . '.' . $ext ) ) ) {
					$image = get_theme_file_uri( '/assets/img/' . $base . '.' . $ext );
					break 2;
				}
			}
		}
		unset( $store['photos'] );
		$stores[ $id ] = array( 'id' => $id ) + $store + array( 'image' => $image );
	}

	$stores = apply_filters( 'medzuro_pickup_stores', $stores );

	return $stores;
}

/**
 * One pickup store's details. Unknown or empty ids give the first store,
 * which is also what orders placed before the choice existed used.
 *
 * Also used on the thank-you page, emails and the admin follow-up box.
 *
 * @param string $id Store id (see medzuro_pickup_stores()).
 * @return array{id:string, name:string, street:string, hours:string, map:string, area:string, image:string}
 */
function medzuro_pickup_store( $id = '' ) {
	$stores = medzuro_pickup_stores();
	return isset( $stores[ $id ] ) ? $stores[ $id ] : reset( $stores );
}

/**
 * The pickup store chosen for an order.
 *
 * @param WC_Order $order Order.
 * @return array See medzuro_pickup_store().
 */
function medzuro_order_pickup_store( $order ) {
	return medzuro_pickup_store( (string) $order->get_meta( '_mz_pickup_store' ) );
}

/**
 * The store id posted with the checkout form, or '' when missing/invalid.
 *
 * @return string
 */
function medzuro_posted_pickup_store() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
	$id = isset( $_POST['mz_pickup_store'] ) ? sanitize_key( wp_unslash( $_POST['mz_pickup_store'] ) ) : '';
	return array_key_exists( $id, medzuro_pickup_stores() ) ? $id : '';
}

/**
 * Build the DHL + pickup rate arguments from a settings array.
 *
 * @param array  $s       Settings (see medzuro_delivery_defaults()).
 * @param string $id_base Rate id prefix, e.g. "medzuro_delivery:3".
 * @return array[] Rate args keyed by "dhl" / "pickup".
 */
function medzuro_delivery_rate_args( $s, $id_base ) {
	$s     = wp_parse_args( $s, medzuro_delivery_defaults() );
	$rates = array();

	if ( 'yes' === $s['dhl_enabled'] ) {
		$rates['dhl'] = array(
			'id'        => $id_base . ':dhl',
			'label'     => __( 'Home Delivery (DHL Express)', 'medzuro' ),
			'cost'      => (float) $s['dhl_cost'],
			'meta_data' => array(
				'type' => 'delivery',
				'note' => $s['dhl_eta'],
			),
		);
	}

	if ( 'yes' === $s['pickup_enabled'] ) {
		$rates['pickup'] = array(
			'id'        => $id_base . ':pickup',
			'label'     => __( 'Store Pickup', 'medzuro' ),
			'cost'      => 0,
			'meta_data' => array(
				'type'    => 'pickup',
				'note'    => $s['pickup_address'],
				'subnote' => $s['pickup_note'],
			),
		);
	}

	return $rates;
}

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
			$this->method_description = __( 'Offers DHL Express home delivery and store pickup (Nakasi or Princes Road) as two rates.', 'medzuro' );
			$this->supports           = array( 'shipping-zones', 'instance-settings' );

			$this->init_instance_form_fields();
			$this->init_settings();

			$this->title = $this->get_option( 'title', $this->method_title );

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_instance_form_fields() {
			$d = medzuro_delivery_defaults();

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
					'default' => $d['dhl_enabled'],
				),
				'dhl_cost'       => array(
					'title'       => __( 'DHL cost', 'medzuro' ),
					'type'        => 'price',
					'description' => __( 'Leave at 0 for free shipping.', 'medzuro' ),
					'default'     => $d['dhl_cost'],
					'desc_tip'    => true,
				),
				'dhl_eta'        => array(
					'title'   => __( 'DHL delivery time', 'medzuro' ),
					'type'    => 'text',
					'default' => $d['dhl_eta'],
				),
				'pickup_enabled' => array(
					'title'   => __( 'Pickup', 'medzuro' ),
					'type'    => 'checkbox',
					'label'   => __( 'Offer store pickup', 'medzuro' ),
					'default' => $d['pickup_enabled'],
				),
				'pickup_address' => array(
					'title'   => __( 'Pickup locations (shown on the option)', 'medzuro' ),
					'type'    => 'text',
					'default' => $d['pickup_address'],
				),
				'pickup_note'    => array(
					'title'   => __( 'Pickup note', 'medzuro' ),
					'type'    => 'text',
					'default' => $d['pickup_note'],
				),
				'pickup_hours'   => array(
					'title'   => __( 'Opening hours', 'medzuro' ),
					'type'    => 'text',
					'default' => $d['pickup_hours'],
				),
			);
		}

		public function calculate_shipping( $package = array() ) {
			$settings = array();
			foreach ( array_keys( medzuro_delivery_defaults() ) as $key ) {
				$settings[ $key ] = $this->get_option( $key );
			}

			foreach ( medzuro_delivery_rate_args( $settings, $this->get_rate_id() ) as $args ) {
				$this->add_rate( $args );
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
 * Show exactly the two Medzuro rates, whatever else the zone is set up with.
 *
 * The live store had "Free shipping" and "Flat rate $10" in its zone. The
 * client's flow has only DHL delivery and pickup, so other rates are dropped,
 * and the two rates are created with defaults when the zone doesn't have the
 * Medzuro method yet.
 *
 * @param WC_Shipping_Rate[] $rates   Rates for the package.
 * @param array              $package Package.
 * @return WC_Shipping_Rate[]
 */
function medzuro_only_delivery_rates( $rates, $package ) {
	$country = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';
	if ( $country && 'FJ' !== $country ) {
		return $rates;
	}

	$ours = array_filter( $rates, fn( $rate ) => 'medzuro_delivery' === $rate->get_method_id() );
	if ( $ours ) {
		return $ours;
	}

	$out = array();
	foreach ( medzuro_delivery_rate_args( array(), 'medzuro_delivery' ) as $args ) {
		$rate = new WC_Shipping_Rate( $args['id'], $args['label'], $args['cost'], array(), 'medzuro_delivery' );
		foreach ( $args['meta_data'] as $k => $v ) {
			$rate->add_meta_data( $k, $v );
		}
		$out[ $args['id'] ] = $rate;
	}

	return $out;
}
add_filter( 'woocommerce_package_rates', 'medzuro_only_delivery_rates', 100, 2 );

/**
 * Whether a rate id is the pickup rate.
 *
 * @param string $rate_id Rate id.
 * @return bool
 */
function medzuro_rate_is_pickup( $rate_id ) {
	return is_string( $rate_id ) && 0 === strpos( $rate_id, 'medzuro_delivery' ) && ':pickup' === substr( $rate_id, -7 );
}

/**
 * Whether the customer has chosen pickup. Null when nothing is chosen yet.
 *
 * Prefers the rate posted with the current request (checkout submit or
 * order review refresh) over the session, which can lag by one request.
 *
 * @return bool|null
 */
function medzuro_chosen_is_pickup() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
		$posted = wc_clean( wp_unslash( reset( $_POST['shipping_method'] ) ) );
		if ( is_string( $posted ) && 0 === strpos( $posted, 'medzuro_delivery' ) ) {
			return medzuro_rate_is_pickup( $posted );
		}
	}
	// phpcs:enable

	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return null;
	}

	$chosen = (array) WC()->session->get( 'chosen_shipping_methods', array() );
	$rate   = reset( $chosen );

	if ( ! $rate || 0 !== strpos( $rate, 'medzuro_delivery' ) ) {
		return null;
	}

	return medzuro_rate_is_pickup( $rate );
}

/**
 * Whether an order is a pickup order.
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function medzuro_order_is_pickup( $order ) {
	// Rate meta ("type") is copied onto the order's shipping line item.
	foreach ( $order->get_shipping_methods() as $item ) {
		if ( 'pickup' === $item->get_meta( 'type' ) || 'local_pickup' === $item->get_method_id() || 'pickup_location' === $item->get_method_id() ) {
			return true;
		}
	}
	return false;
}

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
 * Fiji has no postcodes; never ask for one.
 *
 * @param array $locale Country locales.
 * @return array
 */
function medzuro_fiji_locale( $locale ) {
	$locale['FJ']['postcode'] = array(
		'required' => false,
		'hidden'   => true,
	);
	$locale['FJ']['state']    = array(
		'label'    => __( 'Province', 'medzuro' ),
		'required' => true,
	);
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'medzuro_fiji_locale' );

/**
 * WooCommerce caches shipping rates in each customer's session, keyed by a
 * hash of the package. Adding a version here changes that hash, so carts
 * started before a change to the rate labels/notes pick up the new text.
 * Bump it whenever medzuro_delivery_rate_args() output changes.
 *
 * @param array $packages Shipping packages.
 * @return array
 */
function medzuro_rates_cache_version( $packages ) {
	foreach ( $packages as $i => $package ) {
		$packages[ $i ]['medzuro_rates_version'] = 2;
	}
	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', 'medzuro_rates_cache_version' );
