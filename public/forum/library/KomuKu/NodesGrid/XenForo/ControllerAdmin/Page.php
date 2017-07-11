<?php
class KomuKu_NodesGrid_XenForo_ControllerAdmin_Page extends XFCP_KomuKu_NodesGrid_XenForo_ControllerAdmin_Page
{
	public function actionSave()
	{
		$GLOBALS['node_input'] = $this->getInput();
		return parent::actionSave();
	}
}
?>