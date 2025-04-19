<?php
/**
 * Adyen Payment Processing Class
 *
 * Handles all payment processing functionality for both hosted and onsite payment methods:
 * - Initializes payment gateway hooks
 * - Processes payment submissions
 * - Handles payment confirmations
 * - Manages API communication with Adyen
 *
 * @since 1.0.0
 */
final class EDD_Adyen_Payment {

	/**
	 * API endpoint for the current mode
	 * @var string
	 */
	private static $endPoint;

	/**
	 * API key for the current mode
	 * @var string
	 */
	private static $apiKey;

	/**
	 * Private constructor to prevent instantiation
	 */
	private function __construct() {}

	/**
	 * Initialize the payment gateway
	 */
	public static function init() {
		self::setup_api_credentials();

		if (self::is_hosted_mode()) {
			add_filter('edd_gateway_checkout_label_adyen', [__CLASS__, 'change_gateway_label']);
			add_action('edd_adyen_cc_form', '__return_false');
		}
		add_action('edd_gateway_adyen', [__CLASS__, 'process_payment']);
		add_action('template_redirect', [__CLASS__, 'handle_edd_payment_confirmation']);
	}

	/**
	 * Setup API credentials based on mode
	 */
	private static function setup_api_credentials() {
		$testMode = edd_get_option('adyen_test_mode');
		
		if ($testMode == 'test') {
			self::$endPoint = EDD_ADYEN_TEST_URL;
			self::$apiKey = sanitize_text_field(html_entity_decode(edd_get_option('adyen_api_test_key')));
		} else {
			self::$endPoint = esc_url(edd_get_option('adyen_live_url'));
			self::$apiKey = sanitize_text_field(html_entity_decode(edd_get_option('adyen_api_live_key')));
		}
	}

	/**
	 * Check if hosted payment mode is enabled
	 * @return bool
	 */
	private static function is_hosted_mode() {
		return edd_get_option('adyen_payment_mode') === 'hosted';
	}

	/**
	 * Change gateway label
	 */
	public static function change_gateway_label($label) {
		return esc_html__('Pay with Adyen', 'edd-adyen');
	}

	/**
	 * Handle payment confirmation. Adyen return the user to specific url with some data after payment from Adyen Website and We check here the payment confimation through API call.  
	 * Adyen (or PayPal, Stripe, etc.) is sending the user back to https://yoursite.com/confirmation?payment-id=123, WordPress won’t expect a nonce because:
     * The request originates from an external service, not from a logged-in user.
     * The request is part of a transactional workflow, not an admin action.
	 * If We verify nonce then it can be failed because user may take time.It may cause to failed nonce
	 * We use the payment session id created  through the api call and check with return url data.
	**/
	public static function handle_edd_payment_confirmation() {
		if (!is_page('confirmation') && !isset($_GET['payment-id']) && !isset($_GET['sessionId']) && !isset($_GET['sessionResult'])) {
			return;
		}
        // Hosted Mode Payment Confirmation
		$paymentId     = absint($_GET['payment-id']);
		$sessionId     = sanitize_text_field($_GET['sessionId']);
		$sessionResult = sanitize_text_field($_GET['sessionResult']);
		$sessionIdMeta = edd_get_payment_meta($paymentId,'_edd_adyen_hosted_session_id',true);
		//verifying the current payment session id with stored session id at the time of first api call
		if (!$paymentId && !$sessionId && $sessionId !== $sessionIdMeta) {
			return;
		}

		
		$url = trailingslashit(self::$endPoint) . 'sessions/' . $sessionId . '?sessionResult=' . $sessionResult;
		
		$args = [
			'headers' => [
				'X-API-Key'    => self::$apiKey, // Replace with your Adyen API key
				'Content-Type' => 'application/json',
			],
			'timeout' => 20,
		];
	
		$response = wp_remote_get($url, $args);
		if (is_wp_error($response)) {
			edd_set_error('adyen_error', __('Payment gateway error.', 'edd-adyen'));
			edd_send_back_to_checkout('?payment-mode=adyen');
		}

		$responseBody = wp_remote_retrieve_body($response);
		$result = json_decode($responseBody, true);

		if ($result['status']=='completed') {
			edd_update_payment_status($paymentId,'publish');
		}else{
			edd_set_error('adyen_error', __('Payment gateway error.', 'edd-adyen'));
			edd_send_back_to_checkout('?payment-mode=adyen');

		}

		
	}


	/**
	 * Process payment based on mode
	 */
	public static function process_payment($purchaseData) {
		$endpoint = self::$endPoint;
		$endpoint .= self::is_hosted_mode() ? 'sessions' : 'payments';

		if (self::is_hosted_mode()) {
			self::process_hosted_payment($purchaseData, self::$apiKey, $endpoint);
		} else {
			self::process_onsite_payment($purchaseData, self::$apiKey, $endpoint);
		}
	}

	/**
	 * Process onsite payment with enhanced security
	 */
	private static function process_onsite_payment($purchaseData, $apiKey, $endPoint) {
		$paymentId = self::create_payment($purchaseData);

		$cardData = self::get_sanitized_card_data();
		if (is_wp_error($cardData)) {
			edd_set_error('adyen_error', $cardData->get_error_message());
			edd_send_back_to_checkout('?payment-mode=adyen');
		}

		$paymentData = self::prepare_payment_data($paymentId, $cardData);
		$response = self::send_api_request($endPoint, $paymentData, $apiKey);

		self::handle_payment_response($response, $paymentId);
	}

