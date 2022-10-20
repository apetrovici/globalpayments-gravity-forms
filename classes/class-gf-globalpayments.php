<?php

use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\PaymentGatewayProvider\Data\Order;
use GlobalPayments\PaymentGatewayProvider\Gateways\HeartlandGateway;
use GlobalPayments\PaymentGatewayProvider\Gateways\TransitGateway;
use GlobalPayments\PaymentGatewayProvider\Gateways\GpApiGateway;
use GlobalPayments\PaymentGatewayProvider\Requests\TransactionType;


GFForms::include_payment_addon_framework();

/**
 * Handles payments with Gravity Forms
 * Class GFGlobalPayments
 */
class GFGlobalPayments extends GFPaymentAddOn
{
    private $processPaymentsFor = array( 'gpcreditcard' );
    private $ccFields = array( 'gpcreditcard' );

    /**
     * GATEWAY FOR PAYMENT
    */
    public $gateway;
    /**
     * @var bool
     */
    private $isCC = false;

    /**
     * @var string
     */
    protected $_version = GF_SECURESUBMIT_VERSION;

    /**
     * @var string
     */
    protected $_min_gravityforms_version = '1.9.1.1';

    /**
     * @var string
     */
    protected $_slug = 'global-payments-gravity-forms';

    /**
     * @var string
     */
    protected $_path = 'global-payments-gravity-forms/global-payments-gravity-forms.php';

    /**
     * @var string
     */
    protected $_full_path = __FILE__;

    /**
     * @var string
     */
    protected $_title = 'Global Payments Add-On for Gravity Forms';

    /**
     * @var string
     */
    protected $_short_title = 'Global Payments';

    /**
     * @var bool
     */
    protected $_requires_credit_card = false;

    /**
     * @var bool
     */
    protected $_supports_callbacks = true;

    /**
     * @var bool
     */
    protected $_enable_rg_autoupgrade = true;

    // Permissions

    /**
     * @var string
     */
    protected $_capabilities_settings_page = 'gravityforms_globalpayments';

    /**
     * @var string
     */
    protected $_capabilities_form_settings = 'gravityforms_globalpayments';

    /**
     * @var string
     */
    protected $_capabilities_uninstall = 'gravityforms_globalpayments_uninstall';

    /**
     * @var array
     */
    protected $_capabilities
        = array(
            'gravityforms_globalpayments',
            'gravityforms_globalpayments_uninstall'
        );

    /**
     * @var null
     */
    private static $_instance = null;

    /**
     * @var null
     */
    public $transaction_response = null;

    /**
     * @return GFGlobalPayments|null
     */
    public static function get_instance()
    {
        if (self::$_instance == null) {
            self::$_instance = new GFGlobalPayments();
        }

        return self::$_instance;
    }

    /**
     * Add our Secure CC button to the pricing fields.
     *
     * @param $field_groups
     *
     * @return mixed
     */
    public function gp_add_cc_field($field_groups)
    {
        foreach ($field_groups as &$group) {
            if ($group['name'] == 'pricing_fields') {
                $group['fields'][] = array(
                    'class' => 'button',
                    'data-type' => GF_Field_GPCreditCard::TYPE,
                    // the first param here will be the button text
                    // leave the second one as gravityforms
                    'value' => __('Secure CC', 'gravityforms'),
                    'onclick' => sprintf('StartAddField("%s");', GF_Field_GPCreditCard::TYPE),
                );
                break;
            }
        }

        return $field_groups;
    }

    public function init()
    {
        parent::init();
        add_action('gform_post_payment_completed', array($this, 'updateAuthorizationEntry'), 10, 2);
        add_filter('gform_replace_merge_tags', array($this, 'replaceMergeTags'), 10, 7);
        add_action('gform_admin_pre_render', array($this, 'addClientSideMergeTags'));

        add_filter('gform_add_field_buttons', array($this, 'gp_add_cc_field'));
        add_action('gform_editor_js_set_default_values', array($this, 'set_defaults'));


	    /**
	     * //the wp endpoints for 3ds
	     */

	    add_action( 'rest_api_init', function () {
	        register_rest_route( 'gpapi', 'process_threeDSecure_checkEnrollment', array(
			    'methods'  => 'GET',
			    /*'callback' => function () {
			        // we can use here instead of calling a function also
                },*/
                'callback' => [$this, 'process_threeDSecure_checkEnrollment']
		    ) );
	    } );
	    add_action( 'rest_api_init', function () {
		    register_rest_route( 'gpapi', 'process_threeDSecure_methodNotification', array(
			    'methods'  => 'GET',
			    'callback' => [$this, 'process_threeDSecure_methodNotification']
		    ) );
	    } );
	    add_action( 'rest_api_init', function () {
		    register_rest_route( 'gpapi', 'process_threeDSecure_initiateAuthentication', array(
			    'methods'  => 'GET',
			    'callback' => [$this, 'process_threeDSecure_initiateAuthentication']
		    ) );
	    } );
	    add_action( 'rest_api_init', function () {
		    register_rest_route( 'gpapi', 'process_threeDSecure_challengeNotification', array(
			    'methods'  => 'GET',
			    'callback' => [$this, 'process_threeDSecure_challengeNotification']
		    ) );
	    } );

    }

