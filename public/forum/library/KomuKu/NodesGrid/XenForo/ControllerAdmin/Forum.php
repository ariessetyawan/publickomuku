<?php
class KomuKu_NodesGrid_XenForo_ControllerAdmin_Forum extends XFCP_KomuKu_NodesGrid_XenForo_ControllerAdmin_Forum
{
	public function actionSave()
	{
		$GLOBALS['node_input'] = $this->getInput();
		return parent::actionSave();
	}
}
?>