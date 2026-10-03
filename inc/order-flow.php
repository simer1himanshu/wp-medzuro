<?php
/**
 * After checkout: order statuses, "Reserve without payment", the 10% pickup
 * deposit, DHL tracking, customer emails and the order progress timeline.
 *
 * Statuses added:
 *   Deposit paid      - pickup order, 10% paid with M-PAiSA, balance at pickup
 *   Reserved          - pickup order requested without payment (not guaranteed)
 *   Reservation confirmed - staff confirmed stock for a "Reserved" order
 *   Ready for pickup  - staff have packed a pickup order (customer is emailed)
 *   Shipped           - DHL tracking number added (customer is emailed)
 * "Completed" means delivered (home delivery) or collected (pickup).
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Statuses
 * ---------------------------------------------------------------------- */

/**
 * Custom statuses: slug => label.
 *
 * @return array
 */
function medzuro_custom_statuses() {
	return array(
		'deposit-paid' => __( 'Deposit paid', 'medzuro' ),
		'reserved'     => __( 'Reserved', 'medzuro' ),
		'confirmed'    => __( 'Reservation confirmed', 'medzuro' ),
		'ready-pickup' => __( 'Ready for pickup', 'medzuro' ),
		'shipped'      => __( 'Shipped', 'medzuro' ),
	);
}

/**
 * Register the statuses with WordPress.
 */
function medzuro_register_statuses() {
	foreach ( medzuro_custom_statuses() as $slug => $label ) {
		register_post_status(
			'wc-' . $slug,
			array(
				'label'                     => $label,
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of orders */
				'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'medzuro' ), // phpcs:ignore WordPress.WP.I18n
			)
		);
	}
}
add_action( 'init', 'medzuro_register_statuses' );

/**
 * Add the statuses to WooCommerce's list, after "Processing".
 *
 * @param array $statuses Statuses.
 * @return array
 */
function medzuro_order_statuses( $statuses ) {
	$out = array();
	foreach ( $statuses as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			foreach ( medzuro_custom_statuses() as $slug => $custom ) {
				$out[ 'wc-' . $slug ] = $custom;
			}
		}
	}
	return $out;
}
add_filter( 'wc_order_statuses', 'medzuro_order_statuses' );

/**
 * A shipped order has been paid in full.
 *
 * @param array $statuses Paid statuses.
 * @return array
 */
function medzuro_paid_statuses( $statuses ) {
	$statuses[] = 'shipped';
	return $statuses;
}
add_filter( 'woocommerce_order_is_paid_statuses', 'medzuro_paid_statuses' );

/**
 * Count the new statuses in WooCommerce reports/analytics.
 *
 * @param array|false $statuses Statuses.
 * @return array|false
 */
function medzuro_report_statuses( $statuses ) {
	if ( is_array( $statuses ) ) {
		$statuses = array_merge( $statuses, array( 'deposit-paid', 'ready-pickup', 'shipped' ) );
	}
	return $statuses;
}
add_filter( 'woocommerce_reports_order_statuses', 'medzuro_report_statuses' );

/* -------------------------------------------------------------------------
 * Deposit
 * ---------------------------------------------------------------------- */

/**
 * Whether an order uses the 10% pickup deposit.
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function medzuro_order_is_deposit( $order ) {
	return 'deposit' === $order->get_meta( '_mz_pickup_payment' ) && (float) $order->get_meta( '_mz_deposit_amount' ) > 0;
}

/**
 * Charge only the deposit through M-PAiSA for deposit orders.
 *
 * @param float    $amount Amount.
 * @param WC_Order $order  Order.
 * @return float
 */
function medzuro_mpaisa_deposit_amount( $amount, $order ) {
	return medzuro_order_is_deposit( $order ) ? (float) $order->get_meta( '_mz_deposit_amount' ) : $amount;
}
add_filter( 'medzuro_mpaisa_charge_amount', 'medzuro_mpaisa_deposit_amount', 10, 2 );

