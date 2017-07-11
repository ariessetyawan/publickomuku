<?php

class KomuKuJVC_Route_Prefix_Threads extends XFCP_KomuKuJVC_Route_Prefix_Threads
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerParam($routePath, $request, 'thread_id');
		$action = $router->resolveActionAsPageNumber($action, $request);
		return $router->getRouteMatch('XenForo_ControllerPublic_Thread', $action, 'forums');
	}

	/**
	 * Method overriden to redirect "Directory Forum Threads" to the "Review Page"
	 * Call parent when node_id is not a Directory Forum
	 */
	 
	 // KomuKuJVC override this for directory forum links
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		// check $data[node_id]
		$directoryForums = XenForo_Application::get('options')->directoryForum;
		
		
		if (empty($data['node_id']) || ($data['node_id'] != $directoryForums[0]))
		{
			return parent::buildLink(
				$originalPrefix, 
				$outputPrefix, 
				$action, 
				$extension, 
				$data, 
				$extraParams
			);
		}
		
		$postHash = '';
		if ($action == 'post-permalink' && !empty($extraParams['post']))
		{
			$post = $extraParams['post'];
			unset($extraParams['post']);

			if (!empty($post['post_id']) && isset($post['position']))
			{
				if ($post['position'] > 0)
				{
					$postHash = '#post-' . intval($post['post_id']);
					$extraParams['page'] = floor($post['position'] / XenForo_Application::get('options')->messagesPerPage) + 1;
				}
			}

			$action = '';
		}

		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);

		return XenForo_Link::buildBasicLinkWithIntegerParam('reviews', $action, $extension, $data, 'thread_id', 'title') . $postHash;
	}
}