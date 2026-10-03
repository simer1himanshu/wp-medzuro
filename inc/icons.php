<?php
/**
 * Inline SVG icons.
 *
 * The Shopify theme used the Adorn icon font (assets/adorn-icons.woff2,
 * classes like `at at-search-l`). Inline SVG drops that ~40KB font request and
 * matches the stroke style the medzuro-* sections already used for their own
 * inline icons.
 *
 * @package Medzuro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Echo an icon.
 *
 * @param string $name One of the keys in medzuro_icon_paths().
 * @param int    $size Pixel size of the square viewport.
 */
function medzuro_icon( $name, $size = 22 ) {
	$paths = medzuro_icon_paths();

	if ( ! isset( $paths[ $name ] ) ) {
		return;
	}

	printf(
		'<svg class="mz-icon mz-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		wp_kses( $paths[ $name ], medzuro_svg_allowed_tags() )
	);
}

/**
 * Icon path data.
 *
 * @return array<string,string>
 */
function medzuro_icon_paths() {
	return array(
		'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'user'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'heart'  => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1L12 21.2l7.7-7.7 1.1-1a5.5 5.5 0 0 0 0-7.9Z"/>',
		'bag'    => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
		'shield' => '<path d="M12 3 5 6v5.5c0 4.3 2.9 7.4 7 8.9 4.1-1.5 7-4.6 7-8.9V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
		'gift'   => '<path d="M20 12v8H4v-8"/><path d="M2 7h20v5H2z"/><path d="M12 7v13"/><path d="M12 7H8.5A2.5 2.5 0 1 1 11 4.5c0 1.8 1 2.5 1 2.5z"/><path d="M12 7h3.5A2.5 2.5 0 1 0 13 4.5c0 1.8-1 2.5-1 2.5z"/>',
		'tag'    => '<path d="M3 12V4h8l9 9-8 8-9-9Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
		'truck'  => '<path d="M3 7h10v8H3z"/><path d="M13 10h4l3 3.2V15h-7z"/><circle cx="7" cy="17.3" r="1.7"/><circle cx="16.7" cy="17.3" r="1.7"/>',
		'pin'    => '<path d="M12 21s7-5.2 7-12a7 7 0 0 0-14 0c0 6.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.4"/>',
		'headset'=> '<path d="M4 13a8 8 0 0 1 16 0"/><path d="M4 13v3a2 2 0 0 0 2 2h1v-5H6a2 2 0 0 0-2 2Z"/><path d="M20 13v3a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2Z"/>',
		'lock'   => '<rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
		'close'  => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
		'chev'   => '<path d="m6 9 6 6 6-6"/>',
		'store'  => '<path d="M3 9.5 4.5 4h15L21 9.5"/><path d="M3 9.5a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 12v8h14v-8"/><path d="M10 20v-5h4v5"/>',
		'check'  => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'box'    => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
		'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'phone'  => '<rect x="7" y="2.5" width="10" height="19" rx="2"/><path d="M11 18.5h2"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6 8.5 7 8.5-7"/>',
		'edit'   => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13.5 6.5 4 4"/>',
		'external' => '<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/>',
		'share'  => '<circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.2 10.8 7.6-4.5"/><path d="m8.2 13.2 7.6 4.5"/>',
	);
}

/**
 * Tags permitted inside an icon, for wp_kses().
 *
 * @return array
 */
function medzuro_svg_allowed_tags() {
	$attrs = array(
		'd' => true, 'cx' => true, 'cy' => true, 'r' => true,
		'x' => true, 'y' => true, 'width' => true, 'height' => true,
		'rx' => true, 'points' => true, 'x1' => true, 'x2' => true,
		'y1' => true, 'y2' => true, 'fill' => true,
	);

	return array(
		'path'    => $attrs,
		'circle'  => $attrs,
		'rect'    => $attrs,
		'line'    => $attrs,
		'polygon' => $attrs,
	);
}
