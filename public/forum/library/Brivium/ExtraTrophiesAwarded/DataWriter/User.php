<?php

class Brivium_ExtraTrophiesAwarded_DataWriter_User extends XFCP_Brivium_ExtraTrophiesAwarded_DataWriter_User
{
	protected function _getFields()
	{
		$fields = parent::_getFields();
		
		$fields ['kmk_user']['breta_event_date'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields ['kmk_user']['breta_user_level'] = array('type' => self::TYPE_UINT, 'default' => 1);
		$fields ['kmk_user']['breta_curent_level'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields ['kmk_user']['breta_next_level'] = array('type' => self::TYPE_UINT, 'default' => 0);
		
		return $fields;
	}
}