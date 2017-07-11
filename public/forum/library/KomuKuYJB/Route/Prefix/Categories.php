<?php /*14430f7c781ad32d3385e2ef21c7b395b0808d51*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Route_Prefix_Categories implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'category_id', 'view');
        return $router->getRouteMatch('KomuKuYJB_ControllerPublic_Category', $action);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (!isset($data['category_title']) && isset($data['title']))
        {
            $data['category_title'] = $data['title'];
        }

        if ($action == 'view')
        {
            $action = '';
        }

        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'category_id', 'category_title');
    }
}