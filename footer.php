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
					<img src="<?php echo esc_url( medzuro_logo_url() ); ?>"
					     width="180" height="58" loading="lazy"
					     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>

				<?php if ( $f['tagline'] ) : ?>
					<p class="mz-foot__tagline"><?php echo esc_html( $f['tagline'] ); ?></p>
				<?php endif; ?>
				<?php if ( $f['blurb'] ) : ?>
					<p class="mz-foot__blurb"><?php echo wp_kses_post( $f['blurb'] ); ?></p>
				<?php endif; ?>

				<ul class="mz-foot__trust" role="list">
					<li><span><?php medzuro_icon( 'shield', 22 ); ?></span><?php esc_html_e( 'Genuine Products', 'medzuro' ); ?></li>
					<li><span><?php medzuro_icon( 'truck', 22 ); ?></span><?php esc_html_e( 'Fiji-wide Delivery', 'medzuro' ); ?></li>
					<li><span><?php medzuro_icon( 'headset', 22 ); ?></span><?php esc_html_e( 'Local Support', 'medzuro' ); ?></li>
				</ul>
			</div>

			<div class="mz-foot__cols">
				<?php
				$defaults = medzuro_footer_default_columns();

				for ( $i = 1; $i <= 3; $i++ ) :
					$location = 'footer_' . $i;
					?>
					<div class="mz-foot__col">
						<?php if ( has_nav_menu( $location ) ) : ?>
							<?php $title = wp_get_nav_menu_object( get_nav_menu_locations()[ $location ] ?? 0 ); ?>
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
						<?php else : ?>
							<h2 class="mz-foot__ttl"><?php echo esc_html( $defaults[ $i ]['title'] ); ?></h2>
							<ul class="mz-foot__links">
								<?php foreach ( $defaults[ $i ]['links'] as $link ) : ?>
									<li><a href="<?php echo esc_url( $link[1] ); ?>"><?php echo esc_html( $link[0] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endfor; ?>

				<div class="mz-foot__col mz-foot__connect">
					<h2 class="mz-foot__ttl"><?php echo esc_html( $f['connect_title'] ); ?></h2>
					<p class="mz-foot__connect-text"><?php echo esc_html( $f['connect_text'] ); ?></p>

					<?php
					if ( has_action( 'medzuro_footer_newsletter' ) ) {
						do_action( 'medzuro_footer_newsletter' );
					} else {
						?>
						<form class="mz-foot__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="medzuro_newsletter_subscribe">
							<?php wp_nonce_field( 'medzuro_newsletter_subscribe', 'medzuro_newsletter_nonce' ); ?>
							<label class="mz-foot__trap" aria-hidden="true">Company<input type="text" name="company" tabindex="-1" autocomplete="off"></label>
							<label class="screen-reader-text" for="mz-foot-email"><?php esc_html_e( 'Email address', 'medzuro' ); ?></label>
							<input id="mz-foot-email" type="email" name="email" autocomplete="email" required
							       placeholder="<?php echo esc_attr( $f['newsletter_placeholder'] ); ?>">
							<button type="submit" aria-label="<?php esc_attr_e( 'Subscribe', 'medzuro' ); ?>">
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 12h15"/><path d="m13 6 6 6-6 6"/></svg>
							</button>
						</form>
						<?php
					}
					?>

					<?php if ( $f['show_social'] ) : ?>
						<div class="mz-foot__social">
							<?php if ( $f['facebook_url'] ) : ?>
								<a href="<?php echo esc_url( $f['facebook_url'] ); ?>" target="_blank" rel="noopener" aria-label="Facebook">
									<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 0 0-1.6 19.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.600.8-1.600 1.600V12h2.800l-.4 2.900h-2.300v7A10 10 0 0 0 12 2Z"/></svg>
								</a>
							<?php endif; ?>
							<?php if ( $f['instagram_url'] ) : ?>
								<a href="<?php echo esc_url( $f['instagram_url'] ); ?>" target="_blank" rel="noopener" aria-label="Instagram">
									<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>
								</a>
							<?php endif; ?>
							<?php do_action( 'medzuro_social_links' ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="mz-foot__bottom">
			<div class="mz-foot__legal">
				<p class="mz-foot__copy">
					<?php echo wp_kses_post( str_replace( '[year]', gmdate( 'Y' ), $f['copyright'] ) ); ?>
				</p>
				<?php if ( $f['serving_note'] ) : ?>
					<p class="mz-foot__serving">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/footer-fiji-flag.svg' ); ?>" width="24" height="16" alt="" loading="lazy">
						<?php echo esc_html( $f['serving_note'] ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $f['show_policies'] ) : ?>
				<ul class="mz-foot__policies" role="list">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'legal',
							'container'      => false,
							'items_wrap'     => '%3$s',
							'depth'          => 1,
							'fallback_cb'    => 'medzuro_footer_legal_fallback',
						)
					);
					?>
				</ul>
			<?php endif; ?>

			<?php if ( $f['show_payment'] ) : ?>
				<ul class="mz-foot__badges" role="list">
					<li class="mz-foot__secure"><?php medzuro_icon( 'lock', 26 ); ?><span><?php esc_html_e( 'Secure', 'medzuro' ); ?><br><?php esc_html_e( 'Checkout', 'medzuro' ); ?></span></li>
					<li><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/footer-mpaisa.svg' ); ?>" width="112" height="30" alt="M-Paisa" loading="lazy"></li>
					<li><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/footer-dhl.svg' ); ?>" width="104" height="24" alt="DHL Express" loading="lazy"></li>
				</ul>
			<?php endif; ?>
		</div>

	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
