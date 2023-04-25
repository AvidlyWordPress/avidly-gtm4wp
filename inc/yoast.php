<?php
/**
 * Yoast render callbacks.
 *
 * @package Avidly_GA4
 */

 /**
 * Modify render output: Yoast SEO Breadcrum.
 * Add custom attributes for breadcrum links output.
 * Affect breadcrums added via PHP and block.
 *
 * @param string $output HTML output.
 *
 * @return $output
 */
add_filter(
	'wpseo_breadcrumb_output',
	function ( $output ) {
		// Add custom attributes: data-click-type & data-click-event.
		$output = preg_replace( '/(<a\b[^><]*)>/i', '$1 data-click-type="breadcrumb" data-click-event="wpseo-breadcrumb">', $output );

		return $output;
	},
	10,
	2
);
