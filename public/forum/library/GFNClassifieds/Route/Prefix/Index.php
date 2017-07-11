<?php /*4404a1ca7dd7108a5e36a491ceb331f0fd0dad6b*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Route_Prefix_Index implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'classified_id');
        $action = $router->resolveActionAsPageNumber($action, $request);

        return $router->getRouteMatch('GFNClassifieds_ControllerPublic_Home', $action);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        list ($outputPrefix) = explode('/', $outputPrefix, 2);
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'classified_id', 'title');
    }
}