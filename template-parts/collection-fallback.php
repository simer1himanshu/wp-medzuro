<?php
/**
 * Empty product archive state.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$contact = get_permalink( get_page_by_path( 'contact' ) );
?>
<div class="mz-collection-empty">
	<div>
		<p class="mz-collection-eyebrow"><?php esc_html_e( 'Catalogue update', 'medzuro' ); ?></p>
		<h2><?php esc_html_e( 'New wellness products are on the way.', 'medzuro' ); ?></h2>
		<p><?php esc_html_e( 'We are preparing this collection for Fiji. Contact our team for availability, local pickup, or help choosing a product.', 'medzuro' ); ?></p>
		<div class="mz-collection-empty__actions">
			<a class="mz-collection-btn mz-collection-btn--primary" href="<?php echo esc_url( $contact ); ?>"><?php esc_html_e( 'Contact our team', 'medzuro' ); ?></a>
		</div>
	</div>
	<div class="mz-collection-empty__panel" aria-hidden="true">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/medzuro-home-hero.png' ); ?>" alt="" width="880" height="620" loading="lazy">
	</div>
</div>
