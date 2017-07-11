<?php /*d75a39b990483c1a9d994fd9927a0106a971e927*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_Route_Prefix_Classifieds_Contact implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionAsPageNumber($routePath, $request);
        return $router->getRouteMatch('GFNClassifieds_ControllerPublic_Classified', 'contact-' . $action);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $action = XenForo_Link::getPageNumberAsAction($action, $extraParams);
        return XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
    }
}