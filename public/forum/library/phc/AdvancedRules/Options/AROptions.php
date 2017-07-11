<?php

abstract class phc_AdvancedRules_Options_AROptions
{
    public static function renderSelect(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        return self::_render('option_list_option_select', $view, $fieldPrefix, $preparedOption, $canEdit);
    }

    public static function getUserGroupOptions($selected)
    {
        $ARModel = XenForo_Model::create('phc_AdvancedRules_Model_ARModel');

        $rules = $ARModel->fetchAllAR();

        $options[] = array
        (
            'label' => new XenForo_Phrase('ar_standard_rule'),
            'value' => 0,
            'selected' => ($selected == 0 ? 1 : 0)
        );

        if(is_array($rules) && !empty($rules))
        {
            foreach($rules as $rule)
            {
                $options[] = array
                (
                    'label' => $rule['title'],
                    'value' => $rule['ar_id'],
                    'selected' => ($selected == $rule['ar_id'] ? 1 : 0)
                );
            }
        }

        return $options;
    }

    protected static function _render($templateName, XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $preparedOption['formatParams'] = self::getUserGroupOptions($preparedOption['option_value']);

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            $templateName, $view, $fieldPrefix, $preparedOption, $canEdit
        );
    }
}