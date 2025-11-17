<?php
/**
 * Plugin Management Abilities
 *
 * @package WP_Abilities_API_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Register the Plugin List ability
add_action(
	'wp_abilities_api_init',
	function () {
		wp_register_ability(
			'plugins/get-plugins',
			array(
				'label'               => __( 'Plugin List', 'wp-abilities-api-demo' ),
				'description'         => __( 'Retrieves a list of all installed WordPress plugins with their names and slugs.', 'wp-abilities-api-demo' ),
				'category'            => 'abilities-api-demo',
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the plugin list retrieval completed successfully.', 'wp-abilities-api-demo' ),
						),
						'plugins' => array(
							'type'        => 'array',
							'description' => __( 'List of installed plugins.', 'wp-abilities-api-demo' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'name'    => array(
										'type'        => 'string',
										'description' => __( 'Plugin name.', 'wp-abilities-api-demo' ),
									),
									'slug'    => array(
										'type'        => 'string',
										'description' => __( 'Plugin slug/directory.', 'wp-abilities-api-demo' ),
									),
									'file'    => array(
										'type'        => 'string',
										'description' => __( 'Main plugin file path.', 'wp-abilities-api-demo' ),
									),
									'status'  => array(
										'type'        => 'string',
										'description' => __( 'Plugin status (active/inactive).', 'wp-abilities-api-demo' ),
									),
									'version' => array(
										'type'        => 'string',
										'description' => __( 'Plugin version.', 'wp-abilities-api-demo' ),
									),
								),
							),
						),
						'error'   => array(
							'type'        => 'string',
							'description' => __( 'Error message if the retrieval failed.', 'wp-abilities-api-demo' ),
						),
					),
				),
				'execute_callback'    => 'ai_experiments_get_plugin_list',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true, // make this ability publicly accessible on the default MCP server
						'type'   => 'tool',
					),
				),
			)
		);
	}
);

/**
 * Retrieve list of all installed WordPress plugins.
 *
 * @return array JSON response with plugin list or error.
 */
function ai_experiments_get_plugin_list() {
	if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
		error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Starting plugin list retrieval with input: ' . print_r( $input, true ) );
	}

	try {
		// Check if get_plugins function is available
		$get_plugins_exists = function_exists( 'get_plugins' );
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: get_plugins function exists: ' . ( $get_plugins_exists ? 'true' : 'false' ) );
		}

		if ( ! $get_plugins_exists ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Loading plugin.php from wp-admin/includes' );
			}
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Get all installed plugins
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Calling get_plugins()' );
		}
		$all_plugins  = get_plugins();
		$plugin_count = count( $all_plugins );

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Found ' . $plugin_count . ' total plugins' );
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$active_count   = count( $active_plugins );

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Found ' . $active_count . ' active plugins' );
		}

		$network_active = array();
		$is_multisite   = is_multisite();

		// Get network active plugins if multisite
		if ( $is_multisite ) {
			$network_active = get_site_option( 'active_sitewide_plugins', array() );
			$network_count  = count( $network_active );
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Multisite detected, found ' . $network_count . ' network active plugins' );
			}
		} elseif ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Single site installation' );
		}

		$plugins = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			// Extract slug from plugin file path
			$plugin_slug = dirname( $plugin_file );
			if ( $plugin_slug === '.' ) {
				// Single file plugin
				$plugin_slug = basename( $plugin_file, '.php' );
			}

			// Determine status
			$status = 'inactive';
			if ( in_array( $plugin_file, $active_plugins ) || array_key_exists( $plugin_file, $network_active ) ) {
				$status = 'active';
			}

			$plugins[] = array(
				'name'    => $plugin_data['Name'],
				'slug'    => $plugin_slug,
				'file'    => $plugin_file,
				'status'  => $status,
				'version' => $plugin_data['Version'],
			);

			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Processed plugin: ' . $plugin_data['Name'] . ' (' . $plugin_slug . ') - ' . $status );
			}
		}

		$final_count = count( $plugins );
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Plugin list retrieval completed successfully with ' . $final_count . ' plugins' );
		}

		return array(
			'success' => true,
			'plugins' => $plugins,
		);

	} catch ( Exception $e ) {
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Exception caught: ' . $e->getMessage() );
			error_log( 'AI_EXPERIMENTS_PLUGIN_LIST: Exception trace: ' . $e->getTraceAsString() );
		}
		return array(
			'success' => false,
			'error'   => 'Failed to retrieve plugin list: ' . $e->getMessage(),
		);
	}
}

