<?php

class KomuKu_Emoticons_XenForo_Model_Smilie extends XFCP_KomuKu_Emoticons_XenForo_Model_Smilie
{
	public function getAllSmiliesCategorized($includeHidden = true)
	{
		$categories = parent::getAllSmiliesCategorized($includeHidden);

		$userId = XenForo_Visitor::getUserId();
		$emoticonModel = $this->getModelFromCache('KomuKu_Emoticons_Model_Emoticon');

		$emoticons = $emoticonModel->getEmoticonsFromList($userId);

		if(empty($emoticons) OR !$emoticonModel->canUseOwnEmoticons())
		{
			return $categories;
		}

		// We are push new categories on default list
		$categories['emoticons'] = array(
			'smilie_category_id' => 'emoticons',
			'smilies' => $emoticonModel->prepareEmoticonsForEditor($emoticons)
		);

		return $categories;
	}

	public function getSmilieCategoryTitlePhraseName($smilieCategoryId)
	{
		if('emoticons' === $smilieCategoryId) {
			return 'emoticon_your_emoticons';
		}

		return parent::getSmilieCategoryTitlePhraseName($smilieCategoryId);
	}
}