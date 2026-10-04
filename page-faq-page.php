<?php
/**
 * Template Name: Medzuro — FAQ
 *
 * Assign this template to the "FAQs" page in Pages → Edit → Page Attributes.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'faq' );

get_header();
echo '<div class="page-width mz-faq-page">';
get_template_part( 'template-parts/faq' );
echo '</div>';
get_footer();
