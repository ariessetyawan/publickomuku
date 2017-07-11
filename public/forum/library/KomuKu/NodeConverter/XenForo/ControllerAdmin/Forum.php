<?php

class KomuKu_NodeConverter_XenForo_ControllerAdmin_Forum extends XFCP_KomuKu_NodeConverter_XenForo_ControllerAdmin_Forum
{
	public function actionConvert()
	{
		$forumModel = $this->_getForumModel();

		$forum = $forumModel->getForumById($this->_input->filterSingle('node_id', XenForo_Input::UINT));
		if (!$forum)
		{
			return $this->responseError(new XenForo_Phrase('requested_forum_not_found'), 404);
		}

		if (empty($forum['canConvert']))
		{
			return $this->responseError(new XenForo_Phrase('TT_NC_this_forum_cannot_be_converted_to_category'), 403);
		}

		$db = XenForo_Application::getDb();
		$db->update(
			'kmk_node',
			array(
				'node_type_id' => 'Category'
			),
			array(
				'node_id = ?' => $forum['node_id']
			)
		);

		$db->delete(
			'kmk_forum',
			array(
				'node_id = ?' => $forum['node_id']
			)
		);
		$db->delete('kmk_forum_prefix', 'node_id = ' . $db->quote($forum['node_id']));
		$db->delete('kmk_forum_watch', 'node_id = ' . $db->quote($forum['node_id']));

		XenForo_Application::defer('Permission', array(), 'Permission', true);
		$this->_getNodeModel()->updateNestedSetInfo();

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
			XenForo_Link::buildAdminLink('categories/edit', $forum)
		);
	}
}