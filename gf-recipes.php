<?php
/**
 * Plugin Name: Gluten Free Recipes
 * Plugin URI: https://example.com/gf-recipes
 * Description: Complete recipe management system with custom post types, advanced search, and ingredient-based suggestions
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: gf-recipes
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace GFRecipes;

defined('ABSPATH') || exit;

define('GF_RECIPES_VERSION', '1.0.0');
define('GF_RECIPES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GF_RECIPES_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once GF_RECIPES_PLUGIN_DIR . 'includes/Security.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/CPT.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/Meta.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/Search.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/Suggest.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/REST.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/Seeder.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/Submit.php';
require_once GF_RECIPES_PLUGIN_DIR . 'includes/Shortcodes.php';

class Plugin {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'init']);
        add_action('rest_api_init', [$this, 'rest_init']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function load_textdomain() {
        load_plugin_textdomain('gf-recipes', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function init() {
        CPT::register();
        Meta::register();
        Shortcodes::register();
    }

    public function rest_init() {
        REST::register_routes();
    }

    public function activate() {
        CPT::register();
        flush_rewrite_rules();
        
        Seeder::seed_demo_data();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }
}

Plugin::get_instance();
