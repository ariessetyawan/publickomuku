<?php

//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_ControllerPublic_Member extends XFCP_KomuKu_LikeThreads_ControllerPublic_Member
{
	protected function _getNotableMembers($type, $limit)
	{
	    $parent = parent::_getNotableMembers($type, $limit);
		
		if ($type == 'popular' AND XenForo_Visitor::getInstance()->hasPermission('forum', 'canViewLikes'))
		{
			$userModel = $this->_getUserModel();

			$notableCriteria = array(
				'is_banned' => 0
			);

			$users = $userModel->getUsers($notableCriteria, array(
				'join' => XenForo_Model_User::FETCH_USER_FULL,
				'limit' => $limit,
				'order' => 'liked_thread_count',
				'direction' => 'desc'
			));

			foreach ($users AS $userId => $user)
			{
				if ($user['liked_thread_count'] < 1)
				{
					unset($users[$userId]);
				}
			}

			return array($users, 'liked_thread_count');
		}else{
			return $parent;
		}
	}
}