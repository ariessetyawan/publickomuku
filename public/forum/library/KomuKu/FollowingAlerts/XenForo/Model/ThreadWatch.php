<?php
class KomuKu_FollowingAlerts_XenForo_Model_ThreadWatch extends XFCP_KomuKu_FollowingAlerts_XenForo_Model_ThreadWatch
{
	protected $_notifiedUserIds = array();

	public function sendNotificationToWatchUsersOnReply(array $reply, array $thread = null, array $noAlerts = array())
	{
		$response = parent::sendNotificationToWatchUsersOnReply($reply, $thread, $noAlerts);
		
		$notifiedUserIds = array(
			'alerted' => $noAlerts,
			'emailed' => array()
		);
		
		if (!empty($response['alerted']))
		{
			$notifiedUserIds['alerted'] += $response['alerted'];
		}
		
		if (!empty($response['emailed']))
		{
			$notifiedUserIds['emailed'] += $response['emailed'];
		}
		
		$this->_notifiedUserIds[$reply['post_id']] = $notifiedUserIds;
		
		return $response;
	}
	
	public function get_notifiedUserIds_onReply(array $reply)
	{
		if (isset($this->_notifiedUserIds[$reply['post_id']]))
		{
			return $this->_notifiedUserIds[$reply['post_id']];
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