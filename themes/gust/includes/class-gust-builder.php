<?php
/**
 * Builds & outputs Gust templates
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();
class Gust_Builder {

	private $url;
	private $script_version;
	public function __construct( $url, $script_version ) {
		$this->url            = $url;
		$this->script_version = $script_version;
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'gust_content_safelist', array( $this, 'content_safelist' ), 10, 2 );
		add_filter( 'gust_tag_attributes', array( $this, 'format_attributes' ), 10, 1 );
	}

	public function enqueue_scripts() {
		wp_register_script( 'gust-revealable', $this->url . 'assets/js/components/revealable.js', array( 'jquery' ), $this->script_version, true );
		wp_register_script( 'gust-intersection-reveal', $this->url . 'assets/js/components/intersectionReveal.js', array( 'jquery' ), $this->script_version, true );
		wp_register_script( 'gust-swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), $this->script_version, true );
		wp_register_style( 'gust-swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css', array(), $this->script_version );
		wp_register_script( 'gust-slider', $this->url . 'assets/js/components/slider.js', array( 'gust-swiper', 'jquery' ), $this->script_version, true );
	}

	/**
	 * Adds the correct class names into the safelist for the content
	 * 
	 * @param array $class_names A list of class names.
	 * @param array $content The list of nodes.
	 */
	public function content_safelist( $class_names, $content ) {
		$wrapped_content = array(
			'tag'        => 'div',
			'instanceId' => 'ROOT',
			'classNames' => 'gust',
			'children'   => $content,
		);
		return $this->get_class_list_from_node_list( $wrapped_content, $class_names );
	}

	private function get_function_content( $name ) {
		$valid_get_names = array(
			'get_the_date',
			'get_the_title',
			'get_the_content',
			'get_the_permalink',
			'get_the_post_thumbnail',
			'get_the_post_thumbnail_url',
			'get_the_excerpt',
			'get_post_status',
			'get_the_ID',
		);
		if ( in_array( $name, $valid_get_names ) ) {
			return call_user_func( $name );
		}
		$method_map = array(
			'get_author_name' => function() {
				return get_the_author_meta( 'display_name' );
			},
			'get_author_image_url' => function () {
				return get_avatar_url( get_the_author_meta( 'ID' ) );
			},
			'get_author_permalink' => function () {
				return get_author_posts_url( get_the_author_meta( 'ID' ) );
			},
		);
		if ( array_key_exists( $name, $method_map ) ) {
			return $method_map[ $name ]();
		}

		return '';
	}

	/**
	 * Given a value, will return the media URL if it exists
	 * 
	 * @param string $value A value in the format of attId=123url=string.
	 */
	private function get_media_url_from_value( $value ) {
		if ( ! $value ) {
			return '';
		}

		preg_match_all( '/((?<=^attId=).+?(?=url=)|(?<=url=).*$)/', $value, $matches );

		if ( empty( $matches ) || empty( $matches[0] ) ) {
			return '';
		}

		$attachment_url = wp_get_attachment_url( $matches[0][0] );

		if ( $attachment_url ) {
			return apply_filters( 'gust_attachment_url', $attachment_url, $matches[0][0] );
		}

		return apply_filters( 'gust_attachment_url', $attachment_url, $matches[0][1] );
	}

	/**
	 * Returns the meta value for a post
	 * 
	 * @param number $post_id The post ID.
	 * @param string $meta_key The meta key.
	 */
	private function get_meta( $post_id, $meta_key ) {
		if ( function_exists( 'get_field' ) ) {
			return get_field( $meta_key, $post_id );
		}
		return get_post_meta( $post_id, $meta_key, true );
	}

	private function get_attributes( $tag, $context, $attributes = array() ) {
		if ( ! isset( $tag['attributes'] ) ) {
			return $attributes;
		}
		foreach ( $tag['attributes'] as $attribute ) {
			$value = '';
			switch ( $attribute['type'] ) {
				case 'function': 
					$value = $this->get_function_content( $attribute['value'] );
					break;
				
				case 'filter': 
					$value = apply_filters( $attribute['value'], '', $context );
					break;
				
				case 'value': 
					$value = isset( $attribute['value'] ) ? $attribute['value'] : '';
					break;
				
				case 'image':
					$value = $this->get_media_url_from_value( $attribute['value'] );
					break;
				
				case 'meta':
					$value = $this->get_meta( get_the_ID(), $attribute['value'] );
					break;

				case 'property':
					$tmp_value = $this->get_context_property( $context, $attribute['value'] );
					if ( ! is_null( $tmp_value ) ) {
						$value = $tmp_value;
					}
					break;
			}

			$attributes[ $attribute['name'] ] = esc_attr( $value );
		}
		return $attributes;
	}

	/**
	 * Converts a named array into a list of attribute values
	 *
	 * @param $att_key string The attribute key.
	 * @param $att_value string The attribute value.
	 */
	private function get_attribute_string( $att_key, $att_value ) {
		return $att_key . '="' . esc_attr( $att_value ) . '"';
	}

	private function get_context( $tag, $context = null ) {
		// create a context if we have one
		if ( Gust_Utilities::array_has_key( 'context', $tag ) ) {
			switch ( $tag['context']['type'] ) {
				// in the case of loop queries
				// they will internally use WP_Query
				// but we want to limit what options they can choose
				case 'loop': 
					$context_query = array();
					if ( Gust_Utilities::array_has_key( 'postType', $tag['context'] ) ) {
						$context_query['post_type'] = $tag['context']['postType'];
					}

					// order etc
					if ( Gust_Utilities::array_has_key( 'order', $tag['context'] ) ) {
						$context_query['order'] = $tag['context']['order'];
					}
					if ( Gust_Utilities::array_has_key( 'orderby', $tag['context'] ) ) {
						$context_query['orderby'] = $tag['context']['orderby'];
					}

					// pagination
					if ( Gust_Utilities::array_has_key( 'pagination', $tag['context'] ) ) {
						$pagination = $tag['context']['pagination'];
						if ( Gust_Utilities::array_has_key( 'perPage', $pagination ) ) {
							$context_query['posts_per_page'] = $pagination['perPage'];
						}
						if ( Gust_Utilities::array_has_key( 'paged', $pagination ) ) {
							$context_query['paged'] = $pagination['paged'];
						}
					}

					// create the context
					$context = new WP_Query( $context_query );
					break;
				

				case 'single': 
					if ( Gust_Utilities::array_has_key( 'id', $tag['context'] ) ) {
						$context = new WP_Query(
							array(
								'p' => $tag['context']['id'],
							)
						);
					}
					break;
				

				case 'filter': 
					if ( Gust_Utilities::array_has_key( 'name', $tag['context'] ) ) {
						$context = apply_filters( $tag['context']['name'], null );
					}
					break;

				case 'meta':
					if ( Gust_Utilities::array_has_key( 'name', $tag['context'] ) ) {
						$context = $this->get_meta( get_the_ID(), $tag['context']['name'] );
					}
					break;
				
			}
		}
		return $context;
	}

	/**
	 * Retrieves a property from the item in context, if it exists
	 * 
	 * @param object $context The context item to pull from. Can be an object or an array.
	 * @param string $property_name The property to pull from the context.
	 */
	private function get_context_property( $context, $property_name ) {
		if ( is_array( $context ) && Gust_Utilities::array_has_key( $property_name, $context ) ) {
			return $context[ $property_name ];
		} elseif ( is_object( $context ) && property_exists( $context, $property_name ) ) {
			return $context->{$property_name};
		}
		return null;
	}


	/**
	 * Gets a value for attribute references.
	 *
	 * @param object $reference The reference Must have type attribute.
	 * @param object $parent_component_details The parent node details. 
	 */
	private function value_from_attribute_reference( $reference, $parent_component_details ) {
		if ( ! Gust_Utilities::array_has_key( 'attributes', $parent_component_details ) ) {
			return '';
		}

		foreach ( $parent_component_details['attributes'] as $attribute ) {
			if ( $attribute['name'] !== $reference['target'] ) {
				continue;
			}
			return $attribute['value'];
		}

		return '';
	}

		/**
		 * Returns only references that update attributes.
		 *
		 * @param object $reference The reference.
		 */
	private function filter_attribute_references( $reference ) {
		return 'attribute' === $reference['updates'];
	}

		/**
		 * Returns only references that update class names.
		 *
		 * @param object $reference The reference.
		 */
	private function filter_class_name_references( $reference ) {
		return 'classNames' === $reference['updates'];
	}

	/**
	 * Creates attributes from references.
	 *
	 * @param object $tag The node details.
	 * @param mixed  $parent_component_details The node details of the parent component.
	 * @param object $context The current context.
	 */
	private function get_attributes_from_references( $tag, $parent_component_details, $context ) {
		if ( ! Gust_Utilities::array_has_key( 'references', $tag ) || ! $parent_component_details ) {
			return array();
		}
	

		$references = array_filter( $tag['references'], array( $this, 'filter_attribute_references' ) );
		$attributes = array();
		foreach ( $references as $reference ) {
			switch ( $reference['type'] ) {
				case 'attribute': 
					$attributes[ $reference['name'] ] = $this->value_from_attribute_reference( $reference, $parent_component_details );
					break;
				case 'option': 
					if ( Gust_Utilities::array_has_key( 'options', $parent_component_details ) && Gust_Utilities::array_has_key( $reference['target'], $parent_component_details['options'] ) ) {
						$attributes[ $reference['name'] ] = $parent_component_details['options'][ $reference['target'] ];
					}
					break;
			}
		}
		return $attributes;
	}

	/**
	 * gets class names from references.
	 *
	 * @param object $tag The node details.
	 * @param mixed  $parent_component_details The node details of the parent component.
	 */
	private function get_class_names_from_references( $tag, $parent_component_details ) {
		if ( ! Gust_Utilities::array_has_key( 'references', $tag ) || ! $parent_component_details ) {
			return '';
		}
	

		$references  = array_filter( $tag['references'], array( $this, 'filter_class_name_references' ) );
		$class_names = array();
		foreach ( $references as $reference ) {
			switch ( $reference['type'] ) {
				case 'option': 
					if ( ! Gust_Utilities::array_has_key( 'options', $parent_component_details ) || ! is_array( $parent_component_details['options'] ) || ! Gust_Utilities::array_has_key( $reference['target'], $parent_component_details['options'] ) ) {
						break;
					}
					$class_names[] = $parent_component_details['options'][ $reference['target'] ];
					break;
			}
		}
		return implode( ' ', $class_names );
	}

	/**
	 * Renders the nodes into HTML
	 * 
	 * @param object $tag A node to get the class list from.
	 * @param object $context Any WordPress context that it might have.
	 * @param object $component_details Any details to render the component.
	 * @param object $repeater_items Items used in the repeater.
	 * @param object $repeater_item The current repeater item.
	 * @param mixed  $parent_component_details The node details of the parent component for use in references.
	 */
	public function render_tag( $tag, $context = null, $component_details = array(), $repeater_items = null, $repeater_item = null, $parent_component_details = null ) {
		// this tag may be conditionally rendered
		// if it should not be displayed, we need to break out as early as possible.
		if ( Gust_Utilities::array_has_key( 'if', $tag ) ) {
			switch ( $tag['if']['type'] ) {
				case 'filter': 
					$if_params = Gust_Utilities::array_has_key( 'params', $tag['if'] ) ? $tag['if']['params'] : array();
					$display   = apply_filters( $tag['if']['value'], false, $if_params, $tag, $context );
					if ( ! $display ) {
						return '';
					}
					break;
				
			}
		}

		// set the repeater items.
		if ( Gust_Utilities::array_has_key( 'repeaterItems', $tag ) ) {
			$repeater_items = $tag['repeaterItems'];
		}
		$repeater_items = apply_filters( 'gust_repeater_items', $repeater_items, $context, $tag, $component_details );

		// if it's a component, render the component instead of this.
		if ( Gust_Utilities::array_has_key( 'instanceOfComponent', $tag ) ) {
			$component = apply_filters( 'gust_render_component', null, $tag['instanceOfComponent'] );
			if ( ! $component ) {
				return '';
			}
			$scoped_component_details = isset( $tag['componentDetails'] ) ? $tag['componentDetails'] : $component_details;
			return $this->render_tag( $component, $context, $scoped_component_details, $repeater_items, $repeater_item, null );
		}

		// set the current component details.
		$current_details = array();
		if ( Gust_Utilities::array_has_key( 'id', $tag ) && Gust_Utilities::array_has_key( $tag['id'], $component_details ) ) {
			$current_details = $component_details[ $tag['id'] ];

			// if there are no parent component details, then this must be the parent!
			// so let's set it here and let it continue..
			if ( ! $parent_component_details ) {
				$parent_component_details = $current_details;
			}
		}

		// enqueue any scripts & styles.
		if ( Gust_Utilities::array_has_key( 'scripts', $tag ) ) {
			foreach ( $tag['scripts'] as $script ) {
				wp_enqueue_script( $script );
			}
		}

		if ( Gust_Utilities::array_has_key( 'styles', $tag ) ) {
			foreach ( $tag['styles'] as $style ) {
				wp_enqueue_style( $style );
			}
		}

		// set the current repeater details.
		$repeater_details = array();
		if ( $repeater_item && Gust_Utilities::array_has_key( 'id', $tag ) && Gust_Utilities::array_has_key( $tag['id'], $repeater_item['componentDetails'] ) ) {
			$repeater_details = $repeater_item['componentDetails'][ $tag['id'] ];
		}

		// define the tag name.
		$tag_name = isset( $tag['tag'] ) ? $tag['tag'] : 'div';
		if ( Gust_Utilities::array_has_key( 'id', $tag ) ) {
			$tag_name = apply_filters( "gust_{$tag['id']}_tag_name", $tag_name, $context, $component_details );
		}

		// sort out any classes.
		$class_names = isset( $tag['classNames'] ) ? $tag['classNames'] : '';
		if ( Gust_Utilities::array_has_key( 'classNames', $current_details ) ) {
			$class_names = $current_details['classNames'];
		}

		// add any extra class names.
		$extra_class_names = '';
		if ( Gust_Utilities::array_has_key( 'extraClassNames', $current_details ) ) {
			$extra_class_names = $current_details['extraClassNames'];
		}

		// add any class names from references
		$reference_class_names = $this->get_class_names_from_references( $tag, $parent_component_details );

		// get all attributes from references.
		$attributes = $this->get_attributes_from_references( $tag, $parent_component_details, $context ); 

		// get all the attributes.
		$attributes = $this->get_attributes( $tag, $context, $attributes );

		// get any attributes from the overrides.
		$attributes = $this->get_attributes( $current_details, $context, $attributes );

		// get any attributes from repeaters.
		if ( Gust_Utilities::array_has_key( 'attributes', $repeater_details ) ) {
			$attributes = $this->get_attributes( $repeater_details, $context, $attributes );
		}



		// add optional attributes and classNames
		if ( Gust_Utilities::array_has_key( 'options', $tag ) ) {
			$overrides = Gust_Utilities::array_has_key( 'options', $current_details ) ? $current_details['options'] : array();
			foreach ( $tag['options'] as $option ) {
				// look for a value
				$option_value = Gust_Utilities::array_has_key( $option['name'], $overrides ) ? $overrides[ $option['name'] ] : null;
				if ( empty( $option_value ) && $option['type'] === 'select' ) {
					$option_value = $option['options'][0]['value'];
				}
				if ( empty( $option_value ) && $option['type'] === 'text' && Gust_Utilities::array_has_key( 'defaultValue', $option ) ) {
					$option_value = $option['defaultValue'];
				}

				$media_option_value = $this->get_media_url_from_value( $option_value );
				if ( $media_option_value ) {
					$option_value = $media_option_value;
				}

				switch ( $option['modify'] ) {
					case 'attribute': 
						$attributes[ $option['attributeName'] ] = esc_attr( $option_value );
						break;
					
					case 'className': 
						$class_names .= ' ' . $option_value;
						break;
					
				}
			}
		}


		// start building the content
		$output = '';

		$context = $this->get_context( $tag, $context );

		// add any content
		$content = Gust_Utilities::array_has_key( 'content', $tag ) ? $tag['content'] : null;
		if ( Gust_Utilities::array_has_key( 'content', $current_details ) ) {
			$content = $current_details['content'];
		}

		// get any content from the repeater item
		if ( Gust_Utilities::array_has_key( 'content', $repeater_details ) ) {
			$content = $repeater_details['content'];
		}

		if ( $content ) {
			$output_content = '';
			switch ( $content['type'] ) {
				case 'value': 
					/** NEED TO SANITIZE */
					$output_content .= $content['value'];
					break;
				
				case 'function': 
					$output_content .= $this->get_function_content( $content['value'] );
					break;
				
				case 'filter': 
					$output_content .= apply_filters( $content['value'], '', $context );
					break;
				
				case 'richtext': 
					// add the .prose class to apply the correct styles
					$class_names    .= ' prose';
					$output_content .= $content['value'];
					break;

				case 'meta':
					$output_content .= $this->get_meta( get_the_ID(), $content['value'] );
					break;

				case 'property':
					$tmp_output = $this->get_context_property( $context, $content['value'] );
					if ( ! is_null( $tmp_output ) ) {
						$output_content .= $tmp_output;
					}
					break;
			}
			$output .= do_shortcode( $output_content );
		}

		// add in any children
		$children = Gust_Utilities::array_has_key( 'children', $tag ) ? $tag['children'] : null;

		// check if we allow children and if there are any in the current override
		// this is only for components
		if ( Gust_Utilities::array_has_key( 'allowChildren', $tag ) && $tag['allowChildren'] ) {
			$children = Gust_Utilities::array_has_key( 'children', $current_details ) ? $current_details['children'] : null;
			if ( Gust_Utilities::array_has_key( 'children', $repeater_details ) ) {
				if ( $children ) {
					$children = array_merge( $children, $repeater_details['children'] );
				} else {
					$children = $repeater_details['children'];
				}
			}
		}

		if ( $children ) {
			foreach ( $children as $child ) {
				$use_the_loop = isset( $child['useTheLoop'] ) && $child['useTheLoop'];
				if (
				(
				// if we are using the loop.
				$use_the_loop
				// and if we are using a loop.
				|| ( isset( $child['useContextLoop'] ) && $child['useContextLoop'] )
				// or it's a single query.
				|| ( Gust_Utilities::array_has_key( 'context', $tag ) && Gust_Utilities::array_has_key( 'type', $tag['context'] ) && $tag['context']['type'] === 'single' ) )
				&&
				// if it's a WP_Query or we're using the default query.
				( ! $context || is_a( $context, 'WP_Query' ) )

				) {
					global $wp_query;
					$context_query = $context ? $context : $wp_query;
					if ( $context_query->have_posts() ) {
						while ( $context_query->have_posts() ) {
							$context_query->the_post();
							// don't pass the context down to items inside this one.
							$output .= $this->render_tag( $child, null, $component_details, $repeater_items, $repeater_item, $parent_component_details );
						}
						// reset postdata here.
						if ( ! $use_the_loop ) {
							wp_reset_postdata();
						}
					}
				} elseif (
				// if we have an iterable context and we want to loop it, do that
				// we need to make sure it's iterable though
				// because with a filtered context, someone could pass anything in
				// in reality I'd just say, don't do that!
				// but we'll be a bit forgiving and check this end
				isset( $child['useContextLoop'] ) && $child['useContextLoop'] && is_iterable( $context )
				) {
					foreach ( $context as $item ) {
						$output .= $this->render_tag( $child, $item, $component_details, $repeater_items, $repeater_item, $parent_component_details );
					}
				} else {
					if ( Gust_Utilities::array_has_key( 'useRepeater', $child ) ) {
						foreach ( $repeater_items as $repeat_idx => $item ) {
							  // add 'gust-active-item' to the first item
							if ( $repeat_idx === 0 ) {
								$item['active'] = true;
							}
							$output .= $this->render_tag( $child, $context, $component_details, $repeater_items, $item, $parent_component_details );
						}
					} else {
						$child_component_details = $component_details;
						if ( Gust_Utilities::array_has_key( 'componentDetails', $child ) ) {
							$child_component_details = array_merge( $child_component_details, $child['componentDetails'] );
						}
						$output .= $this->render_tag( $child, $context, $child_component_details, $repeater_items, $repeater_item, $parent_component_details );
					}
				}
			}
		}

		// add any additional class names
		$class_names .= ' ' . $extra_class_names;
		$class_names .= ' ' . $reference_class_names;
		$class_names  = apply_filters( 'gust_tag_class_names', $class_names, $context, $tag, $component_details, $parent_component_details );
		if ( Gust_Utilities::array_has_key( 'id', $tag ) ) {
			$class_names = apply_filters( "gust_{$tag['id']}_class_names", $class_names, $context, $component_details, $tag );
			$attributes  = apply_filters( "gust_{$tag['id']}_attributes", $attributes, $context, $component_details, $tag );
		}
		if ( $repeater_item && Gust_Utilities::array_has_key( 'active', $repeater_item ) ) {
			$class_names .= ' gust-active-item';
		}

		$attributes = apply_filters( 'gust_tag_attributes', $attributes, $context, $tag, $component_details, $parent_component_details );

		// Convert attributes to strings.
		$attributes = array_map( array( $this, 'get_attribute_string' ), array_keys( $attributes ), $attributes );
		$attributes = implode( ' ', $attributes );
		$output     = apply_filters( 'gust_tag_pre_output', $output, $context, $tag, $component_details, $parent_component_details );
		$output     = "<{$tag_name} class=\"{$class_names}\" {$attributes}>{$output}</{$tag_name}>";
		$output     = apply_filters( 'gust_tag_post_output', $output, $context, $tag, $component_details, $parent_component_details );
		// if it's a component, filter the output
		if ( Gust_Utilities::array_has_key( 'id', $tag ) ) {
			$output = apply_filters( "gust_{$tag['id']}_output", $output, $context, $component_details, $parent_component_details );
		}
		return $output;
	}

	/**
	 * Takes a list of nodes and returns the list of class names used
	 * 
	 * @param object $node A node to get the class list from.
	 * @param array  $class_names a list of class names.
	 * @param object $component_details Any details to render the component.
	 * @param object $repeater_items Items used in the repeater.
	 * @param object $repeater_item The current repeater item.
	 */
	public function get_class_list_from_node_list( $node, $class_names = array(), $component_details = array(), $repeater_items = null, $repeater_item = null, $parent_component_id = null ) {

		// set the repeater items.
		if ( Gust_Utilities::array_has_key( 'repeaterItems', $node ) ) {
			$repeater_items = $node['repeaterItems'];
		}

		// if it's a component, render the component instead of this.
		if ( Gust_Utilities::array_has_key( 'instanceOfComponent', $node ) ) {
			$component = apply_filters( 'gust_render_component', null, $node['instanceOfComponent'] );
			if ( ! $component ) {
				return '';
			}
			$scoped_component_details = isset( $node['componentDetails'] ) ? $node['componentDetails'] : $component_details;
			return $this->get_class_list_from_node_list( $component, $class_names, $scoped_component_details, $repeater_items, $repeater_item, $node['instanceOfComponent'] );
		}

		// set the current component details.
		$current_details = array();
		if ( Gust_Utilities::array_has_key( 'id', $node ) && Gust_Utilities::array_has_key( $node['id'], $component_details ) ) {
			$current_details = $component_details[ $node['id'] ];
		}

		// set the current repeater details.
		$repeater_details = array();
		if ( $repeater_item && Gust_Utilities::array_has_key( 'id', $node ) && Gust_Utilities::array_has_key( $node['id'], $repeater_item['componentDetails'] ) ) {
			$repeater_details = $repeater_item['componentDetails'][ $node['id'] ];
		}

		// sort out any classes.
		$tmp_class_names = isset( $node['classNames'] ) ? $node['classNames'] : '';
		if ( Gust_Utilities::array_has_key( 'classNames', $current_details ) ) {
			$tmp_class_names = $current_details['classNames'];
		}

		// add any extra class names.
		if ( Gust_Utilities::array_has_key( 'extraClassNames', $current_details ) ) {
			$tmp_class_names .= ' ' . $current_details['extraClassNames'];
		}

		// add optional classNames.
		if ( Gust_Utilities::array_has_key( 'options', $node ) ) {
			$overrides = Gust_Utilities::array_has_key( 'options', $current_details ) ? $current_details['options'] : array();
			foreach ( $node['options'] as $option ) {
				// look for a value.
				$option_value = Gust_Utilities::array_has_key( $option['name'], $overrides ) ? $overrides[ $option['name'] ] : null;
				if ( empty( $option_value ) && $option['type'] === 'select' ) {
					$option_value = $option['options'][0]['value'];
				}
				if ( empty( $option_value ) && $option['type'] === 'text' && Gust_Utilities::array_has_key( 'defaultValue', $option ) ) {
					$option_value = $option['defaultValue'];
				}

				$media_option_value = $this->get_media_url_from_value( $option_value );
				if ( $media_option_value ) {
					$option_value = $media_option_value;
				}

				$option_class_names = '';
				switch ( $option['modify'] ) {
					
					case 'className': 
						$option_class_names = $option_value;
						break;
					
				}
				$option_class_names = apply_filters( 'gust_option_class_names', $option_class_names, $option, $option_value, $current_details, $node );
				$tmp_class_names   .= ' ' . $option_class_names;
			}
		}

		// add class names from references.
		if ( Gust_Utilities::array_has_key( 'references', $node ) && $parent_component_id && Gust_Utilities::array_has_key( $parent_component_id, $component_details ) ) {
			$reference_class_names = $this->get_class_names_from_references( $node, $component_details[ $parent_component_id ] );
			$tmp_class_names      .= ' ' . $reference_class_names;
		}

		// add in any children.
		$children = Gust_Utilities::array_has_key( 'children', $node ) ? $node['children'] : null;

		// check if we allow children and if there are any in the current override
		// this is only for components.
		if ( Gust_Utilities::array_has_key( 'allowChildren', $node ) && $node['allowChildren'] ) {
			$children = Gust_Utilities::array_has_key( 'children', $current_details ) ? $current_details['children'] : null;
			if ( Gust_Utilities::array_has_key( 'children', $repeater_details ) ) {
				if ( $children ) {
					$children = array_merge( $children, $repeater_details['children'] );
				} else {
					$children = $repeater_details['children'];
				}
			}
		}

		if ( $children ) {
			foreach ( $children as $child ) {
				if ( Gust_Utilities::array_has_key( 'useRepeater', $child ) ) {
					foreach ( $repeater_items as $item ) {
						$child_class_names = $this->get_class_list_from_node_list( $child, $class_names, $component_details, $repeater_items, $item, $parent_component_id );
						$class_names       = array_merge( $class_names, $child_class_names );
					}
				} else {
					$child_component_details = $component_details;
					if ( Gust_Utilities::array_has_key( 'componentDetails', $child ) ) {
						$child_component_details = array_merge( $child_component_details, $child['componentDetails'] );
					}
					$child_class_names = $this->get_class_list_from_node_list( $child, $class_names, $child_component_details, $repeater_items, $repeater_item, $parent_component_id );
					$class_names       = array_merge( $class_names, $child_class_names );
				}
			}
		}

		// add any additional class names.
		if ( Gust_Utilities::array_has_key( 'id', $node ) ) {
			$tmp_class_names = apply_filters( "gust_{$node['id']}_node_class_name_list", $tmp_class_names, $component_details );
		}

		if ( $repeater_item ) {
			$tmp_class_names .= ' gust-active-item';
		}

		$class_names = array_unique( array_merge( $class_names, explode( ' ', $tmp_class_names ) ) );
		$class_names = apply_filters( 'gust_node_class_name_list', $class_names, $node );
		return $class_names;
	}

	/**
	 * Takes a filepath and renders the JSON content
	 */
	public function render_json_file( $filepath, $echo = true ) {
		try {
			$gust_content = json_decode( file_get_contents( $filepath ), true );
			$output       = $this->render_tag( $gust_content );
			if ( $echo ) {
				echo $output;
			} else {
				return $output;
			}
		} catch ( Exception $e ) {
			return '';
		}
	}

	/**
	 * Ensures that any special attributes are filtered correctly
	 *
	 * @param mixed $attributes A list of any attributes.
	 */
	public function format_attributes( $attributes ) {

		if ( Gust_Utilities::array_has_key( 'for', $attributes ) && '#' !== substr( $attributes['for'], 0, 1 ) ) {
			$attributes['for'] = '#' . $attributes['for'];
		}

		return $attributes;
	}
}
