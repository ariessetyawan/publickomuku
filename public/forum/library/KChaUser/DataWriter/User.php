<?php

class KChaUser_DataWriter_User extends XenForo_DataWriter
{
	protected function _getFields()
	{
		return array(
			'xu_username_change_logs' => array(
				'change_id'      => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'user_id'        => array('type' => self::TYPE_UINT, 'required' => true),
				'old_username'   => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50),
				'new_username'   => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50, 'requiredError' => 'please_enter_valid_name', 'verification' => array('$this', '_verifyValidUsername')),
				'is_private'     => array('type' => self::TYPE_UINT, 'required' => true),
				'change_date'    => array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
			)
		);
	}
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data, 'change_id'))
		{
			return false;
		}

		return array('xu_username_change_logs' => $this->_getUsernameChangesModel()->getChangeById($id));
	}
	protected function _getUpdateCondition($tableName)
	{
		return 'change_id = ' . $this->_db->quote($this->getExisting('change_id'));
	}
	protected function _getUsernameChangesModel()
	{
		return $this->getModelFromCache('KChaUser_Model_Changes');
	}
	protected function _verifyValidUsername(&$username)
	{
		$model = $this->_getUsernameChangesModel();
		$visitor = XenForo_Visitor::getInstance();
		
		$allUserNames = $model->getAllUserNames();
		$lastChange = $model->getUserLastChange($this->get('user_id'));
		
		$daysBetweenChanges = $visitor->hasPermission('usernameChanges', 'changeUsername'); 
		
		if ($this->get('old_username') == $username)
		{
			$this->error(new XenForo_Phrase('KChaUser_change_username_same_username'), 'new_username');
			return false;
		}
		
		if (in_array($username, $allUserNames))
		{
			$this->error(new XenForo_Phrase('KChaUser_change_username_usrname_already_taken'), 'new_username');
			return false;
		}
		
		if ($lastChange)
		{
			if (time() < $lastChange['change_date'] + 86400 * $daysBetweenChanges)
			{
				$nextChange = intval(($lastChange['change_date'] + 86400 * $daysBetweenChanges - time()) / 86400);
				$this->error(new XenForo_Phrase('KChaUser_change_username_changed_recently'), 'new_username');
			}
		}
		
		return true;
	}
	protected function _postSave()
	{
		$userDw = XenForo_DataWriter::create('XenForo_DataWriter_User');
		$userDw->setExistingData($this->get('user_id'));
		$userDw->set('username', $this->get('new_username'));
		$userDw->save();
	}
}