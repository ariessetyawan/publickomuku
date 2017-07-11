<?php

class KomuKu_DiscussionsPerPage_XenForo_ControllerPublic_Conversation extends XFCP_KomuKu_DiscussionsPerPage_XenForo_ControllerPublic_Conversation
{
	protected function _getListFetchOptions()
	{
		$options = XenForo_Application::get('options');

		$userOptions = KomuKu_DiscussionsPerPage_Listener::$userOptions;
		if ($userOptions && array_key_exists('conversations', $userOptions) && $userOptions['conversations'])
		{
			$options->set('discussionsPerPage', $userOptions['conversations']);
		}

		return parent::_getListFetchOptions();
	}


}