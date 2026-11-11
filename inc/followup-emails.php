<?php
/**
 * Follow-up emails, sent automatically with Action Scheduler:
 *
 *   Pickup reminder - the order is still "Ready for pickup" N days after it
 *                     was marked ready (default 2). Optional auto-cancel.
 *   Rebuy reminder  - N days after an order is Completed (default 30), a
 *                     "Time to restock?" email with a one-click Buy again link.
 *                     Skipped if the customer has already reordered.
 *
 * The "Ready for pickup" email itself lives in inc/order-flow.php.
 *
 * Settings: WooCommerce -> Settings -> Emails -> "Medzuro follow-up emails".
 * Per product: Product data -> General -> "Rebuy reminder (days)".
 * Queue: WooCommerce -> Status -> Scheduled Actions, group "medzuro-followup".
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

define( 'MEDZURO_FOLLOWUP_GROUP', 'medzuro-followup' );

/* -------------------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------------------- */

/**
 * Add the follow-up settings to WooCommerce -> Settings -> Emails.
 *
 * @param array $settings Email settings.
 * @return array
 */
function medzuro_followup_settings( $settings ) {
	$settings[] = array(
		'title' => __( 'Medzuro follow-up emails', 'medzuro' ),
		'type'  => 'title',
		'desc'  => __( 'Automatic reminders for pickup orders and repeat purchases. Each email sent is noted on the order.', 'medzuro' ),
		'id'    => 'medzuro_followup',
	);
	$settings[] = array(
		'title'    => __( 'Pickup reminder after (days)', 'medzuro' ),
		'desc'     => __( 'Days after an order is marked "Ready for pickup". Several with commas, e.g. 2, 5. Empty = no reminders.', 'medzuro' ),
		'desc_tip' => true,
		'id'       => 'medzuro_pickup_reminder_days',
		'type'     => 'text',
		'default'  => '2',
	);
	$settings[] = array(
		'title'             => __( 'Auto-cancel uncollected after (days)', 'medzuro' ),
		'desc'              => __( 'Cancel "Ready for pickup" orders not collected after this many days; stock is returned. 0 = never.', 'medzuro' ),
		'desc_tip'          => true,
		'id'                => 'medzuro_pickup_cancel_days',
		'type'              => 'number',
		'default'           => '0',
		'custom_attributes' => array( 'min' => '0', 'step' => '1' ),
	);
	$settings[] = array(
		'title'   => __( 'Rebuy reminder', 'medzuro' ),
		'desc'    => __( 'Send a "Time to restock?" email after an order is completed', 'medzuro' ),
		'id'      => 'medzuro_rebuy_enabled',
		'type'    => 'checkbox',
		'default' => 'yes',
	);
	$settings[] = array(
		'title'             => __( 'Rebuy reminder after (days)', 'medzuro' ),
		'desc'              => __( 'Default for products without their own "Rebuy reminder (days)" value.', 'medzuro' ),
		'desc_tip'          => true,
		'id'                => 'medzuro_rebuy_days',
		'type'              => 'number',
		'default'           => '30',
		'custom_attributes' => array( 'min' => '1', 'step' => '1' ),
	);
	$settings[] = array(
		'type' => 'sectionend',
		'id'   => 'medzuro_followup',
	);
	return $settings;
}
add_filter( 'woocommerce_email_settings', 'medzuro_followup_settings' );

/**
 * Pickup reminder days, sorted, at most 10.
 *
 * @return int[]
 */
function medzuro_pickup_reminder_days() {
	$days = array_unique( array_filter( array_map( 'absint', explode( ',', (string) get_option( 'medzuro_pickup_reminder_days', '2' ) ) ) ) );
	sort( $days );
	return array_slice( $days, 0, 10 );
}

/* -------------------------------------------------------------------------
 * Scheduling
 * ---------------------------------------------------------------------- */

/**
 * Cancel pending pickup reminders / auto-cancel for an order.
 *
 * @param int $order_id Order id.
 */
