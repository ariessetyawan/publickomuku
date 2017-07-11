<?php /*473602856a53541a2bb02488ef9d02cfa69b14e8*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Listener_Template
{
    protected static $_hasPermission = null;

    public static function create(&$templateName, array &$params, XenForo_Template_Abstract $template)
    {
        if (self::$_hasPermission === null)
        {
            self::$_hasPermission = XenForo_Visitor::getInstance()->hasPermission('classifieds', 'view');
        }

        if (!isset($params['canViewClassifieds']))
        {
            $params['canViewClassifieds'] = self::$_hasPermission;
        }

        if (!isset($params['classifiedAdvertTypes']))
        {
            $params['classifiedAdvertTypes'] = GFNCore_Registry::get('classifiedAdvertTypes');
        }

        if (!isset($params['classifiedTotals']))
        {
            $params['classifiedTotals'] = GFNCore_Registry::get('classifiedTotals');
        }

        if ($template instanceof XenForo_Template_Admin && $templateName == 'PAGE_CONTAINER')
        {
            if (strpos($params['majorSection'], 'classified') === 0)
            {
                $breadcrumbs = &$params['adminNavigation']['breadCrumb'];

                $breadcrumbs = array(
                    key($breadcrumbs) => current($breadcrumbs),
                    'classifieds' => array(
                        'link' => null,
                        'title' => new XenForo_Phrase('classifieds')
                    )
                ) + array_slice($breadcrumbs, 1);
            }
        }
    }
}