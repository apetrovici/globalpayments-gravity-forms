<?php
/**
 * Plugin Name: Global Payments Gravity Forms
 * Plugin URI: https://developer.heartlandpaymentsystems.com/
 * Description: Integrates Gravity Forms with some of Global Payments' payment gateways, enabling end users to purchase goods and services through Gravity Forms.
 * Version: 1.0.0
 * Author: Global Payments
 * Author URI: https://developer.heartlandpaymentsystems.com/
 */

define('GF_SECURESUBMIT_VERSION', '1.0.0');

add_action('gform_loaded', array('GF_GlobalPayments_Bootstrap', 'load'), 5);

/**
 * Class GF_GlobalPayments_Bootstrap
 */
class GF_GlobalPayments_Bootstrap
{
    public static function load()
    {
        if (!method_exists('GFForms', 'include_payment_addon_framework')) {
            return;
        }

        require_once 'vendor/autoload.php';
        require_once 'classes/class-gf-globalpayments.php';
        require_once 'classes/class-gf-field-gpsecurecc.php';

        GFAddOn::register('GFGlobalPayments');
    }
}

/**
 * @return \GFGlobalPayments|null
 */
function gf_globalpayments()
{
    return GFGlobalPayments::get_instance();
}
