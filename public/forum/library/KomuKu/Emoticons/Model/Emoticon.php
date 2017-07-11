<?php

class KomuKu_Emoticons_Model_Emoticon extends XenForo_Model
{
	/**
	 * Const to define join table kmk_user
	 */
	const FETCH_USER  = 0x01;

	/**
	 * Allowed type of emoticon extension
	 *
	 * @var array
	 */
	protected $_allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

	/**
	 * Do upload an emoticon for user
	 *
	 * @return void
	 */
	public function doUpload(XenForo_Upload $upload, array $input, array $user = null)
	{
		$user = $user ?: XenForo_Visitor::getInstance();
		if(!isset($user['permissions']))
		{
			throw new XenForo_Exception('Missing permissions key in user information.');
		}

		$upload->setConstraints(array(
			'extension' => $this->getAllowedExtensions()
		));

		if(!$upload->isValid())
		{
			return array('errors' => $upload->getErrors());
		}

		$errors = $this->_checkEmoticonRules($upload, $user);
		if($errors)
		{
			return array('errors' => $errors);
		}

		$tempFile = tempnam(XenForo_Helper_File::getTempDir(), 'emoticon');
		if(!$tempFile)
		{
			// Could not make new temp file. Should be cancel the current processing
			throw new XenForo_Exception('Upload emoticon failed. Please try again later.', true);
		}
		XenForo_Helper_File::safeRename($upload->getTempFile(), $tempFile);

		$filehash = md5_file($tempFile);
		if($this->getEmoticons(array('filehash' => $filehash)) && KomuKu_Emoticons_Option::get('preventDuplicate'))
		{
			unlink($tempFile);
			return array('errors' => new XenForo_Phrase('emoticon_the_emoticon_was_used_by_another_user'));
		}

		$dw = XenForo_DataWriter::create('KomuKu_Emoticons_DataWriter_Emoticon');
		$dw->bulkSet(array(
			'user_id' => $user['user_id'],
			'caption' => $input['caption'],
			'text_replace' => $input['text_replace'],
			'width' => $upload->getImageInfoField('width'),
			'height' => $upload->getImageInfoField('height'),
			'file_size' => filesize($tempFile),
			'filename' 	=> $upload->getFileName(),
			'filehash' => $filehash,
			'extension' => XenForo_Helper_File::getFileExtension($upload->getFileName())
		));

		$dw->preSave();
		if($dw->hasErrors())
		{
			// Some errors was found. Stop processing
			unlink($tempFile);
			return array('errors' => $dw->getErrors());
		}

		$dw->save();
		$emoticon = $dw->getMergedData();

		$target = $this->getEmoticonPath($emoticon);
		$targetDirectory = dirname($target);

		if(!is_dir($targetDirectory))
		{
			XenForo_Helper_File::createDirectory($targetDirectory, true);
		}

		XenForo_Helper_File::safeRename($tempFile, $target);
		return array('emoticon' => $emoticon);
	}

	protected function _checkEmoticonRules(XenForo_Upload $upload, $user)
	{
		$errors = array();

		$maxWidth = KomuKu_Emoticons_Option::get('maxWidth');
		$maxHeight = KomuKu_Emoticons_Option::get('maxHeight');
		$maxSize = KomuKu_Emoticons_Option::get('maxSize');

		if(!$this->_hasPermission($user['permissions'], 'noMaxWidth') && $maxWidth)
		{
			if ($upload->getImageInfoField('width') > $maxWidth)
			{
				$errors[] = new XenForo_Phrase('emoticon_please_use_emoticon_which_at_most_x_pixels_of_width',array(
					'pixel' => XenForo_Locale::numberFormat($maxWidth)
				));
			}
		}

		if(!$this->_hasPermission($user['permissions'], 'noMaxHeight') && $maxHeight)
		{
			if ($upload->getImageInfoField('height') > $maxHeight)
			{
				$errors[] = new XenForo_Phrase('emoticon_please_use_emoticon_which_at_most_x_pixels_of_height', array(
					'pixel' => XenForo_Locale::numberFormat($maxHeight)
				));
			}
		}

		if(!$this->_hasPermission($user['permissions'], 'noMaxSize') && $maxSize)
		{
			if(filesize($upload->getTempFile()) > $maxSize)
			{
				$errors[] = new XenForo_Phrase('emoticon_the_emoticon_file_size_large_smaller_x', array(
					'size' => XenForo_Locale::numberFormat($maxSize, 'size')
				));
			}
		}

		return $errors;
	}

