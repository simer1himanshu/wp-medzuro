<?php
/**
 * Multi-step checkout form: Delivery -> Details -> Payment.
 *
 * Overrides woocommerce/templates/checkout/form-checkout.php. Everything is
 * still one classic checkout form, so WooCommerce's own validation, AJAX
 * order review and payment processing are unchanged; the panels are shown
 * one at a time by assets/js/checkout-steps.js. Without JavaScript all
 * panels are visible and the form still works.
 *
 * Panels (and the stepper stage each belongs to):
 *   delivery (Delivery) - home delivery with DHL Express or store pickup
 *   details  (Details)  - name, +679 mobile, email, contact preference
 *   address  (Details)  - delivery address, or pickup location + instructions
 *   review   (Details)  - order summary
 *   payopt   (Payment)  - pickup only: full / 10% / reserve without payment
 *   payment  (Payment)  - payment method, amount or notice, place order
 *
 * @package Medzuro
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

list( $mz_rates, $mz_chosen ) = medzuro_current_rates();

$mz_fields      = $checkout->get_checkout_fields();
$mz_billing     = isset( $mz_fields['billing'] ) ? $mz_fields['billing'] : array();
$mz_panel_keys  = medzuro_checkout_panel_fields();
$mz_is_pickup   = medzuro_rate_is_pickup( $mz_chosen );
$mz_pickup_opts = medzuro_pickup_payment_options();
$mz_pickup_pick = medzuro_pickup_payment_choice();
$mz_pickup_rate = null;
$mz_store       = medzuro_pickup_store();

foreach ( $mz_rates as $mz_rate ) {
	if ( 'pickup' === ( $mz_rate->get_meta_data()['type'] ?? '' ) ) {
		$mz_pickup_rate = $mz_rate;
	}
}

/**
 * Render one billing field by key.
 *
 * @param string $key Field key.
 */
$mz_field = function ( $key ) use ( $mz_billing, $checkout ) {
	if ( ! isset( $mz_billing[ $key ] ) ) {
		return;
	}

	if ( 'billing_contact_method' === $key ) {
		$value = $checkout->get_value( $key );
		$value = $value ? $value : $mz_billing[ $key ]['default'];
		echo '<fieldset class="form-row form-row-wide mz-contact-method validate-required" id="billing_contact_method_field">';
		echo '<legend>' . esc_html( $mz_billing[ $key ]['label'] ) . '</legend><div class="mz-contact-method__opts">';
		foreach ( $mz_billing[ $key ]['options'] as $opt => $label ) {
			printf(
				'<label class="mz-radio"><input type="radio" name="billing_contact_method" value="%1$s" %2$s /> <span>%3$s</span></label>',
				esc_attr( $opt ),
				checked( $value, $opt, false ),
				esc_html( $label )
			);
		}
		echo '</div></fieldset>';
		return;
	}

	if ( 'billing_phone' === $key ) {
		$value = preg_replace( '/^\+?679\s*/', '', (string) $checkout->get_value( $key ) );
		echo '<div class="mz-phone-row">';
		echo '<span class="mz-phone-row__prefix" aria-hidden="true"><span class="mz-flag" aria-hidden="true"></span>+679</span>';
		woocommerce_form_field( $key, $mz_billing[ $key ], $value );
		echo '</div>';
		return;
	}

	woocommerce_form_field( $key, $mz_billing[ $key ], $checkout->get_value( $key ) );
};

