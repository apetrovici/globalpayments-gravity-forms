<?php

$gatewayPorticoClasses = 'medium gateway-setting gateway-portico';
$gatewayTransitClasses = 'medium gateway-setting gateway-transit';

return array(
    array(
        'name' => 'gateway_type',
        'label' => __('Payment Gateway', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'select',
        'onchange' => "GlobalPaymentsAdmin.toggleGatewaySettings(this.value);",
        'choices' => array(
            array(
                'label' => __('Heartland Portico', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'portico',
                'selected' => true,
            ),
            array(
                'label' => __('TSYS Transit', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'transit',
            ),
        ),
    ),
    // Portico Settings
    array(
        'name' => 'public_api_key',
        'label' => __('Public Key', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayPorticoClasses,
    ),
    array(
        'name' => 'secret_api_key',
        'label' => __('Secret Key', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayPorticoClasses,
    ),
    // TransIT Settings
    array(
        'name' => 'merchant_id',
        'label' => __('Merchant ID', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayTransitClasses,
    ),
    array(
        'name' => 'username',
        'label' => __('User ID', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayTransitClasses,
    ),
    array(
        'name' => 'password',
        'label' => __('Password', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayTransitClasses,
    ),
    array(
        'name' => 'device_id',
        'label' => __('Device ID', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayTransitClasses,
    ),
    array(
        'name' => 'tsep_device_id',
        'label' => __('TSEP Device ID', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayTransitClasses,
    ),
    array(
        'name' => 'transaction_key',
        'label' => __('Transaction Key', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'class' => $gatewayTransitClasses,
    ),
    // Shared Settings
    array(
        'name' => 'authorize_or_charge',
        'label' => __('Payment Action', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'select',
        'default_value' => 'charge',
        'tooltip' => __(
            'Choose whether you wish to capture funds immediately or authorize payment only.',
            'global-payments-gateway-provider-for-gravity-forms'
        ),
        'choices' => array(
            array(
                'label' => __('Authorize + Capture', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'charge',
                'selected' => true,
            ),
           array(
                'label' => __('Authorize only', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'authorize',
            ),
        ),
    ),
    array(
        'name' => 'allow_payment_action_override',
        'label' => __('Allow Payment Action Override', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'radio',
        'default_value' => 'no',
        'tooltip' => __(
            'Allows a GlobalPayments Feed to override the default payment action (authorize / capture).',
            'global-payments-gateway-provider-for-gravity-forms'
        ),
        'choices' => array(
            array(
                'label' => __('No', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'no',
                'selected' => true,
            ),
            array(
                'label' => __('Yes', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'yes',
            ),
        ),
        'horizontal' => true,
    ),
    // array(
    //     'name' => 'allow_api_keys_override',
    //     'label' => __('Allow API Keys Override', 'global-payments-gateway-provider-for-gravity-forms'),
    //     'type' => 'radio',
    //     'default_value' => 'no',
    //     'tooltip' => __(
    //         'Allows a GlobalPayments Feed to override the default set of API keys.',
    //         'global-payments-gateway-provider-for-gravity-forms'
    //     ),
    //     'choices' => array(
    //         array(
    //             'label' => __('No', 'global-payments-gateway-provider-for-gravity-forms'),
    //             'value' => 'no',
    //             'selected' => true,
    //         ),
    //         array(
    //             'label' => __('Yes', 'global-payments-gateway-provider-for-gravity-forms'),
    //             'value' => 'yes',
    //         ),
    //     ),
    //     'horizontal' => true,
    // ),
);