	/**
	 * Register an lazy loader to application then reuse later.
	 *
	 * @return void
	 */
	public function registerLazyLoader()
	{
		if(XenForo_Application::isRegistered('emoticons')) {
			return;
		}

		$app = XenForo_Application::getInstance();
		// Attach the fetch emoticons to lazy
		// Only fetch when really need it.
		$app->addLazyLoader('emoticons', array($this, 'getEmoticonsFromCache'));
	}

	/**
	 * Retrive user emoticon record by id
	 *
	 * @return array|false
	 */
	public function getEmoticonById($userEmoticonId, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareEmoticonFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT emoticon.*
				'. $joinOptions['selectFields'] .'
			FROM kmk_user_emoticon AS emoticon
				'. $joinOptions['joinTables'] .'
			WHERE emoticon.emoticon_id = ?
		', $userEmoticonId);
	}

	/**
	 * Get all emoticons which defined by user
	 *
	 * @param integer user id to retrive
	 * @return array
	 */
	public function getEmoticonsByUserId($userId)
	{
		return $this->getEmoticons(array('user_id' => $userId));
	}

	/**
	 * Get emoticon record by text replace
	 *
	 * @return array|false
	 */
	public function getEmoticonByTextReplace($textReplace, array $fetchOptions = array())
	{
		if(!strlen($textReplace))
		{
			throw new InvalidArgumentException('TextReplace parameter could not be empty.');
		}

		if(substr($textReplace, 0, 1) !== ':')
		{
			$textReplace = ':'.$textReplace;
		}

		if(substr($textReplace, -1) !== ':')
		{
			$textReplace = $textReplace.':';
		}

		$joinOptions = $this->prepareEmoticonFetchOptions($fetchOptions);

		return $this->_getDb()->fetchRow('
			SELECT emoticon.*
				'. $joinOptions['selectFields'] .'
			FROM kmk_user_emoticon AS emoticon
				'. $joinOptions['joinTables'] .'
			WHERE emoticon.text_replace = ?
		', array($textReplace));
	}

	/**
	 * Delete all emoticons which defined by user
	 *
	 * @return void
	 */
	public function deleteAllEmoticonsByUserId($userId)
	{
		$this->_getDb()->delete('kmk_user_emoticon', 'user_id = ' . $this->_getDb()->quote($userId));
		$this->rebuildCache();
	}

	public function getAllEmoticons()
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM kmk_user_emoticon
		', 'emoticon_id');
	}

