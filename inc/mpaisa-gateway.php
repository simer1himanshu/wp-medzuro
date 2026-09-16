<?php
/**
 * M-PAiSA (Vodafone Fiji) payment gateway for WooCommerce.
 *
 * Implements the flow documented in Vodafone's "M-PAiSA Payments Gateway
 * API Guide" v1.3: get a bearer token, handshake to get a signed requestID,
 * send the customer to M-PAiSA's hosted page to confirm with their mobile
 * number + PIN + OTP, then verify the signed redirect back before marking
 * the order paid.
 *
 * The Business ID and Client Secret Vodafone issued are entered by the
 * store owner under WooCommerce > Settings > Payments > M-PAiSA. They are
 * stored in the WordPress database (the `woocommerce_mpaisa_settings`
 * option) — never hardcoded here, so they never end up in this repository
 * or its git history.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

// NOTE: this must NOT hook 'plugins_loaded' *or* 'init'. This file is
// require_once'd from the theme's functions.php, which WordPress loads
// *after* 'plugins_loaded' has already fired (plugins load and
// 'plugins_loaded' fires, then 'setup_theme', then functions.php, then
// 'after_setup_theme', then 'init'). A 'plugins_loaded' callback registered
// from here never runs at all — that was the first version of this bug,
// confirmed live via WooCommerce's REST payment_gateways list never
// including "mpaisa".
//
// Hooking 'init' instead "worked" in the sense that the callback did fire,
// but it introduced a second, subtler bug: WC_Payment_Gateways::init()
// (which turns the 'woocommerce_payment_gateways' filter's class-name
// strings into objects via `class_exists($gateway) ? new $gateway() : ...`,
// silently dropping any name that isn't a real class *at that exact
// moment*) runs at a point that is not guaranteed to be after our own
// 'init' callback — WordPress does not order same-hook callbacks across
// files by registration time alone once priorities/other init hooks are
// involved. The result: the classic add_filter() below correctly returned
// the string 'WC_Gateway_MPaisa', but WooCommerce's own gateway loader ran
// before medzuro_mpaisa_init_gateway_class() had defined that class, so
// class_exists('WC_Gateway_MPaisa') was false at the moment it mattered and
// the gateway was silently dropped from WC()->payment_gateways() — even
// though calling `new WC_Gateway_MPaisa()` directly, later in the request,
// worked perfectly fine.
//
// The fix is the same one already used for the Blocks integration below:
// don't hook anything. WooCommerce (a plugin) has fully finished loading,
// including defining WC_Payment_Gateway, by the time this file itself
// loads (theme functions.php always loads after all plugins). So just
// define the class immediately, unconditionally, right now.
medzuro_mpaisa_init_gateway_class();

/**
 * Defines WC_Gateway_MPaisa once WooCommerce's base gateway class exists.
 */
