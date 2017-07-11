<?php
/**
* Data writer for Forums.
*
* @package XenForo_Forum
*/
class Brivium_MetadataEssential_DataWriter_Category extends XFCP_Brivium_MetadataEssential_DataWriter_Category
{
	public function save() {
		$saved = parent::save();
		if (isset($GLOBALS['BRME_CARC_actionSave'])) {
			$GLOBALS['BRME_CARC_actionSave']->brmeActionSave($this);
			unset($GLOBALS['BRME_CARC_actionSave']);
		}
		return $saved;
	}
}