<?php
class KomuKu_FollowingAlerts_Model_Follow extends XenForo_Model
{
	const THREAD_CREATE = 'thread_create';
	const POST_INSERT = 'post_insert';
	const PROFILE_POST = 'profile_post';
	const RESOURCE_CREATE = 'resource_create';
	
	public function getUsersFollowingUserId($userId)
	{
		return $this->fetchAllKeyed('
			SELECT user_follow.*, user.*, user_option.*
			FROM kmk_user_follow AS user_follow
				LEFT JOIN kmk_user AS user ON 
					(user.user_id = user_follow.user_id)
				LEFT JOIN kmk_user_option AS user_option ON 
					(user_option.user_id = user.user_id)
			WHERE user_follow.follow_user_id = ?
		','user_id', $userId);
	}

	public function alertOnPostOrThread(array $post, array $noAlerts = array())
	{
		$users = $this->getUsersFollowingUserId($post['user_id']);
		if (empty($users))
		{
			return $noAlerts;
		}

		foreach ($users as $userId => $user)
		{
			$options = @unserialize($user['alert_preferences']);
			if (!is_array($options))
			{
				continue;
			}

			if ($this->getModelFromCache('XenForo_Model_User')->isUserIgnored($user, $post['user_id']))
			{
				continue;
			}

			if (is_array($noAlerts['alerted']))
			{
				if (in_array($user['user_id'], $noAlerts['alerted']))
				{
					continue;
				}
			}

			if ($user['user_id'] == $post['user_id'])
			{
				continue;
			}

			$alertType = ($post['attach_count'] ? 'insert_attachment' : 'insert');
			if ($post['position'])
			{
				if (isset($options[self::POST_INSERT]) && $options[self::POST_INSERT])
				{
					// send alert when create new post!
					XenForo_Model_Alert::alert($user['user_id'],
						$post['user_id'], $post['username'],
						'post', $post['post_id'],
						$alertType
					);

					$noAlerts['alerted'] = $user['user_id'];
				}
			}
			else
			{
				if (isset($options[self::THREAD_CREATE]) && $options[self::THREAD_CREATE])
				{
					// send alert when create new thread!
					XenForo_Model_Alert::alert($user['user_id'],
						$post['user_id'], $post['username'],
						'post', $post['post_id'],
						$alertType
					);

					$noAlerts['alerted'][] = $user['user_id'];
				}
			}
		}

		return $noAlerts;
	}

	public function alertOnResourceUpdate(array $update, array $resource, array $noAlerts = array())
	{
		$users = $this->getUsersFollowingUserId($resource['user_id']);
		if (empty($users))
		{
			return $noAlerts;
		}

		foreach ($users as $userId => $user)
		{
			$options = @unserialize($user['alert_preferences']);
			if (!is_array($options))
			{
				continue;
			}

			if ($user['user_id'] == $resource['user_id'])
			{
				continue;
			}

			if (in_array($user['user_id'], $noAlerts['alerted']))
			{
				continue;
			}

			if (isset($options[self::RESOURCE_CREATE]) && $options[self::RESOURCE_CREATE])
			{
				XenForo_Model_Alert::alert(
					$user['user_id'],
					$resource['user_id'],
					$resource['username'],
					'resource_update',
					$update['resource_update_id'],
					'insert'
				);

				$noAlerts['alerted'][] = $user['user_id'];
			}
		}
		
		return $noAlerts;
	}

	public function verifyActions(array $actions) {
		if (count($actions) != 4) return false; // hack?
		
		if (!$this->canFollowThread() && isset($actions['thread_create'])) {
			$actions['thread_create'] = 0;
		}
		
		if (!$this->canFollowStatus() && isset($actions['profile_post'])) {
			$actions['profile_post'] = 0;
		}
		
		if (!$this->canFollowPost() && isset($actions['post_insert'])) {
			$actions['post_insert'] = 0;
		}
		
		if (!$this->canFollowResource() && isset($actions['resource_create'])) {
			$actions['resource_create'] = 0;
		}
		
		return $actions;
	}

	public function canFollowThread(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		return ($viewingUser['user_id']
			&& XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'followthread')
		);
	}
	
	public function canFollowPost(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		return ($viewingUser['user_id']
			&& XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'followpost')
		);
	}
	
	public function canFollowStatus(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		return ($viewingUser['user_id']
			&& XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'followstatus')
		);
	}

	public function canFollowResource(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		return ($viewingUser['user_id']
			&& $this->addOnValidAndUsable('XenResource')
			&& XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'followresource')
		);
	}

	public function addOnValidAndUsable($addOnId)
	{
		$addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById($addOnId);

		return !empty($addOn);
	}
}