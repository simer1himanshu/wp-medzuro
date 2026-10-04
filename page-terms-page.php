<?php
/**
 * Template Name: Medzuro — Terms & Conditions
 *
 * Renders the Terms & Conditions text from inc/terms-content.php. Assign this
 * template to the "Terms & Conditions" page in Pages → Edit → Page Attributes.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'legal-page' );

get_header();
echo '<div class="page-width mz-legal-page">';
get_template_part( 'template-parts/terms-page' );
echo '</div>';
get_footer();