	public function countEmoticons(array $conditions = array())
	{
		$whereClause = $this->prepareEmoticonConditions($conditions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM kmk_user_emoticon AS emoticon
			WHERE '. $whereClause .'
		');
	}

	public function getEmoticons(array $conditions = array(), array $fetchOptions = array())
	{
		$whereClause = $this->prepareEmoticonConditions($conditions);
		$joinOptions = $this->prepareEmoticonFetchOptions($fetchOptions);

		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT emoticon.*
					'. $joinOptions['selectFields'] .'
				FROM kmk_user_emoticon AS emoticon
					'. $joinOptions['joinTables'] .'
				WHERE '. $whereClause .'
				ORDER BY emoticon.added_at DESC
			', $limitOptions['limit'], $limitOptions['offset']
		), 'emoticon_id');
	}

	public function prepareEmoticonConditions(array $conditions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if(!empty($conditions['emoticon_id']))
		{
			$sqlConditions[] = 'emoticon.emoticon_id IN (' . $db->quote((array)$conditions['emoticon_id']) . ')';
		}

		if(!empty($conditions['user_id']))
		{
			$sqlConditions[] = 'emoticon.user_id IN (' . $db->quote((array)$conditions['user_id']). ')';
		}

		if(!empty($conditions['text_replace']))
		{
			$sqlConditions[] = 'emoticon.text_replace = ' . $db->quote($conditions['text_replace']);
		}

		if(!empty($conditions['filehash']))
		{
			$sqlConditions[] = 'emoticon.filehash = ' . $db->quote($conditions['filehash']);
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	public function prepareEmoticonFetchOptions(array $fetchOptions)
	{
		$selectFields = $joinTables = '';

		if(!empty($fetchOptions['join']))
		{
			if($fetchOptions['join'] & static::FETCH_USER)
			{
				$selectFields .= ',user.*';
				$joinTables .= '
					LEFT JOIN kmk_user AS user ON (user.user_id = emoticon.user_id)';
			}
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables' => $joinTables,
		);
	}

	/**
	 * Rebuild user emoticons cache
	 *
	 * @return void
	 */
	public function rebuildCache()
	{
		$emoticons = $this->getAllEmoticons();
		$this->_getDataRegistryModel()->set('KomuKu_emoticons', json_encode($emoticons));

		return $emoticons;
	}

	/**
	 * Retrive user emoticons from cache
	 *
	 * @return array
	 */
	public function getEmoticonsFromCache()
	{
		if(!$emoticons = $this->_getDataRegistryModel()->get('KomuKu_emoticons'))
		{
			$emoticons = $this->rebuildCache();
		}

		if(!is_array($emoticons))
		{
			$emoticons = json_decode($emoticons, true);
		}
		$emoticons = $this->prepareEmoticons($emoticons);

		return $emoticons;
	}

	public function getEmoticonsFromList($userId, array $emoticons = null)
	{
		if(empty($userId))
		{
			return array();
		}

		$emoticons = $emoticons ?: $this->getEmoticonsFromCache();
		$results = array();

		foreach($emoticons as $emoticon)
		{
			if($emoticon['user_id'] == $userId)
			{
				$results[$emoticon['emoticon_id']] = $emoticon;
			}
		}

		return $results;
	}

	public function prepareEmoticons(array $emoticons)
	{
		return array_map(array($this, 'prepareEmoticon'), $emoticons);
	}

	/**
	 * Add or Remove extra data in emoticon record
	 *
	 * @return array
	 */
	public function prepareEmoticon(array $emoticon)
	{
		$emoticon['emoticonUrl'] = $this->getEmoticonUrl($emoticon);
		$emoticon['emoticonPath'] = $this->getEmoticonPath($emoticon);

		$emoticon['canEdit'] = $this->canEditEmoticon($emoticon);
		$emoticon['canDelete'] = $this->canDeleteEmoticon($emoticon);

		return $emoticon;
	}

	public function prepareEmoticonsForEditor(array $emoticons)
	{
		$results = array();

		$mapping = array(
			'emoticon_id' => 'smilie_id',
			'caption' => 'title',
			'text_replace' => 'smilie_text',
			'emoticonUrl' => 'image_url',
		);

		foreach($emoticons as $emoticon)
		{
			$emoticonId = $emoticon['user_id'].'_'.$emoticon['emoticon_id'];
			$results[$emoticonId] = array(
				'smilieText' => array($emoticon['text_replace']),

				// Fixed the bug: https://xenforo.com/community/posts/1023157
				'sprite_params' => false
			);

			foreach($mapping as $from => $to)
			{
				$results[$emoticonId][$to] = $emoticon[$from];
			}
		}

		return $results;
	}

	public function canUseOwnEmoticons(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		return $viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'emoticons_enable');
	}

	public function canEditEmoticon(array $emoticon, &$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		return $emoticon['user_id'] == $viewingUser['user_id'];
	}

	public function canDeleteEmoticon(array $emoticon, &$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);

		return $emoticon['user_id'] == $viewingUser['user_id'];
	}

	public function canUploadEmoticons(&$errorPhraseKey = '', array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		if(!$viewingUser['user_id'])
		{
			return false;
		}

		$maxEmoticons = $this->_hasPermission($viewingUser['permissions'], 'maxEmoticons');
		if($maxEmoticons == -1)
		{
			// No limit
			return true;
		}

		if(empty($maxEmoticons))
		{
			// Not allow to use any emoticons
			return false;
		}

		$userId = $viewingUser['user_id'];
		$cacheName = 'emoticons'.$viewingUser['user_id'];

		if(!isset($this->_localCacheData[$cacheName]))
		{
			$this->_localCacheData[$cacheName] = $this->countEmoticons(array('user_id' => $userId));
		}

		if(!$this->_localCacheData[$cacheName])
		{
			return true;
		}

		return ($this->_localCacheData[$cacheName] >= $maxEmoticons) ? false : true;
	}

	protected function _hasPermission(array $permissions, $permissionKey, $group = 'general')
	{
		$permissionKey = 'emoticons_'.$permissionKey;
		return XenForo_Permission::hasPermission($permissions, $group, $permissionKey);
	}

	/**
	 * Get emoticon for URL
	 *
	 * @return string
	 */
	public function getEmoticonUrl(array $emoticon)
	{
		return sprintf('%s/emoticons/%d/%s.%s?t=%d',
			XenForo_Application::$externalDataUrl,
			floor($emoticon['emoticon_id'] / 1000),
			$emoticon['filehash'],
			$emoticon['extension'],
			$emoticon['added_at']
		);
	}

	/**
	 * Get the absolute emoticon path
	 *
	 * @return string
	 */
	public function getEmoticonPath(array $emoticon)
	{
		return sprintf('%s/emoticons/%d/%s.%s',
			XenForo_Helper_File::getExternalDataPath(),
			floor($emoticon['emoticon_id'] / 1000),
			$emoticon['filehash'],
			$emoticon['extension']
		);
	}

	/**
	 * @return array
	 */
	public function getAllowedExtensions()
	{
		return $this->_allowedExtensions;
	}
}
