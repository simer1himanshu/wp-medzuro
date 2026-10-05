<?php
/**
 * Contact page.
 *
 * Sections: hero with quick-contact card, enquiry form beside the office
 * details, ways to reach us, and a closing support strip. CSS lives in
 * assets/css/contact-page.css and is scoped to .mz-contact.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

$c       = medzuro_contact_details();
$fiji    = $c['fiji'];
$india   = $c['india'];
$tel     = function ( $number ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $number );
};
$status  = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$notices = array(
	'sent'    => array( 'ok', __( 'Thank you. Your message has been sent and our team will get back to you soon.', 'medzuro' ) ),
	'invalid' => array( 'err', __( 'Please fill in your name, a valid email, a subject and your message.', 'medzuro' ) ),
	'wait'    => array( 'err', __( 'Please wait a moment before sending another message.', 'medzuro' ) ),
	'error'   => array( 'err', __( 'Sorry, we could not send your message. Please call or email us instead.', 'medzuro' ) ),
);
?>
<div class="mz-contact">

	<section class="mzc-hero">
		<div class="mzc-wrap mzc-hero__grid">
			<div class="mzc-hero__copy">
				<p class="mzc-eyebrow"><?php esc_html_e( 'Contact Medzuro Retail', 'medzuro' ); ?></p>
				<h1><?php esc_html_e( "We're here to help", 'medzuro' ); ?></h1>
				<p class="mzc-hero__lead"><?php esc_html_e( 'Questions about a product, an order, delivery or pickup? Our Fiji team is ready to assist.', 'medzuro' ); ?></p>
				<p class="mzc-hero__sub"><?php esc_html_e( 'With teams in Fiji and India, Medzuro Retail pairs local customer support with an international healthcare and wellness network.', 'medzuro' ); ?></p>
				<div class="mzc-hero__actions">
					<a class="mzc-btn" href="#mz-contact-form"><?php esc_html_e( 'Send a message', 'medzuro' ); ?></a>
					<a class="mzc-btn mzc-btn--ghost" href="<?php echo esc_url( $tel( $fiji['phone'] ) ); ?>"><?php medzuro_ref_icon( 'phone' ); ?><?php echo esc_html( $fiji['phone'] ); ?></a>
				</div>
			</div>

			<aside class="mzc-quick" aria-label="<?php esc_attr_e( 'Quick contact', 'medzuro' ); ?>">
				<h2><?php esc_html_e( 'Talk to us now', 'medzuro' ); ?></h2>
				<ul>
					<li>
						<a href="<?php echo esc_url( $tel( $fiji['phone'] ) ); ?>">
							<span class="mzc-ico"><?php medzuro_ref_icon( 'phone' ); ?></span>
							<span><strong><?php esc_html_e( 'Call us', 'medzuro' ); ?></strong><small><?php echo esc_html( $fiji['phone'] ); ?></small></span>
						</a>
					</li>
					<li>
						<a href="viber://chat?number=<?php echo esc_attr( rawurlencode( $fiji['viber'] ) ); ?>">
							<span class="mzc-ico mzc-ico--viber"><?php medzuro_ref_icon( 'viber' ); ?></span>
							<span><strong><?php esc_html_e( 'Message on Viber', 'medzuro' ); ?></strong><small><?php echo esc_html( $fiji['phone'] ); ?></small></span>
						</a>
					</li>
					<li>
						<a href="mailto:<?php echo esc_attr( $fiji['emails'][0] ); ?>">
							<span class="mzc-ico"><?php medzuro_ref_icon( 'mail' ); ?></span>
							<span><strong><?php esc_html_e( 'Email us', 'medzuro' ); ?></strong><small><?php echo esc_html( $fiji['emails'][0] ); ?></small></span>
						</a>
					</li>
					<li>
						<a href="#mz-offices">
							<span class="mzc-ico mzc-ico--gold"><?php medzuro_ref_icon( 'pin' ); ?></span>
							<span><strong><?php esc_html_e( 'Visit us', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Nakasi, Suva, Fiji', 'medzuro' ); ?></small></span>
						</a>
					</li>
				</ul>
			</aside>
		</div>
	</section>

	<section class="mzc-trust" aria-label="<?php esc_attr_e( 'Why contact us', 'medzuro' ); ?>">
		<div class="mzc-wrap">
			<ul>
				<li><span class="mzc-ico"><?php medzuro_ref_icon( 'clock' ); ?></span><span><strong><?php esc_html_e( 'Fast response', 'medzuro' ); ?></strong><small><?php esc_html_e( 'We reply quickly', 'medzuro' ); ?></small></span></li>
				<li><span class="mzc-ico"><?php medzuro_ref_icon( 'shield' ); ?></span><span><strong><?php esc_html_e( 'Trusted support', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Reliable and secure', 'medzuro' ); ?></small></span></li>
				<li><span class="mzc-ico"><?php medzuro_ref_icon( 'users' ); ?></span><span><strong><?php esc_html_e( 'Real people', 'medzuro' ); ?></strong><small><?php esc_html_e( "We're here for you", 'medzuro' ); ?></small></span></li>
				<li><span class="mzc-ico"><?php medzuro_ref_icon( 'globe' ); ?></span><span><strong><?php esc_html_e( 'Global network', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Local presence, global reach', 'medzuro' ); ?></small></span></li>
			</ul>
		</div>
	</section>

	<section class="mzc-main">
		<div class="mzc-wrap mzc-main__grid">

			<div class="mzc-card mzc-form" id="mz-contact-form">
				<header>
					<p class="mzc-eyebrow"><?php esc_html_e( 'Get in touch', 'medzuro' ); ?></p>
					<h2><?php esc_html_e( 'Send us a message', 'medzuro' ); ?></h2>
					<p><?php esc_html_e( 'Fill out the form and our team will get back to you.', 'medzuro' ); ?></p>
				</header>

				<?php if ( isset( $notices[ $status ] ) ) : ?>
					<p class="mzc-notice mzc-notice--<?php echo esc_attr( $notices[ $status ][0] ); ?>" role="status"><?php echo esc_html( $notices[ $status ][1] ); ?></p>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="medzuro_contact_submit">
					<?php wp_nonce_field( 'medzuro_contact_submit', 'medzuro_contact_nonce' ); ?>
					<label class="mzc-trap" aria-hidden="true">Company<input type="text" name="company" tabindex="-1" autocomplete="off"></label>

					<div class="mzc-fields">
						<div class="mzc-field">
							<label for="mzc-name"><?php esc_html_e( 'Full name', 'medzuro' ); ?> <abbr title="required">*</abbr></label>
							<input id="mzc-name" type="text" name="name" autocomplete="name" placeholder="<?php esc_attr_e( 'Your full name', 'medzuro' ); ?>" required>
						</div>
						<div class="mzc-field">
							<label for="mzc-email"><?php esc_html_e( 'Email address', 'medzuro' ); ?> <abbr title="required">*</abbr></label>
							<input id="mzc-email" type="email" name="email" autocomplete="email" placeholder="<?php esc_attr_e( 'you@example.com', 'medzuro' ); ?>" required>
						</div>
						<div class="mzc-field">
							<label for="mzc-phone"><?php esc_html_e( 'Phone number', 'medzuro' ); ?></label>
							<input id="mzc-phone" type="tel" name="phone" autocomplete="tel" pattern="[0-9+() -]*" placeholder="+679 1234567">
						</div>
						<div class="mzc-field">
							<label for="mzc-country"><?php esc_html_e( 'Country', 'medzuro' ); ?></label>
							<select id="mzc-country" name="country">
								<option value="Fiji"><?php esc_html_e( 'Fiji', 'medzuro' ); ?></option>
								<option value="India"><?php esc_html_e( 'India', 'medzuro' ); ?></option>
								<option value="Other"><?php esc_html_e( 'Other', 'medzuro' ); ?></option>
							</select>
						</div>
						<div class="mzc-field">
							<label for="mzc-type"><?php esc_html_e( 'Enquiry type', 'medzuro' ); ?></label>
							<select id="mzc-type" name="enquiry_type">
								<option><?php esc_html_e( 'Product question', 'medzuro' ); ?></option>
								<option><?php esc_html_e( 'Order support', 'medzuro' ); ?></option>
								<option><?php esc_html_e( 'Delivery or pickup', 'medzuro' ); ?></option>
								<option><?php esc_html_e( 'Partnership', 'medzuro' ); ?></option>
								<option><?php esc_html_e( 'General enquiry', 'medzuro' ); ?></option>
							</select>
						</div>
						<div class="mzc-field">
							<label for="mzc-subject"><?php esc_html_e( 'Subject', 'medzuro' ); ?> <abbr title="required">*</abbr></label>
							<input id="mzc-subject" type="text" name="subject" placeholder="<?php esc_attr_e( 'What is your enquiry about?', 'medzuro' ); ?>" required>
						</div>
						<div class="mzc-field mzc-field--full">
							<label for="mzc-message"><?php esc_html_e( 'Your message', 'medzuro' ); ?> <abbr title="required">*</abbr></label>
							<textarea id="mzc-message" name="message" rows="6" placeholder="<?php esc_attr_e( 'Tell us how we can help you...', 'medzuro' ); ?>" required></textarea>
						</div>
					</div>

					<div class="mzc-submit">
						<button class="mzc-btn" type="submit"><?php esc_html_e( 'Submit enquiry', 'medzuro' ); ?></button>
						<span class="mzc-safe"><?php medzuro_ref_icon( 'shield' ); ?><?php esc_html_e( 'Your information is safe with us. We never share your details.', 'medzuro' ); ?></span>
					</div>
				</form>
			</div>

			<div class="mzc-side" id="mz-offices">
				<article class="mzc-card mzc-office">
					<header>
						<span class="mzc-flag">FJ</span>
						<div><h3><?php esc_html_e( 'Fiji office', 'medzuro' ); ?></h3><p><?php echo esc_html( $fiji['entity'] ); ?></p></div>
					</header>
					<ul>
						<li><?php medzuro_ref_icon( 'pin' ); ?><span><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $fiji['lines'] ) ), array( 'br' => array() ) ); ?></span></li>
						<li><?php medzuro_ref_icon( 'phone' ); ?><a href="<?php echo esc_url( $tel( $fiji['phone'] ) ); ?>"><?php echo esc_html( $fiji['phone'] ); ?></a></li>
						<?php foreach ( $fiji['emails'] as $email ) : ?>
							<li><?php medzuro_ref_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</article>

				<article class="mzc-card mzc-office mzc-office--india">
					<header>
						<span class="mzc-flag mzc-flag--in">IN</span>
						<div><h3><?php esc_html_e( 'India office', 'medzuro' ); ?></h3><p><?php echo esc_html( $india['entity'] ); ?></p></div>
					</header>
					<ul>
						<li><?php medzuro_ref_icon( 'pin' ); ?><span><?php echo wp_kses( implode( '<br>', array_map( 'esc_html', $india['lines'] ) ), array( 'br' => array() ) ); ?></span></li>
						<li><?php medzuro_ref_icon( 'phone' ); ?><a href="<?php echo esc_url( $tel( $india['phone'] ) ); ?>"><?php echo esc_html( $india['phone'] ); ?></a></li>
						<?php foreach ( $india['emails'] as $email ) : ?>
							<li><?php medzuro_ref_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</article>

				<p class="mzc-faq"><?php esc_html_e( 'Looking for quick answers?', 'medzuro' ); ?> <a href="<?php echo esc_url( medzuro_page_url( array( 'faqs', 'faq' ), home_url( '/faqs/' ) ) ); ?>"><?php esc_html_e( 'Read our FAQs', 'medzuro' ); ?> &rarr;</a></p>
			</div>

		</div>
	</section>

	<section class="mzc-ways" aria-label="<?php esc_attr_e( 'Ways to reach us', 'medzuro' ); ?>">
		<div class="mzc-wrap">
			<ul>
				<li><a href="<?php echo esc_url( $tel( $fiji['phone'] ) ); ?>"><span class="mzc-ico"><?php medzuro_ref_icon( 'phone' ); ?></span><span><strong><?php esc_html_e( 'Call us', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Speak directly with our team.', 'medzuro' ); ?></small></span></a></li>
				<li><a href="mailto:<?php echo esc_attr( $fiji['emails'][0] ); ?>"><span class="mzc-ico"><?php medzuro_ref_icon( 'mail' ); ?></span><span><strong><?php esc_html_e( 'Email us', 'medzuro' ); ?></strong><small><?php esc_html_e( "We'll reply as soon as we can.", 'medzuro' ); ?></small></span></a></li>
				<li><a href="viber://chat?number=<?php echo esc_attr( rawurlencode( $fiji['viber'] ) ); ?>"><span class="mzc-ico mzc-ico--viber"><?php medzuro_ref_icon( 'viber' ); ?></span><span><strong><?php esc_html_e( 'Viber', 'medzuro' ); ?></strong><small><?php esc_html_e( 'Message us any time.', 'medzuro' ); ?></small></span></a></li>
				<li><a href="mailto:<?php echo esc_attr( $india['emails'][0] ); ?>?subject=<?php echo rawurlencode( 'Partnership enquiry' ); ?>"><span class="mzc-ico mzc-ico--gold"><?php medzuro_ref_icon( 'users' ); ?></span><span><strong><?php esc_html_e( 'Partnerships', 'medzuro' ); ?></strong><small><?php esc_html_e( "Let's build a healthier future together.", 'medzuro' ); ?></small></span></a></li>
			</ul>
		</div>
	</section>

</div>
