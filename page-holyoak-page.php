<?php
/**
 * Template Name: Medzuro — HolyOak
 *
 * Renders template-parts/holyoak-page.php, ported from
 * sections/medzuro-holyoak-page.liquid. Assign this template to the "HolyOak" page
 * in Pages → Edit → Page Attributes.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'holyoak-page' );

get_header();
get_template_part( 'template-parts/holyoak-page' );
get_footer();