/**
 * Amount the customer has paid so far.
 *
 * @param WC_Order $order Order.
 * @return float
 */
function medzuro_order_amount_paid( $order ) {
	if ( $order->has_status( array( 'deposit-paid' ) ) || ( medzuro_order_is_deposit( $order ) && ! $order->is_paid() && ! $order->has_status( array( 'pending', 'failed', 'cancelled' ) ) ) ) {
		return (float) $order->get_meta( '_mz_deposit_amount' );
	}
	return $order->is_paid() || $order->has_status( 'completed' ) ? (float) $order->get_total() : 0.0;
}

/* -------------------------------------------------------------------------
 * "Reserve without payment" gateway (pickup only)
 * ---------------------------------------------------------------------- */

if ( class_exists( 'WC_Payment_Gateway' ) && ! class_exists( 'WC_Gateway_Medzuro_Reserve' ) ) {

	/**
	 * Reserve a pickup order and pay in store.
	 *
	 * Defined immediately rather than on a hook: this file loads from the
	 * theme, after WooCommerce has loaded (see inc/mpaisa-gateway.php).
	 */
	class WC_Gateway_Medzuro_Reserve extends WC_Payment_Gateway {

		public function __construct() {
			$this->id                 = 'medzuro_reserve';
			$this->method_title       = __( 'Reserve without payment (pickup)', 'medzuro' );
			$this->method_description = __( 'Lets pickup customers reserve an order and pay when they collect it. Only offered for store pickup.', 'medzuro' );
			$this->has_fields         = false;

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'   => __( 'Enable', 'medzuro' ),
					'type'    => 'checkbox',
					'label'   => __( 'Offer "Reserve without payment" for store pickup', 'medzuro' ),
					'default' => 'yes',
				),
				'title'       => array(
					'title'   => __( 'Title', 'medzuro' ),
					'type'    => 'text',
					'default' => __( 'Reserve without payment', 'medzuro' ),
				),
				'description' => array(
					'title'   => __( 'Description', 'medzuro' ),
					'type'    => 'textarea',
					'default' => __( 'Pay in full when you collect from our Nakasi store. Reservations are not guaranteed and are held for a limited time.', 'medzuro' ),
				),
			);
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$order->update_status( 'reserved', __( 'Reserved for pickup without payment.', 'medzuro' ) );
			wc_maybe_reduce_stock_levels( $order_id );
			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}

	add_filter(
		'woocommerce_payment_gateways',
		function ( $gateways ) {
			$gateways[] = 'WC_Gateway_Medzuro_Reserve';
			return $gateways;
		}
	);
}

/* -------------------------------------------------------------------------
 * Emails
 * ---------------------------------------------------------------------- */

/**
 * Send the standard "order received" emails for deposit and reserved orders,
 * which WooCommerce only sends for its own statuses.
 *
 * @param int $order_id Order id.
 */
function medzuro_new_status_emails( $order_id ) {
	$mailer = WC()->mailer();
	$emails = $mailer->get_emails();

	if ( isset( $emails['WC_Email_New_Order'] ) ) {
		$emails['WC_Email_New_Order']->trigger( $order_id );
	}
	if ( isset( $emails['WC_Email_Customer_Processing_Order'] ) ) {
		$emails['WC_Email_Customer_Processing_Order']->trigger( $order_id );
	}
}
add_action( 'woocommerce_order_status_pending_to_deposit-paid', 'medzuro_new_status_emails' );
add_action( 'woocommerce_order_status_failed_to_deposit-paid', 'medzuro_new_status_emails' );
add_action( 'woocommerce_order_status_pending_to_reserved', 'medzuro_new_status_emails' );

/**
 * Send a short branded email using WooCommerce's email template.
 *
 * @param WC_Order $order   Order.
 * @param string   $subject Subject.
 * @param string   $heading Heading.
 * @param string   $body    HTML body.
 */
