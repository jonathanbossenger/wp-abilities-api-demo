# WP Abilities API Demo

A WordPress plugin that demonstrates the WordPress Abilities API with various example abilities. This plugin provides a practical reference implementation and testing interface for developers working with the Abilities API.

## Description

The WP Abilities API Demo plugin showcases how to register and use abilities through the WordPress Abilities API. It includes several working examples and an admin interface for testing abilities directly from the WordPress dashboard.

## Features

- **Site Information Retrieval** - Get details about your WordPress installation
- **Plugin Listing** - Retrieve a list of all installed plugins
- **Debug Log Management** - Read and clear WordPress debug logs
- **Post Creation** - Create new blog posts programmatically
- **Security Checking** - Run security checks using the Plugin Check plugin
- **Admin Testing Interface** - User-friendly interface to test all registered abilities

## Requirements

- WordPress (latest version recommended)
- PHP 8.3.6 or higher
- [Composer](https://getcomposer.org/) (for plugin dependencies, only for development)
- [Plugin Check](https://wordpress.org/plugins/plugin-check/) plugin (for plugin checking ability)

## Installation

1. Download or clone this repository to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/jonathanbossenger/wp-abilities-api-demo.git
   ```

2. Install dependencies using Composer:
   ```bash
   cd wp-abilities-api-demo
   composer install
   ```

3. Activate the plugin through the WordPress admin interface or via WP-CLI:
   ```bash
   wp plugin activate wp-abilities-api-demo
   ```

## Usage

Once activated, access the demo interface from your WordPress admin:

1. Navigate to **Tools → Abilities API Demo**
2. Select an ability from the interface
3. Provide any required input parameters
4. Execute the ability and view the results

## Using with MCP-Enabled AI Applications

The abilities demonstrated in this plugin can be converted into MCP (Model Context Protocol) tools for use with MCP-enabled AI applications and agents. This allows you to integrate WordPress functionality directly into AI assistants like Claude, Copilot, and other MCP-compatible tools.

To learn how to convert WordPress abilities into MCP tools, check out the **[WP MCP Server Demo](https://github.com/jonathanbossenger/wp-mcp-server-demo)** repository. This companion project demonstrates:

- How to create an MCP server that exposes WordPress abilities as tools
- Converting ability schemas into MCP tool definitions
- Integrating WordPress capabilities with AI assistants
- Example implementations for common WordPress operations

This integration enables AI agents to interact with your WordPress site programmatically, opening up possibilities for AI-assisted content management, site administration, and custom workflows.

### Auto-Exposed MCP Tools

Certain abilities in this plugin are automatically exposed as MCP tools when the **[WP MCP Server Demo](https://github.com/jonathanbossenger/wp-mcp-server-demo)** plugin is activated. These abilities are marked for automatic exposure through MCP metadata configuration in their registration.

**How It Works**

Abilities are enabled for automatic MCP exposure by including specific metadata in their registration:

```php
'meta' => array(
    'show_in_rest' => true,
    'mcp' => array(
        'public' => true,
        'type'   => 'tool'
    ),
)
```

When an ability includes `'mcp' => array( 'public' => true )` in its meta configuration, the WP MCP Server Demo plugin automatically registers it as an available tool that MCP-enabled AI applications can discover and use.

**Auto-Exposed Abilities**

The following abilities are configured for automatic MCP exposure:

- **Site Info** (`site/site-info`) - Retrieve comprehensive WordPress site information
- **Get Plugins** (`plugins/get-plugins`) - List all installed plugins with details
- **Read Debug Log** (`debug/read-log`) - View contents of the WordPress debug log
- **Clear Debug Log** (`debug/clear-log`) - Clear the WordPress debug log file

These abilities can be immediately used by AI agents when both this plugin and the WP MCP Server Demo plugin are activated, without requiring any additional configuration.

## Available Abilities

### Site Info (`site/site-info`)
Returns comprehensive information about your WordPress site including:
- Site name and URL
- Active theme
- Active plugins
- PHP version
- WordPress version

### Get Plugins (`plugins/get-plugins`)
Retrieves a list of all installed plugins with their status and details.

### Debug Log (`debug/read-log`, `debug/clear-log`)
- **Read Log**: View the contents of the WordPress debug log
- **Clear Log**: Clear the debug log file

### Create Post (`post/create-post`)
Create a new blog post with specified title, content, and status (draft or publish).

**Input Parameters:**
- `title` (required): The post title
- `content` (required): Post content in block editor markup
- `status` (optional): Either `draft` or `publish` (defaults to `draft`)

### Check Security (`plugin-check/check-security`)
Run security checks on your WordPress installation using the Plugin Check plugin.

## Contributing

We welcome contributions! Here are some ways you can help improve this plugin:

### Add Your Own Abilities

1. Create a new file in the `includes/` directory (e.g., `ability-your-feature.php`)
2. Register your ability using the Abilities API:
   ```php
   add_action( 'wp_abilities_api_init', function() {
       wp_register_ability( 'category/ability-name', array(
           'label' => __( 'Your Ability Name', 'wp-abilities-api-demo' ),
           'description' => __( 'Description of what it does', 'wp-abilities-api-demo' ),
           'category' => 'abilities-api-demo',
           'input_schema' => array( /* ... */ ),
           'output_schema' => array( /* ... */ ),
           'execute_callback' => 'your_callback_function',
           'permission_callback' => function() {
               return current_user_can( 'manage_options' );
           },
           'meta' => array(
               'show_in_rest' => true
           )
       ));
   });
   ```
3. Include your new file in `wp-abilities-api-demo.php`
4. Submit a pull request with your addition

### Other Ways to Contribute

- **Improve Documentation**: Help make the docs clearer or add examples
- **Report Bugs**: Open an issue if you find any problems
- **Enhance the Admin Interface**: Improve the testing UI
- **Add Tests**: Help us build a test suite
- **Code Quality**: Improve code standards compliance

## Development

### Linting

This project follows WordPress Coding Standards. To check your code:

```bash
composer install
vendor/bin/phpcs --standard=WordPress --extensions=php wp-abilities-api-demo.php includes/ admin/
```

Auto-fix violations where possible:

```bash
vendor/bin/phpcbf --standard=WordPress --extensions=php wp-abilities-api-demo.php includes/ admin/
```

### Debugging

Enable debug logging by defining the constant in `wp-config.php`:

```php
define( 'WP_ABILITIES_API_DEMO_DEBUG', true );
```

## Project Structure

```
wp-abilities-api-demo/
├── admin/                          # Admin interface
│   └── abilities-demo-page.php
├── assets/
│   ├── css/
│   │   └── admin.css              # Admin page styles
│   └── js/
│       └── admin.js               # JavaScript for testing abilities
├── includes/                      # Ability implementations
│   ├── category-abilities-api-demo.php
│   ├── ability-site-info.php
│   ├── ability-get-plugins.php
│   ├── ability-debug-log.php
│   ├── ability-create-post.php
│   └── ability-check-security.php
└── wp-abilities-api-demo.php      # Main plugin file
```

## License

GPL-2.0-or-later

## Author

Jonathan Bossenger - [jonathanbossenger@gmail.com](mailto:jonathanbossenger@gmail.com)

## Credits

Built with the [WordPress Abilities API](https://github.com/WordPress/abilities-api/).
