<?php
class KomuKu_FollowingAlerts_XenResource_Model_ResourceWatch extends XFCP_KomuKu_FollowingAlerts_XenResource_Model_ResourceWatch
{
	protected $_notifiedUserIds = array();
	
	public function sendNotificationToWatchUsersOnUpdate(array $update, array $resource, array $noAlerts = array(), array $noEmail = array())
	{
		$response = parent::sendNotificationToWatchUsersOnUpdate($update, $resource, $noAlerts, $noEmail);
		
		$notifiedUserIds = array(
			'alerted' => $noAlerts,
			'emailed' => $noEmail
		);
		
		if (!empty($response['alerted']))
		{
			$notifiedUserIds['alerted'] += $notifiedUserIds['alerted'];
		}
		
		if (!empty($response['emailed']))
		{
			$notifiedUserIds['emailed'] += $notifiedUserIds['emailed'];
		}
		
		$this->_notifiedUserIds[$update['resource_update_id']] = $notifiedUserIds;

		return $response;
	}
	
	public function get_notifiedUserIds_onUpdate(array $update)
	{
		if (isset($this->_notifiedUserIds[$update['resource_update_id']]))
		{
			return $this->_notifiedUserIds[$update['resource_update_id']];
		}
		else
		{
			return array(
				'alerted' => array(),
				'emailed' => array()
			);
		}
	}
}