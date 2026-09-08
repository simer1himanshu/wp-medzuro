<?php
/**
 * Template Name: Medzuro — About
 *
 * Renders template-parts/about-page.php, ported from
 * sections/medzuro-about-page.liquid. Assign this template to the "About" page
 * in Pages → Edit → Page Attributes.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'about-page' );

get_header();
get_template_part( 'template-parts/about-page' );
get_footer();
