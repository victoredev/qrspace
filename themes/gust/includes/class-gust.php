<?php
/**
 * The main Gust class
 */

class Gust {

	protected static $instance = null;

	public $builder;
	public $components;
	public $path;
	public $url;
	public $upload_path;
	public $upload_url;
	public $api_url;
	public $admin;
	public $updater;
	public $api;
	public $regions;
	public $version;
	public $posts;
	public $menu;
	public $is_free = false;
	public $ui_script_url;
	public $script_version;
	public $wc;
	public $forms;

	public function __construct() {
		$theme                 = wp_get_theme( 'gust' );
		$this->version         = $theme->get( 'Version' );
		$this->api_url         = 'https://api.getgust.com/api';
		$this->path            = trailingslashit( $theme->get_template_directory() );
		$this->url             = trailingslashit( $theme->get_template_directory_uri() );
		$this->ui_script_url   = $this->url . 'ui/dist';
		$script_version_suffix = $this->is_free ? 'free' : 'premium';
		$this->script_version  = $this->version . '-' . $script_version_suffix;

		require_once $this->path . 'includes/class-gust-utilities.php';
		require_once $this->path . 'includes/class-gust-builder.php';
		require_once $this->path . 'includes/class-gust-components.php';
		require_once $this->path . 'includes/class-gust-admin.php';
		require_once $this->path . 'includes/class-gust-updater.php';
		require_once $this->path . 'includes/class-gust-api.php';
		require_once $this->path . 'includes/class-gust-regions.php';
		require_once $this->path . 'includes/class-gust-menu.php';
		require_once $this->path . 'includes/class-gust-posts.php';
		require_once $this->path . 'includes/class-gust-comment-walker.php';
		require_once $this->path . 'includes/class-gust-woocommerce.php';
		require_once $this->path . 'includes/class-gust-forms.php';

		$upload_dir        = wp_get_upload_dir();
		$this->upload_path = trailingslashit( $upload_dir['basedir'] ) . 'gust/';
		$this->upload_url  = trailingslashit( $upload_dir['baseurl'] ) . 'gust/';
		$this->components  = new Gust_Components( $this->path );
		$this->api         = new Gust_Api( $this->api_url, $this->is_free );
		$this->builder     = new Gust_Builder( $this->url, $this->script_version );
		$this->regions     = new Gust_Regions( $this->path );
		$this->admin       = new Gust_Admin( $this->components, $this->url, $this->path, $this->upload_url, $this->upload_path, $this->api, $this->regions, $this->is_free, $this->script_version, $this->ui_script_url );

		//$this->updater     = new Gust_Updater( $this->api, $this->version, $this->admin );

		$this->posts       = new Gust_Posts();
		$this->menu        = new Gust_Menu();
		$this->wc          = new Gust_WooCommerce( $this->admin );
		$this->forms       = new Gust_Forms( $this->admin, $this->url, $this->script_version );

		add_action( 'after_setup_theme', array( $this, 'setup' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_core_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_core_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'gust_region', array( $this, 'render_gust_region' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_filter( 'gust_builder_fonts', array( $this, 'register_builder_fonts' ) );

		do_action( 'gust_init', $this );    
	}

	public static function get_instance() {
		// create an object
		null === self::$instance and self::$instance = new self();

		// return the object
		return self::$instance;
	}

	public function register_core_scripts() {
		wp_register_script( 'gust-compiler', $this->ui_script_url . '/compiler.js', array(), $this->script_version, true );
		wp_register_script( 'gust-worker', $this->ui_script_url . '/wp.js', array( 'gust-compiler' ), $this->script_version );
	}

	public function enqueue_scripts() {
		foreach ( $this->get_fonts() as $font ) {
			wp_enqueue_style( 'gust-' . $font['id'], $font['url'], array(), $this->version );
		}
		if ( $this->admin->get_option( 'dev_mode' ) && current_user_can( 'administrator' ) ) {
			wp_enqueue_style( 'site-gust-dev', $this->url . 'assets/css/develop.css', array(), $this->script_version );
			wp_register_script( 'gust-dev', $this->url . 'assets/js/develop.js', array( 'jquery', 'gust-worker' ), $this->script_version, true );
			wp_localize_script(
				'gust-dev', 
				'Gust',
				array(
					'config'            => $this->admin->get_config(),
					'css'               => $this->admin->get_tw_css(),
					'safelist'          => implode( ' ', $this->admin->get_class_safelist() ),
					'tailwindVersion'   => $this->admin->get_option( 'tw_version' ),
				)
			);
			wp_enqueue_script( 'gust-dev' );
		} else {
			$prod_version = $this->admin->get_option( 'prod_css_version' );
			wp_enqueue_style( 'site-gust', $this->upload_url . 'prod.css', array(), $prod_version );
		}
		wp_enqueue_script( 'gust-nav', $this->url . 'assets/js/menu.js', array( 'jquery' ), $this->version, true );
	}

	public function render_content( $gust_content, $content = '' ) {
		try {
			$decoded = json_decode( $gust_content, true );
			if ( ! $decoded ) {
				return $content;
			}
			$wrapped_content = array(
				'tag'        => 'div',
				'instanceId' => 'ROOT',
				'classNames' => 'gust',
				'children'   => $decoded,
			);
			return $this->builder->render_tag( $wrapped_content );
		} catch ( Exception $e ) {
			error_log( $e );
			return $content;
		}
	}

	/**
	 * Returns the rendered content of a region
	 * 
	 * @param string $slug The region name.
	 * @param string $content The default content.
	 */
	public function get_region_content( $slug, $content = '' ) {
		$gust_content = $this->admin->get_gust_region_content( $slug );
		if ( ! $gust_content ) {
			return $content;
		}
		return $this->render_content( $gust_content, $content );
	}

	/**
	 * Returns the rendered content of a post
	 * 
	 * @param string $post_id The post ID.
	 * @param string $content The default content.
	 */
	public function get_post_content( $post_id = 0, $content = '', $echo = true ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		// get the content.
		$gust_content = $this->admin->get_gust_content( $post_id );
		if ( ! $gust_content ) {
			if ( $echo ) {
				echo $content;
			} else {
				return $content;
			}
		}
		$rendered_content = $this->render_content( $gust_content, $content );
		if ( $echo ) {
			echo $rendered_content;
		} else {
			return $rendered_content;
		}
	}

	/**
	 * Given a region key, will echo the content for that region
	 * 
	 * @param string $region_key The name of the region.
	 */
	public function render_gust_region( $region_key = '' ) {
		if ( ! $region_key ) {
			return;
		}
		$content = $this->get_region_content( $region_key );
		echo apply_filters( 'gust_region_content', $content, $region_key );
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	public function setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Gust, use a find and replace
		 * to change 'gust' to the name of your theme in all the template files.
		 */
		// load_theme_textdomain('gust', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'gust_post_image', 625, 523 );
		add_image_size( 'gust_post_thumbnail', 260, 260 );

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary-menu' => esc_html__( 'Primary', 'gust' ),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'gust_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add WooCommerce support.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	public function widgets_init() {
		register_sidebar(
			array(
				'name'          => esc_html__( 'Primary Sidebar', 'gust' ),
				'id'            => 'sidebar-primary',
				'description'   => esc_html__( 'Add widgets here.', 'gust' ),
				'before_widget' => '<section id="%1$s" class="widget prose %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}

	/**
	 * Returns a list of fonts
	 */
	public function get_fonts() {
		$fonts = array(
			array(
				'id'  => 'inter',
				'url' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap',
			),
		);
		return apply_filters( 'gust_fonts', $fonts );
	}

	/**
	 * Registers Gust fonts in the builder
	 *
	 * @param array $fonts A list of fonts.
	 */
	public function register_builder_fonts( $fonts ) {
		return array_merge( $fonts, $this->get_fonts() );
	}
}
