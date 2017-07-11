<?php /*aeb2b7ecae1c0727af8e9c67385d9a6ee7b68594*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_DataWriter_Field extends XenForo_DataWriter
{
    const DATA_TITLE = 'phraseTitle';
    const DATA_DESCRIPTION = 'phraseDescription';
    const DATA_CATEGORIES = 'categories';

    protected $_fieldChoices = null;

    protected function _getFields()
    {
        return array(
            'kmk_classifieds_field' => array(
                'field_id' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true,
                    'maxLength' => 25,
                    'verification' => array('$this', '_validateFieldId')
                ),
                'display_group' => array(
                    'type' => self::TYPE_STRING,
                    'default' => 'above_info',
                    'allowedValues' => array('above_title', 'above_info', 'below_info', 'extra_tab', 'new_tab', 'below_title', 'location_tab')
                ),
                'display_order' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 1
                ),
                'field_type' => array(
                    'type' => self::TYPE_STRING,
                    'default' => 'textbox',
                    'allowedValues' => array('date', 'textbox', 'textarea', 'bbcode', 'select', 'radio', 'checkbox', 'multiselect', 'hint_text')
                ),
                'field_choices' => array(
                    'type' => self::TYPE_SERIALIZED,
                    'default' => 'a:0:{}'
                ),
                'can_be_filtered' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'include_in_classified_list' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'include_in_thread_list' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'include_in_classified_editor' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 1
                ),
                'include_in_classified_view' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 1
                ),
                'include_in_thread_view' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'match_type' => array(
                    'type' => self::TYPE_STRING,
                    'default' => 'none',
                    'allowedValues' => array('none', 'number', 'alphanumeric', 'email', 'url', 'regex', 'callback')
                ),
                'match_regex' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '',
                    'maxLength' => 250
                ),
                'match_callback_class' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '',
                    'maxLength' => 75
                ),
                'match_callback_method' => array(
                    'type' => self::TYPE_STRING,
                    'default' => '',
                    'maxLength' => 75
                ),
                'max_length' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'required' => array(
                    'type' => self::TYPE_BOOLEAN,
                    'default' => 0
                ),
                'display_template' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'hint_text' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                )
            )
        );
    }

    protected function _getExistingData($data)
    {
        $fieldId = $this->_getExistingPrimaryKey($data, 'field_id');
        $field = $this->_getFieldModel()->getFieldById($fieldId);

        if (!$field)
        {
            return false;
        }

        return array('kmk_classifieds_field' => $field);
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'field_id = ' . $this->_db->quote($this->getExisting('field_id'));
    }

    protected function _preSave()
    {
        if (!$this->get('include_in_classified_editor'))
        {
            $this->set('required', false);
        }

        if ($this->isChanged('match_callback_class') || $this->isChanged('match_callback_method'))
        {
            $class = $this->get('match_callback_class');
            $method = $this->get('match_callback_method');

            if (!$class || !$method)
            {
                $this->set('match_callback_class', '');
                $this->set('match_callback_method', '');
            }
            else if (!XenForo_Application::autoload($class) || !method_exists($class, $method))
            {
                $this->error(new XenForo_Phrase('please_enter_valid_callback_method_x_y', array('class' => $class, 'method' => $method)), 'callback_method');
            }
        }

        if ($this->isUpdate() && $this->isChanged('field_type'))
        {
            $typeMap = $this->_getFieldModel()->getFieldTypeMap();
            if ($typeMap[$this->get('field_type')] != $typeMap[$this->getExisting('field_type')])
            {
                $this->error(new XenForo_Phrase('you_may_not_change_field_to_different_type_after_it_has_been_created'), 'field_type');
            }
        }

        if (in_array($this->get('field_type'), array('select', 'radio', 'checkbox', 'multiselect')))
        {
            if (($this->isInsert() && !$this->_fieldChoices) || (is_array($this->_fieldChoices) && !$this->_fieldChoices))
            {
                $this->error(new XenForo_Phrase('please_enter_at_least_one_choice'), 'field_choices', false);
            }

            $this->set('hint_text', '');
        }
        elseif ($this->get('field_type') == 'hint_text')
        {
            if (!$this->get('hint_text'))
            {
                $this->error(new XenForo_Phrase('please_enter_valid_hint_text'), 'hint_text');
            }

            $this->set('required', false);
        }
        else
        {
            $this->set('can_be_filtered', false);
            $this->setFieldChoices(array());
            $this->set('hint_text', '');
        }

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null && strlen($titlePhrase) == 0)
        {
            $this->error(new XenForo_Phrase('please_enter_valid_title'), 'title');
        }

        $descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
        if ($descriptionPhrase !== null && strlen($descriptionPhrase) == 0)
        {
            $this->setExtraData(self::DATA_DESCRIPTION, false);
        }
    }

    protected function _postSave()
    {
        $fieldId = $this->get('field_id');

        if ($this->isUpdate() && $this->isChanged('field_id'))
        {
            $this->_renameMasterPhrase(
                $this->_getTitlePhraseName($this->getExisting('field_id')),
                $this->_getTitlePhraseName($fieldId)
            );

            $this->_renameMasterPhrase(
                $this->_getDescriptionPhraseName($this->getExisting('field_id')),
                $this->_getDescriptionPhraseName($fieldId)
            );
        }

        $titlePhrase = $this->getExtraData(self::DATA_TITLE);
        if ($titlePhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getTitlePhraseName($fieldId), $titlePhrase,
                '', array('global_cache' => 1)
            );
        }

        $descriptionPhrase = $this->getExtraData(self::DATA_DESCRIPTION);
        if ($descriptionPhrase !== null)
        {
            $this->_insertOrUpdateMasterPhrase(
                $this->_getDescriptionPhraseName($fieldId), $descriptionPhrase
            );
        }

        if (is_array($this->_fieldChoices))
        {
            $this->_deleteExistingChoicePhrases();

            foreach ($this->_fieldChoices AS $choice => $text)
            {
                $this->_insertOrUpdateMasterPhrase(
                    $this->_getChoicePhraseName($fieldId, $choice), $text,
                    '', array('global_cache' => 1)
                );
            }
        }

        $categoryIds = $this->getExtraData(self::DATA_CATEGORIES);
        if (is_array($categoryIds))
        {
            $this->_getAssociationModel()->field()->updateAssociationByField($fieldId, $categoryIds);
        }

        $this->_getFieldModel()->rebuildFieldCache();
    }

    protected function _postDelete()
    {
        $fieldId = $this->get('field_id');

        $this->_deleteMasterPhrase($this->_getTitlePhraseName($fieldId));
        $this->_deleteMasterPhrase($this->_getDescriptionPhraseName($fieldId));
        $this->_deleteExistingChoicePhrases();

        $this->_getAssociationModel()->field()->removeAssociationByField($fieldId);
        $this->_getFieldModel()->rebuildFieldCache();
    }

    public function setFieldChoices(array $choices)
    {
        foreach ($choices AS $value => &$text)
        {
            if ($value === '')
            {
                unset($choices[$value]);
                continue;
            }

            $text = strval($text);

            if ($text === '')
            {
                $this->error(new XenForo_Phrase('please_enter_text_for_each_choice'), 'field_choices');
                return false;
            }

            if (preg_match('#[^a-z0-9_]#i', $value))
            {
                $this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'field_choices');
                return false;
            }

            if (strlen($value) > 25)
            {
                $this->error(new XenForo_Phrase('please_enter_value_using_x_characters_or_fewer', array('count' => 25)));
                return false;
            }
        }

        $this->_fieldChoices = $choices;
        $this->set('field_choices', $choices);

        return true;
    }

    /**
     * @return KomuKuYJB_Model_Field
     */
    protected function _getFieldModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_Field');
    }

    /**
     * @return KomuKuYJB_Model_CategoryAssociation
     */
    protected function _getAssociationModel()
    {
        return $this->getModelFromCache('KomuKuYJB_Model_CategoryAssociation');
    }

    protected function _validateFieldId(&$data)
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $data))
        {
            $this->error(new XenForo_Phrase('please_enter_an_id_using_only_alphanumeric'), 'field_id');
            return false;
        }

        if ($data == 'extra')
        {
            $this->error(new XenForo_Phrase('the_field_id_extra_is_system_reserved_and_cannot_be_used'), 'field_id');
            return false;
        }

        if ($data !== $this->getExisting('field_id') && $this->_getFieldModel()->getFieldById($data))
        {
            $this->error(new XenForo_Phrase('field_ids_must_be_unique'), 'field_id');
            return false;
        }

        return true;
    }

    protected function _getTitlePhraseName($fieldId)
    {
        return $this->_getFieldModel()->getFieldTitlePhraseName($fieldId);
    }

    protected function _getDescriptionPhraseName($fieldId)
    {
        return $this->_getFieldModel()->getFieldDescriptionPhraseName($fieldId);
    }

    protected function _getChoicePhraseName($fieldId, $choice)
    {
        return $this->_getFieldModel()->getFieldChoicePhraseName($fieldId, $choice);
    }

    protected function _deleteExistingChoicePhrases()
    {
        $fieldId = $this->get('field_id');

        $existingChoices = $this->getExisting('field_choices');
        if ($existingChoices && $existingChoices = XenForo_Helper_Php::safeUnserialize($existingChoices))
        {
            foreach ($existingChoices AS $choice => $text)
            {
                $this->_deleteMasterPhrase($this->_getChoicePhraseName($fieldId, $choice));
            }
        }
    }
} 