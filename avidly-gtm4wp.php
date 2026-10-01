<?php
/**
 * Plugin Name: Avidly Google Tag Manager
 * Description: Set of base rules to complement GTM setup by pushing page meta data and user information into the dataLayer.
 * Version: 1.4.1
 * Author: Avidly
 * Author URI: http://avidly.fi
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Avidly_GTM4WP
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

define( 'AVIDLY_GTM4WP_VERSION', '1.4.1' );

// Require files.
require_once __DIR__ . '/inc/render-block.php';
require_once __DIR__ . '/inc/yoast.php';
require_once __DIR__ . '/inc/menu.php';

/**
 * Hook functionality.
 */
add_action( 'init', 'avidly_gtm4wp_textdomain' );
add_action( 'wp_enqueue_scripts', 'avidly_gtm4wp_enqueue_script', 10 );
add_action( 'wp_head', 'avidly_gtm4wp_datalayer_push', -9999 );

/**
 * Plugin translations.
 *
 * The text domain is loaded so the plugin header can be translated.
 * Analytics values stay in English on purpose.
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
		AVIDLY_GTM4WP_VERSION,
		true
	);
}

/**
 * Hook GTM scripts to HTML head.
 *
 * @return void
 */
function avidly_gtm4wp_datalayer_push() {
	$sitewide  = avidly_gtm4wp_filter_array( 'avidly_gtm4wp_sitewide', array() );
	$url_param = avidly_gtm4wp_filter_array( 'avidly_gtm4wp_url_params', array() );

	$exclude_post_types = avidly_gtm4wp_filter_array( 'avidly_gtm4wp_exclude_post_types', array() );
	$current_post_type  = get_post_type();
	$current_post_type  = ( is_string( $current_post_type ) && ! in_array( $current_post_type, $exclude_post_types, true ) ) ? $current_post_type : '';

	$single = array();
	if ( '' !== $current_post_type && is_singular( $current_post_type ) ) {
		$single = avidly_gtm4wp_filter_array( 'avidly_gtm4wp_single', array(), $current_post_type );
	}

	$data = avidly_gtm4wp_sanitize_datalayer(
		array_merge( $sitewide, $single, $url_param )
	);

	$json = avidly_gtm4wp_encode( $data );

	if ( ! is_string( $json ) ) {
		// Keep the page-view event so GTM triggers still fire, even without the properties.
		$json = avidly_gtm4wp_encode( array( 'event' => 'agtm4wp_pageview' ) );
	}

	if ( ! is_string( $json ) ) {
		return;
	}
	?>
	<script data-cfasync="false" data-pagespeed-no-defer="" type="text/javascript">
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push(<?php echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Encoded with wp_json_encode() and JSON_HEX_* flags. ?>);
	</script>
	<?php
}

/**
 * Encode the dataLayer payload so it is safe inside a script element.
 *
 * @param array $data Payload.
 * @return string|false
 */
function avidly_gtm4wp_encode( $data ) {
	if ( array() === $data ) {
		return '{}';
	}

	// JSON_UNESCAPED_UNICODE keeps letters such as "ä" readable. PHP still escapes
	// U+2028 and U+2029 unless JSON_UNESCAPED_LINE_TERMINATORS is added, so they
	// cannot act as line breaks inside the script element.
	return wp_json_encode(
		$data,
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE
	);
}

/**
 * Run a dataLayer filter and keep only array results.
 *
 * Extra arguments are passed on to apply_filters().
 *
 * @param string $hook Filter name.
 * @param array  $fallback Value to use when a callback returns a non-array.
 * @return array
 */
function avidly_gtm4wp_filter_array( $hook, $fallback ) {
	$result = call_user_func_array( 'apply_filters', func_get_args() );

	return is_array( $result ) ? $result : $fallback;
}

/**
 * Keep dataLayer values that wp_json_encode() can represent.
 *
 * Strings, numbers, booleans and arrays are kept. Other types are dropped
 * so one unexpected value cannot break the whole script.
 *
 * @param mixed $data Filter payload.
 * @return array
 */
function avidly_gtm4wp_sanitize_datalayer( $data ) {
	if ( ! is_array( $data ) ) {
		return array();
	}

	$clean = array();

	foreach ( $data as $key => $value ) {
		if ( ! is_string( $key ) && ! is_int( $key ) ) {
			continue;
		}

		$sanitized = avidly_gtm4wp_sanitize_datalayer_value( $value );
		if ( null === $sanitized ) {
			continue;
		}

		$clean[ (string) $key ] = $sanitized;
	}

	return $clean;
}

/**
 * Normalize one dataLayer value.
 *
 * @param mixed $value Raw value.
 * @return mixed|null
 */
function avidly_gtm4wp_sanitize_datalayer_value( $value ) {
	if ( is_string( $value ) || is_int( $value ) || is_bool( $value ) ) {
		return $value;
	}

	if ( is_float( $value ) ) {
		return is_finite( $value ) ? $value : null;
	}

	if ( ! is_array( $value ) ) {
		return null;
	}

	$clean   = array();
	$is_list = avidly_gtm4wp_array_is_list( $value );

	foreach ( $value as $key => $item ) {
		$sanitized = avidly_gtm4wp_sanitize_datalayer_value( $item );
		if ( null === $sanitized ) {
			continue;
		}

		if ( $is_list ) {
			$clean[] = $sanitized;
		} else {
			$clean[ (string) $key ] = $sanitized;
		}
	}

	return $clean;
}

