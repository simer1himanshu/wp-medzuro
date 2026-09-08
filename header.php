<?php
/**
 * Site header.
 *
 * Ported from snippets/header.liquid. That file branched on ~15 theme settings;
 * this renders only the configuration the store actually used, read out of
 * config/settings_data.json:
 *
 *   topbar = true            announcement = false      snow_effect = false
 *   align_logo = left        nav_below_logo = false    home_classic = false
 *   enable_search = true     enable_wishlist = true    ajax_cart = true
 *   show_sticky_header = true                          catalogmode = false
 *   layout_style = normal    rtl = false               enable_age_varification = false
 *
 * Dead branches are not carried over. If a setting needs to come back, it
 * belongs in inc/content.php, not as a template conditional.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#002964">
	<meta name="format-detection" content="telephone=no">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link mz-visually-hidden" href="#MainContent"><?php esc_html_e( 'Skip to content', 'medzuro' ); ?></a>

<?php $topbar = medzuro_content()['topbar']; ?>
<?php if ( array_filter( $topbar ) ) : ?>
	<div class="mz-topbar">
		<div class="page-width mz-topbar__inner">
			<div class="mz-topbar__cell mz-topbar__cell--left"><?php echo wp_kses_post( $topbar['left'] ); ?></div>
			<div class="mz-topbar__cell mz-topbar__cell--center"><?php echo wp_kses_post( $topbar['center'] ); ?></div>
			<div class="mz-topbar__cell mz-topbar__cell--right"><?php echo wp_kses_post( $topbar['right'] ); ?></div>
		</div>
	</div>
<?php endif; ?>

<div id="header" class="mz-hd is-sticky">
	<header class="mz-hd__bar page-width" role="banner">

		<div class="mz-hd__mobile">
			<button type="button" class="mz-hd__icon js-mobile-nav-toggle"
			        aria-controls="MobileNav" aria-expanded="false"
			        aria-label="<?php esc_attr_e( 'Menu', 'medzuro' ); ?>">
				<span class="mz-hd__bars" aria-hidden="true"></span>
			</button>
		</div>

		<div class="mz-hd__logo">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mz-hd__logo-link"
			   aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-logo.png' ); ?>"
					     width="180" height="58" fetchpriority="high"
					     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php endif; ?>
			</a>
		</div>

		<nav class="mz-hd__nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary', 'medzuro' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'mz-nav',
					'depth'          => 3,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>

		<div class="mz-hd__icons">
			<button type="button" class="mz-hd__icon js-search-toggle"
			        aria-controls="SearchDrawer" aria-expanded="false"
			        aria-label="<?php esc_attr_e( 'Search', 'medzuro' ); ?>">
				<?php medzuro_icon( 'search' ); ?>
			</button>

			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="mz-hd__icon"
			   aria-label="<?php esc_attr_e( 'Account', 'medzuro' ); ?>">
				<?php medzuro_icon( 'user' ); ?>
			</a>

			<?php
			// enable_wishlist was true in Shopify. WooCommerce has no native
			// wishlist — this renders only once a provider (e.g. YITH) is active.
			if ( defined( 'YITH_WCWL' ) ) :
				?>
				<a href="<?php echo esc_url( get_permalink( get_option( 'yith_wcwl_wishlist_page_id' ) ) ); ?>"
				   class="mz-hd__icon mz-hd__icon--wishlist"
				   aria-label="<?php esc_attr_e( 'Wishlist', 'medzuro' ); ?>">
					<?php medzuro_icon( 'heart' ); ?>
				</a>
			<?php endif; ?>

			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" id="cartLink" class="mz-hd__icon mz-hd__cart"
			   aria-label="<?php esc_attr_e( 'Cart', 'medzuro' ); ?>">
				<?php medzuro_icon( 'bag' ); ?>
				<?php medzuro_cart_count(); ?>
			</a>
		</div>

	</header>
</div>
<div class="mz-hd__spacer"></div>

<main id="MainContent" class="mz-main" role="main">
