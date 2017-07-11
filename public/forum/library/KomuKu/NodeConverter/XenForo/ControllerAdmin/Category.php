<?php

class KomuKu_NodeConverter_XenForo_ControllerAdmin_Category extends XFCP_KomuKu_NodeConverter_XenForo_ControllerAdmin_Category
{
	public function actionConvert()
	{
		$nodeModel = $this->_getNodeModel();

		$category = $nodeModel->getNodeById($this->_input->filterSingle('node_id', XenForo_Input::UINT));
		if (!$category)
		{
			return $this->responseError(new XenForo_Phrase('requested_category_not_found'), 404);
		}

		if (empty($category['canConvert']))
		{
			return $this->responseError(new XenForo_Phrase('TT_NC_this_category_cannot_be_converted_to_forum'), 403);
		}

		$db = XenForo_Application::getDb();
		$db->update(
			'kmk_node',
			array(
				'node_type_id' => 'Forum'
			),
			array(
				'node_id = ?' => $category['node_id']
			)
		);

		$db->insert(
			'kmk_forum',
			array(
				'node_id' => $category['node_id'],
				'discussion_count' => 0,
				'message_count' => 0,
				'last_post_id' => 0,
				'last_post_date' => 0,
				'last_post_user_id' => 0,
				'last_post_username' => '',
				'last_thread_title' => '',
				'allow_posting' => 1,
				'count_messages' => 1,
				'find_new' => 1,
				'require_prefix' => 0,
				'allowed_watch_notifications' => 'all',
				'prefix_cache' => '',
				'default_prefix_id' => 0,
				'default_sort_order' => 'last_post_date',
				'default_sort_direction' => 'desc'
			)
		);

		XenForo_Application::defer('Permission', array(), 'Permission', true);
		$nodeModel->updateNestedSetInfo();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
			XenForo_Link::buildAdminLink('forums/edit', $category)
		);
	}
}