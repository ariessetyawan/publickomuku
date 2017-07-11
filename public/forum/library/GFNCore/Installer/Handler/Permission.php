<?php /*2eb44f3de169eae108c9c3018dbbff990758053f*/

/**
 * @package    GoodForNothing Core
 * @version    1.0.0 Beta 1
 * @since      1.0.0 Beta 1
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <http://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNCore_Installer_Handler_Permission extends GFNCore_Installer_Handler_Abstract
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    public function __construct()
    {
        $this->_db = XenForo_Application::getDb();
    }

    public function applyGlobalPermission($applyGroupId, $applyPermissionId, $dependGroupId = null, $dependPermissionId = null, $checkModerator = true)
    {
        $db = $this->_db;

        XenForo_Db::beginTransaction($db);

        if ($dependGroupId && $dependPermissionId)
        {
            $db->query(
                "INSERT IGNORE INTO kmk_permission_entry
                  (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT user_group_id, user_id, ?, ?, permission_value, permission_value_int
				FROM kmk_permission_entry
				WHERE permission_group_id = ?
                  AND permission_id = ?
                  AND (permission_value = 'allow' OR permission_value = 'use_int')", array($applyGroupId, $applyPermissionId, $dependGroupId, $dependPermissionId)
            );
        }
        else
        {
            $permissionValue = 'allow';
            $permissionInt = 0;

            if ($dependGroupId !== null)
            {
                if ($dependGroupId === false)
                {
                    $permissionValue = 'unset';
                }
                elseif (is_int($dependGroupId))
                {
                    $permissionValue = 'use_int';
                    $permissionInt = $dependGroupId;
                }
            }

            $db->query(
                "INSERT IGNORE INTO kmk_permission_entry
				  (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
				SELECT DISTINCT user_group_id, user_id, ?, ?, ?, ?
				FROM kmk_permission_entry", array($applyGroupId, $applyPermissionId, $permissionValue, $permissionInt)
            );
        }

        if ($checkModerator)
        {
            foreach ($this->getGlobalModPermissions() as $userId => $permissions)
            {
                if ($dependGroupId === null || $dependPermissionId === null || !empty($permissions[$dependGroupId][$dependPermissionId]))
                {
                    $permissions[$applyGroupId][$applyPermissionId] = '1';
                    $this->updateGlobalModPermissions($userId, $permissions);
                }
            }
        }

        XenForo_Db::commit($db);
    }

    protected static $_globalModPermCache = null;

    public function getGlobalModPermissions()
    {
        if (self::$_globalModPermCache === null)
        {
            $moderators = $this->_db->fetchPairs(
                'SELECT user_id, moderator_permissions
                FROM kmk_moderator'
            );

            if (!empty($moderators))
            {
                foreach ($moderators as &$permissions)
                {
                    $permissions = unserialize($permissions);
                }
            }
            else
            {
                $moderators = array();
            }

            self::$_globalModPermCache = $moderators;
            unset($moderators);
        }

        return self::$_globalModPermCache;
    }

    public function updateGlobalModPermissions($userId, array $permissions)
    {
        self::$_globalModPermCache[$userId] = $permissions;

        $this->_db->update('kmk_moderator', array(
            'moderator_permissions' => serialize($permissions)
        ), 'user_id = ' . $this->_db->quote($userId));
    }
} 