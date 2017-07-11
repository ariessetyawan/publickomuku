<?php

class PostComments_ViewPublic_Comment extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		return array(
			'comment' => $this->createTemplateObject('post_comment', $this->_params)
		);
	}
}