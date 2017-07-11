<?php
class KomuKu_FollowingAlerts_XenResource_DataWriter_Update extends XFCP_KomuKu_FollowingAlerts_XenResource_DataWriter_Update
{
	protected function _postSaveAfterTransaction()
	{
		$response = parent::_postSaveAfterTransaction();
		
		if ($this->_isFirstVisible)
		{
			if ($this->_resource
				&& $this->_resource['resource_state'] == 'visible'
				&& $this->get('message_state') == 'visible'
				&& !empty($this->_resource['description_update_id'])
				&& $this->_resource['description_update_id'] != $this->get('resource_update_id')
			)
			{
				$update = $this->getMergedData();

				$notified1 = $this->getModelFromCache('XenResource_Model_ResourceWatch')->get_notifiedUserIds_onUpdate($update);
				$notified2 = $this->getModelFromCache('XenResource_Model_CategoryWatch')->get_notifiedUserIds_toWatchUsers($update);

				$notified = array_merge($notified1, $notified2);
				$this->getModelFromCache('KomuKu_FollowingAlerts_Model_Follow')->alertOnResourceUpdate($update, $this->_resource, $notified);
			}
		}
		
		return $response;
	}
}