function medzuro_mpaisa_init_gateway_class() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'WC_Gateway_MPaisa' ) ) {
		return;
	}

	/**
	 * WooCommerce payment gateway for M-PAiSA.
	 */
	class WC_Gateway_MPaisa extends WC_Payment_Gateway {

		/** Live API base URL. */
		const API_LIVE = 'https://payments.m-paisa.com';

		/** Staging/test API base URL. */
		const API_TEST = 'https://payments-staging.m-paisa.com';

		/** @var string 'test' or 'live'. */
		public $environment;

		/** @var string Business ID / clientId issued by Vodafone. */
		public $business_id;

		/** @var string Client Secret issued by Vodafone (also the "merchant secret" used in signatures). */
		public $client_secret;

		public function __construct() {
			$this->id                 = 'mpaisa';
			$this->icon               = '';
			$this->has_fields         = false;
			$this->method_title       = 'M-PAiSA (Vodafone Fiji)';
			$this->method_description = "Accept payments via M-PAiSA, Vodafone Fiji's mobile money service. Customers are sent to a Vodafone-hosted page to confirm the payment with their mobile number, PIN and OTP, then returned here automatically.";

			$this->init_form_fields();
			$this->init_settings();

			$this->title         = $this->get_option( 'title' );
			$this->description   = $this->get_option( 'description' );
			$this->enabled        = $this->get_option( 'enabled' );
			$this->environment    = $this->get_option( 'environment', 'live' );
			$this->business_id    = $this->get_option( 'business_id' );
			$this->client_secret  = $this->get_option( 'client_secret' );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Admin settings screen fields (WooCommerce > Settings > Payments > M-PAiSA).
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'       => array(
					'title'   => 'Enable/Disable',
					'type'    => 'checkbox',
					'label'   => 'Enable M-PAiSA',
					'default' => 'no',
				),
				'title'         => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'Payment method name the customer sees at checkout.',
					'default'     => 'M-PAiSA (Vodafone Fiji Mobile Money)',
					'desc_tip'    => true,
				),
				'description'   => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'Payment method description the customer sees at checkout.',
					'default'     => "Pay securely with M-PAiSA. You'll be taken to Vodafone's payment page to confirm with your mobile number and PIN.",
					'desc_tip'    => true,
				),
				'environment'   => array(
					'title'       => 'Environment',
					'type'        => 'select',
					'description' => 'Use Test/staging while verifying the integration, then switch to Live to accept real payments.',
					'default'     => 'live',
					'options'     => array(
						'test' => 'Test / staging (payments-staging.m-paisa.com)',
						'live' => 'Live (payments.m-paisa.com)',
					),
					'desc_tip'    => true,
				),
				'business_id'   => array(
					'title'       => 'Business ID (Client ID)',
					'type'        => 'text',
					'description' => 'The Business ID / Client ID Vodafone issued for your M-PAiSA merchant account.',
					'default'     => '',
					'desc_tip'    => true,
				),
				'client_secret' => array(
					'title'       => 'Client Secret',
					'type'        => 'password',
					'description' => 'The Client Secret Vodafone issued. Stored in the WordPress database only — never committed to source control.',
					'default'     => '',
					'desc_tip'    => true,
				),
			);
		}

		/**
		 * @return string API base URL for the configured environment.
		 */
		private function api_base() {
			return 'test' === $this->environment ? self::API_TEST : self::API_LIVE;
		}

		/**
		 * The URL Vodafone redirects the customer's browser back to. A REST
		 * route (not the classic ?wc-api= query var) so it has no query
		 * string of its own for Vodafone's own "?param=value" append to
		 * collide with.
		 *
		 * @return string
		 */
		public static function callback_url() {
			return home_url( '/wp-json/mpaisa/v1/callback' );
		}

		/**
		 * SHA-256 digest per the API guide's signature formulas.
		 *
		 * @param array $parts Ordered strings to concatenate before hashing.
		 * @return string
		 */
		private static function digest( array $parts ) {
			return hash( 'sha256', implode( '', $parts ) );
		}

		/**
		 * @param WC_Order $order
		 * @return string Order total formatted as the API expects, e.g. "10.50".
		 */
		private static function format_amount( WC_Order $order ) {
			return number_format( (float) $order->get_total(), 2, '.', '' );
		}

		/**
		 * A short, plain-text item description. Kept free of spaces/punctuation
		 * so the exact bytes we hash can never drift from what gets sent in a
		 * URL-encoded query string.
		 *
		 * @param WC_Order $order
		 * @return string
		 */
		private static function item_detail( WC_Order $order ) {
			return substr( 'MedzuroOrder' . $order->get_id(), 0, 200 );
		}

		/**
		 * Step 1 of the handshake: exchange the Business ID / Client Secret
		 * for a short-lived bearer token.
		 *
		 * @return string|WP_Error
		 */
		private function get_token() {
			// Per the official "M-PAiSA Payments Gateway API Guide" v1.3
			// (§4.2.1), the generateAuth endpoint lives at
			// {base}/live/API/generateAuth — note the literal "/live/" path
			// segment is present even against the *staging* host; it is not
			// related to which environment (test vs live) is selected. This
			// file previously called {base}/API/generateAuth (missing that
			// segment), which 404'd against Vodafone's own Next.js front end
			// instead of ever reaching their API — confirmed live via a raw
			// HTTP 404 + HTML body captured in the mpaisa log.
			$response = wp_remote_post(
				$this->api_base() . '/live/API/generateAuth',
				array(
					'timeout' => 20,
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode(
						array(
							'clientId'     => $this->business_id,
							'clientSecret' => $this->client_secret,
						)
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code     = wp_remote_retrieve_response_code( $response );
			$raw_body = wp_remote_retrieve_body( $response );
			$body     = json_decode( $raw_body, true );

			if ( 200 !== (int) $code || empty( $body['success'] ) || empty( $body['token'] ) ) {
				medzuro_mpaisa_log( 'generateAuth failed: HTTP ' . $code . ' body=' . substr( (string) $raw_body, 0, 500 ) );
				return new WP_Error( 'mpaisa_auth_failed', 'M-PAiSA authentication failed. Check the Business ID and Client Secret in WooCommerce > Settings > Payments > M-PAiSA.' );
			}

			return $body['token'];
		}

		/**
		 * Step 2: handshake for a requestID, verifying M-PAiSA's own digest
		 * before we trust it.
		 *
		 * @return array|WP_Error {requestID, tid, amt, idet}
		 */
		private function handshake( $token, WC_Order $order ) {
			$tid  = (string) $order->get_id();
			$amt  = self::format_amount( $order );
			$idet = self::item_detail( $order );

			// API guide §4.1.2 ("API Sample Structure") gives this as
			// {base}/live/API/?url=...&tID=...&amt=...&cID=...&iDet=... —
			// the same "/live/" prefix generateAuth needs. (§4.2.2's own
			// worked example omits it and 404s in practice — confirmed
			// live against production, so §4.1.2's pattern is the one that
			// actually matches Vodafone's server; treating that omission
			// as a documentation typo rather than following it.)
			$url = add_query_arg(
				array(
					'url'  => self::callback_url(),
					'tID'  => $tid,
					'amt'  => $amt,
					'cID'  => $this->business_id,
					'iDet' => $idet,
				),
				$this->api_base() . '/live/API/'
			);

			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 20,
					'headers' => array( 'Authorization' => 'Bearer ' . $token ),
				)
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $body['requestID'] ) || ! isset( $body['authdigestv2'] ) ) {
				medzuro_mpaisa_log( 'Handshake failed for order ' . $order->get_id() . ': ' . wp_remote_retrieve_body( $response ) );
				return new WP_Error( 'mpaisa_handshake_failed', 'Could not start the M-PAiSA payment. Please try again.' );
			}

			$response_code   = isset( $body['response'] ) ? (string) $body['response'] : '101';
			$expected_digest = self::digest( array( $tid, $amt, $idet, $this->client_secret, $response_code ) );

			// M-PAiSA sends authdigestv2 as UPPERCASE hex, while PHP's
			// hash('sha256', ...) returns lowercase — confirmed live via a
			// captured mismatch where $expected and $actual were the exact
			// same digest, differing only in case. hash_equals() compares
			// raw bytes, so it (rightly) rejects that as a mismatch; case
			// carries no meaning for a hex digest, so normalize both sides
			// before comparing.
			if ( ! hash_equals( strtolower( $expected_digest ), strtolower( (string) $body['authdigestv2'] ) ) ) {
				medzuro_mpaisa_log( 'Handshake digest mismatch for order ' . $order->get_id() . ' (requestID ' . $body['requestID'] . ')' );
				return new WP_Error( 'mpaisa_digest_mismatch', 'M-PAiSA responded with a signature that did not verify. Payment was not started, for your safety.' );
			}

			return array(
				'requestID'    => $body['requestID'],
				'tid'          => $tid,
				'amt'          => $amt,
				'idet'         => $idet,
				// The handshake response hands back the exact hosted-page
				// base URL to redirect the customer to (API guide §4.2.2
				// example: "paymentspage": "https://payments-staging.m-paisa.com/live/").
				// Preferring this over reconstructing it ourselves means we
				// always follow Vodafone's own live value rather than an
				// assumption baked into this file.
				'paymentspage' => ! empty( $body['paymentspage'] ) ? (string) $body['paymentspage'] : null,
			);
		}

		/**
		 * Called by WooCommerce when the customer clicks "Place order" with
		 * M-PAiSA selected. Runs the handshake, then sends the browser to
		 * Vodafone's hosted payment page.
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return array( 'result' => 'failure' );
			}

			if ( ! $this->business_id || ! $this->client_secret ) {
				wc_add_notice( 'M-PAiSA is not fully configured yet. Please choose another payment method or contact us.', 'error' );
				return array( 'result' => 'failure' );
			}

			$token = $this->get_token();
			if ( is_wp_error( $token ) ) {
				medzuro_mpaisa_log( 'Token error for order ' . $order_id . ': ' . $token->get_error_message() );
				wc_add_notice( 'Could not reach M-PAiSA right now. Please try again shortly.', 'error' );
				return array( 'result' => 'failure' );
			}

			$handshake = $this->handshake( $token, $order );
			if ( is_wp_error( $handshake ) ) {
				wc_add_notice( $handshake->get_error_message(), 'error' );
				return array( 'result' => 'failure' );
			}

			// Remember exactly what we sent, so the callback verifies against
			// our own records rather than anything echoed back in the URL.
			$order->update_meta_data( '_mpaisa_request_id', $handshake['requestID'] );
			$order->update_meta_data( '_mpaisa_tid', $handshake['tid'] );
			$order->update_meta_data( '_mpaisa_amt', $handshake['amt'] );
			$order->update_meta_data( '_mpaisa_idet', $handshake['idet'] );
			$order->update_meta_data( '_mpaisa_environment', $this->environment );
			$order->update_status( 'pending', 'Awaiting M-PAiSA payment confirmation.' );
			$order->save();

			// API guide §4.2.3 ("Load Payments Page"): the customer-facing
			// hosted page is {base}/live/?url=...&tID=...&amt=...&cID=...
			// &iDet=...&rID=... — NOT the {base}/API/ path used for the
			// server-to-server handshake call above. Prefer the exact
			// "paymentspage" base the handshake response handed back;
			// fall back to the documented {base}/live/ only if a given
			// response ever omits it.
			$pay_url = add_query_arg(
				array(
					'url'  => self::callback_url(),
					'tID'  => $handshake['tid'],
					'amt'  => $handshake['amt'],
					'cID'  => $this->business_id,
					'iDet' => $handshake['idet'],
					'rID'  => $handshake['requestID'],
				),
				$handshake['paymentspage'] ? $handshake['paymentspage'] : $this->api_base() . '/live/'
			);

			return array(
				'result'   => 'success',
				'redirect' => $pay_url,
			);
		}

		/**
		 * "Check M-PAiSA status" order action (Edit Order screen), for the
		 * rare case a customer's browser never makes it back to our
		 * callback even though the payment went through on Vodafone's side.
		 *
		 * @param WC_Order $order
		 */
		public function check_status( WC_Order $order ) {
			$rid = $order->get_meta( '_mpaisa_request_id' );
			$tid = $order->get_meta( '_mpaisa_tid' );

			if ( ! $rid || ! $tid ) {
				$order->add_order_note( 'M-PAiSA status check skipped: this order has no recorded M-PAiSA request.' );
				return;
			}

			$token = $this->get_token();
			if ( is_wp_error( $token ) ) {
				$order->add_order_note( 'M-PAiSA status check failed: ' . $token->get_error_message() );
				return;
			}

			// API guide §4.2.4: the status-checker endpoint is
			// {base}/live/requeststatus/?rID=...&tID=...&cID=..., same
			// "/live/" path segment as the generateAuth and payments-page
			// endpoints above — not {base}/requeststatus/.
			$url = add_query_arg(
				array(
					'rID' => $rid,
					'tID' => $tid,
					'cID' => $this->business_id,
				),
				$this->api_base() . '/live/requeststatus/'
			);

			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 20,
					'headers' => array( 'Authorization' => 'Bearer ' . $token ),
				)
			);

			if ( is_wp_error( $response ) ) {
				$order->add_order_note( 'M-PAiSA status check failed: ' . $response->get_error_message() );
				return;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$status = isset( $body['requestIDstatus'] ) ? $body['requestIDstatus'] : 'UNKNOWN';

			$order->add_order_note( 'M-PAiSA status check: ' . $status . ' — ' . wp_remote_retrieve_body( $response ) );

			if ( 'SUCCESS' === $status && ! $order->is_paid() ) {
				$order->payment_complete();
				$order->add_order_note( 'Marked paid from a manual M-PAiSA status check (requestID ' . $rid . ').' );
			}
		}
	}
}