/**
 * Whether an array is a list with consecutive keys starting at 0.
 *
 * @param array $value Array to inspect.
 * @return bool
 */
function avidly_gtm4wp_array_is_list( $value ) {
	if ( function_exists( 'array_is_list' ) ) {
		return array_is_list( $value );
	}

	$expected = 0;

	foreach ( array_keys( $value ) as $key ) {
		if ( $key !== $expected ) {
			return false;
		}

		++$expected;
	}

	return true;
}

/**
 * Define sitewide datalayer properties.
 *
 * @param array $datalayer base properties.
 */
add_filter(
	'avidly_gtm4wp_sitewide',
	function ( $datalayer ) {
		if ( is_archive() ) {
			$post_type      = get_post_type_object( get_post_type() );
			$post_type_name = ( is_object( $post_type ) ) ? $post_type->labels->name : 'undefined';
			$title          = 'Archives: ' . $post_type_name;
		} elseif ( is_search() ) {
			$title = 'Search results';
		} else {
			$title = get_the_title();
		}

		$datalayer = array(
			'event'       => 'agtm4wp_pageview',
			'wp_title'    => $title,
			'wp_lang'     => get_locale(),
			'wp_loggedin' => is_user_logged_in(),
		);

		if ( 0 !== get_current_user_id() ) {
			$datalayer['wp_userid'] = get_current_user_id();
		}

		if ( is_archive() || is_single() || is_page() ) {
			$datalayer['wp_posttype'] = get_post_type();
		}

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
 * @param array  $datalayer base properties.
 * @param string $post_type to detect related terms.
 *
 * @return array
 */
add_filter(
	'avidly_gtm4wp_single',
	function ( $datalayer, $post_type = '' ) {
		if ( ! is_array( $datalayer ) ) {
			$datalayer = array();
		}

		if ( ! is_string( $post_type ) || '' === $post_type ) {
			return $datalayer;
		}

		global $post;

		if ( ! ( $post instanceof WP_Post ) ) {
			return $datalayer;
		}

		if ( is_single() || is_page() ) {
			$datalayer['wp_poststatus'] = get_post_status();
			$datalayer['wp_author']     = get_the_author_meta( 'display_name', $post->post_author );

			if ( 'publish' === get_post_status() || 'private' === get_post_status() ) {
				$datalayer['wp_postdate'] = get_the_date( 'd.m.Y' );
				$datalayer['wp_moddate']  = get_the_modified_date( 'd.m.Y' );
			}
		}

		$taxonomies  = get_object_taxonomies( $post_type );
		$exclude_tax = avidly_gtm4wp_filter_array( 'avidly_gtm4wp_exclude_taxonomies', array() );

		if ( $taxonomies && ! is_wp_error( $taxonomies ) ) {
			foreach ( $taxonomies as $tax ) {
				if ( in_array( $tax, $exclude_tax, true ) ) {
					continue;
				}

				$terms_obj = get_the_terms( $post->ID, $tax );
				$terms     = ( $terms_obj && ! is_wp_error( $terms_obj ) ) ? wp_list_pluck( $terms_obj, 'name' ) : array();

				if ( $terms ) {
					$datalayer[ 'wp_' . $tax ] = array_values( $terms );
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
		if ( ! is_array( $datalayer ) ) {
			$datalayer = array();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading campaign parameters, not processing a form.
		$params = wp_unslash( $_GET );

		if ( ! is_array( $params ) ) {
			return $datalayer;
		}

		$exclude_params = avidly_gtm4wp_filter_array( 'avidly_gtm4wp_exclude_params', array() );

		foreach ( $params as $key => $val ) {
			if ( ! is_string( $key ) || in_array( $key, $exclude_params, true ) ) {
				continue;
			}

			// Arrays are skipped. Version 1.4.0 could not represent them and emitted a warning.
			if ( ! is_scalar( $val ) || ! $val ) {
				continue;
			}

			$datalayer[ 'wp_param_' . $key ] = (string) $val;
		}

		return $datalayer;
	},
	10,
	1
);

/**
 * Exclude post types.
 *
 * @param array $exclude the excluded post types.
 */
add_filter(
	'avidly_gtm4wp_exclude_post_types',
	function ( $exclude ) {
		if ( ! is_array( $exclude ) ) {
			$exclude = array();
		}

		return array_values(
			array_unique(
				array_merge(
					$exclude,
					array(
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
					)
				)
			)
		);
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
		if ( ! is_array( $exclude ) ) {
			$exclude = array();
		}

		return array_values(
			array_unique(
				array_merge(
					$exclude,
					array(
						'post_format',
						'language',
						'post_translations',
					)
				)
			)
		);
	},
	10,
	1
);
