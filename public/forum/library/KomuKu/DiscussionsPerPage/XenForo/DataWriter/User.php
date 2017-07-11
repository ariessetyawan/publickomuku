<?php

class KomuKu_DiscussionsPerPage_XenForo_DataWriter_User extends XFCP_KomuKu_DiscussionsPerPage_XenForo_DataWriter_User
{
	protected function _getFields()
	{
		$fields = parent::_getFields();

		$fields['kmk_user_option']['custom_messages'] = array(
			'type' => self::TYPE_SERIALIZED, 
			'default' => 'a:0:{}'
		);

		return $fields;
	}
	
	protected function _preSave()
	{
		if(!is_null(KomuKu_DiscussionsPerPage_Listener::$globalData))
		{
			KomuKu_DiscussionsPerPage_Listener::$globalData->DPP_actionPreferencesSave($this);
		}
		
		return parent::_preSave();
	}
}