/**
 * Registers the gateway with WooCommerce.
 */
add_filter(
	'woocommerce_payment_gateways',
	function ( $gateways ) {
		$gateways[] = 'WC_Gateway_MPaisa';
		return $gateways;
	}
);

/**
 * Registers M-PAiSA with the WooCommerce Blocks checkout.
 *
 * This theme's checkout page uses the block-based Checkout block, which
 * renders payment methods from its own client-side registry rather than
 * from WC_Payment_Gateways directly. A gateway that only implements the
 * classic WC_Payment_Gateway API (above) is "available" in every
 * server-side sense — it shows up in WC()->payment_gateways() and in the
 * Store API's cart.payment_methods — but never actually appears at
 * checkout, which otherwise silently shows "There are no payment methods
 * available." Built-in gateways (bacs, cheque, cod) don't need this because
 * WooCommerce core registers their Blocks support itself; custom gateways
 * have to do it themselves, via this integration class plus
 * assets/js/mpaisa-blocks.js.
 *
 * NOTE: this must NOT wait for the 'woocommerce_blocks_loaded' action — same
 * class of bug as the 'plugins_loaded' one fixed above. Confirmed live via
 * temporary diagnostics: did_action('woocommerce_blocks_loaded') was 1 (it
 * fires, like 'plugins_loaded', while WooCommerce itself loads, before this
 * theme's functions.php runs) yet Medzuro_MPaisa_Blocks_Support was never
 * defined, because add_action('woocommerce_blocks_loaded', ...) registered
 * from here is - again - registering for an event that already happened.
 * The fix is the same one used above: since WooCommerce (and its bundled
 * Blocks classes) have already fully loaded by the time this file runs,
 * there's nothing to wait for — define the class directly, gated only by a
 * class_exists() safety check, and hook the registration action directly
 * too, without an intermediate 'woocommerce_blocks_loaded' wrapper.
 */
