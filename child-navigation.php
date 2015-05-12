<?php
/*
 * Plugin Name: Child Navigation 
 * Description: Adds support for child navigations to wp_nav_menu()
 * Version: 1.1.1
 * Author: Dennis Hildenbrand
 * Author URI: http://dennishildenbrand.com
 */


/**
 * Modifies wp_nav_menu() output.
 *
 * Adds support for displaying children of current page or starting menu from
 * specific level in current root line.
 *
 * @author Dennis Hildenbrand
 */
class Child_Navigation {

	/**
	 * menu items
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * wp_nav_menu() arguments
	 *
	 * @var object
	 */
	protected $args;

	/**
	 * @var array
	 */
	protected $itemStorage = array();

	/**
	 * @var int
	 */
	protected $filterCall = 0;

	/**
	 * @var int
	 */
	protected $cssCall = 0;

	/**
	 * Makes action parameters to class properties.
	 *
	 * @param array $items
	 * @param object $args
	 */
	protected function init( $items, $args ) {
		$this->items       = $items;
		$this->args        = $args;
		$this->args->depth = $this->args->depth ? $this->args->depth : 999;
	}

	/**
	 * Filters sub navigation with new arguments.
	 *
	 * Adds children_only, children_show_start_level and children_start_level as
	 * arguments to wp_nav_menu().
	 *
	 * @param array $items
	 * @param object $args
	 *
	 * @return array
	 */
	public function filter_submenu( $items, $args ) {
		$this->filterCall ++;
		$this->init( $items, $args );

		if ( $this->args->children_start_level ) {
			$this->args->children_only = true;
		}

		if ( ! $this->args->children_only ) {
			return $this->items;
		}

		$startItem = $this->get_start_item();

		if ( ! $startItem ) {
			return array();
		}


		$depth = $this->args->depth;
		if ( $this->args->children_show_start_level ) {
			$depth --;
		}
		$children = array();
		if ( $depth ) {
			$children = $this->find_sub_items( $startItem->ID, $depth );
		}

		foreach ( $this->items as $key => $item ) {
			if ( ! in_array( $item->ID, $children ) ) {
				unset( $this->items[ $key ] );
			}
		}

		if ( $this->args->children_show_start_level ) {
			$startItem->menu_item_parent = '';
			$this->items[]               = $startItem;
		}

		$this->itemStorage[ $this->filterCall ] = $this->items;

		return $this->items;
	}

	/**
	 * Finds children of given page id.
	 *
	 * @param int $id
	 * @param int $maxDepth
	 * @param int $depth
	 *
	 * @return array
	 */
	protected function find_sub_items( $id, $maxDepth = 999, $depth = 0 ) {
		$depth ++;
		$ids = wp_filter_object_list( $this->items, array( 'menu_item_parent' => $id ), 'and', 'ID' );
		if ( $depth < $maxDepth ) {
			foreach ( $ids as $id ) {
				$ids = array_merge( $ids, $this->find_sub_items( $id, $this->items, $maxDepth, $depth ) );
			}
		}

		return $ids;
	}

	/**
	 * Finds depth of given menu item.
	 *
	 * @param array $items
	 * @param object $current
	 * @param int $depth
	 *
	 * @return int
	 */
	protected function get_current_depth( $items, $current, $depth = 0 ) {
		$depth ++;
		$found = false;
		foreach ( $items as $item ) {
			if ( $item->ID == $current->menu_item_parent ) {
				$found   = true;
				$current = $item;
				break;
			}
		}
		if ( $found ) {
			return $this->get_current_depth( $items, $current, $depth );
		}

		return $depth;
	}

	/**
	 * Gets start menu item for start level argument.
	 *
	 * @return array|null
	 */
	protected function get_start_item() {
		$startItem = array_shift( wp_filter_object_list( $this->items, array( 'current' => true ) ) );

		if ( ! $this->args->children_start_level ) {
			return $startItem;
		}

		$currentDepth = $this->get_current_depth( $this->items, $startItem );
		$steps        = $currentDepth - $this->args->children_start_level;
		$startItem    = $this->go_back_in_rootline( $startItem, $steps );

		return $startItem;
	}

	/**
	 * Get ancestor for given menu item.
	 *
	 * Goes back given amount of steps to find ancestor of given menu item.
	 *
	 * @param object $startItem
	 * @param int $steps
	 *
	 * @return array|null
	 */
	protected function go_back_in_rootline( $startItem, $steps ) {
		if ( $steps < 0 ) {
			return null;
		}

		while ( $steps ) {
			$startItem = array_shift( wp_filter_object_list( $this->items, array( 'ID' => $startItem->menu_item_parent ) ) );
			$steps --;
		}

		return $startItem;
	}

	/**
	 * Fixes automatically given class.
	 *
	 * Gets css classes for each item and checks if they are still correct.
	 *
	 * @param array $items
	 * @param object $args
	 *
	 * @return array
	 */
	public function clean_css_classes( $items, $args ) {
		$this->cssCall ++;
		$this->init( $items, $args );
		$itemStorage = $this->itemStorage[ $this->cssCall ];

		if ( ! $itemStorage ) {
			return $this->items;
		}

		$pattern = '(?<=class=").*?(menu-item).*?(?=")';
		preg_match_all( '#' . $pattern . '#is', $this->items, $matches );
		$menuItemClasses = $matches[0];

		foreach ( $itemStorage as $item ) {
			$this->check_single_item_for_css( $item->ID, $itemStorage, $menuItemClasses );
		}

		return $this->items;
	}

	/**
	 * Removes wrong classes.
	 *
	 * Checks if menu item has children and removes css class "menu-item-has-children" if not.
	 *
	 * @param int $itemID
	 * @param array $items
	 * @param array $menuItemClasses
	 */
	protected function check_single_item_for_css( $itemID, $items, $menuItemClasses ) {
		if ( $this->has_children( $itemID, $items ) ) {
			return;
		}
		foreach ( $menuItemClasses as $menuItem ) {
			if ( strpos( $menuItem, 'menu-item-' . $itemID ) !== false ) {
				$tmpClasses  = str_replace( 'menu-item-has-children', '', $menuItem );
				$this->items = str_replace( $menuItem, $tmpClasses, $this->items );
			}
		}
	}

	/**
	 * Checks if menu item has children.
	 *
	 * @param int $itemID
	 * @param array $items
	 *
	 * @return bool
	 */
	protected function has_children( $itemID, $items ) {
		foreach ( $items as $item ) {
			if ( $item->menu_item_parent == $itemID ) {
				return true;
			}
		}

		return false;
	}

}

$child_navigation = new Child_Navigation();
add_filter( 'wp_nav_menu_objects', array(
	$child_navigation,
	'filter_submenu'
), 10, 2 );
add_filter( 'wp_nav_menu_items', array(
	$child_navigation,
	'clean_css_classes'
), 10, 2 );