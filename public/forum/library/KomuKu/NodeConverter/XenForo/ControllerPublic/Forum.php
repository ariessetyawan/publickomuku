<?php

class KomuKu_NodeConverter_XenForo_ControllerPublic_Forum extends XFCP_KomuKu_NodeConverter_XenForo_ControllerPublic_Forum
{
	public function actionForum()
	{
		if ($this->_routeMatch->getResponseType() == 'rss')
		{
			return parent::actionForum();
		}

		try
		{
			return parent::actionForum();
		}
		catch (XenForo_ControllerResponse_Exception $e)
		{
			if ($e->getControllerResponse()->responseCode == 404)
			{
				$visitor = XenForo_Visitor::getInstance();
				$fetchOptions = array('permissionCombinationId' => $visitor['permission_combination_id']);

				$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
				$forum = $this->_getNodeModel()->getNodeById($forumId, $fetchOptions);

				if (isset($forum['node_type_id']) && $forum['node_type_id'] == 'Category')
				{
					return $this->responseRedirect(
						XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
						XenForo_Link::buildPublicLink('categories', $forum)
					);
				}
			}

			return $e->getControllerResponse();
		}
	}
}