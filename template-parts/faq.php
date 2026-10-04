<?php
/**
 * FAQ accordion with a contact call-to-action.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$items   = medzuro_faq_items();
$contact = apply_filters( 'medzuro_faq_contact_url', home_url( '/contact/' ) );
?>
<section class="mz-faq" aria-labelledby="mz-faq-title">
	<header class="mz-faq__head">
		<p class="mz-faq__eyebrow"><?php esc_html_e( 'Frequently asked questions', 'medzuro' ); ?></p>
		<h2 id="mz-faq-title"><?php esc_html_e( 'Everything You Need to Know', 'medzuro' ); ?></h2>
		<p class="mz-faq__sub"><?php esc_html_e( 'Simple answers about our products, delivery, pickup and support.', 'medzuro' ); ?></p>
	</header>

	<div class="mz-faq__list">
		<?php foreach ( $items as $i => $item ) : ?>
			<details class="mz-faq__item" name="mz-faq"<?php echo 0 === $i ? ' open' : ''; ?>>
				<summary>
					<span class="mz-faq__icon"><?php medzuro_ref_icon( $item['icon'] ); ?></span>
					<span class="mz-faq__q"><?php echo esc_html( $item['q'] ); ?></span>
					<span class="mz-faq__toggle" aria-hidden="true"></span>
				</summary>
				<p><?php echo esc_html( $item['a'] ); ?></p>
			</details>
		<?php endforeach; ?>
	</div>

	<div class="mz-faq__cta">
		<span class="mz-faq__cta-icon"><?php medzuro_ref_icon( 'chat' ); ?></span>
		<div class="mz-faq__cta-text">
			<strong><?php esc_html_e( 'Still have questions?', 'medzuro' ); ?></strong>
			<span><?php esc_html_e( 'Our Fiji support team is happy to help.', 'medzuro' ); ?></span>
		</div>
		<a class="mz-faq__cta-btn" href="<?php echo esc_url( $contact ); ?>"><?php esc_html_e( 'Contact us', 'medzuro' ); ?> <span aria-hidden="true">&rarr;</span></a>
	</div>
</section>
