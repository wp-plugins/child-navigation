=== Child Navigation ===
Contributors: hildende
Tags: navigation, pages, child, children, sub, wp_nav_menu
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=KRBU2JDQUMWP4
Requires at least: 3.2
Tested up to: 4.2
Stable tag: 1.1.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html

Adds support for child navigation to wp_nav_menu()

== Description ==

With installing this plugin you add functionality to `wp_nav_menu()` to show
only children of the current page or start with a specific navigation level.

== Installation ==

1. Upload `child-navigation` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Show only children of current page =

For showing all children of the current page set `children_only` as argument
to `TRUE` in `wp_nav_menu()`.
`<?php wp_nav_menu( array ( 'children_only' => TRUE ) ); ?>`

= Show all items starting at specific navigation level =

For showing all pages in the current root line, starting at a specific
navigation level, set `children_start_level` as argument with the wanted
start level as value.
`<?php wp_nav_menu( array ( 'children_start_level' => 2 ) ); ?>`

= Show current page/start level in child navigation =

With `children_show_start_level` set to `TRUE` the current page is shown in
navigation if `children_only` is set or starts with `children_start_level`,
if `children_start_level` is set.
`<?php
wp_nav_menu( array (
	'children_show_start_level' => TRUE,
	// use either
	'children_only' => TRUE,
	// or
	'children_start_level' => 2,
) );
?>`
The argument `depth` will be relative to the first displayed level.

== Changelog ==

= 1.1.1 =
* Tested up to WordPress 4.0
* Improved code style following WordPress Coding Guidlines

= 1.1.0 =
* Added support for start level
* Added possibility to show current page