function medzuro_unschedule_pickup( $order_id ) {
	for ( $n = 1; $n <= 10; $n++ ) {
		as_unschedule_all_actions( 'medzuro_pickup_reminder', array( (int) $order_id, $n ), MEDZURO_FOLLOWUP_GROUP );
	}
	as_unschedule_all_actions( 'medzuro_pickup_expire', array( (int) $order_id ), MEDZURO_FOLLOWUP_GROUP );
}

/**
 * Schedule or cancel follow-ups when an order changes status.
 *
 * @param int      $order_id Order id.
 * @param string   $from     Old status.
 * @param string   $to       New status.
 * @param WC_Order $order    Order.
 */
function medzuro_followup_on_status( $order_id, $from, $to, $order ) {
	if ( ! function_exists( 'as_schedule_single_action' ) ) {
		return;
	}

	if ( 'ready-pickup' === $from && 'ready-pickup' !== $to ) {
		medzuro_unschedule_pickup( $order_id );
	}

	if ( 'ready-pickup' === $to ) {
		medzuro_unschedule_pickup( $order_id );
		$order->update_meta_data( '_mz_ready_at', time() );
		$order->save();

		$n = 0;
		foreach ( medzuro_pickup_reminder_days() as $days ) {
			++$n;
			as_schedule_single_action( time() + $days * DAY_IN_SECONDS, 'medzuro_pickup_reminder', array( (int) $order_id, $n ), MEDZURO_FOLLOWUP_GROUP );
		}

		$cancel = absint( get_option( 'medzuro_pickup_cancel_days', 0 ) );
		if ( $cancel > 0 ) {
			as_schedule_single_action( time() + $cancel * DAY_IN_SECONDS, 'medzuro_pickup_expire', array( (int) $order_id ), MEDZURO_FOLLOWUP_GROUP );
		}
	}

	if ( 'completed' === $to && 'yes' === get_option( 'medzuro_rebuy_enabled', 'yes' ) && ! $order->get_meta( '_mz_rebuy_sent' ) ) {
		$args = array( (int) $order_id );
		$days = medzuro_rebuy_days( $order );
		if ( $days > 0 && ! as_has_scheduled_action( 'medzuro_rebuy_reminder', $args, MEDZURO_FOLLOWUP_GROUP ) ) {
			as_schedule_single_action( time() + $days * DAY_IN_SECONDS, 'medzuro_rebuy_reminder', $args, MEDZURO_FOLLOWUP_GROUP );
		}
	}

	if ( in_array( $to, array( 'cancelled', 'refunded', 'failed' ), true ) ) {
		as_unschedule_all_actions( 'medzuro_rebuy_reminder', array( (int) $order_id ), MEDZURO_FOLLOWUP_GROUP );
	}
}
add_action( 'woocommerce_order_status_changed', 'medzuro_followup_on_status', 20, 4 );

/**
 * Days until the rebuy email: the shortest interval among the order's
 * products. Empty product value = default; 0 = never for that product.
 *
 * @param WC_Order $order Order.
 * @return int 0 when no reminder should be sent.
 */
function medzuro_rebuy_days( $order ) {
	$default = max( 1, absint( get_option( 'medzuro_rebuy_days', 30 ) ) );
	$days    = array();
	foreach ( $order->get_items() as $item ) {
		$value = get_post_meta( $item->get_product_id(), '_mz_rebuy_days', true );
		if ( '' === $value || false === $value ) {
			$days[] = $default;
		} elseif ( absint( $value ) > 0 ) {
			$days[] = absint( $value );
		}
	}
	return $days ? min( $days ) : 0;
}

/* -------------------------------------------------------------------------
 * Pickup reminder
 * ---------------------------------------------------------------------- */

/**
 * Email the customer that their order is still waiting.
 *
 * @param int $order_id Order id.
 * @param int $n        Reminder number.
 */
