<?php
/**
 * Page shell for the cart and checkout.
 *
 * Renders WooCommerce's classic cart/checkout shortcodes regardless of the
 * blocks saved in the page, so the theme's multi-step flow (see
 * inc/checkout-flow.php) is what customers see.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mz_is_receipt = is_wc_endpoint_url( 'order-received' );
$mz_class      = is_cart() ? 'mz-cart-page' : ( $mz_is_receipt ? 'mz-receipt-page' : 'mz-checkout-page' );
?>

<div class="page-width mz-page mz-commerce <?php echo esc_attr( $mz_class ); ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		echo do_shortcode( medzuro_cart_checkout_shortcode() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	endwhile;
	?>
</div>

<?php
get_footer();
