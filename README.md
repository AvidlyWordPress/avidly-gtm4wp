# Avidly Google Tag Manager
Set of base rules to complement GTM setup by pushing page meta data and user information into the dataLayer.

## How it works
Hooks into wp_head and pushed meta data into the dataLayer.

## Usage
Download the plugin and activate it.

## Hooks
Datalayer values can be manipulated via custom filters for example via theme or features plugin.

1. 'avidly_gtm4wp_sitewide' = Create / modify sitewide values.
2. 'avidly_gtm4wp_single' = Create / modify single post type / page values.
3. 'avidly_gtm4wp_url_params' = Create / modify URL parameters.

Example:

	/**
	* Modify single post type / page datalayers values.
	*
	* @param array $datalayer base values for datalayer.
	* @param array $post_type where the terms will be detected.
	*
	* @return $datalayer.
	*/
	add_filter(
		'avidly_gtm4wp_single',
		function( $datalayer, $post_type ) {
			// Create new custom value.
			$datalayer['custom_meta'] = 'New custom value';

			// Overwrite exsisting value.
			if ( 'post' === $post_type ) {
				$datalayer['wp_author'] = 'Overwrite author';
			}

			// Remove default base value.
			unset( $datalayer['wp_poststatus'] );

			return $datalayer;
		},
		15,
		2
	);