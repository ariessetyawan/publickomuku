<?php

class AnonymousPosting_Route_PrefixAdmin_AnonymousPosting implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'log_id');
        return $router->getRouteMatch('AnonymousPosting_ControllerAdmin_Log', $action, 'logs');
    }

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix,
            $action, $extension, $data, 'post_id', 'anonymous_posting_real_username');
    }
}
