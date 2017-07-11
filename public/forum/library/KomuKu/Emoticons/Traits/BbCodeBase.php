<?php

trait KomuKu_Emoticons_Traits_BbCodeBase
{
    /**
	 * @var KomuKu_Emoticons_Model_Emoticon
	 */
	private $_emoticonModel;

	/**
	 * The current user id of message which working on
	 *
	 * @var integer|null
	 */
	private $_workOnUserId;

	/**
	 * List of all user emoticons
	 *
	 * @var array
	 */
	private $_emoticons = array();

	/**
	 * @var array
	 */
	private $_emoticonsTranslate = array();

	/**
	 * {@inheritdoc}
	 */
	public function preLoadData()
	{
		// Sometime it is not registered the emoticons key
		// We should register it again.
		$this->_getEmoticonModel()->registerLazyLoader();
		$emoticons = XenForo_Application::get('emoticons');

		$this->_prepareEmoticons($emoticons);
		$this->_emoticons = $emoticons;

		return parent::preLoadData();
	}

	protected function _prepareEmoticons(array $emoticonList)
	{
		if(empty($emoticonList))
		{
			return;
		}

		$userIds = XenForo_Application::arrayColumn($emoticonList, 'user_id');
		$userIds = array_unique($userIds);

		$emoticonModel = $this->_getEmoticonModel();

		foreach($userIds as $userId)
		{
			$emoticons = $emoticonModel->getEmoticonsFromList($userId, $emoticonList);

			foreach($emoticons as $emoticon)
			{
				$this->_emoticonsTranslate[$userId][$emoticon['text_replace']] = "\0".$emoticon['emoticon_id']."\0";
			}
		}
	}

	public function filterString($string, array $rendererStates)
	{
		if(!empty($rendererStates['extraUserId']))
		{
			// Set the current user id working on
			$this->_workOnUserId = $rendererStates['extraUserId'];

			$rendered = parent::filterString($string, $rendererStates);

			// Revert working user id to null.
			$this->_workOnUserId = null;

			return $rendered;
		}

		return parent::filterString($string, $rendererStates);
	}

	public function renderTree(array $tree, array $extraStates = array())
	{
		if(empty($extraStates['extraUserId']) && !empty($tree[0]) && is_string($tree[0]))
		{
			$userId = KomuKu_Emoticons_String::deattach($tree[0]);
			if(!empty($userId))
			{
				$extraStates['extraUserId'] = $userId;
			}
		}

		return parent::renderTree($tree, $extraStates);
	}

	public function replaceSmiliesInText($text, $escapeCallback = '')
	{
		$rendered = parent::replaceSmiliesInText($text, $escapeCallback);
		$rendered = $this->_replaceCustomEmoticonsInText($rendered, $this->_workOnUserId);

		return $rendered;
	}

	public function renderTagQuote(array $tag, array $rendererStates)
	{
		if($tag['option'])
		{
			$parts = array_map('trim', explode(',', $tag['option']));
			$parts = end($parts);

			if(!$parts)
			{
				return parent::renderTagQuote($tag, $rendererStates);
			}

			$parts = array_map('trim', explode(' ', $parts));
			$userId = end($parts);

			$rendererStates['extraUserId'] = $userId;
		}

		return parent::renderTagQuote($tag, $rendererStates);
	}

	protected function _replaceCustomEmoticonsInText($text, $userId)
	{
		if(empty($this->_emoticonsTranslate[$userId]) || empty($text))
		{
			return $text;
		}
		$emoticonTranslate = $this->_emoticonsTranslate[$userId];

		$text = strtr($text, $emoticonTranslate);
		$split = preg_split("#\\0(\d+)\\0#", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

		$text = '';
		foreach($split as $key => $value)
		{
			// odd keys contain the delimiter we want
			if ($key % 2 == 0)
			{
				$text .= $value;
			}
			else if (isset($this->_emoticons[$value]))
			{
				$emoticon = $this->_emoticons[$value];

				$text .= sprintf($this->_smilieTemplate,
					$emoticon['emoticonUrl'],
					htmlspecialchars($emoticon['text_replace']),
					htmlspecialchars($emoticon['caption'])
				);
			}
		}

		return $text;
	}

	protected function _getEmoticonModel()
	{
		if($this->_emoticonModel === null)
		{
			$this->_emoticonModel = XenForo_Model::create('KomuKu_Emoticons_Model_Emoticon');
		}

		return $this->_emoticonModel;
	}
}
