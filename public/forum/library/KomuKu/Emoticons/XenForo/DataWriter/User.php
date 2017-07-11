<?php

class KomuKu_Emoticons_XenForo_DataWriter_User extends XFCP_KomuKu_Emoticons_XenForo_DataWriter_User
{
	/**
	 * {@inheritdoc}
	 */
	protected function _postDelete()
	{
		$this->getModelFromCache('KomuKu_Emoticons_Model_Emoticon')->deleteAllEmoticonsByUserId($this->get('user_id'));

		return parent::_postDelete();
	}
}