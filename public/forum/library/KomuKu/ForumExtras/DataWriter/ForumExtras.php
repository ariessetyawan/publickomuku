<?php

//######################## Extra Forum View Settings By KomuKu ###########################
class KomuKu_ForumExtras_DataWriter_ForumExtras extends XenForo_DataWriter
{
	//Error phrase
	protected $_existingDataErrorPhrase = 'requested_extraforum_not_found';

	//Get the fields for our custom table that we created
	protected function _getFields()
	{
		return array(
			'kmk_forum_extra_view_settings' => array(
				'id' => array(
					'type' => self::TYPE_UINT,
					'autoIncrement' => true
				),
				
				'node_id' => array(
					'type' => self::TYPE_INT,
					'default' => 0
				),
				
				'message_count' => array(
					'type' => self::TYPE_INT,
					'default' => 0
				),
				
				'daily_posts' => array(
					'type' => self::TYPE_INT,
					'default' => 0
				),
				
				'register_date' => array(
					'type' => self::TYPE_INT,
					'default' => 0
				),
				
				'user_age' => array(
					'type' => self::TYPE_INT,
					'default' => 0
				),
				
				'user_gender' => array(
					'type' => self::TYPE_STRING
				),
				
				'ban' => array(
					'type' => self::TYPE_STRING
				),
				
			)
		);
	}

	//Get the current existing data from the data that it was passed
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('kmk_forum_extra_view_settings' => $this->_getForumextraModel()->getForumextraById($id));
	}

	//Update the existing field
	protected function _getUpdateCondition($tableName)
	{
		return 'id = ' . $this->_db->quote($this->getExisting('id'));
	}

	//preSsave handling
	protected function _preSave()
	{
		if ($this->isInsert())
		{
			$extras = $this->_getForumextraModel()->getAllForumextras();
		}
	}

	//postSsave handling
	protected function _postSave()
	{
		$this->_rebuildForumextrasCache();
	}

	//postDelete handling
	protected function _postDelete()
	{
		$this->_rebuildForumextrasCache();
	}

	//Rebuild the Extra Forum View Settings cache
	protected function _rebuildForumextrasCache()
	{
		$this->_getForumextraModel()->rebuildForumextrasCache();
	}

	//Return the Extra Forum View Settings model
	protected function _getForumextraModel()
	{
		return $this->getModelFromCache('KomuKu_ForumExtras_Model_ForumExtras');
	}
}