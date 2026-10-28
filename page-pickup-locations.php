<?php
/**
 * Template Name: Medzuro — Pickup Locations
 *
 * Used automatically for the page with the slug "pickup-locations". Shows the
 * page content (if any) followed by the live list of pickup locations.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

medzuro_style( 'pickup-locations' );

get_header();
?>
<div class="page-width mz-page mz-pl-page">
	<?php
	while ( have_posts() ) :
		the_post();
		$mz_content = get_the_content();
		?>
		<h1 class="mz-page__ttl"><?php the_title(); ?></h1>
		<?php if ( trim( wp_strip_all_tags( $mz_content ) ) && ! has_shortcode( $mz_content, 'medzuro_pickup_locations' ) ) : ?>
			<div class="mz-page__body rte"><?php the_content(); ?></div>
		<?php endif; ?>
		<?php
		if ( has_shortcode( $mz_content, 'medzuro_pickup_locations' ) ) {
			the_content();
		} else {
			echo medzuro_pickup_locations_shortcode(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
		}
	endwhile;
	?>
</div>
<?php
get_footer();
