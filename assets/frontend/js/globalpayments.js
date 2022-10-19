// @ts-check

/*global hps, gformInitSpinner*/
(function (window, $, GlobalPayments) {
    'use strict';

    function GlobalPaymentsGravityForms(args) {
        this.form = null;
        this.credentials = null;
        this.fields = null;
        this.pageNo = null;
        this.formId = null;
        this.ccFieldId = null;
        this.ccPage = null;
        this.isAjax = null;
        this.isSecure = false;
        this.isCCA = false;
        this.ccaData = null;
        this.hps = null;
        this.isInit = false;
        this.isCert = false;
        this.cardForm = null;

        var prop;
        for (prop in args) {
            if (args.hasOwnProperty(prop)) {
                this[prop] = args[prop];
            }
        }

        this.ccInputSuffixes = ['1', '2_month', '2_year', '3'];
        this.ccInputPrefix = 'input_' + this.formId + '_' + this.ccFieldId + '_';

        this.init();
    }

    GlobalPaymentsGravityForms.prototype = {
        init: function () {

            if (!this.isCreditCardOnPage()) {
                return;
            }

            // Initialize spinner
            if (!this.isAjax) {
                //gformInitSpinner(this.formId);
            }

            if (!GlobalPayments) {
                console.log('Warning! Global Payments payment fields cannot be loaded ');
                return;
            }

            GlobalPayments.configure(this.credentials);
            this.cardForm = GlobalPayments.ui.form({
            	/*
                 * Configure the iframe fields to tell the library where
                 * the iframe should be inserted into the DOM and some
                 * basic options.
                 */
                fields: this.getFieldConfiguration(),
                styles: this.getStyleConfiguration()
            });

            this.cardForm.on("token-success", this.globalPaymentsResponseHandler.bind(this));
            this.cardForm.on("token-error", this.globalPaymentsResponseHandler.bind(this));
            this.cardForm.on("error", this.globalPaymentsResponseHandler.bind(this));
        },

        getSubmitButton: function () {
            return $('.gform_wrapper input[type="submit"]');
        },

        getFieldConfiguration: function () {
            var $submit = this.getSubmitButton();

            if ($('#iframesCardSubmit').length === 0) {
                // add a div for the submit iframe as a sibling to the original submit
                var container = document.createElement('div');
                container.id = 'iframesCardSubmit';
                $submit.parent().append(container);
                $submit.hide();
            }

            return {
                "card-number": {
                    target:      '#iframesCardNumber',
                    label : this.fields['card-number-field'].label,
                    placeholder: this.fields['card-number-field'].placeholder
                },
                "card-expiration": {
                    target:      '#iframesCardExpiration',
                    label : this.fields['card-expiry-field'].label,
                    placeholder: this.fields['card-expiry-field'].placeholder
                },
                "card-cvv": {
                    target:      '#iframesCardCvv',
                    label : this.fields['card-cvv-field'].label,
                    placeholder: this.fields['card-cvv-field'].placeholder
                },
                "submit": {
                    text: $submit.val(),
                    target: '#iframesCardSubmit'
                }
            };
        },

        getStyleConfiguration: function () {
            var imageBase = 'https://api2.heartlandportico.com/securesubmit.v1/token/gp-1.6.0/assets';
            return {
                'html': {
                    'font-size': '62.5%'
                },
                'body': {
                    'font-size': '1.4rem'
                },
                '#secure-payment-field-wrapper': {
                    'postition': 'relative'
                },
                '#secure-payment-field': {
                    '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                    '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
                    'background-color': '#fff',
                    'border': '1px solid #cecece',
                    'border-radius': '2px',
                    'box-shadow': 'none',
                    'box-sizing': 'border-box',
                    'display': 'block',
                    'font-family': '"Roboto", sans-serif',
                    'font-size': '11px',
                    'font-smoothing': 'antialiased',
                    'height': '35px',
                    'margin': '5px 0 10px 0',
                    'max-width': '100%',
                    'outline': '0',
                    'padding': '0 10px',
                    'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                    'vertical-align': 'baseline',
                    'width': '100%'
                },
                '#secure-payment-field:focus': {
                    'border': '1px solid lightblue',
                    'box-shadow': '0 1px 3px 0 #cecece',
                    'outline': 'none'
                },
                '#secure-payment-field[type=button]': {
                    'text-align': 'center',
                    'text-transform': 'none',
                    'white-space': 'nowrap',

                    'background-image': 'none',
                    'background': '#1979c3',
                    'border': '1px solid #1979c3',
                    'color': '#ffffff',
                    'cursor': 'pointer',
                    'display': 'inline-block',
                    'font-family': '"Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif',
                    'font-weight': '500',
                    'padding': '14px 17px',
                    'font-size': '1.8rem',
                    'line-height': '2.2rem',
                    'box-sizing': 'border-box',
                    'vertical-align': 'middle',
                    'margin': '0',
                    'height': 'initial',
                    'width': 'initial',
                    'flex': 'initial'
                },
                '#secure-payment-field[type=button]:focus': {
                    'outline': 'none',

                    'box-shadow': 'none',
                    'background': '#006bb4',
                    'border': '1px solid #006bb4',
                    'color': '#ffffff'
                },
                '#secure-payment-field[type=button]:hover': {
                    'background': '#006bb4',
                    'border': '1px solid #006bb4',
                    'color': '#ffffff'
                },
                '.card-cvv': {
                    'background': 'transparent url(' + imageBase + '/cvv.png) no-repeat right',
                    'background-size': '60px'
                },
                '.card-cvv.card-type-amex': {
                    'background': 'transparent url(' + imageBase + '/cvv-amex.png) no-repeat right',
                    'background-size': '60px'
                },
                '.card-number': {
                    'background': 'transparent url(' + imageBase + '/logo-unknown@2x.png) no-repeat right',
                    'background-size': '52px'
                },
                '.card-number.invalid.card-type-amex': {
                    'background': 'transparent url(' + imageBase + '/amex-invalid.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '38px'
                },
                '.card-number.invalid.card-type-discover': {
                    'background': 'transparent url(' + imageBase + '/discover-invalid.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '60px'
                },
                '.card-number.invalid.card-type-jcb': {
                    'background': 'transparent url(' + imageBase + '/jcb-invalid.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '38px'
                },
                '.card-number.invalid.card-type-mastercard': {
                    'background': 'transparent url(' + imageBase + '/mastercard-invalid.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '40px'
                },
                '.card-number.invalid.card-type-visa': {
                    'background': 'transparent url(' + imageBase + '/visa-invalid.svg) no-repeat center',
                    'background-position-x': '98%',
                    'background-size': '50px'
                },
                '.card-number.valid.card-type-amex': {
                    'background': 'transparent url(' + imageBase + '/amex.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '38px'
                },
                '.card-number.valid.card-type-discover': {
                    'background': 'transparent url(' + imageBase + '/discover.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '60px'
                },
                '.card-number.valid.card-type-jcb': {
                    'background': 'transparent url(' + imageBase + '/jcb.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '38px'
                },
                '.card-number.valid.card-type-mastercard': {
                    'background': 'transparent url(' + imageBase + '/mastercard.svg) no-repeat center',
                    'background-position-x': '98%',
                    'background-size': '40px'
                },
                '.card-number.valid.card-type-visa': {
                    'background': 'transparent url(' + imageBase + '/visa.svg) no-repeat right center',
                    'background-position-x': '98%',
                    'background-size': '50px'
                },
                '.card-number::-ms-clear': {
                    'display': 'none',
                },
                'input[placeholder]': {
                    'letter-spacing': '.5px',
                }
            };
        },

        // Handles tokenization response
        globalPaymentsResponseHandler: function (response) {

            if ($('#globalpayments_response').length === 0) {
                // Clear any potentially lingering elements
                $('#globalpayments_response').remove();

                var that = this;

                // Add tokenization response to the form
                this.cardForm.frames["card-cvv"].getCvv().then(function (c) {
                    if (!response.error) {
                        response.details.cardSecurityCode = c || response.details.cardSecurityCode;
                    }

                    that.createGlobalPaymentsResponseNode($.toJSON(response));
                    var $form = $('#gform_' + that.formId);
                    $form[0].submit();
                });
            }

            return false;
        },

        isCreditCardOnPage: function () {
            /*
             * If current page is false or no credit card page number,
             * assume this is not a multi-page form
             */
            var currentPage = this.getCurrentPageNumber();
            if (!this.ccPage || !currentPage) {
                return true;
            }
            return this.ccPage === currentPage;
        },

        getCurrentPageNumber: function () {
            var currentPageInput = $('#gform_source_page_number_' + this.formId);
            var currentInput = currentPageInput.val();
            if(currentInput == 0)
            {
                currentInput = this.pageNo;
                $('#gform_source_page_number_' + this.formId).val(currentInput);
            }
            return currentPageInput.length > 0 ? parseInt(currentInput, 10) : false;
        },

        createGlobalPaymentsResponseNode: function (value) {
            var $form = $('#gform_' + this.formId);
            var globalPaymentsResponse = document.createElement('input');
            globalPaymentsResponse.type = 'hidden';
            globalPaymentsResponse.id = 'globalpayments_response';
            globalPaymentsResponse.name = 'globalpayments_response';
            globalPaymentsResponse.value = value;
            $form.append($(globalPaymentsResponse));
        },

        getOrderTotal: function () {
            var $orderTotalElement = $('div.ginput_container_total').find(
                'input[id^="input_' + this.formId + '_"]'
            );
            if ($orderTotalElement) {
                return ($orderTotalElement.val() * 100);
            }
            return 0;
        }
    };

    /** @type {any} */
    (window).GlobalPaymentsGravityForms = GlobalPaymentsGravityForms;
})(
    /**
     * Global `window` reference
     *
     * @type {Window}
     */
    window,
    /**
     * Global `jQuery` reference
     *
     * @type {any}
     */
    (window).jQuery,
    /**
     * Global `GlobalPayments` reference
     *
     * @type {any}
     */
    (window).GlobalPayments
);
