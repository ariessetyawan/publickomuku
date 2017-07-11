<?php /*a2281ebaf960930ed0551110db6e6dbfd6b8131a*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Route_PrefixAdmin_Categories_Permissions_Users implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'user_id');
        return $router->getRouteMatch('GFNClassifieds_ControllerAdmin_CategoryPermission_User', $action);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (!empty($extraParams['user_id']))
        {
            $data['user_id'] = $extraParams['user_id'];
            unset ($extraParams['user_id']);
        }

        if (!empty($extraParams['username']))
        {
            $data['username'] = $extraParams['username'];
            unset ($extraParams['username']);
        }

        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'user_id', 'username');
    }
}