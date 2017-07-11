<?php

class HtmlCustomTitles_Model_Permission extends XenForo_Model
{
	public function applyUserPermissions(array $user)
	{
		$db = $this->_getDb();

		$user['global_permission_cache'] = $db->fetchOne('
			SELECT cache_value
			FROM kmk_permission_combination
			WHERE permission_combination_id = ?
		', $user['permission_combination_id']);

		return $user;
	}
}