<?php

// Team NullXF

class phc_AdvancedRules_Route_PrefixAdmin_ARRoute implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'ar_id');

        return $router->getRouteMatch('phc_AdvancedRules_ControllerAdmin_ARAdmin', $action, 'phc_ar');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'ar_id');
    }
}