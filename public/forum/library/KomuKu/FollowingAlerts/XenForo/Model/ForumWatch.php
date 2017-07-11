<?php
class KomuKu_FollowingAlerts_XenForo_Model_ForumWatch extends XFCP_KomuKu_FollowingAlerts_XenForo_Model_ForumWatch
{
	protected $_notifiedUserIds = array();
	
	public function sendNotificationToWatchUsersOnMessage(array $post, array $thread = null, array $noAlerts = array(), array $noEmail = array())
	{
		$response = parent::sendNotificationToWatchUsersOnMessage($post, $thread, $noAlerts, $noEmail);
		
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
		
		$this->_notifiedUserIds[$post['post_id']] = $notifiedUserIds;

		return $response;
	}
	
	public function get_notifiedUserIds_onMessage(array $post)
	{
		if (isset($this->_notifiedUserIds[$post['post_id']]))
		{
			return $this->_notifiedUserIds[$post['post_id']];
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