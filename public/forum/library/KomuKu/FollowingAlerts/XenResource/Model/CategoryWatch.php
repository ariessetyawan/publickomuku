<?php
class KomuKu_FollowingAlerts_XenResource_Model_CategoryWatch extends XFCP_KomuKu_FollowingAlerts_XenResource_Model_CategoryWatch
{
	protected $_notifiedUserIds = array();
	
	public function sendNotificationToWatchUsers(array $update, array $resource, array $noAlerts = array(), array $noEmail = array())
	{
		$response = parent::sendNotificationToWatchUsers($update, $resource, $noAlerts, $noEmail);
		
		$notifiedUserIds = array(
			'alerted' => $noAlerts,
			'emailed' => $noEmail
		);
		
		if (!empty($response['alerted']))
		{
			$notifiedUserIds['alerted'] += $response['alerted'];
		}
		
		if (!empty($response['emailed']))
		{
			$notifiedUserIds['emailed'] += $response['emailed'];
		}
		
		$this->_notifiedUserIds[$update['resource_update_id']] = $notifiedUserIds;

		return $response;
	}
	
	public function get_notifiedUserIds_toWatchUsers(array $update)
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