function medzuro_send_pickup_reminder( $order_id, $n = 1 ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->has_status( 'ready-pickup' ) ) {
		return; // Collected or cancelled meanwhile.
	}

	$store    = medzuro_order_pickup_store( $order );
	$balance  = (float) $order->get_total() - medzuro_order_amount_paid( $order );
	$ready_at = (int) $order->get_meta( '_mz_ready_at' );
	$cancel   = absint( get_option( 'medzuro_pickup_cancel_days', 0 ) );

	$body  = '<p>' . sprintf( esc_html__( 'Hi %s,', 'medzuro' ), esc_html( $order->get_billing_first_name() ) ) . '</p>';
	$body .= '<p>' . sprintf(
		/* translators: 1: order number, 2: store name, 3: store address */
		esc_html__( 'Just a friendly reminder - order #%1$s is ready and waiting for you at %2$s (%3$s).', 'medzuro' ),
		esc_html( $order->get_order_number() ),
		esc_html( $store['name'] ),
		esc_html( $store['street'] )
	) . '</p>';

	if ( ! empty( $store['hours'] ) ) {
		$body .= '<p><strong>' . esc_html__( 'Opening hours:', 'medzuro' ) . '</strong> ' . esc_html( $store['hours'] ) . '</p>';
	}
	if ( ! empty( $store['map'] ) ) {
		$body .= '<p><a href="' . esc_url( $store['map'] ) . '">' . esc_html__( 'View on Google Maps', 'medzuro' ) . '</a></p>';
	}
	if ( $balance > 0 ) {
		$body .= '<p><strong>' . esc_html__( 'Amount to pay at pickup:', 'medzuro' ) . '</strong> ' . wp_kses_post( wc_price( $balance, array( 'currency' => $order->get_currency() ) ) ) . '</p>';
	}
	if ( $ready_at && $cancel > 0 ) {
		/* translators: %s: date */
		$body .= '<p><strong>' . sprintf( esc_html__( 'Please collect it by %s - after that the reservation will be released.', 'medzuro' ), esc_html( date_i18n( wc_date_format(), $ready_at + $cancel * DAY_IN_SECONDS ) ) ) . '</strong></p>';
	}
	$body .= '<p>' . esc_html__( 'If you can no longer collect this order, just reply to this email and let us know.', 'medzuro' ) . '</p>';

	/* translators: %s: order number */
	$subject = sprintf( __( 'Reminder: your Medzuro order #%s is waiting for pickup', 'medzuro' ), $order->get_order_number() );
	if ( medzuro_send_customer_email( $order, $subject, __( 'Your order is waiting for you', 'medzuro' ), $body ) ) {
		$order->add_order_note(
			$n > 0
				/* translators: %d: reminder number */
				? sprintf( __( 'Pickup reminder email #%d sent.', 'medzuro' ), (int) $n )
				: __( 'Pickup reminder email sent manually by staff.', 'medzuro' )
		);
	}
}
add_action( 'medzuro_pickup_reminder', 'medzuro_send_pickup_reminder', 10, 2 );

/**
 * Cancel an order that was never collected. WooCommerce returns the stock.
 *
 * @param int $order_id Order id.
 */
function medzuro_expire_pickup( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order && $order->has_status( 'ready-pickup' ) ) {
		$order->update_status( 'cancelled', __( 'Not collected within the pickup window - cancelled automatically.', 'medzuro' ) );
	}
}
add_action( 'medzuro_pickup_expire', 'medzuro_expire_pickup' );

/* -------------------------------------------------------------------------
 * Rebuy reminder
 * ---------------------------------------------------------------------- */

/**
 * Emails that unsubscribed from rebuy reminders.
 *
 * @return string[]
 */
function medzuro_rebuy_optouts() {
	return (array) get_option( 'medzuro_rebuy_optout', array() );
}

/**
 * Token for an unsubscribe link.
 *
 * @param string $email Email.
 * @return string
 */
