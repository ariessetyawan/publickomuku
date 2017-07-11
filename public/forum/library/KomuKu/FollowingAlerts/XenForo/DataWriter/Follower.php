<?php
class KomuKu_FollowingAlerts_XenForo_DataWriter_Follower extends XFCP_KomuKu_FollowingAlerts_XenForo_DataWriter_Follower
{
	protected function _getFields() {
		$fields = parent::_getFields();
		
		$fields['kmk_user_follow']['alert_preferences'] = array('type' => self::TYPE_SERIALIZED, 'default' => 'a:0{}');
		return $fields;
	}

	protected function _preSave() {
		if (isset($GLOBALS['KomuKu_FollowingAlerts_ControllerPublic_Member::actionFollow'])) {
			$GLOBALS['KomuKu_FollowingAlerts_ControllerPublic_Member::actionFollow']->followingAlerts_actionFollow($this);
		}
		
		return parent::_preSave();
	}
}