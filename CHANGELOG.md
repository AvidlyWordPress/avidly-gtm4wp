# Changelog

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
