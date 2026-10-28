<?php
/**
 * Pickup Locations page: [medzuro_pickup_locations] shortcode.
 *
 * Lists every enabled location from WooCommerce > Settings > Shipping >
 * Local pickup (via medzuro_pickup_stores()), so adding, editing or turning
 * off a location there updates the page with no code change.
 *
 * The page template page-pickup-locations.php is used automatically for a
 * page with the slug "pickup-locations". The shortcode also works on any
 * other page or in a block.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the locations grid.
 *
 * @param array $atts Shortcode attributes: intro="yes|no".
 * @return string
 */
function medzuro_pickup_locations_shortcode( $atts = array() ) {
	$atts   = shortcode_atts( array( 'intro' => 'yes' ), $atts, 'medzuro_pickup_locations' );
	$stores = function_exists( 'medzuro_pickup_stores' ) ? medzuro_pickup_stores() : array();

	medzuro_style( 'pickup-locations' );

	ob_start();
	?>
	<div class="mz-pl">
		<?php if ( 'yes' === $atts['intro'] ) : ?>
			<div class="mz-pl__intro">
				<p><?php esc_html_e( 'Order online and collect from a location near you. Pickup is free and there is nothing to pay in advance - pay when you collect. We will contact you when your order is ready.', 'medzuro' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! $stores ) : ?>
			<p><?php esc_html_e( 'Pickup locations will be listed here soon.', 'medzuro' ); ?></p>
		<?php else : ?>
			<ul class="mz-pl__grid">
				<?php foreach ( $stores as $store ) : ?>
					<li class="mz-pl__card">
						<?php if ( ! empty( $store['image'] ) ) : ?>
							<img class="mz-pl__img" src="<?php echo esc_url( $store['image'] ); ?>" alt="<?php echo esc_attr( $store['name'] ); ?>" loading="lazy" />
						<?php endif; ?>
						<div class="mz-pl__body">
							<h3 class="mz-pl__name"><?php echo esc_html( $store['name'] ); ?></h3>
							<?php if ( ! empty( $store['street'] ) ) : ?>
								<p class="mz-pl__row"><?php medzuro_icon( 'pin', 18 ); ?><span><?php echo esc_html( $store['street'] ); ?></span></p>
							<?php endif; ?>
							<?php if ( ! empty( $store['hours'] ) ) : ?>
								<p class="mz-pl__row"><?php medzuro_icon( 'clock', 18 ); ?><span><?php echo esc_html( $store['hours'] ); ?></span></p>
							<?php endif; ?>
							<?php if ( ! empty( $store['map'] ) ) : ?>
								<a class="mz-pl__map button" href="<?php echo esc_url( $store['map'] ); ?>" target="_blank" rel="noopener"><?php medzuro_icon( 'map', 18 ); ?><?php esc_html_e( 'Get directions', 'medzuro' ); ?></a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="mz-pl__how">
			<h2><?php esc_html_e( 'How pickup works', 'medzuro' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Add products to your cart and choose Store Pickup at checkout.', 'medzuro' ); ?></li>
				<li><?php esc_html_e( 'Select the location you want to collect from.', 'medzuro' ); ?></li>
				<li><?php esc_html_e( 'Place your order - there is nothing to pay online.', 'medzuro' ); ?></li>
				<li><?php esc_html_e( 'We contact you when your order is ready. Bring your order number and pay at the store when you collect.', 'medzuro' ); ?></li>
			</ol>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="button alt" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Start shopping', 'medzuro' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'medzuro_pickup_locations', 'medzuro_pickup_locations_shortcode' );
