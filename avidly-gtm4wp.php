<?php
/**
 * Plugin Name: Avidly Google Tag Manager
 * Description: Set of base rules to complement GTM setup by pushing page meta data and user information into the dataLayer.
 * Version: 1.3.0
 * Author: Avidly
 * Author URI: http://avidly.fi
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Avidly_GA4
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Hook functionality.
 */
add_action( 'init', 'avidly_gtm4wp_textdomain' );
add_action( 'wp_enqueue_scripts', 'avidly_gtm4wp_enqueue_script', 10 );
add_action( 'wp_head', 'avidly_gtm4wp_datalayer_push', -9999 );
add_action( 'nav_menu_link_attributes', 'avidly_gtm4wp_menu_link_attributes', 10, 4 );

/**
 * Plugin translations.
 */
function avidly_gtm4wp_textdomain() {
	load_plugin_textdomain( 'avidly-gtm4wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

/**
 * Enqueue scripts.
 *
 * @return void
 */
function avidly_gtm4wp_enqueue_script() {
	wp_enqueue_script(
		'avidly-gtm4wp',
		plugin_dir_url( __FILE__ ) . 'assets/dist/js/index.js',
		array(),
		'1.3.0',
		true
	);
}


/**
 * Hook GTM scripts to HTML head.
 *
 * @return void
 */
function avidly_gtm4wp_datalayer_push() {
	// Create sitewide properties for datalayer.
	$sitewide = apply_filters( 'avidly_gtm4wp_sitewide', array() );

	// Define post types to be excluded.
	$exclude_post_types = apply_filters( 'avidly_gtm4wp_exclude_post_types', array() );

	// Get current post type if it's not excluded and create properties for datalayer.
	$current_post_type = ( ! in_array( get_post_type(), $exclude_post_types, true ) ) ? get_post_type() : '';
	$single            = ( is_singular( $current_post_type ) ) ? apply_filters( 'avidly_gtm4wp_single', array(), $current_post_type ) : '';

	// Get URL parameters.
	$url_param = apply_filters( 'avidly_gtm4wp_url_params', array() );
	?>

		<script data-cfasync="false" data-pagespeed-no-defer="" type="text/javascript">
			// Create dataLayer.
			window.dataLayer = window.dataLayer || [];

			var dataLayer_site = {
				<?php
				// Output properties from $sitewide filter.
				if ( $sitewide ) {
					foreach ( $sitewide as $key => $val ) {
						echo sprintf(
							"'%s': %s, \n",
							esc_html( $key ),
							avidly_gtm4wp_esc_value( $val ) // phpcs:ignore
						);
					}
				}

				// Output properties from $single filter.
				if ( $single ) {
					foreach ( $single as $key => $val ) {
						echo sprintf(
							"'%s': %s, \n",
							esc_html( $key ),
							avidly_gtm4wp_esc_value( $val ) // phpcs:ignore
						);
					}
				}

				// Output properties from $url_param filter.
				if ( $url_param ) {
					foreach ( $url_param as $key => $val ) {
						echo sprintf(
							"'%s': %s, \n",
							esc_html( $key ),
							avidly_gtm4wp_esc_value( $val ) // phpcs:ignore
						);
					}
				}
				?>
			}
			dataLayer.push( dataLayer_site );
		</script>

	<?php
}

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


/**
 * Define sitewide datalayer properties.
 *
 * @param array $datalayer base properties.
 */
add_filter(
	'avidly_gtm4wp_sitewide',
	function ( $datalayer ) {

		// Detect title from content type.
		if ( is_archive() ) {
			$post_type      = get_post_type_object( get_post_type() );
			$post_type_name = ( is_object( $post_type ) ) ? $post_type->labels->name : 'undefined';
			$title          = 'Archives: ' . $post_type_name;
		} elseif ( is_search() ) {
			$title = 'Search results';
		} else {
			$title = get_the_title();
		}

		// Default properties.
		$datalayer = array(
			'event'       => 'agtm4wp_pageview',
			'wp_title'    => $title,
			'wp_lang'     => get_locale(),
			'wp_loggedin' => is_user_logged_in(),
		);

		// Detect loggend in users.
		if ( 0 !== get_current_user_id() ) {
			$datalayer['wp_userid'] = get_current_user_id();
		}

		// Set property for archives and single post types.
		if ( is_archive() || is_single() || is_page() ) {
			$datalayer['wp_posttype'] = get_post_type();
		}

		// Set property for paged views.
		if ( is_paged() ) {
			$datalayer['wp_paged'] = get_query_var( 'paged' );
		}

		return $datalayer;
	},
	10,
	1
);

/**
 * Define datalayer tracking for single post type.
 *
 * @param array $datalayer base properties.
 * @param array $post_type to detect related terms.
 *
 * @return $datalayer
 */
add_filter(
	'avidly_gtm4wp_single',
	function ( $datalayer, $post_type = '' ) {
		// Return if post type is not set.
		if ( ! $post_type ) {
			return;
		}

		// Set global post so post meta can be retrieved outside a loop.
		global $post;

		// Set properties for single post types and pages only.
		if ( is_single() || is_page() ) {
			$datalayer['wp_poststatus'] = get_post_status();
			$datalayer['wp_author']     = get_the_author_meta( 'display_name', $post->post_author );

			// Set properties for published content only (password, public and private).
			if ( 'publish' === get_post_status() || 'private' === get_post_status() ) {
				$datalayer['wp_postdate'] = get_the_date( 'd.m.Y' );
				$datalayer['wp_moddate']  = get_the_modified_date( 'd.m.Y' );
			}
		}

		// Get all available taxonomies for post type.
		$taxonomies = get_object_taxonomies( $post_type );

		// Define taxonomies to be exclude.
		$exclude_tax = apply_filters( 'avidly_gtm4wp_exclude_taxonomies', array() );

		// Loop available taxonomies and create property if terms are found.
		if ( $taxonomies && ! is_wp_error( $taxonomies ) ) {
			foreach ( $taxonomies as $tax ) {
				// Skip excluded taxonomies no need to handle those.
				if ( in_array( $tax, $exclude_tax, true ) ) {
					continue;
				}

				// Get terms related to post.
				$terms_obj = get_the_terms( $post->ID, $tax );

				// Create comma separated list of terms.
				$terms = ( $terms_obj && ! is_wp_error( $terms_obj ) ) ? wp_list_pluck( $terms_obj, 'name' ) : null;

				// Option: convert terms to string.
				// $terms = ( $terms_obj && ! is_wp_error( $terms_obj ) ) ? join( ', ', wp_list_pluck( $terms_obj, 'name' ) ) : null; // convert to string.

				// Create property if terms are found.
				if ( $terms ) {
					$datalayer[ 'wp_' . $tax ] = $terms;
				}
			}
		}

		return $datalayer;
	},
	10,
	2
);

/**
 * Define datalayer tracking for URL parameters.
 *
 * @param array $datalayer base properties.
 */
add_filter(
	'avidly_gtm4wp_url_params',
	function ( $datalayer ) {
		// Get current URL parameters.
		$params = $_GET; // phpcs:ignore

		$exclude_params = apply_filters( 'avidly_gtm4wp_exclude_params', array() );

		// Loop available params and create property if value is found.
		if ( $params && ! is_wp_error( $params ) ) {
			foreach ( $params as $key => $val ) {
				// Skip excluded parameters no need to handle those.
				if ( in_array( $key, $exclude_params, true ) ) {
					continue;
				}

				// Create property if value is found.
				if ( $val ) {
					$datalayer[ 'wp_param_' . esc_attr( $key ) ] = esc_html( $val );
				}
			}
		}

		return $datalayer;
	},
	10,
	1
);

/**
 * Detect what format value should be outputed for datalayer.
 *
 * @param mixed $value to detect.
 *
 * @return $value in custom format.
 */
function avidly_gtm4wp_esc_value( $value ) {
	// Modify to string format.
	if ( is_string( $value ) ) {
		return "'" . esc_html( $value ) . "'";
	}
	// Modify to boolean format.
	if ( is_bool( $value ) ) {
		return ( $value ) ? 'true' : 'false';
	}

	// Modify to array format.
	if ( is_array( $value ) ) {
		return "['" . join( "', '", $value ) . "']";
	}

	return $value;
}

/**
 * Exclude post types.
 *
 * @param array $exclude the excluded post types.
 */
add_filter(
	'avidly_gtm4wp_exclude_post_types',
	function ( $exclude ) {
		$excude = array(
			'revision',
			'nav_menu_item',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
			'wp_template',
			'wp_template_part',
			'wp_global_styles',
			'wp_navigation',
			'polylang_mo',
			'acf-field-group',
			'acf-field',
		);

		return $excude;
	},
	10,
	1
);

/**
 * Exclude taxonomies.
 *
 * @param array $exclude the excluded taxonomies.
 */
add_filter(
	'avidly_gtm4wp_exclude_taxonomies',
	function ( $exclude ) {
		$excude = array(
			'post_format',
			'language',
			'post_translations',
		);

		return $excude;
	},
	10,
	1
);

/**
 * Exclude URL parameters.
 *
 * @param array $exclude the excluded URL parameters.
 */
add_filter(
	'avidly_gtm4wp_exclude_params',
	function ( $exclude ) {
		$excude = array();

		return $excude;
	},
	10,
	1
);

/**
 * Modify render output: Button.
 * Add custom attributes for button block in output.
 *
 * @param string $block_content HTML output.
 * @param array  $block attributes.
 *
 * @return $block_content
 */
add_filter(
	'render_block',
	function( $block_content, $block ) {
		// Return if we are not rendering button block.
		if ( 'core/button' !== $block['blockName'] ) {
			return $block_content;
		}

		// Add custom attributes: data-click-type & data-click-event.
		$block_content = preg_replace( '/(<a\b[^><]*)>/i', '$1 data-click-type="button" data-click-event="wp-block-button">', $block_content );

		// Return the content.
		return $block_content;
	},
	10,
	2
);


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