$mz_stages = array(
	'delivery' => __( 'Delivery', 'medzuro' ),
	'details'  => __( 'Details', 'medzuro' ),
	'payment'  => __( 'Payment', 'medzuro' ),
);
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout mz-co<?php echo $mz_is_pickup ? ' is-pickup' : ' is-delivery'; ?>" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<ol class="mz-stepper" aria-label="<?php esc_attr_e( 'Checkout progress', 'medzuro' ); ?>">
		<?php foreach ( $mz_stages as $mz_key => $mz_label ) : ?>
			<li class="mz-stepper__step" data-stage="<?php echo esc_attr( $mz_key ); ?>">
				<span class="mz-stepper__dot"><?php medzuro_icon( 'check', 12 ); ?></span>
				<span class="mz-stepper__label"><?php echo esc_html( $mz_label ); ?></span>
			</li>
		<?php endforeach; ?>
	</ol>

	<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

	<div id="customer_details" class="mz-co__panels">

		<?php /* 1. Delivery choice */ ?>
		<section class="mz-panel" data-panel="delivery" data-stage="delivery" id="mz-panel-delivery">
			<h2 class="mz-panel__ttl"><?php esc_html_e( 'Choose how you want to receive', 'medzuro' ); ?></h2>
			<?php
			if ( $mz_rates ) {
				medzuro_delivery_cards( $mz_rates, $mz_chosen, 0, true );
			} else {
				echo '<p>' . esc_html__( 'There are no delivery options available right now. Please contact us and we will help.', 'medzuro' ) . '</p>';
			}
			?>
			<div class="mz-panel__actions">
				<button type="button" class="button alt mz-next"><?php esc_html_e( 'Continue', 'medzuro' ); ?></button>
			</div>
		</section>

		<?php /* 2. Contact details */ ?>
		<section class="mz-panel" data-panel="details" data-stage="details" id="mz-panel-details">
			<h2 class="mz-panel__ttl"><?php esc_html_e( 'Contact information', 'medzuro' ); ?></h2>
			<div class="woocommerce-billing-fields__field-wrapper mz-fields">
				<?php
				do_action( 'woocommerce_before_checkout_billing_form', $checkout );
				foreach ( $mz_panel_keys['details'] as $mz_key ) {
					$mz_field( $mz_key );
				}
				?>
			</div>
			<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
				<div class="woocommerce-account-fields">
					<?php if ( ! $checkout->is_registration_required() ) : ?>
						<p class="form-row form-row-wide create-account">
							<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
								<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
							</label>
						</p>
					<?php endif; ?>
					<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
						<div class="create-account">
							<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $mz_key => $mz_def ) : ?>
								<?php woocommerce_form_field( $mz_key, $mz_def, $checkout->get_value( $mz_key ) ); ?>
							<?php endforeach; ?>
							<div class="clear"></div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="mz-panel__actions">
				<button type="button" class="button mz-back"><?php esc_html_e( 'Back', 'medzuro' ); ?></button>
				<button type="button" class="button alt mz-next"><?php esc_html_e( 'Continue', 'medzuro' ); ?></button>
			</div>
			<p class="mz-secure-note"><?php medzuro_icon( 'lock', 14 ); ?> <?php esc_html_e( 'Your information is secure', 'medzuro' ); ?></p>
		</section>

		<?php /* 3. Delivery address or pickup */ ?>
		<section class="mz-panel" data-panel="address" data-stage="details" id="mz-panel-address">

			<div class="mz-only-delivery">
				<h2 class="mz-panel__ttl"><?php esc_html_e( 'Delivery address', 'medzuro' ); ?></h2>
				<div class="mz-method-box">
					<span class="mz-method-box__check"><?php medzuro_icon( 'check', 14 ); ?></span>
					<?php medzuro_dhl_badge(); ?>
					<span>
						<strong><?php esc_html_e( 'DHL Express Delivery', 'medzuro' ); ?></strong>
						<small><?php esc_html_e( '3-7 working days · Free shipping (Fiji wide)', 'medzuro' ); ?></small>
					</span>
				</div>
				<div class="woocommerce-billing-fields__field-wrapper mz-fields mz-fields--address">
					<?php
					foreach ( $mz_panel_keys['address'] as $mz_key ) {
						$mz_field( $mz_key );
					}
					?>
				</div>
				<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
				<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>
				<div class="woocommerce-additional-fields__field-wrapper">
					<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $mz_key => $mz_def ) : ?>
						<?php woocommerce_form_field( $mz_key, $mz_def, $checkout->get_value( $mz_key ) ); ?>
					<?php endforeach; ?>
				</div>
				<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
			</div>

			<div class="mz-only-pickup">
				<h2 class="mz-panel__ttl"><?php esc_html_e( 'Select pickup location', 'medzuro' ); ?></h2>
				<label class="mz-store">
					<input type="radio" name="mz_pickup_store" value="nakasi" checked />
					<span class="mz-store__radio" aria-hidden="true"></span>
					<span class="mz-store__body">
						<strong class="mz-store__name"><?php echo esc_html( $mz_store['name'] ); ?></strong>
						<?php if ( $mz_store['image'] ) : ?>
							<img class="mz-store__img" src="<?php echo esc_url( $mz_store['image'] ); ?>" alt="<?php echo esc_attr( $mz_store['name'] ); ?>" loading="lazy" />
						<?php else : ?>
							<span class="mz-store__img mz-store__img--ph"><?php medzuro_icon( 'store', 44 ); ?></span>
						<?php endif; ?>
						<span class="mz-store__addr"><?php echo esc_html( $mz_store['street'] ); ?></span>
						<span class="mz-store__hours">
							<?php
							/* translators: %s: opening hours */
							printf( esc_html__( 'Open: %s', 'medzuro' ), esc_html( $mz_store['hours'] ) );
							?>
						</span>
					</span>
				</label>
				<?php if ( $mz_store['map'] ) : ?>
					<p class="mz-store__map"><a href="<?php echo esc_url( $mz_store['map'] ); ?>" target="_blank" rel="noopener"><?php medzuro_icon( 'map', 16 ); ?> <?php esc_html_e( 'View on Google Maps', 'medzuro' ); ?></a></p>
				<?php endif; ?>

				<p class="form-row form-row-wide" id="mz_pickup_note_field">
					<label for="mz_pickup_note"><?php esc_html_e( 'Pickup instructions', 'medzuro' ); ?> <span class="optional">(<?php esc_html_e( 'optional', 'medzuro' ); ?>)</span></label>
					<span class="woocommerce-input-wrapper"><textarea id="mz_pickup_note" name="mz_pickup_note" class="input-text" rows="3" placeholder="<?php esc_attr_e( 'e.g. Preferred pickup date or time', 'medzuro' ); ?>"></textarea></span>
				</p>
			</div>

			<div class="mz-panel__actions">
				<button type="button" class="button mz-back"><?php esc_html_e( 'Back', 'medzuro' ); ?></button>
				<button type="button" class="button alt mz-next"><?php esc_html_e( 'Continue', 'medzuro' ); ?></button>
			</div>
		</section>
	</div>

	<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order mz-co__panels">

		<?php /* 4. Order summary */ ?>
		<section class="mz-panel" data-panel="review" data-stage="details" id="mz-panel-review">
			<h2 class="mz-panel__ttl" id="order_review_heading"><?php esc_html_e( 'Your order', 'medzuro' ); ?></h2>
			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
			<?php woocommerce_order_review(); ?>

			<div class="mz-review-to mz-only-delivery">
				<div class="mz-review-to__head">
					<strong><?php esc_html_e( 'Delivery address', 'medzuro' ); ?></strong>
					<button type="button" class="mz-link mz-goto" data-goto="address"><?php medzuro_icon( 'edit', 14 ); ?> <?php esc_html_e( 'Edit', 'medzuro' ); ?></button>
				</div>
				<p class="mz-review-to__body" data-mz-summary="address"></p>
				<p class="mz-review-to__body" data-mz-summary="contact"></p>
			</div>

			<div class="mz-review-to mz-review-to--pickup mz-only-pickup">
				<div class="mz-review-to__head">
					<strong><?php esc_html_e( 'Pickup location', 'medzuro' ); ?></strong>
					<button type="button" class="mz-link mz-goto" data-goto="address"><?php esc_html_e( 'Change', 'medzuro' ); ?></button>
				</div>
				<p class="mz-review-to__store"><?php medzuro_icon( 'store', 22 ); ?> <span><?php echo esc_html( $mz_store['name'] ); ?></span></p>
				<p class="mz-review-to__body" data-mz-summary="contact"></p>
			</div>

			<div class="mz-panel__actions">
				<button type="button" class="button mz-back"><?php esc_html_e( 'Back', 'medzuro' ); ?></button>
				<button type="button" class="button alt mz-next">
					<span class="mz-only-pickup"><?php esc_html_e( 'Continue to payment options', 'medzuro' ); ?></span>
					<span class="mz-only-delivery"><?php esc_html_e( 'Continue', 'medzuro' ); ?></span>
				</button>
			</div>
		</section>

		<?php /* 5. Pickup only: how would you like to pay? */ ?>
		<section class="mz-panel" data-panel="payopt" data-stage="payment" id="mz-panel-payopt">
			<h2 class="mz-panel__ttl"><?php esc_html_e( 'How would you like to pay?', 'medzuro' ); ?></h2>
			<div class="mz-pay-options update_totals_on_change" role="radiogroup">
				<?php foreach ( $mz_pickup_opts as $mz_key => $mz_opt ) : ?>
					<label class="mz-pay-option mz-pay-option--<?php echo esc_attr( $mz_key ); ?>">
						<input type="radio" name="mz_pickup_payment" value="<?php echo esc_attr( $mz_key ); ?>" <?php checked( $mz_pickup_pick, $mz_key ); ?> />
						<span class="mz-pay-option__radio" aria-hidden="true"></span>
						<span class="mz-pay-option__icon"><?php medzuro_icon( $mz_opt['icon'], 30 ); ?></span>
						<span class="mz-pay-option__body">
							<strong><?php echo esc_html( $mz_opt['title'] ); ?></strong>
							<small><?php echo esc_html( $mz_opt['desc'] ); ?></small>
							<ul>
								<?php foreach ( $mz_opt['bullets'] as $mz_bullet ) : ?>
									<li><?php echo esc_html( $mz_bullet ); ?></li>
								<?php endforeach; ?>
							</ul>
						</span>
						<?php if ( $mz_opt['badge'] ) : ?>
							<em class="mz-pay-option__badge"><?php echo esc_html( $mz_opt['badge'] ); ?></em>
						<?php endif; ?>
					</label>
				<?php endforeach; ?>
			</div>
			<div class="mz-panel__actions">
				<button type="button" class="button mz-back"><?php esc_html_e( 'Back', 'medzuro' ); ?></button>
				<button type="button" class="button alt mz-next"><?php esc_html_e( 'Proceed', 'medzuro' ); ?></button>
			</div>
		</section>

		<?php /* 6. Payment (amount/notice boxes come from medzuro_payment_summary()) */ ?>
		<section class="mz-panel" data-panel="payment" data-stage="payment" id="mz-panel-payment">
			<h2 class="mz-panel__ttl mz-hide-reserve"><?php esc_html_e( 'Payment method', 'medzuro' ); ?></h2>
			<p class="mz-panel__lead mz-only-delivery"><?php esc_html_e( 'Full payment only', 'medzuro' ); ?></p>

			<?php woocommerce_checkout_payment(); ?>

			<div class="mz-panel__actions mz-panel__actions--start">
				<button type="button" class="button mz-back"><?php esc_html_e( 'Back', 'medzuro' ); ?></button>
			</div>
		</section>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
