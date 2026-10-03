<?php
/**
 * Proceed to checkout button.
 *
 * Overrides woocommerce/templates/cart/proceed-to-checkout-button.php. The
 * customer already chose delivery or pickup on the cart, so checkout opens
 * at the Details step (#details).
 *
 * @package Medzuro
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( wc_get_checkout_url() . '#details' ); ?>" class="checkout-button button alt wc-forward<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
	<?php esc_html_e( 'Proceed to checkout', 'medzuro' ); ?>
</a>
