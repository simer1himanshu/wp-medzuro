<?php
/**
 * Customer reviews.
 *
 * Renders WooCommerce's own review system — genuine reviews and the
 * submission form — rather than any invented testimonial copy. When a
 * product has reviews turned off and none exist yet, comments_template()
 * (via WooCommerce's own comments_template filter) prints nothing, so the
 * section is skipped entirely rather than showing an empty heading.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! comments_open( $product->get_id() ) ) {
	return;
}
?>

<div class="mz-pdp-section mz-pdp-reviews">
	<div class="mz-pdp-shell">
		<p class="mz-pdp-kicker"><?php esc_html_e( 'Customer Reviews', 'medzuro' ); ?></p>
		<?php comments_template(); ?>
	</div>
</div>