	/**
	 * Process hosted payment with enhanced security
	 */
	private static function process_hosted_payment($purchaseData, $apiKey, $endPoint) {
		$paymentId = self::create_payment($purchaseData);

		$paymentData = array(
			'merchantAccount' => sanitize_text_field(edd_get_option('adyen_merchant_account')),
			'amount' => array(
				'currency' => edd_get_currency(),
				'value' => absint(edd_get_cart_total() * 100),
			),
			'mode' => 'hosted',
			'themeId' => sanitize_text_field(edd_get_option('adyen_theme_id')),
			'reference' => $paymentId,
			'returnUrl' => wp_nonce_url(
				esc_url(add_query_arg('payment-confirmation', 'adyen', edd_get_success_page_uri())),
				'payment_confirmation_nonce',
				'nonce'
			),
		);

		$response = self::send_api_request($endPoint, $paymentData, $apiKey);
		self::handle_hosted_response($response,$paymentId);
	}

	/**
	 * Create payment record
	 * @return int|false Payment ID
	 */
	private static function create_payment($purchaseData) {
		$paymentId = edd_insert_payment($purchaseData);
		if (!$paymentId) {
			edd_set_error('adyen_error', __('Unable to create payment record.', 'edd-adyen'));
			edd_send_back_to_checkout('?payment-mode=adyen');
		}
		return $paymentId;
	}

	/**
	 * Get and sanitize card data
	 * @return array|WP_Error
	 */
	private static function get_sanitized_card_data() {
		$cardData = array(
			'holderName' => filter_input(INPUT_POST, 'card_name', FILTER_SANITIZE_STRING),
			'number' => filter_input(INPUT_POST, 'card_number', FILTER_SANITIZE_STRING),
			'cvc' => filter_input(INPUT_POST, 'card_cvc', FILTER_SANITIZE_NUMBER_INT),
			'expMonth' => filter_input(INPUT_POST, 'card_exp_month', FILTER_SANITIZE_NUMBER_INT),
			'expYear' => filter_input(INPUT_POST, 'card_exp_year', FILTER_SANITIZE_NUMBER_INT)
		);

		foreach ($cardData as $key => $value) {
			if (empty($value)) {
				return new WP_Error('invalid_card_data', __('Invalid card data provided.', 'edd-adyen'));
			}
		}

		return $cardData;
	}

	/**
	 * Prepare payment data for API request
	 */
	private static function prepare_payment_data($paymentId, $cardData) {
		$encryptionCode = defined('ADYEN_ENCRYPTION_CODE') ? ADYEN_ENCRYPTION_CODE : 'test';

		return array(
			'amount' => array(
				'currency' => edd_get_currency(),
				'value' => absint(edd_get_cart_total() * 100),
			),
			'reference' => $paymentId,
			'paymentMethod' => array(
				'type' => 'scheme',
				'encryptedCardNumber' => $encryptionCode . '_' . $cardData['number'],
				'encryptedExpiryMonth' => $encryptionCode . '_' . $cardData['expMonth'],
				'encryptedExpiryYear' => $encryptionCode . '_' . $cardData['expYear'],
				'encryptedSecurityCode' => $encryptionCode . '_' . $cardData['cvc'],
				'holderName' => $cardData['holderName']
			),
			'returnUrl' => esc_url(add_query_arg('payment-confirmation', 'adyen', edd_get_success_page_uri())),
			'merchantAccount' => sanitize_text_field(edd_get_option('adyen_merchant_account'))
		);
	}

	/**
	 * Send API request with error handling
	 */
	private static function send_api_request($endPoint, $paymentData, $apiKey) {
		$headers = array(
			'Content-Type' => 'application/json',
			'x-api-key' => $apiKey
		);

		$response = wp_remote_post($endPoint, array(
			'method' => 'POST',
			'headers' => $headers,
			'body' => wp_json_encode($paymentData),
			'timeout' => 45,
			'sslverify' => true
		));

		if (is_wp_error($response)) {
			edd_set_error('adyen_error', __('Payment gateway error.', 'edd-adyen'));
			edd_send_back_to_checkout('?payment-mode=adyen');
		}

		return $response;
	}

	/**
	 * Handle payment API response
	 */
	private static function handle_payment_response($response, $paymentId) {
		$responseBody = json_decode(wp_remote_retrieve_body($response), true);

		if (!is_array($responseBody)) {
			edd_set_error('adyen_error', __('Invalid response from payment gateway.', 'edd-adyen'));
			edd_send_back_to_checkout('?payment-mode=adyen');
		}

		if (isset($responseBody['resultCode'])) {
			switch ($responseBody['resultCode']) {
				case 'RedirectShopper':
					if (isset($responseBody['action']['url'])) {
						wp_redirect(esc_url($responseBody['action']['url']));
						exit;
					}
					break;
				case 'Authorised':
					edd_update_payment_status($paymentId, 'publish');
					edd_send_to_success_page();
					break;
				default:
					edd_set_error('adyen_error', __('Payment processing failed. Please try again.', 'edd-adyen'));
					edd_send_back_to_checkout('?payment-mode=adyen');
			}
		}
	}

	/**
	 * Handle hosted payment response
	 */
	private static function handle_hosted_response($response,$paymentId) {
		$responseBody = wp_remote_retrieve_body($response);
		$result = json_decode($responseBody, true);

		if (isset($result['url'])) {
			edd_update_payment_meta($paymentId,'_edd_adyen_hosted_session_id',$result['id']);
			wp_redirect(esc_url($result['url']));
            exit;
		} else {
			edd_set_error('adyen_error', __('Unable to process payment.', 'edd-adyen'));
			edd_send_back_to_checkout('?payment-mode=adyen');
		}
	}

	/**
	 * Prevent cloning of the class
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing of the class
	 */
	public function __wakeup() {
    throw new Exception("Cannot unserialize a singleton class.");
    }
}