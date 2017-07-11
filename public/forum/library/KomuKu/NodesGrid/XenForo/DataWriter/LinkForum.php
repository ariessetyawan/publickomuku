<?php
class KomuKu_NodesGrid_XenForo_DataWriter_LinkForum extends XFCP_KomuKu_NodesGrid_XenForo_DataWriter_LinkForum
{
	protected function _getFields()
	{
		$fields = parent::_getFields();
		if(empty($fields['kmk_node']['grid_column']))
		{
			$fields['kmk_node']['grid_column'] = array(
				'type' => self::TYPE_BOOLEAN,
				'default' => 0
			);
		}
		return $fields;
	}

	protected function _preSave()
	{
		parent::_preSave();
		if(isset($GLOBALS['node_input']))
		{
			$this->set('grid_column', $GLOBALS['node_input']->filterSingle('grid_column', XenForo_Input::BOOLEAN));
			unset($GLOBALS['node_input']);
		}
	}
}
?>