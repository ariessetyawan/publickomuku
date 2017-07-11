<?php
class KomuKu_CustomNodeIcon_XenForo_DataWriter_Forum extends XFCP_KomuKu_CustomNodeIcon_XenForo_DataWriter_Forum {
	protected function _preSave() {
		if (isset($GLOBALS[KomuKu_CustomNodeIcon_Option::GLOBALS_CONTROLLER_ADMIN_FORUM_ACTION_SAVE])) {
			KomuKu_CustomNodeIcon_DataWriter_Helper::doPreSave($GLOBALS[KomuKu_CustomNodeIcon_Option::GLOBALS_CONTROLLER_ADMIN_FORUM_ACTION_SAVE], $this);
		}		
		
		return parent::_preSave();
	}
	
	protected function _postSave() {
		KomuKu_CustomNodeIcon_DataWriter_Helper::doPostSave($this);
		
		return parent::_postSave();
	}

	protected function _postDelete() {
		KomuKu_CustomNodeIcon_DataWriter_Helper::doPostDelete($this);

		return parent::_postDelete();
	}
}