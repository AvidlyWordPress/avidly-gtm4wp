<?php
/**
 * Menu render callbacks.
 *
 * @package Avidly_GA4
 */

add_action( 'nav_menu_link_attributes', 'avidly_gtm4wp_menu_link_attributes', 10, 4 );

/**
 * Add custom attribute to all menu items for click detection.
 *
 * @param array    $atts The HTML attributes applied to the menu item's <a> element, empty strings are ignored.
 * @param WP_Post  $item The current menu item object.
 * @param stdClass $args An object of wp_nav_menu() arguments.
 * @param int      $depth Depth of menu item. Used for padding.
 *
 * @link https://developer.wordpress.org/reference/hooks/nav_menu_link_attributes/
 */
function avidly_gtm4wp_menu_link_attributes( $atts, $item, $args, $depth ) {
	$atts['data-click-type']  = 'menu';
	$atts['data-click-event'] = $args->theme_location;

	return $atts;
}
