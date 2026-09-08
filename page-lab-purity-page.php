<?php
/**
 * Template Name: Medzuro — Lab Testing & Purity
 *
 * Renders template-parts/lab-purity-page.php, ported from
 * sections/medzuro-lab-purity-page.liquid. Assign this template to the "Lab Testing & Purity" page
 * in Pages → Edit → Page Attributes.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'lab-purity-page' );

get_header();
get_template_part( 'template-parts/lab-purity-page' );
get_footer();
