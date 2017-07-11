<?php
class KomuKu_NodesGrid_XenForo_ControllerAdmin_LinkForum extends XFCP_KomuKu_NodesGrid_XenForo_ControllerAdmin_LinkForum
{
	public function actionSave()
	{
		$GLOBALS['node_input'] = $this->getInput();
		return parent::actionSave();
	}
}
?>