# Changelog

## 1.1.2
Change user ID to INT value for dataLayer (return NULL id user is not logged in).

## 1.1.1
Change pageview and click events names for dataLayer

## 1.1.0
Add support to detect wp_nav_menu items click via dataLayer.

### Added
- Webpack & Laravel Mix compile tools for JS
- Add `data-click-type` and `data-click-event` attributes for all menu item links.
- Create JavaScript listener for click events & send preperties to dataLayer.


## 1.0
Initial release, send basic page view infos to dataLayer.
