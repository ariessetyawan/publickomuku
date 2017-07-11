<?php

class KomuKu_SimpleForms_Model_ForumForm extends XenForo_Model
{
	public function getForumFormById($forumFormId)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM `kmkform__forum_form`
			WHERE `forum_form_id` = ?
		', $forumFormId);
	}
}