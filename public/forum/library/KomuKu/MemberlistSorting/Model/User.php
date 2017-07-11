<?php

class KomuKu_MemberlistSorting_Model_User extends XFCP_KomuKu_MemberlistSorting_Model_User
{
	public function getUsers(array $conditions, array $fetchOptions = array())
	{
		if(!empty($GLOBALS['begin'])&& $GLOBALS['begin'] != '#')
		{
			$conditions['username'] = array($GLOBALS['begin'], 'r');
		}
		
		return parent::getUsers($conditions,$fetchOptions);
	}
}