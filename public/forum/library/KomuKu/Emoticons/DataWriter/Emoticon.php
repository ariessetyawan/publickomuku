<?php

class KomuKu_Emoticons_DataWriter_Emoticon extends XenForo_DataWriter
{
	/**
	 * {@inheritdoc}
	 */
	protected function _getFields()
	{
		return array(
			'kmk_user_emoticon' => array(
				'emoticon_id' 	=> array('type' => static::TYPE_UINT, 'autoIncrement' => true),
				'user_id'			=> array('type' => static::TYPE_UINT, 'required' => true),

				'caption'			=> array('type' => static::TYPE_STRING, 'required' => true, 'maxLength' => 50),
				'text_replace'		=> array('type' => static::TYPE_STRING, 'required' => true, 'maxLength' => 25,
					'verification' 	=> array('$this', '_verifyTextReplace')),

				'width' 			=> array('type' => static::TYPE_UINT, 'default' => 0),
				'height'			=> array('type' => static::TYPE_UINT, 'default' => 0),
				'file_size'			=> array('type' => static::TYPE_UINT, 'default' => 0),
				'filename'			=> array('type' => static::TYPE_STRING, 'required' => true),
				'filehash'			=> array('type' => static::TYPE_STRING, 'required' => true),
				'extension'			=> array('type' => static::TYPE_STRING, 
					'allowedValues' => $this->_getEmoticonModel()->getAllowedExtensions()),

				'added_at'			=> array('type' => static::TYPE_UINT, 'default' => XenForo_Application::$time)
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _getExistingData($data)
	{
		if(!$userEmoticonId = $this->_getExistingPrimaryKey($data, 'emoticon_id'))
		{
			return false;
		}

		return array('kmk_user_emoticon' => $this->_getEmoticonModel()->getEmoticonById($userEmoticonId));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _getUpdateCondition($tableName)
	{
		return 'emoticon_id = ' . $this->_db->quote($this->getExisting('emoticon_id'));
	}

	protected function _verifyTextReplace(&$textReplace)
	{
		if($this->getExisting('text_replace') == $textReplace) 
		{
			// Don't verify if not changed the text replace
			return true;
		}

		if(!preg_match('/^[a-zA-z0-9]+$/i', $textReplace))
		{
			// Some characters is not valid. Throw errors.
			$this->error(new XenForo_Phrase('emoticon_enter_text_replace_using_alphanumeric'), 'text_replace');
			return false;
		}

		$textLength = strlen($textReplace);
		if($textLength < 5 OR $textLength > 23)
		{
			// Too many characters.
			$this->error(new XenForo_Phrase('emoticon_please_enter_text_replace_is_at_least_x_characters_and_at_most_y_characters', array(
				'min' => 5,
				'max' => 23
			)));
			return false;
		}

		if($this->_getEmoticonModel()->getEmoticonByTextReplace($textReplace))
		{
			$this->error(new XenForo_Phrase('emoticon_the_text_replace_x_was_used_by_another_user', array(
				'text' => $textReplace
			)), 'text_replace');
			return false;
		}

		// Using :awesome_text:
		$cloneTextReplace = $textReplace;
		$textReplace = sprintf(':%s:', $textReplace);

		if($this->getModelFromCache('XenForo_Model_Smilie')->getSmiliesByText($textReplace))
		{
			// Well. The text replace was used by system.
			$this->error(new XenForo_Phrase('emoticon_the_text_replace_x_was_used_by_another_user', array(
				'text' => $cloneTextReplace
			)), 'text_replace');
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _preSave()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _postSave()
	{
		$this->_triggerRebuildCache();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _postDelete()
	{
		$this->_triggerRebuildCache();

		$path = $this->_getEmoticonModel()->getEmoticonPath($this->getMergedData());
		if(file_exists($path))
		{
			// Delete the emoticon which stored as file
			unlink($path);
		}
	}

	protected function _triggerRebuildCache()
	{
		$this->_getEmoticonModel()->rebuildCache();
	}

	/**
	 * @return KomuKu_Emoticons_Model_Emoticon
	 */
	protected function _getEmoticonModel()
	{
		return $this->getModelFromCache('KomuKu_Emoticons_Model_Emoticon');
	}
}