// Register the Install Plugin ability
add_action(
	'wp_abilities_api_init',
	function () {
		wp_register_ability(
			'plugins/install-plugin',
			array(
				'label'               => __( 'Install Plugin', 'wp-abilities-api-demo' ),
				'description'         => __( 'Installs and activates a plugin from the WordPress.org repository.', 'wp-abilities-api-demo' ),
				'category'            => 'abilities-api-demo',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'slug' => array(
							'type'        => 'string',
							'description' => __( 'The plugin slug from WordPress.org.', 'wp-abilities-api-demo' ),
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
				'execute_callback'    => 'ai_experiments_install_plugin',
				'permission_callback' => function () {
					return current_user_can( 'install_plugins' );
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
 * Install and activate a plugin from WordPress.org.
 *
 * @param array $input Input data containing the plugin slug.
 *
 * @return array JSON response with success status and message or error.
 */
function ai_experiments_install_plugin( $input ) {
	if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
		error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Starting plugin installation with input: ' . print_r( $input, true ) );
	}

	// Validate input
	if ( ! isset( $input['slug'] ) || empty( $input['slug'] ) ) {
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Invalid input - missing slug' );
		}
		return array(
			'success' => false,
			'error'   => __( 'Plugin slug is required.', 'wp-abilities-api-demo' ),
		);
	}

	$slug = sanitize_text_field( $input['slug'] );

	if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
		error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Sanitized slug: ' . $slug );
	}

	try {
		// Load required WordPress files
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Required files loaded' );
		}

		// Check if plugin is already installed
		$all_plugins = get_plugins();
		$plugin_file = null;

		foreach ( $all_plugins as $file => $plugin_data ) {
			$plugin_slug = dirname( $file );
			if ( $plugin_slug === '.' ) {
				$plugin_slug = basename( $file, '.php' );
			}
			if ( $plugin_slug === $slug ) {
				$plugin_file = $file;
				break;
			}
		}

		if ( $plugin_file ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Plugin already installed: ' . $plugin_file );
			}

			// Plugin already installed, just activate it
			$result = activate_plugin( $plugin_file );
			if ( is_wp_error( $result ) ) {
				if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
					error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Activation failed: ' . $result->get_error_message() );
				}
				return array(
					'success' => false,
					'error'   => __( 'Failed to activate plugin: ', 'wp-abilities-api-demo' ) . $result->get_error_message(),
				);
			}

			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Plugin activated successfully' );
			}

			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %s: plugin slug */
					__( 'Plugin "%s" was already installed and has been activated.', 'wp-abilities-api-demo' ),
					$slug
				),
			);
		}

		// Get plugin information from WordPress.org
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Fetching plugin info from WordPress.org' );
		}

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => $slug,
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'downloadlink'      => true,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: plugins_api() failed: ' . $api->get_error_message() );
			}
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: error message */
					__( 'Failed to fetch plugin information: %s', 'wp-abilities-api-demo' ),
					$api->get_error_message()
				),
			);
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Plugin info retrieved, downloading from: ' . $api->download_link );
		}

		// Install the plugin
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( is_wp_error( $result ) ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Installation failed: ' . $result->get_error_message() );
			}
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: error message */
					__( 'Failed to install plugin: %s', 'wp-abilities-api-demo' ),
					$result->get_error_message()
				),
			);
		}

		if ( ! $result ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Installation failed with no error message' );
			}
			return array(
				'success' => false,
				'error'   => __( 'Failed to install plugin.', 'wp-abilities-api-demo' ),
			);
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Plugin installed successfully' );
		}

		// Get the plugin file from the upgrader
		$plugin_file = $upgrader->plugin_info();
		if ( ! $plugin_file ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Could not determine plugin file after installation' );
			}
			return array(
				'success' => false,
				'error'   => __( 'Plugin installed but could not determine plugin file for activation.', 'wp-abilities-api-demo' ),
			);
		}

		// Activate the plugin
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Activating plugin: ' . $plugin_file );
		}

		$activate_result = activate_plugin( $plugin_file );
		if ( is_wp_error( $activate_result ) ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Activation failed: ' . $activate_result->get_error_message() );
			}
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: error message */
					__( 'Plugin installed but failed to activate: %s', 'wp-abilities-api-demo' ),
					$activate_result->get_error_message()
				),
			);
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Plugin installed and activated successfully' );
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: plugin slug */
				__( 'Plugin "%s" installed and activated successfully.', 'wp-abilities-api-demo' ),
				$slug
			),
		);

	} catch ( Exception $e ) {
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Exception caught: ' . $e->getMessage() );
			error_log( 'AI_EXPERIMENTS_INSTALL_PLUGIN: Exception trace: ' . $e->getTraceAsString() );
		}
		return array(
			'success' => false,
			'error'   => sprintf(
				/* translators: %s: error message */
				__( 'Failed to install plugin: %s', 'wp-abilities-api-demo' ),
				$e->getMessage()
			),
		);
	}
}

