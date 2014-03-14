=== Child Navigation ===
Contributors: hildende
Tags: navigation, pages, children, sub
Requires at least: 3.2
Tested up to: 3.8
Stable tag: 1.1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html

Adds support for child navigation to `wp_nav_menu()`

== Description ==

This plugin adds support for displaying child pages of the current page to wp_nav_menu()

== Installation ==
1. Upload `child-navigation` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Usage =
Set `children_only` as argument to `TRUE` in `wp_nav_menu()` to display only children of the current page

`wp_nav_menu( array ( 'children_only' => TRUE ) );`

Set `children_start_level` as argument with the wanted start level as value to show pages in the current root line starting with given level.

`wp_nav_menu( array ( 'children_start_level' => 2 ) );`

Set `children_show_start_level` as argument to `TRUE` to show the start level (or the current page). if this is not set only child pages are shown.

`wp_nav_menu( array ( 'children_show_start_level' => TRUE ) );`

The argument `depth` will be relative to the first displayed level.


== Changelog ==

= 1.1.0 =
* Added support for start level
* Added possibility to show current page