<?php

//######################## Extra Thread View Settings By KomuKu ###########################
class  KomuKu_ThreadExtras_DataWriter_Discussion_Thread extends XFCP_KomuKu_ThreadExtras_DataWriter_Discussion_Thread
{
    /**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	*/
	protected function _getFields() 
	{
		$fields = parent::_getFields();
		
		$fields['kmk_thread']['posts'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['daily_posts'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['thread_count'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['user_likes'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['user_trophy'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['reg_days'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['age'] = array('type' => self::TYPE_UINT, 'default' => 0);
		$fields['kmk_thread']['user_gender'] = array('type' => self::TYPE_STRING);
		$fields['kmk_thread']['user_moderation'] = array('type' => self::TYPE_STRING);
		$fields['kmk_thread']['specific_users'] = array('type' => self::TYPE_STRING, 'maxLength' => 500, 'default' => '');
		$fields['kmk_thread']['specific_users_extra'] = array('type' => self::TYPE_UNKNOWN);
		
		return $fields;
	}
}