function medzuro_send_customer_email( $order, $subject, $heading, $body ) {
	$to = $order->get_billing_email();
	if ( ! $to ) {
		return;
	}

	$mailer  = WC()->mailer();
	$message = $mailer->wrap_message( $heading, $body );
	$mailer->send( $to, $subject, $message, "Content-Type: text/html\r\n" );
}

/**
 * Email the customer when the order ships with DHL.
 *
 * @param int      $order_id Order id.
 * @param WC_Order $order    Order.
 */
function medzuro_email_shipped( $order_id, $order ) {
	$number = $order->get_meta( '_mz_dhl_tracking' );
	$body   = '<p>' . sprintf( esc_html__( 'Hi %s,', 'medzuro' ), esc_html( $order->get_billing_first_name() ) ) . '</p>';
	$body  .= '<p>' . sprintf( esc_html__( 'Good news - order #%s has been shipped with DHL Express.', 'medzuro' ), esc_html( $order->get_order_number() ) ) . '</p>';

	if ( $number ) {
		$body .= '<p><strong>' . esc_html__( 'Tracking number:', 'medzuro' ) . '</strong> ' . esc_html( $number ) . '</p>';
		$body .= '<p><a href="' . esc_url( medzuro_dhl_tracking_url( $number ) ) . '">' . esc_html__( 'Track your order on DHL', 'medzuro' ) . '</a></p>';
	}

	$body .= '<p>' . esc_html__( 'Delivery usually takes 3-7 working days.', 'medzuro' ) . '</p>';

	/* translators: %s: order number */
	medzuro_send_customer_email( $order, sprintf( __( 'Your Medzuro order #%s has shipped', 'medzuro' ), $order->get_order_number() ), __( 'Your order has shipped', 'medzuro' ), $body );
}
add_action( 'woocommerce_order_status_shipped', 'medzuro_email_shipped', 10, 2 );

/**
 * Email the customer when a pickup order is ready to collect.
 *
 * @param int      $order_id Order id.
 * @param WC_Order $order    Order.
 */
function medzuro_email_ready_pickup( $order_id, $order ) {
	$balance = (float) $order->get_total() - medzuro_order_amount_paid( $order );
	$body    = '<p>' . sprintf( esc_html__( 'Hi %s,', 'medzuro' ), esc_html( $order->get_billing_first_name() ) ) . '</p>';
	$body   .= '<p>' . sprintf( esc_html__( 'Order #%s is ready for pickup at our Nakasi, Suva store.', 'medzuro' ), esc_html( $order->get_order_number() ) ) . '</p>';

	if ( $balance > 0 ) {
		$body .= '<p><strong>' . esc_html__( 'Amount to pay at pickup:', 'medzuro' ) . '</strong> ' . wp_kses_post( wc_price( $balance ) ) . '</p>';
	}

	$body .= '<p>' . esc_html__( 'Please bring your order number when you collect.', 'medzuro' ) . '</p>';

	/* translators: %s: order number */
	medzuro_send_customer_email( $order, sprintf( __( 'Your Medzuro order #%s is ready for pickup', 'medzuro' ), $order->get_order_number() ), __( 'Ready for pickup', 'medzuro' ), $body );
}
add_action( 'woocommerce_order_status_ready-pickup', 'medzuro_email_ready_pickup', 10, 2 );

/* -------------------------------------------------------------------------
 * DHL tracking
 * ---------------------------------------------------------------------- */

/**
 * DHL Express tracking page for a waybill number.
 *
 * @param string $number Tracking number.
 * @return string
 */
function medzuro_dhl_tracking_url( $number ) {
	return apply_filters(
		'medzuro_dhl_tracking_url',
		'https://www.dhl.com/fj-en/home/tracking/tracking-express.html?submit=1&tracking-id=' . rawurlencode( $number ),
		$number
	);
}

/**
 * Tracking meta box on the order edit screen (classic and HPOS).
 */
