<?php
/*=======================================================================*\
|| ##################################################################### ||
|| # ----------------------------------------------------------------- # ||
|| # Copyright © 2014 Jim Dudek AKA Nhawk/KomuKu                         # ||
|| # All Rights Reserved.                                              # ||
|| # This file may not be redistributed in whole or significant part.  # ||
|| ##################################################################### ||
\*=======================================================================*/

class KomuKu_OrderNodes_Route_PrefixAdmin_Ordernodes implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'node_id');
	
		return $router->getRouteMatch('KomuKu_OrderNodes_ControllerAdmin_Ordernodes', $action, 'applications');
	}
	
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'node_id');
	}
}