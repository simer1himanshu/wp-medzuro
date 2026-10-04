<?php
/**
 * Terms & Conditions page body.
 *
 * Renders the blocks from medzuro_terms_text(): the first three are the title,
 * company and dates; "N. HEADING" blocks start numbered sections; the closing
 * FINAL DISCLAIMER is shown as a callout.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$blocks = preg_split( "/\n{2,}/", trim( medzuro_terms_text() ) );
$title  = array_shift( $blocks );
$brand  = array_shift( $blocks );
$dates  = array_shift( $blocks );

/**
 * Escape a block, keep line breaks and link the email address.
 *
 * @param string $text Raw block.
 * @return string
 */
$mz_fmt = function ( $text ) {
	$html = nl2br( esc_html( $text ) );

	return preg_replace( '/([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})/', '<a href="mailto:$1">$1</a>', $html );
};

$in_disclaimer = false;
$open          = false;
?>
<article class="mz-legal">
	<header class="mz-legal__head">
		<p class="mz-legal__eyebrow"><?php echo esc_html( $brand ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<p class="mz-legal__dates"><?php echo wp_kses( nl2br( esc_html( $dates ) ), array( 'br' => array() ) ); ?></p>
	</header>

	<div class="mz-legal__body">
		<?php foreach ( $blocks as $block ) : ?>
			<?php if ( preg_match( '/^(\d+)\.\s+(.+)$/', $block, $m ) ) : ?>
				<?php if ( $open ) : ?></section><?php endif; ?>
				<section class="mz-legal__section">
					<h2><span><?php echo esc_html( $m[1] ); ?></span><?php echo esc_html( $m[2] ); ?></h2>
				<?php $open = true; ?>
			<?php elseif ( 'FINAL DISCLAIMER' === $block ) : ?>
				<?php if ( $open ) : ?></section><?php endif; ?>
				<?php $open = false; $in_disclaimer = true; ?>
				<aside class="mz-legal__disclaimer">
					<h2><?php esc_html_e( 'Final Disclaimer', 'medzuro' ); ?></h2>
			<?php elseif ( $in_disclaimer ) : ?>
					<p><?php echo wp_kses( $mz_fmt( $block ), array( 'br' => array(), 'a' => array( 'href' => array() ) ) ); ?></p>
				</aside>
				<?php $in_disclaimer = false; ?>
			<?php else : ?>
				<?php $shout = $block === strtoupper( $block ) && strlen( $block ) > 40; ?>
				<p<?php echo $shout ? ' class="mz-legal__shout"' : ''; ?>><?php echo wp_kses( $mz_fmt( $block ), array( 'br' => array(), 'a' => array( 'href' => array() ) ) ); ?></p>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $open ) : ?></section><?php endif; ?>
	</div>
</article>
