<?php
/**
 * Default page template, for pages with no Medzuro template assigned.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="page-width mz-page">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<h1 class="mz-page__ttl"><?php the_title(); ?></h1>
		<div class="mz-page__body rte"><?php the_content(); ?></div>
		<?php
	endwhile;
	?>
</div>

<?php
get_footer();
