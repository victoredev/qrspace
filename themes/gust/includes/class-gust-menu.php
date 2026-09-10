<?php
/**
 * Adds main menu functionality
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

class Gust_Menu {

	public function __construct() {
		add_filter( 'gust_theme_safelist', array( $this, 'theme_safelist' ), 10 );
		add_filter( 'nav_menu_css_class', array( $this, 'item_class' ) );
		add_filter( 'page_css_class', array( $this, 'item_class' ) );
		add_filter( 'nav_menu_link_attributes', array( $this, 'link_attributes' ), 10, 2 );
		add_filter( 'page_menu_link_attributes', array( $this, 'link_attributes' ), 10, 2 );
		add_filter( 'nav_menu_submenu_css_class', array( $this, 'sub_menu_class' ), 10 );
	}

	/**
	 * Adds the necessary CSS class names to the safelist
	 * 
	 * @param array $classnames An array of class names
	 */
	public function theme_safelist( $classnames ) {
		$classnames = $this->item_class( $classnames );
		$classnames = $this->sub_menu_class( $classnames );

		// get link class names.
		$item = (object) array( 'current' => false );
		$attrs = $this->link_attributes( array(), $item );
		$classnames[] = $attrs['class'];

		$item = (object) array( 'current' => true );
		$attrs = $this->link_attributes( array(), $item );
		$classnames[] = $attrs['class'];
		return $classnames;
	}

	/**
	 * Returns CSS class names for each menu item
	 * 
	 * @param array $classnames An array of class names.
	 */
	public function item_class( $classnames ) {
		$classnames[] = 'block w-full md:inline-block md:w-auto text-gray-700 mb-2 md:mb-0 group relative';
		return $classnames;
	}

	/**
	 * Adds class names for each menu item link
	 * 
	 * @param object $atts An object of HTML attributes.
	 * @param object $item The link object.
	 */
	public function link_attributes( $atts, $item ) {
		$atts['class'] = 'block p-2 border-b-2 border-transparent hover:bg-gray-100 rounded';
		if ( $item->current ) {
			$atts['class'] .= ' bg-gray-100';
		}
		return $atts;
	}

	/**
	 * Adds class names to the sub menu
	 * 
	 * @param array $classnames An array of class names.
	 */
	public function sub_menu_class( $classnames ) {
		$classnames[] = 'pl-4 md:p-2 md:w-40 md:absolute md:left-0 md:top-full md:hidden md:group-hover:block md:bg-white md:border md:border-gray-100 md:rounded md:shadow';
		return $classnames;
	}
}
