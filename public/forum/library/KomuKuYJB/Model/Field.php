<?php /*1934b72aaab1e12d32c7c7318b7d20139b0d9ae0*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Model_Field extends KomuKuYJB_Model
{
    const FETCH_CATEGORY = 0x01;

    public function getFieldById($fieldId)
    {
        return $this->_getDb()->fetchRow(
            'SELECT *
            FROM kmk_classifieds_field
            WHERE field_id = ?', $fieldId
        );
    }

    public function getAllFields(array $fetchOptions = array())
    {
        return $this->getFields(array(), $fetchOptions);
    }

    public function getFields(array $conditions, array $fetchOptions = array())
    {
        $whereClause = $this->prepareFieldConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareFieldFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            "SELECT field.*
            {$joinOptions['selectFields']}
            FROM kmk_classifieds_field AS field
            {$joinOptions['joinTables']}
            WHERE {$whereClause}
            ORDER BY field.display_group, field.display_order", 'field_id'
        );
    }

    public function prepareFieldFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $db = $this->_getDb();

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_CATEGORY)
            {
                $selectFields .= ', category_assoc.category_id';
                $joinTables .= ' INNER JOIN kmk_classifieds_field_category AS category_assoc
                                    ON (category_assoc.field_id = field.field_id)';
            }
        }

        if (!empty($fetchOptions['categoryId']))
        {
            $joinTables .= ' INNER JOIN kmk_classifieds_field_category AS category_assoc
                                ON (
                                    category_assoc.field_id = field.field_id
                                    AND category_assoc.category_id = ' . $db->quote($fetchOptions['categoryId']) . '
                                )';
        }

        if (!empty($fetchOptions['valueClassifiedId']))
        {
            $selectFields .= ', field_value.field_value';
            $joinTables .= ' LEFT JOIN kmk_classifieds_field_value AS field_value
                                ON (
                                    field.field_id = field_value.field_id
                                    AND field_value.classified_id = ' . $db->quote($fetchOptions['valueClassifiedId']) . '
                                )';
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables
        );
    }

    public function prepareFieldConditions(array $conditions, array &$fetchOptions)
    {
        $sqlConditions = array();
        $db = $this->_getDb();

        if (!empty($conditions['category_ids']))
        {
            $conditions['category_id'] = $conditions['category_ids'];
        }

        if (isset($conditions['category_id']))
        {
            if (is_array($conditions['category_id']))
            {
                $sqlConditions[] = 'category_assoc.category_id IN (' . $db->quote($conditions['category_id']) . ')';
            }
            else
            {
                $sqlConditions[] = 'category_assoc.category_id = ' . $db->quote($conditions['category_id']);
            }

            $this->addFetchOptionJoin($fetchOptions, self::FETCH_CATEGORY);
        }

        return $this->getConditionsForClause($sqlConditions);
    }

    public function getFieldsInCategories($categoryIds)
    {
        if (!$categoryIds)
        {
            return array();
        }

        $db = $this->_getDb();

        return $db->fetchAll(
            'SELECT field.*, category_assoc.category_id
            FROM kmk_classifieds_field AS field
            INNER JOIN kmk_classifieds_field_category AS category_assoc
              ON (field.field_id = category_assoc.field_id)
            WHERE category_assoc.category_id IN (' . $db->quote($categoryIds) . ')
            ORDER BY field.display_order'
        );
    }

    public function getFieldsForEdit($categoryId, $classifiedId = 0)
    {
        $fetchOptions = array(
            'categoryId' => $categoryId,
            'valueClassifiedId' => $classifiedId
        );

        return $this->getFields(array(), $fetchOptions);
    }

    public function getFieldValues($classifiedId)
    {
        $fields = $this->_getDb()->fetchAll(
            'SELECT v.*, field.field_type
            FROM kmk_classifieds_field_value AS v
            INNER JOIN kmk_classifieds_field AS field ON (field.field_id = v.field_id)
            WHERE v.classified_id = ?', $classifiedId
        );

        $values = array();
        foreach ($fields AS $field)
        {
            if ($field['field_type'] == 'checkbox' || $field['field_type'] == 'multiselect')
            {
                $values[$field['field_id']] = XenForo_Helper_Php::safeUnserialize($field['field_value']);
            }
            else
            {
                $values[$field['field_id']] = $field['field_value'];
            }
        }

        return $values;
    }

    public function verifyFieldValue(array $field, &$value, &$error = '')
    {
        $error = false;

        switch ($field['field_type'])
        {
            case 'date':
                if (is_string($value))
                {
                    $value = XenForo_Input::rawFilter($value, XenForo_Input::DATE_TIME);
                }
                elseif (is_numeric($value))
                {
                    $value = intval($value);
                }
                else
                {
                    $value = 0;
                }
                break;

            case 'text_hint':
                $value = $field['hint_text'];
                break;

            case 'textbox':
                $value = preg_replace('/\r?\n/', ' ', strval($value));
            // break missing intentionally

            case 'textarea':
            case 'bbcode':
                $value = trim(strval($value));

                if ($field['field_type'] == 'bbcode')
                {
                    $value = XenForo_Helper_String::autoLinkBbCode($value);
                }

                if ($field['max_length'] && utf8_strlen($value) > $field['max_length'])
                {
                    $error = new XenForo_Phrase('please_enter_value_using_x_characters_or_fewer', array('count' => $field['max_length']));
                    return false;
                }

                $matched = true;

                if ($value !== '')
                {
                    switch ($field['match_type'])
                    {
                        case 'number':
                            $matched = preg_match('/^[0-9]+(\.[0-9]+)?$/', $value);
                            break;

                        case 'alphanumeric':
                            $matched = preg_match('/^[a-z0-9_]+$/i', $value);
                            break;

                        case 'email':
                            $matched = Zend_Validate::is($value, 'EmailAddress');
                            break;

                        case 'url':
                            if ($value === 'http://')
                            {
                                $value = '';
                                break;
                            }
                            if (substr(strtolower($value), 0, 4) == 'www.')
                            {
                                $value = 'http://' . $value;
                            }
                            $matched = Zend_Uri::check($value);
                            break;

                        case 'regex':
                            $matched = preg_match('#' . str_replace('#', '\#', $field['match_regex']) . '#sU', $value);
                            break;

                        case 'callback':
                            $matched = call_user_func_array(
                                array($field['match_callback_class'], $field['match_callback_method']),
                                array($field, &$value, &$error)
                            );

                        default:
                            // no matching
                    }
                }

                if (!$matched)
                {
                    if (!$error)
                    {
                        $error = new XenForo_Phrase('please_enter_value_that_matches_required_format');
                    }
                    return false;
                }
                break;

            case 'radio':
            case 'select':
                $choices = XenForo_Helper_Php::safeUnserialize($field['field_choices']);
                $value = strval($value);

                if (!isset($choices[$value]))
                {
                    $value = '';
                }
                break;

            case 'checkbox':
            case 'multiselect':
                $choices = XenForo_Helper_Php::safeUnserialize($field['field_choices']);
                if (!is_array($value))
                {
                    $value = array();
                }

                $newValue = array();

                foreach ($value AS $key => $choice)
                {
                    $choice = strval($choice);
                    if (isset($choices[$choice]))
                    {
                        $newValue[$choice] = $choice;
                    }
                }

                $value = $newValue;
                break;
        }

        return true;
    }

    public function prepareField(array &$field, $getFieldChoices = false, $fieldValue = null, $valueSaved = true)
    {
        $field['isMultiChoice'] = ($field['field_type'] == 'checkbox' || $field['field_type'] == 'multiselect');
        $field['isChoice'] = ($field['isMultiChoice'] || $field['field_type'] == 'radio' || $field['field_type'] == 'select');

        if ($fieldValue === null && isset($field['field_value']))
        {
            $fieldValue = $field['field_value'];
        }

        if ($field['isMultiChoice'])
        {
            if (is_string($fieldValue))
            {
                $fieldValue = XenForo_Helper_Php::safeUnserialize($fieldValue);
            }
            else if (!is_array($fieldValue))
            {
                $fieldValue = array();
            }
        }

        if ($field['field_type'] == 'date')
        {
            $fieldValue = floatval($fieldValue);
        }

        $field['field_value'] = $fieldValue;

        $field['title'] = new XenForo_Phrase($this->getFieldTitlePhraseName($field['field_id']));
        $field['description'] = new XenForo_Phrase($this->getFieldDescriptionPhraseName($field['field_id']));

        $field['hasValue'] = $valueSaved && ((is_string($fieldValue) && $fieldValue !== '') || (!is_string($fieldValue) && $fieldValue));

        if ($getFieldChoices)
        {
            $field['fieldChoices'] = $this->getFieldChoices($field['field_id'], $field['field_choices']);
        }

        return $field;
    }

    public function prepareFields(array &$fields, $getFieldChoices = false, array $fieldValues = array(), $valueSaved = true)
    {
        foreach ($fields as &$field)
        {
            $value = isset($fieldValues[$field['field_id']]) ? $fieldValues[$field['field_id']] : null;
            $this->prepareField($field, $getFieldChoices, $value, $valueSaved);
        }

        return $fields;
    }

    public function groupFields(array $fields)
    {
        $return = array();

        foreach ($fields AS $fieldId => $field)
        {
            $return[$field['display_group']][$fieldId] = $field;
        }

        return $return;
    }

    public function getFieldChoices($fieldId, $choices, $master = false)
    {
        if (!is_array($choices))
        {
            $choices = ($choices ? XenForo_Helper_Php::safeUnserialize($choices) : array());
        }

        if (!$master)
        {
            foreach ($choices AS $value => &$text)
            {
                $text = new XenForo_Phrase($this->getFieldChoicePhraseName($fieldId, $value));
            }
        }

        return $choices;
    }

    public function getFieldGroups()
    {
        return array(
            'above_title' => array(
                'value' => 'above_title',
                'label' => new XenForo_Phrase('above_classified_title')
            ),
            'below_title' => array(
                'value' => 'below_title',
                'label' => new XenForo_Phrase('below_classified_title')
            ),
            'above_info' => array(
                'value' => 'above_info',
                'label' => new XenForo_Phrase('above_item_description')
            ),
            'below_info' => array(
                'value' => 'below_info',
                'label' => new XenForo_Phrase('below_item_description')
            ),
            'location_tab' => array(
                'value' => 'location_tab',
                'label' => new XenForo_Phrase('location_tab'),
                'hint' => new XenForo_Phrase('this_field_will_only_be_shown_if_location_is_enabled')
            ),
            'extra_tab' => array(
                'value' => 'extra_tab',
                'label' => new XenForo_Phrase('extra_information_tab')
            ),
            'new_tab' => array(
                'value' => 'new_tab',
                'label' => new XenForo_Phrase('own_tab')
            )
        );
    }

    public function getFieldTypes()
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
            'bbcode' => array(
                'value' => 'bbcode',
                'label' => new XenForo_Phrase('rich_text_box'),
            ),
            'date' => array(
                'value' => 'date',
                'label' => new XenForo_Phrase('date_picker')
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
            ),
            'hint_text' => array(
                'value' => 'hint_text',
                'label' => new XenForo_Phrase('hint_text'),
                'hint' => new XenForo_Phrase('will_only_be_shown_on_selected_pages_as_hint_texts')
            )
        );
    }

    public function getFieldTypeMap()
    {
        return array(
            'date' => 'none',
            'hint_text' => 'none',
            'textbox' => 'text',
            'textarea' => 'text',
            'bbcode' => 'text',
            'radio' => 'single',
            'select' => 'single',
            'checkbox' => 'multiple',
            'multiselect' => 'multiple'
        );
    }

    public function getFieldTitlePhraseName($fieldId)
    {
        return 'classifieds_field_' . $this->_getContentId($fieldId, 'field_id');
    }

    public function getFieldTitleMasterPhraseValue($fieldId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getFieldTitlePhraseName($fieldId)
        );
    }

    public function getFieldDescriptionPhraseName($fieldId)
    {
        return 'classifieds_field_description_' . $this->_getContentId($fieldId, 'field_id');
    }

    public function getFieldDescriptionMasterPhraseValue($fieldId)
    {
        return $this->_getPhraseModel()->getMasterPhraseValue(
            $this->getFieldDescriptionPhraseName($this->_getContentId($fieldId, 'field_id'))
        );
    }

    public function getFieldChoicePhraseName($fieldId, $choice)
    {
        return 'classifieds_field_' . $this->_getContentId($fieldId, 'field_id') . '_choice_' . $choice;
    }

    public function rebuildFieldCache()
    {
        $cache = array();

        foreach ($this->getFields(array()) as $fieldId => $field)
        {
            $cache[$fieldId] = KomuKuYJB_Application::arrayFilterKeys($field, array(
                'field_id',
                'field_type',
                'display_group',
                'hint_text',
                'include_in_*'
            ));

            foreach (array('display_template') AS $optionalField)
            {
                if (!empty($field[$optionalField]))
                {
                    $cache[$fieldId][$optionalField] = $field[$optionalField];
                }
            }
        }

        GFNCore_Registry::set('classifiedFields', $cache);
        return $cache;
    }

    public function getFieldCache()
    {
        $return = GFNCore_Registry::get('classifiedFields');
        if (is_array($return))
        {
            return $return;
        }

        return $this->rebuildFieldCache();
    }
}