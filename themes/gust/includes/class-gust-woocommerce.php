<?php
/**
 * Adds WooCommerce support
 * 
 * @package Gust
 */

/**
 * Adds hooks / filters etc to display WC correctly
 */
class Gust_WooCommerce {

	/**
	 * An instance of Gust_Admin
	 * 
	 * @var Gust_Admin admin
	 */
	private $admin;

	/**
	 * Constructor
	 * 
	 * @param Gust_Admin $admin An instance of Gust_Admin.
	 */
	public function __construct( $admin ) {
		$this->admin = $admin;

		$this->setup_hooks();
	}

	/**
	 * Sets up the hooks & filters
	 */
	public function setup_hooks() {
		// wrapper.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', array( $this, 'wrapper_start' ), 10 );
		add_action( 'woocommerce_after_main_content', array( $this, 'wrapper_end' ), 10 );

		// prod category pages.
		add_action( 'woocommerce_before_shop_loop', array( $this, 'open_content_width_container' ), 1 );
		add_action( 'woocommerce_after_shop_loop', array( $this, 'close_div' ), 99999 );

		// empty cat pages.
		add_action( 'woocommerce_no_products_found', array( $this, 'open_content_width_container' ), 1 );
		add_action( 'woocommerce_no_products_found', array( $this, 'close_div' ), 99999 );

		// product pages.
		// add the title.
		add_action( 'woocommerce_before_single_product', array( $this, 'product_page_title' ), 11 );
		// content width for the content.
		add_action( 'woocommerce_before_single_product', array( $this, 'open_content_width_container' ), 12 );
		add_action( 'woocommerce_after_single_product', array( $this, 'close_div' ), 99999 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'meta_placeholder' ), 40 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'open_prose' ), 19 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'close_div' ), 29 );

		// remove breacrumb.
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

		// account pages
		add_filter( 'woocommerce_account_menu_item_classes', array( $this, 'account_menu_item_classes' ) );

		// fns that require WC to be active.
		if ( $this->admin->is_wc_active() ) {
			add_filter( 'gust_theme_safelist', array( $this, 'theme_safelist' ) );
			add_filter( 'gust_content_class_names', array( $this, 'remove_content_prose' ) );
		}
	}

	/**
	 * Adds our WC classes to the theme safelist
	 * 
	 * @param array $safelist An array of strings.
	 */
	public function theme_safelist( $safelist ) {
		$safelist[] = 'woocommerce';
		$safelist[] = 'woocommerce-products-header';
		$safelist[] = 'page-title';
		$safelist[] = 'term-description';
		$safelist[] = 'woocommerce-loop-product__title';
		$safelist[] = 'woocommerce-message';
		$safelist[] = 'added_to_cart';
		return $safelist;
	}

	/**
	 * Wrapper before the content
	 */
	public function wrapper_start() {
		echo '<main id="site-content">';
	}

	/**
	 * Wrapper closing tag
	 */
	public function wrapper_end() {
		echo '</main>';
	}

	/**
	 * Opens the content width container
	 */
	public function open_content_width_container() {
		echo '<div class="max-w-screen-lg px-4 mx-auto mb-8 md:mb-16">';
	}

	/**
	 * Closes a div
	 */
	public function close_div() {
		echo '</div>';
	}

	/**
	 * Displays the product title
	 */
	public function product_page_title() {
		global $product;
		echo '<div class="py-8 mb-8 bg-gray-100 md:py-16 md:mb-16">';
		echo '   <div class="max-w-screen-lg px-4 mx-auto">';
		get_template_part( 'template-parts/content/post-categories', null, array( 'tax' => 'product_cat' ) );
		the_title( '<h1 class="text-4xl font-extrabold md:text-7xl">', '</h1>' );
		if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) {
			echo '<p class="text-xs font-semibold text-gray-500 uppercase">';
			$sku = $product->get_sku();
			echo __( 'SKU: ', 'gust' );
			echo ( $sku ) ? esc_html( $sku ) : esc_html__( 'N/A', 'gust' );
			echo '</p>';
		}
		get_template_part( 'template-parts/content/post-tags', null, array( 'term' => 'product_tag' ) );
		echo '   </div>';
		echo '</div>';
	}

	/**
	 * Runs default meta hooks
	 */
	public function meta_placeholder() {
		echo '<div class="product_meta">';
		do_action( 'woocommerce_product_meta_start' );
		do_action( 'woocommerce_product_meta_end' );
		echo '</div>';
	}

	/**
	 * Opens a Prose block
	 */
	public function open_prose() {
		echo '<div class="prose">';
	}

	/**
	 * Removes the prose class from the content
	 * 
	 * @param string $class_names The class names passed to the wrapper.
	 */
	public function remove_content_prose( $class_names ) {
		if ( is_cart() || is_checkout() || is_account_page() ) {
			$class_names = str_replace( 'prose', '', $class_names );
		}
		return $class_names;
	}

	/**
	 * Adds classes to the account menu items
	 * 
	 * @param array $class_names The list of class names.
	 */
	public function account_menu_item_classes( $class_names ) {
		$class_names[] = 'mb-2';

		if ( in_array( 'is-active', $class_names, true ) ) {
			$class_names[] = 'underline';
		}
		return $class_names;
	}
}