function medzuro_tracking_meta_box() {
	$screens = array( 'shop_order' );
	if ( function_exists( 'wc_get_page_screen_id' ) ) {
		$screens[] = wc_get_page_screen_id( 'shop-order' );
	}

	foreach ( array_unique( $screens ) as $screen ) {
		add_meta_box( 'medzuro-dhl-tracking', __( 'DHL Express tracking', 'medzuro' ), 'medzuro_tracking_meta_box_html', $screen, 'side', 'high' );
	}
}
add_action( 'add_meta_boxes', 'medzuro_tracking_meta_box' );

/**
 * @param WP_Post|WC_Order $post_or_order Order.
 */
function medzuro_tracking_meta_box_html( $post_or_order ) {
	$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
	if ( ! $order ) {
		return;
	}

	$number = $order->get_meta( '_mz_dhl_tracking' );
	wp_nonce_field( 'medzuro_dhl_tracking', 'medzuro_dhl_tracking_nonce' );

	if ( medzuro_order_is_pickup( $order ) ) {
		echo '<p>' . esc_html__( 'Store pickup order. Set the status to "Ready for pickup" when packed - the customer is emailed - then "Completed" once collected.', 'medzuro' ) . '</p>';
		return;
	}
	?>
	<p>
		<label for="mz_dhl_tracking"><?php esc_html_e( 'Tracking number', 'medzuro' ); ?></label>
		<input type="text" id="mz_dhl_tracking" name="mz_dhl_tracking" value="<?php echo esc_attr( $number ); ?>" style="width:100%" />
	</p>
	<p class="description"><?php esc_html_e( 'Saving a tracking number marks the order "Shipped" and emails the customer.', 'medzuro' ); ?></p>
	<?php if ( $number ) : ?>
		<p><a href="<?php echo esc_url( medzuro_dhl_tracking_url( $number ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open on DHL', 'medzuro' ); ?></a></p>
	<?php endif; ?>
	<?php
}

/**
 * Save the tracking number; first save moves the order to "Shipped".
 *
 * @param int $order_id Order id.
 */
