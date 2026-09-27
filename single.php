<?php
/**
 * Single blog article.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

get_header();
$posts_page_id  = (int) get_option( 'page_for_posts' );
$posts_page_url = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/' );
?>

<main class="mz-article">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article <?php post_class(); ?>>
			<header class="page-width mz-article__header">
				<a class="mz-article__back" href="<?php echo esc_url( $posts_page_url ); ?>">
					<span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Back to articles', 'medzuro' ); ?>
				</a>
				<p class="mz-article__eyebrow"><?php esc_html_e( 'Medzuro Journal', 'medzuro' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<div class="mz-article__meta">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<?php if ( get_the_author() ) : ?>
						<span aria-hidden="true">&bull;</span><span><?php echo esc_html( get_the_author() ); ?></span>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="page-width mz-article__featured">
					<?php the_post_thumbnail( 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="page-width mz-article__content">
				<?php the_content(); ?>
				<?php
				wp_link_pages(
					array(
						'before' => '<nav class="mz-article__pages">' . esc_html__( 'Pages:', 'medzuro' ),
						'after'  => '</nav>',
					)
				);
				?>
			</div>
		</article>
	<?php endwhile; ?>
</main>

<?php
get_footer();
