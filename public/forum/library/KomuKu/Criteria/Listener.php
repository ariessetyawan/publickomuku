<?php

class KomuKu_Criteria_Listener
{
	public static function criteriaUser($rule, array $data, array $user, &$returnValue)
	{
		$criteriaModel = XenForo_Model::create('KomuKu_Criteria_Model_Criteria');

		switch ($rule)
		{
			case 'KomuKu_criteria_x_messages_y_days':
			$date = XenForo_Application::$time - ($data['days'] * 86400);

			$messages = $criteriaModel->getMessageCountSinceDate($date, $user['user_id']);

			if (!$messages)
			{
				$returnValue = false;
			}
			if ($messages >= $data['messages'])
			{
				$returnValue = true;
			}
			break;

			case 'KomuKu_criteria_thread_count':
			$threads = $criteriaModel->getThreadCount($user['user_id']);

			if (!$threads)
			{
				$returnValue = false;
			}
			if ($threads >= $data['items'])
			{
				$returnValue = true;
			}
			break;

			case 'KomuKu_criteria_tag_count':
			$tags = $criteriaModel->getTagCount($user['user_id']);

			if (!$tags)
			{
				$returnValue = false;
			}
			if ($tags >= $data['items'])
			{
				$returnValue = true;
			}
			break;

			case 'KomuKu_criteria_has_avatar':
            if (!empty($user['avatar_date']) || !empty($user['gravatar']))
            {
                $returnValue = true;
            }
			break;
		}
	}
}