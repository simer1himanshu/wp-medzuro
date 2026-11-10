<?php
/**
 * A registered WooCommerce email used for all of the theme's own customer
 * emails (ready for pickup, shipped, confirmed, pickup and rebuy reminders).
 *
 * Sending through a registered WC_Email - rather than the anonymous
 * WC()->mailer()->send() - gives these emails the same headers, sender and
 * delivery path as WooCommerce's own order emails. It also adds a
 * "Medzuro customer notices" entry under WooCommerce -> Settings -> Emails
 * that can switch them all off.
 *
 * Loaded from the woocommerce_email_classes filter (see inc/order-flow.php).
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Customer notice email.
 */
class Medzuro_Email_Customer_Notice extends WC_Email {

	/** @var string */
	protected $notice_subject = '';

	/** @var string */
	protected $notice_heading = '';

	/** @var string HTML. */
	protected $notice_body = '';

	public function __construct() {
		$this->id             = 'medzuro_customer_notice';
		$this->customer_email = true;
		$this->title          = __( 'Medzuro customer notices', 'medzuro' );
		$this->description    = __( 'Ready for pickup, shipped, reservation confirmed, pickup reminder and rebuy reminder emails sent by the Medzuro theme. Subjects and text are set by the theme.', 'medzuro' );
		parent::__construct();
	}

	public function get_default_subject() {
		return '{site_title}';
	}

	public function get_default_heading() {
		return '{site_title}';
	}

	/** Always HTML. */
	public function get_email_type() {
		return 'html';
	}

	/** Only enable/disable is configurable; text comes from the theme. */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woocommerce' ),
				'default' => 'yes',
			),
		);
	}

	/**
	 * Send one notice.
	 *
	 * @param WC_Order $order   Order.
	 * @param string   $subject Subject.
	 * @param string   $heading Heading.
	 * @param string   $body    HTML body.
	 * @return bool
	 */
	public function send_notice( $order, $subject, $heading, $body ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		$this->setup_locale();

		$this->object         = $order;
		$this->recipient      = $order->get_billing_email();
		$this->notice_subject = $subject;
		$this->notice_heading = $heading;
		$this->notice_body    = $body;

		$sent = $this->get_recipient() ? $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), array() ) : false;

		$this->restore_locale();
		return (bool) $sent;
	}

	public function get_subject() {
		return $this->notice_subject ? $this->notice_subject : parent::get_subject();
	}

	public function get_heading() {
		return $this->notice_heading ? $this->notice_heading : parent::get_heading();
	}

	public function get_content_html() {
		return wc_get_template_html( 'emails/email-header.php', array( 'email_heading' => $this->get_heading(), 'email' => $this ) )
			. $this->notice_body
			. wc_get_template_html( 'emails/email-footer.php', array( 'email' => $this ) );
	}

	public function get_content_plain() {
		return wp_strip_all_tags( $this->notice_body );
	}
}
