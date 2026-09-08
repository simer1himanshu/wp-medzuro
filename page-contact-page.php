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
get_template_part( 'template-parts/contact-page' );
get_footer();
