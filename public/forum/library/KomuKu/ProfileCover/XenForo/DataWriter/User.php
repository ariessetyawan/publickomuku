<?php

class KomuKu_ProfileCover_XenForo_DataWriter_User extends XFCP_KomuKu_ProfileCover_XenForo_DataWriter_User
{
	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['kmk_user']['cover_date'] = array('type' => static::TYPE_UINT, 'default' => 0);

		return $fields;
	}

	protected function _postSave()
	{
		if ($this->isChanged('cover_date'))
		{
			KomuKu_ProfileCover_Cover::publishNewsFeed($this);
		}

		return parent::_postSave();
	}

	protected function _postDelete()
	{
		KomuKu_ProfileCover_Cover::deleteCover($this->get('user_id'));
		KomuKu_ProfileCover_Cover::deleteNewsFeed($this);

		return parent::_postDelete();
	}

}



