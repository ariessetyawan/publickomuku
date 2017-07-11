<?php /*1684f64ae997ddd97019b722643400d442fc8dd8*/

/**
 * @package    GoodForNothing Classifieds
 * @version    1.0.0 Alpha 1
 * @since      1.0.0 Beta 7
 * @author     GoodForNothing Labs
 * @copyright  Copyright © 2012-2016 GoodForNothing Labs <https://gfnlabs.com/>
 * @license    https://gfnlabs.com/legal/license
 * @link       https://gfnlabs.com/
 */
class GFNClassifieds_ControllerAdmin_CategoryPermission_Abstract extends GFNClassifieds_ControllerAdmin_Abstract
{
    /**
     * @var array
     */
    protected $_category;

    protected function _getAdminPermission()
    {
        return 'classifiedCategory';
    }

    protected function _preDispatch($action)
    {
        parent::_preDispatch($action);
        $this->_category = $this->_getCategoryOrError();
    }

    /**
     * @return XenForo_Model_Permission
     */
    protected function _getPermissionModel()
    {
        return $this->getModelFromCache('XenForo_Model_Permission');
    }

    /**
     * @return XenForo_Model_UserGroup
     */
    protected function _getUserGroupModel()
    {
        return $this->getModelFromCache('XenForo_Model_UserGroup');
    }

    /**
     * @return XenForo_Model_User
     */
    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }

    protected function _getCategoryOrError($categoryId = null)
    {
        if ($categoryId === null)
        {
            $categoryId = $this->_input->filterSingle('category_id', XenForo_Input::UINT);
        }

        $category = $this->models()->category()->getCategoryById($categoryId);
        if (!$category)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_category_not_found');
        }

        return $this->models()->category()->prepareCategory($category);
    }

    protected function _getValidUserOrError($userId)
    {
        $user = $this->_getUserModel()->getUserById($userId);
        if (!$user)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_user_not_found');
        }

        return $user;
    }

    protected function _getValidUserGroupOrError($userGroupId)
    {
        $userGroup = $this->_getUserGroupModel()->getUserGroupById($userGroupId);
        if (!$userGroup)
        {
            throw $this->getErrorOrNoPermissionResponseException('requested_user_group_not_found');
        }

        return $userGroup;
    }

    protected function _permissionsAreRevoked($categoryId, $userGroupId, $userId)
    {
        $permissions = $this->_getPermissionModel()->getContentPermissionsWithValues(
            'classified_category', $categoryId, 'classifieds', $userGroupId, $userId
        );

        foreach ($permissions AS $permission)
        {
            if ($permission['permission_group_id'] == 'classifieds' && $permission['permission_id'] == 'view' && $permission['permission_value'] === 'reset')
            {
                return true;
            }
        }

        return false;
    }

    protected function _setPermissionRevokeStatus($categoryId, $userGroupId, $userId, $revoke)
    {
        $update = array('classifieds' => array('view' => $revoke ? 'reset' : 'unset'));

        $this->_getPermissionModel()->updateContentPermissionsForUserCollection(
            $update, 'classified_category', $categoryId, $userGroupId, $userId
        );
    }
}