<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_Route_PrefixAdmin_ForumExtras implements XenForo_Route_Interface
{
	//Match a specific route with a prefix for the Extra Forum View Settings link in the acp
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'id');
		return $router->getRouteMatch('KomuKu_ForumExtras_ControllerAdmin_ForumExtras', $action, 'forumextras');
	}

	//Build a link for the specified page
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'id');
	}
}