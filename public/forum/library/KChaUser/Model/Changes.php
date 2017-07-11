<?php

class KChaUser_Model_Changes extends XenForo_Model
{
	public function getChangeById($changeId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM xu_username_change_logs
			WHERE change_id = ?
		', $changeId);
	}
	public function getAllUserNames()
	{
		return $this->_getDb()->fetchCol('
			SELECT username
			FROM kmk_user
		');
	}
	public function getUserLastChange($userId)
	{
		return $this->_getDb()->fetchRow('
			SELECT max(change_date) change_date
			FROM xu_username_change_logs
			WHERE user_id = ?
		', $userId);
	}
	public function getAllChangesForUser($userId)
	{
		return $this->_getDb()->fetchAll('
			SELECT *
			FROM xu_username_change_logs
			WHERE user_id = ?
			AND is_private = 0
			ORDER BY change_date DESC
		', $userId);
	}
	public function deleteLogsForUser($userId)
	{
		$logs = $this->_getDb()->fetchAll('
			SELECT *
			FROM xu_username_change_logs
			WHERE user_id = ?
		', $userId);
		
		foreach ($logs AS $log)
		{
			$dw = XenForo_DataWriter::create('KChaUser_DataWriter_User');
			$dw->setExistingData($log['change_id']);
			$dw->delete();
		}
	}
}