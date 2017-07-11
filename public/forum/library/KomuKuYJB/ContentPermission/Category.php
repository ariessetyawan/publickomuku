<?php /*f834dd9c5ea1dabdc0b4258e662e2650c16253f2*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class KomuKuYJB_ContentPermission_Category implements XenForo_ContentPermission_Interface
{
    protected $_initialized = false;

    /**
     * @var XenForo_Model_Permission
     */
    protected $_permissionModel = null;

    protected $_globalPerms = array();

    protected $_categoryTree = array();

    protected $_categories = array();

    protected $_permissionEntries = array();

    public function rebuildContentPermissions($permissionModel, array $userGroupIds, $userId, array $permissionsGrouped, array &$globalPerms)
    {
        $this->_permissionModel = $permissionModel;
        $this->_globalPerms = $globalPerms;

        $this->_setup();

        $finalPermissions = $this->_buildTreePermissions($userId, $userGroupIds, $globalPerms, $permissionsGrouped);

        $globalPerms = $this->_globalPerms;
        return $finalPermissions;
    }

    protected function _setup()
    {
        if ($this->_initialized)
        {
            return;
        }

        $categoryModel = $this->_getCategoryModel();

        $this->_categories = $categoryModel->getAllCategories();
        $this->_categoryTree = $categoryModel->groupCategoriesByParent($this->_categories);
        $this->_permissionEntries = $this->_permissionModel->getAllContentPermissionEntriesByTypeGrouped('classified_category');

        $this->_initialized = true;
    }

    protected function _buildTreePermissions($userId, array $userGroupIds, array $basePermissions, array $permissionsGrouped, $parentId = 0)
    {
        if (!isset($this->_categoryTree[$parentId]))
        {
            return array();
        }

        if (!isset($basePermissions['classifieds']['view']))
        {
            if (isset($this->_globalPerms['classifieds']['view']))
            {
                $basePermissions['classifieds']['view'] = $this->_globalPerms['classifieds']['view'];
            }
            else
            {
                $basePermissions['classifieds']['view'] = 'unset';
            }
        }

        $basePermissions = $this->_adjustBasePermissionAllows($basePermissions);

        $finalPermissions = array();

        foreach ($this->_categoryTree[$parentId] AS $category)
        {
            $categoryId = $category['category_id'];

            $groupEntries = $this->_getUserGroupPermissionEntries($categoryId, $userGroupIds);
            $userEntries = $this->_getUserPermissionEntries($categoryId, $userId);
            $categoryWideEntries = $this->_getCategoryWideEntries($categoryId);

            $categoryPermissions = $this->_permissionModel->buildPermissionCacheForCombination(
                $permissionsGrouped, $categoryWideEntries, $groupEntries, $userEntries,
                $basePermissions, $passPermissions
            );

            if (!isset($categoryPermissions['classifieds']['view']))
            {
                $categoryPermissions['classifieds']['view'] = 'unset';
            }

            $finalCategoryPermissions = $this->_permissionModel->canonicalizePermissionCache($categoryPermissions['classifieds']);

            if (isset($finalCategoryPermissions['view']) && !$finalCategoryPermissions['view'])
            {
                $passPermissions['classifieds']['view'] = 'deny';
            }

            $finalPermissions[$categoryId] = $finalCategoryPermissions;
            $finalPermissions += $this->_buildTreePermissions($userId, $userGroupIds, $passPermissions, $permissionsGrouped, $categoryId);
        }

        return $finalPermissions;
    }

    protected function _adjustBasePermissionAllows(array $basePermissions)
    {
        foreach ($basePermissions AS $group => $p)
        {
            foreach ($p AS $id => $value)
            {
                if ($value === 'content_allow')
                {
                    $basePermissions[$group][$id] = 'allow';
                }
            }
        }

        return $basePermissions;
    }

    protected function _getUserGroupPermissionEntries($categoryId, array $userGroupIds)
    {
        $rawUgEntries = $this->_permissionEntries['userGroups'];
        $groupEntries = array();
        foreach ($userGroupIds AS $userGroupId)
        {
            if (isset($rawUgEntries[$userGroupId], $rawUgEntries[$userGroupId][$categoryId]))
            {
                $groupEntries[$userGroupId] = $rawUgEntries[$userGroupId][$categoryId];
            }
        }

        return $groupEntries;
    }

    protected function _getUserPermissionEntries($categoryId, $userId)
    {
        $rawUserEntries = $this->_permissionEntries['users'];
        if ($userId && isset($rawUserEntries[$userId], $rawUserEntries[$userId][$categoryId]))
        {
            return $rawUserEntries[$userId][$categoryId];
        }
        else
        {
            return array();
        }
    }

    protected function _getCategoryWideEntries($categoryId)
    {
        if (isset($this->_permissionEntries['system'][$categoryId]))
        {
            return $this->_permissionEntries['system'][$categoryId];
        }
        else
        {
            return array();
        }
    }

    /**
     * @return KomuKuYJB_Model_Category
     */
    protected function _getCategoryModel()
    {
        return XenForo_Model::create('KomuKuYJB_Model_Category');
    }
}