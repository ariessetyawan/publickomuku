<?php /*8213a4d09fb4d865c51d2e8e5096678eea6aeaa7*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Route_PrefixAdmin_Categories extends GFNCore_Route_PrefixBackbone
{
    protected $_major = 'classifiedCategories';

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'category_id');
        return parent::match($action, $request, $router);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (isset($data['category_id']))
        {
            $outputPrefix = XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, '', null, $data, 'category_id', 'title');
        }

        $return = parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);

        if (!$return)
        {
            $return = XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
        }

        return $return;
    }

    protected function _getRouteClasses()
    {
        return array(
            'default' => 'controller:KomuKuYJB_ControllerAdmin_Category',
            'permissions' => array(
                'default' => 'controller:KomuKuYJB_ControllerAdmin_CategoryPermission_Home',
                'users' => 'KomuKuYJB_Route_PrefixAdmin_Categories_Permissions_Users',
                'user-groups' => 'KomuKuYJB_Route_PrefixAdmin_Categories_Permissions_UserGroups'
            )
        );
    }
} 