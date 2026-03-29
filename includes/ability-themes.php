<?php
/**
 * Theme Management Abilities
 *
 * @package WP_Abilities_API_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Register the Install Theme ability
add_action(
	'wp_abilities_api_init',
	function () {
		wp_register_ability(
			'themes/install-theme',
			array(
				'label'               => __( 'Install Theme', 'wp-abilities-api-demo' ),
				'description'         => __( 'Installs a theme from the WordPress.org repository.', 'wp-abilities-api-demo' ),
				'category'            => 'abilities-api-demo',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'slug' => array(
							'type'        => 'string',
							'description' => __( 'The theme slug from WordPress.org.', 'wp-abilities-api-demo' ),
						),
					),
					'required'   => array( 'slug' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the operation completed successfully.', 'wp-abilities-api-demo' ),
						),
						'message' => array(
							'type'        => 'string',
							'description' => __( 'Success message.', 'wp-abilities-api-demo' ),
						),
						'error'   => array(
							'type'        => 'string',
							'description' => __( 'Error message if the operation failed.', 'wp-abilities-api-demo' ),
						),
					),
				),
				'execute_callback'    => 'wp_abilities_demo_install_theme',
				'permission_callback' => function () {
					return current_user_can( 'install_themes' );
				},
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
				),
			)
		);
	}
);

/**
 * Install a theme from WordPress.org.
 *
 * @param array $input Input data containing the theme slug.
 *
 * @return array JSON response with success status and message or error.
 */
function wp_abilities_demo_install_theme( $input ) {
	wp_abilities_demo_log( array( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Starting theme installation with input:', $input ) );

	// Validate input
	if ( ! isset( $input['slug'] ) || empty( $input['slug'] ) ) {
		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Invalid input - missing slug' );
		return array(
			'success' => false,
			'error'   => __( 'Theme slug is required.', 'wp-abilities-api-demo' ),
		);
	}

	$slug = sanitize_text_field( $input['slug'] );

	wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Sanitized slug: ' . $slug );

	try {
		// Load required WordPress files
		if ( ! function_exists( 'themes_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
		}
		if ( ! class_exists( 'Theme_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Required files loaded' );

		// Check if theme is already installed
		$theme = wp_get_theme( $slug );
		if ( $theme->exists() ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Theme already installed: ' . $slug );
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %s: theme slug */
					__( 'Theme "%s" is already installed.', 'wp-abilities-api-demo' ),
					$slug
				),
			);
		}

		// Get theme information from WordPress.org
		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Fetching theme info from WordPress.org' );

		$api = themes_api(
			'theme_information',
			array(
				'slug'   => $slug,
				'fields' => array(
					'sections'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'downloadlink'      => true,
					'last_updated'      => false,
					'homepage'          => false,
					'tags'              => false,
					'screenshot_url'    => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: themes_api() failed: ' . $api->get_error_message() );
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: error message */
					__( 'Failed to fetch theme information: %s', 'wp-abilities-api-demo' ),
					$api->get_error_message()
				),
			);
		}

		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Theme info retrieved, downloading from: ' . $api->download_link );

		// Install the theme
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Theme_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Installation failed: ' . $result->get_error_message() );
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: error message */
					__( 'Failed to install theme: %s', 'wp-abilities-api-demo' ),
					$result->get_error_message()
				),
			);
		}

		if ( ! $result ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Installation failed with no error message' );
			return array(
				'success' => false,
				'error'   => __( 'Failed to install theme.', 'wp-abilities-api-demo' ),
			);
		}

		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_INSTALL_THEME: Theme installed successfully' );

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: theme slug */
				__( 'Theme "%s" installed successfully.', 'wp-abilities-api-demo' ),
				$slug
			),
		);

	} catch ( Exception $e ) {
		wp_abilities_demo_log(
			array(
				'WP_ABILITIES_API_DEMO_INSTALL_THEME: Exception caught: ' . $e->getMessage(),
				'WP_ABILITIES_API_DEMO_INSTALL_THEME: Exception trace: ' . $e->getTraceAsString(),
			)
		);
		return array(
			'success' => false,
			'error'   => sprintf(
				/* translators: %s: error message */
				__( 'Failed to install theme: %s', 'wp-abilities-api-demo' ),
				$e->getMessage()
			),
		);
	}
}
