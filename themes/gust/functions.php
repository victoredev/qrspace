<?php
/**
 * The main Gust theme file
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

if ( ! isset( $content_width ) ) {
	$content_width = 992;
}

require_once 'includes/class-gust.php';
Gust::get_instance();
