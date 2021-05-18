/*jslint browser:true, unparam:true*/
/*global gp, gforms_globalpayments_admin_strings, ajaxurl*/
(function (window, $) {
  window.GlobalPaymentsAdminClass = function () {
    this.initEnableGatewaySettingsToggle = function () {
      this.toggleGatewaySettings($('#gaddon-setting-row-gateway_type select').val());
    };

    this.toggleGatewaySettings = function (value) {
      $('.gateway-setting').parents('[id^="gaddon-setting-row-"]').hide();
      $('.gateway-' + value).parents('[id^="gaddon-setting-row-"]').show();
    };

    this.initAdminCCFields = function () {
      var $fields = $('#iframesCardNumber,#iframesCardExpiration,#iframesCardCvv');
      var that = this;
      if ($fields.length > 0) {
        $fields.children().remove();
        $fields.each(function (i, field) {
          field.append(that.getDummyField(field));
        });
      }
    };

    this.getDummyField = function (field) {
      var input = document.createElement('input');
      input.type = 'tel';
      input.disabled = true;

      switch (field.id) {
        case 'iframesCardNumber':
          input.placeholder = '•••• •••• •••• ••••';
          break;
        case 'iframesCardExpiration':
          input.placeholder = 'MM / YYYY';
          break;
        case 'iframesCardCvv':
          input.placeholder = 'CVV';
          break;
      }

      return input;
    };
  };

  $(document).ready(function () {
    window.GlobalPaymentsAdmin = new window.GlobalPaymentsAdminClass();

    window.GlobalPaymentsAdmin.initEnableGatewaySettingsToggle();
    window.GlobalPaymentsAdmin.initAdminCCFields();
  });
})(window, window.jQuery);