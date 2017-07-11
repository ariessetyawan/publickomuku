<?php /*615d11b590dc400fed805b7bd4725e17e0c869f9*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 RC 3
 * @since      1.0.0 RC 3
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Route_Prefix_Traders extends GFNCore_Route_PrefixBackbone
{
    protected function _getRouteClasses()
    {
        return array(
            'default' => function($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
            {
                $action = $router->resolveActionAsPageNumber($routePath, $request);
                return $router->getRouteMatch('KomuKuYJB_ControllerPublic_Trader', $action);
            },
            'ratings' => 'KomuKuYJB_Route_Prefix_Traders_Ratings',
            'rate' => 'KomuKuYJB_Route_Prefix_Traders_Rate'
        );
    }

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'user_id', 'view');
        return parent::match($action, $request, $router);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        $newPrefix = XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, '', null, $data, 'user_id', 'username');
        if ($newPrefix)
        {
            $outputPrefix = $newPrefix;
        }

        $return = parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
        if (!$return)
        {
            $return = XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
        }

        return $return;
    }
}