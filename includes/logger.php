<?php
/**
 * Logger functionality for WP Abilities API Demo
 *
 * @package WP_Abilities_API_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Log data to the WordPress debug log when debugging is enabled.
 *
 * @param mixed $data The data to log. Can be a string, array, or object.
 *
 * @return void
 */
function wp_abilities_demo_log( $data ) {
	if ( ! defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) || ! WP_ABILITIES_API_DEMO_DEBUG ) {
		return;
	}
	if ( is_array( $data ) || is_object( $data ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
		error_log( print_r( $data, true ) );
	} else {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $data );
	}
}
