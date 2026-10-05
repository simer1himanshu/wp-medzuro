<?php
/**
 * Contact page: office details and the enquiry form handler.
 *
 * The form posts to admin-post.php, is protected by a nonce, a honeypot field
 * and a short per-visitor cooldown, and is emailed to the Fiji sales address
 * with the customer's address as Reply-To. No form plugin is needed.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Office and contact details shown on the Contact page.
 *
 * @return array
 */
function medzuro_contact_details() {
	return apply_filters(
		'medzuro_contact_details',
		array(
			'recipient'  => 'sales@medzuroretail.com',
			'fiji'       => array(
				'phone'  => '+679 8029837',
				'viber'  => '+6798029837',
				'emails' => array( 'sales@medzuroretail.com', 'fiji@medzuroretail.com' ),
				'entity' => 'Medzuro Wellness PTE Limited',
				'lines'  => array( '18, Valili St, Vishnu Deo Road', 'Nakasi, Suva, Fiji Islands' ),
			),
			'india'      => array(
				'phone'  => '+91 9024417352',
				'emails' => array( 'hq@medzuroretail.com' ),
				'entity' => 'Medzuro Wellness Private Limited',
				'lines'  => array( '95, Raghunandan Vihar', 'Jagatpura, Jaipur, Rajasthan, India - 302017' ),
			),
		)
	);
}

/**
 * Handle the enquiry form submission.
 */
function medzuro_contact_submit() {
	$back = wp_get_referer() ?: home_url( '/contact/' );
	$back = remove_query_arg( 'contact', $back );
	$done = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'contact', $status, $back ) . '#mz-contact-form' );
		exit;
	};

	if ( ! isset( $_POST['medzuro_contact_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['medzuro_contact_nonce'] ) ), 'medzuro_contact_submit' ) ) {
		$done( 'error' );
	}

	// Honeypot: real visitors never fill this in. Pretend success to bots.
	if ( ! empty( $_POST['company'] ) ) {
		$done( 'sent' );
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$country = sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) );
	$type    = sanitize_text_field( wp_unslash( $_POST['enquiry_type'] ?? '' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( '' === $name || ! is_email( $email ) || '' === $subject || '' === $message ) {
		$done( 'invalid' );
	}

	$key = 'mz_contact_' . md5( $_SERVER['REMOTE_ADDR'] ?? '' ); // phpcs:ignore WordPressVIPMinimum.Variables.ServerVariables.UserControlledHeaders
	if ( get_transient( $key ) ) {
		$done( 'wait' );
	}
	set_transient( $key, 1, 30 );

	$details = medzuro_contact_details();
	$body    = implode(
		"\n",
		array(
			'Name: ' . $name,
			'Email: ' . $email,
			'Phone: ' . $phone,
			'Country: ' . $country,
			'Enquiry type: ' . $type,
			'Subject: ' . $subject,
			'',
			$message,
		)
	);

	$sent = wp_mail(
		$details['recipient'],
		sprintf( '[Medzuro Retail enquiry] %s: %s', $type ?: 'General', $subject ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	$done( $sent ? 'sent' : 'error' );
}
add_action( 'admin_post_nopriv_medzuro_contact_submit', 'medzuro_contact_submit' );
add_action( 'admin_post_medzuro_contact_submit', 'medzuro_contact_submit' );
