<?php
/*
 * Plugin Name: Child Navigation 
 * Description: Adds support for child navigations to wp_nav_menu()
 * Version: 1.0
 * Author: Dennis Hildenbrand
 */


class Child_Navigation {

	public function filter_submenu( $items, $args ) {
		if ( ! $args->children_only ) {
			return $items;
		}

		$currentMenuItem = array_shift ( wp_filter_object_list ( $items, array( 'current' => true ) ) );
		$children = $this->find_sub_items( $currentMenuItem->ID, $items, $args->depth );

		foreach ( $items as $key => $item ) {
			if ( ! in_array( $item->ID, $children ) ) {
				unset( $items[$key] );
			}

		}
		return $items;
	}

	protected function find_sub_items( $id, $items, $maxDepth = 999, $depth = 0 ) {
		$depth ++;
		$ids = wp_filter_object_list( $items, array( 'menu_item_parent' => $id ), 'and', 'ID' );
		if( $depth < $maxDepth ) {
			foreach ( $ids as $id ) {
				$ids = array_merge( $ids, $this->find_sub_items( $id, $items, $maxDepth, $depth ) );
			}
		}
		return $ids;
	}

}

add_filter( 'wp_nav_menu_objects', array( new Child_Navigation() ,'filter_submenu' ), 10, 2 );