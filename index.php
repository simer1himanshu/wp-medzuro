<?php
/**
 * Fallback template, required for a valid WordPress theme.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="page-width mz-blog">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<article <?php post_class( 'mz-blog__item' ); ?>>
				<h2 class="mz-blog__ttl">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="mz-blog__excerpt"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>

		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'medzuro' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
