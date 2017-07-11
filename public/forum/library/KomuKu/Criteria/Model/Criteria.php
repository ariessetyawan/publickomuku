<?php

class KomuKu_Criteria_Model_Criteria extends XenForo_Model
{
	public function getMessageCountSinceDate($date, $userId)
	{
		$db = $this->_getDb();

		return $db->fetchOne("
			SELECT COUNT(*)
			FROM kmk_post
			WHERE user_id = ?
				AND post_date > ?
				AND message_state = 'visible'
		", array(
			$userId,
			$date
		));
	}

	public function getThreadCount($userId)
	{
		$db = $this->_getDb();

		return $db->fetchOne("
			SELECT COUNT(*)
			FROM kmk_thread
			WHERE user_id = ?
				AND discussion_state = 'visible'
		", $userId);
	}

	public function getTagCount($userId)
	{
		$db = $this->_getDb();

		return $db->fetchOne("
			SELECT COUNT(*)
			FROM kmk_tag_content
			WHERE add_user_id = ?
				AND visible = 1
		", $userId);
	}
}