function medzuro_rebuy_unsub_token( $email ) {
	return substr( wp_hash( 'mz_unsub|' . strtolower( $email ) ), 0, 20 );
}

/**
 * Whether the customer has placed another order for any of these products.
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function medzuro_already_rebought( $order ) {
	$since = $order->get_date_completed() ? $order->get_date_completed() : $order->get_date_created();
	$ids   = array();
	foreach ( $order->get_items() as $item ) {
		$ids[] = $item->get_product_id();
	}

	$statuses = array();
	foreach ( array_keys( wc_get_order_statuses() ) as $key ) {
		$slug = 'wc-' === substr( $key, 0, 3 ) ? substr( $key, 3 ) : $key;
		if ( ! in_array( $slug, array( 'cancelled', 'failed', 'refunded', 'pending', 'checkout-draft' ), true ) ) {
			$statuses[] = $slug;
		}
	}

	$later = wc_get_orders(
		array(
			'billing_email' => $order->get_billing_email(),
			'date_created'  => '>' . $since->getTimestamp(),
			'status'        => $statuses,
			'exclude'       => array( $order->get_id() ),
			'limit'         => 20,
		)
	);
	foreach ( $later as $other ) {
		foreach ( $other->get_items() as $item ) {
			if ( in_array( $item->get_product_id(), $ids, true ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Send the "Time to restock?" email.
 *
 * @param int $order_id Order id.
 */
function medzuro_send_rebuy_reminder( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! $order->has_status( 'completed' ) || $order->get_meta( '_mz_rebuy_sent' ) || 'yes' !== get_option( 'medzuro_rebuy_enabled', 'yes' ) ) {
		return;
	}
	$email = strtolower( $order->get_billing_email() );
	if ( ! $email || in_array( $email, medzuro_rebuy_optouts(), true ) ) {
		return;
	}
	if ( medzuro_already_rebought( $order ) ) {
		$order->add_order_note( __( 'Rebuy reminder skipped - customer has already reordered.', 'medzuro' ) );
		return;
	}

	$buy_url   = add_query_arg( array( 'mz_rebuy' => $order->get_id(), 'key' => $order->get_order_key() ), home_url( '/' ) );
	$unsub_url = add_query_arg( array( 'mz_unsub' => rawurlencode( base64_encode( $email ) ), 't' => medzuro_rebuy_unsub_token( $email ) ), home_url( '/' ) );
	$color     = get_option( 'woocommerce_email_base_color', '#2e7d32' );

	$body  = '<p>' . sprintf( esc_html__( 'Hi %s,', 'medzuro' ), esc_html( $order->get_billing_first_name() ) ) . '</p>';
	$body .= '<p>' . esc_html__( 'It has been a little while since your last order. If you are running low, you can get the same items again in one click:', 'medzuro' ) . '</p><ul>';
	foreach ( $order->get_items() as $item ) {
		$body .= '<li>' . esc_html( $item->get_name() ) . ' &times; ' . esc_html( $item->get_quantity() ) . '</li>';
	}
	$body .= '</ul>';
	$body .= '<p style="text-align:center;margin:28px 0;"><a href="' . esc_url( $buy_url ) . '" style="display:inline-block;padding:12px 28px;background:' . esc_attr( $color ) . ';color:#ffffff;text-decoration:none;border-radius:4px;font-weight:bold;">' . esc_html__( 'Buy again', 'medzuro' ) . '</a></p>';
	$body .= '<p>' . esc_html__( 'Thank you for shopping with Medzuro.', 'medzuro' ) . '</p>';
	$body .= '<p style="font-size:12px;color:#888888;">' . esc_html__( "Don't want these reminders?", 'medzuro' ) . ' <a href="' . esc_url( $unsub_url ) . '" style="color:#888888;">' . esc_html__( 'Unsubscribe', 'medzuro' ) . '</a></p>';

	if ( medzuro_send_customer_email( $order, __( 'Running low? Reorder from Medzuro in one click', 'medzuro' ), __( 'Time to restock?', 'medzuro' ), $body ) ) {
		$order->update_meta_data( '_mz_rebuy_sent', time() );
		$order->save();
		$order->add_order_note( __( 'Rebuy reminder email sent.', 'medzuro' ) );
	}
}
add_action( 'medzuro_rebuy_reminder', 'medzuro_send_rebuy_reminder' );