    public function process_threeDSecure_checkEnrollment(){
	    $url = get_site_url() . '/?rest_route=/gpapi/process_threeDSecure_checkEnrollment';
        var_dump( $url );
        die();
    }
	public function process_threeDSecure_methodNotification(){
		$url = get_site_url() . '/?rest_route=/gpapi/process_threeDSecure_methodNotification';
		var_dump( $url );
		die();
	}
	public function process_threeDSecure_initiateAuthentication(){
		$url = get_site_url() . '/?rest_route=/gpapi/process_threeDSecure_initiateAuthentication';
		var_dump( $url );
		die();
	}
	public function process_threeDSecure_challengeNotification(){
		$url = get_site_url() . '/?rest_route=/gpapi/process_threeDSecure_challengeNotification';
		var_dump( $url );
		die();
	}


    public function set_defaults()
    {
        // this hook is fired in the middle of a JavaScript switch statement,
        // so we need to add a case for our new field types
        ?>
        case 'gpcreditcard' :
            field.label = 'Secure Credit Card'; //setting the default field label
            break;
        <?php
    }

    /**
     * @return mixed[]
     */
    public function plugin_settings_fields()
    {
        return array(
            array(
                'title' => __('Global Payments API', $this->_slug),
                'fields' => $this->sdkSettingsFields(),
            ),
            array(
                'title' => __('Velocity Limits', $this->_slug),
                'fields' => $this->vmcSettingsFields(),
            ),
            array(
                'title' => __('AVS/CVV Settings', $this->_slug),
                'fields' => $this->avsCvvSettingsFields(),
            ),
        );
    }

    /**
     * @return false|string
     */
    public function feed_list_message()
    {
        if ($this->_requires_credit_card && (!$this->has_gp_payment_fields())) {
            return $this->requires_credit_card_message();
        }

        // from GFFeedAddOn::feed_list_message
        if (!$this->can_create_feed()) {
            return $this->configure_addon_message();
        }

        return false;
    }

    /**
     * @return bool
     */
    private function has_gp_payment_fields()
    {
        $fields = GFAPI::get_fields_by_type($this->get_current_form(), $this->processPaymentsFor);
        return empty($fields) ? false : true;
    }

    /**
     * @param $form
     *
     * @return bool
     */
    private function has_credit_card_fields($form)
    {
        if (empty($this->isCC)) {
            $fields = GFAPI::get_fields_by_type($form, $this->ccFields);
            $this->isCC = empty($fields) ? false : true;
        }
        return $this->isCC;
    }

    /**
     * @return array
     */
    public function vmcSettingsFields()
    {
        return include plugin_dir_path(__DIR__) . 'etc/fraud-settings.php';
    }

    /**
     * @return array
     */
    public function sdkSettingsFields()
    {
        return include plugin_dir_path(__DIR__) . 'etc/sdk-settings.php';
    }

    /**
     * @return array
     */
    public function feed_settings_fields()
    {
        $default_settings = parent::feed_settings_fields();

        // removes 'Options' checkboxes
        $default_settings = $this->remove_field('options', $default_settings);

        if ($this->getAllowPaymentActionOverride() == 'yes') {
            $authorize_or_charge_field = array(
                'name' => 'authorize_or_charge',
                'label' => __('Payment Action', $this->_slug),
                'type' => 'select',
                'default_value' => 'charge',
                'tooltip' => __(
                    'Choose whether you wish to capture funds immediately or authorize payment only.',
                    $this->_slug
                ),
                'choices' => array(
                     array(
                        'label' => __('Authorize + Capture', $this->_slug),
                        'value' => 'charge',
                        'selected' => $this->getPaymentAction() == 'charge',
                    ),
                    array(
                        'label' => __('Authorize only', $this->_slug),
                        'value' => 'authorize',
                        'selected' => $this->getPaymentAction() == 'authorize',
                    ),
                ),
            );

            $default_settings = $this->add_field_after('paymentAmount', $authorize_or_charge_field, $default_settings);
        }

        return $default_settings;
    }

