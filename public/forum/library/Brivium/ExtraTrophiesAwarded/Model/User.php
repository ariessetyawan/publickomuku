<?php

class Brivium_ExtraTrophiesAwarded_Model_User extends XenForo_Model_User
{
	public function getBretaUserIdsInRange($start, $limit)
	{
		$db = $this->_getDb();

		return $db->fetchCol($db->limit("
			SELECT user_id
			FROM kmk_user
			WHERE user_id > ?
				AND user_state = 'valid'
				AND is_banned = 0
			ORDER BY user_id
		", $limit), $start);
	}
}