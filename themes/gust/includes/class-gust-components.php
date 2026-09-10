<?php
/**
 * Gust component management
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();
class Gust_Components {

	public $components = array();
	private $dir;
	public function __construct( $dir ) {
		$this->dir = $dir;
		add_filter( 'gust_render_component', array( $this, 'gust_component' ), 10, 2 );
		add_action( 'init', array( $this, 'register_components_from_db' ) );
		add_action( 'init', array( $this, 'register_components' ) );
		add_filter( 'gust_option_class_names', array( $this, 'reveal_class_names_safelist' ), 10, 3 );
		add_filter( 'gust_reveal_class_names', array( $this, 'reveal_class_names' ), 10, 3 );
	}

	public function get_components() {
		return $this->components;
	}

	public function gust_component( $_, $name ) {
		return $this->get_component_layout( $name );
	}

	public function register_components_from_db() {
		// get all our available components
		$components = get_option( 'gust_components', array() );
		foreach ( $components as $component_name ) {
			$layout = get_option( 'gust_component_' . $component_name, null );
			if ( $layout ) {
				$this->register_component( $component_name, $layout );
			}
		}
	}

	public function get_component_layout( $name ) {
		if ( isset( $this->components[ $name ] ) ) {
			return $this->components[ $name ];
		}
		return null;
	}

	public function register_component( $name, $layout ) {
		$layout                    = apply_filters( 'gust_register_component', $layout, $name );
		$this->components[ $name ] = $layout;
	}

	public function register_components() {
		$components = array(
			'' => array(
				'card',
				'button',
			),
			'/headings' => array(
				'h1',
				'h2',
				'h3',
				'h4',
				'h5',
				'h6',
			),
			'/layout' => array(
				'container',
				'grid',
				'column',
				'iconRow',
				'reveal',
			),
			'/content' => array(
				'tabsTop',
				'accordion',
				'accordionBoxed',
				'accordionShadowed',
				'image',
				'anchor',
			),
			'/slider' => array(
				'slider',
				'heroImageSlide',
			),
			'/posts' => array(
				'postCard',
				'postImageCard',
				'postGrid',
				'postImageGrid',
				'postListItem',
			),
			'/inputs' => array(
				'form',
				'textInput',
				'textarea',
				'select',
				'checkbox',
				'radio',
				'fieldset',
				'submitButton',
			),
		);
		foreach ( $components as $sub_dir => $dir_components ) {
			foreach ( $dir_components as $component ) {
				$content = file_get_contents( $this->dir . 'includes/components' . $sub_dir . '/' . $component . '.json' );
				$layout  = json_decode( $content, true );
				$this->register_component( $component, $layout );
			}
		}

		// get all the icons
		$icon_dirs = array( 'heroicons/solid/', 'heroicons/outline/', 'solid/', 'regular/', 'brands/' );
		foreach ( $icon_dirs as $icon_dir ) {
			if ( $dh = opendir( $this->dir . 'includes/components/icons/' . $icon_dir ) ) {
				while ( ( $file = readdir( $dh ) ) !== false ) {
					if ( $file === '.' || $file === '..' ) {
						continue;
					}
					$content   = file_get_contents( $this->dir . 'includes/components/icons/' . $icon_dir . $file );
					$layout    = json_decode( $content, true );
					$file_name = str_replace( '.json', '', $file );
					$this->register_component( $file_name, $layout );
				}
				closedir( $dh );
			}
		}

		do_action( 'gust_register_components', $this );
	}

	/**
	 * Adds the selected reveal effect to the class name safelist.
	 * 
	 * @param string $class_names The class names.
	 * @param object $option The current option.
	 * @param string $option_value The selected option value.
	 */
	public function reveal_class_names_safelist( $class_names, $option, $option_value ) {
		if ( 'effect' !== $option['name'] ) {
			return $class_names;
		}
		return $class_names . ' ' . $option_value;
	}

	/**
	 * Adds the selected effect as a class name to the tag.
	 * 
	 * @param string $class_names The class names.
	 * @param object $_context Ignored.
	 * @param object $component_details The component details.
	 */
	public function reveal_class_names( $class_names, $_context, $component_details ) {
		return $class_names . ' ' . $component_details['reveal']['options']['effect'];
	}

	/**
	 * Returns a list of all templates
	 */
	public function get_templates() {

		$hydrated_templates = array();
		$templates          = array(
			'Content' => array(
				'dir'       => 'content',
				'templates' => array(
					'feature-list-with-right-cutoff-image',
					'feature-cta-with-right-cutoff-image',
					'feature-cta-with-right-image',
					'feature-list-with-right-image',
					'feature-icon-list-with-right-cutoff-image',
					'feature-icon-list-with-right-image',
					'alt-text-image',
					'three-col-features',
					'three-col-without-border',
					'two-col-icon-left',
					'gradient-section-image-right',
					'cta-simple',
				),
			),
			'Hero Sections' => array(
				'dir'       => 'hero-sections',
				'templates' => array(
					'with-app-screenshot',
					'hero-1',
					'center-with-bg-image',
					'center-with-bg-color',
					'left-with-bg-image',
					'left-with-bg-color',
					'text-center-image-right',
					'text-left-image-right',
				),
			),
			'Pricing' => array(
				'dir'       => 'pricing',
				'templates' => array(
					'featured-centered-item',
					'single-price-card',
					'three-price-cards',
				),
			),
			'Team' => array(
				'dir'       => 'team',
				'templates' => array(
					'single-col-profile-left-text-right',
					'three-col-rectangle-image',
					'three-cols-round-image-center-text',
					'two-cols-profile-left-text-right',
				),
			),
			'Footers' => array(
				'dir'       => 'footers',
				'templates' => array(
					'default-footer',
					'footer-1',
					'footer-2',
				),
			),
			'Navigation' => array(
				'dir'       => 'navigation',
				'templates' => array(
					'default-nav',
					'default-nav-left',
					'default-nav-with-buttons',
					'default-nav-with-buttons-left',
				),
			),
		);
		foreach ( $templates as $category_name => $category ) {
			$hydrated_templates[ $category_name ] = array();
			foreach ( $category['templates'] as $template_name ) {
				$content                                = file_get_contents( $this->dir . 'includes/templates/' . $category['dir'] . '/' . $template_name . '.json' );
				$template_content                       = json_decode( $content, true );
				$hydrated_templates[ $category_name ][] = $template_content;
			}
		}
		$hydrated_templates = apply_filters( 'gust_templates', $hydrated_templates );
		return $hydrated_templates;
	}
}
