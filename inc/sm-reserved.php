<?php
/**
 * Shop Manager dashboard: show open reservations next to Total Revenue.
 *
 * Total Revenue stays "completed/paid only" (see medzuro_sm_stats_before() in
 * inc/order-flow.php). This adds a separate "Open reservations" strip under the
 * stat cards: how many pickup orders are Reserved / Reservation confirmed /
 * Ready for pickup and what they are worth if collected. It is independent of
 * the selected period, because an open reservation is money still to collect
 * whatever day it was placed.
 *
 * Server: adds a `reserved` object to /shop-manager/v1/dashboard-stats.
 * Browser: a small script (attached to jQuery, which that page loads) reads
 * that field from the dashboard's own request and draws the strip.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Open reservations: count and value per status.
 *
 * @return array{count:int,total:float,currency:string,by_status:array}
 */
function medzuro_open_reservations() {
	$labels = array(
		'reserved'     => __( 'Reserved', 'medzuro' ),
		'confirmed'    => __( 'Confirmed', 'medzuro' ),
		'ready-pickup' => __( 'Ready for pickup', 'medzuro' ),
	);
	$out    = array(
		'count'     => 0,
		'total'     => 0.0,
		'currency'  => get_woocommerce_currency(),
		'by_status' => array(),
	);
	foreach ( $labels as $slug => $label ) {
		$orders = wc_get_orders(
			array(
				'status' => $slug,
				'limit'  => 500,
				'return' => 'objects',
			)
		);
		$sum = 0.0;
		foreach ( $orders as $order ) {
			$sum += (float) $order->get_total();
		}
		$out['by_status'][] = array(
			'label' => $label,
			'count' => count( $orders ),
			'total' => round( $sum, 2 ),
		);
		$out['count'] += count( $orders );
		$out['total'] += $sum;
	}
	$out['total'] = round( $out['total'], 2 );
	return $out;
}

/**
 * Add the reservations to the dashboard response.
 *
 * @param WP_REST_Response|mixed $result  Result.
 * @param WP_REST_Server         $server  Server.
 * @param WP_REST_Request        $request Request.
 * @return mixed
 */
function medzuro_sm_add_reserved( $result, $server, $request ) {
	if ( '/shop-manager/v1/dashboard-stats' !== $request->get_route() || ! ( $result instanceof WP_REST_Response ) || 200 !== $result->get_status() ) {
		return $result;
	}
	$data = $result->get_data();
	if ( is_array( $data ) ) {
		$data['reserved'] = medzuro_open_reservations();
		$result->set_data( $data );
	}
	return $result;
}
add_filter( 'rest_post_dispatch', 'medzuro_sm_add_reserved', 10, 3 );

/**
 * Attach the strip script to jQuery on the Shop Manager dashboard page only.
 * jQuery loads in <head> before the dashboard app, so the script can see the
 * app's very first request.
 */
function medzuro_sm_reserved_script() {
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH );
	if ( 0 !== strpos( untrailingslashit( $path ), '/sm-dashboard' ) || ! wp_script_is( 'jquery', 'registered' ) ) {
		return;
	}
	$js = <<<'JS'
(function () {
	var RE = /shop-manager(\/|%2F)v1(\/|%2F)dashboard-stats/i, last = null, timer = null;
	function money(c, n) { return c + ' $' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
	function row() {
		var els = document.querySelectorAll('div,span,p'), el = null, i;
		for (i = 0; i < els.length; i++) { if (els[i].children.length < 2 && els[i].textContent.trim() === 'Total Revenue') { el = els[i]; break; } }
		if (!el) { return null; }
		var col = el.closest('.ant-col');
		return col ? col.parentElement : null;
	}
	function render(r) {
		if (!r) { return; }
		last = r;
		var host = row();
		if (!host) { return; }
		var box = document.getElementById('mz-reserved-strip');
		if (!box) {
			box = document.createElement('div');
			box.id = 'mz-reserved-strip';
			box.style.cssText = 'margin:16px 0;padding:14px 20px;border:1px solid #f0d9a8;background:#fffbf0;border-radius:8px;display:flex;flex-wrap:wrap;align-items:center;gap:8px 28px;font-size:14px;color:#3d3d3d;';
			host.parentElement.insertBefore(box, host.nextSibling);
		}
		var parts = r.by_status.map(function (s) { return s.label + ' ' + s.count + ' (' + money(r.currency, s.total) + ')'; }).join(' · ');
		box.innerHTML = '<div><div style="font-size:13px;color:#8a6d1d;">Open reservations <span style="opacity:.8">(not counted as revenue until Completed)</span></div>' +
			'<div style="font-size:22px;font-weight:600;color:#1f1f1f;">' + money(r.currency, r.total) + ' <span style="font-size:14px;font-weight:400;color:#6b6b6b;">' + r.count + (r.count === 1 ? ' order' : ' orders') + '</span></div></div>' +
			'<div style="font-size:12.5px;color:#6b6b6b;">' + parts + '</div>';
	}
	function handle(url, json) { if (RE.test(url) && json && json.reserved) { render(json.reserved); } }
	var of = window.fetch;
	if (of) {
		window.fetch = function () {
			var a = arguments[0], u = typeof a === 'string' ? a : (a && a.url) || '', p = of.apply(this, arguments);
			if (RE.test(u)) { p.then(function (res) { res.clone().json().then(function (j) { handle(u, j); }).catch(function () {}); }).catch(function () {}); }
			return p;
		};
	}
	var xo = XMLHttpRequest.prototype.open;
	XMLHttpRequest.prototype.open = function (m, u) {
		var self = this;
		if (RE.test(String(u))) { this.addEventListener('load', function () { try { handle(String(u), JSON.parse(self.responseText)); } catch (e) {} }); }
		return xo.apply(this, arguments);
	};
	// The dashboard re-renders its cards; put the strip back if it was removed.
	new MutationObserver(function () {
		if (last && !document.getElementById('mz-reserved-strip')) { clearTimeout(timer); timer = setTimeout(function () { render(last); }, 150); }
	}).observe(document.documentElement, { childList: true, subtree: true });
	window.mzSmReserved = { render: render };
})();
JS;
	wp_add_inline_script( 'jquery', $js, 'after' );
}
add_action( 'wp_print_scripts', 'medzuro_sm_reserved_script', 1 );
