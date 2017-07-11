<?php

class KomuKu_ProfileCover_NewsFeedHandler_Cover extends XenForo_NewsFeedHandler_Abstract
{
	public function getContentByIds(array $contentIds, $model, array $viewingUser)
	{
		$userModel = $model->getModelFromCache('XenForo_Model_User');

		return $userModel->getUsersByIds($contentIds);
	}

	public function canViewNewsFeedItem(array $item, $content, array $viewingUser)
	{
		return XenForo_Model::create('XenForo_Model_UserProfile')->canViewFullUserProfile($item, $null, $viewingUser);
	}
	
	protected function _prepareNewsFeedItemAfterAction(array $item, $content, array $viewingUser)
	{
		$item['itemUser'] = $item;
		return $item;
	}
}