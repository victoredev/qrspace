<?php
/**
 * Gust admin specific functionality
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

class Gust_Admin {
	const NONCE_KEY         = 'GUST_NONCE';
	const CONTENT_META_KEY  = '_gust_content';
	const USE_GUST_META_KEY = '_use_gust';
	const SAFELIST_META_KEY = '_gust_safelist';
	const PAGE_NAME         = 'gust';
	private $components;
	private $url;
	private $path;
	private $upload_path;
	private $upload_url;
	private $api;
	private $ui_script_url;
	private $regions;
	private $is_free;
	private $script_version;

	public function __construct( $components, $url, $path, $upload_url, $upload_path, $api, $regions, $is_free, $script_version, $ui_script_url ) {
		$this->components     = $components;
		$this->url            = $url;
		$this->path           = $path;
		$this->upload_path    = $upload_path;
		$this->upload_url     = $upload_url;
		$this->api            = $api;
		$this->ui_script_url  = $ui_script_url;
		$this->regions        = $regions;
		$this->is_free        = $is_free;
		$this->script_version = $script_version;

		add_action( 'after_switch_theme', array( $this, 'activate' ) );
		add_action( 'after_setup_theme', array( $this, 'migrate' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_ajax_gust_save_content', array( $this, 'save_content' ) );
		add_action( 'wp_ajax_gust_save_component', array( $this, 'save_component' ) );
		add_action( 'wp_ajax_gust_save_css', array( $this, 'ajax_save_css' ) );
		add_action( 'wp_ajax_gust_get_template', array( $this, 'get_template' ) );
		add_action( 'wp_ajax_gust_get_safelist', array( $this, 'ajax_get_site_safelist' ) );
		add_action( 'wp_ajax_gust_get_nodelist_safelist', array( $this, 'ajax_get_nodelist_safelist' ) );
		add_action( 'wp_ajax_gust_get_tw_css', array( $this, 'ajax_get_tw_css' ) );
		add_action( 'wp_ajax_gust_reset_region', array( $this, 'ajax_reset_region' ) );
		add_action( 'init', array( $this, 'add_row_actions' ) );
		add_action( 'load-post.php', array( $this, 'hook_add_meta_box' ) );
		add_action( 'load-post-new.php', array( $this, 'hook_add_meta_box' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_Gust_purchase_code', array( $this, 'recheck_purchase_code' ), 10, 2 );
		add_action( 'add_option_Gust_purchase_code', array( $this, 'check_purchase_code' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Runs on activation and sets some defaults.
	 */
	public function activate() {
		// check for the upload directory
		// and create it if need be.
		$upload_dir = $this->upload_path;
		if ( ! file_exists( $upload_dir ) ) {
			mkdir( $upload_dir, 0777, true );
		}

		if ( ! file_exists( $upload_dir . 'prod.css' ) ) {
			copy( $this->path . 'defaults/prod.css', $upload_dir . 'prod.css' );
		}

		// check if we have a default config.
		if ( ! $this->get_option( 'config' ) ) {
			$config = file_get_contents( $this->path . 'defaults/config.json' );
			$this->update_option( 'config', $config, false );
		}

		if ( ! $this->get_option( 'tw_css' ) ) {
			$tw_css = file_get_contents( $this->path . 'defaults/tw.css' );
			$this->update_option( 'tw_css', $tw_css, false );
		}

		// check if the user has a config.
		if ( ! $this->get_option( 'user_config_js' ) ) {
			$config = file_get_contents( $this->path . 'defaults/userConfig.js.txt' );
			$this->update_option( 'user_config_js', $config, false );
		}

		// maybe set the css versions.
		if ( ! $this->get_option( 'dev_css_version' ) ) {
			$this->update_option( 'dev_css_version', 1, false );
		}

		if ( ! $this->get_option( 'prod_css_version' ) ) {
			$this->update_option( 'prod_css_version', 1, true );
		}

		// default to version 3.
		if ( ! $this->get_option( 'tw_version' ) ) {
			$this->update_option( 'tw_version', '3', true );
		}

		do_action( 'gust_post_activate' );
	}

	/**
	 * Runs every time and runs any migrations
	 */
	public function migrate() {
		 // updates the old user config to the new one.
		if ( ! $this->get_option( 'user_config_js' ) ) {
			$old_user_config = $this->get_option( 'user_config' );
			if ( ! $old_user_config ) {
				return;
			}
			$user_config_js = 'module.exports = ' . $old_user_config;
			$this->update_option( 'user_config_js', $user_config_js, false );
		}
	}

	public function get_option( $option, $default = false ) {
		// fix this at version three for now.
		if ( 'tw_version' === $option ) {
			return '3';
		}
		return get_option( 'Gust_' . $option, $default );
	}

	public function update_option( $option, $value, $autoload = null ) {
		return update_option( 'Gust_' . $option, $value, $autoload );
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( $hook_suffix === 'toplevel_page_gust' ) {
			wp_register_script( 'gust-settings', $this->url . 'assets/js/settings.js', array( 'jquery', 'wp-codemirror', 'gust-worker' ), $this->script_version, true );
			wp_localize_script(
				'gust-settings',
				'Gust',
				array(
					'adminUrl'          => admin_url( 'admin-ajax.php' ),
					'config'            => $this->get_config(),
					'css'               => $this->get_tw_css(),
					'nonce'             => wp_create_nonce( self::NONCE_KEY ),
					'safelist'          => implode( ' ', $this->get_class_safelist() ),
					'tailwindVersion'   => $this->get_option( 'tw_version' ),
				)
			);
			wp_enqueue_script( 'gust-settings' );
		}
		if ( $hook_suffix === 'toplevel_page_gust' ) {
			// check that we have a post ID
			if ( ( ! isset( $_REQUEST['post'] ) || ! $_REQUEST['post'] ) && ( ! isset( $_REQUEST['region'] ) || ! $_REQUEST['region'] ) ) {
				return;
			}
			wp_enqueue_media();
			$post_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : 0;
			$region  = isset( $_REQUEST['region'] ) ? $_REQUEST['region'] : '';
			wp_register_script( 'gust-builder', $this->ui_script_url . '/builder.js', array( 'gust-compiler' ), $this->script_version, true );
			wp_enqueue_style( 'gust-builder-css', $this->url . 'assets/css/builder.css', array(), $this->script_version );

			function map_object_to_key_label( $obj ) {
				return array(
					'key'   => $obj->name,
					'label' => $obj->label,
				);
			}

			// get a list of all the post types
			$post_types = get_post_types( array(), 'objects' );

			// convert them to something useable
			// maybe just the name and label
			$post_types = array_values( array_map( 'map_object_to_key_label', $post_types ) );

			$taxs = get_taxonomies( array(), 'objects' );
			$taxs = array_values( array_map( 'map_object_to_key_label', $taxs ) );

			// get a list of colours to use.
			$config  = json_decode( $this->get_option( 'config', '{}' ) );
			$colours = $this->get_colour_list( $config->theme->textColor );

			$back_url = get_edit_post_link( $post_id );
			if ( $region ) {
				$back_url = menu_page_url( self::PAGE_NAME, false );
			}

			// check if the user has seen the tour.
			$seen_tour = get_user_meta( get_current_user_id(), 'gust_seen_tour', true );
			if ( ! $seen_tour ) {
				update_user_meta( get_current_user_id(), 'gust_seen_tour', 1 );
			}

			$content = '';
			if ( $post_id ) {
				$content = $this->get_gust_content( $post_id );
			} elseif ( $region ) {
				$content = $this->get_gust_region_content( $region );
			}

			$fonts = apply_filters( 'gust_builder_fonts', array() );
			wp_localize_script(
				'gust-builder',
				'Gust',
				array(
					'adminUrl'          => admin_url( 'admin-ajax.php' ),
					'advancedDnD'       => ! $this->get_option( 'disable_advanced_drag_and_drop' ),
					'backUrl'           => $back_url,
					'builderStyle'      => 'advanced',
					'colours'           => $colours,
					'components'        => $this->components->components,
					'config'            => $this->get_config(),
					'content'           => $content,
					'css'               => $this->get_tw_css(),
					'fonts'             => $fonts,
					'isAdmin'           => current_user_can( 'administrator' ),
					'isFree'            => $this->is_free,
					'languageAtts'      => get_language_attributes( 'html' ),
					'nonce'             => wp_create_nonce( self::NONCE_KEY ),
					'pageUrl'           => get_post_status( $post_id ) === 'publish' ? get_permalink( $post_id ) : get_preview_post_link( $post_id ),
					'postId'            => $post_id,
					'postTypes'         => $post_types,
					'region'            => $region,
					'runTour'           => ! $seen_tour,
					'screens'           => $config->theme->screens,
					'tailwindVersion'   => $this->get_option( 'tw_version' ),
					'taxonomies'        => $taxs,
					'templates'         => $this->components->get_templates(),
					'themeUrl'          => $this->url,
				)
			);
			wp_enqueue_script( 'gust-builder' );
			wp_enqueue_style( 'gust-inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;800&display=swap' );
			// remove default WordPress styles
			wp_deregister_style( 'wp-admin' );
		}
	}

	public function admin_menu() {
		add_menu_page(
			__( 'Gust', 'gust' ),
			__( 'Gust', 'gust' ),
			'edit_posts',
			self::PAGE_NAME,
			array( $this, 'render_admin_menu' ),
			'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQwIiBoZWlnaHQ9IjE2NCIgdmlld0JveD0iMCAwIDI0MCAxNjQiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNLTAuMDAwMTIyMDcgOTQuMzE4N0MtMC4wMDAxMjIwNyA3Mi4yMDA4IDkuNjI5MjcgNDEuMDIwNSAyOS40NDQ4IDIxLjA0MzFDNDkuMjYwMyAxLjA2NTc3IDEwMC45NzcgLTkuODcxNTcgMTQ0LjcyOSAxMy4wNTQ5QzE4OC40ODEgMzUuOTgxNCAyMTcuNzMxIDEzLjA1NDkgMjMzLjQ3IDcuODQ5NDlDMjQyLjg1MyAzOC4zNTI1IDI0MC43IDYxLjI1NjUgMjI3LjQzMyA4MC4xMzU0QzIxNC4xNjUgOTkuMDE0MyAxNjQuOTUxIDExNy40NzggMTEwLjMyMSA4Ny44NjIxQzc3LjM5MjUgNzAuMDExMiA0MS41NjYzIDUxLjcyMzggLTAuMDAwMTIyMDcgOTQuMzE4N1oiIGZpbGw9IiNGQUZERkYiLz4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0yLjMzMTQ2IDE1NC43MTFDLTEuMTkyMDkgMTM5LjQzIC0xLjk4ODQ0IDEyMy4yNDggOS4xNTYxNyAxMDguOTYzQzI0Ljc3NDUgODkuNTIyNyA2My44MDA4IDgyLjk4NjMgODcuMzc1NyA5OC41ODEzQzExMC45NTEgMTE0LjE3NiAxMzQuNjI1IDEyMy4zNDkgMTQxLjk2OSAxMjMuMzQ5QzE0My44MjQgMTM4Ljk1OSAxMjYuMDgyIDE1OS44MTUgMTEyLjM0OSAxNjIuNTg5Qzk4LjYxNjEgMTY1LjM2MiA4NC4yODM5IDE2My44MjggNjcuNzE2IDE1Ny4xNzhDNTEuMTQ4MSAxNTAuNTI5IDM4LjU1NyAxMjkuMjM1IDIuMzMxNDYgMTU0LjcxMVoiIGZpbGw9IiNGQUZERkYiLz4KPC9zdmc+Cg=='
		);
	}

	public function render_admin_menu() {
		if ( ! isset( $_REQUEST['post'] ) && ! isset( $_REQUEST['region'] ) ) {
			$this->render_settings_page();
			return;
		}
		ob_start();
		include $this->path . 'templates/admin-page.php';
		echo ob_get_clean();
	}

	public function save_content() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_KEY ) ) {
			wp_send_json_error( array( 'error' => 'Invalid nonce' ), 403 );
		}
		if ( ! isset( $_REQUEST['postId'] ) && ! isset( $_REQUEST['region'] ) ) {
			wp_send_json_error( array( 'error' => 'Missing post ID or region' ), 400 );
		}
		if ( ! isset( $_REQUEST['css'] ) ) {
			wp_send_json_error( array( 'error' => 'Missing CSS' ), 400 );
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'error' => 'You do not have permission to edit this post' ), 400 );
		}

		// save the post or region content.
		$id         = $_REQUEST['postId'];
		$use_region = false;
		if ( $_REQUEST['region'] ) {
			$id         = $_REQUEST['region'];
			$use_region = true;
		}

		$content = isset( $_REQUEST['content'] )
			? $_REQUEST['content']
			: '';

		$safelist = isset( $_REQUEST['safelist'] )
			? $_REQUEST['safelist']
			: '';

		$css = $_REQUEST['css'];
		$this->save_post_content( $id, $content, $safelist, $css, $use_region );

		wp_send_json_success(
			array(
				'status' => 'saved',
			)
		);
	}

	public function save_post_content( $id, $content, $safelist, $css, $use_region = false ) {
		if ( $use_region ) {
			$region_data = array(
				'content'  => $content,
				'safelist' => $safelist,
			);
			$this->regions->update_region( $id, $region_data );
		} else {
			$abs_post_id = absint( $id );
			update_post_meta( $abs_post_id, self::CONTENT_META_KEY, $content );
			update_post_meta( $abs_post_id, self::USE_GUST_META_KEY, 1 );
			update_post_meta( $abs_post_id, self::SAFELIST_META_KEY, $safelist );
		}

		$this->save_css( stripslashes( $css ) );
	}

	/**
	 * Returns the content for a region
	 * 
	 * @param string $slug The region name.
	 */
	public function get_gust_region_content( $slug ) {
		$region = $this->regions->get_region( $slug );
		if ( ! $region ) {
			return '[]';
		}

		return $region['content'] ? $region['content'] : '[]';
	}

	public function get_gust_content( $post_id ) {
		$meta = get_post_meta( absint( $post_id ), self::CONTENT_META_KEY, true );
		return $meta ? $meta : '[]';
	}

	public function save_component() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_KEY ) ) {
			wp_send_json_error( array( 'error' => 'Invalid nonce' ), 403 );
		}
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'error' => 'You do not have permission to edit this post' ), 400 );
		}

		if ( ! isset( $_REQUEST['node'] ) || ! isset( $_REQUEST['id'] ) || empty( $_REQUEST['node'] ) || empty( $_REQUEST['id'] ) ) {
			wp_send_json_error( array( 'error' => 'Missing required data' ), 400 );
		}

		// update our store of components
		// I'm really not sure options is the best place to do this
		// but maybe it's fine for now?
		// could be a custom post type too...
		$components   = get_option( 'gust_components', array() );
		$components[] = $_REQUEST['id'];
		update_option( 'gust_components', $components );
		update_option( "gust_component_{$_REQUEST['id']}", $_REQUEST['node'] );
		wp_send_json_success();
	}

	public function get_valid_post_types() {
		return apply_filters( 'gust_enable_post_types', array( 'post', 'page' ) );
	}

	public function add_row_actions() {
		 $valid_post_types = $this->get_valid_post_types();
		foreach ( $valid_post_types as $post_type ) {
			add_filter( "{$post_type}_row_actions", array( $this, 'row_actions' ), 10, 2 );
		}
	}

	public function get_post_edit_url( $post_id = 0 ) {
		return add_query_arg(
			array(
				'page' => self::PAGE_NAME,
				'post' => $post_id,
			),
			get_admin_url()
		);
	}

	public function get_region_edit_url( $region ) {
		return add_query_arg(
			array(
				'page'   => self::PAGE_NAME,
				'region' => $region,
			),
			get_admin_url()
		);
	}

	public function row_actions( $actions, $post ) {
		$href      = $this->get_post_edit_url( $post->ID );
		$actions[] = '<a href="' . esc_attr( $href ) . '">Gust Page Builder</a>';
		return $actions;
	}

	public function hook_add_meta_box() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	}

	public function add_meta_boxes() {
		add_meta_box(
			'gust_use_builder',
			'Gust',
			array( $this, 'render_builder_metabox' ),
			$this->get_valid_post_types(),
			'side'
		);
	}

	public function does_post_use_gust( $post_id = 0 ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		return get_post_meta( $post_id, self::USE_GUST_META_KEY, true ) || 0;
	}

	public function render_builder_metabox( $post ) {
		if ( $this->does_post_use_gust( $post->ID ) ) {
			echo '<p>This page uses the Gust page builder</p>';
		} else {
			echo '<p>This page does not use the Gust page builder</p>';
		}
		$href = $this->get_post_edit_url( $post->ID );
		echo '<p><a class="button" href="' . esc_attr( $href ) . '">Use Gust</a></p>';
	}

	public function register_settings() {
		$settings = array(
			array(
				'name'     => 'general_settings',
				'label'    => __( 'General Settings', 'gust' ),
				'settings' => array(
					array(
						'name'        => 'purchase_code',
						'label'       => __( 'Licence Key', 'gust' ),
						'type'        => 'text',
						'render_args' => array(
							'description' => __( 'Enter your licence key to receive automatic updates.', 'gust' ),
						),
					),
					array(
						'name'          => 'user_config_js',
						'label'         => __( 'Tailwind Config', 'gust' ),
						'type'          => 'textarea',
						'codeMirror'    => 'application/javascript',
						'render_args'   => array(
							'description' => __( 'Read more about how to edit the Tailwind css config <a href="https://www.getgust.com/docs/customise-tailwind?utm_platform=gust_settings" target="_blank" rel="noopener noreferrer">in our docs</a>', 'gust' ),
							'codeMirror'  => 'application/javascript',
						),
					),
					array(
						'name'        => 'tw_css',
						'label'       => __( 'Tailwind CSS', 'gust' ),
						'type'        => 'textarea',
						'render_args' => array(
							'codeMirror' => 'css',
						),
					),
					array(
						'name'         => 'form_recipients',
						'label'        => __( 'Form email recipients', 'gust' ),
						'type'         => 'text',
						'render_args'  => array(
							'description'  => __( 'A comma separated list of email addresses that will receive emails when a Gust form is submitted', 'gust' ),
						),
					),
					array(
						'name'         => 'form_sender',
						'label'        => __( 'Form email "from"', 'gust' ),
						'type'         => 'text',
						'render_args'  => array(
							'description'  => __( 'The email address that emails should be sent from. Ensure that you have permission to send from this email address otherwise delivery may fail. <a href="https://www.getgust.com/docs/forms?utm_platform=gust_settings" target="_blank" rel="noopener noreferrer">Read the docs</a> for more info.', 'gust' ),
						),
					),
					array(
						'name'         => 'safelist',
						'label'        => __( 'Safelist', 'gust' ),
						'type'         => 'text',
						'render_args'  => array(
							'description'  => __( 'Gust will run Purge CSS against the class names used on your pages to strip any unused CSS. You can whitelist any class names here that you need to keep if the are not picked up automatically', 'gust' ),
						),
					),
					array(
						'name'         => 'dev_mode',
						'label'        => __( 'Dev Mode', 'gust' ),
						'type'         => 'checkbox',
						'render_args'  => array(
							'description'  => __( 'When enabled, Dev mode will load development styles when viewing the website. This allows you to work with Tailwind CSS classes in templates and PHP files without rebuilding styles after each change. See the docs for more info.', 'gust' ),
						),
					),
					array(
						'name'         => 'disable_advanced_drag_and_drop',
						'label'        => __( 'Disable advanced drag and drop', 'gust' ),
						'type'         => 'checkbox',
						'render_args'  => array(
							'description'  => __( 'Advanced drag and drop will expand each element when adding or moving elements. It aims to improve accuracy when building pages, but can be overwhelming for some users.', 'gust' ),
						),
					),
				),
			),
		);
		$settings = apply_filters( 'gust_settings', $settings, $this );

		$setting_input_types = array(
			'text'     => array( $this, 'render_setting_field_text' ),
			'textarea' => array( $this, 'render_setting_field_textarea' ),
			'select'   => array( $this, 'render_setting_field_select' ),
			'checkbox' => array( $this, 'render_setting_field_checkbox' ),
		);
		$setting_input_types = apply_filters( 'gust_setting_input_types', $setting_input_types, $this );

		foreach ( $settings as $setting_section ) {

			add_settings_section(
				$setting_section['name'],
				$setting_section['label'],
				array( $this, 'render_setting_section_general' ),
				'gust',
			);

			foreach ( $setting_section['settings'] as $setting ) {
				register_setting( 'gust', 'Gust_' . $setting['name'] );

				$render_args = array_merge(
					array(
						'name' => $setting['name'],
					),
					$setting['render_args'],
				);

				add_settings_field(
					$setting['name'],
					$setting['label'],
					$setting_input_types[ $setting['type'] ],
					'gust',
					$setting_section['name'],
					$render_args
				);
			}
		}

	}

	public function render_settings_page() {
		$gust    = array(
			'urls' => array(
				'header' => '',
				'footer' => '',
			),
		);
		$regions = $this->regions->get_regions();
		foreach ( $regions as $region_key ) {
			$gust['urls'][ $region_key ] = $this->get_region_edit_url( $region_key );
		}
		ob_start();
		include $this->path . 'templates/settings.php';
		echo ob_get_clean();
	}

	public function render_setting_section_general() {  }

	public function render_setting_field_text( $args ) {
		$option = $this->get_option( $args['name'] );
		echo '<input id="' . esc_attr( $args['name'] ) . '" name="Gust_' . $args['name'] . '" value="' . esc_attr( $option ) . '">';
		if ( isset( $args['description'] ) && $args['description'] ) {
			echo '<p>' . $args['description'] . '</p>';
		}
	}

	public function render_setting_field_textarea( $args ) {
		$option           = $this->get_option( $args['name'] );
		$code_mirror_mode = '';
		$classes          = array();
		if ( $args['codeMirror'] ) {
			$classes[]        = 'gust-cm';
			$code_mirror_mode = $args['codeMirror'];

			$settings = wp_enqueue_code_editor( array( 'type' => $args['codeMirror'] ) );
			if ( false !== $settings ) {
				wp_add_inline_script(
					'code-editor',
					sprintf(
						'jQuery( function() { wp.codeEditor.initialize( %s, %s ); } );',
						$args['name'],
						wp_json_encode( $settings )
					)
				);
			}
		}

		echo '<textarea class="' . esc_attr( implode( ' ', $classes ) ) . '" id="' . esc_attr( $args['name'] ) . '" name="Gust_' . $args['name'] . '"  data-cm-mode="' . esc_attr( $code_mirror_mode ) . '" >' . esc_textarea( $option ) . '</textarea>';
		if ( isset( $args['description'] ) && $args['description'] ) {
			echo '<p>' . $args['description'] . '</p>';
		}
	}

	/**
	 * Displays a setting checkbox field.
	 * 
	 * @param array $args Options passed in from add_settings_field.
	 */
	public function render_setting_field_checkbox( $args ) {
		$option  = $this->get_option( $args['name'] );
		$checked = $option ? 'checked' : '';
		echo '<input id="' . esc_attr( $args['name'] ) . '" name="Gust_' . $args['name'] . '" value="1" type="checkbox" ' . esc_attr( $checked ) . '>';
		if ( isset( $args['description'] ) && $args['description'] ) {
			echo '<p>' . esc_html( $args['description'] ) . '</p>';
		}
	}

	/**
	 * Displays a setting select box
	 * 
	 * @param array $args Options passed in from add_settings_field.
	 */
	public function render_setting_field_select( $args ) {
		$option = $this->get_option( $args['name'] );
		echo '<select name="Gust_' . esc_attr( $args['name'] ) . '">';
		foreach ( $args['options'] as $value => $label ) {
			$selected = $option === strval( $value ) ? 'selected' : '';
			echo '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Returns the config
	 */
	public function get_config() {
		return $this->get_option( 'user_config_js' );
	}

	/**
	 * Get an array of unique class names used on the site.
	 *
	 * @param array $exclude_ids An array of IDs to exclude from the query.
	 */
	public function get_class_safelist( $exclude_ids = array() ) {
		// get all the safelist metas.
		global $wpdb;

		$safelist_query_and_list = array();
		if ( ! empty( $exclude_ids ) ) {
			$escaped_ids               = array_map( 'esc_sql', $exclude_ids );
			$safelist_query_and_list[] = 'post_id NOT IN(' . implode( ', ', $escaped_ids ) . ')';
		}
		$safelist_query_and = empty( $safelist_query_and_list )
			? ''
			: 'AND ' . implode( ' AND ', $safelist_query_and_list );

		$safelists = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value
      FROM {$wpdb->postmeta}
      WHERE meta_key = %s {$safelist_query_and}",
				self::SAFELIST_META_KEY
			)
		);
		$safelist  = explode( ' ', join( ' ', $safelists ) );

		// also add our own safelist.
		$gust_safelist = $this->get_option( 'safelist' );
		$safelist      = array_merge( $safelist, explode( ' ', $gust_safelist ) );

		// get the safelist from any regions.
		foreach ( $this->regions->get_regions() as $region_key ) {
			$region = $this->regions->get_region( $region_key );
			if ( ! $region ) {
				continue;
			}
			$safelist = array_merge( $safelist, explode( ' ', $region['safelist'] ) );
		}

		// get the safelist from any template files.
		$template_safelist = $this->get_safelist_from_templates();
		$safelist          = array_merge( $safelist, $template_safelist );

		// and finally, this is the safelist from our default theme.
		$theme_safelist  = 'block md:inline-block border border-t border-2 border-b-2 border-transparent border-primary hover:border-primary bg-gray-200 border-gray-200 flex flex-wrap flex-1 font-bold font-medium grid grid-cols-1 md:grid-cols-2 gap-8 hover:opacity-70 max-w-screen-lg mx-auto mb-2 mb-8 my-2 my-8 md:mb-0 w-full md:w-1/4 md:w-auto md:ml-8 p-2 px-4 py-2 rounded space-x-2 space-x-4 space-y-8 text-lg text-2xl text-3xl text-4xl text-xs text-sm text-gray-500 text-gray-700 text-gray-900 uppercase rounded-r rounded-l text-gray-900';
		$theme_safelist .= ' gust-posts-navigation gust-mobile-nav--closed gust-mobile-nav--open gust-search--closed gust-search--open prose aligncenter alignright alignleft wp-caption-text avatar';

		// added by comment template walker.
		$theme_safelist .= ' pl-2 mt-2 ml-2 border-l';

		// screen reader.
		$theme_safelist .= ' screen-reader-text';

		$theme_safelist = explode( ' ', $theme_safelist );
		$theme_safelist = apply_filters( 'gust_theme_safelist', $theme_safelist );

		// sometimes multiple class names may be passed as a single string
		// let's join and re-split.
		$theme_safelist = implode( ' ', $theme_safelist );
		$theme_safelist = explode( ' ', $theme_safelist );
		$safelist       = array_merge( $safelist, $theme_safelist );

		$unique = array_unique( $safelist );
		$unique = apply_filters( 'gust_safelist', $unique );
		return array_values( $unique );
	}

	/**
	 * Searches any templates and attempts to extract CSS class names
	 */
	private function get_safelist_from_templates() {
		$templates_to_check = array(
			'404.php',
			'archive.php',
			'attachment.php',
			'author.php',
			'category.php',
			'comments.php',
			'date.php',
			'embed.php',
			'footer.php',
			'frontpage.php',
			'header.php',
			'home.php',
			'index.php',
			'page.php',
			'paged.php',
			'privacypolicy.php',
			'search.php',
			'searchform.php',
			'sidebar.php',
			'single.php',
			'singular.php',
			'tag.php',
			'taxonomy.php',
			'template-parts/content/post-latest.php',
			'template-parts/content/post-list-item.php',
			'template-parts/content/content-single.php',
			'template-parts/content/content-none.php',
			'template-parts/content/post-tags.php',
			'template-parts/content/post-categories.php',
		);
		$templates_to_check = apply_filters( 'gust_safelist_templates', $templates_to_check );
		$files              = array();
		foreach ( $templates_to_check as $template ) {
			$path = locate_template( $template, false, false );
			if ( '' !== $path ) {
				$files[] = $path;
			}
		}

		$files    = apply_filters( 'gust_safelist_files', $files );
		$safelist = array();
		foreach ( $files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}
			$file_content = file_get_contents( $file );
			// this is a very rudimentary implementation. It's Purge CSS's default extractor.
			// let's work on a more comprehensive PHP extractor.
			preg_match_all( '/[A-Za-z0-9:[%\]#_.-]+/m', $file_content, $matches );
			if ( ! empty( $matches ) && ! empty( $matches[0] ) ) {
				$safelist = array_merge( $safelist, $matches[0] );
			}
		}
		return $safelist;
	}

	private function get_colour_list( $colours = array() ) {
		$list = array();
		foreach ( $colours as $key => $value ) {
			if ( is_object( $value ) ) {
				$list = array_merge( $list, $this->get_colour_list( $value ) );
			} else {
				if ( preg_match( '/^#/', $value ) !== 1 ) {
					continue;
				}
				$list[] = $value;
			}
		}
		return $list;
	}

	/**
	 * Checks the purchase code when it changes
	 * 
	 * @param string $old_value The old value.
	 * @param string $new_value The new value.
	 */
	public function recheck_purchase_code( $old_value, $new_value ) {
		if ( $old_value == $new_value ) {
			return;
		}
		//$this->api->get_theme_info( $new_value, false );
	}

	/**
	 * Checks the purchase code when it's initially added
	 * 
	 * @param string $option_name The option name.
	 * @param string $new_value The new value.
	 */
	public function check_purchase_code( $option_name, $new_value ) {
		//$this->api->get_theme_info( $new_value, false );
	}

	public function admin_notices() {
		$screen = get_current_screen();
        /*
		if ( $screen->id === 'toplevel_page_gust' ) {
			$remote = $this->api->get_theme_info(
				$this->get_option( 'purchase_code' ),
				true
			);
			if ( $remote ) {
				$theme_info = json_decode( $remote['body'] );
				if ( property_exists( $theme_info, 'error_code' ) && ! empty( $theme_info->error_code ) ) {
					echo '<div class="notice notice-error">';
					$message = __( 'You need a valid licence key to receive updates.', 'gust' );
					switch ( $theme_info->error_code ) {
						case 'INVALID_PURCHASE_CODE':
							$message = __( 'That licence key is invalid. You can manage your sites and licences in <a href="https://www.getgust.com/docs/account-and-subscription" target="_blank" rel="noopener noreferrer">your account</a>. Please double check and if you are still having trouble, get in touch with support.', 'gust' );
							break;

						case 'PURCHASE_CODE_IN_USE':
							$message = __( 'That licence key appears to be used on another website. First remove it there and then try again. You can manage your sites and licences in <a href="https://www.getgust.com/docs/account-and-subscription" target="_blank" rel="noopener noreferrer">your account</a>. If you are still having trouble, please get in touch with support', 'gust' );
							break;

						case 'EXPIRED':
							$message = __( 'Your licence has expired', 'gust' );
							break;
					}
					echo '<p>' . $message . '</p>';
					echo '</div>';
				}
			}
		}
        */
	}

	/**
	 * Saves the production CSS
	 *
	 * @param string $css the CSS to save
	 */
	public function save_css( $css ) {
		$file_name  = 'prod.css';
		$final_path = $this->upload_path . $file_name;

		$option_name = 'prod_css_version';
		$v           = $this->get_option( $option_name, 1 );
		$this->update_option( $option_name, $v + 1 );
		file_put_contents( $final_path, $css );
	}

	/**
	 * Returns the current safelist of the site
	 */
	public function ajax_get_site_safelist() {
		$exclude  = isset( $_REQUEST['excludePost'] )
			? array( absint( $_REQUEST['excludePost'] ) )
			: array();
		$safelist = $this->get_class_safelist( $exclude );
		wp_send_json( array( 'safelist' => $safelist ) );
	}

	/**
	 * Saves the CSS
	 */
	public function ajax_save_css() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_KEY ) ) {
			wp_send_json_error( array( 'error' => 'Invalid nonce' ), 403 );
		}
		if ( ! isset( $_REQUEST['css'] ) || empty( $_REQUEST['css'] ) ) {
			wp_send_json_error( array( 'error' => 'Invalid CSS' ), 403 );
		}
		$this->save_css( stripslashes( $_REQUEST['css'] ) );
		wp_send_json( array( 'saved' => true ) );
	}

	/**
	 * Returns the current parsed Tailwind CSS of the site
	 */
	public function get_tw_css() {
		$css      = $this->get_option( 'tw_css' );
		$gust_css = file_get_contents( $this->path . 'defaults/gust-css.css' );
		if ( $this->is_wc_active() && apply_filters( 'gust_include_wc_styles', true ) ) {
			$woo_css   = file_get_contents( $this->path . 'defaults/woo.css' );
			$gust_css .= "\r\n";
			$gust_css .= $woo_css;
		}
		return str_replace( '@gust;', $gust_css, $css );
	}

	/**
	 * Returns the current Tailwind CSS of the site from an AJAX request
	 */
	public function ajax_get_tw_css() {
		 $out = array(
			 'css' => $this->get_tw_css(),
		 );
		 wp_send_json( $out );
	}

	/**
	 * Resets a region
	 */
	public function ajax_reset_region() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], self::NONCE_KEY ) ) {
			wp_send_json_error( array( 'error' => 'Invalid nonce' ), 403 );
		}
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'error' => 'You do not have permission to edit this post' ), 400 );
		}
		if ( ! isset( $_POST['payload'] ) || empty( $_POST['payload'] ) ) {
			wp_send_json_error( array( 'error' => 'Empty region' ) );
		}
		$region = (string) $_POST['payload'];
		$this->regions->reset_region( $region );
	}

	/**
	 * Returns whether WC is active
	 */
	public function is_wc_active() {
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	/**
	 * Given a list of nodes, returns the safelist for those nodes
	 */
	public function ajax_get_nodelist_safelist() {
		if ( ! isset( $_REQUEST['nodes'] ) ) {
			wp_send_json_error( array( 'error' => 'Missing Nodes' ), 400 );
		}

		$nodes    = json_decode( stripslashes( $_REQUEST['nodes'] ), true );
		$safelist = apply_filters( 'gust_content_safelist', array(), $nodes );

		wp_send_json(
			array(
				'safelist' => array_values( $safelist ),
			)
		);
	}
}