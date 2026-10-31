<?php
/**
 * Staff view of pickup orders by store (wp-admin → WooCommerce → Orders).
 *
 * - A summary box above the orders list: how many pickup orders are still
 *   waiting at each store (Reserved, Reservation confirmed, Ready for pickup,
 *   Deposit paid). Each count links to the list filtered to that store.
 * - A "Pickup store" column.
 * - A "All pickup stores" filter dropdown.
 *
 * Works with HPOS (admin.php?page=wc-orders) and the legacy posts screen.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Statuses that mean "not collected yet".
 *
 * @return string[] Slugs without the wc- prefix.
 */
function medzuro_pickup_pending_statuses() {
	return (array) apply_filters( 'medzuro_pickup_pending_statuses', array( 'reserved', 'confirmed', 'ready-pickup', 'deposit-paid' ) );
}

/**
 * Store id chosen for a pickup order, or '' when none was saved.
 *
 * @param WC_Order $order Order.
 * @return string
 */
function medzuro_order_pickup_store_id( $order ) {
	return (string) $order->get_meta( '_mz_pickup_store' );
}

/**
 * Pending pickup orders per store: store id => count.
 *
 * @return int[]
 */
function medzuro_pickup_pending_counts() {
	$counts = array();
	foreach ( array_keys( medzuro_pickup_stores() ) as $id ) {
		$counts[ $id ] = 0;
	}

	$orders = wc_get_orders(
		array(
			'status' => medzuro_pickup_pending_statuses(),
			'limit'  => -1,
			'type'   => 'shop_order',
		)
	);

	foreach ( $orders as $order ) {
		if ( ! medzuro_order_is_pickup( $order ) ) {
			continue;
		}
		$id = medzuro_order_pickup_store_id( $order );
		if ( '' === $id || ! isset( $counts[ $id ] ) ) {
			$id = '_none';
		}
		$counts[ $id ] = isset( $counts[ $id ] ) ? $counts[ $id ] + 1 : 1;
	}

	return $counts;
}

/**
 * Whether the current admin screen is the orders list.
 *
 * @return bool
 */
function medzuro_is_orders_list_screen() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return false;
	}
	if ( 'woocommerce_page_wc-orders' === $screen->id ) {
		return empty( $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	return 'edit-shop_order' === $screen->id;
}

/**
 * Orders list URL filtered to one pickup store.
 *
 * @param string $id Store id.
 * @return string
 */
function medzuro_orders_list_url_for_store( $id ) {
	$base = ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() )
		? admin_url( 'admin.php?page=wc-orders' )
		: admin_url( 'edit.php?post_type=shop_order' );

	return add_query_arg( 'mz_pickup_store', rawurlencode( $id ), $base );
}

/**
 * Summary box above the orders list.
 */
function medzuro_pickup_pending_notice() {
	if ( ! medzuro_is_orders_list_screen() || ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}

	$counts  = medzuro_pickup_pending_counts();
	$stores  = medzuro_pickup_stores();
	$total   = array_sum( $counts );
	$current = isset( $_GET['mz_pickup_store'] ) ? sanitize_text_field( wp_unslash( $_GET['mz_pickup_store'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="notice notice-info mz-pickup-summary" style="padding:10px 12px;">
		<p style="margin:0 0 8px;"><strong>
			<?php
			/* translators: %d: number of orders */
			printf( esc_html__( 'Pickups waiting to be collected: %d', 'medzuro' ), (int) $total );
			?>
		</strong> <span style="color:#646970;"><?php esc_html_e( '(Reserved, Reservation confirmed, Ready for pickup, Deposit paid)', 'medzuro' ); ?></span></p>
		<p style="margin:0;display:flex;flex-wrap:wrap;gap:6px;">
			<?php foreach ( $counts as $id => $n ) : ?>
				<?php
				$label = '_none' === $id ? __( 'No store saved', 'medzuro' ) : ( isset( $stores[ $id ]['name'] ) ? $stores[ $id ]['name'] : $id );
				$style = 'display:inline-block;padding:3px 10px;border:1px solid #c3c4c7;border-radius:999px;text-decoration:none;background:' . ( $current === $id ? '#2271b1;color:#fff' : '#fff' ) . ';';
				?>
				<?php if ( '_none' === $id ) : ?>
					<span style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $label ); ?>: <strong><?php echo (int) $n; ?></strong></span>
				<?php else : ?>
					<a href="<?php echo esc_url( medzuro_orders_list_url_for_store( $id ) ); ?>" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $label ); ?>: <strong><?php echo (int) $n; ?></strong></a>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( $current ) : ?>
				<a href="<?php echo esc_url( remove_query_arg( 'mz_pickup_store' ) ); ?>" style="padding:3px 6px;"><?php esc_html_e( 'Show all stores', 'medzuro' ); ?></a>
			<?php endif; ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'medzuro_pickup_pending_notice' );

/* -------------------------------------------------------------------------
 * "Pickup store" column
 * ---------------------------------------------------------------------- */

/**
 * Add the column after "Status".
 *
 * @param array $columns Columns.
 * @return array
 */
function medzuro_pickup_store_column( $columns ) {
	$out = array();
	foreach ( $columns as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'order_status' === $key ) {
			$out['mz_pickup_store'] = __( 'Pickup store', 'medzuro' );
		}
	}
	if ( ! isset( $out['mz_pickup_store'] ) ) {
		$out['mz_pickup_store'] = __( 'Pickup store', 'medzuro' );
	}
	return $out;
}
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'medzuro_pickup_store_column' );
add_filter( 'manage_edit-shop_order_columns', 'medzuro_pickup_store_column' );

