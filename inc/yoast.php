<?php
/**
 * Yoast render callbacks.
 *
 * @package Avidly_GTM4WP
 */

/**
 * Modify render output: Yoast SEO breadcrumb.
 * Add custom attributes for breadcrumb links output.
 * Affects breadcrumbs added via PHP and block.
 *
 * @param string $output HTML output.
 *
 * @return string
 */
add_filter(
	'wpseo_breadcrumb_output',
	function ( $output ) {
		return avidly_gtm4wp_add_link_click_attributes( $output, 'breadcrumb', 'wpseo-breadcrumb' );
	}
);
