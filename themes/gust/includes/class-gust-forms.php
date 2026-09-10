<?php
/**
 * Gust form management
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

/**
 * Handles all the logic for form submission etc.
 */
class Gust_Forms {


	/**
	 * The nonce key.
	 *
	 * @var string
	 */
	private $nonce_key = 'gust-form-nonce';

	/**
	 * Instance of the admin class.
	 *
	 * @var Gust_Admin
	 */
	private $admin;

	/**
	 * The theme URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * The script version
	 *
	 * @var string
	 */
	private $script_version;

	/**
	 * The constructor
	 *
	 * @param Gust_Admin $admin Instance of the admin class.
	 * @param string     $url The theme URL.
	 * @param string     $script_version The script version.
	 */
	public function __construct( $admin, $url, $script_version ) {
		$this->admin          = $admin;
		$this->url            = $url;
		$this->script_version = $script_version;

		add_filter( 'gust_form_attributes', array( $this, 'form_attributes' ) );
		add_filter( 'gust_tag_attributes', array( $this, 'tag_attributes' ) );
		add_action( 'wp_ajax_gust_form_submission', array( $this, 'gust_form_submission' ) );
		add_action( 'wp_ajax_nopriv_gust_form_submission', array( $this, 'gust_form_submission' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_filter( 'gust_tag_pre_output', array( $this, 'tag_pre_output' ), 10, 5 );
	}

	/**
	 * Registers scripts required for the form.
	 */
	public function register_scripts() {
		wp_register_script( 'gust-forms', $this->url . 'assets/js/components/forms.js', array( 'jquery' ), $this->script_version, true );
		wp_localize_script( 
			'gust-forms',
			'GustForms',
			array(
				'action'   => 'gust_form_submission',
				'adminUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( $this->nonce_key ),
			)
		);
	}

	/**
	 * Adds the required attributes to the form so that we can identify it
	 *
	 * @param array $attributes The form attributes.
	 */
	public function form_attributes( $attributes ) {
		$attributes['data-gust-form'] = 'true';
		return $attributes;
	}

	/**
	 * Modifies some attributes before they are output
	 *
	 * @param array $attributes The form attributes.
	 */
	public function tag_attributes( $attributes ) {
		if ( isset( $attributes['required'] ) && 'false' === $attributes['required'] ) {
			unset( $attributes['required'] );
		}
		return $attributes;
	}

	/**
	 * Handles form submission and verifies inputs.
	 */
	public function gust_form_submission() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), $this->nonce_key ) ) {
			exit( 'Invalid nonce' );
		}

		if ( ! isset( $_REQUEST['gust_data'] ) || ! is_array( $_REQUEST['gust_data'] ) ) {
			wp_send_json_error( array( 'message' => 'Missing Gust data' ), 400 );
			return;
		}

		$to = $this->admin->get_option( 'form_recipients', '' );
		$to = explode( ',', $to );
		$to = array_map( 'trim', $to );
		// leaving this empty will remove any falsy values.
		$to = array_filter( $to );
		if ( empty( $to ) ) {
			$to[] = get_bloginfo( 'admin_email' );
		}

		if ( empty( $to ) ) {
			wp_send_json_error( array( 'message' => 'Missing recipients' ) );
		}

		$message = "Hi there, someone filled out a form on your website with the following info:\n\n";

		foreach ( $_REQUEST['gust_data'] as $form_value ) {
			if ( 'gust_control' === $form_value['name'] ) {
				if ( ! empty( $form_value['value'] ) ) {
					wp_send_json_error( array( 'message' => 'Form validation failed' ), 400 );
					return 2;
				} else {
					continue;
				}
			}
			$message .= $form_value['name'] . ': ' . wp_unslash( $form_value['value'] ) . "\n";
		}

		$from = $this->admin->get_option( 'form_sender', '' );
		if ( ! $from ) {
			$from = get_bloginfo( 'admin_email' );
		}

		$form_details = array(
			'to'      => $to,
			'from'    => '<' . $from . '>',
			'subject' => 'Form submission on your website',
			'message' => $message,
			'headers' => array(
				'Content-Type: text/plain; charset="utf-8";',
			),
		);
		$form_details = apply_filters( 'gust_form_details', $form_details );

		$sent = wp_mail(
			$form_details['to'],
			$form_details['subject'],
			$form_details['message'],
			array_merge(
				$form_details['headers'],
				array(
					'From: ' . $form_details['from'],
				)
			)
		);

		do_action( 'gust_form_sent', $form_details );

		wp_send_json(
			array(
				'message' => 'Form submission successful',
				'sent'    => $sent,
			) 
		);
	}

	/**
	 * Filters the output of various components.
	 *
	 * @param string $output The HTML to putout.
	 * @param mixed  $context Any context that is applied.
	 * @param object $tag Details of the tag.
	 * @param mixed  $component_details The component details, if any, otherwise null.
	 * @param mixed  $parent_component_details The parent component details, if any, otherwise null.
	 */
	public function tag_pre_output( $output, $context, $tag, $component_details, $parent_component_details ) {
		if ( ! Gust_Utilities::array_has_key( 'id', $tag ) ) {
			return $output;
		}
		switch ( $tag['id'] ) {
			case 'select-input': 
				return $this->select_input_output( $parent_component_details );

			default: 
				return $output;

		}
	}

	/**
	 * Outputs HTML options for the select component.
	 *
	 * @param mixed $parent_component_details The parent component details, if any, otherwise null.
	 */
	private function select_input_output( $parent_component_details ) {
		$options = '<option selected disabled>Pick one</option>';
		if ( ! $parent_component_details && ! Gust_Utilities::array_has_key( 'options', $parent_component_details ) && ! Gust_Utilities::array_has_key( 'selectOptions', $parent_component_details['options'] ) ) {
			return $options;
		}

		$option_lines = explode( "\n", $parent_component_details['options']['selectOptions'] );
		foreach ( $option_lines as $option_line ) {
			$trimmed_line = trim( $option_line );
			if ( empty( $trimmed_line ) ) {
				continue;
			}
			$value_labels = explode( '=', $trimmed_line );
			$value        = $value_labels[0];
			$label        = Gust_Utilities::array_has_key( 1, $value_labels ) ? $value_labels[1] : $value_labels[0];
			$options     .= '<option value="' . esc_attr( $value ) . '">' . esc_html( $label ) . '</option>';
		}
		return $options;
	}
}
