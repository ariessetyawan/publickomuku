<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Listener_Template
{
    /**
     * @var null|XenForo_Template_Public
     */
    protected static $_copyrightTemplate = null;

    public static function createPublicPageContainer(&$templateName, array &$params, XenForo_Template_Abstract $template)
    {

    }

    /**
     * @deprecated
     */
    public static function hookPageContainerBreadcrumbBottom() { }

    public static function createAdminOptionList(&$templateName, array &$params, XenForo_Template_Abstract $template)
    {
        if (!($template instanceof XenForo_Template_Admin))
        {
            return;
        }

        $group = $params['group']['group_id'];

        if (!in_array($group, GFNCore_Application::getInstance()->getTabbedOptionGroupList()))
        {
            return;
        }

        $renderedOptions = array();

        foreach ($params['renderedOptions'] as $v)
        {
            foreach ($v as $i => $j)
            {
                $renderedOptions[$i] = $j;
            }
        }

        $tabs = array();
        $outputOptions = array();

        foreach ($params['preparedOptions'] as $k => $option)
        {
            $i = intval(floor($option['display_order'] / 1000));
            $j = intval(floor($option['display_order'] / 100));

            $tabs[$i] = new XenForo_Phrase($group . '_option_list_tab_' . $i);
            $outputOptions[$i][$j][$k] = $renderedOptions[$k];
        }

        $params['renderedOptions'] = $outputOptions;
        $params['tabTitles'] = $tabs;
        $templateName = 'gfncore_option_list';
    }
}