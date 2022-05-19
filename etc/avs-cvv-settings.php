<?php

$avsDeclineCodes = array(
    array(
        'label' => __('A - Address matches, zip No Match', 'global-payments-gateway-provider-for-gravity-forms'),
        'value' => 'A'
    ),
    array(
        'label' => __('N - Neither address or zip code match', 'global-payments-gateway-provider-for-gravity-forms'),
        'value' => 'N',
    ),
    array(
        'value' => "R",
        'label' => __('R - Retry - system unable to respond', 'global-payments-gateway-provider-for-gravity-forms')
    ),
    array(
        'value' => "U",
        'label' => __('U - Visa / Discover card AVS not supported'),
    ),
    array(
        'value' => 'S',
        'label' => __('S - Master / Amex card AVS not supported'),
    ),
    array(
        'value' => 'Z',
        'label' => __('Z - Visa / Discover card 9-digit zip code match, address no match')
    ),
    array(
        'value' => 'W',
        'label' => __('W - Master / Amex card 9-digit zip code match, address no match')
    ),
    array(
        'value' => 'Y',
        'label' => __('Y - Visa / Discover card 5-digit zip code and address match')
    ),
    array(
        'value' => 'X',
        'label' => __('X - Master / Amex card 5-digit zip code and address match')
    ),
    array(
        'value' => 'G',
        'label' => __('G - Address not verified for International transaction')
    ),
    array(
        'value' => 'B',
        'label' => __('B - Address match, Zip not verified')
    ),
    array(
        'value' => 'C',
        'label' => __('C - Address and zip mismatch')
    ),
    array(
        'value' => 'D',
        'label' => __('D - Address and zip match')
    ),
    array(
        'value' => 'I',
        'label' => __('I - AVS not verified for International transaction')
    ),
    array(
        'value' => 'M',
        'label' => __('M - Street address and postal code matches')
    ),
    array(
        'value' => 'P',
        'label' => __('P - Address and Zip not verified')
    )
);

$cvnDeclineCodes = array(
    array(
        'value' => 'N',
        'label' => __('N - Not Matching'),
    ),
    array(
        'value' => 'P',
        'label' => __('P - Not Processed'),
    ),
    array(
        'value' => 'S',
        'label' => __('S - Result not present'),
    ),
    array(
        'value' => 'U',
        'label' => __('U - Issuer not certified'),
    ),
    array(
        'value' => '?',
        'label' => __('? - CVV unrecognized'),
    ),
);

return array(
    array(
        'name' => 'check_avs_cvv',
        'label' => __( 'Check AVS/CVN result codes.'),
        'type' => 'radio',
        'default_value' => 'no',
        'tooltip' => __('This will check AVS/CVN result codes and reverse transaction.', 'global-payments-gateway-provider-for-gravity-forms'),
        'choices' => array(
            array(
                'label' => __('No', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'no',                
            ),
            array(
                'label' => __('Yes', 'global-payments-gateway-provider-for-gravity-forms'),
                'value' => 'yes',
                'selected' => true,
            ),
        ),
        'horizontal' => true,
    ),
    array(
        'name' => 'avs_reject_conditions[]',
        'label' => __('AVS Reject Conditions', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'select',
        'tooltip' => __('Choose for which AVS result codes, the transaction must be auto reveresed', 'global-payments-gateway-provider-for-gravity-forms'),
        'choices' => $avsDeclineCodes,
        'default_value'     => array("N", "S", "U", "P", "R", "G", "C", "I"),
        'multiple' => 'multiple',
        'css'      => 'width: 450px'
    ),
    array(
        'name' => 'cvn_reject_conditions[]',
        'label' => __('CVN Reject Conditions', 'global-payments-gateway-provider-for-gravity-forms'),
        'type' => 'select',
        'multiple' => 'multiple',
        'tooltip' => __('Choose for which CVN result codes, the transaction must be auto reveresed', 'global-payments-gateway-provider-for-gravity-forms'),
        'choices' => $cvnDeclineCodes,
        'default_value'     => array("P", "?", "N"),
        'multiple' => 'multiple',
        'css'      => 'width: 450px',        
    ),
);
