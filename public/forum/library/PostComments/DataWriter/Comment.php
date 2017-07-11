<?php

class PostComments_DataWriter_Comment extends XenForo_DataWriter
{
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields()
	{
		return array(
			'kmk_post_comments' => array(
				'comment_id' => array(
					'type' => self::TYPE_UINT,	
					'autoIncrement' => true
				),
				'user_id' => array(
					'type' => self::TYPE_UINT,	
					'required' => true
				),
				'username' => array(
					'type' => self::TYPE_STRING,  
					'required' => true, 
					'maxLength' => 50
				),
				'content_id' => array(
					'type' => self::TYPE_UINT,	
					'required' => true
				),
				'comment' => array(
					'type' => self::TYPE_STRING,
					'required' => true
				),
				'comment_date' => array(
					'type' => self::TYPE_UINT,	
					'default' => XenForo_Application::$time
				),
			)
		);
	}

	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @see XenForo_DataWriter::_getExistingData()
	*
	* @return array|false
	*/
	protected function _getExistingData($data)
	{
		if (!$id = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		return array('kmk_post_comments' => $this->_getCommentModel()->getCommentById($id));
	}

	/**
	* Gets SQL condition to update the existing record.
	* 
	* @see XenForo_DataWriter::_getUpdateCondition() 
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
		return 'comment_id = ' . $this->_db->quote($this->getExisting('comment_id'));
	}

	/**
	 * Pre-save handling.
	 */
	protected function _preSave()
	{
		global $count;

		$visitor = XenForo_Visitor::getInstance();

		//Set up a min/max characters and spam words block in staff comments. Staff is excluded from both restrictions
		if ($this->isChanged('comment') AND !$visitor['is_admin'] AND !$visitor['is_moderator'] AND !$visitor['is_staff']) 
		{
			//Set up a minimum characters in post comments
			$options = XenForo_Application::getOptions();
			$minchar = (int) $options->get('comments_minchar');
			$maxchar = (int) $options->get('comments_maxchar');
			
			//Min char
			if ($minchar != 0 AND utf8_strlen($this->get('comment')) < $minchar) 
			{
				$this->error(new XenForo_Phrase('x_y_min_chars_pos_comment_required', array('minchar' => $minchar)));
			}

			//Max char
			if ($maxchar != 0 AND utf8_strlen($this->get('comment')) > $maxchar) 
			{
				$this->error(new XenForo_Phrase('x_y_max_chars_pos_comment_required', array('maxchar' => $maxchar)));
			}

			//Stop spam/bad words in spam comments
			$spamwords = explode(",", $options->comments_spamwords);

			if ($options->comments_spamwords  != "")
			{
				if (is_array($spamwords))
				{
					foreach ($spamwords as $key => $spamword)
					{
						$spamword = trim($spamword);

						if ($spamword != '')
						{
							if (!strstr($this->get('comment'), $spamword))
							{
								++$count;
							}
						}
					}

					if($count AND $count!= count($spamwords))
					{
						$this->error(new XenForo_Phrase('stop_bad_spam_words_comments', array('badwordsfilter' => $options->comments_spamwords)));
					}
				}
			}
		}
	}

	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		$comment = $this->getMergedData();

		if ($this->isInsert())
		{
			$dw = XenForo_DataWriter::create('XenForo_DataWriter_DiscussionMessage_Post');

			$dw->setExistingData($this->get('content_id'));

			if ($comment['comment'])
			{
				$dw->set('comment_count', $dw->get('comment_count') + 1);
			}
			$dw->save();

			$post = $this->_getPostModel()->getPostById($this->get('content_id'), array(
				'join' => XenForo_Model_User::FETCH_USER_OPTION
			));
		}

		if (XenForo_Application::get('options')->comments_newsfeed)
		{
			$this->getModelFromCache('XenForo_Model_NewsFeed')->publish($this->get('user_id'), $this->get('username'), 'post_comment', $this->get('content_id'), 'insert', array('comment_id' => $this->get('comment_id'), 'comment' => $this->get('comment')));
		}
	}

	/**
	 * Post-delete handling.
	 */
	protected function _postDelete()
	{
		$comment = $this->getMergedData();

		$this->_db->query('
			UPDATE kmk_post
			SET comment_count = IF(comment_count > 0, comment_count - 1, 0)
			WHERE post_id = ?
		', $this->get('content_id'));

		if (XenForo_Application::get('options')->comments_newsfeed)
		{
			$this->getModelFromCache('XenForo_Model_NewsFeed')->delete('post_comment', $this->get('content_id'), $this->get('user_id'));
		}
	}

	/**
	 * @return XenForo_Model_Post Model
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}

	/**
	 * @return PostComments_Model_Comment Model
	 */
	protected function _getCommentModel()
	{
		return $this->getModelFromCache('PostComments_Model_Comment');
	}
}