<?php
/**
 * Abstraction for Gust API
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

class Gust_Api {

	private $api_url;
	private $is_free;
	const THEME_INFO_TRANSIENT = 'gust_theme_info';

	public function __construct( $api_url, $is_free ) {
		$this->api_url = $api_url;
		$this->is_free = $is_free;
	}

	private function include_wp_http() {
		if ( ! class_exists( 'WP_Http' ) ) {
			include_once ABSPATH . WPINC . '/class-http.php';
		}
	}

	public function get_theme_info( $purchase_code, $use_cache = true ) {
    	/*
		$this->include_wp_http();

		if ( $use_cache ) {
			$cached = get_transient( self::THEME_INFO_TRANSIENT );
			if ( $cached ) {
				return $cached;
			}
		}

		// get the info from the cache
		$remote = wp_remote_post(
			$this->api_url . '/v1/theme-info',
			array(
				'timeout'     => 10,
				'headers'     => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'        => wp_json_encode(
					array(
						'purchaseCode' => $purchase_code,
						'domain'       => get_site_url(),
						'isFree'       => $this->is_free,
					)
				),
				'data_format' => 'body',
			)
		);

		if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
			set_transient( self::THEME_INFO_TRANSIENT, $remote, 43200 ); // 12 hours cache
			return $remote;
		} else {
			return null;
		}
        */
	}
}