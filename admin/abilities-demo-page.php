<?php
/**
 * Abilities API Demo Admin Page
 *
 * @package WP_Abilities_API_Demo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the admin page in the Tools menu.
 */
function wp_abilities_demo_register_admin_page() {
	add_management_page(
		__( 'Abilities API Demo', 'wp-abilities-api-demo' ),
		__( 'Abilities API Demo', 'wp-abilities-api-demo' ),
		'manage_options',
		'abilities-api-demo',
		'wp_abilities_demo_render_admin_page'
	);
}
add_action( 'admin_menu', 'wp_abilities_demo_register_admin_page' );

/**
 * Enqueue scripts and styles for the admin page.
 *
 * @param string $hook The current admin page hook.
 */
function wp_abilities_demo_enqueue_assets( $hook ) {
	// Only enqueue on our admin page
	if ( 'tools_page_abilities-api-demo' !== $hook ) {
		return;
	}

	// Enqueue CSS
	wp_enqueue_style(
		'wp-abilities-demo-admin',
		plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ),
		array(),
		'1.0.0'
	);

	// Enqueue JavaScript - ensure wp-abilities is a dependency
	wp_enqueue_script(
		'wp-abilities-demo-admin',
		plugins_url( 'assets/js/admin.js', dirname( __FILE__ ) ),
		array( 'wp-abilities' ),
		'1.0.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'wp_abilities_demo_enqueue_assets' );

/**
 * Render the admin page.
 */
function wp_abilities_demo_render_admin_page() {
	// Check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-abilities-api-demo' ) );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'This page demonstrates the WordPress Abilities API JavaScript client. Each ability can be executed below.', 'wp-abilities-api-demo' ); ?></p>

		<!-- Site Info Ability -->
		<div class="ability-section">
			<h2><?php esc_html_e( 'Site Info', 'wp-abilities-api-demo' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Returns information about this WordPress site.', 'wp-abilities-api-demo' ); ?></p>
			<button type="button" class="button button-primary execute-ability" data-ability="site/site-info">
				<?php esc_html_e( 'Get Site Info', 'wp-abilities-api-demo' ); ?>
			</button>
			<div class="ability-loading" style="display:none;">
				<span class="spinner is-active"></span>
				<span><?php esc_html_e( 'Loading...', 'wp-abilities-api-demo' ); ?></span>
			</div>
			<div class="ability-output"></div>
		</div>

		<!-- Plugin List Ability -->
		<div class="ability-section">
			<h2><?php esc_html_e( 'Plugin List', 'wp-abilities-api-demo' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Retrieves a list of all installed WordPress plugins.', 'wp-abilities-api-demo' ); ?></p>
			<button type="button" class="button button-primary execute-ability" data-ability="plugins/get-plugins">
				<?php esc_html_e( 'Get Plugin List', 'wp-abilities-api-demo' ); ?>
			</button>
			<div class="ability-loading" style="display:none;">
				<span class="spinner is-active"></span>
				<span><?php esc_html_e( 'Loading...', 'wp-abilities-api-demo' ); ?></span>
			</div>
			<div class="ability-output"></div>
		</div>

		<!-- Debug Log Read Ability -->
		<div class="ability-section">
			<h2><?php esc_html_e( 'Debug Log Reader', 'wp-abilities-api-demo' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Reads the contents of the WordPress debug.log file.', 'wp-abilities-api-demo' ); ?></p>
			<div class="ability-input-group">
				<label for="debug-log-lines">
					<?php esc_html_e( 'Number of lines (1-1000):', 'wp-abilities-api-demo' ); ?>
				</label>
				<input type="number" id="debug-log-lines" name="lines" min="1" max="1000" value="100" />
			</div>
			<button type="button" class="button button-primary execute-ability" data-ability="debug/read-log">
				<?php esc_html_e( 'Read Debug Log', 'wp-abilities-api-demo' ); ?>
			</button>
			<div class="ability-loading" style="display:none;">
				<span class="spinner is-active"></span>
				<span><?php esc_html_e( 'Loading...', 'wp-abilities-api-demo' ); ?></span>
			</div>
			<div class="ability-output"></div>
		</div>

		<!-- Debug Log Clear Ability -->
		<div class="ability-section">
			<h2><?php esc_html_e( 'Debug Log Clearer', 'wp-abilities-api-demo' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Clears the contents of the WordPress debug.log file.', 'wp-abilities-api-demo' ); ?></p>
			<button type="button" class="button button-primary execute-ability" data-ability="debug/clear-log">
				<?php esc_html_e( 'Clear Debug Log', 'wp-abilities-api-demo' ); ?>
			</button>
			<div class="ability-loading" style="display:none;">
				<span class="spinner is-active"></span>
				<span><?php esc_html_e( 'Loading...', 'wp-abilities-api-demo' ); ?></span>
			</div>
			<div class="ability-output"></div>
		</div>

		<!-- Create Post Ability -->
		<div class="ability-section">
			<h2><?php esc_html_e( 'Create Post', 'wp-abilities-api-demo' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Creates a new blog post with the provided content.', 'wp-abilities-api-demo' ); ?></p>
			<div class="ability-input-group">
				<label for="post-title">
					<?php esc_html_e( 'Post Title:', 'wp-abilities-api-demo' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" id="post-title" name="title" class="regular-text" required />
			</div>
			<div class="ability-input-group">
				<label for="post-content">
					<?php esc_html_e( 'Post Content:', 'wp-abilities-api-demo' ); ?>
					<span class="required">*</span>
				</label>
				<textarea id="post-content" name="content" rows="5" class="large-text" required></textarea>
			</div>
			<div class="ability-input-group">
				<label for="post-status">
					<?php esc_html_e( 'Post Status:', 'wp-abilities-api-demo' ); ?>
				</label>
				<select id="post-status" name="status">
					<option value="draft"><?php esc_html_e( 'Draft', 'wp-abilities-api-demo' ); ?></option>
					<option value="publish"><?php esc_html_e( 'Publish', 'wp-abilities-api-demo' ); ?></option>
				</select>
			</div>
			<button type="button" class="button button-primary execute-ability" data-ability="post/create-post">
				<?php esc_html_e( 'Create Post', 'wp-abilities-api-demo' ); ?>
			</button>
			<div class="ability-loading" style="display:none;">
				<span class="spinner is-active"></span>
				<span><?php esc_html_e( 'Loading...', 'wp-abilities-api-demo' ); ?></span>
			</div>
			<div class="ability-output"></div>
		</div>

		<!-- Plugin Security Check Ability -->
		<div class="ability-section">
			<h2><?php esc_html_e( 'Plugin Security Check', 'wp-abilities-api-demo' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Analyzes WordPress plugins for security vulnerabilities.', 'wp-abilities-api-demo' ); ?></p>
			<div class="ability-input-group">
				<label for="plugin-slug">
					<?php esc_html_e( 'Plugin Slug:', 'wp-abilities-api-demo' ); ?>
					<span class="required">*</span>
				</label>
				<input type="text" id="plugin-slug" name="plugin_slug" class="regular-text" placeholder="e.g., akismet" required />
				<p class="description"><?php esc_html_e( 'Enter the plugin directory name (e.g., "akismet" or "hello-dolly")', 'wp-abilities-api-demo' ); ?></p>
			</div>
			<button type="button" class="button button-primary execute-ability" data-ability="plugin-check/check-security">
				<?php esc_html_e( 'Check Plugin Security', 'wp-abilities-api-demo' ); ?>
			</button>
			<div class="ability-loading" style="display:none;">
				<span class="spinner is-active"></span>
				<span><?php esc_html_e( 'Loading...', 'wp-abilities-api-demo' ); ?></span>
			</div>
			<div class="ability-output"></div>
		</div>
	</div>
	<?php
}
