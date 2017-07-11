<?php

class PostComments_Model_Alert extends XFCP_PostComments_Model_Alert
{
	public function sendCommentAlert($alertType, $postId, array $alerts, XenForo_Visitor $visitor = null)
	{
		$visitor = XenForo_Visitor::getInstance(); 
		
		if (!$visitor)
		{
			$visitor = XenForo_Visitor::getInstance();
		}

		if (!empty($alerts))
		{
			foreach ($alerts as $alert)
			{
				$user = $this->_getUserModel()->getUserByName($alert);
				
				if (XenForo_Model_Alert::userReceivesAlert($user, 'post', $alertType))
				{
					XenForo_Model_Alert::alert($user['user_id'],
							$visitor['user_id'], $visitor['username'],
							'post', 
							$postId,
							$alertType
					);
				}
			}
		}

		return false;
	}
	
	//Return the user model
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
}