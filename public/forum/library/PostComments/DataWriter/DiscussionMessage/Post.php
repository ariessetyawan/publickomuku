<?php

class PostComments_DataWriter_DiscussionMessage_Post extends XFCP_PostComments_DataWriter_DiscussionMessage_Post
{
	protected function _getFields() 
	{
		$fields = parent::_getFields();

		$fields['kmk_post']['comment_count'] = array(
			'type' => self::TYPE_UINT,
		);

		return $fields;
	}
}