/**
 * "Buy again" and "Unsubscribe" links from the rebuy email.
 */
function medzuro_rebuy_links() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- links are authorised by the order key / token.
	if ( isset( $_GET['mz_rebuy'], $_GET['key'] ) ) {
		$order = wc_get_order( absint( $_GET['mz_rebuy'] ) );
		$key   = sanitize_text_field( wp_unslash( $_GET['key'] ) );
		if ( ! $order || ! hash_equals( $order->get_order_key(), $key ) || ! WC()->cart ) {
			return;
		}
		if ( WC()->session && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		$added   = 0;
		$missing = array();
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
				$missing[] = $item->get_name();
				continue;
			}
			$variation_id = $item->get_variation_id();
			$attributes   = $variation_id ? wc_get_product_variation_attributes( $variation_id ) : array();
			if ( WC()->cart->add_to_cart( $item->get_product_id(), $item->get_quantity(), $variation_id, $attributes ) ) {
				++$added;
			}
		}
		if ( $added ) {
			wc_add_notice( __( 'Your previous order has been added to your cart.', 'medzuro' ) );
		}
		if ( $missing ) {
			/* translators: %s: product names */
			wc_add_notice( sprintf( __( 'Currently unavailable: %s', 'medzuro' ), implode( ', ', $missing ) ), 'notice' );
		}
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	if ( isset( $_GET['mz_unsub'], $_GET['t'] ) ) {
		$email = strtolower( (string) base64_decode( rawurldecode( sanitize_text_field( wp_unslash( $_GET['mz_unsub'] ) ) ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$token = sanitize_text_field( wp_unslash( $_GET['t'] ) );
		if ( is_email( $email ) && hash_equals( medzuro_rebuy_unsub_token( $email ), $token ) ) {
			$list = medzuro_rebuy_optouts();
			if ( ! in_array( $email, $list, true ) ) {
				$list[] = $email;
				update_option( 'medzuro_rebuy_optout', $list, false );
			}
			wc_add_notice( __( 'You will no longer receive restock reminder emails from us.', 'medzuro' ) );
		}
		wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
		exit;
	}
	// phpcs:enable
}
add_action( 'template_redirect', 'medzuro_rebuy_links' );

/* -------------------------------------------------------------------------
 * Product field
 * ---------------------------------------------------------------------- */

/**
 * "Rebuy reminder (days)" on the product's General tab.
 */
function medzuro_rebuy_product_field() {
	echo '<div class="options_group">';
	woocommerce_wp_text_input(
		array(
			'id'                => '_mz_rebuy_days',
			'label'             => __( 'Rebuy reminder (days)', 'medzuro' ),
			'placeholder'       => __( 'Use default', 'medzuro' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '0', 'step' => '1' ),
			'desc_tip'          => true,
			'description'       => __( 'Days after the order is completed to send a "Time to restock?" email. Empty = default from WooCommerce -> Settings -> Emails. 0 = never for this product.', 'medzuro' ),
		)
	);
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'medzuro_rebuy_product_field' );

/**
 * Save the product field.
 *
 * @param WC_Product $product Product.
 */
function medzuro_rebuy_product_save( $product ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the product nonce.
	if ( ! isset( $_POST['_mz_rebuy_days'] ) ) {
		return;
	}
	$value = trim( sanitize_text_field( wp_unslash( $_POST['_mz_rebuy_days'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( '' === $value ) {
		$product->delete_meta_data( '_mz_rebuy_days' );
	} else {
		$product->update_meta_data( '_mz_rebuy_days', absint( $value ) );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'medzuro_rebuy_product_save' );