if ( class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {

	/**
	 * Blocks checkout integration for M-PAiSA.
	 */
	class Medzuro_MPaisa_Blocks_Support extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {

		/** @var WC_Gateway_MPaisa|null */
		private $gateway;

		/** @var string */
		protected $name = 'mpaisa';

		/**
		 * Loads settings and the underlying classic gateway instance.
		 */
		public function initialize() {
			$this->settings = get_option( 'woocommerce_mpaisa_settings', array() );
			$gateways       = WC()->payment_gateways->payment_gateways();
			$this->gateway  = isset( $gateways[ $this->name ] ) ? $gateways[ $this->name ] : null;
		}

		/**
		 * @return bool Whether M-PAiSA should appear at checkout.
		 */
		public function is_active() {
			return $this->gateway && $this->gateway->is_available();
		}

		/**
		 * Registers and returns the handle of the JS that renders this
		 * payment method in the Checkout block.
		 *
		 * @return string[]
		 */
		public function get_payment_method_script_handles() {
			wp_register_script(
				'medzuro-mpaisa-blocks',
				get_template_directory_uri() . '/assets/js/mpaisa-blocks.js',
				array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities', 'wp-i18n' ),
				wp_get_theme()->get( 'Version' ),
				true
			);

			return array( 'medzuro-mpaisa-blocks' );
		}

		/**
		 * Data exposed to the JS above as wc.wcSettings.getSetting('mpaisa_data').
		 *
		 * @return array
		 */
		public function get_payment_method_data() {
			return array(
				'title'       => $this->gateway ? $this->gateway->title : 'M-PAiSA (Vodafone Fiji Mobile Money)',
				'description' => $this->gateway ? $this->gateway->description : '',
				'supports'    => $this->gateway ? array_filter( array( 'products' ), array( $this->gateway, 'supports' ) ) : array(),
			);
		}
	}

	add_action(
		'woocommerce_blocks_payment_method_type_registration',
		function ( $registry ) {
			// No type hint on $registry: WC has renamed/relocated Blocks
			// payment classes across versions, and a strict-type mismatch
			// here would throw a fatal TypeError from inside this hook,
			// which (on at least one observed request) aborted the rest of
			// that request's payment-gateway setup entirely — the classic
			// "mpaisa" gateway briefly vanished from
			// WC()->payment_gateways()->payment_gateways() as a result.
			// register() is called defensively so a mismatch here degrades
			// to "Blocks checkout support missing" rather than breaking
			// checkout altogether.
			if ( is_object( $registry ) && method_exists( $registry, 'register' ) ) {
				try {
					$registry->register( new Medzuro_MPaisa_Blocks_Support() );
				} catch ( \Throwable $e ) {
					update_option( 'medzuro_mpaisa_blocks_last_error', $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() );
					medzuro_mpaisa_log( 'Blocks registration failed: ' . $e->getMessage() );
				}
			}
		}
	);
}

/**
 * Adds a "Check M-PAiSA status" action to the Edit Order screen.
 */
add_filter(
	'woocommerce_order_actions',
	function ( $actions ) {
		global $theorder;
		if ( $theorder && 'mpaisa' === $theorder->get_payment_method() ) {
			$actions['mpaisa_check_status'] = 'Check M-PAiSA payment status';
		}
		return $actions;
	}
);
add_action(
	'woocommerce_order_action_mpaisa_check_status',
	function ( $order ) {
		$gateways = WC()->payment_gateways()->payment_gateways();
		if ( isset( $gateways['mpaisa'] ) ) {
			$gateways['mpaisa']->check_status( $order );
		}
	}
);

/**
 * Logs to the WooCommerce > Status > Logs "mpaisa" log, when available.
 *
 * @param string $message
 */
function medzuro_mpaisa_log( $message ) {
	if ( function_exists( 'wc_get_logger' ) ) {
		wc_get_logger()->info( $message, array( 'source' => 'mpaisa' ) );
	}
}

/**
 * Registers the REST callback route M-PAiSA redirects the customer back to.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'mpaisa/v1',
			'/callback',
			array(
				'methods'             => array( 'GET' ),
				'callback'            => 'medzuro_mpaisa_handle_callback',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/**
 * Handles the signed redirect M-PAiSA sends the customer's browser back to
 * after they confirm (or cancel) payment on the hosted page.
 *
 * Per the API guide (4.3.2): "M-PAiSA appends tokenv2 to all redirect URLs.
 * Merchants MUST verify tokenv2 before fulfilling any order... Only
 * proceed if they match." That verification — recomputing the SHA-256
 * digest from values *we* recorded at handshake time, never from the query
 * string itself — is what actually protects this endpoint; it needs no
 * separate authentication because an unsigned or mis-signed request simply
 * cannot mark an order paid.
 *
 * @param WP_REST_Request $request
 * @return void
 */
function medzuro_mpaisa_handle_callback( WP_REST_Request $request ) {
	$tid     = sanitize_text_field( (string) $request->get_param( 'tID' ) );
	$rid     = sanitize_text_field( (string) $request->get_param( 'rID' ) );
	$rcode   = sanitize_text_field( (string) $request->get_param( 'rCode' ) );
	$phone   = sanitize_text_field( (string) $request->get_param( 'customerphonenumber' ) );
	$tokenv2 = sanitize_text_field( (string) $request->get_param( 'tokenv2' ) );

	// tID is the WooCommerce order ID — see process_payment() above.
	$order = ( $tid && ctype_digit( $tid ) ) ? wc_get_order( (int) $tid ) : false;

	if ( ! $order || 'mpaisa' !== $order->get_payment_method() ) {
		wp_die( esc_html__( 'Order not found.', 'medzuro' ), 'M-PAiSA', array( 'response' => 404 ) );
	}

	// Already handled (e.g. the customer's browser hit this URL twice).
	if ( $order->is_paid() ) {
		wp_safe_redirect( $order->get_checkout_order_received_url() );
		exit;
	}

	$gateways = WC()->payment_gateways()->payment_gateways();
	$gateway  = isset( $gateways['mpaisa'] ) ? $gateways['mpaisa'] : null;

	if ( ! $gateway instanceof WC_Gateway_MPaisa ) {
		wp_die( esc_html__( 'M-PAiSA is not available.', 'medzuro' ), 'M-PAiSA', array( 'response' => 500 ) );
	}

	$stored_tid = (string) $order->get_meta( '_mpaisa_tid' );
	$stored_rid = (string) $order->get_meta( '_mpaisa_request_id' );
	$stored_amt = (string) $order->get_meta( '_mpaisa_amt' );
	$stored_idet = (string) $order->get_meta( '_mpaisa_idet' );
	$secret     = $gateway->client_secret;

	$expected = hash( 'sha256', $tid . $stored_amt . $stored_idet . $stored_rid . $secret . $rcode );

	// Same case-sensitivity fix as handshake(): M-PAiSA's tokenv2 comes back
	// as UPPERCASE hex; hash() produces lowercase. Normalize both sides.
	$verified = $stored_rid
		&& $tid === $stored_tid
		&& $rid === $stored_rid
		&& hash_equals( strtolower( $expected ), strtolower( $tokenv2 ) );

	if ( ! $verified ) {
		medzuro_mpaisa_log( 'Callback signature did not verify for order ' . $order->get_id() . ' (rCode ' . $rcode . ')' );
		$order->add_order_note( 'M-PAiSA sent back an unverifiable signature — payment was NOT confirmed. rCode received: ' . $rcode );
		wp_safe_redirect( $order->get_checkout_payment_url() );
		exit;
	}

	if ( $phone ) {
		$order->update_meta_data( '_mpaisa_customer_phone', $phone );
	}

	// 101/112 both read as "success" in the API guide's response-code table
	// (§5.0): 101 SUCCESS, 112 Transaction completed successfully.
	if ( in_array( $rcode, array( '101', '112' ), true ) ) {
		$order->payment_complete();
		$order->add_order_note( 'M-PAiSA payment confirmed, signature verified (requestID ' . $rid . ', rCode ' . $rcode . ').' );
		$order->save();

		wp_safe_redirect( $order->get_checkout_order_received_url() );
		exit;
	}

	if ( '111' === $rcode ) {
		$order->update_status( 'cancelled', 'Customer cancelled the M-PAiSA payment.' );
		wp_safe_redirect( $order->get_checkout_payment_url() );
		exit;
	}

	$order->update_status( 'failed', 'M-PAiSA payment failed or was not completed (rCode ' . $rcode . ').' );
	wp_safe_redirect( $order->get_checkout_payment_url() );
	exit;
}

/**
 * A dedicated settings screen for M-PAiSA.
 *
 * This WooCommerce version's redesigned "Payments" settings page only
 * renders UI for a fixed, curated set of gateway ids (WooPayments, PayPal,
 * bacs/cheque/cod, and a short suggestions list) — it silently shows a
 * blank panel for any other registered gateway, custom ones included.
 * Rather than depend on that, M-PAiSA gets its own simple settings page
 * under WooCommerce's admin menu. It reads and writes the exact same
 * `woocommerce_mpaisa_settings` option WC_Payment_Gateway::init_settings()
 * already uses, so the gateway itself needs no special-casing.
 */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'woocommerce',
			'M-PAiSA Settings',
			'M-PAiSA',
			'manage_woocommerce',
			'medzuro-mpaisa-settings',
			'medzuro_mpaisa_settings_page'
		);
	}
);

