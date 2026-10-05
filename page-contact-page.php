<?php
/**
 * Template Name: Medzuro — Contact
 *
 * Renders template-parts/contact-page.php, ported from
 * sections/medzuro-contact-page.liquid. Assign this template to the "Contact" page
 * in Pages → Edit → Page Attributes.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'contact-page' );

get_header();

// Never let a template error blank the whole page: log it, leave a hint for
// debugging, and show a plain set of contact details instead.
ob_start();
try {
	get_template_part( 'template-parts/contact-page' );
	ob_end_flush();
} catch ( \Throwable $e ) {
	ob_end_clean();
	error_log( 'Medzuro contact page: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	echo '<!-- mz-contact-error: ' . esc_html( get_class( $e ) . ': ' . $e->getMessage() . ' @ ' . basename( $e->getFile() ) . ':' . $e->getLine() ) . ' -->';
	echo '<div class="page-width" style="padding:64px 0;max-width:720px"><h1>Contact us</h1>'
		. '<p>Call <a href="tel:+6798029837">+679 8029837</a> or email <a href="mailto:sales@medzuroretail.com">sales@medzuroretail.com</a>.</p>'
		. '<p>Medzuro Wellness PTE Limited, 18, Valili St, Vishnu Deo Road, Nakasi, Suva, Fiji Islands.</p></div>';
}

get_footer();
