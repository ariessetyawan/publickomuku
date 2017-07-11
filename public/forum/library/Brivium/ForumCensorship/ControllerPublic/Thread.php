<?php
class Brivium_ForumCensorship_ControllerPublic_Thread extends XFCP_Brivium_ForumCensorship_ControllerPublic_Thread
{
	public function actionIndex()
	{
		$response = parent::actionIndex();
		if(!empty($response->params['thread']['node_id'])){
			$GLOBALS['BRFC_forumCensorship'] = $response->params['thread']['node_id'];
		}
		return $response;
	}
}