/**
 * Renders (and saves) the M-PAiSA settings page.
 */
function medzuro_mpaisa_settings_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$settings = get_option( 'woocommerce_mpaisa_settings', array() );

	if ( isset( $_POST['medzuro_mpaisa_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['medzuro_mpaisa_nonce'] ) ), 'medzuro_mpaisa_save' ) ) {
		$environment = isset( $_POST['environment'] ) && 'test' === $_POST['environment'] ? 'test' : 'live';

		$settings = array(
			'enabled'       => isset( $_POST['enabled'] ) ? 'yes' : 'no',
			'title'         => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'description'   => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
			'environment'   => $environment,
			'business_id'   => isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '',
			'client_secret' => isset( $_POST['client_secret'] ) ? sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) : '',
		);

		update_option( 'woocommerce_mpaisa_settings', $settings );
		echo '<div class="notice notice-success is-dismissible"><p>M-PAiSA settings saved.</p></div>';
	}

	$enabled       = isset( $settings['enabled'] ) ? $settings['enabled'] : 'no';
	$title         = isset( $settings['title'] ) ? $settings['title'] : 'M-PAiSA (Vodafone Fiji Mobile Money)';
	$description   = isset( $settings['description'] ) ? $settings['description'] : "Pay securely with M-PAiSA. You'll be taken to Vodafone's payment page to confirm with your mobile number and PIN.";
	$environment   = isset( $settings['environment'] ) ? $settings['environment'] : 'live';
	$business_id   = isset( $settings['business_id'] ) ? $settings['business_id'] : '';
	$client_secret = isset( $settings['client_secret'] ) ? $settings['client_secret'] : '';
	$callback_url  = class_exists( 'WC_Gateway_MPaisa' ) ? WC_Gateway_MPaisa::callback_url() : home_url( '/wp-json/mpaisa/v1/callback' );
	?>
	<div class="wrap">
		<h1>M-PAiSA (Vodafone Fiji) Settings</h1>
		<p>WooCommerce's built-in Payments screen doesn't display custom gateways in this version, so M-PAiSA is configured here instead — these are the same settings the gateway reads at checkout.</p>
		<p>Callback URL Vodafone redirects customers back to: <code><?php echo esc_html( $callback_url ); ?></code></p>
		<form method="post">
			<?php wp_nonce_field( 'medzuro_mpaisa_save', 'medzuro_mpaisa_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mpaisa_enabled">Enable M-PAiSA</label></th>
					<td><input type="checkbox" id="mpaisa_enabled" name="enabled" <?php checked( $enabled, 'yes' ); ?> /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mpaisa_title">Title</label></th>
					<td>
						<input type="text" class="regular-text" id="mpaisa_title" name="title" value="<?php echo esc_attr( $title ); ?>" />
						<p class="description">Payment method name the customer sees at checkout.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mpaisa_description">Description</label></th>
					<td>
						<textarea class="large-text" rows="3" id="mpaisa_description" name="description"><?php echo esc_textarea( $description ); ?></textarea>
						<p class="description">Payment method description the customer sees at checkout.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mpaisa_environment">Environment</label></th>
					<td>
						<select id="mpaisa_environment" name="environment">
							<option value="live" <?php selected( $environment, 'live' ); ?>>Live (payments.m-paisa.com)</option>
							<option value="test" <?php selected( $environment, 'test' ); ?>>Test / staging (payments-staging.m-paisa.com)</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mpaisa_business_id">Business ID (Client ID)</label></th>
					<td><input type="text" class="regular-text" id="mpaisa_business_id" name="business_id" value="<?php echo esc_attr( $business_id ); ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="mpaisa_client_secret">Client Secret</label></th>
					<td>
						<input type="password" class="regular-text" id="mpaisa_client_secret" name="client_secret" value="<?php echo esc_attr( $client_secret ); ?>" autocomplete="off" />
						<p class="description">Stored in the WordPress database only — never committed to source control.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'Save M-PAiSA settings' ); ?>
		</form>
	</div>
	<?php
}
