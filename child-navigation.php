<?php
/*
 * Plugin Name: Child Navigation 
 * Description: Adds support for child navigations to wp_nav_menu()
 * Version: 1.1
 * Author: Dennis Hildenbrand
 */


class Child_Navigation {

	protected $items;
	protected $args;
	protected $itemStorage = array();
	protected $filterCall = 0;
	protected $cssCall = 0;

	protected function init($items, $args){
		$this->items = $items;
		$this->args = $args;
		$this->args->depth = $this->args->depth ? $this->args->depth : 999;

	}

	public function filter_submenu($items, $args) {
		$this->filterCall++;
		$this->init($items, $args);

		if (!$this->args->children_only)
			return $this->items;

		$startItem = $this->getStartItem();

		if(!$startItem)
			return array();


		$depth = $this->args->depth;
		if($this->args->children_show_start_level) {
			$depth--;
		}
		$children = array();
		if($depth) {
			$children = $this->find_sub_items( $startItem->ID, $depth);
		}

		foreach ( $this->items as $key => $item ) {
			if ( ! in_array( $item->ID, $children ) ) {
				unset( $this->items[$key] );
			}
		}

		if($this->args->children_show_start_level) {
			$startItem->menu_item_parent = '';
			$this->items[] = $startItem;
		}

		$this->itemStorage[$this->filterCall] = $this->items;
		return $this->items;
	}

	protected function find_sub_items( $id, $maxDepth = 999, $depth = 0 ) {
		$depth ++;
		$ids = wp_filter_object_list($this->items, array( 'menu_item_parent' => $id ), 'and', 'ID');
		if( $depth < $maxDepth ) {
			foreach ( $ids as $id ) {
				$ids = array_merge( $ids, $this->find_sub_items( $id, $this->items, $maxDepth, $depth ) );
			}
		}
		return $ids;
	}

	protected function getCurrentDepth($items, $current, $depth = 0) {
		$depth++;
		$found = false;
		foreach($items as $item) {
			if($item->ID == $current->menu_item_parent) {
				$found = true;
				$current = $item;
				break;
			}
		}
		if($found)
			return $this->getCurrentDepth($items, $current, $depth);
		return $depth;
	}

	protected function getStartItem(){
		$startItem = array_shift ( wp_filter_object_list ( $this->items, array( 'current' => true ) ) );

		if(!$this->args->children_start_level)
			return $startItem;

		$currentDepth = $this->getCurrentDepth($this->items, $startItem);
		$steps = $currentDepth - $this->args->children_start_level;
		$startItem = $this->goBackInRootline($startItem, $steps);

		return $startItem;
	}

	protected function goBackInRootline($startItem, $steps) {
		if($steps < 0)
			return null;

		while($steps) {
			$startItem = array_shift(wp_filter_object_list($this->items, array('ID' => $startItem->menu_item_parent)));
			$steps--;
		}

		return $startItem;
	}

	public function cleanCssClasses($items, $args) {
		$this->cssCall++;
		$this->init($items, $args);
		$itemStorage = $this->itemStorage[$this->cssCall];

		if(!$itemStorage)
			return $this->items;

		$pattern = '(?<=class=").*?(menu-item).*?(?=")';
		preg_match_all('#' . $pattern . '#is', $this->items, $matches);
		$menuItemClasses = $matches[0];

		foreach($itemStorage as $item) {
			$this->checkSingleItemForCss($item->ID, $itemStorage, $menuItemClasses);
		}

		return $this->items;
	}

	protected function checkSingleItemForCss($itemID, $items, $menuItemClasses){
		if($this->hasChildren($itemID, $items))
			return;
		foreach($menuItemClasses as $menuItem) {
			if(strpos($menuItem, 'menu-item-' . $itemID) !== false) {

				$tmpClasses = str_replace('menu-item-has-children', '', $menuItem);
				$this->items = str_replace($menuItem, $tmpClasses, $this->items);
			}
		}
	}

	protected function hasChildren($itemID, $items){
		foreach($items as $item) {
			if($item->menu_item_parent == $itemID)
				return true;
		}
		return false;
	}

}

$child_navigation = new Child_Navigation();
add_filter( 'wp_nav_menu_objects', array( $child_navigation,'filter_submenu' ), 10, 2 );
add_filter( 'wp_nav_menu_items', array( $child_navigation ,'cleanCssClasses' ), 10, 2 );
