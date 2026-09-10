<?php
/**
 * Gust utilities
 *
 * @package Gust
 */

defined( 'ABSPATH' ) || die();

/**
 * Adds a bunch of utility methods
 */
class Gust_Utilities {
	/**
	 * Determines whether an array has a key.
	 *
	 * @param string $key The key to check for.
	 * @param array  $array The array to check in.
	 */
	public static function array_has_key( $key, $array ) {
		return array_key_exists( $key, $array ) && ! empty( $array[ $key ] );
	}
}
