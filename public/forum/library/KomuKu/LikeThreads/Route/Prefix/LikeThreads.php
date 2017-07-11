<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_Route_Prefix_LikeThreads implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{	
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'like_id');
		return $router->getRouteMatch('KomuKu_LikeThreads_ControllerPublic_LikeThreads', $action, 'popular');
	}
	
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'like_id');
	}
}