/**
 * Print the column.
 *
 * @param string       $column Column id.
 * @param WC_Order|int $order  Order (HPOS) or post id (legacy).
 */
function medzuro_pickup_store_column_value( $column, $order ) {
	if ( 'mz_pickup_store' !== $column ) {
		return;
	}
	$order = $order instanceof WC_Order ? $order : wc_get_order( $order );
	if ( ! $order || ! medzuro_order_is_pickup( $order ) ) {
		echo '<span aria-hidden="true">&ndash;</span>';
		return;
	}
	$id    = medzuro_order_pickup_store_id( $order );
	$store = medzuro_order_pickup_store( $order );
	if ( $id ) {
		echo '<a href="' . esc_url( medzuro_orders_list_url_for_store( $id ) ) . '">' . esc_html( $store['name'] ) . '</a>';
	} else {
		echo esc_html( $store['name'] );
	}
}
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'medzuro_pickup_store_column_value', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'medzuro_pickup_store_column_value', 10, 2 );

/* -------------------------------------------------------------------------
 * Filter by store
 * ---------------------------------------------------------------------- */

/**
 * Store dropdown next to the other list filters.
 */
function medzuro_pickup_store_filter_dropdown() {
	$current = isset( $_GET['mz_pickup_store'] ) ? sanitize_text_field( wp_unslash( $_GET['mz_pickup_store'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	echo '<select name="mz_pickup_store"><option value="">' . esc_html__( 'All pickup stores', 'medzuro' ) . '</option>';
	foreach ( medzuro_pickup_stores() as $id => $store ) {
		echo '<option value="' . esc_attr( $id ) . '"' . selected( $current, $id, false ) . '>' . esc_html( $store['name'] ) . '</option>';
	}
	echo '</select>';
}
add_action( 'woocommerce_order_list_table_restrict_manage_orders', 'medzuro_pickup_store_filter_dropdown' );
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'shop_order' === $post_type ) {
			medzuro_pickup_store_filter_dropdown();
		}
	}
);

/**
 * Meta query for the chosen store, or null.
 *
 * @return array|null
 */
function medzuro_pickup_store_filter_meta_query() {
	$id = isset( $_GET['mz_pickup_store'] ) ? sanitize_text_field( wp_unslash( $_GET['mz_pickup_store'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( '' === $id ) {
		return null;
	}
	return array(
		'key'   => '_mz_pickup_store',
		'value' => $id,
	);
}

/**
 * HPOS list.
 *
 * @param array $args Query args.
 * @return array
 */
function medzuro_pickup_store_filter_hpos( $args ) {
	$clause = medzuro_pickup_store_filter_meta_query();
	if ( $clause ) {
		$args['meta_query']   = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'][] = $clause;
	}
	return $args;
}
add_filter( 'woocommerce_order_list_table_prepare_items_query_args', 'medzuro_pickup_store_filter_hpos' );

/**
 * Legacy posts list.
 *
 * @param WP_Query $query Query.
 */
function medzuro_pickup_store_filter_legacy( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'shop_order' !== $query->get( 'post_type' ) ) {
		return;
	}
	$clause = medzuro_pickup_store_filter_meta_query();
	if ( $clause ) {
		$meta   = (array) $query->get( 'meta_query' );
		$meta[] = $clause;
		$query->set( 'meta_query', $meta );
	}
}
add_action( 'pre_get_posts', 'medzuro_pickup_store_filter_legacy' );
