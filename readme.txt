=== GlobalPayments Addon for Gravity Forms ===
Contributors: markhagan
Tags: gravity, forms, gravityforms, heartland, payment, systems, gateway, token, tokenize
Tested up to: 5.9.3
Stable tag: trunk
License: GPLv2
License URI: https://github.com/globalpayments/globalpayments-gravity-forms/

GlobalPayments allows merchants to take PCI-Friendly Credit Card payments with Gravity Forms using Heartland Payment Systems Payment Gateway.

== Description ==

This plugin allows Gravity Forms to use the Heartland Payment Systems Gateway. All card data is tokenized using Heartland's GlobalPayments product.

Features of GlobalPayments:

* Only two configuration fields: public and secret API key
* Simple to install and configure.
* Tokenized payments help reduce PCI Scope
* Enables credit card saving for a friction-reduced checkout.

== Installation ==

  1. Sign Up for an account @ developer.heartlandpaymentsystems.com if you haven't already
  2. Download Gravity Forms
  3. Install AND Activate Gravity Forms WP plugin AND Heartland GlobalPayments for Gravity Forms WP plugin
  4. Configure Gravity Forms and GlobalPayments accounts:
      * Navigate to Settings to enter your API Keys provided by your Heartland Developer Portal Account
  5. Add Form:
      * Navigate to Forms > Add New Form > Edit Form
      * Add Required Fields:
        * Pricing
        * Product
        * Total
        * CC and/or ACH form
  6. Add new Feed:
      * Form Settings > GlobalPayments > Add new feed
  7. Add form to WP page

* NEED ADDITIONAL HELP? Contact Us  http://developer.heartlandpaymentsystems.com/support


== Changelog ==


= 1.1.0 =
* Added AVS/CVV result based reversal conditions in admin and store.

= 1.0.0 =
* Initial Release



