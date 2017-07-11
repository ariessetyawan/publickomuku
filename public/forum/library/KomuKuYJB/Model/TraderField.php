<?php /*55cb793c051a24f611456186615d19f9f93ca53a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_TraderField extends XenForo_Model
{
    public function getTraderFieldById($fieldId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_trader_field
            WHERE field_id = ?', $fieldId
        );
    }

    public function getTraderFields(array $conditions, array $viewParams = array())
    {

    }

    public function getAllTraderFields(array $fetchOptions = array())
    {
        return $this->getTraderFields(array());
    }

    public function prepareTraderFieldConditions(array $conditions, array &$fetchOptions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();

        return $this->getConditionsForClause($sqlConditions);
    }

    public function prepareTraderFieldFetchOptions(array $fetchOptions)
    {

    }

    public function groupTraderFields(array $fields)
    {

    }

    public function prepareTraderField(array $field, $getFieldChoices = false, $fieldValue = null, $valueSaved = true)
    {

    }

    public function prepareTraderFields(array $fields, $getFieldChoices = false, array $fieldValues = array(), $valueSaved = true)
    {

    }

    public function getTraderFieldChoices()
    {

    }

    public function verifyTraderFieldValue()
    {

    }

    public function getTraderFieldGroups()
    {

    }

    public function getTraderFieldTypes()
    {
        return array(
            'textbox' => array(
                'value' => 'textbox',
                'label' => new XenForo_Phrase('single_line_text_box')
            ),
            'textarea' => array(
                'value' => 'textarea',
                'label' => new XenForo_Phrase('multi_line_text_box')
            ),
            'select' => array(
                'value' => 'select',
                'label' => new XenForo_Phrase('drop_down_selection')
            ),
            'radio' => array(
                'value' => 'radio',
                'label' => new XenForo_Phrase('radio_buttons')
            ),
            'checkbox' => array(
                'value' => 'checkbox',
                'label' => new XenForo_Phrase('check_boxes')
            ),
            'multiselect' => array(
                'value' => 'multiselect',
                'label' => new XenForo_Phrase('multiple_choice_drop_down_selection')
            )
        );
    }

    public function getTraderFieldTypeMap()
    {
        return array(
            'textbox' => 'text',
            'textarea' => 'text',
            'radio' => 'single',
            'select' => 'single',
            'checkbox' => 'multiple',
            'multiselect' => 'multiple'
        );
    }

    public function getTraderFieldTitlePhraseName($fieldId)
    {
        return 'classifieds_trader_field_' . $fieldId;
    }

    public function getTraderFieldDescriptionPhraseName($fieldId)
    {
        return 'classifieds_trader_field_' . $fieldId . '_desc';
    }

    public function getTraderFieldChoicePhraseName($fieldId, $choice)
    {
        return 'classifieds_trader_field_' . $fieldId . '_choice_' . $choice;
    }

    public function getTraderFieldMasterTitlePhraseValue($fieldId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getTraderFieldTitlePhraseName($fieldId)
        );
    }

    public function getTraderFieldMasterDescriptionPhraseValue($fieldId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getTraderFieldDescriptionPhraseName($fieldId)
        );
    }

    public function getTraderFieldValues()
    {

    }

    public function rebuildTraderFieldCache()
    {

    }

    /**
     * @return XenForo_Model_Phrase
     */
    protected function _getPhraseModel()
    {
        return $this->getModelFromCache('XenForo_Model_Phrase');
    }
}