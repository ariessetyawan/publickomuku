<?php

class EWRatendo_Model_Birthdays extends XenForo_Model
{
	public function getBirthdays($month, $day)
	{
		$options = XenForo_Application::get('options');
		$cutoff = strtotime('-'.$options->EWRatendo_birthdaycutoff.' months');

		$birthdays = $this->_getDb()->fetchAll("
			SELECT * 
				FROM kmk_user
				LEFT JOIN kmk_user_profile ON (kmk_user_profile.user_id = kmk_user.user_id)
				LEFT JOIN kmk_user_option ON (kmk_user_option.user_id = kmk_user.user_id)
			WHERE kmk_user_profile.dob_month = ?
				AND kmk_user_profile.dob_day = ?
				AND kmk_user_option.show_dob_date != '0'
				AND kmk_user.is_banned = '0'
				AND kmk_user.last_activity > ?
			ORDER BY kmk_user.username
		", array($month, $day, $cutoff));

		foreach ($birthdays AS &$user)
		{
			$user = array_merge($user, $this->getModelFromCache('XenForo_Model_UserProfile')->getUserBirthdayDetails($user));
		}

		return $birthdays;
	}

	public function getBirthdayCount($month)
	{
		$options = XenForo_Application::get('options');
		$cutoff = strtotime('-'.$options->EWRatendo_birthdaycutoff.' months');

		$birthdays = $this->fetchAllKeyed("
			SELECT COUNT(*) AS count, kmk_user_profile.dob_day
				FROM kmk_user
				LEFT JOIN kmk_user_profile ON (kmk_user_profile.user_id = kmk_user.user_id)
				LEFT JOIN kmk_user_option ON (kmk_user_option.user_id = kmk_user.user_id)
			WHERE kmk_user_profile.dob_month = ?
				AND kmk_user_option.show_dob_date != '0'
				AND kmk_user.is_banned = '0'
				AND kmk_user.last_activity > ?
			GROUP BY kmk_user_profile.dob_day
		", 'dob_day', array($month, $cutoff));

		return $birthdays;
	}
}