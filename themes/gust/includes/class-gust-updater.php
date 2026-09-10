<?php
/**
 * Handles updating Gust
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

/**
 * Most of this was taken from this site:
 * https://rudrastyh.com/wordpress/self-hosted-plugin-update.html
 */

class Gust_Updater {

	const TRANSIENT = 'gust_update';
	private $api;
	private $theme_slug;
	private $version;
	private $admin;

	public function __construct( $api, $version, $admin ) {
		$this->api       = $api;
		$this->theme_slug = 'gust';
		$this->version   = $version;
		$this->admin = $admin;

		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'next_update' ) );
	}

	public function next_update( $checked_data ) {
    /*
		$remote = $this->api->get_theme_info( $this->admin->get_option( 'purchase_code' ) );
		if ( ! $remote ) {
			return $checked_data;
		}

		$remote = json_decode( $remote['body'] );
		if ( $remote && version_compare( $this->version, $remote->version, '<' ) && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' ) ) {
			$checked_data->response[ $this->theme_slug ] = array(
				'package'     => $remote->package,
				'new_version' => $remote->version,
				'url'         => $remote->homepage,
			);
		}
		return $checked_data;
        */
	}
}