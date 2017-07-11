<?php
//######################## Like Threads By KomuKu ###########################
class KomuKu_LikeThreads_DataWriter_LikeThreads extends XenForo_DataWriter
{
	protected $_existingDataErrorPhrase = 'requested_page_not_found';

	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'kmk_liked_threads' => array(
			    'like_id'	=> array('type' => self::TYPE_UINT, 'autoIncrement' => true),
				'thread_id'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'user_id'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'username'  => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50),
				'like_date'	=> array('type' => self::TYPE_UINT, 'required' => true),
				'message' => array('type' => self::TYPE_STRING, 'maxLength' => 150),
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|bool
	*/
	protected function _getExistingData($data)
	{
		if (!$likeId = $this->_getExistingPrimaryKey($data, 'like_id'))
		{
			return false;
		}

		return array('kmk_liked_threads' => $this->getModelFromCache('KomuKu_LikeThreads_Model_LikeThreads')->getLikesById($likeId));
	}

	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'like_id = ' . $this->_db->quote($this->getExisting('like_id'));
	}

	/**
	 * Pre-save handling.
	 */
	protected function _preSave()
	{
		$visitor = XenForo_Visitor::getInstance();
		$this->set('user_id', $visitor['user_id']);
		$this->set('username', $visitor['username']);
		$this->set('like_date', XenForo_Application::$time);
	}
	
	/**
	 * Post-save handling.
	 */
	protected function _postSave() 
	{
	    //Update thread like count.
		if ($this->isInsert()) 
		{
			$this->_db->query("
				UPDATE `kmk_thread`
				SET like_count = like_count + ?
				WHERE thread_id = ?
			", array(
				1,
				$this->get('thread_id'),
			));
			
			//Update users most liked threads count.
			$threadId = $this->get('thread_id');

			XenForo_Model::create('KomuKu_LikeThreads_Model_LikeThreads')->countThreadsLikedForUser($threadId);
			
			//Show new reviews in the news feed
            $this->_getNewsFeedModel()->publish(
				$this->get('user_id'),
				$this->get('username'),
				'th',
				$this->get('like_id'),
				'insert'
			);
		}
	}
	
	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$this->_db->query("
				UPDATE `kmk_thread`
				SET like_count = like_count - ?
				WHERE thread_id = ?
			", array(
				1,
				$this->get('thread_id'),
			));	
	}
}