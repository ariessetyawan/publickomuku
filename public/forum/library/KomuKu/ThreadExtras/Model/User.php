<?php

class KomuKu_ThreadExtras_Model_User extends XenForo_Model_User
{
	public function getUserIdsByNames(array $conditions)
	{
		return $this->_getDb()->fetchCol('
			SELECT user.user_id
			FROM kmk_user AS user
			WHERE user.username IN (' . $this->_getDb()->quote($conditions['usernames']) .')
			ORDER BY user.user_id
		');
	}
}