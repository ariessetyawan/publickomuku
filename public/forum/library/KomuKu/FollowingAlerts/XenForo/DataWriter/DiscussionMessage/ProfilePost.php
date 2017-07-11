<?php
class KomuKu_FollowingAlerts_XenForo_DataWriter_DiscussionMessage_ProfilePost extends XFCP_KomuKu_FollowingAlerts_XenForo_DataWriter_DiscussionMessage_ProfilePost
{
	protected function _publishAndNotify()
	{
		parent::_publishAndNotify();
		
		if ($this->get('message_state') == 'visible')
		{
			if ($this->isInsert() || $this->getExisting('message_state') == 'moderated')
			{
				if (XenForo_Visitor::getUserId() == $this->get('profile_user_id'))
				{
					$this->_alertToFollowers();
				}
			}
		}
	}
	
	protected function _alertToFollowers()
	{
		$userModel = XenForo_Model::create('XenForo_Model_User');

		$userId = $this->get('profile_user_id');
		$profilePostId = $this->get('profile_post_id');

		$users = $this->getModelFromCache('KomuKu_FollowingAlerts_Model_Follow')->getUsersFollowingUserId($userId);
		
		if (!$profileUser = $this->getExtraData(self::DATA_PROFILE_USER))
		{
			$profileUser = $userModel->getUserById($this->get('profile_user_id'), array(
				'join' => XenForo_Model_User::FETCH_USER_FULL
			));
		}

		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$options = @unserialize($user['alert_preferences']);
				if (!is_array($user))
				{
					continue;
				}
				
				if ($userModel->isUserIgnored($user, $profileUser['user_id']))
				{
					continue;
				}

				if (isset($options[KomuKu_FollowingAlerts_Model_Follow::PROFILE_POST])
					&& $options[KomuKu_FollowingAlerts_Model_Follow::PROFILE_POST]
				)
				{
					if (XenForo_Model_Alert::userReceivesAlert($user, $this->getContentType(), 'insert'))
					{
						XenForo_Model_Alert::alert(
							$user['user_id'],
							$profileUser['user_id'],
							$profileUser['username'],
							$this->getContentType(),
							$this->get('profile_post_id'),
							'insert_edited'
						);
					}
				}
			}
		}
		
		// dont support send email at this time.
	}
}