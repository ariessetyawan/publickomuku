<?php
class Brivium_MetadataEssential_DataWriter_Resource extends XFCP_Brivium_MetadataEssential_DataWriter_Resource
{
	public function save() {
		$saved = parent::save();
		if (isset($GLOBALS['BRME_CPR_actionSave'])) {
			$GLOBALS['BRME_CPR_actionSave']->brmeActionSave($this);
			unset($GLOBALS['BRME_CPR_actionSave']);
		}
		return $saved;
	}
}