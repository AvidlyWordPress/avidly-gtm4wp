# Avidly Google Tag Manager
Set of base rules to complement GTM setup by pushing page meta data and user information into the dataLayer.

## How it works
Hooks into wp_head and pushed meta data into the dataLayer on pageView & click event.

### pageView events
Push set of base rules into dataLayer on every page view.

### Click events
Detect with JavaScript if element with `[data-click-event]` has been clicked and push dataLayer values:
```
'event': 'agtm4wp_click',
'wp_click_url': this.getAttribute('href'),
'wp_click_text': element innerHTML without HTML markup.
'wp_click_type': this.getAttribute('data-click-type'),
'wp_click_event': this.getAttribute('data-click-event'),
'wp_click_current': window.location.href,
'wp_click_dom': parent DOM element name if detected, supports: HEADER, FOOTER, ASIDE, SECTION
```

## Usage
Download the plugin and activate it.

## Hooks

### Manipulating base 
Datalayer properties can be manipulated via custom filters (for example via theme or features plugin). Each key is creating a new dataLayer property. 

1. 'avidly_gtm4wp_sitewide' = Create or modify sitewide properties.
```
Array (
['event']        => (string)
['wp_title']     => (string)
['wp_lang']      => (string)
['wp_loggedin']  => (bool)
['wp_userid']    => (int)
['wp_posttype']  => (string)
['wp_paged']     => (int)
)
```
2. 'avidly_gtm4wp_single' = Create or modify single post type properties.
```
Array (
['wp_poststatus'] => (string)
['wp_author']     => (string)
['wp_postdate']   => (string)
['wp_moddate']    => (string)
['wp_{tax_slug}'] => (array)
)
```
3. 'avidly_gtm4wp_url_params' = Create or modify URL parameters properties.
```
Array (
['wp_param_{param}'] => (string)
)
```

Example of use:

	/**
	 * Modify single post type aataLayer properties
	 *
	 * @param array $datalayer base properties for dataLayer.
	 * @param array $post_type where the terms will be detected.
	 *
	 * @return array $datalayer.
	 */
	add_filter(
		['avidly_gtm4wp_single',
		function( $datalayer, $post_type ) {
			// Create new custom property.
			$datalayer['custom_meta'] = 'New custom value';

			// Overwrite exsisting wp_author property for posts only.
			if ( 'post' === $post_type ) {
				$datalayer['wp_author'] = 'New custom value';
			}

			// Remove wp_poststatus base property.
			unset( $datalayer['wp_poststatus'] );

			return $datalayer;
		},
		15,
		2
	);

### Excluding
Excluded post types, taxonomies and parameters can be manipulated via custom filters (for example via theme or features plugin).

1. 'avidly_gtm4wp_exclude_post_types' = Create or modify excluded post types.
```
Array (
[0] => 'revision'
[1] => 'nav_menu_item'
[2] => 'custom_css'
[3] => 'customize_changeset'
[4] => 'oembed_cache'
[5] => 'user_request'
[6] => 'wp_block'
[7] => 'wp_template'
[8] => 'wp_template_part'
[9] => 'wp_global_styles'
[10] => 'wp_navigation'
[11] => 'polylang_mo'
[12] => 'acf-field-group'
[13] => 'acf-field'
)
```
2. 'avidly_gtm4wp_exclude_taxonomies' = Create or modify excluded taxonomies.
```
Array (
[0] => 'post_format',
[1] => 'language',
[2] => 'post_translations',
)
```
3. 'avidly_gtm4wp_exclude_params' = Create or modify excluded URL parameters. Defaults to none.

Example of use:

	/**
	 * Exclude post types properties from dataLayer.
	 *
	 * @param array $exclude set the excluded post types.
	 *
	 * @return array $exclude.
	 */
	add_filter(
		['avidly_gtm4wp_exclude_post_types',
		function( $exclude ) {
			// Add new exclusion.
			$exclude[] = 'post';

			return $exclude;
		},
		10,
		1
	);
