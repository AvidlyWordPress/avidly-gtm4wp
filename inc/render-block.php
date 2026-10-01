<?php
/**
 * Block render callbacks.
 *
 * @package Avidly_GTM4WP
 */

/**
 * Modify render output for block with links.
 * Add custom attributes to block output that has links.
 *
 * @param string $block_content HTML output.
 * @param array  $block attributes.
 *
 * @return string
 */
add_filter(
	'render_block',
	function ( $block_content, $block ) {
		$supported_blocks = array(
			'core/button'    => array(
				'type'  => 'button',
				'event' => 'wp-block-button',
			),
			'core/file'      => array(
				'type'  => 'file',
				'event' => 'wp-block-file',
			),
			'core/read-more' => array(
				'type'  => 'post-link',
				'event' => 'wp-read-more',
			),
		);

		if ( empty( $block['blockName'] ) || ! isset( $supported_blocks[ $block['blockName'] ] ) ) {
			return $block_content;
		}

		$config = $supported_blocks[ $block['blockName'] ];

		return avidly_gtm4wp_add_link_click_attributes( $block_content, $config['type'], $config['event'] );
	},
	10,
	2
);

/**
 * Add click-tracking attributes to anchors that do not already have them.
 *
 * @param string $html HTML fragment.
 * @param string $type data-click-type value.
 * @param string $event data-click-event value.
 * @return string
 */
function avidly_gtm4wp_add_link_click_attributes( $html, $type, $event ) {
	if ( ! is_string( $html ) || '' === $html || ! is_string( $type ) || ! is_string( $event ) ) {
		return is_string( $html ) ? $html : '';
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		if ( false !== stripos( $html, 'data-click-type=' ) || false !== stripos( $html, 'data-click-event=' ) ) {
			return $html;
		}

		$updated = preg_replace(
			'/(<a\b[^><]*)>/i',
			'$1 data-click-type="' . esc_attr( $type ) . '" data-click-event="' . esc_attr( $event ) . '">',
			$html
		);

		return is_string( $updated ) ? $updated : $html;
	}

	$processor = new WP_HTML_Tag_Processor( $html );

	// The tag name is queried in upper case because WordPress 6.2 and 6.3 match it case-sensitively.
	while ( $processor->next_tag( array( 'tag_name' => 'A' ) ) ) {
		if ( null === $processor->get_attribute( 'data-click-type' ) ) {
			$processor->set_attribute( 'data-click-type', $type );
		}

		if ( null === $processor->get_attribute( 'data-click-event' ) ) {
			$processor->set_attribute( 'data-click-event', $event );
		}
	}

	return $processor->get_updated_html();
}