    /**
     * @return array
     */
    public function scripts()
    {
        $this->isCert = (
            false !== strpos(
                (string)trim(
                    $this->get_setting(
                        'public_api_key', '',
                        $this->get_plugin_settings()
                    )
                ), '_cert_')
        );
        $scripts = array(
            array(
                'handle' => 'globalpayments_js',
                'src' => 'https://js.globalpay.com/v1/globalpayments.js',
                'version' => $this->_version,
                'deps' => array(),
                'enqueue' => array(
                    array(
                        'admin_page' => array('plugin_settings'),
                        'tab' => array($this->_slug, $this->get_short_title()),
                        'frontend' => array($this, 'hasFeedCallback'),
                    ),
                ),
            ),
            array(
                'handle' => 'gforms_globalpayments_frontend',
                'src' => $this->get_base_url() . '/../assets/frontend/js/globalpayments.js',
                'version' => $this->_version,
                'deps' => array('jquery', 'globalpayments_js'),
                'in_footer' => false,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                ),
            ),
            array(
                'handle' => 'gform_json',
                'src' => GFCommon::get_base_url() . '/js/jquery.json-1.3.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'in_footer' => false,
                'enqueue' => array(
                    array($this, 'hasFeedCallback'),
                ),
            ),
            array(
                'handle' => 'gforms_globalpayments_admin',
                'src' => $this->get_base_url() . '/../assets/frontend/js/globalpayments-admin.js',
                'version' => $this->_version,
                'deps' => array('jquery'),
                'in_footer' => false,
                'enqueue' => array(
                    array(
                        'admin_page' => array('plugin_settings', 'form_editor'),
                        'tab' => array($this->_slug, $this->get_short_title()),
                    ),
                ),
                'strings' => array(
                    'spinner' => GFCommon::get_base_url() . '/images/spinner.gif',
                ),
            ),
        );

