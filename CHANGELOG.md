# Changelog

## 1.4.1
Stop taxonomy terms and other dataLayer values from breaking out of the page-view script.

### Changed
- Page-view data is written with `wp_json_encode()`. Property names, event names and which parameters are copied stay the same.
- Values are no longer HTML-escaped, so `Tom &amp; Jerry` is stored as `Tom & Jerry`.
- URL parameter values are unslashed, so `O\'Brien` is stored as `O'Brien`. Empty values and the value `0` are still skipped. Array parameters are skipped.
- Exclusion filters keep values added by an earlier callback. The default exclusion lists are unchanged.
- Block and breadcrumb links are not given a second copy of an existing click attribute.
- The click listener declares its loop variable. Click properties are unchanged.

### Notes
Nothing is stored in the database, so the files can be replaced in place. Review the GTM container when upgrading from 1.0, because releases 1.1 through 1.4 already changed the payload: the page-view event is `agtm4wp_pageview`, `wp_userid` is sent only for logged-in users, taxonomy terms are an array, and `wp_loggedin` is a boolean.

## 1.4.0
Click tracking extending and improvements to file structure.

### Added
- New property for click tracking: wp_click_current (url where click was performed).
- Click tracking attributes for file block.
- Click tracking attributes for read more block.

### Changed
- Improved file structure.
- Security updates for NPM dependencies.

## 1.3.0
Extend click tracking with wp_click_url and wp_click_text properties.

## 1.2.0
Click tracking extending and improvements to user ID detection.

### Added
- Click tracking attributes for button block.
- Click tracking attributes for Yoast SEO breadcrum.

### Changed
- Do not add wp_userid property to dataLayer if user is not logged in (should return NULL in GTM).

## 1.1.1
Change pageview and click events names for dataLayer.

## 1.1.0
Add support to detect wp_nav_menu items click via dataLayer.

### Added
- Webpack & Laravel Mix compile tools for JS
- Add `data-click-type` and `data-click-event` attributes for all menu item links.
- Create JavaScript listener for click events & send preperties to dataLayer.


## 1.0
Initial release, send basic page view infos to dataLayer.
