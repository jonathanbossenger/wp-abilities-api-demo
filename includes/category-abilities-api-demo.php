<?php
/**
 * Abilities API Demo Category Registration
 *
 * @package WP_Abilities_API_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Register the abilities-api-demo category
add_action( 'abilities_api_categories_init', function () {
	wp_register_ability_category( 'abilities-api-demo', array(
		'label'       => __( 'Abilities API Demo', 'wp-abilities-api-demo' ),
		'description' => __( 'Demo abilities for the WordPress Abilities API.', 'wp-abilities-api-demo' ),
	) );
} );
