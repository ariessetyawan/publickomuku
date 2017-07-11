<?php

class PostComments_DataWriter_Forum extends XFCP_PostComments_DataWriter_Forum
{
	protected function _getFields()
	{
		$fields = parent::_getFields();
		
		$fields['kmk_forum']['comment_count'] = array('type' => self::TYPE_UINT_FORCED, 'default' => 0);
		
		return $fields;
	}
}