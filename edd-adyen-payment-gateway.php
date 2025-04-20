<?php
/**
 * Plugin Name: Integrate Adyen Payment Gateway for EDD
 * Plugin URI: https://yourwebsite.com/edd-adyen
 * Description: Adds Adyen as a payment gateway for Easy Digital Downloads.
 * Version: 1.0.0
 * Requires at least: 5.6
 * Requires PHP: 7.2
 * Author: Mohammad
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: integrate-adyen-payment-gateway-edd
 * Domain Path: /languages
 *
 * @package EDD_Adyen
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class EDD_Adyen {
    /**
     * Plugin directory path
     * @var string
     */
    private static string $plugin_dir;

    /**
     * Plugin URL
     * @var string
     */
    private static string $plugin_url;

    /**
     * Instance of the class
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Get the singleton instance of the class
     * 
     * @return self
     */
    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
            self::define_constants();
            self::load_dependencies();
            self::init_hooks();
        }
        return self::$instance;
    }

    /**
     * Define constants
     */
    private static function define_constants(): void {
        self::$plugin_dir = plugin_dir_path(__FILE__);
        self::$plugin_url = plugin_dir_url(__FILE__);
        
        define('EDD_ADYEN_PLUGIN_DIR', self::$plugin_dir);
        define('EDD_ADYEN_PLUGIN_URL', self::$plugin_url);
        define('EDD_ADYEN_TEST_URL', 'https://checkout-test.adyen.com/v71/');
        
    }

    /**
     * Load required dependencies
     */
    private static function load_dependencies(): void {
        require_once self::$plugin_dir . 'includes/autoloader.php';
    }

    /**
     * Initialize hooks
     */
    private static function init_hooks(): void {
        add_action('plugins_loaded', [self::class, 'check_edd_dependency']);
        add_action('plugins_loaded', [self::class, 'load_textdomain']);
    }

    /**
     * Check if Easy Digital Downloads is installed
     */
    public static function check_edd_dependency(): void {
        if (!class_exists('Easy_Digital_Downloads')) {
            add_action('admin_notices', [self::class, 'edd_missing_notice']);
            return;
        }

        EDD_Adyen_Core::get_instance();
    }

    /**
     * Show an admin notice if EDD is missing
     */
    public static function edd_missing_notice(): void {
        echo '<div class="notice notice-error"><p>' . esc_html__('Easy Digital Downloads must be installed and activated for the Adyen gateway to work.', 'integrate-adyen-payment-gateway-edd') . '</p></div>';
    }

    /**
     * Load plugin text domain
     */
    public static function load_textdomain(): void {
        load_plugin_textdomain('integrate-adyen-payment-gateway-edd', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup(): void {
        throw new Exception("Cannot unserialize a singleton class.");
    }
}

// Initialize the plugin
EDD_Adyen::get_instance();
