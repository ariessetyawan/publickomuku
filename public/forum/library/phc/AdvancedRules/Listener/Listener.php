<?php
// Team NullXF

class phc_AdvancedRules_Listener_Listener
{
    public static function load_class_controller($class, array &$extend)
    {
        switch($class)
        {
            case 'XenForo_ControllerPublic_Forum':
                $extend[] = 'phc_AdvancedRules_Extend_XenForo_ControllerPublic_Forum';
                break;

            case 'XenForo_ControllerPublic_Thread':
                $extend[] = 'phc_AdvancedRules_Extend_XenForo_ControllerPublic_Thread';
                break;

             case 'XenForo_ControllerPublic_Conversation':
                $extend[] = 'phc_AdvancedRules_Extend_XenForo_ControllerPublic_Conversation';
                break;

            case 'XenGallery_ControllerPublic_Album':
                $extend[] = 'phc_AdvancedRules_Extend_XenGallery_ControllerPublic_Album';
                break;

            case 'XenGallery_ControllerPublic_Category':
                $extend[] = 'phc_AdvancedRules_Extend_XenGallery_ControllerPublic_Category';
                break;

            case 'XenGallery_ControllerPublic_Media':
                $extend[] = 'phc_AdvancedRules_Extend_XenGallery_ControllerPublic_Media';
                break;

            case 'XenResource_ControllerPublic_Resource':
                $extend[] = 'phc_AdvancedRules_Extend_XenResource_ControllerPublic_Resource';
                break;

            case 'XenForo_ControllerAdmin_Option':
                $extend[] = 'phc_AdvancedRules_Extend_XenForo_ControllerAdmin_Option';
                break;
        }
    }

    public static function template_hook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
    {
        if(
            ($hookName == 'navigation_tabs_forums' || $hookName == 'footer_links') &&
            (XenForo_Application::get('options')->ar_footer || $hookName == 'navigation_tabs_forums'))
        {
            $link = XenForo_Link::buildPublicLink('rule');

            $hookParams = array(
                'link' => $link,
            );

            $mergedParams = array_merge($template->getParams(), $hookParams);

            $arTplLink = $template->create('rule_link', $mergedParams);
        }

        if($hookName == 'navigation_tabs_forums' && XenForo_Application::get('options')->ar_navbar)
        {
            $contents .= $arTplLink;
        }

        if($hookName == 'footer_links' && XenForo_Application::get('options')->ar_footer)
        {
            $contents .= $arTplLink;
        }
    }

/*    public static function navigation_tabs(array &$extraTabs, $selectedTabId)
    {

            $extraTabs['phc_ar'] = array(
                'title' => ,
                'href' => XenForo_Link::buildPublicLink('full:rule'),
                'selected' => ($selectedTabId == 'phc_ar'),
            );
        }
    }*/
}
