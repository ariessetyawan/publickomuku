<?php

class KomuKuJVC_Route_Prefix_Directory implements XenForo_Route_Interface
{
	
	
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'node_id');
		$action = $router->resolveActionAsPageNumber($action, $request);
		return $router->getRouteMatch('KomuKuJVC_ControllerPublic_Directory', $action, 'directory');
	}
	

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'node_id', 'title');
	}
	
	
		
}