<?php /*9f03402bd0048457ac7302dc4d8b4fef71d5c263*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Beta 7
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Listener_NavigationTabs
{
    public static function classifieds(array &$extraTabs)
    {
        if (!XenForo_Visitor::getInstance()->hasPermission('classifieds', 'view'))
        {
            return;
        }

        $extraTabs += array(
            'classifieds' => array(
                'title' => new XenForo_Phrase('classifieds'),
                'href' => XenForo_Link::buildPublicLink('full:classifieds'),
                'position' => KomuKuYJB_Options::getInstance()->get('navTabLocation'),
                'linksTemplate' => 'classifieds_tab_links',
                'canAddClassified' => XenForo_Model::create('KomuKuYJB_Model_Category')->canAddClassified()
            )
        );
    }
}