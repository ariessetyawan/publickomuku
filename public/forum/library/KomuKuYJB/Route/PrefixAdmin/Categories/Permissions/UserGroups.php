<?php /*9c19f14f19bb9463c00888cf133ab0177222d93b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Route_PrefixAdmin_Categories_Permissions_UserGroups implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'user_group_id');
        return $router->getRouteMatch('KomuKuYJB_ControllerAdmin_CategoryPermission_UserGroup', $action);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (!empty($extraParams['user_group_id']))
        {
            $data['user_group_id'] = $extraParams['user_group_id'];
            unset ($extraParams['user_group_id']);
        }

        if (!empty($extraParams['user_group_title']))
        {
            $data['user_group_title'] = $extraParams['user_group_title'];
            unset ($extraParams['user_group_title']);
        }

        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'user_group_id', 'user_group_title');
    }
}