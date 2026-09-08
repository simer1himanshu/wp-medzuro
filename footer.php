<?php
/**
 * Site footer.
 *
 * Ported from sections/medzuro-footer.liquid. The Shopify version was
 * block-driven (menu / links / text / newsletter blocks added in the theme
 * editor). WooCommerce has no block editor for this, so the three link columns
 * became the footer_1..footer_3 nav menu locations registered in functions.php,
 * and the settings became inc/content.php entries.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$f = medzuro_content()['footer'];
?>
</main><!-- #MainContent -->

<footer class="mz-foot" role="contentinfo">
	<div class="page-width">

		<div class="mz-foot__top">
			<div class="mz-foot__brand">
				<a class="mz-foot__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"
				   aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-logo.png' ); ?>"
					     width="180" height="58" loading="lazy"
					     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>

				<?php if ( $f['blurb'] ) : ?>
					<p class="mz-foot__blurb"><?php echo wp_kses_post( $f['blurb'] ); ?></p>
				<?php endif; ?>

				<?php if ( $f['show_seller_badge'] && $f['badge_text'] ) : ?>
					<div class="mz-foot__badge">
						<?php medzuro_icon( 'shield', 24 ); ?>
						<span><?php echo esc_html( $f['badge_text'] ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $f['address'] || $f['phone'] || $f['email'] ) : ?>
					<ul class="mz-foot__contact" role="list">
						<?php if ( $f['address'] ) : ?>
							<li><?php echo wp_kses_post( $f['address'] ); ?></li>
						<?php endif; ?>
						<?php if ( $f['phone'] ) : ?>
							<li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $f['phone'] ) ); ?>"><?php echo esc_html( $f['phone'] ); ?></a></li>
						<?php endif; ?>
						<?php if ( $f['email'] ) : ?>
							<li><a href="mailto:<?php echo esc_attr( $f['email'] ); ?>"><?php echo esc_html( $f['email'] ); ?></a></li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="mz-foot__cols">
				<?php
				// Mirrors the Liquid `when 'menu'` block: a column with no menu
				// assigned renders nothing, so the footer degrades cleanly
				// before the menus are created in wp-admin.
				for ( $i = 1; $i <= (int) $f['columns']; $i++ ) :
					$location = 'footer_' . $i;

					if ( ! has_nav_menu( $location ) ) {
						continue;
					}

					$title = wp_get_nav_menu_object( get_nav_menu_locations()[ $location ] ?? 0 );
					?>
					<div class="mz-foot__col">
						<?php if ( $title ) : ?>
							<h2 class="mz-foot__ttl"><?php echo esc_html( $title->name ); ?></h2>
						<?php endif; ?>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => $location,
								'container'      => false,
								'menu_class'     => 'mz-foot__links',
								'depth'          => 1,
								'fallback_cb'    => false,
							)
						);
						?>
					</div>
				<?php endfor; ?>

				<?php
				/**
				 * Newsletter slot — the Liquid `when 'newsletter'` block rendered
				 * snippets/newsletter-form.liquid, which posted to Shopify's
				 * customer form. Hook a mail plugin's shortcode on here.
				 */
				do_action( 'medzuro_footer_newsletter' );
				?>
			</div>
		</div>

		<div class="mz-foot__bottom">
			<p class="mz-foot__copy">
				<?php echo wp_kses_post( str_replace( '[year]', gmdate( 'Y' ), $f['copyright'] ) ); ?>
			</p>

			<?php if ( $f['show_policies'] ) : ?>
				<ul class="mz-foot__policies" role="list">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'legal',
							'container'      => false,
							'items_wrap'     => '%3$s',
							'depth'          => 1,
							'fallback_cb'    => false,
						)
					);
					?>
				</ul>
			<?php endif; ?>

			<div class="mz-foot__end">
				<?php if ( $f['show_social'] ) : ?>
					<div class="mz-foot__social"><?php do_action( 'medzuro_social_links' ); ?></div>
				<?php endif; ?>

				<?php
				/**
				 * Payment icons.
				 *
				 * Shopify supplied these via shop.enabled_payment_types +
				 * payment_type_svg_tag. WooCommerce has no equivalent, so the
				 * list is explicit — edit to match the gateways actually enabled.
				 */
				$pay = apply_filters( 'medzuro_payment_icons', array( 'visa', 'mastercard', 'amex', 'paypal' ) );

				// Only render icons whose SVG has actually been added to
				// assets/img/, so the row degrades cleanly rather than showing
				// broken images before the artwork is in place.
				$pay = array_values(
					array_filter(
						$pay,
						fn( $t ) => file_exists( get_template_directory() . '/assets/img/pay-' . $t . '.svg' )
					)
				);

				if ( $f['show_payment'] && $pay ) :
					?>
					<ul class="mz-foot__pay" role="list">
						<?php foreach ( $pay as $type ) : ?>
							<li>
								<img class="mz-foot__payicon" loading="lazy" width="38" height="24"
								     src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/pay-' . $type . '.svg' ); ?>"
								     alt="<?php echo esc_attr( ucfirst( $type ) ); ?>">
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
