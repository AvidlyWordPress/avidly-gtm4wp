<?php
/**
 * Block render callbacks.
 *
 * @package Avidly_GA4
 */

/**
 * Modify render output for block with links.
 * Add custom attributes to block output that has links.
 *
 * @param string $block_content HTML output.
 * @param array  $block attributes.
 *
 * @return $block_content
 */
add_filter(
	'render_block',
	function( $block_content, $block ) {
		
		// Block that has links to track.
		$supported_blocks = array(
			'core/button' => array(
				'type' => 'button',
				'event' => 'wp-block-button'
			),
			'core/file' => array(
				'type' => 'file',
				'event' => 'wp-block-file'
			),
			'core/read-more' => array(
				'type' => 'post-link',
				'event' => 'wp-read-more'
			),
		);

		// Return if we are not rendering button block.
		if ( ! array_key_exists( $block['blockName'], $supported_blocks ) ) {
			return $block_content;
		}

		foreach ( $supported_blocks as $key => $val ) {
			if ( $key === $block['blockName'] ) {
				// Add custom attributes: data-click-type & data-click-event.
				$block_content = preg_replace( '/(<a\b[^><]*)>/i', '$1 data-click-type="' . $val['type']. '" data-click-event="' . $val['event'] . '">', $block_content );
			}
		}

		// Return the content.
		return $block_content;
	},
	10,
	2
);
