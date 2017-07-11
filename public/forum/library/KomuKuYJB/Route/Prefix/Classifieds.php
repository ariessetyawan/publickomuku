<?php /*621d726de8563b79fb932fe753926e622ca8c5eb*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 2
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_Route_Prefix_Classifieds extends GFNCore_Route_PrefixBackbone
{
    protected function _getRouteClasses()
    {
        return array(
            'default' => function($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
            {
                return $router->getRouteMatch('KomuKuYJB_ControllerPublic_Classified', $routePath);
            },
            'fields' => 'KomuKuYJB_Route_Prefix_Classifieds_Fields',
            'contact' => 'KomuKuYJB_Route_Prefix_Classifieds_Contact',
            'comments' => 'KomuKuYJB_Route_Prefix_Classifieds_Comments'
        );
    }

    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'classified_id', 'view');
        return parent::match($action, $request, $router);
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        if (!isset($data['classified_title']) && isset($data['title']))
        {
            $data['classified_title'] = $data['title'];
        }

        $outputPrefix = XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, '', null, $data, 'classified_id', 'classified_title') ?: $outputPrefix;
        $return = parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);

        if (!$return)
        {
            $return = XenForo_Link::buildBasicLink($outputPrefix, $action, $extension);
        }

        return $return;
    }
}