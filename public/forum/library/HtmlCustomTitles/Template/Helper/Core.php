<?php

class HtmlCustomTitles_Template_Helper_Core extends XenForo_Template_Helper_Core
{
	protected static $_permissionCache = array();
	protected static $_permissionModel = null;

	public static function helperUserTitle($user, $allowCustomTitle = true, $withBanner = false)
	{
		$parent = parent::helperUserTitle($user, $allowCustomTitle, $withBanner);

		if (empty($user['permissions']))
		{
			if (!isset(self::$_permissionCache[$user['user_id']]))
			{
				$permissionModel = self::_getPermissionModel();

				$user = $permissionModel->applyUserPermissions($user);
				$user['permissions'] = XenForo_Permission::unserializePermissions($user['global_permission_cache']);

				self::$_permissionCache[$user['user_id']] = $user['permissions'];
			}

			$user['permissions'] = self::$_permissionCache[$user['user_id']];
		}
		else
		{
			self::$_permissionCache[$user['user_id']] = $user['permissions'];
		}

		if (!empty($user['permissions']['general']['htmlCustomTitleAllowed']))
		{
			if ($allowCustomTitle && !empty($user['custom_title']))
			{
				return strip_tags($user['custom_title'], XenForo_Application::getOptions()->allowedTagsCustomTitles);
			}
		}

		return $parent;
	}

	/**
	 * @return HtmlCustomTitles_Model_Permission
	 */
	protected static function _getPermissionModel()
	{
		if (self::$_permissionModel === null)
		{
			self::$_permissionModel = XenForo_Model::create('HtmlCustomTitles_Model_Permission');
		}

		return self::$_permissionModel;
	}
}
