<?php
/**
 * Plugin Name: Avidly Google Tag Manager
 * Description: Set of base rules to complement GTM setup by pushing page meta data and user information into the dataLayer.
 * Version: 1.0
 * Author: Avidly
 * Author URI: http://avidly.fi
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Avidly_GA4
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Plugin translations.
 */
add_action(
	'init',
	function() {
		load_plugin_textdomain( 'avidly-gtm4wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

/**
 * Hook functionality.
 */
add_action( 'wp_head', 'avidly_gtm4wp_datalayer_push', -999 );

/**
 * Hook GTM scripts to HTML head.
 * This will handle the sitewide and single values to dataLayer.
 *
 * @return void
 */
function avidly_gtm4wp_datalayer_push() {
	// Create sitewide values.
	$sitewide = apply_filters( 'avidly_gtm4wp_sitewide', array() );

	// Define post types to be excluded.
	$exclude_post_types = apply_filters( 'avidly_gtm4wp_exclude_post_types', array() );

	// Get current post type if it's not excluded and pull values for datalayer.
	$current_post_type = ( ! in_array( get_post_type(), $exclude_post_types, true ) ) ? get_post_type() : '';
	$single            = ( is_singular( $current_post_type ) ) ? apply_filters( 'avidly_gtm4wp_single', array(), $current_post_type ) : '';

	// Get URL parameters.
	$url_param = apply_filters( 'avidly_gtm4wp_url_params', array() );
	?>

		<!-- GTM Sitewide & Single -->
		<script data-cfasync="false" data-pagespeed-no-defer="" type="text/javascript">
			// Create dataLayer.
			window.dataLayer = window.dataLayer || [];

			var dataLayer_site = {
				<?php
				// Output values from $sitewide filter.
				if ( $sitewide ) {
					foreach ( $sitewide as $key => $val ) {
						echo sprintf(
							"'%s': '%s', \n",
							esc_html( $key ),
							esc_html( $val )
						);
					}
				}

				// Output single from $single filter.
				if ( $single ) {
					foreach ( $single as $key => $val ) {
						echo sprintf(
							"'%s': '%s', \n",
							esc_html( $key ),
							esc_html( $val )
						);
					}
				}

				// Output URL params from $url_param filter.
				if ( $url_param ) {
					foreach ( $url_param as $key => $val ) {
						echo sprintf(
							"'%s': '%s', \n",
							esc_html( $key ),
							esc_html( $val )
						);
					}
				}
				?>
			}
			dataLayer.push( dataLayer_site );
		</script>
		<!-- End GTM Sitewide -->

	<?php
}


/**
 * Define sitewide datalayer tracking.
 *
 * @param array $datalayer set the tracking values.
 */
add_filter(
	'avidly_gtm4wp_sitewide',
	function ( $datalayer ) {

		// Detect title from content type.
		if ( is_archive() ) {
			$title = 'Archives';
		} elseif ( is_search() ) {
			$title = 'Search results';
		} else {
			$title = get_the_title();
		}

		// These values should allways been set.
		$datalayer = array(
			'wp_title'      => $title,
			'wp_lang'       => get_locale(),
			'wp_loggedin'   => ( is_user_logged_in() ) ? 'true' : 'false',
			'wp_userid'     => get_current_user_id(), // 0 if user is not logged in.
		);

		// Display post type in archives and single post types/pages.
		if ( is_archive() || is_single() || is_page() ) {
			$datalayer['wp_posttype'] = get_post_type();
		}

		return $datalayer;
	},
	10,
	1
);

/**
 * Define datalayer tracking for single post type.
 *
 * @param array $datalayer set the tracking values.
 * @param array $post_type where the terms will be detected.
 *
 * @return $datalayer
 */
add_filter(
	'avidly_gtm4wp_single',
	function ( $datalayer, $post_type = '' ) {
		// Return empty if post type is not found.
		if ( ! $post_type ) {
			return;
		}

		// Set global post so values can be retrieved outside a loop.
		global $post;

		// Display post types & date information only in single post types and pages.
		if ( is_single() || is_page() ) {
			$datalayer['wp_poststatus'] = get_post_status();

			// Get post dates only for published content (password, public and private).
			if ( 'publish' === get_post_status() || 'private' === get_post_status() ) {
				$datalayer['wp_postdate'] = get_the_date( 'd.m.Y' );
				$datalayer['wp_moddate']  = get_the_modified_date( 'd.m.Y' );
			}
		}

		// Add author info only for posts.
		if ( 'post' === $post_type ) {
			$datalayer['wp_author'] = get_the_author_meta( 'display_name', $post->post_author );
		}

		// Get all available taxonomies for post type.
		$taxonomies = get_object_taxonomies( $post_type );

		// Define taxonomies to be exclude.
		$exclude_tax = apply_filters( 'avidly_gtm4wp_exclude_taxonomies', array() );

		// Loop thru available taxonomies and create key & value if terms are found.
		if ( $taxonomies && ! is_wp_error( $taxonomies ) ) {
			foreach ( $taxonomies as $tax ) {
				// Skip excluded taxonomies no need to hadle those.
				if ( in_array( $tax, $exclude_tax, true ) ) {
					continue;
				}

				// Get terms related to post.
				$terms_obj = get_the_terms( $post->ID, $tax );

				// Create comma separated list of terms.
				$terms = ( $terms_obj && ! is_wp_error( $terms_obj ) ) ? join( ', ', wp_list_pluck( $terms_obj, 'name' ) ) : 'false';

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
 * @param array $datalayer set the tracking values.
 */
add_filter(
	'avidly_gtm4wp_url_params',
	function ( $datalayer ) {

		// Set global post so values can be retrieved outside a loop.
		global $wp;

		// Get current URL parameters.
		$params = $_GET;

		$exclude_params = apply_filters( 'avidly_gtm4wp_exclude_params', array() );

		if ( $params && ! is_wp_error( $params ) ) {
			foreach ( $params as $key => $val ) {
				// Skip excluded taxonomies no need to hadle those.
				if ( in_array( $key, $exclude_params, true ) ) {
					continue;
				}

				// Add to dataLayer if value is found.
				if ( $val ) {
					$datalayer[ 'wp_param_' . $key ] = $val;
				}
			}
		}

		return $datalayer;
	},
	10,
	1
);

/**
 * Exclude post types.
 *
 * @param array $exclude set the excluded post types.
 */
add_filter(
	'avidly_gtm4wp_exclude_post_types',
	function ( $exclude ) {

		// These values should always been ignored from dataLayer.
		$excude = array(
			'attachment',
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
 * @param array $exclude set the excluded taxonomies.
 */
add_filter(
	'avidly_gtm4wp_exclude_taxonomies',
	function ( $exclude ) {

		// These values should always been ignored from dataLayer.
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
 * @param array $exclude set the excluded URL parameters.
 */
add_filter(
	'avidly_gtm4wp_exclude_params',
	function ( $exclude ) {

		// These values should always been ignored from dataLayer.
		$excude = array(
			'post_type',
		);

		return $excude;
	},
	10,
	1
);
