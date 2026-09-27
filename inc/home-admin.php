<?php
/**
 * Homepage-managed testimonials and newsletter subscribers.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/** Register admin-managed homepage content. */
function medzuro_register_home_content_types() {
	register_post_type(
		'mz_testimonial',
		array(
			'labels' => array(
				'name'          => __( 'Testimonials', 'medzuro' ),
				'singular_name' => __( 'Testimonial', 'medzuro' ),
				'add_new_item'  => __( 'Add Customer Testimonial', 'medzuro' ),
				'edit_item'     => __( 'Edit Customer Testimonial', 'medzuro' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-format-quote',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'mz_subscriber',
		array(
			'labels' => array(
				'name'          => __( 'Newsletter Subscribers', 'medzuro' ),
				'singular_name' => __( 'Newsletter Subscriber', 'medzuro' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
		)
	);
}
add_action( 'init', 'medzuro_register_home_content_types' );

/** Add testimonial details to the editor. */
function medzuro_testimonial_metaboxes() {
	add_meta_box(
		'medzuro-testimonial-details',
		__( 'Customer Details', 'medzuro' ),
		'medzuro_testimonial_metabox',
		'mz_testimonial',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'medzuro_testimonial_metaboxes' );

/** Render testimonial fields. */
function medzuro_testimonial_metabox( $post ) {
	$location = get_post_meta( $post->ID, '_mz_testimonial_location', true );
	$rating   = (int) get_post_meta( $post->ID, '_mz_testimonial_rating', true );
	$rating   = $rating ?: 5;
	wp_nonce_field( 'medzuro_save_testimonial', 'medzuro_testimonial_nonce' );
	?>
	<p>
		<label for="mz-testimonial-location"><strong><?php esc_html_e( 'Customer location', 'medzuro' ); ?></strong></label><br>
		<input class="widefat" id="mz-testimonial-location" name="mz_testimonial_location" type="text"
			value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'Suva, Fiji', 'medzuro' ); ?>">
	</p>
	<p>
		<label for="mz-testimonial-rating"><strong><?php esc_html_e( 'Rating', 'medzuro' ); ?></strong></label><br>
		<select id="mz-testimonial-rating" name="mz_testimonial_rating">
			<?php for ( $stars = 5; $stars >= 1; $stars-- ) : ?>
				<option value="<?php echo esc_attr( $stars ); ?>" <?php selected( $rating, $stars ); ?>>
					<?php echo esc_html( sprintf( _n( '%d star', '%d stars', $stars, 'medzuro' ), $stars ) ); ?>
				</option>
			<?php endfor; ?>
		</select>
	</p>
	<p><?php esc_html_e( 'Use the title for the customer name, the editor for their review, and Featured Image for an optional customer photo. Order controls the display sequence.', 'medzuro' ); ?></p>
	<?php
}

/** Save testimonial fields. */
function medzuro_save_testimonial( $post_id ) {
	if ( ! isset( $_POST['medzuro_testimonial_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['medzuro_testimonial_nonce'] ) ), 'medzuro_save_testimonial' )
		|| ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		|| ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$location = isset( $_POST['mz_testimonial_location'] ) ? sanitize_text_field( wp_unslash( $_POST['mz_testimonial_location'] ) ) : '';
	$rating   = isset( $_POST['mz_testimonial_rating'] ) ? absint( $_POST['mz_testimonial_rating'] ) : 5;
	update_post_meta( $post_id, '_mz_testimonial_location', $location );
	update_post_meta( $post_id, '_mz_testimonial_rating', min( 5, max( 1, $rating ) ) );
}
add_action( 'save_post_mz_testimonial', 'medzuro_save_testimonial' );

/** Return admin testimonials, falling back to the bundled starter reviews. */
function medzuro_managed_testimonials() {
	$posts = get_posts(
		array(
			'post_type'      => 'mz_testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		)
	);

	if ( ! $posts ) {
		return medzuro_home_blocks( 'review' );
	}

	return array_map(
		function ( $post ) {
			return array(
				'rating'   => (int) ( get_post_meta( $post->ID, '_mz_testimonial_rating', true ) ?: 5 ),
				'text'     => wp_strip_all_tags( $post->post_content ),
				'author'   => get_the_title( $post ),
				'location' => (string) get_post_meta( $post->ID, '_mz_testimonial_location', true ),
				'photo'    => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: '',
				'avatar'   => 'sea',
			);
		},
		$posts
	);
}

/** Process and store a homepage newsletter subscription. */
function medzuro_newsletter_subscribe() {
	$redirect = home_url( '/' );
	$status   = 'invalid';

	if ( isset( $_POST['medzuro_newsletter_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['medzuro_newsletter_nonce'] ) ), 'medzuro_newsletter_subscribe' )
		&& empty( $_POST['company'] ) ) {
		$email = isset( $_POST['email'] ) ? strtolower( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : '';

		if ( $email && is_email( $email ) ) {
			$existing = get_posts(
				array(
					'post_type'      => 'mz_subscriber',
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_key'       => '_mz_subscriber_email',
					'meta_value'     => $email,
				)
			);

			if ( $existing ) {
				$status = 'exists';
			} else {
				$post_id = wp_insert_post(
					array(
						'post_type'   => 'mz_subscriber',
						'post_status' => 'publish',
						'post_title'  => $email,
					)
				);

				if ( ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, '_mz_subscriber_email', $email );
					wp_mail(
						get_option( 'admin_email' ),
						__( 'New Medzuro newsletter subscriber', 'medzuro' ),
						sprintf( __( 'New subscriber: %s', 'medzuro' ), $email )
					);
					$status = 'success';
				}
			}
		}
	}

	wp_safe_redirect( add_query_arg( 'newsletter', $status, $redirect ) . '#mz-newsletter' );
	exit;
}
add_action( 'admin_post_nopriv_medzuro_newsletter_subscribe', 'medzuro_newsletter_subscribe' );
add_action( 'admin_post_medzuro_newsletter_subscribe', 'medzuro_newsletter_subscribe' );
