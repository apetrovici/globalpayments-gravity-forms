<div class="ginput_complex ginput_container ginput_container_creditcard gp_secure_cc" id="<?php echo $field_id; ?>">
  <div id="HPS_secure_cc">
    <div class="ss-shield"<?php echo $this->get_tabindex(); // added to get rid of GF silly confusion for the menu item being the next index from the first input ?>></div>
    <div id="global-payments-card-holder-name" class="form-group">
      <label for="iframesCardHolder">Card Holder</label>
      <input type="text" name="card_name" placeholder="John Doe" <?php echo $this->get_tabindex(); ?>/><br /><br />
    </div>
    <!-- The Payment Form -->
    <div id="global-payments-card-number" class="form-group">
      <label for="iframesCardNumber">Card Number</label>
      <div class="iframeholder" id="iframesCardNumber" <?php echo $this->get_tabindex(); ?>></div>
    </div>
    <div id="global-payments-expiration-date" class="form-group">
      <label for="iframesCardExpiration">Card Expiration</label>
      <div class="iframeholder" id="iframesCardExpiration" <?php echo $this->get_tabindex(); ?>></div>
    </div>
    <div id="global-payments-cvv" class="form-group">
      <label for="iframesCardCvv">Card CVV</label>
      <div class="iframeholder" id="iframesCardCvv" <?php echo $this->get_tabindex(); ?>></div>
    </div>
  </div>
</div>

<?php if (is_admin()): ?>
  <script>
    window.GlobalPaymentsAdmin = window.GlobalPaymentsAdmin || {};
    window.GlobalPaymentsAdmin.initAdminCCFields = window.GlobalPaymentsAdmin.initAdminCCFields || function () {};
    window.GlobalPaymentsAdmin.initAdminCCFields();
  </script>
<?php endif; ?>
