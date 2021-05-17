<?php

return array(
    array(
        'name' => 'enable_fraud',
        'label' => __('Velocity Settings', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'select',
        'default_value' => 'Enabled',
        'tooltip' => __('Choose whether you wish to limit failed attempts', 'global-payments-gateway-provider-for-gravity-forms'),
        'choices' => array(
            array(
                'label' => __('Enabled', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'true',
                'selected' => true,
            ),
            array(
                'label' => __('Disabled', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'false',
            ),
        ),
    ),
    array(
        'name' => 'fraud_message',
        'label' => __('Displayed Message', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'tooltip' => __(
            'Text entered here will be displayed to your consumer if they exceed the failures within the timeframe.',
            'global-payments-gateway-provider-for-gravity-forms'
        ),
        'default_value' => 'Please contact us to complete the transaction.',
        'class' => 'medium',
    ),
    array(
        'name' => 'fraud_velocity_attempts',
        'label' => __('How many failed attempts before blocking?', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'text',
        'default_value' => '3',
        'class' => 'small',
    ),
    array(
        'name' => 'fraud_velocity_timeout',
        'label' => __(
            'How long (in minutes) should we keep a tally of recent failures?',
            'global-payments-gateway-provider-for-gravity-forms'
        ),
        'type' => 'text',
        'default_value' => '10',
        'class' => 'small',
    ),
);
