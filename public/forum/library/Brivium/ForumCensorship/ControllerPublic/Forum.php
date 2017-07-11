<?php
class Brivium_ForumCensorship_ControllerPublic_Forum extends XFCP_Brivium_ForumCensorship_ControllerPublic_Forum
{
	public function actionForum()
	{
		$response = parent::actionForum();
		if(!empty($response->params['forum']['node_id'])){
			$nodeId = $response->params['forum']['node_id'];
			XenForo_Helper_Cookie::setCookie('forumCensorship', $nodeId);
		}
		return $response;
	}
	public function actionIndex()
	{
		$response = parent::actionIndex();
		if(!method_exists('XenForo_ControllerPublic_Forum','actionForum')){
			if(!empty($response->params['forum']['node_id'])){
				$nodeId = $response->params['forum']['node_id'];
				XenForo_Helper_Cookie::setCookie('forumCensorship', $nodeId);
			}
		}
		return $response;
	}
}