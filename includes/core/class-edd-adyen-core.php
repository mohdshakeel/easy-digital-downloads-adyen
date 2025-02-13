<?php
/**
 * Main Adyen Payment Gateway Class
 * 
 * This class serves as the core initialization point for the Adyen payment gateway.
 * It loads all necessary components using the singleton pattern to ensure only one
 * instance exists throughout the application lifecycle.
 * 
 * @since 1.0.0
 */
class EDD_Adyen_Core {

	/**
	 * Single instance of this class
	 *
	 * @since 1.0.0
	 * @var EDD_Adyen_Core
	 */
	private static $instance = null;

	/**
	 * Constructor - Initialize all required components
	 * 
	 * Loads the following components:
	 * - EDD_Adyen_Settings: Handles all gateway settings and configuration
	 * - EDD_Adyen_Gateway: Manages payment gateway registration and integration
	 * - EDD_Adyen_Payment: Processes payment transactions and callbacks
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		// Initialize components
		new EDD_Adyen_Settings();  // Load settings management
		new EDD_Adyen_Gateway();   // Load gateway integration
		EDD_Adyen_Payment::init();   // Load payment processing
	}

	/**
	 * Get singleton instance
	 * 
	 * Ensures only one instance is loaded or can be loaded.
	 * 
	 * @since 1.0.0
	 * @return EDD_Adyen_Core
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