        return array_merge(parent::scripts(), $scripts);
    }

    /**
     * @return array
     */
    public function styles()
    {
        $styles = array(
            array(
                'handle' => 'globalpayments_css',
                'src' => $this->get_base_url() . '/../assets/frontend/css/style.css',
                'version' => $this->_version,
                'enqueue' => array(
                    array($this,'hasFeedCallback'),
                ),
            ),
        );

        return array_merge(parent::styles(), $styles);
    }

    public function add_theme_scripts()
    {
        wp_enqueue_style('style', $this->get_base_url() . '/../assets/frontend/css/style.css', array(), '1.1', 'all');

        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function init_frontend()
    {
        add_filter('gform_register_init_scripts', array($this, 'registerInitScripts'), 10, 3);
        add_filter('gform_field_content', array($this, 'addGlobalPaymentsInputs'), 10, 5);
        parent::init_frontend();
    }

    /**
     * @param $form
     * @param $field_values
     * @param $is_ajax
     */
    public function registerInitScripts($form, $field_values, $is_ajax)
    {
        if (!$this->has_feed($form['id'])) {
            return;
        }

        if (!$this->has_credit_card_fields($form)) {
            return;
        }

        $cc_field = $this->get_credit_card_field($form);

        if ($cc_field === false) {
            $cc_field = $this->get_gpcredit_card_field($form);
        }

        $gateway = $this->getGateway();

        $args = array(
            'credentials' => $gateway->getFrontendGatewayOptions(),
            'fields' => $gateway->securePaymentFieldsConfiguration(),
            'formId' => $form['id'],
            'ccFieldId' => $cc_field['id'],
            'ccPage' => rgar($cc_field, 'pageNumber'),
            'isAjax' => $is_ajax,
            'pageNo' => rgpost('gform_source_page_number_'.$form['id'].''),
            'baseUrl' => plugins_url('', dirname(__FILE__) . '../'),
        );

        $script = 'new window.GlobalPaymentsGravityForms(' . json_encode($args) . ');';
        GFFormDisplay::add_init_script($form['id'], 'globalpayments', GFFormDisplay::ON_PAGE_RENDER, $script);
    }

    /**
     * @param $content
     * @param $field
     * @param $value
     * @param $lead_id
     * @param $form_id
     *
     * @return string
     */
    public function addGlobalPaymentsInputs($content, $field, $value, $lead_id, $form_id)
    {
        $type = GFFormsModel::get_input_type($field);
        $globalPaymentsFieldFound = in_array($type, $this->processPaymentsFor);
        $hasFeed = $this->has_feed($form_id);

        if (!$globalPaymentsFieldFound) {
            return $content;
        }

        if ($this->getGlobalPaymentsJsResponse()) {
            $content .= '<input type=\'hidden\' name=\'globalpayments_response\' id=\'gf_globalpayments_response\' value=\'' . rgpost('globalpayments_response') . '\' />';
        }

        if (!$hasFeed && $globalPaymentsFieldFound) { // Style sheet wont have loaded
            $fieldLabel = $field->label;
            $content = '<span style="color:#ce1025 !important;padding-left:3px;font-size:20px !important;font-weight:700 !important;">Your ['.$fieldLabel.'] seems to be missing a feed. Please check your configuration!!</span>';
        }

        return $content;
    }

    /**
     * @param array $validationResult
     *
     * @return array
     */
    public function maybe_validate($validationResult, $context = 'api-submit')
    {
        if (!$this->has_feed($validationResult['form']['id'], true)) {
            return $validationResult;
        }

        foreach ($validationResult['form']['fields'] as $field) {
            $currentPage = GFFormDisplay::get_source_page($validationResult['form']['id']);
            $fieldOnCurrentPage = $currentPage > 0 && $field['pageNumber'] == $currentPage;
            $fieldType = GFFormsModel::get_input_type($field);

            if (!in_array($fieldType, $this->processPaymentsFor) || !$fieldOnCurrentPage) {
                continue;
            }

            if ($this->getGlobalPaymentsJsError() && $this->hasPayment($validationResult)) {
                $field['failed_validation'] = true;
                $field['validation_message'] = 'The following error occured: ['.$this->getGlobalPaymentsJsError() . ']';
            } else {
                $field['failed_validation'] = false;
            }

            $validationResult['is_valid'] = !$field['failed_validation'];

            break;
        }

        return parent::maybe_validate($validationResult);
    }

    /**
     * @param $validation_result
     *
     * @return mixed
     */
    public function validation($validation_result)
    {
        if (!rgar($validation_result['form'], 'id', false)) {
            return $validation_result;
        }

        if (!$this->has_feed($validation_result['form']['id'], true)) {
            return $validation_result;
        }

        $this->isCC = false;
        foreach ($validation_result['form']['fields'] as $field) {
            $current_page = GFFormDisplay::get_source_page($validation_result['form']['id']);

            if ($current_page > 0) {
                $field_on_curent_page = $field['pageNumber'] == $current_page;
            } else {
                $field_on_curent_page = true;
            }

            $fieldType = GFFormsModel::get_input_type($field);

            if (in_array($fieldType, $this->ccFields) && $field_on_curent_page) {
                $this->isCC = $field;
                if ($this->getGlobalPaymentsJsError() && $this->hasPayment($validation_result)) {
                    $field['failed_validation'] = true;
                    $field['validation_message'] = $this->getGlobalPaymentsJsError();
                } else {
                    // override validation in case user has marked field as required allowing globalpayments to handle cc validation
                    $field['failed_validation'] = false;
                }
            }
        }
        // revalidate the validation result
        $validation_result['is_valid'] = true;
        foreach ($validation_result['form']['fields'] as $field) {
            if (in_array($field['type'], $this->processPaymentsFor)
                && false !== $this->isCC
                && false === $this->isCC->failed_validation
            ) {
                continue;
            }

            if ($field['failed_validation']) {
                $validation_result['is_valid'] = false;
                break;
            }
        }

        return parent::validation($validation_result);
    }

    /**
     *
     * @param $feed - Current configured payment feed
     * @param $submission_data - Contains form field data submitted by the user as well as payment information (i.e. payment amount, setup fee, line items, etc...)
     * @param $form - Current form array containing all form settings
     * @param $entry - Current entry array containing entry information (i.e data submitted by users). NOTE: the entry hasn't been saved to the database at this point, so this $entry object does not have the 'ID' property and is only a memory representation of the entry.
     *
     * @return array - Return an $authorization array in the following format:
     * [
     *  'is_authorized' => true|false,
     *  'error_message' => 'Error message',
     *  'transaction_id' => 'XXX',
     *
     *  //If the payment is captured in this method, return a 'captured_payment' array with the following information about the payment
     *  'captured_payment' => ['is_success'=>true|false, 'error_message' => 'error message', 'transaction_id' => 'xxx', 'amount' => 20]
     * ]
     */
    public function authorize($feed, $submission_data, $form, $entry)
    {

        //print_r($submission_data);
        //die();

        $auth = array(
            'is_authorized' => false,
            'captured_payment' => array('is_success' => false),
        );
        $this->includeDependencies();

        $submission_data = array_merge($submission_data, $this->get_submission_data($feed, $form, $entry));
        $isCCData = $this->getGlobalPaymentsJsResponse();

        if (false === $this->isCC || empty($isCCData->paymentReference)) {
            return $auth;
        }

        $this->populateCreditCardLastFour($form);

        $this->velocityPreCheck();

        if ($this->getGlobalPaymentsJsError()) {
            return $this->authorization_error($this->getGlobalPaymentsJsError());
        }

        $isAuth = $this->getPaymentAction($feed) == 'authorize';

        try {
            $response = $this->getGlobalPaymentsJsResponse();

            $gateway = $this->getGateway();
            $gateway->paymentAction = $this->getPaymentAction($feed);

            $order = new Order();
            $order->cardHolderName = $this->getCardHolderName($feed, $submission_data, $entry);
            $order->billingAddress = $this->buildAddress($feed, $submission_data, $entry);
            $order->currency = GFCommon::get_currency();
            $order->amount = $submission_data['payment_amount'];
            $order->cardData = $response;

            $transaction = $gateway->processPayment($order);

            //reverse incase of avs/cvv failure
            $this->checkAvsCvvResults($transaction, $submission_data['payment_amount']);

            do_action('globalpayments_gravityforms_transaction_success', $form, $entry, $transaction, $response);
            self::get_instance()->transaction_response = $transaction;

            $type = $isAuth
                ? 'Authorization'
                : 'Payment';
            $amount_formatted = GFCommon::to_money($submission_data['payment_amount'], GFCommon::get_currency());
            $note = sprintf(
                __('%s has been completed. Amount: %s. Transaction Id: %s.', $this->_slug),
                $type,
                $amount_formatted,
                $transaction->transactionId
            );

            if ($isAuth) {
                $note .= sprintf(__(' Authorization Code: %s', $this->_slug), $transaction->authorizationCode);
            }

            $auth = array(
                'is_authorized' => true,
                'captured_payment' => array(
                    'is_success' => true,
                    'transaction_id' => $transaction->transactionId,
                    'amount' => $submission_data['payment_amount'],
                    'payment_method' => $response->details->cardType,
                    'globalpayments_payment_action' => $this->getPaymentAction($feed),
                    'note' => $note,
                ),
            );
        } catch (ApiException $e) {
            do_action('globalpayments_gravityforms_transaction_failure', $form, $entry, $e);
            $this->updateVelocityCheckData($e);
            $auth = $this->authorization_error($e->getMessage());
        } catch (Exception $e) {
            do_action('globalpayments_gravityforms_transaction_failure', $form, $entry, $e);
            $auth = $this->authorization_error($e->getMessage());
        }

        return $auth;
    }

    /**
     * @param $form
     *
     * @return bool|\GF_Field
     */
    private function get_gpcredit_card_field($form)
    {
        $fields = GFAPI::get_fields_by_type($form, array('gpcreditcard'));
        return empty($fields) ? false : $fields[0];
    }

    // Helper functions

    /**
     * @param       $entry
     * @param array $result
     *
     * @return mixed
     */
    public function updateAuthorizationEntry($entry, $result = array())
    {
        if (isset($result['globalpayments_payment_action'])
            && $result['globalpayments_payment_action'] == 'authorize'
            && isset($result['is_success'])
            && $result['is_success']
        ) {
            $entry['payment_status'] = __('Authorized', $this->_slug);
            GFAPI::update_entry($entry);
        }

        return $entry;
    }

    /**
     * @param array $feed
     * @param array $submission_data
     * @param array $entry
     *
     * @return string
     */
    private function getCardHolderName($feed, $submission_data, $entry)
    {
        return rgar($submission_data, 'card_name');
    }

    /**
     * @param array $feed
     * @param array $submission_data
     * @param array $entry
     *
     * @return array
     */
    private function buildAddress($feed, $submission_data, $entry)
    {
        $isRecurring = isset($feed['meta']['transactionType']) && $feed['meta']['transactionType'] == 'subscription';
        $address = array();

        $address['streetAddress1'] = rgar($submission_data, 'address')
            . rgar($submission_data, 'address2');
        if (empty($address['streetAddress1']) && in_array('billingInformation_address', $feed['meta'])) {
            $address['streetAddress1']
                = $entry[ $feed['meta']['billingInformation_address'] ] . $entry[ $feed['meta']['billingInformation_address2'] ];
        }

        $address['city'] = rgar($submission_data, 'city');
        if (empty($address['city']) && in_array('billingInformation_city', $feed['meta'])) {
            $address['city'] = $entry[ $feed['meta']['billingInformation_city'] ];
        }

        $address['province'] = rgar($submission_data, 'state');
        if (empty($address['province']) && in_array('billingInformation_state', $feed['meta'])) {
            $address['province'] = $entry[ $feed['meta']['billingInformation_state'] ];
        }

        $address['postalCode'] = rgar($submission_data, 'zip');
        if (empty($address['postalCode']) && in_array('billingInformation_zip', $feed['meta'])) {
            $address['postalCode'] = $entry[ $feed['meta']['billingInformation_zip'] ];
        }

        $address['country'] = $this->normalizeCountry(rgar($submission_data, 'country'), $isRecurring);
        if (empty($address['country']) && in_array('billingInformation_country', $feed['meta'])) {
            $address['country'] = $this->normalizeCountry($entry[ $feed['meta']['billingInformation_country'] ], $isRecurring);
        }

        return $address;
    }

    /**
     * @param mixed $validation_result
     *
     * @return bool
     */
    public function hasPayment($validation_result)
    {
        $form = $validation_result['form'];
        $entry = GFFormsModel::create_lead($form);
        $feed = $this->get_payment_feed($entry, $form);

        if (!$feed) {
            return false;
        }

        $submission_data = $this->get_submission_data($feed, $form, $entry);

        // Do not process payment if payment amount is 0 or less
        return floatval($submission_data['payment_amount']) > 0;
    }

    /**
     * @param $form
     */
    public function populateCreditCardLastFour($form)
    {
        $cc_field = $this->get_credit_card_field($form);
        if (false === $cc_field) {
            return;
        }

        $response = $this->getGlobalPaymentsJsResponse();
        $_POST[ 'input_' . $cc_field['id'] . '_1' ] = 'XXXXXXXXXXXX' . ($response != null
                ? $response->details->cardLast4
                : '');
        $_POST[ 'input_' . $cc_field['id'] . '_4' ] = ($response != null
            ? $response->details->cardType
            : '');
    }

    public function includeDependencies()
    {
        require_once plugin_dir_path(__DIR__) . 'vendor/autoload.php';
        do_action('gform_globalpayments_post_include_api');
    }

    /**
     * @param null $feed
     *
     * @return string
     */
    public function getPaymentAction($feed = null)
    {
        if ($feed != null && isset($feed['meta']['authorize_or_charge'])) {
            return (string)$feed['meta']['authorize_or_charge'];
        }
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('authorize_or_charge', 'charge', $settings);
    }

    /**
     * @return string
     */
    public function getAllowPaymentActionOverride()
    {
        $settings = $this->get_plugin_settings();

        return (string)$this->get_setting('allow_payment_action_override', 'no', $settings);
    }

    /**
     * @param $form
     *
     * @return bool
     */
    public function hasFeedCallback($form)
    {
        return $form && $this->has_feed($form['id']);
    }

    /**
     * @return array|mixed|object
     */
    public function getGlobalPaymentsJsResponse()
    {
        return json_decode(rgpost('globalpayments_response'));
    }

    /**
     * @return bool
     */
    public function getGlobalPaymentsJsError()
    {
        $response = $this->getGlobalPaymentsJsResponse();

        if (isset($response->error)) {
            return $response->error->message;
        }

        return false;
    }

    /**
     * @param $field
     * @param $parent
     */
    public function isFieldOnValidPage($field, $parent)
    {
        $form = $this->get_current_form();

        $mapped_field_id = $this->get_setting($field['name']);
        $mapped_field = GFFormsModel::get_field($form, $mapped_field_id);
        $mapped_field_page = rgar($mapped_field, 'pageNumber');

        $cc_field = $this->get_credit_card_field($form);
        $cc_page = rgar($cc_field, 'pageNumber');

        if ($mapped_field_page > $cc_page) {
            $this->set_field_error(
                $field,
                __('The selected field needs to be on the same page as the Credit Card field or a previous page.', $this->_slug)
            );
        }
    }

    /**
     * @param $text
     * @param $form
     * @param $entry
     * @param $url_encode
     * @param $esc_html
     * @param $nl2br
     * @param $format
     *
     * @return mixed
     */
    public function replaceMergeTags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format)
    {
        $mergeTags = array(
            'transactionId' => '{globalpayments_transaction_id}',
            'authorizationCode' => '{globalpayments_authorization_code}',
        );

        $gFormsKey = array('transactionId' => 'transaction_id',);

        foreach ($mergeTags as $key => $mergeTag) {
            // added for GF 1.9.x
            if (strpos($text, $mergeTag) === false || empty($entry) || empty($form)) {
                return $text;
            }

            $value = '';
            if (class_exists('GFGlobalPayments') && isset(GFGlobalPayments::get_instance()->transaction_response)) {
                $value = GFGlobalPayments::get_instance()->transaction_response->$key;
            }

            if (isset($gFormsKey[ $key ]) && empty($value)) {
                $value = rgar($entry, $gFormsKey[ $key ]);
            }

            $text = str_replace($mergeTag, $value, $text);
        }

        return $text;
    }

    /**
     * @param $form
     *
     * @return mixed
     */
    public function addClientSideMergeTags($form)
    {
        include plugin_dir_path(__FILE__) . '../templates/client-side-merge-tags.php';

        return $form;
    }

    /**
     * Attempts to get real ip even if there is a proxy chain
     *
     * @return string
     */
    private function getRemoteIP()
    {
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $remoteIPArray = array_values(
                array_filter(
                    explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])
                )
            );
            $remoteIP = end($remoteIPArray);
        }

        return $remoteIP;
    }

    /**
     * Gets the payment validation result.
     *
     * @since  Unknown
     * @access public
     *
     * @used-by GFPaymentAddOn::validation()
     *
     * @param array $validationResult    Contains the form validation results.
     * @param array $authorizationResult Contains the form authorization results.
     *
     * @return array The validation result for the credit card field.
     */
    public function get_validation_result($validationResult, $authorizationResult)
    {
        $page = 0;
        foreach ($validationResult['form']['fields'] as $field) {
            if ($field->type == 'gpcreditcard' && !$authorizationResult['is_authorized']) {
                $field->failed_validation  = true;
                $field->validation_message = $authorizationResult['error_message'] ?? '';
                $page                      = $field->pageNumber;
                break;
            }
        }
        $validationResult['credit_card_page'] = $page;
        $validationResult['is_valid']         = true;
        return parent::get_validation_result($validationResult, $authorizationResult);
    }

    private function getGateway()
    {
        $this->includeDependencies();

        $gateway = null;
        $settings = $this->get_plugin_settings();
        $gatewayType = (string)trim($this->get_setting('gateway_type', '', $settings));
        $is_sandbox_mode = (string)trim($this->get_setting('is_sandbox_mode', '', $settings));

        switch ($gatewayType) {
            case 'transit':
	            $gateway = new TransitGateway();
	            $gateway->merchantId = (string)trim($this->get_setting('merchant_id', '', $settings));
	            $gateway->username = (string)trim($this->get_setting('username', '', $settings));
	            $gateway->password = (string)trim($this->get_setting('password', '', $settings));
	            $gateway->deviceId = (string)trim($this->get_setting('device_id', '', $settings));
	            $gateway->tsepDeviceId = (string)trim($this->get_setting('tsep_device_id', '', $settings));
	            $gateway->transactionKey = (string)trim($this->get_setting('transaction_key', '', $settings));
	            $gateway->developerId = (string)trim($this->get_setting('developer_id', '', $settings));
                break;
            case 'gpapi':
	            $gateway = new GpApiGateway();
	            // configure gateway settings
	            $gateway->appId = (string)trim($this->get_setting('app_id_gpapi', '', $settings));
	            $gateway->appKey = (string)trim($this->get_setting('app_key_gpapi', '', $settings));
	            $gateway->country = 'US';
	            $gateway->isProduction = false;
	            $gateway->methodNotificationUrl = '';
	            $gateway->challengeNotificationUrl = '';
	            $gateway->paymentAction = TransactionType::AUTHORIZE;
                break;
            default:
	            $gateway = new HeartlandGateway();
	            $gateway->publicKey = (string)trim($this->get_setting('public_api_key', '', $settings));
	            $gateway->secretKey = (string)trim($this->get_setting('secret_api_key', '', $settings));
                break;
        }

        // is_sandbox_mode is not setted on the admin, always is false. so always ispoduction is true
        // $gateway->isProduction = $is_sandbox_mode !== 'yes';

        $this->gateway = $gateway;

        return $gateway;
    }

    protected function normalizeCountry($country, $isRecurring = false)
    {
        switch (strtolower($country)) {
            case null:
            case '':
            case 'us':
            case 'usa':
            case 'united states':
            case 'united states of america':
                return 'USA';
            case 'ca':
            case 'can':
            case 'cana':
            case 'cgg':
            case 'canada':
                return 'CAN';
            default:
                if ($isRecurring) {
                    throw new ApiException(sprintf('Country "%s" is currently not supported', $country));
                }
                return null;
        }
    }

    protected function normalizeState($state)
    {
        $na_state_abbreviations  = array(
            // United States
            'ALABAMA' => 'AL',
            'ALASKA' => 'AK',
            'ARIZONA' => 'AZ',
            'ARKANSAS' => 'AR',
            'CALIFORNIA' => 'CA',
            'COLORADO' => 'CO',
            'CONNECTICUT' => 'CT',
            'DELAWARE' => 'DE',
            'DISTRICT OF COLUMBIA' => 'DC',
            'FLORIDA' => 'FL',
            'GEORGIA' => 'GA',
            'HAWAII' => 'HI',
            'IDAHO' => 'ID',
            'ILLINOIS' => 'IL',
            'INDIANA' => 'IN',
            'IOWA' => 'IA',
            'KANSAS' => 'KS',
            'KENTUCKY' => 'KY',
            'LOUISIANA' => 'LA',
            'MAINE' => 'ME',
            'MARYLAND' => 'MD',
            'MASSACHUSETTS' => 'MA',
            'MICHIGAN' => 'MI',
            'MINNESOTA' => 'MN',
            'MISSISSIPPI' => 'MS',
            'MISSOURI' => 'MO',
            'MONTANA' => 'MT',
            'NEBRASKA' => 'NE',
            'NEVADA' => 'NV',
            'NEW HAMPSHIRE' => 'NH',
            'NEW JERSEY' => 'NJ',
            'NEW MEXICO' => 'NM',
            'NEW YORK' => 'NY',
            'NORTH CAROLINA' => 'NC',
            'NORTH DAKOTA' => 'ND',
            'OHIO' => 'OH',
            'OKLAHOMA' => 'OK',
            'OREGON' => 'OR',
            'PENNSYLVANIA' => 'PA',
            'RHODE ISLAND' => 'RI',
            'SOUTH CAROLINA' => 'SC',
            'SOUTH DAKOTA' => 'SD',
            'TENNESSEE' => 'TN',
            'TEXAS' => 'TX',
            'UTAH' => 'UT',
            'VERMONT' => 'VT',
            'VIRGINIA' => 'VA',
            'WASHINGTON' => 'WA',
            'WEST VIRGINIA' => 'WV',
            'WISCONSIN' => 'WI',
            'WYOMING' => 'WY',
            'ARMED FORCES AMERICAS' => 'AA',
            'ARMED FORCES EUROPE' => 'AE',
            'ARMED FORCES PACIFIC' => 'AP',
            // Canada
            'ALBERTA' => 'AB',
            'BRITISH COLUMBIA' => 'BC',
            'MANITOBA' => 'MB',
            'NEW BRUNSWICK' => 'NB',
            'NEWFOUNDLAND AND LABRADOR' => 'NL',
            'NORTHWEST TERRITORIES' => 'NT',
            'NOVA SCOTIA' => 'NS',
            'NUNAVUT' => 'NU',
            'ONTARIO' => 'ON',
            'PRINCE EDWARD ISLAND' => 'PE',
            'QUEBEC' => 'QC',
            'SASKATCHEWAN' => 'SK',
            'YUKON' => 'YT',
        );

        $state_uc = strtoupper($state);

        if (!empty($na_state_abbreviations[$state_uc])) {
            return $na_state_abbreviations[$state_uc];
        }

        if (in_array($state_uc, $na_state_abbreviations, true)) {
            return $state_uc;
        }

        throw new ApiException(sprintf('State/Province "%s" is currently not supported', $state));
    }

    protected function velocityPreCheck()
    {
        /** Currently saved plugin settings */
        $settings = $this->get_plugin_settings();

        /** This is the message show to the consumer if the rule is flagged */
        $fraud_message = (string)$this->get_setting(
            'fraud_message',
            'Please contact us to complete the transaction.',
            $settings
        );

        /** Maximum number of failures allowed before rule is triggered */
        $fraud_velocity_attempts = (int)$this->get_setting('fraud_velocity_attempts', '3', $settings);

        /** Variable name with hash of IP address to identify uniqe transient values         */
        $HPS_VarName = (string)'HeartlandHPS_Velocity_' . md5($this->getRemoteIP());

        /** Running count of failed transactions from the current IP*/
        $HeartlandHPS_FailCount = (int)get_transient($HPS_VarName);

        /** Defaults to true or checks actual settings for this plugin from $settings. If true the following settings are applied:
         *
         * $fraud_message
         *
         * $fraud_velocity_attempts
         *
         * $fraud_velocity_timeout
         *
         */
        $enable_fraud = (bool)($this->get_setting('enable_fraud', 'true', $settings) === 'true');

        /**
         * if fraud_velocity_attempts is less than the $HeartlandHPS_FailCount then we know
         * far too many failures have been tried
         */
        if ($enable_fraud && $HeartlandHPS_FailCount >= $fraud_velocity_attempts) {
            sleep(5);
            $issuerResponse = (string)get_transient($HPS_VarName . 'IssuerResponse');
            return $this->authorization_error(wp_sprintf('%s %s', $fraud_message, $issuerResponse));
        }

        return null;
    }

    protected function updateVelocityCheckData($e)
    {
        /** Currently saved plugin settings */
        $settings = $this->get_plugin_settings();

        /** Maximum number of failures allowed before rule is triggered */
        $fraud_velocity_attempts = (int)$this->get_setting('fraud_velocity_attempts', '3', $settings);

        /** Maximum amount of time in minutes to track failures. If this amount of time elapse between failures then the counter($HeartlandHPS_FailCount) will reset */
        $fraud_velocity_timeout = (int)$this->get_setting('fraud_velocity_timeout', '10', $settings);

        /** Variable name with hash of IP address to identify uniqe transient values         */
        $HPS_VarName = (string)'HeartlandHPS_Velocity_' . md5($this->getRemoteIP());

        /** Running count of failed transactions from the current IP*/
        $HeartlandHPS_FailCount = (int)get_transient($HPS_VarName);

        /** Defaults to true or checks actual settings for this plugin from $settings. If true the following settings are applied:
         *
         * $fraud_message
         *
         * $fraud_velocity_attempts
         *
         * $fraud_velocity_timeout
         *
         */
        $enable_fraud = (bool)($this->get_setting('enable_fraud', 'true', $settings) === 'true');
        // if advanced fraud is enabled, increment the error count
        if ($enable_fraud) {
            if (empty($HeartlandHPS_FailCount)) {
                $HeartlandHPS_FailCount = 0;
            }

            set_transient(
                $HPS_VarName,
                $HeartlandHPS_FailCount + 1,
                MINUTE_IN_SECONDS * $fraud_velocity_timeout
            );

            if ($HeartlandHPS_FailCount < $fraud_velocity_attempts) {
                set_transient(
                    $HPS_VarName . 'IssuerResponse',
                    $e->getMessage(),
                    MINUTE_IN_SECONDS * $fraud_velocity_timeout
                );
            }
        }
    }

    /**
     * @return array
     */
    public function avsCvvSettingsFields()
    {
        return include plugin_dir_path(__DIR__) . 'etc/avs-cvv-settings.php';
    }

    public function checkAvsCvvResults($transaction, $amount){
        $settings = $this->get_plugin_settings();
        $checkAvsCvv = $this->get_setting("check_avs_cvv", '', $settings);
        if($checkAvsCvv === 'yes'){
            $avsRejectConditions = $this->get_setting("avs_reject_conditions", '', $settings);
            $cvnRejectConditions = $this->get_setting("cvn_reject_conditions", '', $settings);

            //reverse incase of AVS/CVN failure
            if(!empty($transaction->transactionReference->transactionId)){
                if(!empty($transaction->avsResponseCode) || !empty($transaction->cvnResponseCode)){
                    //check admin selected decline condtions
                    if(in_array($transaction->avsResponseCode, $avsRejectConditions) ||
                    in_array($transaction->cvnResponseCode, $cvnRejectConditions)){
                        Transaction::fromId( $transaction->transactionReference->transactionId )
                        ->reverse( $amount )
                        ->execute();

                        throw new Exception('Transaction failed due to AVS/CVV failure. Contact merchant!');
                    }
                }
            }
        }
    }
}
