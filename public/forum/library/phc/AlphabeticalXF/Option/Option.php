<?php

// Team NullXF
class phc_AlphabeticalXF_Option_Option
{
    public static function renderMultipleForums(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $editLink = $view->createTemplateObject('option_list_option_editlink', array(
            'preparedOption' => $preparedOption,
            'canEditOptionDefinition' => $canEdit
        ));

        if(isset($preparedOption['option_value'][0]) && $preparedOption['option_value'][0] == 0)
        {
            unset($preparedOption['option_value']);

            $preparedOption['option_value'][0] = 0;
        }

        $nodeModel = XenForo_Model::create('XenForo_Model_Node');
        $forumOptions = $nodeModel->getNodeOptionsArray($nodeModel->getAllNodes(), $preparedOption['option_value'], 'All forums');

        return $view->createTemplateObject('option_list_option_multi_alphaxf', array(
            'fieldPrefix' => $fieldPrefix,
            'listedFieldName' => $fieldPrefix . '_listed[]',
            'preparedOption' => $preparedOption,
            'formatParams' => $forumOptions,
            'editLink' => $editLink
        ));
    }

    public static function renderOption_Languages(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        $value = $preparedOption['option_value'];

        $choices = array();
        foreach($value AS $alpha)
        {
            $choices[] = array(
                'abc' => $alpha['abc'],
                'all' => $alpha['all'],
                'language' => $alpha['language'],
                'lang_text' => $alpha['lang_text'],
            );
        }

        $editLink = $view->createTemplateObject('option_list_option_editlink', array(
            'preparedOption' => $preparedOption,
            'canEditOptionDefinition' => $canEdit
        ));

        return $view->createTemplateObject('option_template_languages_alphaxf', array(
            'fieldPrefix' => $fieldPrefix,
            'listedFieldName' => $fieldPrefix . '_listed[]',
            'preparedOption' => $preparedOption,
            'formatParams' => $preparedOption['formatParams'],
            'editLink' => $editLink,

            'choices' => $choices,
            'nextCounter' => count($choices),

            'languages' => XenForo_Model::create('XenForo_Model_Language')->getLanguagesForOptionsTag(),
        ));
    }

    public static function verifyOption_Languages(array &$alphas, XenForo_DataWriter $dw, $fieldName)
    {
        $output = array();

        foreach($alphas AS $alpha)
        {
            if(!isset($alpha['abc']) || !$alpha['abc'])
            {
                continue;
            }
            
            if(!$alpha['lang_text'])
            {
                $dw->error(new XenForo_Phrase('alphaxf_you_must_set_a_language_name'), $fieldName);
            }

            $output[] = array(
                'abc' => trim(preg_replace('#[\\x00-\\x20]+#', '', $alpha['abc'])),
                'all' => $alpha['all'],
                'language' => $alpha['language'],
                'lang_text' => $alpha['lang_text'],
            );
        }

        $alphas = $output;
        return true;
    }
}