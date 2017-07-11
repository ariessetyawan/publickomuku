<?php
class KomuKu_NodesGrid_XenForo_ControllerAdmin_Category extends XFCP_KomuKu_NodesGrid_XenForo_ControllerAdmin_Category
{
	public function actionSave()
	{
		$GLOBALS['node_input'] = $this->getInput();
		return parent::actionSave();
	}
}
?>