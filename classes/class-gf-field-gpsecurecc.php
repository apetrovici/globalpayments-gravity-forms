<?php

if (!class_exists('GFForms')) {
    die();
}

/**
 * Class GF_Field_GPCreditCard
 */
class GF_Field_GPCreditCard extends GF_Field
{
    /**
     * @var string
     */
    public const TYPE = 'gpcreditcard';

    /**
     * @var string
     */
    public $type = self::TYPE;

    protected $_slug = 'gravityforms-globalpayments';

    /**
     * @return string
     */
    public function get_form_editor_field_title()
    {

        return esc_attr__('Secure Credit Card', 'gravityforms');
    }

    /**
     * @return array
     */
    public function get_form_editor_button()
    {
        return array(); // this button is conditionally added in the form detail page
    }

    /**
     * Returns the class names of the settings which should be available on the field in the form editor.
     *
     * @return array
     */
    public function get_form_editor_field_settings()
    {
        return array(
            'label_setting',
        );
    }

    /**
     * Returns the field inner markup.
     *
     * @param array $form The Form Object currently being processed.
     * @param string|array $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
     * @param null|array $entry Null or the Entry Object currently being edited.
     *
     * @return string
     */
    public function get_field_input($form, $value = '', $entry = null)
    {
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $form_id  = $form['id'];
        $id       = intval($this->id);
        $field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $form_id  = ($is_entry_detail || $is_form_editor) && empty($form_id) ? rgget('id') : $form_id;

        ob_start();
        include dirname(__FILE__) . "/../templates/cc-payment-fields.php";
        return ob_get_clean();
    }
}
GF_Fields::register(new GF_Field_GPCreditCard());
