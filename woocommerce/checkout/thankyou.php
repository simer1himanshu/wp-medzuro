<?php
/**
 * Order received ("Payment Successful!").
 *
 * Overrides woocommerce/templates/checkout/thankyou.php with the client's
 * confirmation screen: status, order number, payment method, amount paid,
 * payment status and next steps. The order progress timeline and the order
 * details table follow, from the standard hooks.
 *
 * @package Medzuro
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order mz-receipt">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );

		$mz_pickup  = medzuro_order_is_pickup( $order );
		$mz_option  = $order->get_meta( '_mz_pickup_payment' );
		$mz_paid    = medzuro_order_amount_paid( $order );
		$mz_balance = max( 0, (float) $order->get_total() - $mz_paid );
		$mz_contact = $order->get_meta( '_billing_contact_method' );
		$mz_contact = $mz_contact ? ucfirst( $mz_contact ) : __( 'email', 'medzuro' );

		if ( $order->has_status( 'failed' ) ) {
			$mz_state = 'failed';
		} elseif ( $order->has_status( 'reserved' ) ) {
			$mz_state = 'reserved';
		} elseif ( $order->has_status( 'deposit-paid' ) ) {
			$mz_state = 'deposit';
		} elseif ( $order->is_paid() || $order->has_status( array( 'completed', 'ready-pickup' ) ) ) {
			$mz_state = 'paid';
		} else {
			$mz_state = 'waiting';
		}

		$mz_copy = array(
			'paid'     => array( __( 'Payment successful!', 'medzuro' ), __( 'Your order has been placed.', 'medzuro' ), __( 'Completed', 'medzuro' ) ),
			'deposit'  => array( __( 'Payment successful!', 'medzuro' ), __( 'Your order has been successfully reserved.', 'medzuro' ), __( '10% paid', 'medzuro' ) ),
			'reserved' => array( __( 'Order request received!', 'medzuro' ), __( 'We have received your order request. Stock is subject to availability until payment is received.', 'medzuro' ), __( 'Payment pending', 'medzuro' ) ),
			'waiting'  => array( __( 'Waiting for payment confirmation', 'medzuro' ), __( 'If you completed the payment on your phone, this can take a minute. Refresh this page to check.', 'medzuro' ), __( 'Awaiting payment', 'medzuro' ) ),
			'failed'   => array( __( 'Payment not completed', 'medzuro' ), __( 'Your payment was declined or cancelled. You can try again below.', 'medzuro' ), __( 'Failed', 'medzuro' ) ),
		);
		list( $mz_title, $mz_sub, $mz_status ) = $mz_copy[ $mz_state ];

		if ( $mz_pickup && 'paid' === $mz_state ) {
			$mz_sub = __( 'Your order has been successfully reserved.', 'medzuro' );
		}

		$mz_store     = medzuro_pickup_store();
		$mz_paid_note = 'deposit' === $mz_option ? __( '(10% payment)', 'medzuro' ) : __( '(Full payment)', 'medzuro' );
		?>

		<section class="mz-receipt__card mz-receipt__card--<?php echo esc_attr( $mz_state ); ?>">
			<span class="mz-receipt__icon" aria-hidden="true">
				<?php medzuro_icon( in_array( $mz_state, array( 'waiting', 'failed' ), true ) ? ( 'failed' === $mz_state ? 'close' : 'clock' ) : 'check', 40 ); ?>
			</span>
			<h1 class="mz-receipt__ttl"><?php echo esc_html( $mz_title ); ?></h1>
			<p class="mz-receipt__sub"><?php echo esc_html( $mz_sub ); ?></p>

			<dl class="mz-receipt__facts woocommerce-order-overview order_details">
				<div><dt><?php esc_html_e( 'Order number', 'medzuro' ); ?></dt><dd>#<?php echo esc_html( $order->get_order_number() ); ?></dd></div>
				<div><dt><?php esc_html_e( 'Date', 'medzuro' ); ?></dt><dd><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></dd></div>
				<?php if ( $order->get_payment_method_title() ) : ?>
					<div><dt><?php esc_html_e( 'Payment method', 'medzuro' ); ?></dt><dd><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></dd></div>
				<?php endif; ?>
				<div><dt><?php esc_html_e( 'Payment made', 'medzuro' ); ?></dt><dd><?php echo wp_kses_post( wc_price( $mz_paid, array( 'currency' => $order->get_currency() ) ) ); ?><?php if ( $mz_pickup && $mz_paid > 0 ) : ?> <small><?php echo esc_html( $mz_paid_note ); ?></small><?php endif; ?></dd></div>
				<?php if ( $mz_balance > 0 && in_array( $mz_state, array( 'deposit', 'reserved' ), true ) ) : ?>
					<div><dt><?php esc_html_e( 'Balance at pickup', 'medzuro' ); ?></dt><dd><?php echo wp_kses_post( wc_price( $mz_balance, array( 'currency' => $order->get_currency() ) ) ); ?></dd></div>
				<?php endif; ?>
				<div><dt><?php esc_html_e( 'Payment status', 'medzuro' ); ?></dt><dd class="mz-receipt__status"><?php echo esc_html( $mz_status ); ?></dd></div>
				<?php if ( $mz_pickup ) : ?>
					<div class="mz-receipt__stack"><dt><?php esc_html_e( 'Pickup location', 'medzuro' ); ?></dt><dd><?php echo esc_html( $mz_store['name'] ); ?></dd></div>
					<div class="mz-receipt__stack"><dt><?php esc_html_e( 'Pickup hours', 'medzuro' ); ?></dt><dd><?php echo esc_html( $mz_store['hours'] ); ?></dd></div>
				<?php endif; ?>
			</dl>

			<?php if ( 'reserved' === $mz_state ) : ?>
				<div class="mz-receipt__alert" role="note">
					<?php medzuro_icon( 'alert', 20 ); ?>
					<span><?php esc_html_e( 'This is not a confirmed reservation. Our team will contact you shortly to confirm availability and payment.', 'medzuro' ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( 'failed' === $mz_state || ( 'waiting' === $mz_state && $order->needs_payment() ) ) : ?>
				<p class="mz-receipt__actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button alt"><?php esc_html_e( 'Pay now', 'medzuro' ); ?></a>
				</p>
			<?php endif; ?>

			<?php if ( 'reserved' === $mz_state ) : ?>
				<div class="mz-receipt__next">
					<h2><?php esc_html_e( 'What happens next?', 'medzuro' ); ?></h2>
					<ol class="mz-receipt__steps">
						<li><?php esc_html_e( 'Our team will review your order.', 'medzuro' ); ?></li>
						<li>
							<?php
							/* translators: %s: Viber / Phone / Email */
							printf( esc_html__( 'We may contact you by %s to confirm availability.', 'medzuro' ), esc_html( $mz_contact ) );
							?>
						</li>
						<li><?php esc_html_e( 'Once payment is received, your order will be reserved.', 'medzuro' ); ?></li>
					</ol>
				</div>
			<?php elseif ( 'failed' !== $mz_state ) : ?>
				<div class="mz-receipt__next">
					<h2><?php esc_html_e( 'Next steps', 'medzuro' ); ?></h2>
					<ul>
						<?php if ( $mz_pickup ) : ?>
							<li><?php medzuro_icon( 'mail', 18 ); ?> <span><?php esc_html_e( 'You will also receive a confirmation by SMS/Email.', 'medzuro' ); ?></span></li>
							<li><?php medzuro_icon( 'store', 18 ); ?> <span>
								<?php
								/* translators: %s: Viber / Phone / Email */
								printf( esc_html__( 'Our team will notify you by %s when your order is ready for pickup.', 'medzuro' ), esc_html( $mz_contact ) );
								?>
							</span></li>
							<?php if ( $mz_balance > 0 ) : ?>
								<li><?php medzuro_icon( 'coin', 18 ); ?> <span>
									<?php
									/* translators: %s: amount */
									printf( esc_html__( 'Please pay the remaining %s before or at pickup.', 'medzuro' ), wp_kses_post( wc_price( $mz_balance ) ) );
									?>
								</span></li>
							<?php endif; ?>
						<?php else : ?>
							<li><?php medzuro_icon( 'mail', 18 ); ?> <span><?php esc_html_e( 'You will receive an order confirmation by email.', 'medzuro' ); ?></span></li>
							<li><?php medzuro_icon( 'box', 18 ); ?> <span><?php esc_html_e( 'We will prepare your order for shipment.', 'medzuro' ); ?></span></li>
							<li><?php medzuro_icon( 'truck', 18 ); ?> <span><?php esc_html_e( 'You will receive a DHL tracking number once your order is shipped.', 'medzuro' ); ?></span></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>

			<p class="mz-receipt__actions">
				<a href="<?php echo esc_url( is_user_logged_in() && $order->get_user_id() === get_current_user_id() ? $order->get_view_order_url() : '#mz-details' ); ?>" class="button alt"><?php esc_html_e( 'View order details', 'medzuro' ); ?></a>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button"><?php esc_html_e( 'Continue shopping', 'medzuro' ); ?></a>
			</p>
		</section>

		<div id="mz-details"></div>
		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
