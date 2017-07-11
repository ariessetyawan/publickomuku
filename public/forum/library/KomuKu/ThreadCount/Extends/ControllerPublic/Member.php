<?php

/**
 * @author KomuKu
 * XenForo-Turkiye.com
 */

class KomuKu_ThreadCount_Extends_ControllerPublic_Member extends XFCP_KomuKu_ThreadCount_Extends_ControllerPublic_Member
{
    protected function _getNotableMembers($type, $limit)
	{

		if ($type == 'threads')
		{
			$userModel = $this->_getUserModel();

			$notableCriteria = array(
				'is_banned' => 0,
				'thread_count' => array('>', 0)
			);
			return array($userModel->getUsers($notableCriteria, array(
				'join' => XenForo_Model_User::FETCH_USER_FULL,
				'limit' => $limit,
				'order' => 'thread_count',
				'direction' => 'desc'
			)), 'thread_count');
		}

		return parent::_getNotableMembers($type, $limit);
	}
}
?>