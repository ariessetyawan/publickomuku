<?php

class KomuKu_MyThreads_Route_Prefix_MyThreads implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('KomuKu_MyThreads_ControllerPublic_MyThreads', $routePath);
	}
}