=== Child Navigation ===
Contributors: hildende
Tags: navigation, pages, children, sub
Requires at least: 3.2
Tested up to: 3.8
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html

Adds support for child navigation to `wp_nav_menu()`

== Description ==

This plugin adds support for displaying only child pages of the current page to wp_nav_menu()

== Installation ==
1. Upload `child-navigation` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Usage =
Set `children_only` as argument to `TRUE` in `wp_nav_menu()` to display only children of the current page

`wp_nav_menu( array ( 'children_only' => TRUE ) );`

the argument `depth` will be relative to the first child level