<script type="text/javascript">
    if (window.gform) {
        gform.addFilter("gform_merge_tags", "add_merge_tags");
    }
    function add_merge_tags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option){
        mergeTags["custom"].tags.push({ tag: '{globalpayments_transaction_id}', label: 'GlobalPayments Transaction ID' });
        mergeTags["custom"].tags.push({ tag: '{globalpayments_authorization_code}', label: 'GlobalPayments Authorization Code' });

        return mergeTags;
    }
</script>
