<?php
/**
 * Adds "region" support for rendering regions in templates
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

class Gust_Regions {

	private $path;
	private $default_regions;

	public function __construct( $path ) {
		$this->path = $path;
		add_action( 'gust_post_activate', array( $this, 'create_default_regions' ) );
		add_filter( 'gust_main_nav', array( $this, 'render_main_nav' ) );
		add_filter( 'gust_search', array( $this, 'render_gust_search' ) );
		$this->default_regions = array( 'header', 'footer' );
	}

	/**
	 * Returns a list of region names
	 */
	public function get_regions() {
		return apply_filters( 'gust_regions', $this->default_regions );
	}


	/**
	 * Creates the initial default regions
	 */
	public function create_default_regions() {
		// check for defaults.
		foreach ( $this->default_regions as $region_key ) {
			// if the user has existing data, we should migrate it.
			$legacy_region = $this->get_legacy_region( $region_key );
			if ( $legacy_region ) {
				$region_content  = '';
				$region_safelist = '';
				$meta_content    = get_post_meta( $legacy_region->ID, '_gust_content', true );
				if ( $meta_content ) {
					$region_content = $meta_content;
				}
				$meta_safelist = get_post_meta( $legacy_region->ID, '_gust_safelist', true );
				if ( $meta_safelist ) {
					$region_safelist = $meta_safelist;
				}
				$region_data = array( 
					'content'  => $region_content,
					'safelist' => $region_safelist,
				);
				$this->update_region( $region_key, $region_data );
			}
		}
	}

	/**
	 * Returns the region content and safelist. Null, if it does not exist
	 * 
	 * @param string $slug The name of the region.
	 */
	public function get_region( $slug ) {
		$option_data = get_option( $this->get_region_id( $slug ) );
		if ( ! $option_data ) {
			return $this->get_region_defaults( $slug );
		}
		return $option_data;
	}

	/**
	 * Given a slug, will return the option ID used in the database
	 * 
	 * @param string $slug The name of the region.
	 */
	public function get_region_id( $slug ) {
		return "gust_region_{$slug}";
	}

	/**
	 * Updates or creates a region ID
	 * 
	 * @param string $slug the name of the region.
	 * @param object $region_data { content: string; safelist: string }.
	 */
	public function update_region( $slug, $region_data ) {
		$region_data['content'] = stripslashes( $region_data['content'] );
		return update_option( $this->get_region_id( $slug ), $region_data, true );
	}

	public function render_main_nav() {
		ob_start();
		wp_nav_menu(
			array(
				'theme_location' => 'primary-menu',
				'depth'          => 2,
				'fallback_cb'    => array( $this, 'main_nav_fallback' ),
			)
		);
		return ob_get_clean();
	}

	public function main_nav_fallback() {
		$args = array(
			'sort_column'  => 'menu_order, post_title',
			'menu_id'      => '',
			'menu_class'   => 'menu',
			'container'    => 'div',
			'echo'         => false,
			'link_before'  => '',
			'link_after'   => '',
			'before'       => '<ul>',
			'after'        => '</ul>',
			'item_spacing' => 'discard',
			'walker'       => '',
			'title_li'     => false,
			'depth'        => 1,
		);

		$menu = wp_list_pages( $args );

		$menu = '<div><ul>' . $menu . '</ul></div>';

		echo $menu;
	}

	public function render_gust_search() {
		return get_search_form( array( 'echo' => false ) );
	}

	/**
	 * Returns data for legacy region support
	 * 
	 * @param string $slug The region name.
	 */
	public function get_legacy_region( $slug ) {
		$args    = array(
			'name'           => $slug,
			'post_type'      => 'gust_region',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		);
		$results = get_posts( $args );
		if ( ! $results || is_wp_error( $results ) || empty( $results ) ) {
			return null;
		}
		return $results[0];
	}

	/**
	 * Returns the default data for a region
	 * 
	 * @param string $slug The region name.
	 */
	public function get_region_defaults( $slug ) {
		$accepted_regions = array( 'header', 'footer' );
		if ( ! in_array( $slug, $accepted_regions, true ) ) {
			return array(
				'content'  => '[]',
				'safelist' => '',
			);
		}
		$region_content  = file_get_contents( $this->path . 'defaults/regions/' . $slug . '.json' );
		$region_safelist = file_get_contents( $this->path . 'defaults/regions/' . $slug . 'Safelist.txt' );
		return array(
			'content'  => $region_content,
			'safelist' => $region_safelist,
		);
	}

	/**
	 * Resets a region back to using the defaults
	 * 
	 * @param string $slug The region name.
	 */
	public function reset_region( $slug ) {
		if ( ! in_array( $slug, $this->get_regions() ) ) {
			return;
		}

		delete_option( $this->get_region_id( $slug ) );
	}
}
