<?php
class KomuKu_FollowingAlerts_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_KomuKu_FollowingAlerts_XenForo_DataWriter_DiscussionMessage_Post
{
	protected function _postSaveAfterTransaction()
	{
		$response = parent::_postSaveAfterTransaction();

		if ($this->get('message_state') == 'visible')
		{
			if ($this->isInsert() || $this->getExisting('message_state') == 'moderated')
			{
				$post = $this->getMergedData();


				$notifiedOnMessage = $this->getModelFromCache('XenForo_Model_ForumWatch')->get_notifiedUserIds_onMessage($post);
				$notifiedOnReply = $this->getModelFromCache('XenForo_Model_ThreadWatch')->get_notifiedUserIds_onReply($post);

				$noAlerts = array_merge($notifiedOnMessage, $notifiedOnReply);

				$this->getModelFromCache('KomuKu_FollowingAlerts_Model_Follow')->alertOnPostOrThread($post, $noAlerts);
			}
		}

		return $response;
	}
}