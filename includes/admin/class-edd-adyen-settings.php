<?php
/**
 * Adyen Payment Gateway Settings Class
 *
 * This class handles the configuration settings for the Adyen payment gateway integration.
 * It adds a dedicated settings section under the EDD Payment Gateways tab and registers
 * all required configuration fields including:
 * - API Keys (Live/Test). These are different for each region of world and account specific.
 * - Endpoint URLs
 * - Merchant Account
 * - Payment Modes
 * - Theme Settings
 *
 * @since 1.0.0
 */
class EDD_Adyen_Settings {

	/**
	 * Constructor - Register settings hooks
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'edd_settings_sections_gateways', array( $this, 'register_adyen_section' ) );
		add_filter( 'edd_settings_gateways', array( $this, 'add_adyen_settings' ) );
	}

	/**
	 * Add Adyen as a subsection under the Payments tab.
	 * 
	 * @since 1.0.0
	 * @param array $sections Existing sections.
	 * @return array Updated sections with Adyen.
	 */
	public function register_adyen_section( $sections ) {
		$sections['adyen'] = __( 'Adyen', 'integrate-adyen-payment-gateway-edd' );
		return $sections;
	}

	/**
	 * Add Adyen settings to EDD payment gateway settings.
	 * 
	 * Configures all required fields for Adyen integration:
	 * - Test/Live mode toggle
	 * - API credentials for both modes
	 * - Endpoint URLs for API communication
	 * - Merchant account details
	 * - Theme customization options 
	 * - Payment processing mode (Hosted/Onsite)
	 *
	 * @since 1.0.0
	 * @param array $settings Existing EDD settings.
	 * @return array Updated settings with Adyen configuration fields.
	 */
	public function add_adyen_settings( $settings ) {
		$adyen_settings = array(
			'adyen_settings' => array(
				'id'   => 'adyen_settings',
				'name' => '<strong>' . __( 'Adyen Settings', 'integrate-adyen-payment-gateway-edd' ) . '</strong>',
				'desc' => __( 'Configure the Adyen payment gateway.', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'header',
			),
			'adyen_test_mode' => array(
				'id'   => 'adyen_test_mode',
				'name' => __( 'Test Mode', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Select the payment mode (Live or Test).', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'select',
				'options' => array(
					'live' => __( 'Live Payment Mode', 'integrate-adyen-payment-gateway-edd' ),
					'test' => __( 'Test Payment Mode', 'integrate-adyen-payment-gateway-edd' ),
				),
			),
			'adyen_api_live_key' => array(
				'id'   => 'adyen_api_live_key',
				'name' => __( 'Live API Key', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Enter your Adyen Live API Key.', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'text',
			),
			'adyen_live_url' => array(
				'id'   => 'adyen_live_url',
				'name' => __( 'Api Live EndPoint URL', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Enter your Adyen Live EndPoint URL.', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'text',
			),
			'adyen_api_test_key' => array(
				'id'   => 'adyen_api_test_key',
				'name' => __( 'TEST API Key', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Enter your Adyen Test API Key.', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'text',
			),
			
			'adyen_merchant_account' => array(
				'id'   => 'adyen_merchant_account',
				'name' => __( 'Merchant Account', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Enter your Adyen Merchant Account.', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'text',
			),
			'adyen_theme_id' => array(
				'id'   => 'adyen_theme_id',
				'name' => __( 'Theme ID', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Enter your Adyen Theme ID for hosted payment. To know about to find out  the Theme ID <a target="_blank" href="https://docs.adyen.com/online-payments/build-your-integration/sessions-flow/?platform=Web&integration=Hosted+Checkout&programming_language=bash">Click here</a>', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'text',
			),
			'adyen_payment_mode' => array(
				'id'   => 'adyen_payment_mode',
				'name' => __( 'Payment Mode', 'integrate-adyen-payment-gateway-edd' ),
				'desc' => __( 'Select the payment mode (Hosted or Onsite).', 'integrate-adyen-payment-gateway-edd' ),
				'type' => 'select',
				'options' => array(
					'hosted' => __( 'Hosted Payment Page', 'integrate-adyen-payment-gateway-edd' ),
					'onsite' => __( 'Onsite Payment', 'integrate-adyen-payment-gateway-edd' ),
				),
			)
		);
		
		$settings['adyen'] = $adyen_settings;
		return $settings;
	}
}