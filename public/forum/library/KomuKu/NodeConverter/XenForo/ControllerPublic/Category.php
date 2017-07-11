<?php

class KomuKu_NodeConverter_XenForo_ControllerPublic_Category extends XFCP_KomuKu_NodeConverter_XenForo_ControllerPublic_Category
{
	public function actionIndex()
	{
		if ($this->_routeMatch->getResponseType() == 'rss')
		{
			return parent::actionIndex();
		}

		try
		{
			return parent::actionIndex();
		}
		catch (XenForo_ControllerResponse_Exception $e)
		{
			if ($e->getControllerResponse()->responseCode == 404)
			{
				$visitor = XenForo_Visitor::getInstance();
				$fetchOptions = array('permissionCombinationId' => $visitor['permission_combination_id']);

				$categoryId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
				$category = $this->_getNodeModel()->getNodeById($categoryId, $fetchOptions);

				if (isset($category['node_type_id']) && $category['node_type_id'] == 'Forum')
				{
					return $this->responseRedirect(
						XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL_PERMANENT,
						XenForo_Link::buildPublicLink('forums', $category)
					);
				}
			}

			return $e->getControllerResponse();
		}
	}
}