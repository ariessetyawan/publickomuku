<?php
class KomuKu_CustomNodeIcon_XenForo_ControllerAdmin_Forum extends XFCP_KomuKu_CustomNodeIcon_XenForo_ControllerAdmin_Forum {
	public function actionSave() {
		$GLOBALS[KomuKu_CustomNodeIcon_Option::GLOBALS_CONTROLLER_ADMIN_FORUM_ACTION_SAVE] = $this;
		
		return parent::actionSave();
	}
	
	public function actionEdit() {
		$response = parent::actionEdit();
		
		if ($response instanceof XenForo_ControllerResponse_View) {
			$response->params[KomuKu_CustomNodeIcon_Option::KEY_PARAMS_NODE] = $response->params['forum'];
			KomuKu_CustomNodeIcon_Icon::injectIconsToNode($response->params[KomuKu_CustomNodeIcon_Option::KEY_PARAMS_NODE]);
		}
		
		return $response;
	}
}