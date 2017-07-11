<?php

class KomuKu_featuredmembers_XenForo_DataWriter_User extends XFCP_KomuKu_featuredmembers_XenForo_DataWriter_User
{
	protected function _getFields()
	{
		$parent = parent::_getFields();

		$parent['kmk_user']['dad_fm_is_featured'] = ['type' => self::TYPE_BOOLEAN, 'default' => 0];

		$parent['kmk_user']['dad_fm_is_verified'] = ['type' => self::TYPE_BOOLEAN, 'default' => 0];

		return $parent;
	}
}

if (false)
{
	class XFCP_KomuKu_featuredmembers_XenForo_DataWriter_User extends XenForo_DataWriter_User {}
}