<?php
/**
 * Floating "Chat on WhatsApp" button.
 *
 * Set the number in Appearance > Customize > WhatsApp chat. The button stays
 * hidden until a number is entered, so this can ship before the client
 * confirms which number to use.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customizer section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function medzuro_whatsapp_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'medzuro_whatsapp',
		array(
			'title'       => __( 'WhatsApp chat', 'medzuro' ),
			'description' => __( 'Floating WhatsApp button shown on every page. Leave the number empty to hide it.', 'medzuro' ),
			'priority'    => 160,
		)
	);

	$fields = array(
		'medzuro_whatsapp_number'  => array(
			'label'       => __( 'WhatsApp number (with country code)', 'medzuro' ),
			'description' => __( 'Example: 6799123456 for +679 912 3456.', 'medzuro' ),
			'default'     => '',
			'sanitize'    => 'medzuro_whatsapp_sanitize_number',
			'type'        => 'text',
		),
		'medzuro_whatsapp_message' => array(
			'label'       => __( 'Pre-filled message', 'medzuro' ),
			'description' => __( 'The product name is added automatically on product pages.', 'medzuro' ),
			'default'     => __( 'Hi Medzuro, I have a question.', 'medzuro' ),
			'sanitize'    => 'sanitize_text_field',
			'type'        => 'text',
		),
		'medzuro_whatsapp_label'   => array(
			'label'       => __( 'Button text', 'medzuro' ),
			'description' => '',
			'default'     => __( 'Chat with us', 'medzuro' ),
			'sanitize'    => 'sanitize_text_field',
			'type'        => 'text',
		),
		'medzuro_whatsapp_checkout' => array(
			'label'       => __( 'Also show on cart and checkout', 'medzuro' ),
			'description' => '',
			'default'     => false,
			'sanitize'    => 'wp_validate_boolean',
			'type'        => 'checkbox',
		),
	);

	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting(
			$id,
			array(
				'type'              => 'option',
				'default'           => $f['default'],
				'sanitize_callback' => $f['sanitize'],
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $f['label'],
				'description' => $f['description'],
				'section'     => 'medzuro_whatsapp',
				'type'        => $f['type'],
			)
		);
	}
}
add_action( 'customize_register', 'medzuro_whatsapp_customizer' );

/**
 * Digits only.
 *
 * @param string $value Number.
 * @return string
 */
function medzuro_whatsapp_sanitize_number( $value ) {
	return preg_replace( '/\D+/', '', (string) $value );
}

/**
 * Print the button.
 */
function medzuro_whatsapp_button() {
	$number = medzuro_whatsapp_sanitize_number( get_option( 'medzuro_whatsapp_number', '' ) );
	$number = (string) apply_filters( 'medzuro_whatsapp_number', $number );
	if ( ! $number || is_admin() || is_page( 'sm-dashboard' ) ) {
		return;
	}
	if ( ! get_option( 'medzuro_whatsapp_checkout', false ) && function_exists( 'is_checkout' ) && ( is_checkout() || is_cart() ) ) {
		return;
	}

	$message = (string) get_option( 'medzuro_whatsapp_message', __( 'Hi Medzuro, I have a question.', 'medzuro' ) );
	if ( function_exists( 'is_product' ) && is_product() ) {
		/* translators: %s: product name */
		$message .= ' ' . sprintf( __( '(About: %s)', 'medzuro' ), get_the_title() );
	}
	$label = (string) get_option( 'medzuro_whatsapp_label', __( 'Chat with us', 'medzuro' ) );
	$url   = 'https://wa.me/' . rawurlencode( $number ) . '?text=' . rawurlencode( trim( $message ) );
	?>
	<a class="mz-wa" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $label . ' - WhatsApp' ); ?>">
		<svg viewBox="0 0 32 32" width="26" height="26" aria-hidden="true" focusable="false"><path fill="currentColor" d="M16 3C9 3 3.3 8.6 3.3 15.6c0 2.2.6 4.4 1.7 6.3L3 29l7.3-1.9c1.8 1 3.8 1.5 5.8 1.5 7 0 12.7-5.7 12.7-12.7S23 3 16 3zm0 23.2c-1.9 0-3.7-.5-5.3-1.4l-.4-.2-4.3 1.1 1.2-4.2-.3-.4c-1-1.6-1.6-3.5-1.6-5.5C5.3 9.8 10.1 5.2 16 5.2s10.6 4.7 10.6 10.5S21.8 26.2 16 26.2zm5.8-7.9c-.3-.2-1.9-.9-2.2-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-.3-.2-1.3-.5-2.5-1.6-.9-.8-1.6-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.3.3-.6.1-.2 0-.4 0-.6-.1-.2-.7-1.7-1-2.3-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.1-1.2 2.8s1.2 3.2 1.4 3.5c.2.2 2.4 3.6 5.7 5 .8.3 1.4.5 1.9.7.8.3 1.5.2 2.1.1.6-.1 1.9-.8 2.2-1.5.3-.7.3-1.4.2-1.5-.1-.2-.3-.3-.6-.4z"/></svg>
		<span class="mz-wa__label"><?php echo esc_html( $label ); ?></span>
	</a>
	<style>
		.mz-wa{position:fixed;right:18px;bottom:18px;z-index:9990;display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border-radius:999px;background:#25d366;color:#fff;font-weight:600;text-decoration:none;box-shadow:0 6px 18px rgba(0,0,0,.18);transition:transform .15s ease}
		.mz-wa:hover,.mz-wa:focus-visible{transform:translateY(-2px);color:#fff}
		.mz-wa:focus-visible{outline:3px solid #0b1b33;outline-offset:2px}
		@media (max-width:600px){.mz-wa{padding:13px;right:14px;bottom:14px}.mz-wa__label{display:none}}
		@media print{.mz-wa{display:none}}
	</style>
	<?php
}
add_action( 'wp_footer', 'medzuro_whatsapp_button', 50 );
