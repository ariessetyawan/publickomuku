<?php

/*
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Extends_DataWriter_User extends XFCP_KomuKu_ThreadCount_Extends_DataWriter_User
{

	protected function _getFields()
	{
		$parent = parent::_getFields();

		$parent['kmk_user']['thread_count'] = array(
			'type' => self::TYPE_UINT, 'default' => 0
		);


		return $parent;
	}

}