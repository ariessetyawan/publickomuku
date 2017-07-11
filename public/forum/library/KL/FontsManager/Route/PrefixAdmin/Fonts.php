<?php

/**
 * KL_FontsManager_Route_PrefixAdmin_Fonts
 *
 * @author: Nerian
 * @last_edit:    01.09.2015
 */
class KL_FontsManager_Route_PrefixAdmin_Fonts implements XenForo_Route_Interface
{
    /**
     * @param $routePath
     * @param Zend_Controller_Request_Http $request
     * @param XenForo_Router $router
     * @return XenForo_RouteMatch
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'id');

        return $router->getRouteMatch('KL_FontsManager_ControllerAdmin_Fonts', $action, 'kl_fm');
    }
}