function medzuro_save_tracking( $order_id ) {
	if ( ! isset( $_POST['medzuro_dhl_tracking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['medzuro_dhl_tracking_nonce'] ) ), 'medzuro_dhl_tracking' ) ) {
		return;
	}
	if ( ! isset( $_POST['mz_dhl_tracking'] ) || ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$number = preg_replace( '/[^A-Za-z0-9-]/', '', sanitize_text_field( wp_unslash( $_POST['mz_dhl_tracking'] ) ) );
	$old    = $order->get_meta( '_mz_dhl_tracking' );

	if ( $number === $old ) {
		return;
	}

	$order->update_meta_data( '_mz_dhl_tracking', $number );
	$order->save();

	if ( $number && $order->has_status( array( 'processing', 'on-hold' ) ) ) {
		// Runs after WooCommerce saves the status field from the same form.
		add_action(
			'woocommerce_process_shop_order_meta',
			function ( $id ) use ( $order_id, $number ) {
				if ( (int) $id !== (int) $order_id ) {
					return;
				}
				$o = wc_get_order( $order_id );
				if ( $o && $o->has_status( array( 'processing', 'on-hold' ) ) ) {
					/* translators: %s: tracking number */
					$o->update_status( 'shipped', sprintf( __( 'Shipped with DHL Express, tracking %s.', 'medzuro' ), $number ) );
				}
			},
			999
		);
	} elseif ( $number && $order->has_status( 'shipped' ) ) {
		// Number corrected after shipping: resend the email.
		medzuro_email_shipped( $order_id, $order );
	}
}
add_action( 'woocommerce_process_shop_order_meta', 'medzuro_save_tracking', 5 );

/* -------------------------------------------------------------------------
 * Customer-facing progress
 * ---------------------------------------------------------------------- */

/**
 * Timeline steps and the index reached for an order.
 *
 * @param WC_Order $order Order.
 * @return array{steps: array, reached: int, pickup: bool}
 */
function medzuro_order_progress( $order ) {
	$pickup = medzuro_order_is_pickup( $order );

	if ( $pickup ) {
		$second = __( 'Payment confirmed', 'medzuro' );
		if ( $order->has_status( 'reserved' ) || 'reserve' === $order->get_meta( '_mz_pickup_payment' ) ) {
			$second = __( 'Reserved', 'medzuro' );
		} elseif ( medzuro_order_is_deposit( $order ) ) {
			$second = __( 'Deposit paid', 'medzuro' );
		}
		$steps = array(
			array( 'box', __( 'Order placed', 'medzuro' ) ),
			array( 'check', $second ),
			array( 'store', __( 'Ready for pickup', 'medzuro' ) ),
			array( 'bag', __( 'Collected', 'medzuro' ) ),
		);
	} else {
		$steps = array(
			array( 'box', __( 'Order placed', 'medzuro' ) ),
			array( 'check', __( 'Payment confirmed', 'medzuro' ) ),
			array( 'truck', __( 'Shipped with DHL', 'medzuro' ) ),
			array( 'bag', __( 'Delivered', 'medzuro' ) ),
		);
	}

	$reached = 0;
	if ( $order->has_status( array( 'processing', 'on-hold', 'deposit-paid', 'reserved', 'confirmed' ) ) ) {
		$reached = 1;
	} elseif ( $order->has_status( array( 'shipped', 'ready-pickup' ) ) ) {
		$reached = 2;
	} elseif ( $order->has_status( 'completed' ) ) {
		$reached = 3;
	}

	return array(
		'steps'   => $steps,
		'reached' => $reached,
		'pickup'  => $pickup,
	);
}

/**
 * Timeline + tracking above the order details (thank-you and My Account).
 *
 * @param WC_Order $order Order.
 */
function medzuro_order_timeline( $order ) {
	if ( ! $order instanceof WC_Order || $order->has_status( array( 'failed', 'cancelled', 'refunded' ) ) ) {
		return;
	}

	$p      = medzuro_order_progress( $order );
	$number = $order->get_meta( '_mz_dhl_tracking' );
	?>
	<section class="mz-track" aria-label="<?php esc_attr_e( 'Order progress', 'medzuro' ); ?>">
		<ol class="mz-track__steps">
			<?php foreach ( $p['steps'] as $i => $step ) : ?>
				<li class="mz-track__step<?php echo $i <= $p['reached'] ? ' is-done' : ''; ?><?php echo $i === $p['reached'] ? ' is-current' : ''; ?>">
					<span class="mz-track__icon"><?php medzuro_icon( $step[0], 20 ); ?></span>
					<span class="mz-track__label"><?php echo esc_html( $step[1] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>

		<?php if ( $number ) : ?>
			<div class="mz-track__dhl">
				<?php medzuro_dhl_badge(); ?>
				<span><?php esc_html_e( 'Tracking number', 'medzuro' ); ?> <strong><?php echo esc_html( $number ); ?></strong></span>
				<a class="button alt" href="<?php echo esc_url( medzuro_dhl_tracking_url( $number ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Track your order', 'medzuro' ); ?> <?php medzuro_icon( 'external', 14 ); ?></a>
			</div>
		<?php endif; ?>
	</section>
	<?php
}
add_action( 'woocommerce_order_details_before_order_table', 'medzuro_order_timeline', 5 );

/**
 * Tracking number in the customer's order emails.
 *
 * @param WC_Order $order         Order.
 * @param bool     $sent_to_admin Admin email.
 * @param bool     $plain_text    Plain text.
 */
function medzuro_email_tracking( $order, $sent_to_admin, $plain_text ) {
	$number = $order->get_meta( '_mz_dhl_tracking' );
	if ( ! $number ) {
		return;
	}
	if ( $plain_text ) {
		echo "\n" . esc_html__( 'DHL tracking:', 'medzuro' ) . ' ' . esc_html( $number ) . ' - ' . esc_url_raw( medzuro_dhl_tracking_url( $number ) ) . "\n";
		return;
	}
	echo '<p><strong>' . esc_html__( 'DHL tracking:', 'medzuro' ) . '</strong> <a href="' . esc_url( medzuro_dhl_tracking_url( $number ) ) . '">' . esc_html( $number ) . '</a></p>';
}
add_action( 'woocommerce_email_order_meta', 'medzuro_email_tracking', 20, 3 );

/**
 * Deposit/balance lines on order totals (thank-you, My Account, emails).
 *
 * @param array    $rows  Total rows.
 * @param WC_Order $order Order.
 * @return array
 */
function medzuro_order_totals_rows( $rows, $order ) {
	$option = $order->get_meta( '_mz_pickup_payment' );

	if ( 'deposit' === $option ) {
		$deposit = (float) $order->get_meta( '_mz_deposit_amount' );
		$rows['mz_deposit'] = array(
			'label' => __( 'Deposit (10%):', 'medzuro' ),
			'value' => wc_price( $deposit, array( 'currency' => $order->get_currency() ) ),
		);
		if ( ! $order->has_status( 'completed' ) ) {
			$rows['mz_balance'] = array(
				'label' => __( 'Balance at pickup:', 'medzuro' ),
				'value' => wc_price( (float) $order->get_total() - $deposit, array( 'currency' => $order->get_currency() ) ),
			);
		}
	} elseif ( 'reserve' === $option && ! $order->has_status( 'completed' ) ) {
		$rows['mz_balance'] = array(
			'label' => __( 'Pay at pickup:', 'medzuro' ),
			'value' => wc_price( (float) $order->get_total(), array( 'currency' => $order->get_currency() ) ),
		);
	}

	return $rows;
}
add_filter( 'woocommerce_get_order_item_totals', 'medzuro_order_totals_rows', 10, 2 );


/* -------------------------------------------------------------------------
 * Admin follow-up box (design 10)
 * ---------------------------------------------------------------------- */

/**
 * Payment status label and tone for an order.
 *
 * @param WC_Order $order Order.
 * @return array{0:string,1:string} Label, tone (pending|partial|paid).
 */
function medzuro_payment_badge( $order ) {
	if ( $order->is_paid() || $order->has_status( 'completed' ) ) {
		return array( __( 'Paid', 'medzuro' ), 'paid' );
	}
	if ( $order->has_status( 'deposit-paid' ) || ( medzuro_order_is_deposit( $order ) && medzuro_order_amount_paid( $order ) > 0 ) ) {
		return array( __( '10% paid', 'medzuro' ), 'partial' );
	}
	return array( __( 'Payment pending', 'medzuro' ), 'pending' );
}

/**
 * Register the follow-up box on the order screen.
 */
function medzuro_followup_meta_box() {
	$screens = array( 'shop_order' );
	if ( function_exists( 'wc_get_page_screen_id' ) ) {
		$screens[] = wc_get_page_screen_id( 'shop-order' );
	}
	foreach ( array_unique( $screens ) as $screen ) {
		add_meta_box( 'medzuro-followup', __( 'Order follow-up', 'medzuro' ), 'medzuro_followup_meta_box_html', $screen, 'side', 'high' );
	}
}
add_action( 'add_meta_boxes', 'medzuro_followup_meta_box', 5 );

/**
 * @param WP_Post|WC_Order $post_or_order Order.
 */
function medzuro_followup_meta_box_html( $post_or_order ) {
	$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
	if ( ! $order ) {
		return;
	}

	list( $label, $tone ) = medzuro_payment_badge( $order );

	$pickup  = medzuro_order_is_pickup( $order );
	$option  = $order->get_meta( '_mz_pickup_payment' );
	$labels  = medzuro_pickup_payment_options();
	$phone   = preg_replace( '/\D+/', '', $order->get_billing_phone() );
	$contact = $order->get_meta( '_billing_contact_method' );
	$paid    = medzuro_order_amount_paid( $order );
	$store   = medzuro_pickup_store();
	$action  = function ( $do ) use ( $order ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=medzuro_order_action&do=' . $do . '&order_id=' . $order->get_id() ), 'medzuro_order_action_' . $order->get_id() );
	};
	?>
	<div class="mz-fu">
		<p><span class="mz-fu__badge mz-fu__badge--<?php echo esc_attr( $tone ); ?>"><?php echo esc_html( $label ); ?></span></p>
		<table class="mz-fu__table">
			<tr><th><?php esc_html_e( 'Customer', 'medzuro' ); ?></th><td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?><br /><?php echo esc_html( $order->get_billing_phone() ); ?><br /><?php echo esc_html( $order->get_billing_email() ); ?><?php echo $contact ? '<br /><em>' . esc_html( sprintf( __( 'Prefers %s', 'medzuro' ), ucfirst( $contact ) ) ) . '</em>' : ''; ?></td></tr>
			<tr><th><?php esc_html_e( 'Order type', 'medzuro' ); ?></th><td><?php echo esc_html( $pickup ? __( 'Pickup', 'medzuro' ) : __( 'Home delivery (DHL)', 'medzuro' ) ); ?></td></tr>
			<?php if ( $pickup ) : ?>
				<tr><th><?php esc_html_e( 'Pickup location', 'medzuro' ); ?></th><td><?php echo esc_html( $store['name'] ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Payment option', 'medzuro' ); ?></th><td><?php echo esc_html( isset( $labels[ $option ] ) ? $labels[ $option ]['short'] : __( 'Full payment', 'medzuro' ) ); ?></td></tr>
			<?php endif; ?>
			<tr><th><?php esc_html_e( 'Total', 'medzuro' ); ?></th><td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td></tr>
			<tr><th><?php esc_html_e( 'Paid so far', 'medzuro' ); ?></th><td><?php echo wp_kses_post( wc_price( $paid, array( 'currency' => $order->get_currency() ) ) ); ?></td></tr>
			<tr><th><?php esc_html_e( 'Created', 'medzuro' ); ?></th><td><?php echo esc_html( wc_format_datetime( $order->get_date_created(), get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td></tr>
		</table>

		<div class="mz-fu__actions">
			<?php if ( $phone ) : ?>
				<a class="button" href="<?php echo esc_url( 'viber://chat?number=%2B' . $phone, array( 'viber' ) ); ?>"><?php esc_html_e( 'Contact customer (Viber)', 'medzuro' ); ?></a>
				<a class="button" href="<?php echo esc_url( 'tel:+' . $phone ); ?>"><?php esc_html_e( 'Call customer', 'medzuro' ); ?></a>
			<?php endif; ?>
			<?php if ( $order->get_billing_email() ) : ?>
				<a class="button" href="<?php echo esc_url( 'mailto:' . $order->get_billing_email() . '?subject=' . rawurlencode( sprintf( __( 'Your Medzuro order #%s', 'medzuro' ), $order->get_order_number() ) ) ); ?>"><?php esc_html_e( 'Send email', 'medzuro' ); ?></a>
			<?php endif; ?>
			<?php if ( $order->has_status( 'reserved' ) ) : ?>
				<a class="button button-primary" href="<?php echo esc_url( $action( 'confirm' ) ); ?>"><?php esc_html_e( 'Mark as confirmed', 'medzuro' ); ?></a>
			<?php endif; ?>
			<?php if ( ! $order->has_status( array( 'completed', 'cancelled', 'refunded' ) ) ) : ?>
				<a class="button mz-fu__cancel" href="<?php echo esc_url( $action( 'cancel' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Cancel this order? Stock will be returned.', 'medzuro' ) ); ?>');"><?php esc_html_e( 'Cancel order', 'medzuro' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Handle "Mark as confirmed" / "Cancel order" from the follow-up box.
 */
function medzuro_handle_order_action() {
	$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
	$do       = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';

	check_admin_referer( 'medzuro_order_action_' . $order_id );

	if ( ! current_user_can( 'edit_shop_orders' ) ) {
		wp_die( esc_html__( 'You are not allowed to change orders.', 'medzuro' ) );
	}

	$order = wc_get_order( $order_id );
	if ( $order ) {
		if ( 'confirm' === $do && $order->has_status( 'reserved' ) ) {
			$order->update_status( 'confirmed', __( 'Reservation confirmed by staff.', 'medzuro' ) );
		} elseif ( 'cancel' === $do && ! $order->has_status( array( 'completed', 'cancelled', 'refunded' ) ) ) {
			$order->update_status( 'cancelled', __( 'Cancelled from the follow-up box.', 'medzuro' ) );
		}
	}

	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=wc-orders' ) );
	exit;
}
add_action( 'admin_post_medzuro_order_action', 'medzuro_handle_order_action' );

/**
 * Email the customer when a reservation is confirmed.
 *
 * @param int      $order_id Order id.
 * @param WC_Order $order    Order.
 */
function medzuro_email_confirmed( $order_id, $order ) {
	$store = medzuro_pickup_store();
	$body  = '<p>' . sprintf( esc_html__( 'Hi %s,', 'medzuro' ), esc_html( $order->get_billing_first_name() ) ) . '</p>';
	$body .= '<p>' . sprintf( esc_html__( 'Good news - we have confirmed stock for order #%s and reserved it for you.', 'medzuro' ), esc_html( $order->get_order_number() ) ) . '</p>';
	$body .= '<p><strong>' . esc_html__( 'Pay at pickup:', 'medzuro' ) . '</strong> ' . wp_kses_post( wc_price( (float) $order->get_total() ) ) . '<br />';
	$body .= '<strong>' . esc_html__( 'Pickup:', 'medzuro' ) . '</strong> ' . esc_html( $store['name'] ) . ', ' . esc_html( $store['hours'] ) . '</p>';

	/* translators: %s: order number */
	medzuro_send_customer_email( $order, sprintf( __( 'Your Medzuro order #%s is confirmed', 'medzuro' ), $order->get_order_number() ), __( 'Reservation confirmed', 'medzuro' ), $body );
}
add_action( 'woocommerce_order_status_confirmed', 'medzuro_email_confirmed', 10, 2 );

/**
 * Status colours in the orders list and the follow-up box styles.
 */
function medzuro_admin_order_styles() {
	?>
	<style>
		.order-status.status-reserved { background: #fde7e9; color: #b0101c; }
		.order-status.status-deposit-paid { background: #fff1d6; color: #8a5300; }
		.order-status.status-confirmed, .order-status.status-ready-pickup { background: #dbeafe; color: #1e3a8a; }
		.order-status.status-shipped { background: #c6e1c6; color: #2c4700; }
		.mz-fu__badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-weight: 600; }
		.mz-fu__badge--pending { background: #fde7e9; color: #b0101c; }
		.mz-fu__badge--partial { background: #fff1d6; color: #8a5300; }
		.mz-fu__badge--paid { background: #dcf2e1; color: #1a7f3c; }
		.mz-fu__table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
		.mz-fu__table th { text-align: left; vertical-align: top; width: 38%; padding: 4px 6px 4px 0; color: #50575e; font-weight: 600; }
		.mz-fu__table td { padding: 4px 0; word-break: break-word; }
		.mz-fu__actions { display: grid; gap: 6px; }
		.mz-fu__actions .button { text-align: center; }
		.mz-fu__cancel { color: #b32d2e !important; border-color: #b32d2e !important; }
	</style>
	<?php
}
add_action( 'admin_head', 'medzuro_admin_order_styles' );
