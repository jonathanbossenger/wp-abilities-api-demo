<?php
/**
 * Theme Management Abilities
 *
 * @package WP_Abilities_API_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Register the Update Themes ability
add_action(
	'wp_abilities_api_init',
	function () {
		wp_register_ability(
			'themes/update-themes',
			array(
				'label'               => __( 'Update Themes', 'wp-abilities-api-demo' ),
				'description'         => __( 'Updates all WordPress themes that have available updates.', 'wp-abilities-api-demo' ),
				'category'            => 'abilities-api-demo',
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'        => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the operation completed successfully.', 'wp-abilities-api-demo' ),
						),
						'updated_themes' => array(
							'type'        => 'array',
							'description' => __( 'List of themes that were updated.', 'wp-abilities-api-demo' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'name'         => array(
										'type'        => 'string',
										'description' => __( 'Theme name.', 'wp-abilities-api-demo' ),
									),
									'slug'         => array(
										'type'        => 'string',
										'description' => __( 'Theme slug/directory.', 'wp-abilities-api-demo' ),
									),
									'old_version'  => array(
										'type'        => 'string',
										'description' => __( 'Previous version.', 'wp-abilities-api-demo' ),
									),
									'new_version'  => array(
										'type'        => 'string',
										'description' => __( 'Updated version.', 'wp-abilities-api-demo' ),
									),
								),
							),
						),
						'message'        => array(
							'type'        => 'string',
							'description' => __( 'Success message.', 'wp-abilities-api-demo' ),
						),
						'error'          => array(
							'type'        => 'string',
							'description' => __( 'Error message if the operation failed.', 'wp-abilities-api-demo' ),
						),
					),
				),
				'execute_callback'    => 'wp_abilities_demo_update_themes',
				'permission_callback' => function () {
					return current_user_can( 'update_themes' );
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
 * Update all WordPress themes that have available updates.
 *
 * @return array JSON response with list of updated themes or error.
 */
function wp_abilities_demo_update_themes() {
	wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Starting theme update process' );

	try {
		// Load required WordPress files
		if ( ! function_exists( 'wp_get_themes' ) ) {
			require_once ABSPATH . 'wp-includes/theme.php';
		}
		if ( ! function_exists( 'get_theme_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}
		if ( ! class_exists( 'Theme_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}
		if ( ! function_exists( 'request_filesystem_credentials' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Required files loaded' );

		// Force check for theme updates
		wp_update_themes();

		// Get themes with available updates
		$theme_updates = get_theme_updates();

		if ( empty( $theme_updates ) ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: No theme updates available' );
			return array(
				'success'        => true,
				'updated_themes' => array(),
				'message'        => __( 'No theme updates available.', 'wp-abilities-api-demo' ),
			);
		}

		$theme_count = count( $theme_updates );
		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Found ' . $theme_count . ' theme(s) with updates available' );

		// Prepare the upgrader
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Theme_Upgrader( $skin );

		$updated_themes = array();
		$errors         = array();

		// Update each theme
		foreach ( $theme_updates as $stylesheet => $theme ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Updating theme: ' . $theme->get( 'Name' ) . ' (' . $stylesheet . ')' );

			$old_version = $theme->get( 'Version' );

			// Perform the update
			$result = $upgrader->upgrade( $stylesheet );

			if ( is_wp_error( $result ) ) {
				wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Failed to update theme ' . $stylesheet . ': ' . $result->get_error_message() );
				$errors[] = sprintf(
					/* translators: 1: theme name, 2: error message */
					__( 'Failed to update "%1$s": %2$s', 'wp-abilities-api-demo' ),
					$theme->get( 'Name' ),
					$result->get_error_message()
				);
				continue;
			}

			if ( ! $result ) {
				wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Failed to update theme ' . $stylesheet . ' with no error message' );
				$errors[] = sprintf(
					/* translators: %s: theme name */
					__( 'Failed to update "%s".', 'wp-abilities-api-demo' ),
					$theme->get( 'Name' )
				);
				continue;
			}

			// Get the new version
			$updated_theme = wp_get_theme( $stylesheet );
			$new_version   = $updated_theme->get( 'Version' );

			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Successfully updated theme ' . $stylesheet . ' from ' . $old_version . ' to ' . $new_version );

			$updated_themes[] = array(
				'name'        => $theme->get( 'Name' ),
				'slug'        => $stylesheet,
				'old_version' => $old_version,
				'new_version' => $new_version,
			);
		}

		// Build response
		if ( empty( $updated_themes ) && ! empty( $errors ) ) {
			wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: All theme updates failed' );
			return array(
				'success' => false,
				'error'   => implode( ' ', $errors ),
			);
		}

		$success_count = count( $updated_themes );
		wp_abilities_demo_log( 'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Theme update process completed. Updated ' . $success_count . ' theme(s)' );

		$message = sprintf(
			/* translators: %d: number of themes updated */
			_n(
				'Successfully updated %d theme.',
				'Successfully updated %d themes.',
				$success_count,
				'wp-abilities-api-demo'
			),
			$success_count
		);

		if ( ! empty( $errors ) ) {
			$message .= ' ' . __( 'Some updates failed: ', 'wp-abilities-api-demo' ) . implode( ' ', $errors );
		}

		return array(
			'success'        => true,
			'updated_themes' => $updated_themes,
			'message'        => $message,
		);

	} catch ( Exception $e ) {
		wp_abilities_demo_log(
			array(
				'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Exception caught: ' . $e->getMessage(),
				'WP_ABILITIES_API_DEMO_UPDATE_THEMES: Exception trace: ' . $e->getTraceAsString(),
			)
		);
		return array(
			'success' => false,
			'error'   => sprintf(
				/* translators: %s: error message */
				__( 'Failed to update themes: %s', 'wp-abilities-api-demo' ),
				$e->getMessage()
			),
		);
	}
}