// Register the Delete Plugin ability
add_action(
	'wp_abilities_api_init',
	function () {
		wp_register_ability(
			'plugins/delete-plugin',
			array(
				'label'               => __( 'Delete Plugin', 'wp-abilities-api-demo' ),
				'description'         => __( 'Deactivates and deletes an installed plugin. This is a destructive and idempotent operation.', 'wp-abilities-api-demo' ),
				'category'            => 'abilities-api-demo',
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'slug' => array(
							'type'        => 'string',
							'description' => __( 'The plugin slug/directory name.', 'wp-abilities-api-demo' ),
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
				'execute_callback'    => 'ai_experiments_delete_plugin',
				'permission_callback' => function () {
					return current_user_can( 'delete_plugins' );
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
 * Deactivate and delete a plugin. This is an idempotent operation.
 *
 * @param array $input Input data containing the plugin slug.
 *
 * @return array JSON response with success status and message or error.
 */
function ai_experiments_delete_plugin( $input ) {
	if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
		error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Starting plugin deletion with input: ' . print_r( $input, true ) );
	}

	// Validate input
	if ( ! isset( $input['slug'] ) || empty( $input['slug'] ) ) {
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Invalid input - missing slug' );
		}
		return array(
			'success' => false,
			'error'   => __( 'Plugin slug is required.', 'wp-abilities-api-demo' ),
		);
	}

	$slug = sanitize_text_field( $input['slug'] );

	if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
		error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Sanitized slug: ' . $slug );
	}

	try {
		// Load required WordPress files
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! function_exists( 'delete_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Required files loaded' );
		}

		// Find the plugin file
		$all_plugins = get_plugins();
		$plugin_file = null;

		foreach ( $all_plugins as $file => $plugin_data ) {
			$plugin_slug = dirname( $file );
			if ( $plugin_slug === '.' ) {
				$plugin_slug = basename( $file, '.php' );
			}
			if ( $plugin_slug === $slug ) {
				$plugin_file = $file;
				break;
			}
		}

		// Idempotency: If plugin doesn't exist, return success
		if ( ! $plugin_file ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Plugin not found, returning success (idempotent)' );
			}
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %s: plugin slug */
					__( 'Plugin "%s" is not installed (already deleted or never existed).', 'wp-abilities-api-demo' ),
					$slug
				),
			);
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Found plugin file: ' . $plugin_file );
		}

		// Check if plugin is active
		if ( is_plugin_active( $plugin_file ) ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Plugin is active, deactivating' );
			}

			// Deactivate the plugin
			deactivate_plugins( $plugin_file );

			// Verify deactivation
			if ( is_plugin_active( $plugin_file ) ) {
				if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
					error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Failed to deactivate plugin' );
				}
				return array(
					'success' => false,
					'error'   => __( 'Failed to deactivate plugin before deletion.', 'wp-abilities-api-demo' ),
				);
			}

			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Plugin deactivated successfully' );
			}
		} elseif ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Plugin is not active' );
		}

		// Delete the plugin
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Deleting plugin' );
		}

		$result = delete_plugins( array( $plugin_file ) );

		if ( is_wp_error( $result ) ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Deletion failed: ' . $result->get_error_message() );
			}
			return array(
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: error message */
					__( 'Failed to delete plugin: %s', 'wp-abilities-api-demo' ),
					$result->get_error_message()
				),
			);
		}

		if ( ! $result ) {
			if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
				error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Deletion failed with no error message' );
			}
			return array(
				'success' => false,
				'error'   => __( 'Failed to delete plugin.', 'wp-abilities-api-demo' ),
			);
		}

		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Plugin deleted successfully' );
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: plugin slug */
				__( 'Plugin "%s" deactivated and deleted successfully.', 'wp-abilities-api-demo' ),
				$slug
			),
		);

	} catch ( Exception $e ) {
		if ( defined( 'WP_ABILITIES_API_DEMO_DEBUG' ) && WP_ABILITIES_API_DEMO_DEBUG ) {
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Exception caught: ' . $e->getMessage() );
			error_log( 'AI_EXPERIMENTS_DELETE_PLUGIN: Exception trace: ' . $e->getTraceAsString() );
		}
		return array(
			'success' => false,
			'error'   => sprintf(
				/* translators: %s: error message */
				__( 'Failed to delete plugin: %s', 'wp-abilities-api-demo' ),
				$e->getMessage()
			),
		);
	}
}
