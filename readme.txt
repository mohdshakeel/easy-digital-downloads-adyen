=== Integrate Adyen Payment Gateway for EDD ===
Contributors: [mdshak]
Tags: Easy Digital Downloads, Payment Gateway, Adyen
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate Adyen Payment Gateway for EDD is an add-on plugin for Easy Digital Downloads (EDD) that integrates Adyen’s payment services.

== Description ==

Integrate Adyen Payment Gateway for EDD allows you to connect your Easy Digital Downloads (EDD) store with Adyen’s robust payment platform. With this plugin, you can enable seamless payment processing for both test and live environments.

Key Features:
- Switch between Test and Live modes.
- Configure API keys and endpoints for secure transactions.
- Supports onsite or hosted payment modes.

== Installation ==

1. Download the plugin ZIP file.
2. In your WordPress dashboard, go to **Plugins > Add New**.
3. Click **Upload Plugin** and select the downloaded ZIP file.
4. Click **Install Now** and then **Activate**.
5. Ensure that Easy Digital Downloads (EDD) is installed and activated.

== Setup Instructions ==

1. **Activate Adyen Gateway**:
   - Go to **EDD > Settings > General**.
   - Under **Active Gateways**, check the box for **Adyen**.
   - Under **Default Gateway**, select **Adyen** if desired.
   - Save changes.

2. **Configure Adyen Settings**:
   - Navigate to **EDD > Settings > Payments**.
   - Click on the **Adyen** sub-tab.

### Settings Available:

1. **Test or Live Settings**:
   - Choose between **Test** (sandbox environment) or **Live** (production environment).

2. **Live API Key**:
   - Log in to your Adyen account.
   - Navigate to **Developers > API Credentials**.
   - Copy the live API key and paste it into the corresponding field.

3. **Test API Key**:
   - Log in to your Adyen Sandbox account.
   - Navigate to **Developers > API Credentials**.
   - Copy the test API key and paste it into the corresponding field.

4. **Live API Endpoint URL**:
   - Refer to Adyen’s official documentation for the latest live endpoint URL.
   - Copy and paste it into the plugin settings.

5. **Test API Endpoint URL**:
   - Refer to Adyen’s official documentation for the latest test endpoint URL.
   - Copy and paste it into the plugin settings.

6. **Merchant Account**:
   - Log in to your Adyen account.
   - Go to **Account > Merchant Accounts**.
   - Copy your merchant account name and paste it into the plugin settings.

7. **Theme ID**:
   - Use this field to customize the appearance of payment forms.

8. **Payment Mode (Onsite/Hosted)**:
   - **Onsite**: Process payments directly on your website.
   - **Hosted**: Redirect users to Adyen’s secure payment page.

== Testing ==

1. Set the plugin to **Test** mode in the settings.
2. Enter the test API key and test endpoint URL.
3. Perform a test transaction to ensure everything is working correctly.

== Going Live ==

1. Switch to **Live** mode in the settings.
2. Enter the live API key, live endpoint URL, and merchant account.
3. Perform a live transaction to confirm the integration.

== Support ==

For support, please visit [Support URL or Contact Email].

== Changelog ==

= 1.0.0 =
* Initial release with support for Integrate Adyen Payment Gateway for EDD Adyen’s payment gateway.