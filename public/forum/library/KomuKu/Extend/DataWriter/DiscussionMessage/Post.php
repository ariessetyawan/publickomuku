<?php
class KomuKu_Extend_DataWriter_DiscussionMessage_Post extends XFCP_KomuKu_Extend_DataWriter_DiscussionMessage_Post {
	protected function _getFields() {
		$fields = parent::_getFields();
		
		$fields['kmk_post']['kmk_KomuKu_latest_given'] = array('type' => XenForo_DataWriter::TYPE_SERIALIZED, 'default' => 'a:0:{}');
		
		return $fields;
	}
}