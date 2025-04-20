<?php 
/**
 * Class EDD_Adyen_Gateway
 * Handles the integration of Adyen payment gateway with Easy Digital Downloads
 */
class EDD_Adyen_Gateway {

	/**
	 * Constructor - Register necessary hooks for Adyen gateway integration
	 */
	public function __construct() {
		add_filter( 'edd_payment_gateways', array( $this, 'register_adyen_gateway' ) );
		add_filter( 'edd_gateway_settings_url_adyen', array( $this, 'add_adyen_settings_url' ) );
	}

	/**
	 * Add settings URL for Adyen gateway configuration
	 * 
	 * @param string $settings_url The settings URL
	 * @return string Modified settings URL
	 */
	public function add_adyen_settings_url( $settings_url ) {
		// Build the URL pointing to the Adyen gateway settings section
		$settings_url = admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=adyen' );
		return $settings_url;
	}

	/**
	 * Register Adyen as a payment gateway in EDD
	 * 
	 * @param array $gateways List of registered payment gateways
	 * @return array Modified list of payment gateways including Adyen
	 */
	public function register_adyen_gateway( $gateways ) {
		$gateways['adyen'] = array(
			'admin_label'    => __( 'Adyen', 'integrate-adyen-payment-gateway-edd' ),
			'checkout_label' => __( 'Credit Card (Adyen)', 'integrate-adyen-payment-gateway-edd' ),
		);

		return $gateways;
	}
}