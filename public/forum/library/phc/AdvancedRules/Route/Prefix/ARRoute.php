<?php

// Team NullXF

class phc_AdvancedRules_Route_Prefix_ARRoute implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, '');

        return $router->getRouteMatch('phc_AdvancedRules_ControllerPublic_AR', $action, 'phc_ar');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, '');
    }
}